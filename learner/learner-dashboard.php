<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Include session configuration and path setup
require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$page_title = "Dashboard - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 id="welcome-message" class="text-3xl font-bold text-white mb-2">Welcome back!</h1>
            <p class="text-gray-400">Here's what's happening with your learning journey</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-[#00D4AA]/10 text-[#00D4AA] rounded-xl">
                        <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="total-sessions" class="text-2xl font-extrabold text-white">0</p>
                        <p class="text-gray-400 text-sm">Total Sessions</p>
                    </div>
                </div>
            </div>
            <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="completed-sessions" class="text-2xl font-extrabold text-white">0</p>
                        <p class="text-gray-400 text-sm">Completed</p>
                    </div>
                </div>
            </div>
            <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-teal-500/10 text-[#00D4AA] rounded-xl">
                        <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="progress" class="text-2xl font-extrabold text-white">0%</p>
                        <p class="text-gray-400 text-sm">Progress</p>
                    </div>
                </div>
            </div>
            <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-500/10 text-indigo-400 rounded-xl">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="active-experts" class="text-2xl font-extrabold text-white">0</p>
                        <p class="text-gray-400 text-sm">Active Experts</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Upcoming Sessions -->
            <div class="lg:col-span-2 bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold text-white">Upcoming Sessions</h2>
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=my-sessions" class="text-[#00D4AA] hover:text-white text-sm font-medium transition">View All Sessions</a>
                </div>
                <div id="upcoming-sessions" class="space-y-4">
                    <p class="text-gray-400 text-center py-8">Loading sessions...</p>
                </div>
            </div>

            <!-- Quick Actions & Progress -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold text-white mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="block w-full bg-[#00D4AA] text-[#080B10] py-3 px-4 rounded-lg text-center hover:bg-[#00bda0] font-bold transition">
                            Find New Expert
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=booking" class="block w-full border border-gray-800 text-gray-300 py-3 px-4 rounded-lg text-center hover:bg-gray-800 transition">
                            Schedule Session
                        </a>
                        <a id="update-profile-link" href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=profile" class="block w-full border border-gray-800 text-gray-300 py-3 px-4 rounded-lg text-center hover:bg-gray-800 transition">
                            Update Profile
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold text-white mb-4">Recent Activity</h2>
                    <div id="recent-activity" class="space-y-3">
                        <p class="text-gray-400 text-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sessions -->
        <div class="mt-8 bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-white">Recent Sessions</h2>
                <a href="#" class="text-[#00D4AA] hover:text-white text-sm transition">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Expert</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Topic</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Date</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Duration</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Status</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody id="recent-sessions-table">
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">Loading sessions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>

<!-- Profile Completion Modal -->
<div id="profile-completion-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-[#131b2e] to-[#0e1322] border-b border-gray-850 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Complete Your Profile</h3>
                        <p class="text-gray-400 text-sm">Get personalized recommendations</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="mb-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#00D4AA]/10 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-white mb-1">Why complete your profile?</h4>
                        <ul class="text-sm text-gray-400 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-[#00D4AA] mt-0.5">✓</span>
                                <span>Get AI-powered expert recommendations based on your goals</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#00D4AA] mt-0.5">✓</span>
                                <span>Experts can understand your challenges and prepare better</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[#00D4AA] mt-0.5">✓</span>
                                <span>Receive personalized session insights and learning paths</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="bg-amber-950/20 border-l-4 border-amber-500 p-4 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-sm text-amber-200">
                            <strong>Missing Information:</strong> Please fill in your Learning Goals, Challenges, Education, and Profession to unlock these benefits.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="dismissProfileModal()" class="flex-1 px-4 py-3 border border-gray-800 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                    Later
                </button>
                <button onclick="goToProfile()" class="flex-1 px-4 py-3 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] hover:shadow-lg transition font-bold">
                    Complete Profile
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="review-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-[#131b2e] to-[#0e1322] border-b border-gray-850 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Rate Your Session</h3>
                        <p class="text-gray-400 text-sm">Share your experience</p>
                    </div>
                </div>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="flex items-center justify-center gap-3 mb-3">
                    <img id="review-expert-photo" src="" alt="" class="w-16 h-16 rounded-full object-cover border-2 border-gray-800">
                    <div class="text-left">
                        <p class="text-sm text-gray-400">How was your session with</p>
                        <h4 id="review-expert-name" class="text-lg font-bold text-white"></h4>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-3 text-center">Rating</label>
                <div class="flex justify-center gap-2" id="star-rating">
                    <button type="button" onclick="setRating(1)" class="text-5xl text-gray-600 hover:text-yellow-400 transition focus:outline-none">★</button>
                    <button type="button" onclick="setRating(2)" class="text-5xl text-gray-600 hover:text-yellow-400 transition focus:outline-none">★</button>
                    <button type="button" onclick="setRating(3)" class="text-5xl text-gray-600 hover:text-yellow-400 transition focus:outline-none">★</button>
                    <button type="button" onclick="setRating(4)" class="text-5xl text-gray-600 hover:text-yellow-400 transition focus:outline-none">★</button>
                    <button type="button" onclick="setRating(5)" class="text-5xl text-gray-600 hover:text-yellow-400 transition focus:outline-none">★</button>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Review (Optional)</label>
                <textarea id="review-text" rows="4" 
                    class="w-full px-4 py-2 bg-[#0e1322] border border-gray-850 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] resize-none"
                    placeholder="Share your experience with this expert..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeReviewModal()" class="flex-1 px-4 py-3 border border-gray-800 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                    Skip
                </button>
                <button onclick="submitReview()" id="submit-review-btn" class="flex-1 px-4 py-3 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] hover:shadow-lg transition font-bold">
                    Submit Review
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    console.log('Dashboard BASE_PATH detected as:', window.BASE_PATH);

    // Review Modal Variables
    let selectedRating = 0;
    let currentReviewBookingId = null;
    let currentReviewExpertId = null;

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

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

    document.addEventListener('DOMContentLoaded', async function() {
        try {
            console.log('Loading dashboard data from:', `${window.BASE_PATH}/admin-panel/apis/learner/dashboard.php`);
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/dashboard.php`);
            console.log('Dashboard response status:', response.status);
            console.log('Dashboard response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Dashboard result:', result);
            
            if (result.success) {
                const data = result.data;
                
                // Check if profile is incomplete (shows modal if needed)
                checkProfileCompletion(data.profile);

                // Check for pending review and show popup
                if (data.pending_review) {
                    showReviewModal(data.pending_review);
                }

                // Hide the "Update Profile" quick-action if profile is fully complete
                const updateLink = document.getElementById('update-profile-link');
                if (updateLink) {
                    const hasGoals = data.profile.goals && data.profile.goals.trim().length > 0;
                    const hasChallenges = data.profile.challenges && data.profile.challenges.trim().length > 0;
                    const hasEducation = data.profile.education && data.profile.education.trim().length > 0;
                    const hasProfession = data.profile.profession && data.profile.profession.trim().length > 0;
                    const profileComplete = hasGoals && hasChallenges && hasEducation && hasProfession;
                    updateLink.style.display = profileComplete ? 'none' : '';
                }

                // Update welcome message (using textContent for safety)
                document.getElementById('welcome-message').textContent = `Welcome back, ${data.profile.full_name}!`;
                
                // Update stats
                document.getElementById('total-sessions').textContent = data.stats.total_sessions;
                document.getElementById('completed-sessions').textContent = data.stats.completed_sessions;
                document.getElementById('progress').textContent = data.stats.progress + '%';
                document.getElementById('active-experts').textContent = data.stats.active_experts;
                
                // Render upcoming sessions
                const upcomingContainer = document.getElementById('upcoming-sessions');
                if (data.upcoming_sessions.length === 0) {
                    upcomingContainer.innerHTML = `<p class="text-gray-500 text-center py-8">No upcoming sessions. <a href="${window.BASE_PATH}/index.php?panel=learner&page=browse-experts" class="text-primary">Browse experts</a> to book a session.</p>`;
                } else {
                    upcomingContainer.innerHTML = data.upcoming_sessions.map(session => {
                        const sessionDate = new Date(session.session_datetime);
                        const formattedDate = sessionDate.toLocaleString('en-IN', {
                            timeZone: 'Asia/Kolkata',
                            month: 'short',
                            day: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                        // Determine if session is joinable (confirmed and within join window)
                        const now = new Date();
                        const startTime = sessionDate;
                        // Allow join 10 minutes before start
                        const joinOpenTime = new Date(startTime.getTime() - 10 * 60000);
                        const canJoin = session.status === 'confirmed' && now >= joinOpenTime;
                        const joinUrl = `${window.BASE_PATH}/learner/learner-session-execution.php?booking_id=${encodeURIComponent(session.id)}`;
                        
                        // Check status for cancel/reschedule buttons
                        const status = session.status.toLowerCase();
                        const isCompleted = status === 'completed';
                        const isCancelled = status === 'cancelled';
                        const isPending = status === 'pending';
                        const canShowReschedule = !isCompleted && !isCancelled && sessionDate > now;
                        
                        return `
                            <div class="border border-gray-800 rounded-xl p-4 bg-[#0d131f] hover:bg-[#131b2e] transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex">
                                        <img src="${escapeHtml(resolveImagePath(session.profile_photo))}" 
                                             alt="${escapeHtml(session.expert_name)}" 
                                             class="w-12 h-12 rounded-full mr-4 object-cover border border-gray-800">
                                        <div>
                                            <h3 class="font-semibold text-white">${escapeHtml(session.expert_name)}</h3>
                                            <p class="text-gray-400 text-sm">${escapeHtml(session.tagline || 'Expert Session')}</p>
                                            <div class="flex items-center mt-2 text-sm text-gray-400">
                                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                ${formattedDate}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="px-2.5 py-0.5 ${session.status.toLowerCase() === 'confirmed' ? 'bg-emerald-950/40 text-emerald-400 border border-emerald-800/40' : 'bg-yellow-950/40 text-yellow-400 border border-yellow-800/40'} text-xs rounded-full font-medium border">${escapeHtml(session.status)}</span>
                                        <div class="flex flex-wrap gap-2">
                                            ${canShowReschedule ? `
                                            <button onclick="openCancelModal(${session.id}, '${escapeHtml(session.expert_name).replace(/'/g, "\\'")}')" 
                                               class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-950/40 text-red-400 border border-red-900/30 hover:bg-red-900/20 transition">
                                                Cancel
                                            </button>
                                            ` : ''}
                                            ${canShowReschedule ? `
                                            <button onclick="openRescheduleModal(${session.id}, '${escapeHtml(session.expert_name).replace(/'/g, "\\'")}', ${session.expert_id})"
                                               class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-yellow-950/40 text-yellow-400 border border-yellow-900/30 hover:bg-yellow-900/20 transition">
                                                Reschedule
                                            </button>
                                            ` : ''}
                                            <a href="${window.BASE_PATH}/index.php?panel=learner&page=booking-details&booking_id=${session.id}" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-[#00D4AA] text-[#080B10] hover:bg-[#00bda0] inline-block transition shadow-md">Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                    // Attach event listeners for join buttons
                    document.querySelectorAll('.join-session-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const url = e.currentTarget.getAttribute('data-join-url');
                            if (!url || e.currentTarget.disabled) return;
                            window.location.href = url;
                        });
                    });
                }
                
                // Render recent activity
                const activityContainer = document.getElementById('recent-activity');
                if (data.recent_activity.length === 0) {
                    activityContainer.innerHTML = '<p class="text-gray-400 text-sm">No recent activity</p>';
                } else {
                    activityContainer.innerHTML = data.recent_activity.slice(0, 3).map(activity => {
                        const updatedDate = new Date(activity.updated_at);
                        const timeAgo = getTimeAgo(updatedDate);
                        const statusColors = {
                            'completed': 'bg-emerald-500',
                            'confirmed': 'bg-blue-500',
                            'pending': 'bg-yellow-500',
                            'cancelled': 'bg-red-500'
                        };
                        
                        return `
                            <div class="flex items-center">
                                <div class="w-2 h-2 ${statusColors[activity.status] || 'bg-gray-500'} rounded-full mr-3"></div>
                                <div class="text-sm">
                                    <p class="text-gray-200">${activity.status === 'completed' ? 'Completed' : 'Booked'} session with ${escapeHtml(activity.expert_name)}</p>
                                    <p class="text-gray-400">${timeAgo}</p>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
                
                // Render recent sessions table
                const tableBody = document.getElementById('recent-sessions-table');
                if (data.recent_sessions.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-gray-400">No completed sessions yet</td></tr>';
                } else {
                    tableBody.innerHTML = data.recent_sessions.map(session => {
                        const sessionDate = new Date(session.session_datetime);
                        const formattedDate = sessionDate.toLocaleDateString('en-IN', {
                            timeZone: 'Asia/Kolkata',
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        
                        return `
                            <tr class="border-b border-gray-800">
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <img src="${escapeHtml(resolveImagePath(session.profile_photo))}" 
                                             alt="${escapeHtml(session.expert_name)}" 
                                             class="w-8 h-8 rounded-full mr-3 object-cover border border-gray-800">
                                        <span class="text-white">${escapeHtml(session.expert_name)}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-300">${escapeHtml(session.tagline || 'Expert Session')}</td>
                                <td class="py-3 px-4 text-gray-300">${formattedDate}</td>
                                <td class="py-3 px-4 text-gray-300">${session.duration_minutes} min</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-emerald-950/40 text-emerald-400 text-xs rounded-full border border-emerald-800/40 font-medium">Completed</span>
                                </td>
                                <td class="py-3 px-4">
                                    <button class="text-[#00D4AA] hover:text-white text-sm transition">View Details</button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            } else {
                console.error('Error loading dashboard:', result.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60
        };
        
        for (const [unit, secondsInUnit] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / secondsInUnit);
            if (interval >= 1) {
                return `${interval} ${unit}${interval !== 1 ? 's' : ''} ago`;
            }
        }
        return 'just now';
    }
    
    // Profile completion check
    function checkProfileCompletion(profile) {
        // Check if essential fields are filled
        const hasGoals = profile.goals && profile.goals.trim().length > 0;
        const hasChallenges = profile.challenges && profile.challenges.trim().length > 0;
        const hasEducation = profile.education && profile.education.trim().length > 0;
        const hasProfession = profile.profession && profile.profession.trim().length > 0;

        const isProfileComplete = hasGoals && hasChallenges && hasEducation && hasProfession;

        // Respect per-tab dismissal: sessionStorage is scoped to the tab and cleared when tab/window closes
        const modalDismissed = sessionStorage.getItem('profile_modal_dismissed');

        // Show modal only if profile incomplete AND user hasn't dismissed it in this tab
        if (!isProfileComplete && !modalDismissed) {
            // Show modal after a short delay for better UX
            setTimeout(() => {
                document.getElementById('profile-completion-modal').classList.remove('hidden');
            }, 1000);
        }
    }

    function dismissProfileModal() {
        document.getElementById('profile-completion-modal').classList.add('hidden');
        // Remember dismissal for this tab only — sessionStorage is cleared when the tab/window is closed
        try {
            sessionStorage.setItem('profile_modal_dismissed', 'true');
        } catch (e) {
            // ignore (e.g., privacy mode blocking storage)
            console.warn('Could not persist modal dismissal in sessionStorage', e);
        }
    }
    
    function goToProfile() {
        window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=profile`;
    }

    // Review Modal Functions
    function showReviewModal(pendingReview) {
        currentReviewBookingId = pendingReview.booking_id;
        currentReviewExpertId = pendingReview.expert_id;
        
        // Set expert details
        document.getElementById('review-expert-name').textContent = pendingReview.expert_name;
        
        // Set expert photo
        const photoPath = pendingReview.expert_photo 
            ? resolveImagePath(pendingReview.expert_photo)
            : `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        document.getElementById('review-expert-photo').src = photoPath;
        
        // Reset form
        selectedRating = 0;
        document.getElementById('review-text').value = '';
        updateStarDisplay();
        
        // Show modal after a short delay for better UX
        setTimeout(() => {
            document.getElementById('review-modal').classList.remove('hidden');
        }, 1500);
    }

    function closeReviewModal() {
        document.getElementById('review-modal').classList.add('hidden');
        selectedRating = 0;
        currentReviewBookingId = null;
        currentReviewExpertId = null;
    }

    function setRating(rating) {
        selectedRating = rating;
        updateStarDisplay();
    }

    function updateStarDisplay() {
        const stars = document.querySelectorAll('#star-rating button');
        stars.forEach((star, index) => {
            if (index < selectedRating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.add('text-gray-300');
                star.classList.remove('text-yellow-400');
            }
        });
    }

    async function submitReview() {
        if (selectedRating === 0) {
            alert('Please select a rating (1-5 stars)');
            return;
        }

        const reviewText = document.getElementById('review-text').value.trim();
        const submitBtn = document.getElementById('submit-review-btn');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/reviews.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    action: 'submit_review',
                    booking_id: currentReviewBookingId,
                    rating: selectedRating,
                    review_text: reviewText
                })
            });

            const result = await response.json();

            if (result.success) {
                // Clear review pending flag
                await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/session-management.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'clear_review_flag',
                        booking_id: currentReviewBookingId
                    })
                });

                closeReviewModal();
                
                // Show success message
                alert('✅ Thank you for your review! Your feedback helps others find great experts.');
            } else {
                alert('Error: ' + (result.message || 'Failed to submit review'));
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            alert('An error occurred while submitting your review. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    // Cancel Modal Functions
    let currentCancelBookingId = null;
    let currentCancelExpertName = null;
    
    function openCancelModal(bookingId, expertName) {
        currentCancelBookingId = bookingId;
        currentCancelExpertName = expertName;
        document.getElementById('cancel-expert-name').textContent = expertName;
        document.getElementById('cancel-modal').classList.remove('hidden');
    }
    
    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
        currentCancelBookingId = null;
        currentCancelExpertName = null;
        document.getElementById('cancel-reason').value = '';
    }
    
    async function submitCancel() {
        const reason = document.getElementById('cancel-reason').value.trim();
        
        if (!reason) {
            alert('Please provide a reason for cancellation.');
            return;
        }
        
        const submitBtn = document.getElementById('cancel-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Cancelling...';
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    booking_id: currentCancelBookingId,
                    action: 'cancel',
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Session cancelled successfully!');
                closeCancelModal();
                location.reload(); // Reload dashboard
            } else {
                alert(result.message || 'Failed to cancel session.');
            }
        } catch (error) {
            console.error('Error cancelling session:', error);
            alert('Error cancelling session. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Cancel Session';
        }
    }

    // Reschedule Modal Functions
    let currentRescheduleBookingId = null;
    let currentExpertId = null;
    let expertAvailability = [];
    let expertBookedSlots = [];
    
    async function openRescheduleModal(bookingId, expertName, expertId) {
        if (!expertId) {
            alert('Expert ID not found. Please refresh the page and try again.');
            return;
        }
        
        currentRescheduleBookingId = bookingId;
        currentExpertId = expertId;
        document.getElementById('reschedule-expert-name').textContent = expertName;
        document.getElementById('reschedule-modal').classList.remove('hidden');
        
        // Show loading state
        document.getElementById('availability-loading').classList.remove('hidden');
        document.getElementById('availability-content').classList.add('hidden');
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`, {
                credentials: 'include'
            });
            
            const result = await response.json();
            
            if (result.success && result.data) {
                expertAvailability = result.data.availability || [];
                expertBookedSlots = result.data.booked_slots || [];
                generateAvailableDates();
            } else {
                alert('Failed to load expert availability');
            }
        } catch (error) {
            console.error('Error loading availability:', error);
            alert('Error loading expert availability');
        } finally {
            document.getElementById('availability-loading').classList.add('hidden');
            document.getElementById('availability-content').classList.remove('hidden');
        }
    }
    
    function generateAvailableDates() {
        const dateSelect = document.getElementById('reschedule-date-select');
        dateSelect.innerHTML = '<option value="">Select a date</option>';
        
        const availableDays = new Set();
        expertAvailability.forEach(slot => {
            availableDays.add(parseInt(slot.day_of_week));
        });
        
        const today = new Date();
        for (let i = 1; i < 90; i++) {
            const date = new Date(today.getTime() + (i * 24 * 60 * 60 * 1000));
            const dayNumber = date.getDay();
            
            if (availableDays.has(dayNumber)) {
                const dateStr = date.toISOString().split('T')[0];
                const formattedDate = date.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const option = document.createElement('option');
                option.value = dateStr;
                option.textContent = formattedDate;
                dateSelect.appendChild(option);
            }
        }
    }
    
    function onDateChange() {
        const selectedDate = document.getElementById('reschedule-date-select').value;
        const timeSelect = document.getElementById('reschedule-time-select');
        timeSelect.innerHTML = '<option value="">Select a time</option>';
        
        if (!selectedDate) return;
        
        const date = new Date(selectedDate + 'T00:00:00');
        const dayNumber = date.getDay();
        
        const daySlots = expertAvailability.filter(slot => 
            parseInt(slot.day_of_week) === dayNumber
        );
        
        const bookedTimes = expertBookedSlots
            .filter(slot => slot.session_datetime.startsWith(selectedDate))
            .map(slot => {
                const time = new Date(slot.session_datetime);
                return time.getHours() * 60 + time.getMinutes();
            });
        
        daySlots.forEach(slot => {
            const startParts = slot.start_time.split(':');
            const endParts = slot.end_time.split(':');
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            
            for (let time = startMinutes; time < endMinutes; time += 60) {
                const isBooked = bookedTimes.some(bookedTime => 
                    Math.abs(bookedTime - time) < 60
                );
                
                if (!isBooked) {
                    const hours = Math.floor(time / 60);
                    const minutes = time % 60;
                    const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    const displayTime = new Date(2000, 0, 1, hours, minutes).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    const option = document.createElement('option');
                    option.value = timeStr;
                    option.textContent = displayTime;
                    timeSelect.appendChild(option);
                }
            }
        });
        
        if (timeSelect.options.length === 1) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No available slots';
            option.disabled = true;
            timeSelect.appendChild(option);
        }
    }
    
    function closeRescheduleModal() {
        document.getElementById('reschedule-modal').classList.add('hidden');
        currentRescheduleBookingId = null;
        currentExpertId = null;
        document.getElementById('reschedule-date-select').value = '';
        document.getElementById('reschedule-time-select').innerHTML = '<option value="">Select a time</option>';
        document.getElementById('reschedule-reason').value = '';
    }
    
    async function submitReschedule() {
        const newDate = document.getElementById('reschedule-date-select').value;
        const newTime = document.getElementById('reschedule-time-select').value;
        const reason = document.getElementById('reschedule-reason').value;
        
        if (!newDate || !newTime) {
            alert('Please select both date and time for rescheduling.');
            return;
        }
        
        const submitBtn = document.getElementById('reschedule-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    booking_id: currentRescheduleBookingId,
                    action: 'reschedule',
                    new_date: newDate,
                    new_time: newTime,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Reschedule request submitted successfully!');
                closeRescheduleModal();
                location.reload();
            } else {
                alert(result.message || 'Failed to reschedule session.');
            }
        } catch (error) {
            console.error('Error rescheduling session:', error);
            alert('Error rescheduling session. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Reschedule Request';
        }
    }
</script>

<!-- Cancel Modal -->
<div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-[#131b2e] to-[#0e1322] border-b border-gray-850 p-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-950/40 border border-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Cancel Session</h3>
                    <p class="text-gray-400 text-sm">with <span id="cancel-expert-name" class="text-white font-semibold"></span></p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Reason for cancellation *</label>
                <textarea id="cancel-reason" rows="4" 
                    class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:ring-1 focus:ring-red-500 focus:border-red-500 resize-none"
                    placeholder="Please provide a reason for cancellation..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button onclick="closeCancelModal()" class="flex-1 px-4 py-3 border border-gray-800 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                    Keep Session
                </button>
                <button onclick="submitCancel()" id="cancel-submit-btn" class="flex-1 px-4 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-bold shadow-md">
                    Cancel Session
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-[#131b2e] to-[#0e1322] border-b border-gray-850 p-6 text-white">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Reschedule Session</h3>
                    <p class="text-gray-400 text-sm">with <span id="reschedule-expert-name" class="text-white font-semibold"></span></p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div id="availability-loading" class="hidden text-center py-8">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#00D4AA] mx-auto mb-4"></div>
                <p class="text-gray-400">Loading availability...</p>
            </div>
            
            <div id="availability-content">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select New Date *</label>
                    <select id="reschedule-date-select" onchange="onDateChange()" 
                        class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:border-gray-700">
                        <option value="">Select a date</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Select New Time *</label>
                    <select id="reschedule-time-select" 
                        class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:border-gray-700">
                        <option value="">Select a time</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Reason (Optional)</label>
                    <textarea id="reschedule-reason" rows="3" 
                        class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:border-gray-700 resize-none"
                        placeholder="Reason for rescheduling..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button onclick="closeRescheduleModal()" class="flex-1 px-4 py-3 border border-gray-800 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                        Cancel
                    </button>
                    <button onclick="submitReschedule()" id="reschedule-submit-btn" class="flex-1 px-4 py-3 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition font-bold">
                        Submit Reschedule Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
