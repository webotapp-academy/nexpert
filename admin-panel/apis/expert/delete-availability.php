<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Log incoming request
error_log('Delete Availability Request Method: ' . $_SERVER['REQUEST_METHOD']);

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $dayOfWeek = isset($input['day_of_week']) ? intval($input['day_of_week']) : -1;
    
    if ($dayOfWeek < 0 || $dayOfWeek > 6) {
        throw new Exception('Invalid day of week');
    }

    // Check if slots exist for this day
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM expert_availability 
        WHERE expert_id = :expert_id AND day_of_week = :day_of_week
    ");
    $checkStmt->execute([
        ':expert_id' => $userId,
        ':day_of_week' => $dayOfWeek
    ]);
    
    $result = $checkStmt->fetch();
    if ($result['count'] == 0) {
        throw new Exception('No availability slots found for this day');
    }

    // Delete all slots for this day
    $deleteStmt = $pdo->prepare("
        DELETE FROM expert_availability 
        WHERE expert_id = :expert_id AND day_of_week = :day_of_week
    ");
    $deleteStmt->execute([
        ':expert_id' => $userId,
        ':day_of_week' => $dayOfWeek
    ]);
    
    $deletedCount = $deleteStmt->rowCount();

    // Log successful deletion
    error_log("Availability slots deleted successfully");
    error_log("Expert ID: $userId");
    error_log("Day of Week: $dayOfWeek");
    error_log("Deleted Count: $deletedCount");

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => "Successfully deleted $deletedCount availability slot(s)",
        'deleted_count' => $deletedCount
    ]);

} catch (Exception $e) {
    // Log the error
    error_log('Delete Availability Error: ' . $e->getMessage());
    error_log('User ID: ' . ($userId ?? 'N/A'));
    error_log('Slot ID: ' . ($slotId ?? 'N/A'));

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
