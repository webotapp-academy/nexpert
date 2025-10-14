<?php
// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Allow PUT method
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

// Log all incoming requests
error_log('Incoming Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Request Headers: ' . print_r(getallheaders(), true));
error_log('Request Body: ' . file_get_contents('php://input'));

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
    error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Session Role: ' . ($_SESSION['role'] ?? 'Not set'));

    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Ensure PUT method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        throw new Exception('Method Not Allowed');
    }

    // Parse PUT data
    $putData = json_decode(file_get_contents('php://input'), true);
    
    if (!$putData) {
        error_log('Invalid or empty request data');
        throw new Exception('Invalid request data');
    }

    error_log('Parsed PUT Data: ' . print_r($putData, true));

    $userId = $_SESSION['user_id'];
    $section = $putData['section'] ?? null;

    // Validate section
    $validSections = [
        'profile', 
        'bank', 
        'password', 
        'notifications', 
        'privacy', 
        'two_factor'
    ];
    
    if (!$section || !in_array($section, $validSections)) {
        error_log('Invalid section: ' . ($section ?? 'NULL'));
        throw new Exception('Invalid section');
    }

    // Database connection
    $pdo->beginTransaction();

    // Handle different sections
    switch ($section) {
        case 'profile':
            $stmt = $pdo->prepare("
                UPDATE expert_profiles 
                SET 
                    full_name = :full_name, 
                    tagline = :tagline, 
                    bio_full = :bio_full, 
                    timezone = :timezone, 
                    experience_years = :experience_years
                WHERE user_id = :user_id
            ");
            $result = $stmt->execute([
                ':full_name' => $putData['full_name'] ?? '',
                ':tagline' => $putData['tagline'] ?? '',
                ':bio_full' => $putData['bio_full'] ?? '',
                ':timezone' => $putData['timezone'] ?? 'UTC',
                ':experience_years' => $putData['experience_years'] ?? null,
                ':user_id' => $userId
            ]);

            error_log('Profile Update Result: ' . ($result ? 'Success' : 'Failure'));
            error_log('Affected Rows: ' . $stmt->rowCount());
            break;

        case 'bank':
            $stmt = $pdo->prepare("
                INSERT INTO expert_bank_details 
                (user_id, account_holder_name, bank_name, branch_name, account_number, ifsc_code, account_type)
                VALUES (:user_id, :account_holder_name, :bank_name, :branch_name, :account_number, :ifsc_code, :account_type)
                ON DUPLICATE KEY UPDATE 
                account_holder_name = :account_holder_name, 
                bank_name = :bank_name, 
                branch_name = :branch_name, 
                account_number = :account_number, 
                ifsc_code = :ifsc_code, 
                account_type = :account_type
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':account_holder_name' => $putData['account_holder_name'] ?? '',
                ':bank_name' => $putData['bank_name'] ?? '',
                ':branch_name' => $putData['branch_name'] ?? '',
                ':account_number' => $putData['account_number'] ?? '',
                ':ifsc_code' => $putData['ifsc_code'] ?? '',
                ':account_type' => $putData['account_type'] ?? ''
            ]);
            break;

        case 'password':
            // Validate current password and new password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($putData['current_password'], $user['password'])) {
                throw new Exception('Current password is incorrect');
            }

            // Validate new password
            $newPassword = $putData['new_password'] ?? '';
            if (strlen($newPassword) < 6) {
                throw new Exception('New password must be at least 6 characters long');
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            break;

        case 'notifications':
            $stmt = $pdo->prepare("
                UPDATE expert_profiles 
                SET 
                    notify_booking_email = :booking,
                    notify_payment_email = :payment,
                    notify_reminder_email = :reminder,
                    notify_marketing_email = :marketing,
                    notify_urgent_sms = :sms
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                ':booking' => $putData['notify_booking_email'] ?? false,
                ':payment' => $putData['notify_payment_email'] ?? false,
                ':reminder' => $putData['notify_reminder_email'] ?? false,
                ':marketing' => $putData['notify_marketing_email'] ?? false,
                ':sms' => $putData['notify_urgent_sms'] ?? false,
                ':user_id' => $userId
            ]);
            break;

        case 'privacy':
            $stmt = $pdo->prepare("
                UPDATE expert_profiles 
                SET 
                    show_in_search = :search,
                    show_email = :email,
                    accept_bookings = :bookings
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                ':search' => $putData['show_in_search'] ?? false,
                ':email' => $putData['show_email'] ?? false,
                ':bookings' => $putData['accept_bookings'] ?? false,
                ':user_id' => $userId
            ]);
            break;

        case 'two_factor':
            $stmt = $pdo->prepare("
                UPDATE expert_profiles 
                SET two_factor_enabled = :enabled 
                WHERE user_id = :user_id
            ");
            $stmt->execute([
                ':enabled' => $putData['enabled'] ?? false,
                ':user_id' => $userId
            ]);
            break;

        default:
            throw new Exception('Invalid section');
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => ucfirst($section) . ' settings updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction if needed
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error
    error_log('Settings Update Error: ' . $e->getMessage());
    error_log('User ID: ' . $userId);
    error_log('Section: ' . $section);
    error_log('Request Data: ' . print_r($putData, true));
    error_log('Full Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
