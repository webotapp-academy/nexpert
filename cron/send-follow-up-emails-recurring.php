<?php
/**
 * CRON JOB: Send Recurring Follow-up Emails to Learners
 * 
 * Purpose: Automatically sends up to 3 reminder emails to learners who:
 * 1. Gave a 4-5 star rating
 * 2. Review was submitted 7/14/21 days ago
 * 3. Haven't rebooked that expert yet
 * 4. Haven't booked any expert in last 7 days
 * 
 * Email Schedule:
 * - 1st email: 7 days after review
 * - 2nd email: 14 days after review (if still no rebooking)
 * - 3rd email: 21 days after review (if still no rebooking)
 * 
 * Run this daily via crontab:
 * 0 10 * * * /usr/bin/php /path/to/send-follow-up-emails-recurring.php
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/email-helper.php';

// Initialize EmailHelper
$emailHelper = new EmailHelper();

echo "🔄 Recurring Follow-Up Email Cron Job Started at " . date('Y-m-d H:i:s') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Define email intervals (days after review)
    $intervals = [7, 14, 21];
    $totalSent = 0;
    
    foreach ($intervals as $index => $days) {
        $followUpNumber = $index + 1; // 1, 2, 3
        
        echo "📧 Checking for {$followUpNumber}° email ({$days} days after review)...\n";
        
        // Find reviews that need this follow-up
        $stmt = $pdo->prepare("
            SELECT 
                r.id as review_id,
                r.booking_id,
                r.learner_id,
                r.expert_id,
                r.rating,
                r.review_text,
                r.created_at,
                r.follow_up_count,
                r.last_follow_up_date,
                lp.full_name as learner_name,
                u.email as learner_email,
                ep.full_name as expert_name,
                ep.category as expertise,
                b.session_datetime,
                b.session_summary,
                b.duration_minutes
            FROM reviews r
            JOIN learner_profiles lp ON r.learner_id = lp.user_id
            JOIN users u ON r.learner_id = u.id
            JOIN expert_profiles ep ON r.expert_id = ep.user_id
            JOIN bookings b ON r.booking_id = b.id
            WHERE r.rating >= 4
              AND r.follow_up_count < 3
              AND r.follow_up_count = ?
              AND r.booking_id > 0
              AND b.status = 'completed'
              AND r.created_at BETWEEN DATE_SUB(NOW(), INTERVAL ? DAY + INTERVAL 12 HOUR) 
                                    AND DATE_SUB(NOW(), INTERVAL ? DAY - INTERVAL 12 HOUR)
              -- Check this was learner's last booking with this expert
              AND NOT EXISTS (
                  SELECT 1 FROM bookings b2 
                  WHERE b2.learner_id = r.learner_id 
                    AND b2.expert_id = r.expert_id
                    AND b2.id > b.id
                    AND b2.status IN ('confirmed', 'completed')
              )
              -- Also check no new bookings in last 7 days with ANY expert
              AND NOT EXISTS (
                  SELECT 1 FROM bookings b3
                  WHERE b3.learner_id = r.learner_id
                    AND b3.created_at > r.created_at
                    AND b3.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND b3.status IN ('confirmed', 'completed', 'pending')
              )
            ORDER BY r.created_at ASC
            LIMIT 50
        ");
        
        $stmt->execute([$index, $days, $days]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Found " . count($reviews) . " review(s) for {$followUpNumber}° email\n\n";
        
        if (count($reviews) == 0) {
            continue;
        }
        
        foreach ($reviews as $review) {
            echo "   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "   Processing Review ID: {$review['review_id']}\n";
            echo "   Learner: {$review['learner_name']}\n";
            echo "   Expert: {$review['expert_name']}\n";
            echo "   Follow-up #: {$followUpNumber}\n\n";
            
            try {
                // Generate email content based on follow-up number
                $emailContent = generateRecurringEmail($review, $followUpNumber);
                
                // Different subject lines for each follow-up
                $subjects = [
                    1 => "Your expert is waiting for you! 🌟",
                    2 => "Still thinking about your next session? 💭",
                    3 => "Last reminder: Continue your learning journey! 🚀"
                ];
                
                $subject = $subjects[$followUpNumber];
                
                // Send email
                $sent = sendEmail(
                    $review['learner_email'],
                    $review['learner_name'],
                    $subject,
                    $emailContent
                );
                
                if ($sent) {
                    // Update follow_up_count and last_follow_up_date
                    $updateStmt = $pdo->prepare("
                        UPDATE reviews 
                        SET follow_up_count = follow_up_count + 1,
                            last_follow_up_date = NOW(),
                            follow_up_email_sent = 1,
                            follow_up_email_sent_at = COALESCE(follow_up_email_sent_at, NOW())
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$review['review_id']]);
                    
                    echo "   ✅ Email #{$followUpNumber} sent successfully!\n\n";
                    $totalSent++;
                } else {
                    echo "   ❌ Failed to send email\n\n";
                }
                
            } catch (Exception $e) {
                echo "   ❌ Error: " . $e->getMessage() . "\n\n";
                error_log("Follow-up email error for review {$review['review_id']}: " . $e->getMessage());
            }
            
            // Rate limiting
            sleep(1);
        }
        
        echo "\n";
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Cron job completed at " . date('Y-m-d H:i:s') . "\n";
    echo "📊 Total emails sent: {$totalSent}\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    error_log("Follow-up email cron fatal error: " . $e->getMessage());
}

/**
 * Generate email content based on follow-up number
 */
