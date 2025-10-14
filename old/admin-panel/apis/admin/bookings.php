<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get all bookings
if ($method === 'GET') {
    $status = $_GET['status'] ?? null;
    $expert_id = $_GET['expert_id'] ?? null;
    $learner_id = $_GET['learner_id'] ?? null;
    
    try {
        $sql = "
            SELECT b.*, 
                   lp.full_name as learner_name,
                   ep.full_name as expert_name,
                   lu.email as learner_email,
                   eu.email as expert_email,
                   p.amount, p.status as payment_status
            FROM bookings b
            LEFT JOIN learner_profiles lp ON lp.user_id = b.learner_id
            LEFT JOIN expert_profiles ep ON ep.user_id = b.expert_id
            LEFT JOIN users lu ON lu.id = b.learner_id
            LEFT JOIN users eu ON eu.id = b.expert_id
            LEFT JOIN payments p ON p.booking_id = b.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND b.status = ?";
            $params[] = $status;
        }
        
        if ($expert_id) {
            $sql .= " AND b.expert_id = ?";
            $params[] = $expert_id;
        }
        
        if ($learner_id) {
            $sql .= " AND b.learner_id = ?";
            $params[] = $learner_id;
        }
        
        $sql .= " ORDER BY b.session_datetime DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (PDOException $e) {
        error_log('Admin Get Bookings Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching bookings']);
    }
    exit;
}

// Update booking status
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $booking_id = $input['booking_id'] ?? null;
    $new_status = $input['status'] ?? null;
    $admin_notes = $input['admin_notes'] ?? null;
    
    if (!$booking_id || !$new_status) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$new_status, $booking_id]);
        
        echo json_encode(['success' => true, 'message' => 'Booking status updated']);
    } catch (PDOException $e) {
        error_log('Admin Update Booking Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating booking']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>
