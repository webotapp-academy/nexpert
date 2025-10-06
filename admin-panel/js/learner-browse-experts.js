(function() {
    'use strict';

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
            const response = await fetch('/admin-panel/apis/learner/browse-experts.php?' + params.toString());
            const result = await response.json();

            if (result.success) {
                experts = result.data;
                currentPage = result.page;
                totalPages = result.totalPages;
                renderExperts();
                updateResultCount(result.total);
                renderPagination();
            } else {
                console.error('Error loading experts:', result.message);
                document.getElementById('experts-grid').innerHTML = '<div class="col-span-full text-center py-12"><p class="text-gray-500">Error loading experts. Please try again.</p></div>';
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

    // Render experts
    function renderExperts() {
        const container = document.getElementById('experts-grid');
        if (!container) return;

        if (experts.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No experts found. Try adjusting your filters.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = experts.map(expert => `
            <div class="bg-white rounded-lg shadow-xl overflow-hidden hover:shadow-2xl transition duration-300 border border-gray-100">
                <div class="relative">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 pb-3">
                        <div class="flex items-center">
                            <div class="relative rounded-full overflow-hidden border-4 border-white shadow-lg" style="width: 64px; height: 64px;">
                                <img src="${escapeHtml(expert.profile_photo || 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg')}" 
                                     alt="${escapeHtml(expert.name)}" 
                                     class="w-full h-full object-cover">
                                <div class="absolute -bottom-1 -right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="font-bold text-lg text-gray-900">${escapeHtml(expert.name)}</h3>
                                <p class="text-blue-600 font-medium text-sm">${escapeHtml(expert.professional_title || 'Expert')}</p>
                                <div class="flex items-center mt-1">
                                    <div class="flex text-yellow-400 text-sm">
                                        ${'★'.repeat(Math.floor(expert.avg_rating))}${'☆'.repeat(5 - Math.floor(expert.avg_rating))}
                                    </div>
                                    <span class="text-gray-600 text-xs ml-2">(${escapeHtml(String(expert.avg_rating))}) • ${escapeHtml(String(expert.review_count))} reviews</span>
                                </div>
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">${escapeHtml(expert.badge)}</span>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <p class="text-gray-700 text-sm mb-4 leading-relaxed line-clamp-3">${escapeHtml(expert.bio || 'Experienced professional ready to help you achieve your goals.')}</p>
                        <div class="flex flex-wrap gap-2 mb-5">
                            ${expert.skills.slice(0, 3).map(skill => `
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">${escapeHtml(skill)}</span>
                            `).join('')}
                            ${expert.skills.length > 3 ? `<span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">+${expert.skills.length - 3} more</span>` : ''}
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-bold text-gray-900">₹${escapeHtml(String(expert.hourly_rate || 0))}</span>
                                <span class="text-gray-600 text-sm">/hour</span>
                            </div>
                            <a href="?panel=learner&page=expert-profile&expert_id=${encodeURIComponent(expert.id)}" 
                               class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 text-sm font-medium shadow-md hover:shadow-lg">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
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
                    class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}">
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
            paginationHTML += `<button onclick="changePage(1)" class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">1</button>`;
            if (startPage > 2) {
                paginationHTML += `<span class="px-3 py-2">...</span>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button onclick="changePage(${i})" 
                        class="px-3 py-2 ${i === currentPage ? 'text-white bg-primary border-primary' : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'} border rounded-lg">
                    ${i}
                </button>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<span class="px-3 py-2">...</span>`;
            }
            paginationHTML += `<button onclick="changePage(${totalPages})" class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">${totalPages}</button>`;
        }

        // Next button
        paginationHTML += `
            <button onclick="changePage(${currentPage + 1})" 
                    ${currentPage === totalPages ? 'disabled' : ''} 
                    class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}">
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
            if (value === '4plus') {
                filters.minRating = 4;
            } else if (value === '4.5plus') {
                filters.minRating = 4.5;
            } else {
                filters.minRating = null;
            }
            currentPage = 1;
            loadExperts(1);
        }

        // Desktop Search input (with debounce)
        const searchInput = document.getElementById('search-input');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filters.search = this.value;
                    currentPage = 1;
                    loadExperts(1);
                }, 500);
            });
        }

        // Mobile Search input (with debounce)
        const mobileSearchInput = document.getElementById('mobile-search-input');
        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filters.search = this.value;
                    currentPage = 1;
                    loadExperts(1);
                }, 500);
            });
        }

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
