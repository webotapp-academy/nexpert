<?php
// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Prevent redirect loop by checking current page
    $currentPage = $_SERVER['REQUEST_URI'];
    $authPage = BASE_PATH . '/index.php?panel=learner&page=auth';
    
    if ($currentPage !== $authPage) {
        // Save the current URL to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $authPage);
        exit;
    }
}
?>