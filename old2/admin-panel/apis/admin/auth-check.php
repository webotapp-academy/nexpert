<?php
// Admin authentication check - include at top of all admin APIs

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// CSRF token validation for state-changing requests
function validateCSRF() {
    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!isset($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
    }
}
?>
