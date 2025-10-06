<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../../../includes/session-config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $name = $input['name'] ?? '';
        $mobile = $input['mobile'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($name) || empty($mobile) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
            exit;
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
        
        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$mobile]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Mobile number already registered']);
            exit;
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (role, email, password_hash, phone, status, created_at)
                VALUES ('learner', ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$email, $passwordHash, $mobile]);
            $userId = $pdo->lastInsertId();
            
            // Insert learner profile
            $stmt = $pdo->prepare("
                INSERT INTO learner_profiles (user_id, full_name, timezone, created_at)
                VALUES (?, ?, 'Asia/Kolkata', NOW())
            ");
            $stmt->execute([$userId, $name]);
            
            // Commit transaction
            $pdo->commit();
            
            // Create session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = 'learner';
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $name;
            
            // Get redirect URL if it exists
            $redirectUrl = $_SESSION['redirect_after_login'] ?? null;
            // Clear the redirect URL from session
            unset($_SESSION['redirect_after_login']);
            
            $response = [
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'full_name' => $name
                ]
            ];
            
            if ($redirectUrl) {
                $response['redirect_url'] = $redirectUrl;
            }
            
            echo json_encode($response);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Learner Registration API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
