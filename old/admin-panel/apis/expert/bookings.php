<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get bookings
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $sql = "
            SELECT b.*, 
                   lp.full_name as learner_name, 
                   lp.profile_photo as learner_photo,
                   w.title as workflow_title,
                   p.amount, p.status as payment_status
            FROM bookings b
            LEFT JOIN learner_profiles lp ON lp.user_id = b.learner_id
            LEFT JOIN workflows w ON w.id = b.workflow_id
            LEFT JOIN payments p ON p.booking_id = b.id
            WHERE b.expert_id = ?
        ";
        
        $params = [$expert_id];
        
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY b.session_datetime DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (PDOException $e) {
        error_log('Bookings API Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching bookings']);
    }
    exit;
}

// Update booking status (approve/reject/reschedule/cancel)
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = $data['booking_id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $status = $data['status'] ?? null;
    $reason = $data['reason'] ?? null;
    $new_datetime = $data['new_datetime'] ?? null;
    
    if (!$booking_id || !$expert_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Booking ID, Expert ID, and status are required']);
        exit;
    }
    
    try {
        // Verify booking belongs to this expert
        $stmt = $pdo->prepare("SELECT expert_id FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if (!$booking || $booking['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        if ($status === 'rescheduled' && $new_datetime) {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = ?, session_datetime = ?, reschedule_reason = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $new_datetime, $reason, $booking_id]);
        } elseif ($status === 'cancelled') {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = ?, cancel_reason = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $reason, $booking_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $booking_id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
    } catch (PDOException $e) {
        error_log('Update Booking Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating booking']);
    }
    exit;
}
?>
