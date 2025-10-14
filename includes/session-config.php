<?php
// Centralized session configuration for the entire application
// Include this at the top of every PHP file that needs sessions

// Include path configuration (must be first)
require_once __DIR__ . '/config.php';

// Set default timezone to IST (India Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Include currency configuration
require_once __DIR__ . '/currency-config.php';

// Detect HTTPS
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

// Set secure session cookie params for localhost
session_set_cookie_params([
    'lifetime' => 7200, // 2 hours for development  
    'path' => BASE_PATH . '/',
    'domain' => '',
    'secure' => false, // HTTP for localhost
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session
session_start();

// Session timeout check (30 minutes of inactivity)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}