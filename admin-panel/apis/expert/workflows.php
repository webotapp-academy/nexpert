<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get workflows
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM workflows
            WHERE expert_id = ? AND is_active = 1
            ORDER BY created_at DESC
        ");
        $stmt->execute([$expert_id]);
        $workflows = $stmt->fetchAll();
        
        foreach ($workflows as &$workflow) {
            $stmt = $pdo->prepare("
                SELECT * FROM workflow_steps
                WHERE workflow_id = ?
                ORDER BY step_order ASC
            ");
            $stmt->execute([$workflow['id']]);
            $workflow['steps'] = $stmt->fetchAll();
        }
        
        echo json_encode(['success' => true, 'workflows' => $workflows]);
    } catch (PDOException $e) {
        error_log('Get Workflows Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching workflows']);
    }
    exit;
}

// Create workflow
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $title = $data['title'] ?? '';
    $goal_outcome = $data['goal_outcome'] ?? '';
    $duration_weeks = $data['duration_weeks'] ?? null;
    $description = $data['description'] ?? '';
    $steps = $data['steps'] ?? [];
    
    if (!$expert_id || !$title) {
        echo json_encode(['success' => false, 'message' => 'Expert ID and title are required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO workflows (expert_id, title, goal_outcome, duration_weeks, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$expert_id, $title, $goal_outcome, $duration_weeks, $description]);
        $workflow_id = $pdo->lastInsertId();
        
        if (!empty($steps)) {
            $stmt = $pdo->prepare("
                INSERT INTO workflow_steps (workflow_id, step_order, step_type, title, description, duration_minutes, resources)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($steps as $index => $step) {
                $resources = json_encode($step['resources'] ?? []);
                $stmt->execute([
                    $workflow_id,
                    $index + 1,
                    $step['step_type'] ?? 'session',
                    $step['title'] ?? '',
                    $step['description'] ?? '',
                    $step['duration_minutes'] ?? null,
                    $resources
                ]);
            }
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Workflow created successfully', 'id' => $workflow_id]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Create Workflow Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while creating workflow']);
    }
    exit;
}

// Update workflow
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $workflow_id = $data['workflow_id'] ?? null;
    $expert_id = $data['expert_id'] ?? null;
    $title = $data['title'] ?? '';
    $goal_outcome = $data['goal_outcome'] ?? '';
    $duration_weeks = $data['duration_weeks'] ?? null;
    $description = $data['description'] ?? '';
    
    if (!$workflow_id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Workflow ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify workflow belongs to expert
        $stmt = $pdo->prepare("SELECT expert_id FROM workflows WHERE id = ?");
        $stmt->execute([$workflow_id]);
        $workflow = $stmt->fetch();
        
        if (!$workflow || $workflow['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE workflows
            SET title = ?, goal_outcome = ?, duration_weeks = ?, description = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$title, $goal_outcome, $duration_weeks, $description, $workflow_id]);
        
        echo json_encode(['success' => true, 'message' => 'Workflow updated successfully']);
    } catch (PDOException $e) {
        error_log('Update Workflow Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating workflow']);
    }
    exit;
}

// Delete workflow
if ($method === 'DELETE') {
    $workflow_id = $_GET['id'] ?? null;
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$workflow_id || !$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Workflow ID and Expert ID are required']);
        exit;
    }
    
    try {
        // Verify workflow belongs to expert
        $stmt = $pdo->prepare("SELECT expert_id FROM workflows WHERE id = ?");
        $stmt->execute([$workflow_id]);
        $workflow = $stmt->fetch();
        
        if (!$workflow || $workflow['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE workflows SET is_active = 0 WHERE id = ?");
        $stmt->execute([$workflow_id]);
        
        echo json_encode(['success' => true, 'message' => 'Workflow deleted successfully']);
    } catch (PDOException $e) {
        error_log('Delete Workflow Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting workflow']);
    }
    exit;
}
?>
