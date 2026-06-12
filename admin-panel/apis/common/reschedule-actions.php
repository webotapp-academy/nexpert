<?php
header('Content-Type: application/json');

// Load domain path configuration
$base_path = require_once dirname(dirname(dirname(__DIR__))) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(dirname(dirname(__DIR__))) . '/admin-panel/apis/connection/pdo.php';
require_once dirname(dirname(dirname(__DIR__))) . '/admin-panel/apis/connection/trust-helper.php';
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get input
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$action = $data['action'] ?? '';
$bookingId = $data['booking_id'] ?? null;

if (!$bookingId) {
    echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
    exit;
}

// Verify booking ownership
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

if ($userRole === 'learner' && $booking['learner_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to booking']);
    exit;
}

if ($userRole === 'expert' && $booking['expert_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to booking']);
    exit;
}

try {
    switch ($action) {
        case 'request_reschedule':
            $newDatetime = $data['new_datetime'] ?? '';
            $reason = $data['reason'] ?? '';

            if (empty($newDatetime)) {
                echo json_encode(['success' => false, 'message' => 'New datetime is required']);
                exit;
            }

            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET reschedule_requested = 1,
                    reschedule_new_datetime = ?,
                    reschedule_reason = ?,
                    reschedule_requested_by = ?,
                    reschedule_requested_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$newDatetime, $reason, $userRole, $bookingId]);

            // Log trust event
            TrustHelper::logEvent($pdo, 'reschedule_requested', $booking['expert_id'], $booking['learner_id'], [
                'booking_id' => $bookingId,
                'requested_by' => $userRole,
                'old_time' => $booking['session_datetime'],
                'new_time' => $newDatetime,
                'reason' => $reason
            ]);

            echo json_encode(['success' => true, 'message' => 'Reschedule request sent successfully']);
            break;

        case 'approve_reschedule':
            if ($booking['reschedule_requested'] != 1) {
                echo json_encode(['success' => false, 'message' => 'No active reschedule request found']);
                exit;
            }

            // Update booking with new time
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET session_datetime = reschedule_new_datetime,
                    reschedule_requested = 0,
                    reschedule_new_datetime = NULL,
                    reschedule_reason = NULL,
                    reschedule_requested_by = NULL,
                    reschedule_requested_at = NULL
                WHERE id = ?
            ");
            $stmt->execute([$bookingId]);

            // Log trust event
            TrustHelper::logEvent($pdo, 'reschedule_approved', $booking['expert_id'], $booking['learner_id'], [
                'booking_id' => $bookingId,
                'approved_by' => $userRole,
                'final_time' => $booking['reschedule_new_datetime']
            ]);

            echo json_encode(['success' => true, 'message' => 'Reschedule approved and updated']);
            break;

        case 'reject_reschedule':
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET reschedule_requested = 0,
                    reschedule_new_datetime = NULL,
                    reschedule_reason = NULL,
                    reschedule_requested_by = NULL,
                    reschedule_requested_at = NULL
                WHERE id = ?
            ");
            $stmt->execute([$bookingId]);

            // Log trust event
            TrustHelper::logEvent($pdo, 'reschedule_rejected', $booking['expert_id'], $booking['learner_id'], [
                'booking_id' => $bookingId,
                'rejected_by' => $userRole
            ]);

            echo json_encode(['success' => true, 'message' => 'Reschedule request rejected']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
