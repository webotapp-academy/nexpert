<?php

// Detect base URL automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';

// Calculate base path from document root to application root
// Find the application root by looking for index.php
$document_root = $_SERVER['DOCUMENT_ROOT'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];

// Get the directory of the current executing script
$current_dir = dirname($script_filename);

// Traverse up from current directory to find index.php (application root)
$app_root = $current_dir;
while ($app_root !== $document_root && $app_root !== '/') {
    if (file_exists($app_root . '/index.php') && file_exists($app_root . '/includes/config.php')) {
        break;
    }
    $app_root = dirname($app_root);
}

// Calculate base path relative to document root
$base_path = '';
if ($app_root !== $document_root) {
    $base_path = str_replace($document_root, '', $app_root);
    $base_path = str_replace('\\', '/', $base_path); // Normalize Windows paths
}

// Full base URL
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $base_path);
define('BASE_PATH', $base_path);

?>
