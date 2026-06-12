<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

// Check if expert is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$expertId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $bookingId = $data['booking_id'] ?? null;
    $action = $data['action'] ?? null;
    
    if (!$bookingId || !$action) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Booking ID and action are required']);
        exit;
    }
    
    if (!in_array($action, ['accept', 'decline'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    // Get booking with reschedule request
    $stmt = $pdo->prepare("
        SELECT b.*, 
               lp.full_name as learner_name, 
               u.email as learner_email,
               ep.full_name as expert_name
        FROM bookings b
        LEFT JOIN users u ON b.learner_id = u.id
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        LEFT JOIN expert_profiles ep ON b.expert_id = ep.user_id
        WHERE b.id = ? AND b.expert_id = ? AND b.reschedule_requested = 1
    ");
    $stmt->execute([$bookingId, $expertId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reschedule request not found']);
        exit;
    }
    
    if ($action === 'accept') {
        // Update booking with new datetime
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET session_datetime = reschedule_new_datetime,
                reschedule_requested = 0,
                reschedule_accepted = 1,
                reschedule_accepted_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
        
        $message = 'Reschedule request accepted successfully';
        $emailSubject = 'Reschedule Request Accepted';
        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #059669;'>Reschedule Request Accepted</h2>
                <p>Hello " . htmlspecialchars($booking['learner_name']) . ",</p>
                <p>Great news! " . htmlspecialchars($booking['expert_name']) . " has accepted your reschedule request.</p>
                
                <div style='background-color: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #065f46;'>New Session Time</h3>
                    <p><strong>Date & Time:</strong> " . date('l, F j, Y \a\t g:i A', strtotime($booking['reschedule_new_datetime'])) . " IST</p>
                </div>
                
                <p>Please make sure to join the session at the new scheduled time.</p>
                <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
            </div>
        ";
        
    } else {
        // Decline - just clear the reschedule request
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET reschedule_requested = 0,
                reschedule_declined = 1,
                reschedule_declined_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
        
        $message = 'Reschedule request declined';
        $emailSubject = 'Reschedule Request Declined';
        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc2626;'>Reschedule Request Declined</h2>
                <p>Hello " . htmlspecialchars($booking['learner_name']) . ",</p>
                <p>" . htmlspecialchars($booking['expert_name']) . " has declined your reschedule request.</p>
                
                <div style='background-color: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #92400e;'>Original Session Time Remains</h3>
                    <p><strong>Date & Time:</strong> " . date('l, F j, Y \a\t g:i A', strtotime($booking['session_datetime'])) . " IST</p>
                </div>
                
                <p>Please attend the session at the originally scheduled time, or contact the expert for alternative arrangements.</p>
                <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
            </div>
        ";
    }
    
    // Send email notification to learner
    try {
        require_once __DIR__ . '/../connection/email-helper.php';
        $emailHelper = new EmailHelper($pdo);
        $emailHelper->sendEmail($booking['learner_email'], $emailSubject, $emailBody);
    } catch (Exception $e) {
        error_log("Reschedule email notification failed: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (PDOException $e) {
    error_log("Reschedule API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
