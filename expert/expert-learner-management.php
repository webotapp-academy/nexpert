<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Central session + config (defines BASE_PATH / BASE_URL and starts session)
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Learner Management - Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';

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
    <div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-8 gap-4 pb-6 border-b border-gray-800">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                        Learner CRM & Progression
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">Learner Management</h1>
                    <p class="text-sm text-gray-400 mt-1">Track and manage your learners' progression, milestones, and session outcomes</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button id="export-data-btn" class="bg-[#0D131F] border border-gray-700 text-gray-300 hover:text-white hover:border-gray-500 px-4 py-2.5 rounded-xl transition text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export Roster
                    </button>
                    <a href="?panel=expert&page=messages" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-5 py-2.5 rounded-xl font-extrabold text-xs shadow-[0_0_15px_rgba(0,212,170,0.25)] transition inline-flex items-center gap-2">
                        Open Messages &rarr;
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/10 border border-blue-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Total Learners</p>
                            <p class="text-3xl font-extrabold text-white font-mono mt-1"><?php echo $totalLearners; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Active This Month</p>
                            <p class="text-3xl font-extrabold text-white font-mono mt-1"><?php echo $activeLearners; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-500/10 border border-yellow-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Avg. Progress Rate</p>
                            <p class="text-3xl font-extrabold text-white font-mono mt-1"><?php echo $avgProgress; ?>%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-500/10 border border-purple-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Reliability Score</p>
                            <p class="text-3xl font-extrabold text-white font-mono mt-1"><?php echo $avgSatisfaction > 0 ? $avgSatisfaction . '%' : '98%'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 gap-4">
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 flex-1">
                        <div class="relative flex-1">
                            <input type="text" placeholder="Search by name or email..." class="w-full pl-10 pr-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                            <svg class="absolute left-3.5 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        <select class="px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Completed Program</option>
                            <option>On Hold</option>
                        </select>
                        
                        <select class="px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                            <option>Sort by Name</option>
                            <option>Sort by Progress</option>
                            <option>Sort by Last Session</option>
                        </select>
                    </div>
                    
                    <button class="px-5 py-2.5 bg-[#00D4AA] text-[#080B10] font-extrabold rounded-xl hover:bg-[#00bfa0] transition text-xs shadow-md">Apply Filters</button>
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
                    
                    // Photo & Initials setup
                    $learnerInitials = getInitials($learner['full_name']);
                    $hasRealPhoto = !empty($learner['profile_photo']) && $learner['profile_photo'] !== 'null' && strpos($learner['profile_photo'], 'diverse_professional') === false;
                    $learnerPhotoSrc = '';
                    if ($hasRealPhoto) {
                        $rawP = $learner['profile_photo'];
                        if (preg_match('/^(https?:\/\/|data:)/', $rawP)) {
                            $learnerPhotoSrc = $rawP;
                        } elseif (strpos($rawP, BASE_PATH) === 0) {
                            $learnerPhotoSrc = $rawP;
                        } else {
                            $learnerPhotoSrc = BASE_PATH . '/' . ltrim($rawP, '/');
                        }
                    }
                    
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
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6 hover:border-gray-700 transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <?php if ($hasRealPhoto): ?>
                                <div class="relative w-11 h-11 rounded-full mr-3 overflow-hidden border border-gray-700 shrink-0">
                                    <img src="<?php echo htmlspecialchars($learnerPhotoSrc); ?>" alt="<?php echo htmlspecialchars($learner['full_name']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full items-center justify-center font-black text-sm text-[#00D4AA] bg-gradient-to-br from-[#0c1222] to-[#131b2e]">
                                        <?php echo htmlspecialchars($learnerInitials); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="w-11 h-11 rounded-full mr-3 bg-gradient-to-br from-[#0c1222] to-[#131b2e] border border-[#00D4AA]/30 flex items-center justify-center font-black text-sm text-[#00D4AA] shrink-0">
                                    <?php echo htmlspecialchars($learnerInitials); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="font-bold text-white text-sm"><?php echo htmlspecialchars($learner['full_name']); ?></h3>
                                <p class="text-gray-400 text-xs"><?php echo htmlspecialchars($learner['email']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs text-gray-400 font-medium">Outcome Progress</span>
                            <span class="text-xs font-bold text-white font-mono"><?php echo $progress; ?>%</span>
                        </div>
                        <div class="w-full bg-[#080B10] border border-gray-800 rounded-full h-2">
                            <div class="bg-[#00D4AA] h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Enrolled Programs -->
                    <?php if (count($enrolledPrograms) > 0): ?>
                    <div class="mb-4 p-3 bg-[#080B10] border border-gray-800 rounded-xl">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-[#00D4AA] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-gray-300 mb-1">Enrolled Tracks:</p>
                                <div class="space-y-1">
                                    <?php foreach ($enrolledPrograms as $prog): ?>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-gray-400 truncate flex-1" title="<?php echo htmlspecialchars($prog['title']); ?>">
                                            • <?php echo htmlspecialchars($prog['title']); ?>
                                        </span>
                                        <span class="text-[11px] font-bold text-[#00D4AA] font-mono shrink-0"><?php echo round($prog['progress']); ?>%</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-2 gap-3 mb-4 text-xs">
                        <div>
                            <span class="text-gray-400">Sessions:</span>
                            <span class="font-bold text-white font-mono ml-1"><?php echo $learner['completed_sessions']; ?>/<?php echo $learner['total_sessions']; ?></span>
                        </div>
                        <div>
                            <?php if ($learner['next_session_date']): ?>
                                <span class="text-gray-400">Next:</span>
                                <span class="font-bold text-white font-mono ml-1"><?php echo date('M j', strtotime($learner['next_session_date'])); ?></span>
                            <?php elseif ($learner['last_session_date']): ?>
                                <span class="text-gray-400">Last:</span>
                                <span class="font-bold text-white font-mono ml-1"><?php echo date('M j', strtotime($learner['last_session_date'])); ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-gray-400">Trust Band:</span>
                            <span class="font-bold text-emerald-400 ml-1">Verified</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Status:</span>
                            <span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full text-[10px] font-bold ml-1"><?php echo $statusText; ?></span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2 pt-2 border-t border-gray-800">
                        <?php if (!empty($learner['latest_booking_id'])): ?>
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-details&booking_id=<?php echo $learner['latest_booking_id']; ?>" 
                               class="flex-1 bg-[#00D4AA] text-[#080B10] py-2 px-3 rounded-xl font-extrabold hover:bg-[#00bfa0] transition text-xs text-center">
                                View Details
                            </a>
                        <?php else: ?>
                            <button class="flex-1 bg-gray-800 text-gray-500 py-2 px-3 rounded-xl cursor-not-allowed text-xs" disabled>
                                No Sessions
                            </button>
                        <?php endif; ?>
                        <button class="message-btn bg-[#080B10] border border-gray-700 text-gray-300 hover:text-white hover:border-gray-500 py-2 px-3 rounded-xl transition text-xs font-bold flex items-center gap-1" 
                                data-learner-id="<?php echo $learner['user_id']; ?>" 
                                data-learner-name="<?php echo htmlspecialchars($learner['full_name']); ?>">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Message
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl p-12 text-center mb-8">
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
                            <table class="min-w-full text-left">
                                <thead class="bg-[#080B10] border-b border-gray-800 text-gray-400 text-xs uppercase font-bold">
                                    <tr>
                                        <th class="py-3 px-4 text-center">Photo</th>
                                        <th class="py-3 px-4 text-center">Full name</th>
                                        <th class="py-3 px-4 text-center">Program</th>
                                        <th class="py-3 px-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                                    <?php foreach ($purchasedPrograms as $program): 
                                        $learnerNames = !empty($program['learner_names']) ? explode('|||', $program['learner_names']) : [];
                                        $learnerPhotos = !empty($program['learner_photos']) ? explode('|||', $program['learner_photos']) : [];
                                        $learnerIds = !empty($program['learner_ids']) ? explode(',', $program['learner_ids']) : [];
                                        if (count($learnerNames) > 0):
                                            foreach ($learnerNames as $i => $name):
                                                $photoRaw = $learnerPhotos[$i] ?? '';
                                                $hasRealPhotoItem = !empty($photoRaw) && $photoRaw !== 'null' && strpos($photoRaw, 'diverse_professional') === false;
                                                $photoSrc = '';
                                                if ($hasRealPhotoItem) {
                                                    if (preg_match('/^(https?:\/\/|data:)/', $photoRaw)) {
                                                        $photoSrc = $photoRaw;
                                                    } elseif (strpos($photoRaw, BASE_PATH) === 0) {
                                                        $photoSrc = $photoRaw;
                                                    } else {
                                                        $photoSrc = BASE_PATH . '/' . ltrim($photoRaw, '/');
                                                    }
                                                }
                                                $initialsItem = getInitials($name);
                                                $learnerId = !empty($learnerIds[$i]) ? $learnerIds[$i] : '';
                                    ?>
                                    <tr class="hover:bg-[#131B2E] transition-colors">
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($hasRealPhotoItem): ?>
                                                <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="w-8 h-8 rounded-full object-cover border border-gray-700 mx-auto" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                                <div class="hidden w-8 h-8 rounded-full bg-[#0c1222] border border-[#00D4AA]/30 items-center justify-center font-bold text-xs text-[#00D4AA] mx-auto">
                                                    <?php echo htmlspecialchars($initialsItem); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-full bg-[#0c1222] border border-[#00D4AA]/30 flex items-center justify-center font-bold text-xs text-[#00D4AA] mx-auto">
                                                    <?php echo htmlspecialchars($initialsItem); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-white text-center"><?php echo htmlspecialchars($name); ?></td>
                                        <td class="py-3 px-4 text-[#00D4AA] font-semibold text-center"><?php echo htmlspecialchars($program['title']); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if (!empty($learnerId) && !empty($program['id'])): ?>
                                                <a href="<?php echo BASE_PATH; ?>/expert/expert-learner-program-execution.php?id=<?php echo $program['id']; ?>&learner_id=<?php echo $learnerId; ?>" class="inline-block bg-[#00D4AA] text-[#080B10] px-4 py-1.5 rounded-lg font-bold hover:bg-[#00bfa0] transition text-xs">View Details</a>
                                            <?php else: ?>
                                                <span class="text-gray-500">No Details</span>
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
                        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-xl font-extrabold text-white mb-2">No Learners Yet</h3>
                        <p class="text-gray-400 mb-6 text-sm">You don't have any learners yet. Start accepting bookings to see your learners here.</p>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=dashboard" class="inline-block bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-6 py-2.5 rounded-xl font-extrabold text-xs shadow-[0_0_15px_rgba(0,212,170,0.25)] transition">
                            Go to Dashboard
                        </a>
                    <?php endif; ?>
                    </div>
                    
                    <!-- Show Purchased Programs if available -->
                    <?php if (!empty($purchasedPrograms)): ?>
                    <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6 mt-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-[#00D4AA]/10 border border-[#00D4AA]/25 rounded-xl">
                                <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-white">Your Programs - Enrollment Overview</h3>
                                <p class="text-xs text-gray-400">Programs that learners have enrolled in and are currently progressing through</p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($purchasedPrograms as $program): 
                                $learnerNames = !empty($program['learner_names']) ? explode('|||', $program['learner_names']) : [];
                                $learnerPhotos = !empty($program['learner_photos']) ? explode('|||', $program['learner_photos']) : [];
                                $learnerIds = !empty($program['learner_ids']) ? explode(',', $program['learner_ids']) : [];
                            ?>
                            <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-4 hover:border-[#00D4AA]/30 transition">
                                <div class="flex items-start justify-between mb-3">
                                    <h4 class="font-bold text-white text-sm flex-1"><?php echo htmlspecialchars($program['title']); ?></h4>
                                    <span class="ml-2 px-2.5 py-0.5 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] text-[10px] font-bold rounded-full flex-shrink-0 font-mono">
                                        <?php echo $program['enrolled_count']; ?> 
                                        <?php echo $program['enrolled_count'] == 1 ? 'learner' : 'learners'; ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($program['description'])): ?>
                                <p class="text-xs text-gray-400 mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars(substr($program['description'], 0, 80)) . (strlen($program['description']) > 80 ? '...' : ''); ?>
                                </p>
                                <?php endif; ?>
                                
                                <!-- Enrolled Learners Photos -->
                                <?php if (count($learnerNames) > 0): ?>
                                <div class="mb-3 pb-3 border-b border-white/[0.06]">
                                    <p class="text-[11px] font-medium text-gray-400 mb-2">Enrolled Learners:</p>
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
                                                 class="w-7 h-7 rounded-full border-2 border-[#080B10] object-cover program-learner-photo">
                                            <?php endfor; ?>
                                            
                                            <?php if (count($learnerNames) > $maxDisplay): ?>
                                            <div class="w-7 h-7 rounded-full bg-white/[0.08] border-2 border-[#080B10] flex items-center justify-center text-[10px] font-bold text-[#00D4AA]">
                                                +<?php echo count($learnerNames) - $maxDisplay; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (count($learnerNames) <= 2): ?>
                                        <div class="flex-1 min-w-0">
                                            <?php foreach (array_slice($learnerNames, 0, 2) as $name): ?>
                                            <p class="text-xs text-gray-300 truncate"><?php echo htmlspecialchars($name); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center justify-between text-xs mb-2">
                                    <div>
                                        <span class="text-gray-400">Avg Progress:</span>
                                        <span class="font-bold text-[#00D4AA] font-mono ml-1"><?php echo round($program['avg_progress'] ?? 0); ?>%</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Price:</span>
                                        <span class="font-bold text-white font-mono ml-1">₹<?php echo number_format($program['price_inr'] ?? 0); ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="w-full bg-[#0D131F] border border-white/[0.06] rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-[#00D4AA] h-1.5 rounded-full transition-all" style="width: <?php echo round($program['avg_progress'] ?? 0); ?>%"></div>
                                    </div>
                                </div>
                                
                                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=program-details&id=<?php echo $program['id']; ?>" 
                                   class="block text-center text-xs text-gray-300 hover:text-white font-semibold py-2 bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.08] rounded-xl transition">
                                    View Program Details &rarr;
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=my-programs" 
                               class="inline-flex items-center gap-2 text-[#00D4AA] hover:text-[#00e5b7] font-semibold text-xs transition">
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
        <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6 mt-8">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-white">Quick Actions & Reminders</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Automate learner nudges, progress reports, and cohort milestones</p>
                </div>
                <button class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-4 py-2 rounded-xl font-extrabold text-xs shadow-[0_0_15px_rgba(0,212,170,0.25)] transition">
                    Schedule Reminder
                </button>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Recent Activity -->
                <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-5">
                    <h3 class="font-bold text-white text-sm mb-3">Recent Activity</h3>
                    <div class="py-8 text-center">
                        <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs text-gray-300 font-semibold">Activity telemetry active</p>
                        <p class="text-[11px] text-gray-500 mt-1">Live tracking enabled for verified sessions</p>
                    </div>
                </div>

                <!-- Upcoming Reminders -->
                <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-5">
                    <h3 class="font-bold text-white text-sm mb-3">Upcoming Reminders</h3>
                    <div class="py-8 text-center">
                        <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-xs text-gray-300 font-semibold">Automated nudge system</p>
                        <p class="text-[11px] text-gray-500 mt-1">Session preparation alerts sent automatically</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-5">
                    <h3 class="font-bold text-white text-sm mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="?panel=expert&page=messages" class="w-full text-left px-3.5 py-2.5 text-xs text-gray-300 hover:text-white bg-white/[0.02] hover:bg-white/[0.06] border border-white/[0.04] rounded-xl transition flex items-center gap-2">
                            <span>📧</span><span>Send Direct Message</span>
                        </a>
                        <a href="?panel=expert&page=booking-management" class="w-full text-left px-3.5 py-2.5 text-xs text-gray-300 hover:text-white bg-white/[0.02] hover:bg-white/[0.06] border border-white/[0.04] rounded-xl transition flex items-center gap-2">
                            <span>📅</span><span>Manage Session Schedules</span>
                        </a>
                        <a href="?panel=expert&page=my-programs" class="w-full text-left px-3.5 py-2.5 text-xs text-gray-300 hover:text-white bg-white/[0.02] hover:bg-white/[0.06] border border-white/[0.04] rounded-xl transition flex items-center gap-2">
                            <span>📝</span><span>Curate Program Milestones</span>
                        </a>
                        <button id="export-roster-btn" class="w-full text-left px-3.5 py-2.5 text-xs text-gray-300 hover:text-white bg-white/[0.02] hover:bg-white/[0.06] border border-white/[0.04] rounded-xl transition flex items-center gap-2">
                            <span>📊</span><span>Export Learner Telemetry</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="fixed inset-0 bg-black/75 backdrop-blur-md hidden z-50 flex items-center justify-center p-4" id="messageModal">
        <div class="bg-[#0D131F] border border-white/[0.1] rounded-2xl shadow-2xl max-w-lg w-full p-6 text-white">
            <div class="flex justify-between items-center mb-6 pb-3 border-b border-white/[0.08]">
                <h3 class="text-lg font-bold text-white">Send Message to <span id="modal-learner-name" class="text-[#00D4AA]"></span></h3>
                <button id="close-message-modal" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/[0.05] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-5">
                <label for="message-text" class="block text-xs font-semibold text-gray-300 mb-2">Your Message</label>
                <textarea id="message-text" rows="5" 
                          class="w-full px-4 py-3 bg-[#080B10] border border-white/[0.1] text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs placeholder-gray-500" 
                          placeholder="Type your message here..."></textarea>
                <p class="text-[11px] text-gray-500 mt-1.5"><span id="char-count">0</span>/1000 characters</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button id="cancel-message-btn" class="px-4 py-2 bg-white/[0.04] border border-white/10 text-gray-300 hover:text-white text-xs font-semibold rounded-xl hover:bg-white/[0.08] transition">Cancel</button>
                <button id="send-message-btn" class="px-5 py-2 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] font-extrabold text-xs rounded-xl shadow-[0_0_15px_rgba(0,212,170,0.25)] transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Message
                </button>
            </div>
        </div>
    </div>

    <!-- Learner Detail Modal (Hidden by default) -->
    <div class="fixed inset-0 bg-black/75 backdrop-blur-md hidden z-50 flex items-center justify-center p-4" id="learnerModal">
        <div class="bg-[#0D131F] border border-white/[0.1] rounded-2xl shadow-2xl max-w-2xl w-full p-6 text-white">
            <div class="flex justify-between items-center mb-6 pb-3 border-b border-white/[0.08]">
                <h3 class="text-lg font-bold text-white">Learner Details - <span id="learner-detail-name">Priyadarshini</span></h3>
                <button class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/[0.05] transition" onclick="document.getElementById('learnerModal').classList.add('hidden')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-4">
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-3 text-[#00D4AA]">Progress Overview</h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-gray-400">
                            <span>Current Program:</span>
                            <span class="font-semibold text-white">AI Architecture Mastery</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Sessions Completed:</span>
                            <span class="font-semibold text-white font-mono">4 of 5</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Progress:</span>
                            <span class="font-semibold text-[#00D4AA] font-mono">80%</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Next Session:</span>
                            <span class="font-semibold text-white font-mono">Aug 30, 2026</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-[#080B10] border border-white/[0.06] rounded-xl p-4">
                    <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-3 text-[#00D4AA]">Verified Telemetry</h4>
                    <div class="space-y-2 text-xs text-gray-300">
                        <div class="flex items-center gap-2"><span>✅</span><span>Completed 4 advisory sessions</span></div>
                        <div class="flex items-center gap-2"><span>📝</span><span>Session outcome notes verified</span></div>
                        <div class="flex items-center gap-2"><span>⭐</span><span>Calculated trust rating 5.0/5</span></div>
                        <div class="flex items-center gap-2"><span>📅</span><span>Next milestone confirmed</span></div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3">
                <button class="px-4 py-2 bg-white/[0.04] border border-white/10 text-gray-300 hover:text-white text-xs font-semibold rounded-xl hover:bg-white/[0.08] transition" onclick="document.getElementById('learnerModal').classList.add('hidden')">Close</button>
                <a href="?panel=expert&page=messages" class="px-5 py-2 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] font-extrabold text-xs rounded-xl shadow-[0_0_15px_rgba(0,212,170,0.25)] transition">Send Message</a>
            </div>
        </div>
    </div>
</div>
<!-- End Main Content Wrapper -->

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>

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
