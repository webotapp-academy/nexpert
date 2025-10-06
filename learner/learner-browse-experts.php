<?php
$page_title = "Browse Experts - Nexpert.ai";
$panel_type = "learner";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <!-- Header -->
        <div class="mb-4 sm:mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Browse Experts</h1>
            <p class="text-sm sm:text-base text-gray-600">Find the perfect expert to accelerate your learning journey</p>
        </div>

        <!-- Mobile Filter/Sort Icons -->
        <div class="flex md:hidden justify-between items-center mb-4 gap-3">
            <button id="mobile-filter-btn" class="flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition flex-1" style="min-height: 44px;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter
            </button>
            <button id="mobile-sort-btn" class="flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition flex-1" style="min-height: 44px;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
                </svg>
                Sort
            </button>
        </div>

        <!-- Filters - Top Bar for Desktop Only -->
        <div class="hidden md:block bg-white rounded-lg shadow-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search-input" placeholder="Search experts..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        <option>All Categories</option>
                        <option>Coaches</option>
                        <option>Mentors</option>
                        <option>Consultants</option>
                        <option>Trainers</option>
                        <option>Freelancers</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                    <select id="price-select" name="price" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        <option value="">All Prices</option>
                        <option value="under_500">Under ₹500</option>
                        <option value="500_1000">₹500 - ₹1,000</option>
                        <option value="1000_2000">₹1,000 - ₹2,000</option>
                        <option value="2000_plus">₹2,000+</option>
                    </select>
                </div>

                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Rating</label>
                    <select id="rating-select" name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        <option value="">All Ratings</option>
                        <option value="4plus">4+ Stars</option>
                        <option value="4.5plus">4.5+ Stars</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Sort Bar -->
        <div class="hidden md:flex justify-between items-center mb-6">
            <span id="result-count" class="text-sm sm:text-base text-gray-600">Loading experts...</span>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">Sort:</span>
                <select id="sort-select" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                    <option value="relevance">Relevance</option>
                    <option value="price_low_high">Price: Low to High</option>
                    <option value="price_high_low">Price: High to Low</option>
                    <option value="rating">Rating</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
        </div>

        <!-- Mobile Result Count -->
        <div class="md:hidden mb-4">
            <span id="result-count-mobile" class="text-sm text-gray-600">Loading experts...</span>
        </div>

        <!-- Experts Grid -->
        <div>
                <!-- AI Loading Indicator -->
                <div id="ai-loader" class="hidden">
                    <div class="flex flex-col items-center justify-center py-20">
                        <div class="relative">
                            <!-- Animated AI Brain/Circuit -->
                            <div class="relative w-32 h-32">
                                <!-- Outer rotating ring -->
                                <div class="absolute inset-0 border-4 border-blue-200 rounded-full animate-spin" style="border-top-color: #3B82F6;"></div>
                                
                                <!-- Inner pulsing circle -->
                                <div class="absolute inset-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full animate-pulse flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                                
                                <!-- Orbiting dots -->
                                <div class="absolute top-0 left-1/2 w-3 h-3 bg-blue-500 rounded-full -ml-1.5 animate-ping"></div>
                                <div class="absolute bottom-0 left-1/2 w-3 h-3 bg-purple-500 rounded-full -ml-1.5 animate-ping" style="animation-delay: 0.5s;"></div>
                            </div>
                        </div>
                        
                        <!-- Loading Text -->
                        <div class="mt-8 text-center">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">AI is finding your perfect experts</h3>
                            <p class="text-gray-600">Analyzing profiles and matching expertise...</p>
                            
                            <!-- Animated dots -->
                            <div class="flex justify-center items-center space-x-2 mt-4">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                            </div>
                        </div>
                        
                        <!-- Background Animation Grid -->
                        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-10">
                            <div class="absolute top-1/4 left-1/4 w-16 h-16 border-2 border-blue-500 rounded-lg animate-float"></div>
                            <div class="absolute top-1/3 right-1/4 w-12 h-12 border-2 border-purple-500 rounded-lg animate-float-delay"></div>
                            <div class="absolute bottom-1/3 left-1/3 w-14 h-14 border-2 border-indigo-500 rounded-lg animate-pulse-slow"></div>
                        </div>
                    </div>
                </div>

                <!-- Expert Cards Grid -->
                <div id="experts-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Experts will be loaded dynamically -->
                </div>

            <!-- Pagination -->
            <div class="flex justify-center mt-8">
                <nav class="flex space-x-2">
                    <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-2 text-white bg-primary border border-primary rounded-lg">1</button>
                    <button class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                    <button class="px-3 py-2 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>
                </nav>
            </div>
        </div>
    </div>

