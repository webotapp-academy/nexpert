<?php
// For online deployment, set BASE_PATH to empty for root directory
$BASE_PATH = '';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Get user_id from session
$userId = $_SESSION['user_id'] ?? null;
$expertProfileId = null;

// Initialize all data variables
$profileData = [];
$kycData = [];
$bankData = [];
$pricingData = [];
$availabilityData = [];

if ($userId) {
    // Get expert profile data
    $stmt = $pdo->prepare("
        SELECT ep.*, u.email, u.phone 
        FROM expert_profiles ep
        JOIN users u ON u.id = ep.user_id
        WHERE ep.user_id = ?
    ");
    $stmt->execute([$userId]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profileData) {
        $expertProfileId = $profileData['id'];
        
        // Get expert pricing
        $stmt = $pdo->prepare("
            SELECT * FROM expert_pricing 
            WHERE expert_id = ? AND is_active = 1
        ");
        $stmt->execute([$userId]);
        $pricingData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get availability slots
        $stmt = $pdo->prepare("
            SELECT * FROM expert_availability 
            WHERE expert_id = ? AND is_active = 1
            ORDER BY day_of_week, start_time
        ");
        $stmt->execute([$userId]);
        $availabilityData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get KYC verification data
        $stmt = $pdo->prepare("
            SELECT * FROM expert_kyc_verification 
            WHERE expert_id = ?
        ");
        $stmt->execute([$userId]);
        $kycData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get bank details (check if separate table exists, otherwise use KYC data)
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM expert_bank_details 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $bankData = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // If table doesn't exist, use KYC data
            if ($kycData) {
                $bankData = [
                    'account_holder_name' => $kycData['account_holder_name'] ?? '',
                    'bank_name' => $kycData['bank_name'] ?? '',
                    'branch_name' => '',
                    'account_number' => $kycData['account_number'] ?? '',
                    'ifsc_code' => $kycData['ifsc_code'] ?? '',
                    'account_type' => $kycData['account_type'] ?? ''
                ];
            }
        }
    }
}

// Helper function to display ID document type
function formatIdType($type) {
    $types = [
        'passport' => 'Passport',
        'drivers_license' => 'Driver\'s License',
        'aadhaar' => 'Aadhaar Card',
        'pan' => 'PAN Card',
        'national_id' => 'National ID'
    ];
    return $types[$type] ?? ucfirst($type);
}

// Helper function for verification status
function getVerificationStatusBadge($status) {
    $badges = [
        'draft' => '<span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">Draft</span>',
        'pending' => '<span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Pending Review</span>',
        'approved' => '<span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Approved</span>',
        'rejected' => '<span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Rejected</span>'
    ];
    return $badges[$status] ?? $badges['draft'];
}

// Helper function for verification message
function getVerificationMessage($status) {
    $messages = [
        'draft' => 'Your KYC details are incomplete. Please complete your verification.',
        'pending' => 'Your KYC verification is currently under review. Verification typically takes 24-48 hours.',
        'approved' => 'Your KYC verification has been approved.',
        'rejected' => 'Your KYC verification was rejected. Please review the feedback and resubmit.'
    ];
    return $messages[$status] ?? $messages['draft'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Add Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Add SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Diagnostic script to log BASE_PATH -->
    <script>
        console.log('BASE_PATH:', '<?php echo $BASE_PATH; ?>');
        window.onerror = function(message, source, lineno, colno, error) {
            console.error('Global Error:', {
                message: message,
                source: source,
                lineno: lineno,
                colno: colno,
                error: error
            });
        };
    </script>
</head>
<body>
<?php
$page_title = "Expert Settings - Nexpert.ai";
$panel_type = "expert";

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Account Settings</h1>
            <p class="text-sm sm:text-base text-gray-600">Manage your profile, security, and preferences</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-8">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-3 sm:p-4 lg:sticky lg:top-24">
                    <nav class="space-y-1">
                        <button onclick="switchTab('profile')" id="tab-profile" class="settings-tab active w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile Information
                        </button>
                        <button onclick="switchTab('kyc')" id="tab-kyc" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                            KYC Details
                        </button>
                        <button onclick="switchTab('bank')" id="tab-bank" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Bank Details
                        </button>
                        <button onclick="switchTab('availability')" id="tab-availability" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Availability
                        </button>
                        <button onclick="switchTab('pricing')" id="tab-pricing" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pricing
                        </button>
                        <button onclick="switchTab('security')" id="tab-security" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Security
                        </button>
                        <button onclick="switchTab('notifications')" id="tab-notifications" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Notifications
                        </button>
                        <button onclick="switchTab('privacy')" id="tab-privacy" class="settings-tab w-full text-left px-4 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Privacy
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3">
                <!-- Profile Information Tab -->
                <div id="content-profile" class="tab-content bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8">
                    <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">Profile Information</h2>
                    
                    <form id="profileForm" onsubmit="return false;" method="post" action="javascript:void(0);">
                        <!-- Profile Photo -->
                        <div class="flex flex-col sm:flex-row items-center sm:space-x-6 mb-6 pb-6 border-b space-y-4 sm:space-y-0">
                            <div id="profilePhotoPreview" class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 overflow-hidden">
                                <?php if (!empty($profileData['profile_photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($profileData['profile_photo']); ?>" alt="Profile" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="text-center sm:text-left">
                                <input type="file" id="profilePhoto" name="profile_photo" accept="image/*" class="hidden">
                                <label for="profilePhoto" class="bg-accent text-white px-4 py-2 text-sm sm:text-base rounded-lg hover:bg-yellow-600 transition cursor-pointer inline-block">
                                    Change Photo
                                </label>
                                <p class="text-gray-600 text-xs sm:text-sm mt-2">JPG, PNG up to 5MB</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($profileData['full_name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Professional Title *</label>
                                    <input type="text" name="tagline" value="<?php echo htmlspecialchars($profileData['tagline'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($profileData['email'] ?? ''); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profileData['phone'] ?? ''); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Professional Bio *</label>
                                <textarea name="bio_full" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"><?php echo htmlspecialchars($profileData['bio_full'] ?? ''); ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                    <input type="text" name="timezone" value="<?php echo htmlspecialchars($profileData['timezone'] ?? 'UTC'); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                                    <select name="experience_years" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                        <option value="">Select experience</option>
                                        <option value="1-2" <?php echo (isset($profileData['experience_years']) && $profileData['experience_years'] >= 1 && $profileData['experience_years'] <= 2) ? 'selected' : ''; ?>>1-2 years</option>
                                        <option value="3-5" <?php echo (isset($profileData['experience_years']) && $profileData['experience_years'] >= 3 && $profileData['experience_years'] <= 5) ? 'selected' : ''; ?>>3-5 years</option>
                                        <option value="5-8" <?php echo (isset($profileData['experience_years']) && $profileData['experience_years'] >= 5 && $profileData['experience_years'] <= 8) ? 'selected' : ''; ?>>5-8 years</option>
                                        <option value="8-10" <?php echo (isset($profileData['experience_years']) && $profileData['experience_years'] >= 8 && $profileData['experience_years'] <= 10) ? 'selected' : ''; ?>>8-10 years</option>
                                        <option value="10+" <?php echo (isset($profileData['experience_years']) && $profileData['experience_years'] > 10) ? 'selected' : ''; ?>>10+ years</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button type="submit" class="w-full sm:w-auto bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold text-sm sm:text-base">Save Changes</button>
                            <button type="button" class="w-full sm:w-auto bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-sm sm:text-base">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- KYC Details Tab -->
                <div id="content-kyc" class="tab-content bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8 hidden">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
                        <h2 class="text-xl sm:text-2xl font-semibold text-gray-900">KYC Verification Details</h2>
                        <?php echo getVerificationStatusBadge($kycData['verification_status'] ?? 'draft'); ?>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-sm text-blue-700"><?php echo getVerificationMessage($kycData['verification_status'] ?? 'draft'); ?></p>
                    </div>

                    <?php if (!empty($kycData)): ?>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Full Legal Name</p>
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base"><?php echo htmlspecialchars($kycData['full_legal_name'] ?? 'Not provided'); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Date of Birth</p>
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base"><?php echo !empty($kycData['date_of_birth']) ? date('jS M Y', strtotime($kycData['date_of_birth'])) : 'Not provided'; ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Nationality</p>
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base"><?php echo htmlspecialchars($kycData['nationality'] ?? 'Not provided'); ?></p>
                                </div>
                                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">ID Type</p>
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base"><?php echo formatIdType($kycData['id_document_type'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($kycData['id_document_front_url']) || !empty($kycData['id_document_back_url'])): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Documents</h3>
                            <div class="space-y-3">
                                <?php if (!empty($kycData['id_document_front_url'])): ?>
                                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-10 h-10 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-gray-900">ID Document - Front</p>
                                            <p class="text-sm text-gray-600">Uploaded on <?php echo !empty($kycData['created_at']) ? date('d M Y', strtotime($kycData['created_at'])) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($kycData['id_document_front_url']); ?>" target="_blank" class="text-accent hover:text-yellow-600 text-sm font-semibold">View</a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($kycData['id_document_back_url'])): ?>
                                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-10 h-10 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="font-semibold text-gray-900">ID Document - Back</p>
                                            <p class="text-sm text-gray-600">Uploaded on <?php echo !empty($kycData['created_at']) ? date('d M Y', strtotime($kycData['created_at'])) : ''; ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($kycData['id_document_back_url']); ?>" target="_blank" class="text-accent hover:text-yellow-600 text-sm font-semibold">View</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="flex space-x-4">
                            <a href="?panel=expert&page=kyc" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">Edit KYC Details</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-600 mb-4">No KYC information found. Please complete your KYC verification.</p>
                        <a href="?panel=expert&page=kyc" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold inline-block">Complete KYC Verification</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Bank Details Tab -->
                <div id="content-bank" class="tab-content bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8 hidden">
                    <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">Bank Account Details</h2>
                    
                    <form id="bankForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name *</label>
                                <input type="text" name="account_holder_name" value="<?php echo htmlspecialchars($bankData['account_holder_name'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name *</label>
                                    <input type="text" name="bank_name" value="<?php echo htmlspecialchars($bankData['bank_name'] ?? ''); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name</label>
                                    <input type="text" name="branch_name" value="<?php echo htmlspecialchars($bankData['branch_name'] ?? ''); ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Number *</label>
                                    <input type="text" name="account_number" value="<?php echo !empty($bankData['account_number']) ? '****' . substr($bankData['account_number'], -4) : ''; ?>" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Account Number *</label>
                                    <input type="text" name="account_number_confirm" placeholder="Re-enter account number" class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm sm:text-base">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">IFSC Code *</label>
                                    <input type="text" name="ifsc_code" value="<?php echo htmlspecialchars($bankData['ifsc_code'] ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Type *</label>
                                    <select name="account_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                        <option value="">Select type</option>
                                        <option value="savings" <?php echo (isset($bankData['account_type']) && $bankData['account_type'] == 'savings') ? 'selected' : ''; ?>>Savings</option>
                                        <option value="current" <?php echo (isset($bankData['account_type']) && $bankData['account_type'] == 'current') ? 'selected' : ''; ?>>Current</option>
                                    </select>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> Ensure all bank details are correct. Incorrect details may delay your payouts.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button type="submit" class="w-full sm:w-auto bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold text-sm sm:text-base">Save Changes</button>
                            <button type="button" class="w-full sm:w-auto bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-sm sm:text-base">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Availability Tab -->
                <div id="content-availability" class="tab-content bg-white rounded-lg shadow-lg p-6 md:p-8 hidden">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Availability Settings</h2>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-sm text-blue-700">
                            Set your weekly availability schedule. Learners can only book sessions during your available time slots.
                        </p>
                    </div>

                    <?php if (!empty($availabilityData)): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Availability</h3>
                        <div class="space-y-3">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $groupedSlots = [];
                            foreach ($availabilityData as $slot) {
                                $day = $days[$slot['day_of_week']] ?? 'Unknown';
                                if (!isset($groupedSlots[$day])) {
                                    $groupedSlots[$day] = [];
                                }
                                $groupedSlots[$day][] = $slot;
                            }
                            
                            foreach ($days as $day):
                                if (isset($groupedSlots[$day])):
                            ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900"><?php echo $day; ?></p>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <?php foreach ($groupedSlots[$day] as $slot): ?>
                                        <span class="px-3 py-1 bg-accent text-white rounded-full text-sm">
                                            <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <button onclick="editDayAvailability('<?php echo $day; ?>')" class="ml-4 text-accent hover:text-yellow-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12 mb-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-600 mb-4">No availability schedule set yet.</p>
                        <p class="text-sm text-gray-500">Add your available time slots so learners can book sessions with you.</p>
                    </div>
                    <?php endif; ?>

                    <!-- Add New Availability Form -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Time Slot</h3>
                        <form id="availabilityForm">
                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Day of Week *</label>
                                    <select name="day_of_week" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                        <option value="">Select day</option>
                                        <option value="0">Monday</option>
                                        <option value="1">Tuesday</option>
                                        <option value="2">Wednesday</option>
                                        <option value="3">Thursday</option>
                                        <option value="4">Friday</option>
                                        <option value="5">Saturday</option>
                                        <option value="6">Sunday</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Time *</label>
                                    <input type="time" name="start_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time *</label>
                                    <input type="time" name="end_time" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <button type="submit" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">Add Time Slot</button>
                                <button type="button" onclick="clearAvailabilityForm()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">Clear</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pricing Tab -->
                <div id="content-pricing" class="tab-content bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8">
                    <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">Pricing Management</h2>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-sm text-blue-700">
                            Set your session pricing. You can create multiple pricing tiers for different session types.
                        </p>
                    </div>

                    <!-- Pricing Form -->
                    <form id="pricingForm" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Type</label>
                                <select name="pricing_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                    <option value="per_session">Per Session</option>
                                    <option value="package">Package</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                <input type="number" name="amount" placeholder="Session Price" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                    <option value="INR">INR (Indian Rupees)</option>
                                    <option value="USD">USD (US Dollars)</option>
                                    <option value="EUR">EUR (Euros)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Session Duration (minutes)</label>
                                <input type="number" name="duration_minutes" placeholder="Session Duration" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                                      placeholder="Describe your session type (optional)"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" 
                                   class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-2">
                            <label for="is_active" class="text-sm text-gray-700">Active Pricing</label>
                        </div>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button type="submit" class="w-full sm:w-auto bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold text-sm sm:text-base">
                                Add Pricing
                            </button>
                            <button type="button" onclick="resetPricingForm()" class="w-full sm:w-auto bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-sm sm:text-base">
                                Clear
                            </button>
                        </div>
                    </form>

                    <!-- Existing Pricing Table -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Pricing Tiers</h3>
                        <div id="current-pricing-container" class="space-y-4">
                            <?php if (!empty($pricingData)): ?>
                                <?php foreach ($pricingData as $pricing): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($pricing['pricing_type'] === 'per_session' ? 'Per Session' : 'Package'); ?> 
                                                - <?php echo htmlspecialchars($pricing['amount'] . ' ' . $pricing['currency']); ?>
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($pricing['duration_minutes'] . ' minutes'); ?>
                                                <?php echo $pricing['description'] ? ' | ' . htmlspecialchars($pricing['description']) : ''; ?>
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <span class="px-2 py-1 text-xs rounded-full <?php 
                                                echo $pricing['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                            ?>">
                                                <?php echo $pricing['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                            <button onclick="editPricing(<?php echo $pricing['id']; ?>)" class="text-blue-600 hover:underline text-sm">Edit</button>
                                            <button onclick="deletePricing(<?php echo $pricing['id']; ?>)" class="text-red-600 hover:underline text-sm">Delete</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div id="no-pricing-message" class="text-center text-gray-500 py-4">
                                    No pricing tiers set up yet. Add your first pricing tier.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div id="content-security" class="tab-content bg-white rounded-lg shadow-lg p-6 md:p-8 hidden">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Security Settings</h2>
                    
                    <!-- Change Password -->
                    <div class="mb-8 pb-8 border-b">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                        <form id="passwordForm">
                            <div class="space-y-4 max-w-md">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" name="current_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" name="new_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                </div>
                                <button type="submit" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">Update Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="mb-8 pb-8 border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h3>
                                <p class="text-gray-600 text-sm mt-1">Add an extra layer of security to your account</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="two-factor" <?php echo (!empty($profileData['two_factor_enabled']) && $profileData['two_factor_enabled']) ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-accent"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Active Sessions -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Sessions</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-10 h-10 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">Current Device</p>
                                        <p class="text-sm text-gray-600">Active Session</p>
                                    </div>
                                </div>
                                <span class="text-green-600 text-sm font-semibold">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div id="content-notifications" class="tab-content bg-white rounded-lg shadow-lg p-6 md:p-8 hidden">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Notification Preferences</h2>
                    
                    <div class="space-y-6">
                        <!-- Email Notifications -->
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Notifications</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">New Booking Requests</p>
                                        <p class="text-sm text-gray-600">Get notified when learners book sessions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="notify-booking" <?php echo (!empty($profileData['notify_booking_email']) && $profileData['notify_booking_email']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Payment Notifications</p>
                                        <p class="text-sm text-gray-600">Get notified about earnings and payouts</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="notify-payment" <?php echo (!empty($profileData['notify_payment_email']) && $profileData['notify_payment_email']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Session Reminders</p>
                                        <p class="text-sm text-gray-600">Reminders 1 hour before sessions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="notify-reminder" <?php echo (!empty($profileData['notify_reminder_email']) && $profileData['notify_reminder_email']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Marketing Emails</p>
                                        <p class="text-sm text-gray-600">Platform updates and promotions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="notify-marketing" <?php echo (!empty($profileData['notify_marketing_email']) && $profileData['notify_marketing_email']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Notifications -->
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">SMS Notifications</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Urgent Updates</p>
                                        <p class="text-sm text-gray-600">Critical booking changes and cancellations</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="notify-sms" <?php echo (!empty($profileData['notify_urgent_sms']) && $profileData['notify_urgent_sms']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">Save Preferences</button>
                    </div>
                </div>

                <!-- Privacy Tab -->
                <div id="content-privacy" class="tab-content bg-white rounded-lg shadow-lg p-6 md:p-8 hidden">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Privacy Settings</h2>
                    
                    <div class="space-y-6">
                        <!-- Profile Visibility -->
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Visibility</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Show Profile in Search</p>
                                        <p class="text-sm text-gray-600">Allow learners to find your profile</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="privacy-show-search" <?php echo (!empty($profileData['show_in_search']) && $profileData['show_in_search']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Show Email Address</p>
                                        <p class="text-sm text-gray-600">Display email on your public profile</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="privacy-show-email" <?php echo (!empty($profileData['show_email']) && $profileData['show_email']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Accept New Bookings</p>
                                        <p class="text-sm text-gray-600">Allow new learners to book sessions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="privacy-accept-bookings" <?php echo (!empty($profileData['accept_bookings']) && $profileData['accept_bookings']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Data Privacy -->
                        <div class="pb-6 border-b">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data & Privacy</h3>
                            <div class="space-y-3">
                                <button class="text-accent hover:text-yellow-600 font-semibold text-sm">Download My Data</button>
                                <p class="text-gray-600 text-sm">Request a copy of your personal data</p>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div>
                            <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-gray-900 font-medium mb-2">Deactivate Account</p>
                                <p class="text-sm text-gray-600 mb-4">Temporarily disable your account. You can reactivate it anytime.</p>
                                <button class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm font-semibold">Deactivate Account</button>
                            </div>
                        </div>

                        <button class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition font-semibold">Save Privacy Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

    <script>
        // Set BASE_PATH globally
        window.BASE_PATH = '<?php echo $BASE_PATH; ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="<?php echo $BASE_PATH; ?>/admin-panel/js/expert-settings.js"></script>
    <script>
        // Function to update pricing display
        function updatePricingDisplay(pricingData) {
            const container = document.getElementById('current-pricing-container');
            const noMessage = document.getElementById('no-pricing-message');

            // Clear existing content
            container.innerHTML = '';

            if (!pricingData || pricingData.length === 0) {
                const noDataDiv = document.createElement('div');
                noDataDiv.id = 'no-pricing-message';
                noDataDiv.className = 'text-center text-gray-500 py-4';
                noDataDiv.textContent = 'No pricing tiers set up yet. Add your first pricing tier.';
                container.appendChild(noDataDiv);
                return;
            }

            pricingData.forEach(pricing => {
                const div = document.createElement('div');
                div.className = 'bg-gray-50 p-4 rounded-lg flex justify-between items-center';
                div.innerHTML = `
                    <div>
                        <p class="font-semibold text-gray-900">
                            ${pricing.pricing_type === 'per_session' ? 'Per Session' : 'Package'} 
                            - ${pricing.amount} ${pricing.currency}
                        </p>
                        <p class="text-sm text-gray-600">
                            ${pricing.duration_minutes} minutes
                            ${pricing.description ? ' | ' + pricing.description : ''}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            pricing.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                        }">
                            ${pricing.is_active ? 'Active' : 'Inactive'}
                        </span>
                        <button onclick="editPricing(${pricing.id})" class="text-blue-600 hover:underline text-sm">Edit</button>
                        <button onclick="deletePricing(${pricing.id})" class="text-red-600 hover:underline text-sm">Delete</button>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pricingForm = document.getElementById('pricingForm');
            
            if (pricingForm) {
                // Prevent default form submission
                pricingForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Get form elements
                    const amountInput = pricingForm.querySelector('input[name="amount"]');
                    const durationInput = pricingForm.querySelector('input[name="duration_minutes"]');
                    const submitButton = pricingForm.querySelector('button[type="submit"]');
                    
                    // Validation
                    const amount = parseFloat(amountInput.value);
                    const duration = parseInt(durationInput.value);
                    
                    // Reset previous error states
                    amountInput.classList.remove('border-red-500');
                    durationInput.classList.remove('border-red-500');
                    
                    // Validate inputs
                    let isValid = true;
                    const validationErrors = [];
                    
                    if (isNaN(amount) || amount <= 0) {
                        isValid = false;
                        amountInput.classList.add('border-red-500');
                        validationErrors.push('Amount must be a positive number');
                    }
                    
                    if (isNaN(duration) || duration <= 0) {
                        isValid = false;
                        durationInput.classList.add('border-red-500');
                        validationErrors.push('Duration must be a positive number');
                    }
                    
                    // If validation fails, show errors and stop
                    if (!isValid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: validationErrors.map(err => `<p>${err}</p>`).join(''),
                            confirmButtonColor: '#3085d6'
                        });
                        return false;
                    }
                    
                    // Disable submit button
                    submitButton.disabled = true;
                    const originalText = submitButton.textContent;
                    submitButton.innerHTML = `
                        <span class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving Pricing...
                        </span>
                    `;
                    
                    // Prepare form data
                    const formData = {
                        pricing_type: pricingForm.querySelector('select[name="pricing_type"]').value,
                        amount: amountInput.value,
                        currency: pricingForm.querySelector('select[name="currency"]').value,
                        duration_minutes: durationInput.value,
                        description: pricingForm.querySelector('textarea[name="description"]').value || null,
                        is_active: pricingForm.querySelector('input[name="is_active"]').checked ? 1 : 0
                    };
                    
                    // Send data via fetch
                    fetch(`admin-panel/apis/expert/update-pricing.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(result => {
                        // Restore button
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        
                        if (result.success) {
                            // Success notification
                            Swal.fire({
                                icon: 'success',
                                title: 'Pricing Added Successfully',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            // Reload pricing data
                            return fetch(`admin-panel/apis/expert/get-pricing.php`);
                        } else {
                            throw new Error(result.message || 'Failed to add pricing tier');
                        }
                    })
                    .then(response => response.json())
                    .then(pricingData => {
                        if (pricingData.success) {
                            updatePricingDisplay(pricingData.data);
                        }
                        pricingForm.reset();
                    })
                    .catch(error => {
                        console.error('Pricing update error:', error);
                        
                        // Restore button
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        
                        // Error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Unable to save pricing. Please check your connection.',
                            confirmButtonColor: '#3085d6'
                        });
                    });
                    
                    // Prevent default navigation
                    return false;
                });
            }
        });

        // Placeholder functions for edit and delete (to be implemented later)
        function editPricing(pricingId) {
            Swal.fire({
                icon: 'info',
                title: 'Coming Soon',
                text: 'Edit functionality will be added in a future update.',
                confirmButtonColor: '#3085d6'
            });
        }

        function deletePricing(pricingId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to delete this pricing tier?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`admin-panel/apis/expert/delete-pricing.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ pricing_id: pricingId })
                        });

                        const data = await response.json();

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pricing Tier Deleted',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            // Reload pricing data
                            const pricingResponse = await fetch(`admin-panel/apis/expert/get-pricing.php`);
                            const pricingData = await pricingResponse.json();

                            if (pricingData.success) {
                                updatePricingDisplay(pricingData.data);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to delete pricing tier',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    } catch (error) {
                        console.error('Delete pricing error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Unable to delete pricing tier. Please check your connection.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }
            });
        }
    </script>

<!-- Minimal tab switching script -->
<script>
    console.log('Tab switching script loaded');
    function switchTab(tabName) {
        console.log('Switching tab:', tabName);
        // Hide all content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.classList.remove('active', 'bg-accent', 'text-white');
            tab.classList.add('text-gray-700');
        });
        
        // Show selected content
        const contentElement = document.getElementById('content-' + tabName);
        if (contentElement) {
            contentElement.classList.remove('hidden');
        } else {
            console.error('Content element not found:', 'content-' + tabName);
        }
        
        // Add active class to selected tab
        const activeTab = document.getElementById('tab-' + tabName);
        if (activeTab) {
            activeTab.classList.add('active', 'bg-accent', 'text-white');
            activeTab.classList.remove('text-gray-700');
        } else {
            console.error('Tab element not found:', 'tab-' + tabName);
        }
        
        // Update URL hash
        window.location.hash = tabName;
    }
    
    // Check URL hash on page load
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.substring(1);
        console.log('Initial hash:', hash);
        if (hash && document.getElementById('tab-' + hash)) {
            switchTab(hash);
        }
    });
</script>

<style>
    .settings-tab.active {
        background-color: #f59e0b;
        color: white;
    }
</style>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
