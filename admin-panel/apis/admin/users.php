<?php
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/auth-check.php';

validateCSRF();

require_once '../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get all users
if ($method === 'GET') {
    $role = $_GET['role'] ?? null;
    $status = $_GET['status'] ?? null;
    
    try {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (PDOException $e) {
        error_log('Admin Get Users Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching users']);
    }
    exit;
}

// Update user status
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $data['user_id'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$user_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'User ID and status are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
    } catch (PDOException $e) {
        error_log('Admin Update User Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating user']);
    }
    exit;
}
?>
