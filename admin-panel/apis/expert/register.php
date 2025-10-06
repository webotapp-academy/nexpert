<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (!$data || !isset($data['name']) || !isset($data['email']) || !isset($data['mobile']) || !isset($data['password'])) {
            throw new Exception('All fields are required');
        }
        
        $name = trim($data['name']);
        $email = trim($data['email']);
        $mobile = trim($data['mobile']);
        $password = $data['password'];
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        
        // Validate password length
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email address is already registered');
        }
        
        // Check if mobile already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$mobile]);
        if ($stmt->fetch()) {
            throw new Exception('Mobile number is already registered');
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Insert into users table
            $stmt = $pdo->prepare("
                INSERT INTO users (email, phone, password_hash, role, status, created_at)
                VALUES (?, ?, ?, 'expert', 'active', NOW())
            ");
            $stmt->execute([$email, $mobile, $passwordHash]);
            $userId = $pdo->lastInsertId();
            
            // Insert into expert_profiles table
            $stmt = $pdo->prepare("
                INSERT INTO expert_profiles (user_id, full_name, verification_status, created_at)
                VALUES (?, ?, 'pending', NOW())
            ");
            $stmt->execute([$userId, $name]);
            
            $pdo->commit();
            
            // Log the user in automatically
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'expert';
            $_SESSION['full_name'] = $name;
            $_SESSION['verification_status'] = 'pending';
            $_SESSION['last_activity'] = time();
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'full_name' => $name,
                    'verification_status' => 'pending'
                ]
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
