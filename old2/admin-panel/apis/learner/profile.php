<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch profile data
if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT lp.*, u.email, u.phone 
            FROM learner_profiles lp
            JOIN users u ON u.id = lp.user_id
            WHERE lp.user_id = ?
        ");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$profile) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Profile not found']);
            exit;
        }
        
        echo json_encode(['success' => true, 'profile' => $profile]);
    } catch (PDOException $e) {
        error_log("Learner Get Profile Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    exit;
}

// POST - Update profile data or upload photo
if ($method === 'POST') {
    // Check if this is a file upload
    if (isset($_FILES['profile_photo'])) {
        try {
            $file = $_FILES['profile_photo'];
            
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File upload error']);
                exit;
            }
            
            // Check file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'File size must be less than 10MB']);
                exit;
            }
            
            // Validate MIME type using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($mimeType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Only JPG and PNG images are allowed']);
                exit;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'learner_' . $userId . '_' . time() . '.' . $extension;
            $uploadDir = __DIR__ . '/../../../uploads/profiles/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $photoPath = '/uploads/profiles/' . $filename;
                
                // Update database
                $stmt = $pdo->prepare("UPDATE learner_profiles SET profile_photo = ? WHERE user_id = ?");
                $stmt->execute([$photoPath, $userId]);
                
                echo json_encode(['success' => true, 'photo_url' => $photoPath]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to save file']);
            }
            
        } catch (PDOException $e) {
            error_log("Learner Photo Upload Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        }
        exit;
    }
    
    // Regular profile update
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $fullName = $data['full_name'] ?? '';
        $phone = $data['phone'] ?? '';
        $timezone = $data['timezone'] ?? 'Asia/Kolkata';
        $learningGoals = $data['learning_goals'] ?? '';
        
        // Validate required fields
        if (empty($fullName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Full name is required']);
            exit;
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Update learner profile (only update columns that exist)
            $stmt = $pdo->prepare("
                UPDATE learner_profiles 
                SET full_name = ?, timezone = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$fullName, $timezone, $userId]);
            
            // Update phone in users table
            if ($phone) {
                $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $stmt->execute([$phone, $userId]);
            }
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Learner Update Profile Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
