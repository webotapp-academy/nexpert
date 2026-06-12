<?php
/**
 * Domain Path Configuration
 * Automatically detects and sets the correct base URL for different environments
 * - Replit: '' or '/'
 * - Localhost: '/nexpert' (or your subfolder name)
 * - Online Host: '/v2' or '' (depending on your setup)
 */

// Detect the base path automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';

// Get document root and current script path
$document_root = $_SERVER['DOCUMENT_ROOT'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];
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

// Set the base URL (full URL with protocol, host, and path)
$base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $base_path;

// Only define constants if they don't exist (to avoid redefinition warnings)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', $base_path);
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}

// Return the base path for direct usage
return $base_path;
