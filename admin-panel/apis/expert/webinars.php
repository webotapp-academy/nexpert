<?php
/**
 * Expert Webinars API
 * Handles CRUD operations for webinars
 */

header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$expert_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetWebinars($pdo, $expert_id);
            break;
            
        case 'POST':
            handleCreateWebinar($pdo, $expert_id);
            break;
            
        case 'PUT':
            handleUpdateWebinar($pdo, $expert_id);
            break;
            
        case 'DELETE':
            handleDeleteWebinar($pdo, $expert_id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Webinars API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get all webinars for expert
 */
function handleGetWebinars($pdo, $expert_id) {
    $query = "
        SELECT 
            w.*,
            COUNT(DISTINCT wr.id) as total_registrations,
            SUM(CASE WHEN wr.attended = 1 THEN 1 ELSE 0 END) as total_attended,
            AVG(wr.feedback_rating) as avg_rating
        FROM webinars w
        LEFT JOIN webinar_registrations wr ON w.id = wr.webinar_id
        WHERE w.expert_id = ?
        GROUP BY w.id
        ORDER BY w.webinar_date DESC, w.webinar_time DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$expert_id]);
    $webinars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $stats = [
        'total_webinars' => count($webinars),
        'upcoming' => 0,
        'completed' => 0,
        'total_registrations' => 0
    ];
    
    foreach ($webinars as &$webinar) {
        // Convert price to number
        $webinar['price_inr'] = (float) $webinar['price_inr'];
        $webinar['duration_hours'] = (float) $webinar['duration_hours'];
        $webinar['total_registrations'] = (int) $webinar['total_registrations'];
        $webinar['total_attended'] = (int) $webinar['total_attended'];
        $webinar['avg_rating'] = $webinar['avg_rating'] ? round((float) $webinar['avg_rating'], 1) : null;
        
        // Update stats
        if ($webinar['status'] === 'upcoming') {
            $stats['upcoming']++;
        } else if ($webinar['status'] === 'completed') {
            $stats['completed']++;
        }
        $stats['total_registrations'] += $webinar['total_registrations'];
    }
    
    echo json_encode([
        'success' => true,
        'webinars' => $webinars,
        'stats' => $stats
    ]);
}

/**
 * Create new webinar
 */
function handleCreateWebinar($pdo, $expert_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['title', 'description', 'date', 'time', 'duration', 'price'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            return;
        }
    }
    
    // Validate date is in future
    $webinarDateTime = $data['date'] . ' ' . $data['time'];
    if (strtotime($webinarDateTime) <= time()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Webinar date and time must be in the future']);
        return;
    }
    
    // Validate duration
    if ($data['duration'] < 0.5 || $data['duration'] > 24) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Duration must be between 0.5 and 24 hours']);
        return;
    }
    
    // Validate price
    if ($data['price'] < 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Price cannot be negative']);
        return;
    }
    
    // Insert webinar
    $query = "
        INSERT INTO webinars (
            expert_id, title, description, category,
            webinar_date, webinar_time, duration_hours,
            price_inr, max_participants, status, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'upcoming', 1)
    ";
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([
        $expert_id,
        trim($data['title']),
        trim($data['description']),
        isset($data['category']) ? trim($data['category']) : null,
        $data['date'],
        $data['time'],
        $data['duration'],
        $data['price'],
        isset($data['max_participants']) && $data['max_participants'] > 0 ? $data['max_participants'] : null
    ]);
    
    if ($success) {
        $webinar_id = $pdo->lastInsertId();
        
        // Get created webinar
        $stmt = $pdo->prepare("SELECT * FROM webinars WHERE id = ?");
        $stmt->execute([$webinar_id]);
        $webinar = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Webinar created successfully',
            'webinar' => $webinar
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create webinar']);
    }
}

/**
 * Update existing webinar
 */
function handleUpdateWebinar($pdo, $expert_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Webinar ID is required']);
        return;
    }
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM webinars WHERE id = ? AND expert_id = ?");
    $stmt->execute([$data['id'], $expert_id]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Webinar not found or unauthorized']);
        return;
    }
    
    // Build update query dynamically
    $updates = [];
    $params = [];
    
    $allowedFields = [
        'title', 'description', 'category', 'webinar_date', 'webinar_time',
        'duration_hours', 'price_inr', 'max_participants', 'meeting_link', 'status'
    ];
    
    foreach ($allowedFields as $field) {
        $dataKey = str_replace('_', '', strtolower($field));
        if (isset($data[$dataKey])) {
            $updates[] = "$field = ?";
            $params[] = $data[$dataKey];
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        return;
    }
    
    $params[] = $data['id'];
    $params[] = $expert_id;
    
    $query = "UPDATE webinars SET " . implode(', ', $updates) . " WHERE id = ? AND expert_id = ?";
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Webinar updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update webinar']);
    }
}

/**
 * Delete webinar
 */
function handleDeleteWebinar($pdo, $expert_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Webinar ID is required']);
        return;
    }
    
    // Check if webinar has registrations
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as reg_count 
        FROM webinar_registrations 
        WHERE webinar_id = ? AND payment_status = 'completed'
    ");
    $stmt->execute([$data['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['reg_count'] > 0) {
        // Don't delete, just mark as cancelled
        $stmt = $pdo->prepare("
            UPDATE webinars 
            SET status = 'cancelled', is_active = 0 
            WHERE id = ? AND expert_id = ?
        ");
        $success = $stmt->execute([$data['id'], $expert_id]);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Webinar cancelled (has registrations)'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to cancel webinar']);
        }
    } else {
        // No registrations, safe to delete
        $stmt = $pdo->prepare("DELETE FROM webinars WHERE id = ? AND expert_id = ?");
        $success = $stmt->execute([$data['id'], $expert_id]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Webinar deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete webinar']);
        }
    }
}
