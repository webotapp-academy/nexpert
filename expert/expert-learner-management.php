<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Learner Management - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

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

if ($userId) {
    // Get total unique learners - use userId for bookings.expert_id
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.learner_id) as total
        FROM bookings b
        WHERE b.expert_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalLearners = $result['total'] ?? 0;
    
    // Get active learners (with bookings in the last 30 days) - use userId
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.learner_id) as active
        FROM bookings b
        WHERE b.expert_id = ? 
        AND b.session_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeLearners = $result['active'] ?? 0;
    
    // Get average satisfaction from reviews - use expertProfileId for reviews
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating
        FROM reviews
        WHERE expert_id = ? AND status = 'approved'
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $avgSatisfaction = round($result['avg_rating'] ?? 0, 1);
    
    // Get learners with their data - use userId for bookings.expert_id
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            u.id as user_id,
            u.email,
            lp.full_name,
            lp.profile_photo,
            COUNT(DISTINCT b.id) as total_sessions,
            SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
            SUM(CASE WHEN b.status = 'pending' OR b.status = 'confirmed' THEN 1 ELSE 0 END) as upcoming_sessions,
            MAX(b.session_datetime) as last_session_date,
            MIN(CASE WHEN b.session_datetime > NOW() THEN b.session_datetime ELSE NULL END) as next_session_date,
            (SELECT id FROM bookings WHERE learner_id = u.id AND expert_id = ? ORDER BY session_datetime DESC LIMIT 1) as latest_booking_id,
            AVG(r.rating) as avg_rating,
            COALESCE(MAX(lprog.progress_percentage), 0) as progress_percentage,
            GROUP_CONCAT(DISTINCT w.title ORDER BY lprog.created_at DESC SEPARATOR '|||') as enrolled_programs,
            GROUP_CONCAT(DISTINCT lprog.workflow_id ORDER BY lprog.created_at DESC SEPARATOR ',') as program_ids,
            GROUP_CONCAT(DISTINCT lprog.progress_percentage ORDER BY lprog.created_at DESC SEPARATOR ',') as program_progress
        FROM bookings b
        JOIN users u ON b.learner_id = u.id
        JOIN learner_profiles lp ON u.id = lp.user_id
        LEFT JOIN reviews r ON b.id = r.booking_id AND r.expert_id = ?
        LEFT JOIN learner_progress lprog ON lprog.learner_id = u.id AND lprog.expert_id = ?
        LEFT JOIN workflows w ON lprog.workflow_id = w.id
        WHERE b.expert_id = ?
        GROUP BY u.id, u.email, lp.full_name, lp.profile_photo
        ORDER BY last_session_date DESC
    ");
    $stmt->execute([$userId, $expertProfileId, $userId, $userId]);
    $learners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate average progress
    if (count($learners) > 0) {
        $totalProgress = array_sum(array_column($learners, 'progress_percentage'));
        $avgProgress = round($totalProgress / count($learners), 0);
    }
    
    // Get expert's programs that learners have purchased
    $purchasedPrograms = [];
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            w.id,
            w.title,
            w.description,
            w.price_inr,
            COUNT(DISTINCT lp.learner_id) as enrolled_count,
            AVG(lp.progress_percentage) as avg_progress,
            GROUP_CONCAT(DISTINCT learner_prof.full_name ORDER BY lp.created_at DESC SEPARATOR '|||') as learner_names,
            GROUP_CONCAT(DISTINCT learner_prof.profile_photo ORDER BY lp.created_at DESC SEPARATOR '|||') as learner_photos,
            GROUP_CONCAT(DISTINCT lp.learner_id ORDER BY lp.created_at DESC SEPARATOR ',') as learner_ids
        FROM workflows w
        LEFT JOIN learner_progress lp ON w.id = lp.workflow_id AND lp.expert_id = ?
        LEFT JOIN learner_profiles learner_prof ON lp.learner_id = learner_prof.user_id
        WHERE w.expert_id = ? AND w.is_active = 1
        GROUP BY w.id, w.title, w.description, w.price_inr
        ORDER BY enrolled_count DESC
    ");
    $stmt->execute([$userId, $userId]);
    $purchasedPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <button id="export-data-btn" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
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
                    
                    // Parse enrolled programs
                    $enrolledPrograms = [];
                    if (!empty($learner['enrolled_programs'])) {
                        $programTitles = explode('|||', $learner['enrolled_programs']);
                        $programIds = explode(',', $learner['program_ids'] ?? '');
                        $programProgressArray = explode(',', $learner['program_progress'] ?? '');
                        
                        foreach ($programTitles as $index => $title) {
                            if (!empty($title)) {
                                $enrolledPrograms[] = [
                                    'title' => $title,
                                    'id' => $programIds[$index] ?? '',
                                    'progress' => $programProgressArray[$index] ?? 0
                                ];
                            }
                        }
                    }
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
                    
                    <!-- Enrolled Programs -->
                    <?php if (count($enrolledPrograms) > 0): ?>
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-amber-800 mb-1">Enrolled Programs:</p>
                                <div class="space-y-1">
                                    <?php foreach ($enrolledPrograms as $prog): ?>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs text-amber-700 truncate flex-1" title="<?php echo htmlspecialchars($prog['title']); ?>">
                                            • <?php echo htmlspecialchars($prog['title']); ?>
                                        </span>
                                        <span class="text-xs font-medium text-amber-600 flex-shrink-0"><?php echo round($prog['progress']); ?>%</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
                        <?php if (!empty($learner['latest_booking_id'])): ?>
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-details&booking_id=<?php echo $learner['latest_booking_id']; ?>" 
                               class="flex-1 bg-primary text-white py-2 px-3 rounded-lg hover:bg-secondary transition text-sm text-center">
                                View Details
                            </a>
                        <?php else: ?>
                            <button class="flex-1 bg-gray-300 text-gray-500 py-2 px-3 rounded-lg cursor-not-allowed text-sm" disabled>
                                No Bookings
                            </button>
                        <?php endif; ?>
                        <button class="message-btn bg-gray-200 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-300 transition text-sm flex items-center gap-1" 
                                data-learner-id="<?php echo $learner['user_id']; ?>" 
                                data-learner-name="<?php echo htmlspecialchars($learner['full_name']); ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Message
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-white rounded-lg shadow-lg p-12 text-center mb-8">
                    <?php 
                    $hasLearner = false;
                    if (!empty($purchasedPrograms)) {
                        foreach ($purchasedPrograms as $program) {
                            $learnerNames = !empty($program['learner_names']) ? explode('|||', $program['learner_names']) : [];
                            $learnerPhotos = !empty($program['learner_photos']) ? explode('|||', $program['learner_photos']) : [];
                            $learnerIds = !empty($program['learner_ids']) ? explode(',', $program['learner_ids']) : [];
                            if (count($learnerNames) > 0) {
                                $hasLearner = true;
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if ($hasLearner): ?>
                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full bg-white rounded-lg shadow border">
                                                            <thead>
                                                                <tr class="bg-gray-100 text-gray-700 text-sm">
                                                                    <th class="py-3 px-4 text-center">Photo</th>
                                                                    <th class="py-3 px-4 text-center">Full name</th>
                                                                    <th class="py-3 px-4 text-center">Program</th>
                                                                    <th class="py-3 px-4 text-center">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($purchasedPrograms as $program): 
                                                                    $learnerNames = !empty($program['learner_names']) ? explode('|||', $program['learner_names']) : [];
                                                                    $learnerPhotos = !empty($program['learner_photos']) ? explode('|||', $program['learner_photos']) : [];
                                                                    $learnerIds = !empty($program['learner_ids']) ? explode(',', $program['learner_ids']) : [];
                                                                    if (count($learnerNames) > 0):
                                                                        foreach ($learnerNames as $i => $name):
                                                                            $photo = !empty($learnerPhotos[$i]) ? $learnerPhotos[$i] : 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
                                                                            $learnerId = !empty($learnerIds[$i]) ? $learnerIds[$i] : '';
                                                                ?>
                                                                <tr class="border-b hover:bg-gray-50">
                                                                    <td class="py-3 px-4 text-center">
                                                                        <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow mx-auto" />
                                                                    </td>
                                                                    <td class="py-3 px-4 font-semibold text-gray-900 text-center"><?php echo htmlspecialchars($name); ?></td>
                                                                    <td class="py-3 px-4 text-blue-700 font-medium text-center"><?php echo htmlspecialchars($program['title']); ?></td>
                                                                    <td class="py-3 px-4 text-center">
                                                                        <?php if (!empty($learnerId) && !empty($program['id'])): ?>
                                                                            <a href="<?php echo BASE_PATH; ?>/expert/expert-learner-program-execution.php?id=<?php echo $program['id']; ?>&learner_id=<?php echo $learnerId; ?>" class="inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition text-sm font-medium">View Details</a>
                                                                        <?php else: ?>
                                                                            <span class="text-gray-400">No Details</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                                        <?php endforeach;
                                                                    endif;
                                                                endforeach; ?>
                                                            </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Learners Yet</h3>
                        <p class="text-gray-600 mb-6">You don't have any learners yet. Start accepting bookings to see your learners here.</p>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=dashboard" class="inline-block bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
                            Go to Dashboard
                        </a>
                    <?php endif; ?>
                    </div>
                    
                    <!-- Show Purchased Programs if available -->
                    <?php if (!empty($purchasedPrograms)): ?>
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg shadow-lg p-6 border-2 border-amber-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-amber-500 rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-amber-900">Your Programs - Enrollment Overview</h3>
                                <p class="text-sm text-amber-700">Programs that learners have purchased</p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($purchasedPrograms as $program): 
                                // Parse learner data
                                $learnerNames = !empty($program['learner_names']) ? explode('|||', $program['learner_names']) : [];
                                $learnerPhotos = !empty($program['learner_photos']) ? explode('|||', $program['learner_photos']) : [];
                                $learnerIds = !empty($program['learner_ids']) ? explode(',', $program['learner_ids']) : [];
                            ?>
                            <div class="bg-white rounded-lg p-4 border-l-4 border-amber-500 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-3">
                                    <h4 class="font-semibold text-gray-900 text-sm flex-1"><?php echo htmlspecialchars($program['title']); ?></h4>
                                    <span class="ml-2 px-2 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full flex-shrink-0">
                                        <?php echo $program['enrolled_count']; ?> 
                                        <?php echo $program['enrolled_count'] == 1 ? 'learner' : 'learners'; ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($program['description'])): ?>
                                <p class="text-xs text-gray-600 mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars(substr($program['description'], 0, 80)) . (strlen($program['description']) > 80 ? '...' : ''); ?>
                                </p>
                                <?php endif; ?>
                                
                                <!-- Enrolled Learners Photos -->
                                <?php if (count($learnerNames) > 0): ?>
                                <div class="mb-3 pb-3 border-b border-gray-200">
                                    <p class="text-xs font-medium text-gray-700 mb-2">Enrolled Learners:</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex -space-x-2">
                                            <?php 
                                            $maxDisplay = 4;
                                            $displayCount = min(count($learnerNames), $maxDisplay);
                                            for ($i = 0; $i < $displayCount; $i++): 
                                                $photo = !empty($learnerPhotos[$i]) ? $learnerPhotos[$i] : 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
                                                $name = htmlspecialchars($learnerNames[$i] ?? 'Learner');
                                            ?>
                                            <img src="<?php echo htmlspecialchars($photo); ?>" 
                                                 alt="<?php echo $name; ?>" 
                                                 title="<?php echo $name; ?>"
                                                 class="w-8 h-8 rounded-full border-2 border-white object-cover program-learner-photo">
                                            <?php endfor; ?>
                                            
                                            <?php if (count($learnerNames) > $maxDisplay): ?>
                                            <div class="w-8 h-8 rounded-full bg-amber-100 border-2 border-white flex items-center justify-center">
                                                <span class="text-xs font-bold text-amber-700">+<?php echo count($learnerNames) - $maxDisplay; ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (count($learnerNames) <= 2): ?>
                                        <div class="flex-1 min-w-0">
                                            <?php foreach (array_slice($learnerNames, 0, 2) as $name): ?>
                                            <p class="text-xs text-gray-600 truncate"><?php echo htmlspecialchars($name); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs mb-3">
                                    <div>
                                        <span class="text-gray-500">Avg Progress:</span>
                                        <span class="font-semibold text-amber-700 ml-1"><?php echo round($program['avg_progress'] ?? 0); ?>%</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Price:</span>
                                        <span class="font-semibold text-green-700 ml-1">₹<?php echo number_format($program['price_inr'] ?? 0); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Progress bar -->
                                <div class="mb-3">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-amber-500 h-1.5 rounded-full transition-all" style="width: <?php echo round($program['avg_progress'] ?? 0); ?>%"></div>
                                    </div>
                                </div>
                                
                                <!-- View Details Button -->
                                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=program-details&id=<?php echo $program['id']; ?>" 
                                   class="block text-center text-xs text-amber-700 hover:text-amber-900 font-medium py-2 bg-amber-50 rounded hover:bg-amber-100 transition">
                                    View Program Details →
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=my-programs" 
                               class="inline-flex items-center gap-2 text-amber-700 hover:text-amber-900 font-medium text-sm">
                                <span>View All Programs</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
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

    <!-- Message Modal -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50" id="messageModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Send Message to <span id="modal-learner-name"></span></h3>
                    <button id="close-message-modal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label for="message-text" class="block text-sm font-medium text-gray-700 mb-2">Your Message</label>
                    <textarea id="message-text" rows="6" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                              placeholder="Type your message here..."></textarea>
                    <p class="text-xs text-gray-500 mt-1"><span id="char-count">0</span>/1000 characters</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancel-message-btn" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                    <button id="send-message-btn" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Send Message
                    </button>
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

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

    // Update profile photo paths in the purchased programs section
    document.querySelectorAll('.program-learner-photo').forEach(img => {
        const originalSrc = img.getAttribute('src');
        img.setAttribute('src', resolveImagePath(originalSrc));
    });

    // Export Data functionality
    document.getElementById('export-data-btn').addEventListener('click', function() {
        const learners = <?php echo json_encode($learners); ?>;
        
        if (learners.length === 0) {
            alert('No learner data to export');
            return;
        }
        
        // Create CSV headers
        const headers = ['Name', 'Email', 'Total Sessions', 'Completed Sessions', 'Upcoming Sessions', 'Last Session', 'Next Session', 'Avg Rating', 'Progress %'];
        
        // Create CSV rows
        const rows = learners.map(learner => {
            return [
                learner.full_name || 'N/A',
                learner.email || 'N/A',
                learner.total_sessions || '0',
                learner.completed_sessions || '0',
                learner.upcoming_sessions || '0',
                learner.last_session_date ? new Date(learner.last_session_date).toLocaleDateString() : 'Never',
                learner.next_session_date ? new Date(learner.next_session_date).toLocaleDateString() : 'None',
                learner.avg_rating ? parseFloat(learner.avg_rating).toFixed(1) : 'N/A',
                learner.progress_percentage || '0'
            ];
        });
        
        // Combine headers and rows
        const csvContent = [headers, ...rows]
            .map(row => row.map(cell => `"${cell}"`).join(','))
            .join('\n');
        
        // Create blob and download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `learners_data_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success message
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Exported!';
        btn.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-50', 'text-green-700', 'border-green-300');
        }, 2000);
    });

    // Message Modal Functionality
    let selectedLearnerId = null;
    const messageModal = document.getElementById('messageModal');
    const messageText = document.getElementById('message-text');
    const charCount = document.getElementById('char-count');
    const sendBtn = document.getElementById('send-message-btn');
    
    // Open message modal
    document.querySelectorAll('.message-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectedLearnerId = this.dataset.learnerId;
            const learnerName = this.dataset.learnerName;
            
            document.getElementById('modal-learner-name').textContent = learnerName;
            messageText.value = '';
            charCount.textContent = '0';
            messageModal.classList.remove('hidden');
        });
    });
    
    // Close message modal
    function closeMessageModal() {
        messageModal.classList.add('hidden');
        selectedLearnerId = null;
        messageText.value = '';
    }
    
    document.getElementById('close-message-modal').addEventListener('click', closeMessageModal);
    document.getElementById('cancel-message-btn').addEventListener('click', closeMessageModal);
    
    // Character count
    messageText.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        if (count > 1000) {
            this.value = this.value.substring(0, 1000);
            charCount.textContent = '1000';
        }
    });
    
    // Send message
    sendBtn.addEventListener('click', async function() {
        const message = messageText.value.trim();
        
        if (!message) {
            alert('Please enter a message');
            return;
        }
        
        if (!selectedLearnerId) {
            alert('Learner not selected');
            return;
        }
        
        // Disable button and show loading
        const originalHTML = sendBtn.innerHTML;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/messages.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    learner_id: selectedLearnerId,
                    message: message
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Message sent successfully!');
                closeMessageModal();
            } else {
                alert(result.message || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to send message. Please try again.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalHTML;
        }
    });
</script>
