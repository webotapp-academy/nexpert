<?php
/**
 * Cron Job: Check Inactive Learners
 * Run this daily to send reminder emails to learners who haven't logged in for 14 days
 * 
 * Setup Cron (macOS/Linux):
 * crontab -e
 * Add: 0 9 * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/v6/cron/check-inactive-learners.php
 * 
 * Or run manually: php check-inactive-learners.php
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/email-helper.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting inactive learners check...\n";

try {
    // Find learners who:
    // 1. Created account more than 14 days ago
    // 2. Haven't logged in for 14+ days OR never logged in
    // 3. Haven't received this reminder email yet (or received it more than 14 days ago)
    // This ensures emails are sent every 14 days: Day 14, Day 28, Day 42, etc.
    
    $query = "
        SELECT 
            u.id,
            u.email,
            lp.full_name,
            u.created_at,
            u.last_login,
            lp.inactive_email_sent_at
        FROM users u
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        WHERE u.role = 'learner'
        AND u.status = 'active'
        AND u.created_at <= DATE_SUB(NOW(), INTERVAL 14 DAY)
        AND (
            u.last_login IS NULL 
            OR u.last_login <= DATE_SUB(NOW(), INTERVAL 14 DAY)
        )
        AND (
            lp.inactive_email_sent_at IS NULL 
            OR lp.inactive_email_sent_at <= DATE_SUB(NOW(), INTERVAL 14 DAY)
        )
    ";
    
    error_log("Executing query: " . $query);
    $stmt = $pdo->query($query);
    $inactiveLearners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalFound = count($inactiveLearners);
    $emailsSent = 0;
    $emailsFailed = 0;
    
    echo "Found {$totalFound} inactive learners\n";
    error_log("Found {$totalFound} inactive learners");
    
    if ($totalFound === 0) {
        echo "No inactive learners to email. Exiting.\n";
        exit(0);
    }
    
    $emailHelper = new EmailHelper();
    
    foreach ($inactiveLearners as $learner) {
        $learnerId = $learner['id'];
        $learnerEmail = $learner['email'];
        $learnerName = $learner['full_name'] ?? 'Learner';
        $createdAt = $learner['created_at'];
        $lastLogin = $learner['last_login'];
        $lastEmailSent = $learner['inactive_email_sent_at'];
        
        $daysInactive = $lastLogin 
            ? round((time() - strtotime($lastLogin)) / (60 * 60 * 24))
            : round((time() - strtotime($createdAt)) / (60 * 60 * 24));
        
        // Calculate reminder number (1st, 2nd, 3rd, etc.)
        $reminderNumber = $lastEmailSent ? floor($daysInactive / 14) : 1;
        $reminderSuffix = $reminderNumber == 1 ? 'st' : ($reminderNumber == 2 ? 'nd' : ($reminderNumber == 3 ? 'rd' : 'th'));
        
        echo "\nProcessing: {$learnerName} ({$learnerEmail}) - {$daysInactive} days inactive (Reminder #{$reminderNumber})\n";
        
        // Create email content with dynamic messaging based on reminder number
        $subject = $reminderNumber == 1 
            ? "We Miss You! 🎓 Your Learning Journey Awaits"
            : "Reminder #{$reminderNumber}: We're Still Here for You! 🎓";
        
        $emailHTML = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }
                .container { max-width: 600px; margin: 0 auto; background: white; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
                .content { padding: 40px 30px; }
                .greeting { font-size: 20px; font-weight: 600; color: #2d3748; margin-bottom: 20px; }
                .message { font-size: 16px; color: #4a5568; margin-bottom: 20px; line-height: 1.8; }
                .highlight-box { background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin: 25px 0; }
                .highlight-box h3 { margin: 0 0 15px 0; color: #1e40af; font-size: 18px; }
                .benefits { list-style: none; padding: 0; margin: 20px 0; }
                .benefits li { padding: 12px 0; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; }
                .benefits li:last-child { border-bottom: none; }
                .benefits li::before { content: "✓"; display: inline-block; width: 25px; height: 25px; background: #10b981; color: white; border-radius: 50%; text-align: center; line-height: 25px; margin-right: 12px; font-weight: bold; }
                .cta-button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; margin: 20px 0; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: transform 0.2s; }
                .cta-button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5); }
                .stats { display: flex; justify-content: space-around; margin: 30px 0; }
                .stat-item { text-align: center; padding: 15px; }
                .stat-number { font-size: 32px; font-weight: bold; color: #667eea; }
                .stat-label { font-size: 14px; color: #6b7280; margin-top: 5px; }
                .footer { background: #f9fafb; padding: 30px; text-align: center; color: #6b7280; font-size: 14px; }
                .footer p { margin: 5px 0; }
                .emoji { font-size: 48px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="emoji">🎓</div>
                    <h1>' . ($reminderNumber == 1 ? 'We Miss You!' : 'We\'re Still Here for You!') . '</h1>
                    <p style="margin: 10px 0 0 0; opacity: 0.95;">' . ($reminderNumber == 1 ? 'Your learning journey is waiting' : 'Reminder #' . $reminderNumber . ' - Let\'s get you back on track!') . '</p>
                </div>
                
                <div class="content">
                    ' . ($reminderNumber > 1 ? '<div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                        <strong>📬 Reminder #' . $reminderNumber . '</strong> - This is your <strong>' . $reminderNumber . $reminderSuffix . ' reminder</strong> to come back!
                    </div>' : '') . '
                    
                    <div class="greeting">Hi ' . htmlspecialchars($learnerName) . ',</div>
                    
                    <div class="message">
                        ' . ($reminderNumber == 1 
                            ? 'We noticed it\'s been <strong>' . $daysInactive . ' days</strong> since you last visited Nexpert.ai. We\'ve been working hard to make your learning experience even better, and we\'d love to see you back!'
                            : 'It\'s now been <strong>' . $daysInactive . ' days</strong> since your last visit to Nexpert.ai. We really don\'t want you to miss out on achieving your learning goals!') . '
                    </div>
                    
                    <div class="highlight-box">
                        <h3>🚀 What You\'re Missing:</h3>
                        <ul class="benefits">
                            <li>Connect with <strong>expert mentors</strong> in your field</li>
                            <li>Book <strong>1-on-1 sessions</strong> at your convenience</li>
                            <li>Get <strong>personalized guidance</strong> for your goals</li>
                            <li>Access <strong>exclusive resources</strong> and learning materials</li>
                            <li>Track your progress with <strong>AI-powered insights</strong></li>
                        </ul>
                    </div>
                    
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Active Learners</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Expert Mentors</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">10k+</div>
                            <div class="stat-label">Sessions Completed</div>
                        </div>
                    </div>
                    
                    <div class="message">
                        Your learning journey is unique, and we\'re here to support you every step of the way. 
                        Whether you\'re looking to upskill, change careers, or pursue a passion project, our expert mentors are ready to help.
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . (defined('BASE_URL') ? BASE_URL : 'https://nexpert.ai') . '/index.php?panel=learner&page=browse-experts" class="cta-button">
                            Browse Expert Mentors →
                        </a>
                    </div>
                    
                    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-top: 25px;">
                        <strong>💡 Pro Tip:</strong> Start with a 30-minute session to discuss your goals and find the perfect mentor match!
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>Nexpert.ai</strong> - Your Personal Learning Platform</p>
                    <p>Questions? Reply to this email - we\'re here to help! 💪</p>
                    <p style="font-size: 12px; color: #9ca3af; margin-top: 15px;">
                        You\'re receiving this because you created an account on Nexpert.ai
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        // Send email
        $result = $emailHelper->sendEmail($learnerEmail, $subject, $emailHTML, $learnerName);
        
        if ($result['success']) {
            // Update the inactive_email_sent_at timestamp
            $updateStmt = $pdo->prepare("
                UPDATE learner_profiles 
                SET inactive_email_sent_at = NOW() 
                WHERE user_id = ?
            ");
            $updateStmt->execute([$learnerId]);
            
            echo "✓ Email sent successfully to {$learnerEmail}\n";
            $emailsSent++;
        } else {
            echo "✗ Failed to send email to {$learnerEmail}: " . ($result['error'] ?? 'Unknown error') . "\n";
            $emailsFailed++;
        }
        
        // Add small delay to avoid overwhelming SMTP server
        usleep(500000); // 0.5 second delay
    }
    
    echo "\n========================================\n";
    echo "Summary:\n";
    echo "Total inactive learners: {$totalFound}\n";
    echo "Emails sent: {$emailsSent}\n";
    echo "Emails failed: {$emailsFailed}\n";
    echo "========================================\n";
    echo "[" . date('Y-m-d H:i:s') . "] Inactive learners check completed.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    error_log("Inactive learners cron error: " . $e->getMessage());
    exit(1);
}
