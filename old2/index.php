<?php
require_once 'includes/session-config.php';

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

// Debug function for database connectivity
function debugDatabaseConnection() {
    // Attempt to include database configuration
    $debug_output = [];

    // Check database configuration file
    $db_config_path = 'includes/db-config.php';
    if (!file_exists($db_config_path)) {
        $debug_output[] = "❌ Database configuration file not found: $db_config_path";
    } else {
        $debug_output[] = "✅ Database configuration file exists";
        
        // Include the configuration to check for errors
        ob_start();
        include $db_config_path;
        $include_errors = ob_get_clean();
        
        if (!empty($include_errors)) {
            $debug_output[] = "❌ Errors in database configuration file: " . htmlspecialchars($include_errors);
        }
    }

    // Check database connection parameters
    $required_env_vars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
    foreach ($required_env_vars as $var) {
        if (!defined($var)) {
            $debug_output[] = "❌ Missing environment variable: $var";
        } else {
            $debug_output[] = "✅ Environment variable $var is set";
        }
    }

    // Attempt database connection
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            $debug_output[] = "❌ Database Connection Failed: " . $conn->connect_error;
            $debug_output[] = "Connection Details:";
            $debug_output[] = "Host: " . DB_HOST;
            $debug_output[] = "User: " . DB_USER;
            $debug_output[] = "Database: " . DB_NAME;
        } else {
            $debug_output[] = "✅ Successfully connected to database";
            
            // Test a simple query
            $test_query = "SHOW TABLES";
            $result = $conn->query($test_query);
            
            if ($result) {
                $debug_output[] = "✅ Successfully executed test query";
                $table_count = $result->num_rows;
                $debug_output[] = "Total Tables: $table_count";
            } else {
                $debug_output[] = "❌ Failed to execute test query: " . $conn->error;
            }
            
            $conn->close();
        }
    } catch (Exception $e) {
        $debug_output[] = "❌ Exception during database connection: " . $e->getMessage();
    }

    return $debug_output;
}

// Modify the routing to add debug panel for browse-experts
if ($panel === 'learner' && $page === 'browse-experts') {
    // Check if debug mode is requested
    $debug_mode = isset($_GET['debug']) && $_GET['debug'] === 'true';
    
    if ($debug_mode) {
        // Render debug information
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Debug - Nexpert.ai</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-50 p-8">
            <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-4 text-red-600">Database Connectivity Debug</h1>';
        
        $debug_results = debugDatabaseConnection();
        
        echo '<div class="space-y-2">';
        foreach ($debug_results as $result) {
            $color = strpos($result, '❌') !== false ? 'text-red-600' : 'text-green-600';
            echo "<p class='$color'>$result</p>";
        }
        echo '</div>
            <div class="mt-6">
                <a href="?panel=learner&page=browse-experts" class="bg-blue-600 text-white px-4 py-2 rounded">Back to Browse Experts</a>
            </div>
        </body>
        </html>';
        exit;
    }
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