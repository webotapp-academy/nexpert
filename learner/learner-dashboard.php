<?php
// Include session configuration and path setup
require_once dirname(__DIR__) . '/includes/session-config.php';

// For backward compatibility keep a local variable used in the markup
// but ensure it always points to application root (not current directory)
$BASE_PATH = BASE_PATH; // e.g. /nexpert

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$page_title = "Dashboard - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 id="welcome-message" class="text-3xl font-bold text-gray-900 mb-2">Welcome back!</h1>
            <p class="text-gray-600">Here's what's happening with your learning journey</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-primary rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="total-sessions" class="text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-gray-600 text-sm">Total Sessions</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="completed-sessions" class="text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-gray-600 text-sm">Completed</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-accent rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="progress" class="text-2xl font-semibold text-gray-900">0%</p>
                        <p class="text-gray-600 text-sm">Progress</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p id="active-experts" class="text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-gray-600 text-sm">Active Experts</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Upcoming Sessions -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">Upcoming Sessions</h2>
                    <a href="<?php echo $BASE_PATH; ?>/index.php?panel=learner&page=booking" class="text-primary hover:text-secondary text-sm">View All</a>
                </div>
                <div id="upcoming-sessions" class="space-y-4">
                    <p class="text-gray-500 text-center py-8">Loading sessions...</p>
                </div>
            </div>

            <!-- Quick Actions & Progress -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="block w-full bg-primary text-white py-3 px-4 rounded-lg text-center hover:bg-secondary transition">
                            Find New Expert
                        </a>
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=learner&page=booking" class="block w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg text-center hover:bg-gray-50 transition">
                            Schedule Session
                        </a>
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=learner&page=profile" class="block w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg text-center hover:bg-gray-50 transition">
                            Update Profile
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
                    <div id="recent-activity" class="space-y-3">
                        <p class="text-gray-500 text-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sessions -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Recent Sessions</h2>
                <a href="#" class="text-primary hover:text-secondary text-sm">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Expert</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Topic</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Duration</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody id="recent-sessions-table">
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">Loading sessions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';
    console.log('Dashboard BASE_PATH detected as:', window.BASE_PATH);

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
                        
                        return `
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex">
                                        <img src="${escapeHtml(resolveImagePath(session.profile_photo))}" 
                                             alt="${escapeHtml(session.expert_name)}" 
                                             class="w-12 h-12 rounded-full mr-4 object-cover">
                                        <div>
                                            <h3 class="font-semibold">${escapeHtml(session.expert_name)}</h3>
                                            <p class="text-gray-600 text-sm">${escapeHtml(session.tagline || 'Expert Session')}</p>
                                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                ${formattedDate}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <span class="px-3 py-1 ${session.status === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} text-sm rounded">${escapeHtml(session.status)}</span>
                                        ${session.status === 'confirmed' ? `<button ${canJoin ? '' : 'disabled'} data-join-url="${joinUrl}" data-booking-id="${session.id}" class="join-session-btn px-3 py-1 text-sm rounded bg-primary text-white hover:bg-secondary disabled:opacity-50 disabled:cursor-not-allowed">${canJoin ? 'Join' : 'Join Soon'}</button>` : ''}
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
                    activityContainer.innerHTML = '<p class="text-gray-500 text-sm">No recent activity</p>';
                } else {
                    activityContainer.innerHTML = data.recent_activity.slice(0, 3).map(activity => {
                        const updatedDate = new Date(activity.updated_at);
                        const timeAgo = getTimeAgo(updatedDate);
                        const statusColors = {
                            'completed': 'bg-green-500',
                            'confirmed': 'bg-blue-500',
                            'pending': 'bg-yellow-500',
                            'cancelled': 'bg-red-500'
                        };
                        
                        return `
                            <div class="flex items-center">
                                <div class="w-2 h-2 ${statusColors[activity.status] || 'bg-gray-500'} rounded-full mr-3"></div>
                                <div class="text-sm">
                                    <p class="text-gray-900">${activity.status === 'completed' ? 'Completed' : 'Booked'} session with ${escapeHtml(activity.expert_name)}</p>
                                    <p class="text-gray-500">${timeAgo}</p>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
                
                // Render recent sessions table
                const tableBody = document.getElementById('recent-sessions-table');
                if (data.recent_sessions.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="py-8 text-center text-gray-500">No completed sessions yet</td></tr>';
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
                            <tr class="border-b border-gray-100">
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <img src="${escapeHtml(resolveImagePath(session.profile_photo))}" 
                                             alt="${escapeHtml(session.expert_name)}" 
                                             class="w-8 h-8 rounded-full mr-3 object-cover">
                                        <span>${escapeHtml(session.expert_name)}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">${escapeHtml(session.tagline || 'Expert Session')}</td>
                                <td class="py-3 px-4">${formattedDate}</td>
                                <td class="py-3 px-4">${session.duration_minutes} min</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded-full">Completed</span>
                                </td>
                                <td class="py-3 px-4">
                                    <button class="text-primary hover:text-secondary text-sm">View Details</button>
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
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
