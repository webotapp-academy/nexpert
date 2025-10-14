<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/admin-panel/apis/connection/pdo.php';

// Log incoming request
error_log('Incoming Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Request Headers: ' . print_r(getallheaders(), true));
error_log('Request Body: ' . file_get_contents('php://input'));

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
    error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Session Role: ' . ($_SESSION['role'] ?? 'Not set'));

    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    // Ensure POST method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        throw new Exception('Method Not Allowed');
    }

    // Parse input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['pricing_id'])) {
        throw new Exception('Missing required field: pricing_id');
    }

    $userId = $_SESSION['user_id'];
    $pricingId = $input['pricing_id'];

    // Start database transaction
    $pdo->beginTransaction();

    // First, verify that the pricing tier belongs to the current expert
    $checkStmt = $pdo->prepare("
        SELECT id FROM expert_pricing 
        WHERE id = :pricing_id AND expert_id = :expert_id
    ");
    $checkStmt->execute([
        ':pricing_id' => $pricingId,
        ':expert_id' => $userId
    ]);

    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Pricing tier not found or does not belong to the current expert');
    }

    // Prepare SQL statement to delete the pricing tier
    $stmt = $pdo->prepare("
        DELETE FROM expert_pricing 
        WHERE id = :pricing_id AND expert_id = :expert_id
    ");

    // Execute the statement
    $result = $stmt->execute([
        ':pricing_id' => $pricingId,
        ':expert_id' => $userId
    ]);

    // Commit transaction
    $pdo->commit();

    // Log successful deletion
    error_log('Pricing tier deleted successfully');
    error_log('Expert ID: ' . $userId);
    error_log('Pricing ID: ' . $pricingId);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Pricing tier deleted successfully'
    ]);

} catch (PDOException $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error
    error_log('Pricing Delete PDO Error: ' . $e->getMessage());
    error_log('User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Input Data: ' . print_r($input ?? [], true));
    error_log('Full Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error
    error_log('Pricing Delete Error: ' . $e->getMessage());
    error_log('User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Input Data: ' . print_r($input ?? [], true));
    error_log('Full Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
