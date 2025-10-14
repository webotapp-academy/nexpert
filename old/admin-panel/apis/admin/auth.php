<?php
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');

require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Login
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    // Prevent timing attacks with constant-time comparison
    $authSuccess = false;
    $userData = null;
    $dummyHash = '$2y$10$' . str_repeat('0', 53); // Valid bcrypt dummy hash
    
    try {
        // Query admin users (role = 'admin')
        $stmt = $pdo->prepare("
            SELECT id, email, password_hash, role, status 
            FROM users 
            WHERE email = ? AND role = 'admin'
        ");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        // Always verify password against a hash (timing attack mitigation)
        $hashToVerify = $admin ? $admin['password_hash'] : $dummyHash;
        $passwordValid = password_verify($password, $hashToVerify);
        
        // Validate credentials from database
        if ($admin && $passwordValid) {
            // Check if account is active
            if ($admin['status'] !== 'active') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Account is inactive. Contact support.'
                ]);
                exit;
            }
            
            $authSuccess = true;
            $userData = $admin;
        }
        
        if ($authSuccess && $userData) {
            // Regenerate session ID to prevent fixation attacks
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $userData['id'],
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ]
            ]);
        } else {
            // Generic error message to prevent user enumeration
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
        }
    } catch (PDOException $e) {
        error_log('Admin Login Error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred during login'
        ]);
    }
    exit;
}

// Logout
if ($method === 'DELETE') {
    // Clear session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
    exit;
}

// Check session status
if ($method === 'GET') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['role'] === 'admin') {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'logged_in' => false
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>
