<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';

// DB connection
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    die("Not logged in as expert. Session: " . print_r($_SESSION, true));
}

echo "<!DOCTYPE html><html><head><title>Debug</title>";
echo '<script src="https://cdn.tailwindcss.com"></script>';
echo "</head><body class='bg-gray-100'>";

$page_title = "Test";
$panel_type = "expert";

echo "<h1 style='color: red; font-size: 40px; padding: 20px;'>DEBUG MODE</h1>";

echo "<div style='background: yellow; padding: 20px; margin: 20px;'>";
echo "<h2>Step 1: PHP is working ✓</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Role: " . $_SESSION['role'] . "</p>";
echo "</div>";

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
echo "<div style='background: lime; padding: 20px; margin: 20px;'>";
echo "<h2>Step 2: Header loaded ✓</h2>";
echo "</div>";

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
echo "<div style='background: cyan; padding: 20px; margin: 20px;'>";
echo "<h2>Step 3: Navigation loaded ✓</h2>";
echo "</div>";

echo "<div class='max-w-7xl mx-auto px-4 py-8'>";
echo "<div class='bg-white rounded-lg shadow-lg p-6'>";
echo "<h1 class='text-3xl font-bold text-gray-900'>Main Content Test</h1>";
echo "<p class='text-gray-600 mt-4'>If you see this, content is rendering!</p>";
echo "<div class='mt-4 grid grid-cols-3 gap-4'>";
echo "<div class='bg-blue-500 text-white p-4 rounded'>Box 1</div>";
echo "<div class='bg-green-500 text-white p-4 rounded'>Box 2</div>";
echo "<div class='bg-red-500 text-white p-4 rounded'>Box 3</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div style='background: orange; padding: 20px; margin: 20px;'>";
echo "<h2>Step 4: Main content rendered ✓</h2>";
echo "</div>";

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php';

echo "</body></html>";
?>
