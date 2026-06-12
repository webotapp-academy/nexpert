<?php
ob_start();

session_start();

// Include database and domain path configuration
require_once dirname(dirname(__DIR__)) . '/apis/connection/pdo.php';
require_once dirname(dirname(__DIR__)) . '/apis/connection/domain-path.php';

ob_end_clean();

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please login as learner.'
    ]);
    exit;
}

$learnerId = $_SESSION['user_id'];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$stepId = isset($_POST['step_id']) ? intval($_POST['step_id']) : 0;
$progressId = isset($_POST['progress_id']) ? intval($_POST['progress_id']) : 0;

// Validate inputs
if (!$stepId || !$progressId) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: step_id or progress_id'
    ]);
    exit;
}

try {
    // Verify that this progress belongs to the logged-in learner
    $verifyStmt = $pdo->prepare("
        SELECT lp.id, lp.workflow_id 
        FROM learner_progress lp
        WHERE lp.id = ? AND lp.learner_id = ?
    ");
    $verifyStmt->execute([$progressId, $learnerId]);
    $progressRecord = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$progressRecord) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid progress ID or unauthorized access'
        ]);
        exit;
    }
    
    // Verify that this step belongs to the workflow
    $stepStmt = $pdo->prepare("
        SELECT id, step_type 
        FROM workflow_steps 
        WHERE id = ? AND workflow_id = ?
    ");
    $stepStmt->execute([$stepId, $progressRecord['workflow_id']]);
    $stepRecord = $stepStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stepRecord) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid step ID for this program'
        ]);
        exit;
    }
    
    // Check if this is a milestone (not an assignment)
    // Milestones can have step_type as 'milestone', empty string, or NULL
    $assignmentTypes = ['assignment_template', 'assignment', 'task', 'session', 'survey', 'followup'];
    if (in_array($stepRecord['step_type'], $assignmentTypes)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'This action is only for milestones. Use assignment submission for assignments.'
        ]);
        exit;
    }
    
    // Check if already completed
    $checkStmt = $pdo->prepare("
        SELECT status 
        FROM learner_step_progress 
        WHERE step_id = ? AND progress_id = ?
    ");
    $checkStmt->execute([$stepId, $progressId]);
    $existingProgress = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingProgress && $existingProgress['status'] === 'completed') {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'This milestone is already marked as complete'
        ]);
        exit;
    }
    
    // Insert or update step progress
    if ($existingProgress) {
        // Update existing record
        $updateStmt = $pdo->prepare("
            UPDATE learner_step_progress 
            SET status = 'completed',
                completed_at = NOW()
            WHERE step_id = ? AND progress_id = ?
        ");
        $updateStmt->execute([$stepId, $progressId]);
    } else {
        // Insert new record
        $insertStmt = $pdo->prepare("
            INSERT INTO learner_step_progress (step_id, progress_id, status, completed_at)
            VALUES (?, ?, 'completed', NOW())
        ");
        $insertStmt->execute([$stepId, $progressId]);
    }
    
    // Update overall progress percentage
    updateOverallProgress($progressId, $pdo);
    
    // Get updated progress
    $progressStmt = $pdo->prepare("
        SELECT progress_percentage 
        FROM learner_progress 
        WHERE id = ?
    ");
    $progressStmt->execute([$progressId]);
    $updatedProgress = $progressStmt->fetch(PDO::FETCH_ASSOC);
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Milestone marked as complete successfully!',
        'progress_percentage' => $updatedProgress['progress_percentage']
    ]);
    
} catch (PDOException $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

/**
 * Calculate and update overall progress percentage
 * Includes both milestones and assignments for 100% completion
 */
function updateOverallProgress($progressId, $pdo) {
    try {
        // Get workflow_id from progress record
        $stmt = $pdo->prepare("SELECT workflow_id FROM learner_progress WHERE id = ?");
        $stmt->execute([$progressId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$progress) return;
        
        $workflowId = $progress['workflow_id'];
        
        // Count total steps (milestones + assignments)
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workflow_steps WHERE workflow_id = ?");
        $stmt->execute([$workflowId]);
        $totalSteps = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        if ($totalSteps === 0) return;
        
        // Count completed steps (including both 'completed' and 'submitted' statuses)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed 
            FROM learner_step_progress lsp
            JOIN workflow_steps ws ON lsp.step_id = ws.id
            WHERE lsp.progress_id = ? 
            AND ws.workflow_id = ?
            AND lsp.status IN ('completed', 'submitted')
        ");
        $stmt->execute([$progressId, $workflowId]);
        $completedSteps = $stmt->fetch(PDO::FETCH_ASSOC)['completed'] ?? 0;
        
        // Calculate percentage
        $percentage = round(($completedSteps / $totalSteps) * 100, 2);
        
        // Update progress record
        $stmt = $pdo->prepare("UPDATE learner_progress SET progress_percentage = ? WHERE id = ?");
        $stmt->execute([$percentage, $progressId]);
        
    } catch (Exception $e) {
        error_log("Error updating overall progress: " . $e->getMessage());
    }
}
