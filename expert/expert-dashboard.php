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

$page_title = "Expert Dashboard - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/navigation.php';

// Check onboarding steps completion
$userId = $_SESSION['user_id'] ?? null;
$profileComplete = false;
$kycComplete = false;
$availabilitySet = false;
$firstBooking = false;
$expertProfileId = null;

if ($userId) {
    // Check profile completion - query by user_id instead of id
    $stmt = $pdo->prepare("
        SELECT id, full_name, tagline, bio_short, expertise_verticals, verification_status
        FROM expert_profiles 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        $expertProfileId = $profile['id'];
        
        if (!empty($profile['full_name']) && !empty($profile['tagline']) && 
            !empty($profile['bio_short']) && !empty($profile['expertise_verticals'])) {
            $profileComplete = true;
        }
        
        // Check KYC verification
        if ($profile['verification_status'] === 'approved') {
            $kycComplete = true;
        }
    }
    
    // Check if availability is set
    if ($expertProfileId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM expert_availability WHERE expert_id = ?");
        $stmt->execute([$expertProfileId]);
        $availCount = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($availCount && $availCount['count'] > 0) {
            $availabilitySet = true;
        }
        
        // Check for first booking
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ?");
        $stmt->execute([$expertProfileId]);
        $bookingCount = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($bookingCount && $bookingCount['count'] > 0) {
            $firstBooking = true;
        }
    }
}

// Dashboard stats
$totalEarnings = 0;
$activeLearners = 0;
$sessionsThisMonth = 0;

if ($expertProfileId) {
    // Get total earnings
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE b.expert_id = ? AND p.status = 'completed'
    ");
    $stmt->execute([$expertProfileId]);
    $totalEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Get active learners count
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT b.learner_id) as count
        FROM bookings b
        WHERE b.expert_id = ?
    ");
    $stmt->execute([$expertProfileId]);
    $activeLearners = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Get sessions this month
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM bookings
        WHERE expert_id = ? 
        AND MONTH(session_datetime) = MONTH(CURRENT_DATE())
        AND YEAR(session_datetime) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute([$expertProfileId]);
    $sessionsThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
}
?>

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8">
    <!-- Welcome Section -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome to Your Dashboard</h1>
        <p class="text-sm sm:text-base text-gray-600">Manage your profile, sessions, and grow your expert business</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-gray-600 text-xs sm:text-sm font-medium">Total Earnings</h3>
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900">₹<?php echo number_format($totalEarnings, 0); ?></p>
            <p class="text-xs sm:text-sm text-gray-500 mt-1"><?php echo $totalEarnings > 0 ? 'Great progress!' : 'Start your first session'; ?></p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-gray-600 text-xs sm:text-sm font-medium">Active Learners</h3>
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo $activeLearners; ?></p>
            <p class="text-xs sm:text-sm text-gray-500 mt-1"><?php echo $activeLearners > 0 ? 'Keep it up!' : 'No learners yet'; ?></p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-gray-600 text-xs sm:text-sm font-medium">Sessions This Month</h3>
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo $sessionsThisMonth; ?></p>
            <p class="text-xs sm:text-sm text-gray-500 mt-1"><?php echo $sessionsThisMonth > 0 ? 'This month' : 'Schedule your first session'; ?></p>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-gray-600 text-xs sm:text-sm font-medium">Profile Views</h3>
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900">0</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Coming soon</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=profile-setup" class="bg-white rounded-lg shadow p-4 sm:p-6 hover:shadow-lg transition">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-accent/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Complete Profile</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Update your expert details</p>
                    </div>
                </div>
            </a>

            <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=booking-management" class="bg-white rounded-lg shadow p-4 sm:p-6 hover:shadow-lg transition">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Manage Bookings</h3>
                        <p class="text-xs sm:text-sm text-gray-600">View session requests</p>
                    </div>
                </div>
            </a>

            <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=earnings" class="bg-white rounded-lg shadow p-4 sm:p-6 hover:shadow-lg transition">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">View Earnings</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Track your revenue</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Getting Started Guide -->
    <div class="bg-gradient-to-r from-accent/10 to-yellow-500/10 rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3 sm:mb-4">🚀 Getting Started as an Expert</h2>
        <div class="space-y-3">
            <!-- Step 1: Profile Setup -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $profileComplete ? 'bg-green-500' : 'bg-gray-300'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $profileComplete ? '✓' : '1'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=settings#profile" class="<?php echo $profileComplete ? 'text-green-600 hover:text-green-700' : 'text-accent hover:text-yellow-600'; ?>">
                            <?php echo $profileComplete ? 'Profile Setup Complete' : 'Complete Your Profile →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo $profileComplete ? 'Great! Your profile is set up' : 'Add your expertise and details'; ?></p>
                </div>
            </div>
            
            <!-- Step 2: KYC Verification -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $kycComplete ? 'bg-green-500' : 'bg-gray-300'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $kycComplete ? '✓' : '2'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=settings#kyc" class="<?php echo $kycComplete ? 'text-green-600 hover:text-green-700' : 'text-accent hover:text-yellow-600'; ?>">
                            <?php echo $kycComplete ? 'KYC Verification Complete' : 'Complete KYC Verification →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo $kycComplete ? 'You are verified to earn' : 'Verify your identity to start earning'; ?></p>
                </div>
            </div>
            
            <!-- Step 3: Set Availability -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $availabilitySet ? 'bg-green-500' : 'bg-gray-300'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $availabilitySet ? '✓' : '3'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=settings#availability" class="<?php echo $availabilitySet ? 'text-green-600 hover:text-green-700' : 'text-accent hover:text-yellow-600'; ?>">
                            <?php echo $availabilitySet ? 'Availability Set' : 'Set Your Availability →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo $availabilitySet ? 'Learners can see when you\'re free' : 'Let learners know when you\'re available'; ?></p>
                </div>
            </div>
            
            <!-- Step 4: First Booking -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $firstBooking ? 'bg-green-500' : 'bg-gray-300'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $firstBooking ? '✓' : '4'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">
                        <a href="<?php echo $BASE_PATH; ?>/index.php?panel=expert&page=booking-management" class="<?php echo $firstBooking ? 'text-green-600 hover:text-green-700' : 'text-accent hover:text-yellow-600'; ?>">
                            <?php echo $firstBooking ? 'First Booking Completed' : 'Accept Your First Booking →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo $firstBooking ? 'You\'ve started helping learners!' : 'Start helping learners achieve their goals'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
        </div>
        <div class="p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p class="text-gray-500">No recent activity</p>
            <p class="text-sm text-gray-400 mt-2">Your bookings and sessions will appear here</p>
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
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/footer.php'; ?>
