<?php
require_once 'includes/session-config.php';

// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';

// Main router for Nexpert.ai application
// If no panel specified, show homepage
if (!isset($_GET['panel']) && !isset($_GET['page'])) {
    $panel = '';
    $page = 'home';
} else {
    $panel = $_GET['panel'] ?? '';
    
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
$learner_pages = [
    'auth', 'profile', 'browse-experts', 'expert-profile', 'booking', 'payments', 
    'dashboard', 'notifications', 'my-programs', 'my-sessions', 'messages', 
    'booking-details', 'program-details', 'program-payment', 'program-execution', 
    'webinar-details', 'webinar-payment', 'expert-trust-report', 'outcome-tracker'
];

$expert_pages = [
    'auth', 'dashboard', 'profile-setup', 'profile-view', 'booking-details', 
    'program-details', 'kyc', 'workflow-builder', 'booking-management', 
    'session-execution', 'earnings', 'learner-management', 'notifications', 
    'settings', 'my-programs', 'my-webinars', 'webinar-details', 'messages', 
    'trust-certificate', 'certificate', 'trust-insights', 'apply'
];

$admin_pages = [
    'auth', 'dashboard', 'experts', 'users', 'payouts', 'bookings', 
    'payments', 'kyc-verification', 'settings', 'credibility', 'enterprise-leads'
];

// Function to render 404 page
if (!function_exists('render_404')) {
    function render_404() {
        http_response_code(404);
        $home_url = defined('BASE_PATH') && BASE_PATH ? BASE_PATH . '/index.php' : '/';
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Nexpert.ai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#080B10] min-h-screen flex items-center justify-center text-white">
    <div class="text-center p-8">
        <h1 class="text-6xl font-bold text-gray-600 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-200 mb-4">Page Not Found</h2>
        <p class="text-gray-400 mb-8">The page you are looking for does not exist.</p>
        <a href="' . htmlspecialchars($home_url) . '" class="bg-[#00D4AA] text-[#080B10] font-bold px-6 py-3 rounded-lg hover:bg-[#00bda0] transition">Go Home</a>
    </div>
</body>
</html>';
    }
}

// Route to appropriate page
if ($page === 'home') {
    if (file_exists('home.php')) {
        include 'home.php';
    } else {
        render_404();
    }
} elseif ($page === 'methodology') {
    if (file_exists('pages/methodology.php')) {
        include 'pages/methodology.php';
    } elseif (file_exists('how-trust-works.php')) {
        include 'how-trust-works.php';
    } else {
        render_404();
    }
} elseif ($page === 'how-trust-works') {
    if (file_exists('how-trust-works.php')) {
        include 'how-trust-works.php';
    } else {
        render_404();
    }
} elseif ($page === 'for-learners') {
    if (file_exists('for-learners.php')) {
        include 'for-learners.php';
    } else {
        render_404();
    }
} elseif ($page === 'for-experts') {
    if (file_exists('for-experts.php')) {
        include 'for-experts.php';
    } else {
        render_404();
    }
} elseif ($page === 'for-enterprise') {
    if (file_exists('pages/for-enterprise.php')) {
        include 'pages/for-enterprise.php';
    } elseif (file_exists('for-enterprise.php')) {
        include 'for-enterprise.php';
    } else {
        render_404();
    }
} elseif ($page === 'privacy-policy' || $page === 'privacy') {
    if (file_exists('pages/privacy-policy.php')) {
        include 'pages/privacy-policy.php';
    } else {
        render_404();
    }
} elseif ($page === 'terms' || $page === 'terms-of-service') {
    if (file_exists('pages/terms.php')) {
        include 'pages/terms.php';
    } else {
        render_404();
    }
} elseif ($panel === 'learner' && in_array($page, $learner_pages)) {
    if (file_exists("learner/learner-{$page}.php")) {
        include "learner/learner-{$page}.php";
    } elseif (file_exists("learner/{$page}.php")) {
        include "learner/{$page}.php";
    } else {
        render_404();
    }
} elseif ($panel === 'expert' && in_array($page, $expert_pages)) {
    if ($page === 'apply') {
        if (file_exists('expert/apply.php')) {
            include 'expert/apply.php';
        } elseif (file_exists('expert/expert-auth.php')) {
            include 'expert/expert-auth.php';
        } else {
            render_404();
        }
    } elseif ($page === 'certificate' || $page === 'trust-certificate') {
        if (file_exists('expert/trust-certificate.php')) {
            include 'expert/trust-certificate.php';
        } else {
            render_404();
        }
    } elseif (file_exists("expert/expert-{$page}.php")) {
        include "expert/expert-{$page}.php";
    } elseif (file_exists("expert/{$page}.php")) {
        include "expert/{$page}.php";
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