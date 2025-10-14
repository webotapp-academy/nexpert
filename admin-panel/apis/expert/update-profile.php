<?php
header('Content-Type: application/json');

// Include necessary files
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

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
    // Validate and sanitize input
    $userId = $_SESSION['user_id'];
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $tagline = filter_input(INPUT_POST, 'tagline', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'bio_full', FILTER_SANITIZE_STRING);
    $timezone = filter_input(INPUT_POST, 'timezone', FILTER_SANITIZE_STRING) ?: 'UTC';
    $experienceYears = filter_input(INPUT_POST, 'experience_years', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($fullName) || empty($tagline) || empty($email) || empty($phone)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Please fill in all required fields'
        ]);
        exit;
    }

    // Handle profile photo upload
    $profilePhotoPath = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/';
        
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file
        $fileInfo = getimagesize($_FILES['profile_photo']['tmp_name']);
        if (!$fileInfo) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid image file'
            ]);
            exit;
        }

        // Generate unique filename
        $fileExtension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
            $profilePhotoPath = 'uploads/profiles/' . $fileName;
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to upload profile photo'
            ]);
            exit;
        }
    }

    // Prepare SQL query
    $updateFields = [
        'full_name = :full_name',
        'tagline = :tagline',
        'bio_full = :bio_full',
        'timezone = :timezone',
        'experience_years = :experience_years'
    ];

    $params = [
        ':full_name' => $fullName,
        ':tagline' => $tagline,
        ':bio_full' => $bio,
        ':timezone' => $timezone,
        ':experience_years' => $experienceYears,
        ':user_id' => $userId
    ];

    // Add profile photo to update if uploaded
    if ($profilePhotoPath) {
        $updateFields[] = 'profile_photo = :profile_photo';
        $params[':profile_photo'] = $profilePhotoPath;
    }

    // Update user email and phone in users table
    $userStmt = $pdo->prepare("UPDATE users SET email = :email, phone = :phone WHERE id = :user_id");
    $userStmt->execute([
        ':email' => $email,
        ':phone' => $phone,
        ':user_id' => $userId
    ]);

    // Update expert profile
    $stmt = $pdo->prepare("UPDATE expert_profiles SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id");
    $stmt->execute($params);

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully',
        'profile_photo' => $profilePhotoPath
    ]);

} catch (PDOException $e) {
    // Log the error
    error_log("Profile Update Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while updating your profile'
    ]);
}
