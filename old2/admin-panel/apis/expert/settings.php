<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

// Start session to get user_id
session_start();

// Check if expert is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Fetch all settings for the expert
            $stmt = $pdo->prepare("
                SELECT 
                    u.email, u.phone,
                    ep.full_name, ep.tagline, ep.bio_short, ep.bio_full, 
                    ep.expertise_verticals, ep.credentials, ep.experience_years, ep.timezone,
                    ep.profile_photo,
                    eb.account_holder_name, eb.bank_name, eb.branch_name,
                    eb.account_number, eb.ifsc_code, eb.account_type
                FROM users u
                LEFT JOIN expert_profiles ep ON u.id = ep.user_id
                LEFT JOIN expert_bank_details eb ON u.id = eb.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Settings not found']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $settings]);
            break;

        case 'PUT':
            // Update settings
            $input = json_decode(file_get_contents('php://input'), true);
            $section = $input['section'] ?? '';

            switch ($section) {
                case 'profile':
                    // Update profile information
                    $stmt = $pdo->prepare("
                        UPDATE expert_profiles 
                        SET full_name = ?, tagline = ?, bio_short = ?, bio_full = ?, 
                            expertise_verticals = ?, credentials = ?, experience_years = ?, timezone = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $input['full_name'] ?? '',
                        $input['tagline'] ?? '',
                        $input['bio_short'] ?? '',
                        $input['bio_full'] ?? '',
                        isset($input['expertise_verticals']) ? json_encode($input['expertise_verticals']) : null,
                        $input['credentials'] ?? '',
                        $input['experience_years'] ?? null,
                        $input['timezone'] ?? 'UTC',
                        $userId
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                    break;

                case 'bank':
                    // Update bank details
                    $stmt = $pdo->prepare("
                        INSERT INTO expert_bank_details 
                        (user_id, account_holder_name, bank_name, branch_name, account_number, ifsc_code, account_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        account_holder_name = VALUES(account_holder_name),
                        bank_name = VALUES(bank_name),
                        branch_name = VALUES(branch_name),
                        account_number = VALUES(account_number),
                        ifsc_code = VALUES(ifsc_code),
                        account_type = VALUES(account_type)
                    ");
                    $stmt->execute([
                        $userId,
                        $input['account_holder_name'],
                        $input['bank_name'],
                        $input['branch_name'],
                        $input['account_number'],
                        $input['ifsc_code'],
                        $input['account_type']
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Bank details updated successfully']);
                    break;

                case 'password':
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!password_verify($input['current_password'], $user['password_hash'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                        exit;
                    }

                    // Update password
                    $new_password_hash = password_hash($input['new_password'], PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$new_password_hash, $userId]);

                    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
                    break;

                case 'notifications':
                    // Update notification preferences
                    $stmt = $pdo->prepare("
                        UPDATE expert_profiles 
                        SET notify_booking_email = ?, 
                            notify_payment_email = ?, 
                            notify_reminder_email = ?,
                            notify_marketing_email = ?,
                            notify_urgent_sms = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $input['notify_booking_email'] ? 1 : 0,
                        $input['notify_payment_email'] ? 1 : 0,
                        $input['notify_reminder_email'] ? 1 : 0,
                        $input['notify_marketing_email'] ? 1 : 0,
                        $input['notify_urgent_sms'] ? 1 : 0,
                        $userId
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Notification preferences updated successfully']);
                    break;

                case 'privacy':
                    // Update privacy settings
                    $stmt = $pdo->prepare("
                        UPDATE expert_profiles 
                        SET show_in_search = ?, 
                            show_email = ?, 
                            accept_bookings = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $input['show_in_search'] ? 1 : 0,
                        $input['show_email'] ? 1 : 0,
                        $input['accept_bookings'] ? 1 : 0,
                        $userId
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Privacy settings updated successfully']);
                    break;

                case 'two_factor':
                    // Update two-factor authentication
                    $stmt = $pdo->prepare("
                        UPDATE expert_profiles 
                        SET two_factor_enabled = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $input['enabled'] ? 1 : 0,
                        $userId
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Two-factor authentication updated successfully']);
                    break;

                case 'availability':
                    // Add availability slot
                    $stmt = $pdo->prepare("
                        INSERT INTO expert_availability 
                        (expert_id, day_of_week, start_time, end_time, is_active)
                        VALUES (?, ?, ?, ?, 1)
                    ");
                    $stmt->execute([
                        $userId,
                        $input['day_of_week'],
                        $input['start_time'],
                        $input['end_time']
                    ]);

                    echo json_encode(['success' => true, 'message' => 'Availability slot added successfully']);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid section']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Settings API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
