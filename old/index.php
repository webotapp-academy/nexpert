<?php
require_once 'includes/session-config.php';

// Define BASE_PATH for use in other files
$BASE_PATH = '/nexpert';

// Main router for Nexpert.ai application
// If no panel specified, show homepage
if (!isset($_GET['panel']) && !isset($_GET['page'])) {
    $panel = '';
    $page = 'home';
} else {
    $panel = $_GET['panel'] ?? 'learner';
    
    // Set default page based on panel
    if (!isset($_GET['page'])) {
        if ($panel === 'admin') {
            $page = 'dashboard';
        } elseif ($panel === 'expert') {
            $page = 'dashboard';
        } elseif ($panel === 'learner') {
            $page = 'dashboard';
        } else {
            $page = 'home';
        }
    } else {
        $page = $_GET['page'];
    }
}

// Define available pages for each panel
$learner_pages = ['auth', 'profile', 'browse-experts', 'expert-profile', 'booking', 'payments', 'dashboard', 'notifications', 'my-programs'];
$expert_pages = ['auth', 'dashboard', 'profile-setup', 'kyc', 'workflow-builder', 'booking-management', 'session-execution', 'earnings', 'learner-management', 'notifications', 'settings', 'my-programs'];
$admin_pages = ['auth', 'dashboard', 'experts', 'users', 'payouts', 'bookings', 'payments', 'kyc-verification', 'settings'];

// Function to render 404 page
function render_404() {
    http_response_code(404);
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Nexpert.ai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
        <p class="text-gray-600 mb-8">The page you are looking for does not exist.</p>
        <a href="/" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">Go Home</a>
    </div>
</body>
</html>';
}

// Route to appropriate page
if ($page === 'home') {
    if (file_exists('home.php')) {
        include 'home.php';
    } else {
        render_404();
    }
} elseif ($panel === 'learner' && in_array($page, $learner_pages)) {
    $file_path = "learner/learner-{$page}.php";
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        render_404();
    }
} elseif ($panel === 'expert' && in_array($page, $expert_pages)) {
    $file_path = "expert/expert-{$page}.php";
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        render_404();
    }
} elseif ($panel === 'admin' && in_array($page, $admin_pages)) {
    // Check admin authentication
    $isAdminLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['role'] === 'admin';
    
    // Allow access to auth page without login
    if ($page === 'auth' || $isAdminLoggedIn) {
        $file_path = "admin/admin-{$page}.php";
        if (file_exists($file_path)) {
            include $file_path;
        } else {
            render_404();
        }
    } else {
        // Redirect to admin login
        header('Location: ?panel=admin&page=auth');
        exit;
    }
} else {
    render_404();
}
?>