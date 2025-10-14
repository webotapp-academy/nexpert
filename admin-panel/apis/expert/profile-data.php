<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log incoming request
error_log('Incoming Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Request Headers: ' . print_r(getallheaders(), true));

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Default profile data structure
$defaultProfileData = [
    'full_name' => '',
    'tagline' => '',
    'bio_full' => '',
    'timezone' => 'UTC',
    'experience_years' => null,
    'profile_photo' => null,
    'email' => '',
    'phone' => '',
    'show_in_search' => false,
    'show_email' => false,
    'accept_bookings' => true,
    'notify_booking_email' => true,
    'notify_payment_email' => true,
    'notify_reminder_email' => true,
    'notify_marketing_email' => false,
    'notify_urgent_sms' => false,
    'two_factor_enabled' => false
];

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
    error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Session Role: ' . ($_SESSION['role'] ?? 'Not set'));

    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access',
        'data' => $defaultProfileData
    ]);
    exit;
}

try {
    // Ensure GET method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        throw new Exception('Method Not Allowed');
    }

    $userId = $_SESSION['user_id'];
    error_log('Fetching profile data for User ID: ' . $userId);

    // Fetch profile data with a flexible query
    $stmt = $pdo->prepare("
        SELECT 
            ep.full_name, 
            ep.tagline, 
            ep.bio_full, 
            ep.timezone, 
            ep.experience_years, 
            ep.profile_photo,
            u.email,
            u.phone
        FROM expert_profiles ep
        JOIN users u ON u.id = ep.user_id
        WHERE ep.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log fetched profile data
    error_log('Fetched Profile Data: ' . print_r($profileData, true));

    // If no profile data found, use default
    if (!$profileData) {
        error_log('No profile data found for User ID: ' . $userId);
        $profileData = $defaultProfileData;

        // Attempt to insert a default profile
        $insertStmt = $pdo->prepare("
            INSERT INTO expert_profiles 
            (user_id, full_name, tagline, bio_full, timezone, experience_years) 
            VALUES (:user_id, '', '', '', 'UTC', NULL)
        ");
        $insertStmt->execute([':user_id' => $userId]);
    }

    // Merge fetched data with default to ensure all keys exist
    $profileData = array_merge($defaultProfileData, $profileData);

    // Normalize profile photo path
    if (!empty($profileData['profile_photo'])) {
        // Extensive logging for photo path
        error_log('Original Profile Photo Path: ' . $profileData['profile_photo']);
        
        // Remove any leading slashes
        $photo = ltrim($profileData['profile_photo'], '/');
        
        // Remove prefixes if present
        $photo = preg_replace('/^(uploads\/profiles\/|nexpert\/uploads\/profiles\/)/', '', $photo);
        
        // Check if the file exists
        $full_path = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/' . $photo;
        
        error_log('Normalized Photo Path: ' . $photo);
        error_log('Full Filesystem Path: ' . $full_path);
        error_log('File Exists: ' . (file_exists($full_path) ? 'Yes' : 'No'));

        if (file_exists($full_path)) {
            // Always use uploads/profiles/ prefix with dynamic BASE_PATH
            $profileData['profile_photo'] = BASE_PATH . '/uploads/profiles/' . $photo;
            error_log('Final Resolved Photo Path: ' . $profileData['profile_photo']);
        } else {
            // If file doesn't exist, set to null
            $profileData['profile_photo'] = null;
            error_log('Profile photo file not found, setting to null');
        }
    } else {
        $profileData['profile_photo'] = null;
    }

    // Return success response with extensive logging
    error_log('Final Profile Data Before Response: ' . print_r($profileData, true));
    
    echo json_encode([
        'success' => true,
        'data' => $profileData
    ]);

} catch (PDOException $e) {
    // Log the error
    error_log('Profile Data API PDO Error: ' . $e->getMessage());
    error_log('User ID: ' . $userId);
    error_log('Trace: ' . $e->getTraceAsString());

    // Return error response with default data
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => $defaultProfileData
    ]);
} catch (Exception $e) {
    // Log other unexpected errors
    error_log('Profile Data API Unexpected Error: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());

    // Return error response with default data
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected server error',
        'data' => $defaultProfileData
    ]);
}