function generateRecurringEmail($review, $followUpNumber) {
    $learnerName = htmlspecialchars($review['learner_name']);
    $expertName = htmlspecialchars($review['expert_name']);
    $expertise = htmlspecialchars($review['expertise']);
    
    // Different messaging for each follow-up
    $messages = [
        1 => [
            'greeting' => "Hope you're doing great!",
            'main' => "We noticed you had an amazing session with <strong>{$expertName}</strong> last week. Your expert is ready and waiting to continue your learning journey!",
            'cta' => "Book Your Next Session"
        ],
        2 => [
            'greeting' => "Still thinking about booking?",
            'main' => "It's been 2 weeks since your fantastic session with <strong>{$expertName}</strong>. Don't let the momentum fade - your expert is eager to help you achieve your goals!",
            'cta' => "Continue Learning Now"
        ],
        3 => [
            'greeting' => "This is your final reminder!",
            'main' => "It's been 3 weeks since you worked with <strong>{$expertName}</strong>. This is the perfect time to take the next step. Your expert is still available and excited to guide you further!",
            'cta' => "Book Before It's Too Late"
        ]
    ];
    
    $msg = $messages[$followUpNumber];
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
            .email-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 25px; }
            .header h1 { color: #2563eb; margin: 0; font-size: 24px; }
            .content { margin-bottom: 25px; }
            .expert-info { background: #eff6ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .cta-button { display: inline-block; background: #2563eb; color: white !important; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
            .cta-button:hover { background: #1d4ed8; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            .reminder-badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='email-card'>
                <div class='header'>
                    <h1>Nexpert.ai</h1>
                    <span class='reminder-badge'>Reminder #{$followUpNumber}</span>
                </div>
                
                <div class='content'>
                    <p>Hi <strong>{$learnerName}</strong>,</p>
                    
                    <p>{$msg['greeting']}</p>
                    
                    <p>{$msg['main']}</p>
                    
                    <div class='expert-info'>
                        <strong>💼 Expert:</strong> {$expertName}<br>
                        <strong>🎯 Expertise:</strong> {$expertise}
                    </div>
                    
                    <p><strong>Why book now?</strong></p>
                    <ul>
                        <li>✅ Maintain learning momentum</li>
                        <li>✅ Same trusted expert who understands your goals</li>
                        <li>✅ Flexible scheduling that works for you</li>
                        <li>✅ Continue building on your progress</li>
                    </ul>
                    
                    <center>
                        <a href='https://nexpert.ai/learner/learner-browse-experts.php' class='cta-button'>{$msg['cta']}</a>
                    </center>
                    
                    <p style='margin-top: 25px; font-size: 14px; color: #666;'>
                        Questions? Our support team is here to help at <a href='mailto:support@nexpert.ai'>support@nexpert.ai</a>
                    </p>
                </div>
                
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Nexpert.ai. All rights reserved.</p>
                    <p>You're receiving this because you recently completed a session with one of our experts.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Send email using EmailHelper
 */
function sendEmail($to, $toName, $subject, $htmlBody) {
    global $emailHelper;
    
    try {
        return $emailHelper->sendEmail(
            $to,
            $toName,
            $subject,
            $htmlBody,
            'noreply@nexpert.ai',
            'Nexpert.ai'
        );
    } catch (Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}

?>
