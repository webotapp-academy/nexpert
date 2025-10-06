<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

// Check if expert is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['photo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    // Validate file type using finfo (server-side validation)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and WEBP are allowed');
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 5MB limit');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../../uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }

    // Update database
    $photoUrl = '/uploads/profiles/' . $filename;
    $stmt = $pdo->prepare("
        UPDATE expert_profiles 
        SET profile_photo = ? 
        WHERE user_id = ?
    ");
    $stmt->execute([$photoUrl, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated successfully',
        'photo_url' => $photoUrl
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
