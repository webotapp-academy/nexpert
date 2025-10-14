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
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

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
    $requiredFields = ['pricing_type', 'amount', 'currency', 'duration_minutes', 'is_active'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $userId = $_SESSION['user_id'];

    // Start database transaction
    $pdo->beginTransaction();

    // Prepare SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO expert_pricing 
        (expert_id, pricing_type, amount, currency, duration_minutes, description, is_active) 
        VALUES (:expert_id, :pricing_type, :amount, :currency, :duration_minutes, :description, :is_active)
    ");

    // Execute the statement
    $result = $stmt->execute([
        ':expert_id' => $userId,
        ':pricing_type' => $input['pricing_type'],
        ':amount' => $input['amount'],
        ':currency' => $input['currency'],
        ':duration_minutes' => $input['duration_minutes'],
        ':description' => $input['description'] ?? null,
        ':is_active' => $input['is_active'] ? 1 : 0
    ]);

    // Commit transaction
    $pdo->commit();

    // Log successful insertion
    error_log('Pricing tier added successfully');
    error_log('Expert ID: ' . $userId);
    error_log('Pricing Details: ' . print_r($input, true));

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Pricing tier added successfully'
    ]);

} catch (PDOException $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error
    error_log('Pricing Update PDO Error: ' . $e->getMessage());
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
    error_log('Pricing Update Error: ' . $e->getMessage());
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
