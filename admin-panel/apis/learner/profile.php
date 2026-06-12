<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

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
            
            error_log("Photo Upload - Attempting to save file to: " . $uploadPath);
            error_log("Photo Upload - Upload directory exists: " . (is_dir($uploadDir) ? 'yes' : 'no'));
            error_log("Photo Upload - Upload directory writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
            error_log("Photo Upload - Temp file exists: " . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $photoPath = '/uploads/profiles/' . $filename;
                
                error_log("Photo Upload - File saved successfully: " . $photoPath);
                
                // Update database
                $stmt = $pdo->prepare("UPDATE learner_profiles SET profile_photo = ? WHERE user_id = ?");
                $stmt->execute([$photoPath, $userId]);
                
                // Update session for immediate reflection in navigation
                $_SESSION['profile_photo'] = $photoPath;
                
                error_log("Photo Upload - Database updated successfully");
                error_log("Photo Upload - Session updated with new photo: " . $photoPath);
                
                echo json_encode(['success' => true, 'photo_url' => $photoPath]);
            } else {
                $error = error_get_last();
                error_log("Photo Upload - Failed to move file: " . print_r($error, true));
                error_log("Photo Upload - PHP upload_tmp_dir: " . ini_get('upload_tmp_dir'));
                error_log("Photo Upload - PHP file_uploads: " . ini_get('file_uploads'));
                
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to save file',
                    'debug' => [
                        'upload_dir' => $uploadDir,
                        'writable' => is_writable($uploadDir),
                        'temp_file' => $file['tmp_name'],
                        'target' => $uploadPath
                    ]
                ]);
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
        $rawInput = file_get_contents('php://input');
        error_log("Learner Profile Update - Raw Input: " . $rawInput);
        
        $data = json_decode($rawInput, true);
        
        if ($data === null) {
            error_log("Learner Profile Update - JSON decode failed: " . json_last_error_msg());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        
        error_log("Learner Profile Update - Decoded data: " . print_r($data, true));
        
        $fullName = $data['full_name'] ?? '';
        $phone = $data['phone'] ?? '';
        $timezone = $data['timezone'] ?? 'Asia/Kolkata';
        $learningGoals = $data['learning_goals'] ?? '';
        $challenges = $data['challenges'] ?? '';
        $education = $data['education'] ?? '';
        $profession = $data['profession'] ?? '';
        
        // Validate required fields
        if (empty($fullName)) {
            error_log("Learner Profile Update - Full name is empty");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Full name is required']);
            exit;
        }
        
        error_log("Learner Profile Update - Updating for user ID: " . $userId);
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Update learner profile with new fields
            $stmt = $pdo->prepare("
                UPDATE learner_profiles 
                SET full_name = ?, 
                    timezone = ?, 
                    goals = ?,
                    challenges = ?,
                    education = ?,
                    profession = ?
                WHERE user_id = ?
            ");
            $result = $stmt->execute([$fullName, $timezone, $learningGoals, $challenges, $education, $profession, $userId]);
            
            error_log("Learner Profile Update - Profile update result: " . ($result ? 'success' : 'failed'));
            error_log("Learner Profile Update - Rows affected: " . $stmt->rowCount());
            
            // Update phone in users table
            if ($phone) {
                $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $stmt->execute([$phone, $userId]);
                error_log("Learner Profile Update - Phone updated");
            }
            
            $pdo->commit();
            
            // Update session data for immediate reflection in navigation
            $_SESSION['full_name'] = $fullName;
            
            error_log("Learner Profile Update - Transaction committed successfully");
            error_log("Learner Profile Update - Session updated with new name: " . $fullName);
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Learner Profile Update - Transaction rollback: " . $e->getMessage());
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Learner Update Profile Error: " . $e->getMessage());
        error_log("Learner Update Profile Error - Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error occurred', 'debug' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>
