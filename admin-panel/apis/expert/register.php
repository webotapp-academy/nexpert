<?php
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
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
            // Insert into users table (setting both password and password_hash)
            $stmt = $pdo->prepare("
                INSERT INTO users (email, phone, password, password_hash, role, status, created_at)
                VALUES (?, ?, ?, ?, 'expert', 'active', NOW())
            ");
            $stmt->execute([$email, $mobile, $passwordHash, $passwordHash]);
            $userId = $pdo->lastInsertId();
            
            // Insert into expert_profiles table
            $stmt = $pdo->prepare("
                INSERT INTO expert_profiles (user_id, full_name, verification_status, timezone, created_at)
                VALUES (?, ?, 'pending', 'UTC', NOW())
            ");
            $stmt->execute([$userId, $name]);

            // Initialize default baseline trust_state record
            $trustStmt = $pdo->prepare("
                INSERT INTO trust_state (expert_id, overall_score, trust_tier, band_name, confidence_score, stability_score, trend_direction, last_updated)
                VALUES (?, 50.00, 'C', 'Unverified', 0.00, 100.00, 'stable', NOW())
                ON DUPLICATE KEY UPDATE expert_id = expert_id
            ");
            $trustStmt->execute([$userId]);
            
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
