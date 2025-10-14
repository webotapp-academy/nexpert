<?php
$page_title = "Expert Profile - Nexpert.ai";
$panel_type = "learner";

// For online deployment, set BASE_PATH to empty for root directory
$BASE_PATH = '';

require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<!-- Expert Profile Hero Section (styled to match reference) -->
<div class="pt-12 md:pt-16 pb-6 md:pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg ring-1 ring-gray-100 overflow-hidden px-6 py-8 md:px-8 md:py-10 relative">
        <!-- Decorative background shapes -->
        <div class="pointer-events-none absolute -top-16 -right-20 w-72 h-72 bg-gradient-to-br from-primary/10 via-pink-200/20 to-yellow-200/20 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-24 w-80 h-80 bg-gradient-to-tr from-blue-200/20 via-purple-200/20 to-primary/10 rounded-full blur-3xl"></div>
        <div class="flex flex-col md:flex-row items-center md:items-center gap-10 md:gap-12 relative">
            <!-- Expert Photo + Availability + Location under photo -->
            <div class="flex flex-col items-center md:items-start">
                <div class="relative group">
                <div class="pointer-events-none absolute -inset-3 rounded-full bg-gradient-to-br from-yellow-100/60 via-pink-100/40 to-blue-100/50 blur-xl -z-10"></div>
                <div id="expert-photo" class="relative w-40 h-40 md:w-48 md:h-48 rounded-full bg-white shadow-2xl overflow-hidden flex items-center justify-center ring-4 ring-white">
                    <div class="animate-pulse">
                        <svg class="w-20 h-20 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <!-- Looking for Work pill -->
                <div class="absolute left-1/2 -translate-x-1/2 -bottom-6 md:left-0 md:translate-x-0 md:bottom-2 md:-left-3">
                    <span class="inline-flex items-center gap-2 bg-green-500 text-white text-sm font-semibold px-4 py-2 rounded-full shadow-lg">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span id="expert-availability-text">Looking for Work</span>
                    </span>
                </div>
                </div>
                <!-- Location under profile image (centered under avatar) -->
                <div class="mt-8 self-center flex items-center justify-center text-gray-700 gap-2 text-sm bg-gray-100 rounded-full px-3 py-1.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <span id="expert-location">Chicago, IL</span>
                </div>
            </div>

            <!-- Expert Info -->
            <div class="flex-1 w-full text-center md:text-left">
                <!-- Name + small badge -->
                <div class="flex items-center justify-center md:justify-start gap-3">
                    <h1 id="expert-name" class="text-4xl md:text-5xl font-bold text-gray-900 relative">
                        <span class="pointer-events-none absolute -bottom-2 left-0 right-0 mx-auto md:mx-0 h-1 bg-gradient-to-r from-yellow-300 via-pink-300 to-purple-300 rounded-full opacity-70"></span>
                        <span class="inline-block animate-pulse bg-gray-200 rounded px-4 py-2">Loading...</span>
                    </h1>
                    <!-- Yellow star-like badge -->
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ring-2 ring-yellow-300 bg-yellow-100">
                        <svg class="w-4 h-4 text-yellow-700" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.97 0 1.372 1.24.589 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </span>
                </div>

                <!-- Rating + Reviews (restored) -->
                <div class="mt-2 flex items-center justify-center md:justify-start gap-3 text-sm">
                    <div id="expert-rating-stars" class="flex text-yellow-400 text-lg">☆☆☆☆☆</div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <span id="expert-rating-value" class="font-semibold text-gray-900">(0.0)</span>
                        <span id="expert-review-count">• 0 reviews</span>
                    </div>
                </div>

                

                <!-- Role & Experience (restored: Role left, Experience right) -->
                <div class="mt-6 grid grid-cols-2 gap-2 max-w-xl mx-auto md:mx-0">
                    <div>
                        <div class="text-gray-500 text-xs uppercase tracking-wide">Role</div>
                        <div id="expert-title" class="text-2xl font-semibold text-gray-900">
                            <span class="inline-block animate-pulse bg-gray-200 rounded px-3 py-1">Loading...</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-xs uppercase tracking-wide">Experience</div>
                        <div id="expert-experience-header" class="text-2xl font-semibold text-gray-900">0 years</div>
                    </div>
                </div>

                

                <!-- Skills Tags -->
                <div class="mt-6">
                    <div class="text-gray-500 text-sm mb-2">Superpower Skills</div>
                    <div id="expert-skills" class="flex flex-wrap gap-3 justify-center md:justify-start bg-gray-50 rounded-xl p-3">
                        <!-- Skills loaded dynamically; styled as soft pills by JS or server -->
                    </div>
                </div>

                <!-- Actions moved below skills -->
                <div class="mt-6 pt-6 border-t border-gray-100 flex items-center justify-center md:justify-start gap-3">
                    <!-- Hourly rate should appear before Book Session -->
                    <div class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-900 px-4 py-2 rounded-full font-semibold ring-1 ring-gray-200">
                        <span id="expert-hourly-rate">₹0</span>
                        <span class="text-gray-600 text-sm">/hr</span>
                    </div>
                    <a id="book-session-btn" href="?panel=learner&page=booking" class="bg-black text-white px-5 py-2.5 rounded-full font-semibold flex items-center gap-2 hover:opacity-90 shadow-md hover:shadow-lg active:translate-y-px transition transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Book Session
                    </a>
                    <button id="hero-message-btn" class="ring-1 ring-gray-300 text-gray-900 px-5 py-2.5 rounded-full font-semibold flex items-center gap-2 hover:bg-gray-900 hover:text-white hover:ring-gray-400 shadow-sm hover:shadow-md active:translate-y-px transition transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Send Message
                    </button>
                </div>

                
            </div>
            <!-- Vertical divider on md+ -->
            <div class="hidden md:block w-px h-40 bg-gradient-to-b from-transparent via-gray-200 to-transparent"></div>
        </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 md:pt-6 pb-12">
    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-8">
                <button class="py-4 px-2 border-b-2 border-primary text-primary font-semibold transition-colors">
                    About
                </button>
                <button class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition-colors">
                    Experience
                </button>
                <button class="py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium transition-colors">
                    Reviews
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-8 lg:p-12">
            <div class="grid lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Bio Section -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">About Expert</h2>
                        <div id="expert-bio" class="text-gray-700 leading-relaxed whitespace-pre-line">
                            <div class="space-y-3">
                                <div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div>
                                <div class="h-4 bg-gray-200 rounded animate-pulse w-5/6"></div>
                                <div class="h-4 bg-gray-200 rounded animate-pulse w-4/6"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-3 gap-6">
                        <div class="bg-blue-50 rounded-xl p-6 text-center hover:shadow-lg transition">
                            <div class="mb-2">
                                <svg class="w-10 h-10 mx-auto text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                            </div>
                            <div id="expert-total-sessions" class="text-3xl font-bold text-primary mb-1">0</div>
                            <div class="text-gray-600 text-sm font-medium">Sessions</div>
                        </div>
                        <div class="bg-green-50 rounded-xl p-6 text-center hover:shadow-lg transition">
                            <div class="mb-2">
                                <svg class="w-10 h-10 mx-auto text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div id="expert-experience-years" class="text-3xl font-bold text-green-600 mb-1">0</div>
                            <div class="text-gray-600 text-sm font-medium">Years Exp</div>
                        </div>
                        <div class="bg-yellow-50 rounded-xl p-6 text-center hover:shadow-lg transition">
                            <div class="mb-2">
                                <svg class="w-10 h-10 mx-auto text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </div>
                            <div id="expert-total-reviews" class="text-3xl font-bold text-yellow-600 mb-1">0</div>
                            <div class="text-gray-600 text-sm font-medium">Reviews</div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Verification Card -->
                    <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Verification Status</h3>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 bg-green-500 rounded-full p-2">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 mb-1">Verified Expert</div>
                                <div class="text-sm text-gray-600">Identity & credentials verified by Nexpert.ai</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a id="sidebar-book-btn" href="?panel=learner&page=booking" class="block w-full bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition text-center font-semibold">
                                Book Session
                            </a>
                            <button class="w-full border-2 border-primary text-primary px-6 py-3 rounded-lg hover:bg-primary hover:text-white transition text-center font-semibold">
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Use window.BASE_PATH to avoid redeclaration
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';
    console.log('Expert Profile BASE_PATH detected as:', window.BASE_PATH);
</script>
<script src="<?php echo $BASE_PATH; ?>/admin-panel/js/learner-expert-profile.js"></script>
<?php require_once 'includes/footer.php'; ?>
