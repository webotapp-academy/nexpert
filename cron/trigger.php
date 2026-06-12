<?php
/**
 * Silent Trigger for Auto Scheduler
 * 
 * Include this in frequently accessed pages (e.g., footer.php):
 * require_once __DIR__ . '/../cron/trigger.php';
 * 
 * This will silently trigger the auto-scheduler in background
 */

// Only trigger if this file is included, not directly accessed
if (basename($_SERVER['PHP_SELF']) === 'trigger.php') {
    // Direct access - trigger via image
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    
    // Trigger in background (non-blocking)
    $scheduler_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/auto-scheduler.php?key=nexpert_cron_2025';
    
    // Use fsockopen for non-blocking request
    $url_parts = parse_url($scheduler_url);
    $fp = @fsockopen($url_parts['host'], 80, $errno, $errstr, 0.1);
    if ($fp) {
        $path = $url_parts['path'] . '?' . $url_parts['query'];
        $out = "GET {$path} HTTP/1.1\r\n";
        $out .= "Host: {$url_parts['host']}\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        fclose($fp);
    }
    exit;
}

// Silent background trigger (when included in other files)
// Check if we should trigger (max once per page load)
if (!defined('CRON_TRIGGERED')) {
    define('CRON_TRIGGERED', true);
    
    // Only trigger occasionally (10% of page loads to reduce server load)
    if (rand(1, 10) === 1) {
        // Trigger via cURL in background if available
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/v6/cron/auto-scheduler.php?key=nexpert_cron_2025');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);
            curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}
