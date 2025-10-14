<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request
// TODO: Add session validation and derive expert_id from authenticated user

$method = $_SERVER['REQUEST_METHOD'];

// Get learners list with progress OR single learner details
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    $learner_id = $_GET['learner_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        // Get single learner details if learner_id provided
        if ($learner_id) {
            // Learner info
            $stmt = $pdo->prepare("
                SELECT lp.*, prog.*
                FROM learner_profiles lp
                LEFT JOIN learner_progress prog ON prog.learner_id = lp.user_id AND prog.expert_id = ?
                WHERE lp.user_id = ?
            ");
            $stmt->execute([$expert_id, $learner_id]);
            $learner = $stmt->fetch();
            
            if (!$learner) {
                echo json_encode(['success' => false, 'message' => 'Learner not found']);
                exit;
            }
            
            // Session history
            $stmt = $pdo->prepare("
                SELECT * FROM bookings
                WHERE learner_id = ? AND expert_id = ?
                ORDER BY session_datetime DESC
            ");
            $stmt->execute([$learner_id, $expert_id]);
            $learner['sessions'] = $stmt->fetchAll();
            
            // Assignments
            $stmt = $pdo->prepare("
                SELECT a.*, s.submission_text, s.submitted_at, s.feedback
                FROM assignments a
                LEFT JOIN assignment_submissions s ON s.assignment_id = a.id
                WHERE a.learner_id = ? AND a.expert_id = ?
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$learner_id, $expert_id]);
            $learner['assignments'] = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'learner' => $learner]);
        } else {
            // Get all learners list with progress
            $stmt = $pdo->prepare("
                SELECT 
                    prog.learner_id,
                    prog.progress_percentage,
                    prog.total_sessions,
                    prog.completed_sessions,
                    prog.total_assignments,
                    prog.completed_assignments,
                    prog.expert_notes,
                    prog.last_interaction_date,
                    w.title as workflow_title,
                    lp.full_name,
                    lp.profile_photo,
                    COUNT(DISTINCT b.id) as total_bookings,
                    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings
                FROM learner_progress prog
                JOIN learner_profiles lp ON lp.user_id = prog.learner_id
                LEFT JOIN workflows w ON w.id = prog.workflow_id
                LEFT JOIN bookings b ON b.learner_id = prog.learner_id AND b.expert_id = prog.expert_id
                WHERE prog.expert_id = ?
                GROUP BY prog.learner_id, prog.progress_percentage, prog.total_sessions, 
                         prog.completed_sessions, prog.total_assignments, prog.completed_assignments,
                         prog.expert_notes, prog.last_interaction_date, w.title, 
                         lp.full_name, lp.profile_photo
                ORDER BY prog.last_interaction_date DESC
            ");
            $stmt->execute([$expert_id]);
            $learners = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'learners' => $learners]);
        }
    } catch (PDOException $e) {
        error_log('Learners API Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching learner data']);
    }
    exit;
}

// Update learner progress/notes
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $learner_id = $data['learner_id'] ?? null;
    $expert_notes = $data['expert_notes'] ?? '';
    
    if (!$expert_id || !$learner_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID and Learner ID are required']);
        exit;
    }
    
    try {
        // Verify expert owns this learner relationship
        $stmt = $pdo->prepare("
            SELECT id FROM learner_progress
            WHERE expert_id = ? AND learner_id = ?
        ");
        $stmt->execute([$expert_id, $learner_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE learner_progress
            SET expert_notes = ?, updated_at = NOW()
            WHERE expert_id = ? AND learner_id = ?
        ");
        $stmt->execute([$expert_notes, $expert_id, $learner_id]);
        
        echo json_encode(['success' => true, 'message' => 'Notes updated successfully']);
    } catch (PDOException $e) {
        error_log('Update Learner Notes Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating notes']);
    }
    exit;
}

// Add follow-up reminder
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $learner_id = $data['learner_id'] ?? null;
    $reminder_datetime = $data['reminder_datetime'] ?? null;
    $message = $data['message'] ?? '';
    
    if (!$expert_id || !$learner_id || !$reminder_datetime) {
        echo json_encode(['success' => false, 'message' => 'Expert ID, Learner ID, and reminder datetime are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO follow_up_reminders (expert_id, learner_id, reminder_datetime, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$expert_id, $learner_id, $reminder_datetime, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Reminder added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log('Add Reminder Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while adding reminder']);
    }
    exit;
}
?>
