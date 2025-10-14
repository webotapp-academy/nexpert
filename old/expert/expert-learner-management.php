<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Learner Management - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/navigation.php';

// Get expert profile ID
$userId = $_SESSION['user_id'] ?? null;
$expertProfileId = null;

if ($userId) {
    $stmt = $pdo->prepare("SELECT id FROM expert_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile) {
        $expertProfileId = $profile['id'];
    }
}

// Initialize data
$totalLearners = 0;
$activeLearners = 0;
$avgProgress = 0;
$avgSatisfaction = 0;
$learners = [];

if ($expertProfileId) {
    // Get total unique learners
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.learner_id) as total
        FROM bookings b
        WHERE b.expert_id = ?
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalLearners = $result['total'] ?? 0;
    
    // Get active learners (with bookings in the last 30 days)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.learner_id) as active
        FROM bookings b
        WHERE b.expert_id = ? 
        AND b.session_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeLearners = $result['active'] ?? 0;
    
    // Get average satisfaction from reviews
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating
        FROM reviews
        WHERE expert_id = ? AND status = 'approved'
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $avgSatisfaction = round($result['avg_rating'] ?? 0, 1);
    
    // Get learners with their data
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            u.id as user_id,
            u.email,
            lp.id as learner_profile_id,
            lp.full_name,
            lp.profile_photo,
            COUNT(DISTINCT b.id) as total_sessions,
            SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
            SUM(CASE WHEN b.status = 'pending' OR b.status = 'confirmed' THEN 1 ELSE 0 END) as upcoming_sessions,
            MAX(b.session_datetime) as last_session_date,
            MIN(CASE WHEN b.session_datetime > NOW() THEN b.session_datetime ELSE NULL END) as next_session_date,
            AVG(r.rating) as avg_rating,
            COALESCE(lpr.progress_percentage, 0) as progress_percentage
        FROM bookings b
        JOIN learner_profiles lp ON b.learner_id = lp.id
        JOIN users u ON lp.user_id = u.id
        LEFT JOIN reviews r ON b.id = r.booking_id AND r.expert_id = ?
        LEFT JOIN learner_progress lpr ON lpr.learner_id = lp.id AND lpr.expert_id = ?
        WHERE b.expert_id = ?
        GROUP BY u.id, u.email, lp.id, lp.full_name, lp.profile_photo, lpr.progress_percentage
        ORDER BY last_session_date DESC
    ");
    $stmt->execute([$expertProfileId, $expertProfileId, $expertProfileId]);
    $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate average progress
    if (count($learners) > 0) {
        $totalProgress = array_sum(array_column($learners, 'progress_percentage'));
        $avgProgress = round($totalProgress / count($learners), 0);
    }
}
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Learner Management</h1>
                <p class="text-gray-600">Track and manage your learners' progress and engagement</p>
            </div>
            <div class="flex space-x-3">
                <button class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                    Export Data
                </button>
                <button class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                    Send Bulk Message
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $totalLearners; ?></p>
                        <p class="text-gray-600 text-sm">Total Learners</p>
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
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $activeLearners; ?></p>
                        <p class="text-gray-600 text-sm">Active This Month</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $avgProgress; ?>%</p>
                        <p class="text-gray-600 text-sm">Avg. Progress Rate</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $avgSatisfaction > 0 ? $avgSatisfaction : 'N/A'; ?></p>
                        <p class="text-gray-600 text-sm">Avg. Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search by name or email..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <select class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Completed Program</option>
                        <option>On Hold</option>
                        <option>Inactive</option>
                    </select>
                    
                    <select class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        <option>All Programs</option>
                        <option>UX Bootcamp</option>
                        <option>Career Coaching</option>
                        <option>Portfolio Review</option>
                        <option>One-time Sessions</option>
                    </select>
                    
                    <select class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        <option>Sort by Name</option>
                        <option>Sort by Progress</option>
                        <option>Sort by Last Session</option>
                        <option>Sort by Start Date</option>
                    </select>
                </div>
                
                <button class="px-4 py-2 bg-accent text-white rounded-lg hover:bg-yellow-600 transition text-sm">Apply Filters</button>
            </div>
        </div>

        <!-- Learners Grid -->
        <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            <?php if (count($learners) > 0): ?>
                <?php foreach ($learners as $learner): 
                    $progress = round($learner['progress_percentage'] ?? 0);
                    $rating = round($learner['avg_rating'] ?? 0, 1);
                    $hasUpcoming = $learner['upcoming_sessions'] > 0;
                    $hasCompleted = $learner['completed_sessions'] == $learner['total_sessions'];
                    
                    $progressColor = 'bg-accent';
                    if ($progress >= 80) $progressColor = 'bg-green-500';
                    else if ($progress >= 50) $progressColor = 'bg-blue-500';
                    else if ($progress >= 20) $progressColor = 'bg-orange-500';
                    
                    $statusBadge = 'bg-gray-100 text-gray-800';
                    $statusText = 'Inactive';
                    if ($hasUpcoming) {
                        $statusBadge = 'bg-green-100 text-green-800';
                        $statusText = 'Active';
                    } else if ($hasCompleted) {
                        $statusBadge = 'bg-blue-100 text-blue-800';
                        $statusText = 'Completed';
                    }
                    
                    $profilePhoto = !empty($learner['profile_photo']) ? $learner['profile_photo'] : 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
                ?>
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="<?php echo htmlspecialchars($learner['full_name']); ?>" class="w-12 h-12 rounded-full mr-3 object-cover learner-profile-photo">
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($learner['full_name']); ?></h3>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($learner['email']); ?></p>
                            </div>
                        </div>
                        <div class="relative">
                            <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Progress</span>
                            <span class="text-sm font-semibold text-gray-900"><?php echo $progress; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="<?php echo $progressColor; ?> h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <span class="text-gray-600">Sessions:</span>
                            <span class="font-semibold ml-1"><?php echo $learner['completed_sessions']; ?>/<?php echo $learner['total_sessions']; ?></span>
                        </div>
                        <div>
                            <?php if ($learner['next_session_date']): ?>
                                <span class="text-gray-600">Next:</span>
                                <span class="font-semibold ml-1"><?php echo date('M j', strtotime($learner['next_session_date'])); ?></span>
                            <?php elseif ($learner['last_session_date']): ?>
                                <span class="text-gray-600">Last:</span>
                                <span class="font-semibold ml-1"><?php echo date('M j', strtotime($learner['last_session_date'])); ?></span>
                            <?php else: ?>
                                <span class="text-gray-600">-</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-gray-600">Rating:</span>
                            <span class="font-semibold ml-1"><?php echo $rating > 0 ? $rating . ' ★' : 'N/A'; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 py-0.5 <?php echo $statusBadge; ?> rounded-full text-xs"><?php echo $statusText; ?></span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button class="flex-1 bg-primary text-white py-2 px-3 rounded-lg hover:bg-secondary transition text-sm">
                            View Details
                        </button>
                        <button class="bg-gray-200 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-300 transition text-sm">
                            Message
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full bg-white rounded-lg shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Learners Yet</h3>
                    <p class="text-gray-600 mb-6">You don't have any learners yet. Start accepting bookings to see your learners here.</p>
                    <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=dashboard" class="inline-block bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
                        Go to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bulk Actions & Reminders -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Quick Actions & Reminders</h2>
                <button class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                    Schedule Reminder
                </button>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Recent Activity -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Recent Activity</h3>
                    <div class="py-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-500">Activity tracking</p>
                        <p class="text-xs text-gray-400 mt-1">Coming soon</p>
                    </div>
                </div>

                <!-- Upcoming Reminders -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Upcoming Reminders</h3>
                    <div class="py-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-sm text-gray-500">Reminder system</p>
                        <p class="text-xs text-gray-400 mt-1">Coming soon</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📧 Send Progress Report
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📅 Schedule Group Session
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📝 Create Assignment
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📊 Export Progress Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Learner Detail Modal (Hidden by default) -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50" id="learnerModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Learner Details - Aarav Patel</h3>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Progress Overview</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Program:</span>
                                <span class="font-medium">UX Bootcamp</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sessions Completed:</span>
                                <span class="font-medium">6 of 8</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Progress:</span>
                                <span class="font-medium">75%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Next Session:</span>
                                <span class="font-medium">Sep 30, 2:00 PM</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Recent Activity</h4>
                        <div class="space-y-2 text-sm">
                            <div class="text-gray-700">✅ Completed Assignment #3</div>
                            <div class="text-gray-700">📝 Session notes updated</div>
                            <div class="text-gray-700">⭐ Rated last session 4.8/5</div>
                            <div class="text-gray-700">📅 Booked next session</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Close</button>
                    <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition">Send Message</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/footer.php'; ?>

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

    // Update profile photo paths in the learners grid
    document.querySelectorAll('.learner-profile-photo').forEach(img => {
        const originalSrc = img.getAttribute('src');
        img.setAttribute('src', resolveImagePath(originalSrc));
    });
</script>
