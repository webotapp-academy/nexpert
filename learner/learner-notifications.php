<?php
$page_title = "Notifications - Nexpert.ai";
$panel_type = "learner";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
                </button>
                <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    Settings
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button class="py-2 px-1 border-b-2 border-primary text-primary font-medium">All</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700">Unread</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700">Sessions</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700">Payments</button>
                    <button class="py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700">Updates</button>
                </nav>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
            <!-- Session Reminder - Unread -->
            <div class="bg-blue-50 border-l-4 border-primary p-4 rounded-lg">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center">
                                <h3 class="text-sm font-semibold text-gray-900">Session Reminder</h3>
                                <span class="ml-2 px-2 py-0.5 bg-primary text-white text-xs rounded-full">New</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1">
                                Your session with <strong>Sarah Chen</strong> starts in 1 hour. 
                                <a href="?panel=learner&page=dashboard" class="text-primary hover:text-secondary">Join now</a>
                            </p>
                            <p class="text-xs text-gray-500 mt-2">2 minutes ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Assignment Upload -->
            <div class="bg-white border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">New Assignment</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                <strong>Marcus Johnson</strong> uploaded a new assignment: "React Component Refactoring Exercise"
                            </p>
                            <p class="text-xs text-gray-500 mt-2">1 hour ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Payment Confirmation -->
            <div class="bg-white border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-accent rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">Payment Confirmed</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                Your payment of $75.00 for the session with <strong>Sarah Chen</strong> has been processed successfully.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">3 hours ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expert Message -->
            <div class="bg-white border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <img src="attached_assets/stock_images/diverse_professional_4d71624f.jpg" alt="Elena Rodriguez" class="w-10 h-10 rounded-full object-cover">
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">Message from Expert</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                <strong>Elena Rodriguez</strong>: "Great work on the marketing strategy! I've uploaded additional resources for you to review."
                            </p>
                            <p class="text-xs text-gray-500 mt-2">Yesterday, 4:30 PM</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Session Completed -->
            <div class="bg-white border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition opacity-75">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">Session Completed</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                Your session with <strong>Elena Rodriguez</strong> has been completed. Session recording and notes are now available.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">2 days ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="bg-white border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition opacity-75">
                <div class="flex justify-between items-start">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-sm font-semibold text-gray-900">Welcome to Nexpert.ai!</h3>
                            <p class="text-sm text-gray-700 mt-1">
                                Thank you for joining Nexpert.ai! Explore our expert network and book your first session to accelerate your learning journey.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">1 week ago</p>
                        </div>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State (Hidden when notifications present) -->
        <div class="text-center py-12 hidden">
            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-3a1 1 0 011-1h3a1 1 0 011 1v3z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
            <p class="text-gray-600">You're all caught up! New notifications will appear here.</p>
        </div>

        <!-- Load More -->
        <div class="text-center mt-8">
            <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Load More Notifications
            </button>
        </div>
    </div>

    <!-- Notification Settings Modal (Hidden by default) -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50" id="settingsModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Email notifications</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Push notifications</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Session reminders</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Marketing emails</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                    <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition">Save</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
