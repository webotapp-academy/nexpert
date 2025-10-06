<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get availability
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM expert_availability WHERE expert_id = ? AND is_active = 1 ORDER BY day_of_week, start_time");
        $stmt->execute([$expert_id]);
        $availability = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'availability' => $availability]);
    } catch (PDOException $e) {
        error_log('Get Availability Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching availability']);
    }
    exit;
}

// Create availability slots
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $slots = $data['slots'] ?? [];
    
    if (!$expert_id || empty($slots)) {
        echo json_encode(['success' => false, 'message' => 'Expert ID and slots are required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE expert_availability SET is_active = 0 WHERE expert_id = ?");
        $stmt->execute([$expert_id]);
        
        $stmt = $pdo->prepare("
            INSERT INTO expert_availability (expert_id, day_of_week, start_time, end_time)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($slots as $slot) {
            $stmt->execute([
                $expert_id,
                $slot['day_of_week'],
                $slot['start_time'],
                $slot['end_time']
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Availability updated successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Update Availability Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating availability']);
    }
    exit;
}
?>
