<?php
// Clean output buffer to prevent any extra output
ob_start();

// Load domain path configuration
require_once dirname(dirname(dirname(__DIR__))) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(dirname(__DIR__)) . '/apis/connection/pdo.php';

// Clear any output that might have been generated
ob_end_clean();

// Now set the header
header('Content-Type: application/json');

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $learnerId = $_SESSION['user_id'];
    $stepId = $_POST['step_id'] ?? null;
    $progressId = $_POST['progress_id'] ?? null;
    $action = $_POST['action'] ?? 'submit'; // 'submit' or 'draft'
    $submissionContent = $_POST['submission_content'] ?? '';
    
    if (!$stepId || !$progressId) {
        throw new Exception('Missing required parameters');
    }
    
    // Verify that this progress record belongs to the learner
    $stmt = $pdo->prepare("SELECT id FROM learner_progress WHERE id = ? AND learner_id = ?");
    $stmt->execute([$progressId, $learnerId]);
    if (!$stmt->fetch()) {
        throw new Exception('Invalid progress record');
    }
    
    // Handle file upload if present
    $fileUrl = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/assignments/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
        $fileName = 'assignment_' . $stepId . '_' . $learnerId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Validate file size (10MB max)
        if ($_FILES['submission_file']['size'] > 10 * 1024 * 1024) {
            throw new Exception('File size exceeds 10MB limit');
        }
        
        // Validate file type
        $allowedExtensions = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed: PDF, DOC, DOCX, ZIP, Images');
        }
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $filePath)) {
            $fileUrl = 'uploads/assignments/' . $fileName;
        } else {
            throw new Exception('Failed to upload file');
        }
    }
    
    // Determine status based on action
    $status = ($action === 'submit') ? 'submitted' : 'in_progress';
    $submittedAt = ($action === 'submit') ? date('Y-m-d H:i:s') : null;
    
    // Check if step progress already exists
    $stmt = $pdo->prepare("
        SELECT id FROM learner_step_progress 
        WHERE step_id = ? AND progress_id = ?
    ");
    $stmt->execute([$stepId, $progressId]);
    $existingProgress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingProgress) {
        // Update existing record
        $updateFields = [
            'submitted_content = ?',
            'status = ?'
        ];
        $params = [$submissionContent, $status];
        
        if ($fileUrl) {
            $updateFields[] = 'submitted_file_url = ?';
            $params[] = $fileUrl;
        }
        
        if ($submittedAt) {
            $updateFields[] = 'submitted_at = ?';
            $params[] = $submittedAt;
        }
        
        $updateFields[] = 'updated_at = NOW()';
        $params[] = $existingProgress['id'];
        
        $sql = "UPDATE learner_step_progress SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert new record
        $stmt = $pdo->prepare("
            INSERT INTO learner_step_progress 
            (step_id, progress_id, submitted_content, submitted_file_url, status, submitted_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $stepId,
            $progressId,
            $submissionContent,
            $fileUrl,
            $status,
            $submittedAt
        ]);
    }
    
    // Update overall progress percentage if submission is final
    if ($action === 'submit') {
        updateOverallProgress($pdo, $progressId);
    }
    
    // Clean output and send JSON response
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => $action === 'submit' ? 'Assignment submitted successfully!' : 'Draft saved successfully!',
        'status' => $status
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("Assignment submission error: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

/**
 * Update overall progress percentage based on completed steps
 */
function updateOverallProgress($pdo, $progressId) {
    try {
        // Get workflow_id from progress record
        $stmt = $pdo->prepare("SELECT workflow_id FROM learner_progress WHERE id = ?");
        $stmt->execute([$progressId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$progress) return;
        
        $workflowId = $progress['workflow_id'];
        
        // Count total steps
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM workflow_steps WHERE workflow_id = ?");
        $stmt->execute([$workflowId]);
        $totalSteps = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        if ($totalSteps === 0) return;
        
        // Count completed steps
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
        $stmt = $pdo->prepare("UPDATE learner_progress SET progress_percentage = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$percentage, $progressId]);
        
    } catch (Exception $e) {
        error_log("Error updating overall progress: " . $e->getMessage());
    }
}
