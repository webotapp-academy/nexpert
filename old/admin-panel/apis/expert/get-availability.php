<?php
// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
    error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Session Role: ' . ($_SESSION['role'] ?? 'Not set'));

    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access',
        'data' => []
    ]);
    exit;
}

try {
    // Ensure GET method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        throw new Exception('Method Not Allowed');
    }

    $userId = $_SESSION['user_id'];

    // Fetch availability slots
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            day_of_week, 
            start_time, 
            end_time, 
            is_active
        FROM expert_availability 
        WHERE expert_id = :expert_id 
        AND is_active = 1
        ORDER BY day_of_week, start_time
    ");
    $stmt->execute([':expert_id' => $userId]);
    $availabilitySlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log fetched availability slots
    error_log('Fetched Availability Slots:');
    error_log(print_r($availabilitySlots, true));

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $availabilitySlots
    ]);

} catch (PDOException $e) {
    // Log the error
    error_log('Get Availability API PDO Error: ' . $e->getMessage());
    error_log('User ID: ' . $userId);
    error_log('Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'data' => []
    ]);
} catch (Exception $e) {
    // Log other unexpected errors
    error_log('Get Availability API Unexpected Error: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unexpected server error',
        'data' => []
    ]);
}