<!-- Mobile Filter Modal -->
<div id="filter-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="filter-modal-backdrop"></div>
        
        <div class="inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <button id="close-filter-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" id="mobile-search-input" placeholder="Search experts..." class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="mobile-category-select" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option>All Categories</option>
                            <option>Coaches</option>
                            <option>Mentors</option>
                            <option>Consultants</option>
                            <option>Trainers</option>
                            <option>Freelancers</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <select id="mobile-price-select" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Prices</option>
                            <option value="under_500">Under ₹500</option>
                            <option value="500_1000">₹500 - ₹1,000</option>
                            <option value="1000_2000">₹1,000 - ₹2,000</option>
                            <option value="2000_plus">₹2,000+</option>
                        </select>
                    </div>

                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Rating</label>
                        <select id="mobile-rating-select" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Ratings</option>
                            <option value="4plus">4+ Stars</option>
                            <option value="4.5plus">4.5+ Stars</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button id="clear-filters" class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition">
                        Clear All
                    </button>
                    <button id="apply-filters" class="flex-1 px-4 py-3 bg-primary text-white rounded-lg font-medium hover:bg-secondary transition">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sort Modal -->
<div id="sort-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="sort-modal-backdrop"></div>
        
        <div class="inline-block w-full max-w-lg overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sort By</h3>
                    <button id="close-sort-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-2">
                    <button class="mobile-sort-option w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition font-medium" data-value="relevance">
                        Relevance
                    </button>
                    <button class="mobile-sort-option w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition" data-value="price_low_high">
                        Price: Low to High
                    </button>
                    <button class="mobile-sort-option w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition" data-value="price_high_low">
                        Price: High to Low
                    </button>
                    <button class="mobile-sort-option w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition" data-value="rating">
                        Rating
                    </button>
                    <button class="mobile-sort-option w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition" data-value="newest">
                        Newest
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_PATH; ?>/admin-panel/js/learner-browse-experts.js"></script>
<script>
// Mobile Filter and Sort Modal Handlers
document.addEventListener('DOMContentLoaded', function() {
    const filterModal = document.getElementById('filter-modal');
    const sortModal = document.getElementById('sort-modal');
    const mobileFilterBtn = document.getElementById('mobile-filter-btn');
    const mobileSortBtn = document.getElementById('mobile-sort-btn');
    const closeFilterModal = document.getElementById('close-filter-modal');
    const closeSortModal = document.getElementById('close-sort-modal');
    const filterModalBackdrop = document.getElementById('filter-modal-backdrop');
    const sortModalBackdrop = document.getElementById('sort-modal-backdrop');

    // Open filter modal
    if (mobileFilterBtn) {
        mobileFilterBtn.addEventListener('click', () => {
            filterModal.classList.remove('hidden');
        });
    }

    // Open sort modal
    if (mobileSortBtn) {
        mobileSortBtn.addEventListener('click', () => {
            sortModal.classList.remove('hidden');
        });
    }

    // Close filter modal
    if (closeFilterModal) {
        closeFilterModal.addEventListener('click', () => {
            filterModal.classList.add('hidden');
        });
    }

    if (filterModalBackdrop) {
        filterModalBackdrop.addEventListener('click', () => {
            filterModal.classList.add('hidden');
        });
    }

    // Close sort modal
    if (closeSortModal) {
        closeSortModal.addEventListener('click', () => {
            sortModal.classList.add('hidden');
        });
    }

    if (sortModalBackdrop) {
        sortModalBackdrop.addEventListener('click', () => {
            sortModal.classList.add('hidden');
        });
    }

    // Apply filters
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            filterModal.classList.add('hidden');
        });
    }

    // Clear filters
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            document.getElementById('mobile-search-input').value = '';
            document.getElementById('mobile-category-select').selectedIndex = 0;
            document.getElementById('mobile-price-select').selectedIndex = 0;
            document.getElementById('mobile-rating-select').selectedIndex = 0;
        });
    }

    // Mobile sort options
    const sortOptions = document.querySelectorAll('.mobile-sort-option');
    sortOptions.forEach(option => {
        option.addEventListener('click', () => {
            const value = option.getAttribute('data-value');
            const desktopSortSelect = document.getElementById('sort-select');
            if (desktopSortSelect) {
                desktopSortSelect.value = value;
                desktopSortSelect.dispatchEvent(new Event('change'));
            }
            sortModal.classList.add('hidden');
        });
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
