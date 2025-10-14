<?php
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
    
    // Get bookings with learner info (only confirmed bookings)
    $stmt = $pdo->prepare("
        SELECT b.*, 
               lp.full_name as learner_name, u.email as learner_email, 
               lp.id as learner_profile_id, lp.profile_photo as learner_photo,
               p.amount, p.status as payment_status
        FROM bookings b
        LEFT JOIN learner_profiles lp ON b.learner_id = lp.id
        LEFT JOIN users u ON lp.user_id = u.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.expert_id = ? AND b.status = 'confirmed'
        ORDER BY b.session_datetime DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $limit, $offset]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Let's see how many bookings we found
    echo "<!-- DEBUG: Found " . count($bookings) . " bookings for user ID " . $userId . " -->";
}

$totalPages = ceil($totalBookings / $limit);
?>
    </nav>

    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 sm:mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Booking Management</h1>
                <p class="text-sm sm:text-base text-gray-600">Manage your session requests and bookings</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <button id="exportBookings" class="bg-white border border-gray-300 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-50 transition text-sm">
                    Export Data
                </button>
                <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=settings#availability" class="bg-accent text-white px-4 py-3 rounded-lg hover:bg-yellow-600 transition text-sm inline-block text-center">
                    Manage Availability
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Pending Requests Card - Hidden as we only show confirmed bookings -->
            <!-- 
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-yellow-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $pendingCount; ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">Pending Requests</p>
                    </div>
                </div>
            </div>
            -->
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-green-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $confirmedCount; ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">Confirmed Sessions</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-blue-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $thisWeekCount; ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">This Week</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-purple-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900">₹<?php echo number_format($thisMonthEarnings, 0); ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">This Month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-col space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <input type="text" placeholder="Search by learner name..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent text-sm">
                        <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent text-sm">
                        <option>All Statuses</option>
                        <option>Pending Approval</option>
                        <option>Confirmed</option>
                        <option>Completed</option>
                        <option>Cancelled</option>
                        <option>Rescheduled</option>
                    </select>
                    
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent text-sm">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>This Week</option>
                        <option>This Month</option>
                        <option>Custom Range</option>
                    </select>
                    
                    <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent text-sm">
                        <option>All Session Types</option>
                        <option>One-time Session</option>
                        <option>Package Sessions</option>
                        <option>Subscription</option>
                    </select>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button class="w-full sm:w-auto px-4 py-3 bg-accent text-white rounded-lg hover:bg-yellow-600 transition text-sm">Apply Filters</button>
                    <button class="w-full sm:w-auto px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">Reset</button>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Recent Bookings</h2>
                    <div class="flex gap-2">
                        <button class="px-4 py-3 bg-accent text-white rounded text-sm">Confirmed</button>
                        <!-- <button class="px-4 py-3 bg-gray-200 text-gray-700 rounded text-sm">All Bookings</button> -->
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Learner</th>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Session Details</th>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Date & Time</th>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Amount</th>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Status</th>
                            <th class="text-left py-3 px-6 font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-gray-600 mb-2">No bookings yet</p>
                                    <p class="text-sm text-gray-500">Start accepting bookings to see them here!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): 
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'rescheduled' => 'bg-purple-100 text-purple-800'
                                ];
                                $statusClass = $statusClasses[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
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
                                                 class="w-10 h-10 rounded-full mr-3 object-cover">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <span class="text-gray-600 font-semibold"><?php echo strtoupper(substr($booking['learner_name'] ?? 'U', 0, 1)); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['learner_name'] ?? 'Unknown'); ?></p>
                                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($booking['learner_email'] ?? ''); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($booking['session_topic'] ?? 'Session'); ?></p>
                                        <p class="text-gray-600 text-sm"><?php echo ($booking['duration'] ?? 60); ?> minutes</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime($booking['session_datetime'])); ?></p>
                                        <p class="text-gray-600 text-sm"><?php echo date('g:i A', strtotime($booking['session_datetime'])); ?> IST</p>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-semibold text-gray-900">₹<?php echo number_format($booking['amount'] ?? 0, 0); ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-1 <?php echo $statusClass; ?> text-sm rounded-full"><?php echo ucfirst($booking['status']); ?></span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <button class="bg-primary text-white px-4 py-3 rounded text-sm hover:bg-secondary transition">Join Session</button>
                                        <?php elseif ($booking['status'] === 'completed'): ?>
                                            <button class="bg-gray-500 text-white px-4 py-3 rounded text-sm">View Details</button>
                                        <?php else: ?>
                                            <button class="bg-gray-400 text-white px-4 py-3 rounded text-sm cursor-not-allowed" disabled>No Action</button>
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
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 sm:gap-0">
                    <span class="text-xs sm:text-sm text-gray-600">
                        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $totalBookings); ?> of <?php echo $totalBookings; ?> bookings
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $page - 1; ?>" class="px-4 py-3 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">Previous</a>
                        <?php else: ?>
                            <button class="px-4 py-3 text-gray-500 bg-white border border-gray-300 rounded-lg disabled:opacity-50 cursor-not-allowed text-sm" disabled>Previous</button>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <button class="px-4 py-3 text-white bg-accent border border-accent rounded-lg text-sm min-w-[44px]"><?php echo $i; ?></button>
                            <?php else: ?>
                                <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $i; ?>" class="px-4 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm min-w-[44px] text-center"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=booking-management&booking_page=<?php echo $page + 1; ?>" class="px-4 py-3 text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">Next</a>
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
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

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

    // Add any page-specific JavaScript here
    document.getElementById('exportBookings').addEventListener('click', function() {
        // Implement export functionality
        alert('Export functionality will be implemented soon.');
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
