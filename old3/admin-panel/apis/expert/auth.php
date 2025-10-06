<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            throw new Exception('Email and password are required');
        }
        
        $email = trim($data['email']);
        $password = $data['password'];
        
        // Fetch user from database
        $stmt = $pdo->prepare("
            SELECT u.*, ep.full_name, ep.verification_status
            FROM users u
            LEFT JOIN expert_profiles ep ON u.id = ep.user_id
            WHERE u.email = ? AND u.role = 'expert'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Use timing attack protection - verify dummy password
            password_verify($password, '$2y$10$dummyHashForTimingAttackProtection');
            throw new Exception('Invalid email or password');
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid email or password');
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            throw new Exception('Your account has been ' . $user['status'] . '. Please contact support.');
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = 'expert';
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['verification_status'] = $user['verification_status'];
        $_SESSION['last_activity'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'verification_status' => $user['verification_status']
            ]
        ]);
        
    } elseif ($method === 'DELETE') {
        // Logout
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
        
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
