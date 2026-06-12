<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

$page_title = "Notifications - Nexpert.ai";
$panel_type = "learner";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Notifications</h1>
                <p class="text-gray-400 text-sm">Stay updated with your activities</p>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition text-sm font-bold shadow-md">
                    Mark all as read
                </button>
                <button onclick="document.getElementById('settingsModal').classList.remove('hidden')" class="px-4 py-2 border border-gray-850 text-gray-400 rounded-lg hover:bg-gray-800 transition text-sm font-medium">
                    Settings
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-800">
                <nav class="flex space-x-8">
                    <button class="py-2 px-1 border-b-2 border-[#00D4AA] text-[#00D4AA] font-bold text-sm sm:text-base">All</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-400 hover:text-white transition text-sm sm:text-base">Unread</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-400 hover:text-white transition text-sm sm:text-base">Sessions</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-400 hover:text-white transition text-sm sm:text-base">Payments</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-400 hover:text-white transition text-sm sm:text-base">Updates</button>
                </nav>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
            <!-- Session Reminder - Unread -->
            <div class="bg-[#131b2e] border-l-4 border-[#00D4AA] border border-gray-800/80 p-4 rounded-r-lg">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-[#00D4AA]/10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-white">Session Reminder</h3>
                                <span class="px-2 py-0.5 bg-[#00D4AA] text-[#080B10] text-[10px] font-bold rounded-full uppercase tracking-wider">New</span>
                            </div>
                            <p class="text-sm text-gray-300 mt-1">
                                Your session with <strong>Sarah Chen</strong> starts in 1 hour. 
                                <a href="?panel=learner&page=dashboard" class="text-[#00D4AA] hover:text-white hover:underline ml-1">Join now</a>
                            </p>
                            <p class="text-xs text-gray-500 mt-2">2 minutes ago</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Assignment Upload -->
            <div class="bg-[#131b2e] border border-gray-850 p-4 rounded-lg hover:border-gray-800 hover:bg-[#0e1322]/40 transition">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-white">New Assignment</h3>
                            <p class="text-sm text-gray-300 mt-1">
                                <strong>Marcus Johnson</strong> uploaded a new assignment: "React Component Refactoring Exercise"
                            </p>
                            <p class="text-xs text-gray-500 mt-2">1 hour ago</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Payment Confirmation -->
            <div class="bg-[#131b2e] border border-gray-850 p-4 rounded-lg hover:border-gray-800 hover:bg-[#0e1322]/40 transition">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-[#00D4AA]/10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-white">Payment Confirmed</h3>
                            <p class="text-sm text-gray-300 mt-1">
                                Your payment of ₹75.00 for the session with <strong>Sarah Chen</strong> has been processed successfully.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">3 hours ago</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expert Message -->
            <div class="bg-[#131b2e] border border-gray-850 p-4 rounded-lg hover:border-gray-800 hover:bg-[#0e1322]/40 transition">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <img src="attached_assets/stock_images/diverse_professional_4d71624f.jpg" alt="Elena Rodriguez" class="w-10 h-10 rounded-full object-cover border border-gray-800">
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-white">Message from Expert</h3>
                            <p class="text-sm text-gray-300 mt-1">
                                <strong>Elena Rodriguez</strong>: "Great work on the marketing strategy! I've uploaded additional resources for you to review."
                            </p>
                            <p class="text-xs text-gray-500 mt-2">Yesterday, 4:30 PM</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Session Completed -->
            <div class="bg-[#131b2e] border border-gray-850 p-4 rounded-lg hover:border-gray-800 hover:bg-[#0e1322]/40 transition opacity-75">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-white">Session Completed</h3>
                            <p class="text-sm text-gray-400 mt-1">
                                Your session with <strong>Elena Rodriguez</strong> has been completed. Session recording and notes are now available.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">2 days ago</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="bg-[#131b2e] border border-gray-850 p-4 rounded-lg hover:border-gray-800 hover:bg-[#0e1322]/40 transition opacity-75">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-500/10 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-white">Welcome to Nexpert.ai!</h3>
                            <p class="text-sm text-gray-400 mt-1">
                                Thank you for joining Nexpert.ai! Explore our expert network and book your first session to accelerate your learning journey.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">1 week ago</p>
                        </div>
                    </div>
                    <button class="text-gray-500 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State (Hidden when notifications present) -->
        <div class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-[#131b2e] border border-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-3a1 1 0 011-1h3a1 1 0 011 1v3z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No notifications</h3>
            <p class="text-gray-400">You're all caught up! New notifications will appear here.</p>
        </div>

        <!-- Load More -->
        <div class="text-center mt-8">
            <button class="px-6 py-2 border border-gray-850 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium text-sm">
                Load More Notifications
            </button>
        </div>
    </div>

    <!-- Notification Settings Modal (Hidden by default) -->
    <div class="fixed inset-0 bg-black bg-opacity-60 hidden z-50 animate-fadeIn" id="settingsModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-850">
                    <h3 class="text-lg font-bold text-white">Notification Settings</h3>
                    <button onclick="document.getElementById('settingsModal').classList.add('hidden')" class="text-gray-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Email notifications</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-[#080B10] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-800 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Push notifications</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-[#080B10] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-800 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Session reminders</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-[#080B10] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-800 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Marketing emails</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-[#080B10] after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-800 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="document.getElementById('settingsModal').classList.add('hidden')" class="px-4 py-2 text-gray-400 border border-gray-850 rounded-lg hover:bg-gray-800 transition text-sm font-medium">Cancel</button>
                    <button onclick="document.getElementById('settingsModal').classList.add('hidden')" class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition text-sm font-bold shadow-md">Save</button>
                </div>
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
