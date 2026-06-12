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
error_log('Request Body: ' . print_r($_POST, true));

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

    $userId = $_SESSION['user_id'];

    // Validate day of week
    $dayOfWeek = filter_input(INPUT_POST, 'day_of_week', FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 0, 
            'max_range' => 6
        ]
    ]);

    // Validate day of week
    if ($dayOfWeek === false || $dayOfWeek === null) {
        throw new Exception('Invalid day of week');
    }

    // Get time arrays (support both single values and arrays)
    $startTimes = isset($_POST['start_time']) ? (is_array($_POST['start_time']) ? $_POST['start_time'] : [$_POST['start_time']]) : [];
    $endTimes = isset($_POST['end_time']) ? (is_array($_POST['end_time']) ? $_POST['end_time'] : [$_POST['end_time']]) : [];

    // Validate that we have time slots
    if (empty($startTimes) || empty($endTimes)) {
        throw new Exception('Start and end times are required');
    }

    // Validate that start and end times have same count
    if (count($startTimes) !== count($endTimes)) {
        throw new Exception('Start and end times count mismatch');
    }

    // Start database transaction
    $pdo->beginTransaction();

    $insertedSlots = 0;
    $skippedSlots = 0;

    // Loop through each time slot
    for ($i = 0; $i < count($startTimes); $i++) {
        $startTime = trim($startTimes[$i]);
        $endTime = trim($endTimes[$i]);

        // Validate time format (HH:MM)
        if (!preg_match('/^\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            throw new Exception('Invalid time format. Use HH:MM');
        }

        // Check for existing slot
        $checkStmt = $pdo->prepare("
            SELECT id FROM expert_availability 
            WHERE expert_id = :expert_id 
            AND day_of_week = :day_of_week 
            AND start_time = :start_time 
            AND end_time = :end_time
        ");
        $checkStmt->execute([
            ':expert_id' => $userId,
            ':day_of_week' => $dayOfWeek,
            ':start_time' => $startTime,
            ':end_time' => $endTime
        ]);

        // If slot already exists, skip it
        if ($checkStmt->rowCount() > 0) {
            $skippedSlots++;
            continue;
        }

        // Insert new availability slot
        $stmt = $pdo->prepare("
            INSERT INTO expert_availability 
            (expert_id, day_of_week, start_time, end_time, is_active) 
            VALUES (:expert_id, :day_of_week, :start_time, :end_time, 1)
        ");
        $stmt->execute([
            ':expert_id' => $userId,
            ':day_of_week' => $dayOfWeek,
            ':start_time' => $startTime,
            ':end_time' => $endTime
        ]);

        $insertedSlots++;

        // Log successful insertion
        error_log("Slot $i added: Day $dayOfWeek, $startTime - $endTime");
    }

    // Commit transaction
    $pdo->commit();

    // Log summary
    error_log("Availability update summary:");
    error_log("Expert ID: $userId");
    error_log("Day of Week: $dayOfWeek");
    error_log("Inserted slots: $insertedSlots");
    error_log("Skipped slots: $skippedSlots");

    // Create success message
    $message = "Availability updated successfully";
    if ($insertedSlots > 0 && $skippedSlots > 0) {
        $message = "$insertedSlots slot(s) added, $skippedSlots already existed";
    } elseif ($insertedSlots > 0) {
        $message = "$insertedSlots slot(s) added successfully";
    } elseif ($skippedSlots > 0) {
        $message = "All slots already exist";
    }

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'inserted' => $insertedSlots,
        'skipped' => $skippedSlots
    ]);

} catch (Exception $e) {
    // Rollback transaction if needed
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log the error
    error_log('Availability Update Error: ' . $e->getMessage());
    error_log('User ID: ' . $userId);
    error_log('Request Data: ' . print_r($_POST, true));
    error_log('Full Trace: ' . $e->getTraceAsString());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
