<?php
require_once '../connection/pdo.php';
session_start();

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$expert_id = $_SESSION['user_id'];

// GET: Fetch all programs for this expert
if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                w.*,
                COUNT(DISTINCT ws.id) as milestone_count,
                COUNT(DISTINCT a.id) as assignment_count,
                COUNT(DISTINCT lp.learner_id) as learner_count
            FROM workflows w
            LEFT JOIN workflow_steps ws ON w.id = ws.workflow_id
            LEFT JOIN assignments a ON a.workflow_id = w.id
            LEFT JOIN learner_progress lp ON lp.workflow_id = w.id
            WHERE w.expert_id = ? AND w.is_active = 1
            GROUP BY w.id
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$expert_id]);
        $programs = $stmt->fetchAll();
        
        // Get stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT w.id) as total_programs,
                COUNT(DISTINCT lp.learner_id) as active_learners,
                COUNT(DISTINCT a.id) as total_assignments,
                IFNULL(AVG(lp.progress_percentage), 0) as avg_completion
            FROM workflows w
            LEFT JOIN learner_progress lp ON lp.workflow_id = w.id
            LEFT JOIN assignments a ON a.workflow_id = w.id
            WHERE w.expert_id = ? AND w.is_active = 1
        ");
        $stmt->execute([$expert_id]);
        $stats = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'programs' => $programs,
            'stats' => $stats
        ]);
    } catch (PDOException $e) {
        error_log('Get Programs Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch programs']);
    }
    exit;
}

// POST: Create new program
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $duration_weeks = $data['duration_weeks'] ?? null;
    $price = $data['price'] ?? null;
    $milestones = $data['milestones'] ?? [];
    $assignments = $data['assignments'] ?? [];
    $resources = $data['resources'] ?? [];
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Program title is required']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insert program (workflow)
        $stmt = $pdo->prepare("
            INSERT INTO workflows (expert_id, title, description, duration_weeks, goal_outcome, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $goal_outcome = "Complete structured program: " . $title;
        
        $stmt->execute([
            $expert_id,
            $title,
            $description,
            $duration_weeks,
            $goal_outcome
        ]);
        
        $program_id = $pdo->lastInsertId();
        
        // Insert milestones (workflow_steps)
        if (!empty($milestones)) {
            $stmt = $pdo->prepare("
                INSERT INTO workflow_steps (workflow_id, step_order, step_type, title, description, duration_minutes)
                VALUES (?, ?, 'milestone', ?, ?, ?)
            ");
            
            foreach ($milestones as $index => $milestone) {
                $week_num = $milestone['week'] ?? ($index + 1);
                $duration_minutes = $week_num * 7 * 24 * 60; // Convert weeks to minutes
                
                $stmt->execute([
                    $program_id,
                    $index + 1,
                    $milestone['title'] ?? '',
                    $milestone['deliverable'] ?? '',
                    $duration_minutes
                ]);
            }
        }
        
        // Insert assignments
        if (!empty($assignments)) {
            $stmt = $pdo->prepare("
                INSERT INTO assignments (workflow_id, expert_id, title, description, assignment_type, due_date, created_at)
                VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), NOW())
            ");
            
            foreach ($assignments as $index => $assignment) {
                $due_days = ($index + 1) * 7; // Each assignment due 1 week apart
                
                $stmt->execute([
                    $program_id,
                    $expert_id,
                    $assignment['title'] ?? '',
                    $assignment['description'] ?? '',
                    $assignment['type'] ?? 'project',
                    $due_days
                ]);
            }
        }
        
        // Insert resources as workflow step resources
        if (!empty($resources)) {
            $stmt = $pdo->prepare("
                INSERT INTO workflow_steps (workflow_id, step_order, step_type, title, description, resources)
                VALUES (?, ?, 'resource', ?, '', ?)
            ");
            
            $order = count($milestones) + 1;
            foreach ($resources as $resource) {
                $resource_json = json_encode([
                    'type' => $resource['type'] ?? 'document',
                    'url' => $resource['url'] ?? ''
                ]);
                
                $stmt->execute([
                    $program_id,
                    $order++,
                    $resource['title'] ?? 'Resource',
                    $resource_json
                ]);
            }
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Program created successfully',
            'program_id' => $program_id
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Create Program Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to create program: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete a program
if ($method === 'DELETE') {
    $program_id = $_GET['id'] ?? null;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        exit;
    }
    
    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT expert_id FROM workflows WHERE id = ?");
        $stmt->execute([$program_id]);
        $program = $stmt->fetch();
        
        if (!$program || $program['expert_id'] != $expert_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        // Soft delete
        $stmt = $pdo->prepare("UPDATE workflows SET is_active = 0, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$program_id]);
        
        echo json_encode(['success' => true, 'message' => 'Program deleted successfully']);
        
    } catch (PDOException $e) {
        error_log('Delete Program Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete program']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
