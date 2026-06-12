<?php
/**
 * One-time maintenance script to fix empty step_type values in workflow_steps
 * Run this once via browser: yoursite.com/admin-panel/apis/maintenance/fix-empty-step-types.php
 */

require_once '../connection/pdo.php';

try {
    $pdo->beginTransaction();
    
    // Update empty or NULL step_type values with intelligent defaults
    $updateQuery = "
        UPDATE workflow_steps 
        SET step_type = CASE 
            WHEN title LIKE '%assignment%' OR title LIKE '%project%' OR title LIKE '%quiz%' OR description LIKE '%submit%' THEN 'assignment_template'
            WHEN title LIKE '%milestone%' OR title LIKE '%week%' OR title LIKE '%deliverable%' THEN 'milestone'
            WHEN title LIKE '%task%' THEN 'task'
            WHEN title LIKE '%survey%' OR title LIKE '%feedback%' THEN 'survey'
            ELSE 'session'
        END 
        WHERE step_type IS NULL OR step_type = ''
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute();
    $affectedRows = $stmt->rowCount();
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Fixed $affectedRows workflow steps with empty step_type values",
        'affected_rows' => $affectedRows
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Fix Step Types Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
