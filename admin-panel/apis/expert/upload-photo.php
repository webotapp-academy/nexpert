<?php
// Disable error display in output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Log incoming request
error_log('Incoming Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Request Headers: ' . print_r(getallheaders(), true));
error_log('Uploaded Files: ' . print_r($_FILES, true));

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(true, 'OK', null, 200);
}

// Include necessary files
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    error_log('Unauthorized access attempt');
    error_log('Session User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('Session Role: ' . ($_SESSION['role'] ?? 'Not set'));
    sendJsonResponse(false, 'Unauthorized access', null, 403);
}

try {
    // Ensure POST method is used
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
        sendJsonResponse(false, 'Method Not Allowed', null, 405);
    }

    // Check if file was uploaded
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        error_log('No file uploaded or upload error occurred');
        error_log('Upload error: ' . ($_FILES['profile_photo']['error'] ?? 'No file'));
        sendJsonResponse(false, 'No file uploaded or upload error occurred', null, 400);
    }

    $file = $_FILES['profile_photo'];
    $userId = $_SESSION['user_id'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log('Invalid file type: ' . $file['type']);
        sendJsonResponse(false, 'Invalid file type. Only JPG and PNG are allowed', null, 400);
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log('File size exceeds limit: ' . $file['size']);
        sendJsonResponse(false, 'File size exceeds 5MB limit', null, 400);
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log('Failed to create upload directory');
            sendJsonResponse(false, 'Failed to create upload directory', null, 500);
        }
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
    $destination = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        error_log('Failed to move uploaded file');
        error_log('Temp file: ' . $file['tmp_name']);
        error_log('Destination: ' . $destination);
        error_log('Upload directory permissions: ' . substr(sprintf('%o', fileperms($uploadDir)), -4));
        error_log('PHP process user: ' . exec('whoami'));
        sendJsonResponse(false, 'Failed to move uploaded file', null, 500);
    }

    // Update profile photo in database
    $relativePath = '/uploads/profiles/' . $filename;
    $stmt = $pdo->prepare("
        UPDATE expert_profiles 
        SET profile_photo = :photo_path 
        WHERE user_id = :user_id
    ");
    $result = $stmt->execute([
        ':photo_path' => $relativePath,
        ':user_id' => $userId
    ]);

    error_log('Database update result: ' . ($result ? 'Success' : 'Failure'));
    error_log('Affected rows: ' . $stmt->rowCount());
    error_log('Stored photo path: ' . $relativePath);

    if (!$result) {
        error_log('Database update failed');
        sendJsonResponse(false, 'Failed to update profile photo in database', null, 500);
    }

    // Return full path for immediate use
    $fullPath = BASE_PATH . '/uploads/profiles/' . $filename;
    error_log('Returned photo URL: ' . $fullPath);

    // Return success response
    sendJsonResponse(true, 'Profile photo updated successfully', [
        'photo_url' => $fullPath
    ]);

} catch (PDOException $e) {
    // Log the error
    error_log('Profile Photo Upload PDO Error: ' . $e->getMessage());
    error_log('User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('File Details: ' . print_r($file ?? [], true));
    error_log('Full Trace: ' . $e->getTraceAsString());
    sendJsonResponse(false, 'Database error occurred', null, 500);

} catch (Exception $e) {
    // Log other unexpected errors
    error_log('Profile Photo Upload Error: ' . $e->getMessage());
    error_log('User ID: ' . ($_SESSION['user_id'] ?? 'Not set'));
    error_log('File Details: ' . print_r($file ?? [], true));
    error_log('Full Trace: ' . $e->getTraceAsString());
    sendJsonResponse(false, 'An unexpected error occurred', null, 500);
}