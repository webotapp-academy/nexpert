<?php
/**
 * Initialize Google OAuth Flow
 * Sets session variables and returns Google OAuth URL
 */

// Load session configuration
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/connection/google-oauth-helper.php';

// Set JSON response header
header('Content-Type: application/json');

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['state']) || !isset($input['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request parameters'
    ]);
    exit;
}

// Validate role
$role = $input['role'];
if (!in_array($role, ['learner', 'expert'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user role'
    ]);
    exit;
}

// Store state and role in session
$_SESSION['oauth_state'] = $input['state'];
$_SESSION['oauth_role'] = $role;

// Force session write to ensure data is saved before redirect
session_write_close();
session_start(); // Restart session for subsequent operations

// Get the current domain for redirect URI
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . '://' . $host . BASE_PATH . '/admin-panel/apis/oauth/google-callback.php';

// Initialize OAuth helper
$oauth = new GoogleOAuthHelper($redirectUri);

// Check if credentials are configured
if (!$oauth->credentialsConfigured()) {
    echo json_encode([
        'success' => false,
        'message' => 'Google Sign In is not configured. Please contact support.'
    ]);
    exit;
}

// Generate Google OAuth URL
$authUrl = $oauth->getAuthUrl($input['state']);

echo json_encode([
    'success' => true,
    'auth_url' => $authUrl
]);
