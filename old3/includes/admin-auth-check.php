<?php
// Admin authentication check - include this in all protected admin pages
require_once __DIR__ . '/session-config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Not logged in or not an admin, redirect to auth page
    header('Location: ?panel=admin&page=auth');
    exit;
}

// Check session timeout (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: ?panel=admin&page=auth&timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Make admin data available to the page
$adminId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
$adminEmail = $_SESSION['email'] ?? 'admin@nexpert.ai';
$adminName = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Admin';
?>
