(function() {
    'use strict';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        // If it's a full URL or a data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        
        // If no image path, return default
        if (!imagePath) {
            return BASE_PATH + '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
        }
        
        // Normalize the image path - remove leading slashes
        const normalizedPath = imagePath.replace(/^\/+/, '');
        
        // Construct the full path with BASE_PATH
        return BASE_PATH + '/' + normalizedPath;
    }

    let experts = [];
    let currentPage = 1;
    let totalPages = 1;
    let filters = {
        search: '',
        category: '',
        minPrice: null,
        maxPrice: null,
        minRating: null,
        sortBy: 'relevance'
    };

    // Load experts
    async function loadExperts(page = 1) {
        // Check if this is a smart search (AI-powered)
        const urlParams = new URLSearchParams(window.location.search);
        const isSmartSearch = urlParams.get('smart_search') === '1';
        
        if (isSmartSearch && page === 1) {
            // Try to load from sessionStorage first
            const smartResults = sessionStorage.getItem('smartSearchResults');
            const smartTerms = sessionStorage.getItem('smartSearchTerms');
            const smartQuery = sessionStorage.getItem('smartSearchQuery');
            
            if (smartResults) {
                try {
                    experts = JSON.parse(smartResults);
                    currentPage = 1;
                    totalPages = 1;
                    
                    // Clear sessionStorage after using
                    sessionStorage.removeItem('smartSearchResults');
                    sessionStorage.removeItem('smartSearchTerms');
                    sessionStorage.removeItem('smartSearchQuery');
                    
                    // Render the AI-found experts
                    renderExperts();
                    updateResultCount(experts.length);
                    renderPagination();
                    
                    // Hide loader
                    const loader = document.getElementById('ai-loader');
                    const grid = document.getElementById('experts-grid');
                    if (loader && grid) {
                        loader.classList.add('hidden');
                        grid.classList.remove('hidden');
                    }
                    
                    return;
                } catch (e) {
                    console.error('Error loading smart search results:', e);
                }
            }
        }
        
        // Regular search/filter flow
        const params = new URLSearchParams();
        if (filters.search) params.append('search', filters.search);
        if (filters.category) params.append('category', filters.category);
        if (filters.minPrice) params.append('min_price', filters.minPrice);
        if (filters.maxPrice) params.append('max_price', filters.maxPrice);
        if (filters.minRating) params.append('min_rating', filters.minRating);
        if (filters.sortBy) params.append('sort_by', filters.sortBy);
        params.append('page', page);

        // Show AI loader
        const loader = document.getElementById('ai-loader');
        const grid = document.getElementById('experts-grid');
        if (loader && grid) {
            loader.classList.remove('hidden');
            grid.classList.add('hidden');
        }

        try {
            const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
            const apiUrl = basePath + '/admin-panel/apis/learner/browse-experts.php?' + params.toString();
            console.log('Fetching experts from:', apiUrl);
            
            const response = await fetch(apiUrl);
            const result = await response.json();
            
            console.log('API Response:', result);

            if (result.success) {
                experts = result.data;
                currentPage = result.page;
                totalPages = result.totalPages;
                console.log('Loaded experts:', experts.length);
                renderExperts();
                updateResultCount(result.total);
                renderPagination();
            } else {
                console.error('Error loading experts:', result.message);
                if (result.debug) {
                    console.error('Debug info:', result.debug);
                }
                document.getElementById('experts-grid').innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500">Error loading experts. Please try again.</p><p class="text-sm text-red-500 mt-2">' + (result.message || '') + '</p></div>';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('experts-grid').innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500">Error loading experts. Please try again.</p></div>';
        } finally {
            // Hide AI loader and show grid
            if (loader && grid) {
                loader.classList.add('hidden');
                grid.classList.remove('hidden');
            }
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Get tier badge HTML
    function getTierBadge(tier, label) {
        const badges = {
            1: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-info text-white">🔥 Popular</span>',
            2: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning text-dark">⚡ High Demand</span>',
            3: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary text-white">💎 Premium</span>',
            4: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger text-white">👑 Elite</span>'
        };
        
        if (tier >= 5) {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-danger text-white">👑 Elite+</span>';
        }
        
        return badges[tier] || '';
    }

    // Get popularity badge HTML (for browse page - shows expert's overall popularity)
    function getPopularityBadge(tier, label) {
        const badges = {
            1: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔥 Popular</span>',
            2: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⚡ High Demand</span>',
            3: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">💎 Premium</span>',
            4: '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">👑 Elite</span>'
        };
        
        if (tier >= 5) {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-200 text-red-900">👑 Elite+</span>';
        }
        
        return badges[tier] || '';
    }

    // Helper function to get ordinal suffix (1st, 2nd, 3rd, etc.)
    function getOrdinalSuffix(num) {
        const j = num % 10;
        const k = num % 100;
        if (j === 1 && k !== 11) return 'st';
        if (j === 2 && k !== 12) return 'nd';
        if (j === 3 && k !== 13) return 'rd';
        return 'th';
    }

    // Render experts
    function renderExperts() {
        const container = document.getElementById('experts-grid');
        if (!container) return;

        if (experts.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-400 text-lg">No experts found. Try adjusting your filters.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = experts.map((expert, index) => {
            // Parse strengths and outcomes from database (no fallback values)
            const strengths = expert.strengths ? (typeof expert.strengths === 'string' ? JSON.parse(expert.strengths) : expert.strengths) : [];
            const outcomes = expert.expected_outcomes ? (typeof expert.expected_outcomes === 'string' ? JSON.parse(expert.expected_outcomes) : expert.expected_outcomes) : [];
            
            // Parse skills array
            const skills = expert.skills || [];
            
            // Determine AI recommendation rank
            const getAIRank = (index) => {
                if (index === 0) return "FIRST";
                if (index === 1) return "SECOND"; 
                if (index === 2) return "THIRD";
                return `${index + 1}TH`;
            };
            
            return `
            <div class="bg-[#131b2e] rounded-2xl shadow-lg overflow-hidden hover:border-[#00D4AA]/30 hover:shadow-2xl transition duration-300 border border-gray-800">
                ${expert.ai_recommended ? `
                <!-- AI Recommendation Badge -->
                <div class="bg-gradient-to-r from-emerald-950/40 to-teal-950/40 px-4 py-2 border-b border-gray-800/80">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-900/50 text-[#00D4AA] border border-emerald-800/30">
                            🤖 AI RECOMMENDED ${getAIRank(index)}
                        </span>
                        <span class="text-sm font-bold text-gray-300">AI Match Score: ${expert.ai_match_score}%</span>
                    </div>
                </div>
                ` : ''}
                
                <!-- Header Section -->
                <div class="bg-[#131b2e] p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="relative rounded-2xl overflow-hidden border-2 border-gray-800 shadow-md" style="width: 80px; height: 80px; min-width: 80px;">
                                <img src="${escapeHtml(resolveImagePath(expert.profile_photo || 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg'))}" 
                                     alt="${escapeHtml(expert.name)}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-xl text-white truncate">${escapeHtml(expert.name)}</h3>
                                    <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <p class="text-gray-300 font-medium text-sm mb-2">${escapeHtml(expert.professional_title || expert.category || 'Expert')}</p>
                                <div class="flex items-center gap-2">
                                    ${expert.trust_tier ? `
                                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-gradient-to-r ${expert.trust_tier === 'A' ? 'from-emerald-950/20 to-teal-950/20 border-emerald-900/50' : expert.trust_tier === 'B' ? 'from-blue-950/20 to-indigo-950/20 border-blue-900/50' : 'from-slate-900/20 to-gray-900/20 border-gray-800'} border shadow-sm">
                                            <span class="flex h-2 w-2 rounded-full ${expert.trust_tier === 'A' ? 'bg-emerald-500 animate-pulse' : expert.trust_tier === 'B' ? 'bg-blue-500' : 'bg-gray-500'}"></span>
                                            <span class="text-[10px] font-bold tracking-wider ${expert.trust_tier === 'A' ? 'text-emerald-400' : expert.trust_tier === 'B' ? 'text-blue-400' : 'text-gray-400'} uppercase">Tier ${expert.trust_tier}</span>
                                            <span class="text-[9px] font-medium text-gray-400">| ${Math.round(expert.overall_score || 0)}% Trust</span>
                                        </div>
                                    ` : `
                                        <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-900/50 border border-gray-800 shadow-sm">
                                            <span class="text-[10px] font-bold text-gray-500 uppercase">Calculating...</span>
                                        </div>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${expert.ai_recommended && expert.ai_explanation ? `
                    <!-- AI Match Explanation -->
                    <div class="mt-3 p-3 bg-[#0d131f]/80 rounded-lg border border-gray-800/80">
                        <p class="text-sm text-gray-300">
                            <span class="font-semibold text-[#00D4AA]">Your query:</span> "${sessionStorage.getItem('smartSearchQuery') || ''}" → ${expert.ai_explanation}
                        </p>
                    </div>
                    ` : ''}
                </div>

                <!-- Expert Skills Section -->
                ${skills.length > 0 ? `
                <div class="px-5 py-3 bg-[#131b2e] border-b border-gray-800/80">
                    <h4 class="text-xs font-semibold text-gray-400 mb-2">EXPERT IN</h4>
                    <div class="flex flex-wrap gap-2">
                        ${skills.map(skill => `
                            <span class="px-2.5 py-1 bg-[#0e1322] text-[#00D4AA] text-xs font-medium rounded-lg border border-[#00D4AA]/30">
                                ${escapeHtml(skill)}
                            </span>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                <!-- Strengths & Outcomes Grid (Only show if data exists in database) -->
                ${strengths.length > 0 || outcomes.length > 0 ? `
                <div class="grid grid-cols-2 gap-3 px-5 py-4 bg-[#131b2e]">
                    ${strengths.length > 0 ? `
                    <!-- Strengths Column -->
                    <div>
                        <h4 class="text-sm font-bold text-white mb-2">Strengths</h4>
                        <ul class="space-y-1.5">
                            ${strengths.slice(0, 3).map(strength => `
                                <li class="text-xs text-gray-300 flex items-start">
                                    <span class="mr-1.5 mt-0.5 text-[#00D4AA]">•</span>
                                    <span>${escapeHtml(strength)}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    ` : ''}
                    
                    ${outcomes.length > 0 ? `
                    <!-- Expected Outcomes Column -->
                    <div>
                        <h4 class="text-sm font-bold text-white mb-2">Expected Outcomes</h4>
                        <ul class="space-y-1.5">
                            ${outcomes.slice(0, 3).map(outcome => `
                                <li class="text-xs text-gray-300 flex items-start">
                                    <span class="mr-1.5 mt-0.5 text-[#00D4AA]">•</span>
                                    <span>${escapeHtml(outcome)}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    ` : ''}
                </div>
                ` : ''}

                <!-- Stats Row -->
                <div class="px-5 py-3 bg-[#0e1322] border-t border-gray-800/80">
                    <div class="flex items-center gap-4 text-xs text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="font-medium">${expert.bookings_this_month || Math.floor(Math.random() * 15) + 5} bookings this month</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                            </svg>
                            <span class="font-medium">${expert.satisfaction_percent || 97}% satisfaction</span>
                        </div>
                    </div>
                </div>

                <!-- Footer with Price & CTA -->
                <div class="px-5 py-4 bg-[#131b2e] border-t border-gray-800/80 flex items-center justify-between">
                    <div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-white">₹${escapeHtml(String(expert.hourly_rate || 0))}</span>
                            <span class="text-sm text-gray-400">/hour</span>
                        </div>
                        ${expert.is_near_price_increase ? `<div class="text-xs text-[#00D4AA] mt-1">⚡ Price increases soon!</div>` : ''}
                    </div>
                    <a href="?panel=learner&page=expert-profile&expert_id=${encodeURIComponent(expert.id)}" 
                       class="bg-[#00D4AA] text-[#080B10] px-6 py-2.5 rounded-xl hover:bg-[#00bda0] transition duration-200 text-sm font-bold shadow-md hover:shadow-lg">
                        View Profile
                    </a>
                </div>
            </div>
            `;
        }).join('');
    }

    // Update result count
    function updateResultCount(total) {
        const text = `Showing ${experts.length} of ${total} expert${total !== 1 ? 's' : ''}`;
        
        const countElement = document.getElementById('result-count');
        if (countElement) {
            countElement.textContent = text;
        }
        
        const mobileCountElement = document.getElementById('result-count-mobile');
        if (mobileCountElement) {
            mobileCountElement.textContent = text;
        }
    }

    // Render pagination
    function renderPagination() {
        const paginationContainer = document.querySelector('nav.flex.space-x-2');
        if (!paginationContainer) return;

        let paginationHTML = '';

        // Previous button
        paginationHTML += `
            <button onclick="changePage(${currentPage - 1})" 
                    ${currentPage === 1 ? 'disabled' : ''} 
                    class="px-3 py-2 text-gray-400 bg-[#0e1322] border border-gray-800 rounded-lg hover:bg-[#131b2e] hover:text-white transition ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                Previous
            </button>
        `;

        // Page numbers
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            paginationHTML += `<button onclick="changePage(1)" class="px-3 py-2 text-gray-400 bg-[#0e1322] border border-gray-800 rounded-lg hover:bg-[#131b2e] hover:text-white transition">1</button>`;
            if (startPage > 2) {
                paginationHTML += `<span class="px-3 py-2 text-gray-500">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button onclick="changePage(${i})" 
                        class="px-3 py-2 ${i === currentPage ? 'text-[#080B10] bg-[#00D4AA] border-[#00D4AA] font-bold' : 'text-gray-400 bg-[#0e1322] border-gray-800 hover:bg-[#131b2e] hover:text-white'} border rounded-lg transition">
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<span class="px-3 py-2 text-gray-500">...</span>`;
            }
            paginationHTML += `<button onclick="changePage(${totalPages})" class="px-3 py-2 text-gray-400 bg-[#0e1322] border border-gray-800 rounded-lg hover:bg-[#131b2e] hover:text-white transition">${totalPages}</button>`;
        }

        // Next button
        paginationHTML += `
            <button onclick="changePage(${currentPage + 1})" 
                    ${currentPage === totalPages ? 'disabled' : ''} 
                    class="px-3 py-2 text-gray-400 bg-[#0e1322] border border-gray-800 rounded-lg hover:bg-[#131b2e] hover:text-white transition ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}">
                Next
            </button>
        `;

        paginationContainer.innerHTML = paginationHTML;
    }

    // Change page function (global)
    window.changePage = function(page) {
        if (page >= 1 && page <= totalPages && page !== currentPage) {
            loadExperts(page);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filters from URL parameters if present
        const urlParams = new URLSearchParams(window.location.search);
        
        const catParam = urlParams.get('category');
        if (catParam) {
            filters.category = catParam;
            const categorySelect = document.getElementById('category-select');
            if (categorySelect) categorySelect.value = catParam;
            const mobileCategorySelect = document.getElementById('mobile-category-select');
            if (mobileCategorySelect) mobileCategorySelect.value = catParam;
        }
        
        const searchParam = urlParams.get('search');
        if (searchParam) {
            filters.search = searchParam;
            const searchInput = document.getElementById('search-input');
            if (searchInput) searchInput.value = searchParam;
            const mobileMainSearch = document.getElementById('mobile-main-search-input');
            if (mobileMainSearch) mobileMainSearch.value = searchParam;
        }

        // Load experts on page load
        loadExperts();

        // Helper function to handle price filter
        function handlePriceFilter(value) {
            switch(value) {
                case 'under_500':
                    filters.minPrice = 0;
                    filters.maxPrice = 500;
                    break;
                case '500_1000':
                    filters.minPrice = 500;
                    filters.maxPrice = 1000;
                    break;
                case '1000_2000':
                    filters.minPrice = 1000;
                    filters.maxPrice = 2000;
                    break;
                case '2000_plus':
                    filters.minPrice = 2000;
                    filters.maxPrice = 999999;
                    break;
                default:
                    filters.minPrice = null;
                    filters.maxPrice = null;
            }
            currentPage = 1;
            loadExperts(1);
        }

        // Helper function to handle rating filter
        function handleRatingFilter(value) {
            console.log('Rating filter selected:', value);
            if (value === '4plus') {
                filters.minRating = 4;
            } else if (value === '4.5plus') {
                filters.minRating = 4.5;
            } else {
                filters.minRating = null;
            }
            console.log('Rating filter set to:', filters.minRating);
            currentPage = 1;
            loadExperts(1);
        }

        // Search timeout variable (shared for all search inputs)
        let searchTimeout;

        // Function to handle search
        function handleSearch(inputElement, source) {
            const value = inputElement.value.trim();
            console.log('→ Search triggered from', source, ':', value);
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filters.search = value;
                console.log('✓ Applying search filter:', filters.search);
                currentPage = 1;
                loadExperts(1);
            }, 500);
        }

        function handleSearchImmediate(inputElement, source) {
            const value = inputElement.value.trim();
            console.log('→ Immediate search from', source, ':', value);
            clearTimeout(searchTimeout);
            filters.search = value;
            currentPage = 1;
            loadExperts(1);
        }

        // Desktop Search input
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            console.log('✓ Desktop search input found');
            
            // Multiple event types for better compatibility
            searchInput.addEventListener('input', function() { handleSearch(this, 'desktop-input'); }, false);
            searchInput.addEventListener('change', function() { handleSearchImmediate(this, 'desktop-change'); }, false);
            searchInput.addEventListener('keyup', function(e) {
                if (e.keyCode === 13 || e.which === 13 || e.key === 'Enter') {
                    handleSearchImmediate(this, 'desktop-enter');
                    e.preventDefault();
                }
            }, false);
        } else {
            console.error('✗ Desktop search input NOT found');
        }

        // Mobile Main Search input - Always visible on mobile
        const mobileMainSearchInput = document.getElementById('mobile-main-search-input');
        const mobileMainSearchIcon = document.getElementById('mobile-main-search-icon');
        
        if (mobileMainSearchInput) {
            console.log('✓ Mobile main search input found');
            
            // Create a simple search handler
            window.mobileSearchHandler = function() {
                var value = mobileMainSearchInput.value.trim();
                console.log('→ Mobile search triggered:', value);
                clearTimeout(searchTimeout);
                filters.search = value;
                currentPage = 1;
                loadExperts(1);
            };
            
            // Create a delayed search handler
            window.mobileSearchDelayed = function() {
                var value = mobileMainSearchInput.value.trim();
                console.log('→ Mobile search delayed:', value);
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    filters.search = value;
                    currentPage = 1;
                    loadExperts(1);
                }, 500);
            };
            
            // Try multiple binding methods for iOS compatibility
            
            // Method 1: Direct property assignment (most reliable on iOS)
            mobileMainSearchInput.oninput = function() {
                window.mobileSearchDelayed();
            };
            
            mobileMainSearchInput.onchange = function() {
                window.mobileSearchHandler();
            };
            
            mobileMainSearchInput.onsearch = function() {
                window.mobileSearchHandler();
            };
            
            // Method 2: addEventListener as backup
            try {
                mobileMainSearchInput.addEventListener('input', function() {
                    window.mobileSearchDelayed();
                }, false);
                
                mobileMainSearchInput.addEventListener('search', function() {
                    window.mobileSearchHandler();
                }, false);
            } catch(e) {
                console.log('addEventListener not supported:', e);
            }
            
            // Handle Enter key
            mobileMainSearchInput.onkeydown = function(e) {
                var key = e.keyCode || e.which;
                if (key === 13) {
                    console.log('→ Enter key pressed');
                    window.mobileSearchHandler();
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    return false;
                }
            };
            
            // Search Icon Handler
            if (mobileMainSearchIcon) {
                console.log('✓ Mobile main search icon found');
                
                // Create click handler
                window.mobileIconHandler = function(e) {
                    if (e) {
                        if (e.preventDefault) e.preventDefault();
                        if (e.stopPropagation) e.stopPropagation();
                    }
                    console.log('→ Search icon clicked');
                    window.mobileSearchHandler();
                    return false;
                };
                
                // Method 1: Direct property (most reliable on iOS)
                mobileMainSearchIcon.onclick = function(e) {
                    return window.mobileIconHandler(e);
                };
                
                // Method 2: Touch events for iOS
                mobileMainSearchIcon.ontouchstart = function(e) {
                    this.style.opacity = '0.5';
                };
                
                mobileMainSearchIcon.ontouchend = function(e) {
                    this.style.opacity = '1';
                    if (e.preventDefault) e.preventDefault();
                    if (e.stopPropagation) e.stopPropagation();
                    window.mobileSearchHandler();
                    return false;
                };
                
                // Method 3: addEventListener as backup
                try {
                    mobileMainSearchIcon.addEventListener('click', function(e) {
                        return window.mobileIconHandler(e);
                    }, false);
                } catch(e) {
                    console.log('addEventListener not supported for icon:', e);
                }
            }
        } else {
            console.error('✗ Mobile main search input NOT found');
        }

        // Mobile Search input in modal - DISABLED (using main search bar only)
        // Keeping the code commented for reference
        /*
        const mobileSearchInput = document.getElementById('mobile-search-input');
        if (mobileSearchInput) {
            console.log('✓ Mobile modal search input found (DISABLED)');
        }
        */

        // Desktop Price select
        const priceSelect = document.getElementById('price-select');
        if (priceSelect) {
            priceSelect.addEventListener('change', function() {
                handlePriceFilter(this.value);
            });
        }

        // Mobile Price select
        const mobilePriceSelect = document.getElementById('mobile-price-select');
        if (mobilePriceSelect) {
            mobilePriceSelect.addEventListener('change', function() {
                handlePriceFilter(this.value);
            });
        }

        // Desktop Rating select
        const ratingSelect = document.getElementById('rating-select');
        if (ratingSelect) {
            ratingSelect.addEventListener('change', function() {
                handleRatingFilter(this.value);
            });
        }

        // Mobile Rating select
        const mobileRatingSelect = document.getElementById('mobile-rating-select');
        if (mobileRatingSelect) {
            mobileRatingSelect.addEventListener('change', function() {
                handleRatingFilter(this.value);
            });
        }

        // Desktop Category select
        const categorySelect = document.getElementById('category-select');
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                filters.category = this.value === 'All Categories' ? '' : this.value;
                currentPage = 1;
                loadExperts(1);
            });
        }

        // Mobile Category select
        const mobileCategorySelect = document.getElementById('mobile-category-select');
        if (mobileCategorySelect) {
            mobileCategorySelect.addEventListener('change', function() {
                filters.category = this.value === 'All Categories' ? '' : this.value;
                currentPage = 1;
                loadExperts(1);
            });
        }

        // Sort by
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                filters.sortBy = this.value;
                currentPage = 1;
                loadExperts(1);
            });
        }
    });
})();
