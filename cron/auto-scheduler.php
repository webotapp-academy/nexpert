<?php
/**
 * Auto Scheduler - Runs background tasks automatically
 * 
 * This file should be called by:
 * 1. External cron service (like cron-job.org, easycron.com)
 * 2. Server cron: curl https://yourdomain.com/cron/auto-scheduler.php
 * 3. WordPress/Plugin cron alternative
 * 
 * Add this to any frequently accessed page (like footer.php) to auto-trigger:
 * <img src="/v6/cron/trigger.php" style="display:none;" />
 */

// Security: Only allow execution from command line or with secret key
$secret_key = 'nexpert_cron_2025'; // Change this!
$is_cli = php_sapi_name() === 'cli';
$is_authorized = isset($_GET['key']) && $_GET['key'] === $secret_key;

if (!$is_cli && !$is_authorized) {
    http_response_code(403);
    die('Unauthorized access');
}

// Set unlimited execution time for cron jobs
set_time_limit(0);

// Load database connection
require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';

// Log file
$logFile = __DIR__ . '/../logs/auto-scheduler.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

logMessage("========================================");
logMessage("Auto Scheduler Started");

try {
    // Check last run time from database
    $checkLastRun = $pdo->query("
        SELECT * FROM cron_jobs 
        WHERE job_name = 'inactive_learners_check' 
        ORDER BY last_run DESC LIMIT 1
    ");
    
    $lastRun = $checkLastRun->fetch(PDO::FETCH_ASSOC);
    $shouldRun = false;
    
    if (!$lastRun) {
        // First time running
        $shouldRun = true;
        logMessage("First time execution - creating cron job record");
        
        // Create cron_jobs table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cron_jobs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_name VARCHAR(100) UNIQUE NOT NULL,
                last_run DATETIME NOT NULL,
                next_run DATETIME NOT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                last_result TEXT,
                run_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            INSERT INTO cron_jobs (job_name, last_run, next_run, status) 
            VALUES ('inactive_learners_check', NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'pending')
        ");
    } else {
        // Check if 24 hours have passed
        $lastRunTime = strtotime($lastRun['last_run']);
        $currentTime = time();
        $hoursSinceLastRun = ($currentTime - $lastRunTime) / 3600;
        
        logMessage("Last run: " . $lastRun['last_run'] . " ({$hoursSinceLastRun} hours ago)");
        
        if ($hoursSinceLastRun >= 24) {
            $shouldRun = true;
            logMessage("24+ hours passed - running job");
        } else {
            $hoursRemaining = 24 - $hoursSinceLastRun;
            logMessage("Only {$hoursSinceLastRun} hours passed - skipping (need {$hoursRemaining} more hours)");
        }
    }
    
    if ($shouldRun) {
        logMessage("Executing inactive learners check...");
        
        // Execute the inactive learners check
        ob_start();
        include __DIR__ . '/check-inactive-learners.php';
        $output = ob_get_clean();
        
        // Parse output for results
        $emailsSent = 0;
        if (preg_match('/Emails sent: (\d+)/', $output, $matches)) {
            $emailsSent = intval($matches[1]);
        }
        
        // Update cron job record
        $updateStmt = $pdo->prepare("
            UPDATE cron_jobs 
            SET last_run = NOW(), 
                next_run = DATE_ADD(NOW(), INTERVAL 1 DAY),
                status = 'completed',
                last_result = ?,
                run_count = run_count + 1
            WHERE job_name = 'inactive_learners_check'
        ");
        $updateStmt->execute(["Success: {$emailsSent} emails sent"]);
        
        logMessage("Job completed successfully - {$emailsSent} emails sent");
        logMessage($output);
    }
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    
    // Update cron job with error
    try {
        $updateStmt = $pdo->prepare("
            UPDATE cron_jobs 
            SET status = 'failed',
                last_result = ?
            WHERE job_name = 'inactive_learners_check'
        ");
        $updateStmt->execute(["Error: " . $e->getMessage()]);
    } catch (Exception $updateError) {
        logMessage("Failed to update cron job record: " . $updateError->getMessage());
    }
}

logMessage("Auto Scheduler Finished");
logMessage("========================================\n");

// Return success response for web calls
if (!$is_cli) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Scheduler executed',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
