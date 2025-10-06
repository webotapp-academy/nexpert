<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get pricing
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM expert_pricing WHERE expert_id = ? ORDER BY created_at DESC");
        $stmt->execute([$expert_id]);
        $pricing = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'pricing' => $pricing]);
    } catch (PDOException $e) {
        error_log('Get Pricing Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching pricing']);
    }
    exit;
}

// Create pricing
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $pricing_type = $data['pricing_type'] ?? 'per_session';
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'USD';
    $duration_minutes = $data['duration_minutes'] ?? null;
    $sessions_count = $data['sessions_count'] ?? null;
    $description = $data['description'] ?? '';
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO expert_pricing (expert_id, pricing_type, amount, currency, 
                duration_minutes, sessions_count, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $expert_id, $pricing_type, $amount, $currency,
            $duration_minutes, $sessions_count, $description
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Pricing added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log('Create Pricing Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while adding pricing']);
    }
    exit;
}

// Update pricing
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $is_active = $data['is_active'] ?? 1;
    
    if (!$id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Pricing ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify pricing belongs to this expert
        $stmt = $pdo->prepare("SELECT expert_id FROM expert_pricing WHERE id = ?");
        $stmt->execute([$id]);
        $pricing = $stmt->fetch();
        
        if (!$pricing || $pricing['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE expert_pricing SET is_active = ? WHERE id = ?");
        $stmt->execute([$is_active, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Pricing updated successfully']);
    } catch (PDOException $e) {
        error_log('Update Pricing Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating pricing']);
    }
    exit;
}

// Delete pricing
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Pricing ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify pricing belongs to this expert
        $stmt = $pdo->prepare("SELECT expert_id FROM expert_pricing WHERE id = ?");
        $stmt->execute([$id]);
        $pricing = $stmt->fetch();
        
        if (!$pricing || $pricing['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM expert_pricing WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Pricing deleted successfully']);
    } catch (PDOException $e) {
        error_log('Delete Pricing Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting pricing']);
    }
    exit;
}
?>
