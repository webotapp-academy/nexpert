<?php
// Expert authentication check - include this in all protected expert pages
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Not logged in or not an expert, redirect to auth page
    header('Location: ?panel=expert&page=auth');
    exit;
}

// Check session timeout (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: ?panel=expert&page=auth&timeout=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Make user data available to the page
$expertId = $_SESSION['user_id'];
$expertEmail = $_SESSION['email'];
$expertName = $_SESSION['full_name'] ?? 'Expert';
$verificationStatus = $_SESSION['verification_status'] ?? 'pending_kyc';
?>
