<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Notifications - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
            </div>
            <div class="flex space-x-3">
                <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                    Mark All Read
                </button>
                <button class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                    Settings
                </button>
            </div>
        </div>

        <!-- Notification Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex flex-wrap gap-4">
                <button class="px-4 py-2 bg-accent text-white rounded-lg text-sm">All (12)</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">Booking Requests (5)</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">Messages (3)</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">Payments (2)</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">System (2)</button>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Notifications List -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Unread Notification 1 -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-blue-500 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">New Booking Request</h3>
                                <p class="text-gray-700 mb-2">Aarav Patel has requested a UX Design Consultation session for Sep 28, 2:00 PM IST. Package includes portfolio review and career guidance.</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    2 hours ago
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">Approve</button>
                            <button class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition">Decline</button>
                        </div>
                    </div>
                </div>

                <!-- Unread Notification 2 -->
                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-green-500 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Payment Received</h3>
                                <p class="text-gray-700 mb-2">Payment of ₹1,500 received from Sneha Gupta for Career Transition Coaching session scheduled on Sep 29.</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    3 hours ago
                                </div>
                            </div>
                        </div>
                        <button class="text-green-600 hover:text-green-700 text-sm">View Details</button>
                    </div>
                </div>

                <!-- Unread Notification 3 -->
                <div class="bg-purple-50 border-l-4 border-purple-500 rounded-lg p-6 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-purple-500 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">New Message</h3>
                                <p class="text-gray-700 mb-2">Rahul Sharma sent you a message: "Thank you for the portfolio review session! Could you share the design resources you mentioned?"</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    4 hours ago
                                </div>
                            </div>
                        </div>
                        <button class="text-purple-600 hover:text-purple-700 text-sm">Reply</button>
                    </div>
                </div>

                <!-- Read Notification 1 -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm opacity-75">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-gray-400 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Session Rated</h3>
                                <p class="text-gray-700 mb-2">Ananya Singh rated your UX Research Workshop session 5 stars with review: "Excellent session! Learned so much about user research methodologies."</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Yesterday
                                </div>
                            </div>
                        </div>
                        <button class="text-gray-400 text-sm">Read</button>
                    </div>
                </div>

                <!-- Read Notification 2 -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm opacity-75">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-gray-400 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Assignment Submitted</h3>
                                <p class="text-gray-700 mb-2">Vikram Kumar submitted his Design Systems assignment for review. The assignment includes component library documentation and usage guidelines.</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Yesterday
                                </div>
                            </div>
                        </div>
                        <button class="text-gray-400 text-sm">Read</button>
                    </div>
                </div>

                <!-- Read Notification 3 -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm opacity-75">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="p-2 bg-gray-400 rounded-full mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Payout Processed</h3>
                                <p class="text-gray-700 mb-2">Your monthly payout of ₹42,500 has been processed and will be credited to your account ending in 1234 within 2-3 business days.</p>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    2 days ago
                                </div>
                            </div>
                        </div>
                        <button class="text-gray-400 text-sm">Read</button>
                    </div>
                </div>

                <!-- Load More -->
                <div class="text-center py-6">
                    <button class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                        Load More Notifications
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">New Bookings</span>
                            <span class="font-semibold text-blue-600">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Messages</span>
                            <span class="font-semibold text-purple-600">5</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Completed Sessions</span>
                            <span class="font-semibold text-green-600">2</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Earnings Today</span>
                            <span class="font-semibold text-accent">₹3,000</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Sessions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Sessions</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center mb-2">
                                <img src="https://via.placeholder.com/32x32" alt="Aarav" class="w-8 h-8 rounded-full mr-2 session-profile-photo">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Aarav Patel</p>
                                    <p class="text-gray-600 text-xs">UX Consultation</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                Today, 2:00 PM - 3:00 PM IST
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center mb-2">
                                <img src="https://via.placeholder.com/32x32" alt="Sneha" class="w-8 h-8 rounded-full mr-2 session-profile-photo">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Sneha Gupta</p>
                                    <p class="text-gray-600 text-xs">Career Coaching</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                Tomorrow, 10:00 AM - 11:30 AM IST
                            </div>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center mb-2">
                                <img src="https://via.placeholder.com/32x32" alt="Ananya" class="w-8 h-8 rounded-full mr-2 session-profile-photo">
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">Ananya Singh</p>
                                    <p class="text-gray-600 text-xs">Research Workshop</p>
                                </div>
                            </div>
                            <div class="text-xs text-gray-600">
                                Oct 5, 11:00 AM - 12:30 PM IST
                            </div>
                        </div>
                    </div>
                    
                    <button class="w-full mt-4 text-accent hover:text-yellow-600 text-sm font-medium">
                        View All Sessions →
                    </button>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📧 Send Message to Learner
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📅 Block Time Slot
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📝 Create Assignment
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            ⚙️ Update Profile
                        </button>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">Email notifications</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">Push notifications</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">SMS notifications</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">Daily summary</span>
                        </div>
                    </div>
                    
                    <button class="w-full mt-4 bg-accent text-white py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                        Update Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        // If it's a full URL or a data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        
        // If no image path, use a default
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        
        // Remove leading slashes
        const normalizedPath = imagePath.replace(/^\/+/, '');
        
        // Construct full path
        return `${window.BASE_PATH}/${normalizedPath}`;
    }

    // Update placeholder images in upcoming sessions
    document.querySelectorAll('.session-profile-photo').forEach(img => {
        const originalSrc = img.getAttribute('src');
        img.setAttribute('src', resolveImagePath(originalSrc));
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
