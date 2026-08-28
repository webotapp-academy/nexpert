<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Include session configuration and path setup
require_once dirname(__DIR__) . '/includes/session-config.php';

// Use the BASE_PATH constant from session-config
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Booking Management - Nexpert.ai";
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

// Pagination
$page = isset($_GET['booking_page']) ? (int)$_GET['booking_page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Initialize stats
$pendingCount = 0;
$confirmedCount = 0;
$thisWeekCount = 0;
$thisMonthEarnings = 0;
$bookings = [];
$totalBookings = 0;

if ($expertProfileId && $userId) {
    // DEBUG: Let's see what expert profile ID we're using
    echo "<!-- DEBUG: Expert Profile ID: " . $expertProfileId . ", User ID: " . $userId . " -->";
    
    // Get booking stats - expert_id in bookings table refers to users.id, not expert_profiles.id
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'pending'");
    $stmt->execute([$userId]); // Use userId instead of expertProfileId
    $pendingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'confirmed'");
    $stmt->execute([$userId]);
    $confirmedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Get cancelled count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'cancelled'");
    $stmt->execute([$userId]);
    $cancelledCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE expert_id = ? 
        AND WEEK(session_datetime) = WEEK(CURRENT_DATE())
        AND YEAR(session_datetime) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$userId]);
    $thisWeekCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE b.expert_id = ? 
        AND MONTH(b.session_datetime) = MONTH(CURRENT_DATE())
        AND YEAR(b.session_datetime) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$userId]);
    $thisMonthEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Get total count for pagination (only confirmed bookings)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'confirmed'");
    $stmt->execute([$userId]);
    $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Get reschedule requests
    $rescheduleRequests = [];
    $stmt = $pdo->prepare("
        SELECT b.*, 
               lp.full_name as learner_name, u.email as learner_email, 
               lp.profile_photo as learner_photo,
               b.reschedule_new_datetime,
               b.reschedule_reason,
               b.reschedule_requested_at
        FROM bookings b
        LEFT JOIN users u ON b.learner_id = u.id
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        WHERE b.expert_id = ? AND b.reschedule_requested = 1
        ORDER BY b.reschedule_requested_at DESC
    ");
    $stmt->execute([$userId]);
    $rescheduleRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rescheduleCount = count($rescheduleRequests);
    
    // Get bookings with learner info (only confirmed bookings)
    $stmt = $pdo->prepare("
        SELECT b.*, 
               lp.full_name as learner_name, u.email as learner_email, 
               lp.id as learner_profile_id, lp.profile_photo as learner_photo,
               p.amount, p.status as payment_status,
               COALESCE(b.accept_booking, 'no') as accept_booking
        FROM bookings b
        LEFT JOIN users u ON b.learner_id = u.id
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.expert_id = ? AND b.status = 'confirmed'
        ORDER BY b.session_datetime DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $limit, $offset]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get cancelled bookings
    $stmt = $pdo->prepare("
        SELECT b.*, 
               lp.full_name as learner_name, u.email as learner_email, 
               lp.id as learner_profile_id, lp.profile_photo as learner_photo,
               p.amount, p.status as payment_status,
               b.cancellation_reason, b.cancelled_by, b.cancelled_at
        FROM bookings b
        LEFT JOIN users u ON b.learner_id = u.id
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.expert_id = ? AND b.status = 'cancelled'
        ORDER BY b.cancelled_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $cancelledBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Let's see how many bookings we found
    echo "<!-- DEBUG: Found " . count($bookings) . " bookings for user ID " . $userId . " -->";
}

$totalPages = ceil($totalBookings / $limit);
?>
    <div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-8 gap-4 pb-6 border-b border-gray-800">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                        Session Scheduling Engine
                    </div>
                    <h1 class="text-3xl font-extrabold text-white">Booking Management</h1>
                    <p class="text-sm text-gray-400 mt-1">Manage your 1-on-1 sessions, learner requests, and calendar appointments</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="exportBookings" class="bg-[#0D131F] border border-gray-700 text-gray-300 hover:text-white hover:border-gray-500 px-4 py-2.5 rounded-xl transition text-xs font-bold">
                        Export Data
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=settings#availability" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-5 py-2.5 rounded-xl transition text-xs font-extrabold shadow-[0_0_15px_rgba(0,212,170,0.25)] inline-block text-center">
                        Manage Availability &rarr;
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Confirmed Sessions</p>
                            <p class="text-2xl font-extrabold text-white font-mono mt-1"><?php echo $confirmedCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-500/10 border border-red-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Cancelled Sessions</p>
                            <p class="text-2xl font-extrabold text-white font-mono mt-1"><?php echo $cancelledCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500/10 border border-blue-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">This Week</p>
                            <p class="text-2xl font-extrabold text-white font-mono mt-1"><?php echo $thisWeekCount; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-500/10 border border-purple-500/25 rounded-xl">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">This Month Volume</p>
                            <p class="text-2xl font-extrabold text-white font-mono mt-1">₹<?php echo number_format($thisMonthEarnings, 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Reschedule Requests Section -->
        <?php if (!empty($rescheduleRequests)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-500 rounded-full mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Reschedule Requests <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full ml-2"><?php echo $rescheduleCount; ?></span></h2>
                </div>
            </div>
            
            <div class="space-y-4">
                <?php foreach ($rescheduleRequests as $request): 
                    $originalDate = new DateTime($request['session_datetime']);
                    $newDate = new DateTime($request['reschedule_new_datetime']);
                    $requestedAt = new DateTime($request['reschedule_requested_at']);
                ?>
                <div class="bg-white rounded-lg p-4 border border-yellow-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <?php 
                            $learnerPhotoPath = $request['learner_photo'] ?? '';
                            if ($learnerPhotoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $learnerPhotoPath)) {
                                $learnerImageSrc = BASE_PATH . '/' . $learnerPhotoPath;
                            } else {
                                $learnerImageSrc = '';
                            }
                            ?>
                            <?php if ($learnerImageSrc): ?>
                                <img src="<?php echo htmlspecialchars($learnerImageSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($request['learner_name'] ?? 'Learner'); ?>" 
                                     class="w-12 h-12 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold text-lg"><?php echo strtoupper(substr($request['learner_name'] ?? 'U', 0, 1)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($request['learner_name'] ?? 'Unknown Learner'); ?></h3>
                                <p class="text-sm text-gray-600">Requested <?php echo $requestedAt->format('M j, Y \a\t g:i A'); ?></p>
                                
                                <div class="mt-2 flex flex-col sm:flex-row gap-2 sm:gap-4 text-sm">
                                    <div class="flex items-center text-red-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <span class="line-through"><?php echo $originalDate->format('M j, Y g:i A'); ?></span>
                                    </div>
                                    <div class="flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="font-medium"><?php echo $newDate->format('M j, Y g:i A'); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($request['reschedule_reason'])): ?>
                                <p class="mt-2 text-sm text-gray-500 italic">"<?php echo htmlspecialchars($request['reschedule_reason']); ?>"</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 sm:flex-col">
                            <button onclick="handleReschedule(<?php echo $request['id']; ?>, 'accept')" 
                                    class="flex-1 sm:flex-none px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition text-sm font-medium">
                                Accept
                            </button>
                            <button onclick="handleReschedule(<?php echo $request['id']; ?>, 'decline')" 
                                    class="flex-1 sm:flex-none px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm font-medium">
                                Decline
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cancelled Sessions Section -->
        <?php if (!empty($cancelledBookings)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg shadow-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-500 rounded-full mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Cancelled Sessions <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-2"><?php echo $cancelledCount; ?></span></h2>
                </div>
            </div>
            
            <div class="space-y-4">
                <?php foreach ($cancelledBookings as $cancelled): 
                    $sessionDate = new DateTime($cancelled['session_datetime']);
                    $cancelledAt = $cancelled['cancelled_at'] ? new DateTime($cancelled['cancelled_at']) : null;
                ?>
                <div class="bg-white rounded-lg p-4 border border-red-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <?php 
                            $learnerPhotoPath = $cancelled['learner_photo'] ?? '';
                            if ($learnerPhotoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $learnerPhotoPath)) {
                                $learnerImageSrc = BASE_PATH . '/' . $learnerPhotoPath;
                            } else {
                                $learnerImageSrc = '';
                            }
                            ?>
                            <?php if ($learnerImageSrc): ?>
                                <img src="<?php echo htmlspecialchars($learnerImageSrc); ?>" 
                                     alt="<?php echo htmlspecialchars($cancelled['learner_name'] ?? 'Learner'); ?>" 
                                     class="w-12 h-12 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold text-lg"><?php echo strtoupper(substr($cancelled['learner_name'] ?? 'U', 0, 1)); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($cancelled['learner_name'] ?? 'Unknown Learner'); ?></h3>
                                <p class="text-sm text-gray-600">
                                    Session was scheduled for: <span class="font-medium"><?php echo $sessionDate->format('M j, Y \a\t g:i A'); ?></span>
                                </p>
                                <?php if ($cancelledAt): ?>
                                <p class="text-sm text-red-600">
                                    Cancelled <?php echo $cancelledAt->format('M j, Y \a\t g:i A'); ?> 
                                    by <?php echo ucfirst($cancelled['cancelled_by'] ?? 'unknown'); ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($cancelled['cancellation_reason'])): ?>
                                <p class="mt-2 text-sm text-gray-500 italic">"<?php echo htmlspecialchars($cancelled['cancellation_reason']); ?>"</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Cancelled</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filters and Search -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6 mb-8">
            <div class="flex flex-col space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <input type="text" placeholder="Search by learner name..." class="w-full pl-10 pr-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                        <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <select class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                        <option>All Statuses</option>
                        <option>Pending Approval</option>
                        <option>Confirmed</option>
                        <option>Completed</option>
                        <option>Cancelled</option>
                    </select>
                    
                    <select class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>This Week</option>
                        <option>This Month</option>
                    </select>
                    
                    <select class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
                        <option>All Session Types</option>
                        <option>1-on-1 Strategy</option>
                        <option>Code Review</option>
                        <option>Career Guidance</option>
                    </select>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button class="w-full sm:w-auto px-5 py-2.5 bg-[#00D4AA] text-[#080B10] font-extrabold rounded-xl hover:bg-[#00bfa0] transition text-xs shadow-md">Apply Filters</button>
                    <button class="w-full sm:w-auto px-5 py-2.5 bg-[#080B10] border border-gray-700 text-gray-300 rounded-xl hover:border-gray-500 transition text-xs font-semibold">Reset</button>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl overflow-hidden mb-10">
            <div class="px-6 py-4 border-b border-gray-800">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <h2 class="text-base font-extrabold text-white">Recent Session Bookings</h2>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#080B10] border-b border-gray-800 text-gray-400 text-xs uppercase tracking-wider font-bold">
                        <tr>
                            <th class="py-3 px-6">Learner</th>
                            <th class="py-3 px-6">Session Details</th>
                            <th class="py-3 px-6">Date & Time</th>
                            <th class="py-3 px-6">Amount</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6">Accepted</th>
                            <th class="py-3 px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800 text-xs text-gray-300">
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-500">
                                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-white font-bold text-sm mb-1">No bookings recorded</p>
                                    <p class="text-xs text-gray-400">Share your profile link to begin receiving sessions</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): 
                                $statusClasses = [
                                    'pending' => 'bg-yellow-500/10 border border-yellow-500/20 text-yellow-400',
                                    'confirmed' => 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400',
                                    'completed' => 'bg-blue-500/10 border border-blue-500/20 text-blue-400',
                                    'cancelled' => 'bg-red-500/10 border border-red-500/20 text-red-400',
                                    'rescheduled' => 'bg-purple-500/10 border border-purple-500/20 text-purple-400'
                                ];
                                $statusClass = $statusClasses[$booking['status']] ?? 'bg-gray-800 text-gray-300';
                            ?>
                            <tr class="hover:bg-[#131B2E] transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <?php 
                                        $learnerPhotoPath = $booking['learner_photo'] ?? '';
                                        if ($learnerPhotoPath && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $learnerPhotoPath)) {
                                            $learnerImageSrc = BASE_PATH . '/' . $learnerPhotoPath;
                                        } else {
                                            $learnerImageSrc = '';
                                        }
                                        ?>
                                        <?php if ($learnerImageSrc): ?>
                                            <img src="<?php echo htmlspecialchars($learnerImageSrc); ?>" 
                                                 alt="<?php echo htmlspecialchars($booking['learner_name'] ?? 'Learner'); ?>" 
                                                 class="w-9 h-9 rounded-full mr-3 object-cover border border-gray-700">
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center mr-3 text-xs font-bold text-gray-300">
                                                <?php echo strtoupper(substr($booking['learner_name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-bold text-white"><?php echo htmlspecialchars($booking['learner_name'] ?? 'Unknown'); ?></p>
                                            <p class="text-gray-400 text-[11px]"><?php echo htmlspecialchars($booking['learner_email'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-bold text-white"><?php echo htmlspecialchars($booking['session_topic'] ?? '1-on-1 Mentorship'); ?></p>
                                        <p class="text-gray-400 text-[11px]"><?php echo ($booking['duration'] ?? 60); ?> minutes</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-mono text-white"><?php echo date('M d, Y', strtotime($booking['session_datetime'])); ?></p>
                                        <p class="text-gray-400 text-[11px] font-mono"><?php echo date('g:i A', strtotime($booking['session_datetime'])); ?> IST</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-white font-mono">
                                    ₹<?php echo number_format($booking['amount'] ?? 0, 0); ?>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 <?php echo $statusClass; ?> text-[10px] font-bold rounded-full uppercase tracking-wider"><?php echo ucfirst($booking['status']); ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <?php 
                                    $isAccepted = in_array(strtolower((string)($booking['accept_booking'] ?? 'no')), ['yes', '1', 'true'], true);
                                    ?>
                                    <span class="px-2.5 py-1 <?php echo $isAccepted ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/20 text-amber-400'; ?> text-[10px] rounded-full font-bold">
                                        <?php echo $isAccepted ? 'Yes' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <?php if ($isAccepted): ?>
                                                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-details&booking_id=<?php echo $booking['id']; ?>" class="bg-[#00D4AA] text-[#080B10] px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-[#00bfa0] transition inline-block">View Details</a>
                                            <?php else: ?>
                                                <button onclick="handleBookingAction(<?php echo $booking['id']; ?>, 'accept')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Accept</button>
                                                <button onclick="handleBookingAction(<?php echo $booking['id']; ?>, 'reject')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Reject</button>
                                            <?php endif; ?>
                                        <?php elseif ($booking['status'] === 'completed'): ?>
                                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-details&booking_id=<?php echo $booking['id']; ?>" class="bg-gray-800 border border-gray-700 text-gray-300 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition inline-block">View Details</a>
                                        <?php else: ?>
                                            <span class="text-gray-500 text-xs italic">Closed</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalBookings > 0): ?>
            <div class="px-6 py-4 border-t border-gray-800 bg-[#080B10]/50">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <span class="text-xs text-gray-400">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalBookings); ?> of <?php echo $totalBookings; ?> bookings
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $page - 1; ?>" class="px-3 py-1.5 text-gray-300 bg-[#0D131F] border border-gray-700 rounded-lg hover:border-gray-500 text-xs">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <button class="px-3 py-1.5 text-[#080B10] font-extrabold bg-[#00D4AA] rounded-lg text-xs min-w-[32px]"><?php echo $i; ?></button>
                            <?php else: ?>
                                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $i; ?>" class="px-3 py-1.5 text-gray-300 bg-[#0D131F] border border-gray-700 rounded-lg hover:border-gray-500 text-xs min-w-[32px] text-center"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $page + 1; ?>" class="px-4 py-3 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">Next</a>
                        <?php else: ?>
                            <button class="px-4 py-3 text-gray-500 bg-white border border-gray-300 rounded-lg disabled:opacity-50 cursor-not-allowed text-sm" disabled>Next</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

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

    // Show loading popup
    function showLoadingPopup(message) {
        const popup = document.createElement('div');
        popup.id = 'loadingPopup';
        popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        popup.innerHTML = `
            <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
                <div class="flex justify-center mb-4">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-accent"></div>
                </div>
                <p class="text-lg font-semibold text-gray-800 mb-2">Processing...</p>
                <p class="text-sm text-gray-600">${message}</p>
            </div>
        `;
        document.body.appendChild(popup);
    }

    // Hide loading popup
    function hideLoadingPopup() {
        const popup = document.getElementById('loadingPopup');
        if (popup) {
            popup.remove();
        }
    }

    // Handle booking accept/reject actions
    async function handleBookingAction(bookingId, action) {
        const actionText = action === 'accept' ? 'accept' : 'reject';
        
        if (!confirm(`Are you sure you want to ${actionText} this booking?`)) {
            return;
        }
        
        try {
            // Show loading popup for accept action (which creates Zoom meeting and sends emails)
            if (action === 'accept') {
                showLoadingPopup('Creating Zoom meeting and sending emails...');
            }
            
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/booking-action.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    action: action
                })
            });
            
            const result = await response.json();
            
            // Hide loading popup
            hideLoadingPopup();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                // Show detailed error information
                let errorMessage = 'Error: ' + result.error;
                if (result.details) {
                    errorMessage += '\n\nDetails: ' + result.details;
                }
                if (result.zoom_error) {
                    errorMessage += '\n\nZoom Error: ' + result.zoom_error;
                }
                alert(errorMessage);
            }
        } catch (error) {
            // Hide loading popup on error
            hideLoadingPopup();
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
    
    // Handle reschedule request (accept/decline)
    async function handleReschedule(bookingId, action) {
        const actionText = action === 'accept' ? 'accept' : 'decline';
        if (!confirm(`Are you sure you want to ${actionText} this reschedule request?`)) {
            return;
        }
        
        showLoadingPopup();
        
        try {
            const response = await fetch('<?php echo BASE_PATH; ?>/admin-panel/apis/expert/reschedule.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: bookingId,
                    action: action
                })
            });
            
            const result = await response.json();
            hideLoadingPopup();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to process request'));
            }
        } catch (error) {
            hideLoadingPopup();
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
    
    // Add any page-specific JavaScript here
    document.getElementById('exportBookings').addEventListener('click', function() {
        // Implement export functionality
        alert('Export functionality will be implemented soon.');
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
