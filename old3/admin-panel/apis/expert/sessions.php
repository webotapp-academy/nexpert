<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Add session notes
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'notes') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = $data['booking_id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $notes = $data['notes'] ?? '';
    
    if (!$booking_id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify booking belongs to expert
        $stmt = $pdo->prepare("SELECT expert_id FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if (!$booking || $booking['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE bookings SET session_notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$notes, $booking_id]);
        
        echo json_encode(['success' => true, 'message' => 'Notes saved successfully']);
    } catch (PDOException $e) {
        error_log('Add Session Notes Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while saving notes']);
    }
    exit;
}

// Add session resource
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'resource') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = $data['booking_id'] ?? null;
    $uploaded_by = $data['uploaded_by'] ?? null;
    $resource_type = $data['resource_type'] ?? 'document';
    $file_url = $data['file_url'] ?? '';
    $file_name = $data['file_name'] ?? '';
    $description = $data['description'] ?? '';
    
    if (!$booking_id || !$uploaded_by) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and uploader ID are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO session_resources (booking_id, uploaded_by, resource_type, file_url, file_name, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$booking_id, $uploaded_by, $resource_type, $file_url, $file_name, $description]);
        
        echo json_encode(['success' => true, 'message' => 'Resource added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log('Add Session Resource Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while adding resource']);
    }
    exit;
}

// Create assignment
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'assignment') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = $data['booking_id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $learner_id = $data['learner_id'] ?? null;
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $due_date = $data['due_date'] ?? null;
    $resources = json_encode($data['resources'] ?? []);
    
    if (!$expert_id || !$learner_id || !$title) {
        echo json_encode(['success' => false, 'message' => 'Expert ID, Learner ID, and title are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO assignments (booking_id, expert_id, learner_id, title, description, due_date, resources)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$booking_id, $expert_id, $learner_id, $title, $description, $due_date, $resources]);
        
        echo json_encode(['success' => true, 'message' => 'Assignment created successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log('Create Assignment Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while creating assignment']);
    }
    exit;
}

// Complete session
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = $data['booking_id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $recording_url = $data['recording_url'] ?? null;
    
    if (!$booking_id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify booking belongs to expert
        $stmt = $pdo->prepare("SELECT expert_id FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();
        
        if (!$booking || $booking['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'completed', recording_url = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$recording_url, $booking_id]);
        
        echo json_encode(['success' => true, 'message' => 'Session completed successfully']);
    } catch (PDOException $e) {
        error_log('Complete Session Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while completing session']);
    }
    exit;
}
?>
