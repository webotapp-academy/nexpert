<?php
/**
 * Send Follow-Up Emails for Next Booking
 * 
 * This cron job runs daily and:
 * 1. Finds 4-5 star reviews that are exactly 7 days old
 * 2. Generates AI-powered personalized email
 * 3. Sends email encouraging next booking
 * 4. Marks follow_up_email_sent = 1
 * 
 * Run: php cron/send-follow-up-emails.php
 * Or schedule: 0 10 * * * (Daily at 10 AM)
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/email-helper.php';
require_once __DIR__ . '/../admin-panel/apis/connection/universal-env.php';

// Configuration
$FROM_EMAIL = UniversalEnv::get('SMTP_FROM_EMAIL', 'support@nexpert.ai');
$FROM_NAME = UniversalEnv::get('SMTP_FROM_NAME', 'Nexpert.ai');
$OPENAI_API_KEY = UniversalEnv::get('OPENAI_API_KEY');

// Initialize EmailHelper
$emailHelper = new EmailHelper();

echo "🔄 Follow-Up Email Cron Job Started at " . date('Y-m-d H:i:s') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    // Find reviews that are:
    // - 4 or 5 stars
    // - Created exactly 7 days ago (±12 hours window)
    // - Follow-up email not sent yet
    // - For bookings (not programs)
    // - Learner's LAST COMPLETED booking was this one (no new bookings after)
    // - Learner has NOT booked again with same expert in last 7 days
    
    $stmt = $pdo->prepare("
        SELECT 
            r.id as review_id,
            r.booking_id,
            r.learner_id,
            r.expert_id,
            r.rating,
            r.review_text,
            r.created_at,
            lp.full_name as learner_name,
            u.email as learner_email,
            ep.full_name as expert_name,
            ep.category as expertise,
            b.session_datetime,
            b.session_summary,
            b.duration_minutes,
            b.id as last_booking_id
        FROM reviews r
        JOIN learner_profiles lp ON r.learner_id = lp.user_id
        JOIN users u ON r.learner_id = u.id
        JOIN expert_profiles ep ON r.expert_id = ep.user_id
        JOIN bookings b ON r.booking_id = b.id
        WHERE r.rating >= 4
          AND r.follow_up_email_sent = 0
          AND r.booking_id > 0
          AND b.status = 'completed'
          AND r.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY 12 HOUR) 
                                AND DATE_SUB(NOW(), INTERVAL 7 DAY - 12 HOUR)
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
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Found " . count($reviews) . " review(s) eligible for follow-up\n\n";
    
    if (count($reviews) == 0) {
        echo "✅ No follow-up emails to send today.\n";
        exit(0);
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($reviews as $review) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Processing Review ID: {$review['review_id']}\n";
        echo "Learner: {$review['learner_name']} ({$review['learner_email']})\n";
        echo "Expert: {$review['expert_name']}\n";
        echo "Rating: {$review['rating']} ⭐\n";
        echo "Review Date: {$review['created_at']}\n\n";
        
        try {
            // Generate AI-powered email content
            $emailContent = generateFollowUpEmail($review);
            
            // Send email
            $subject = "Ready for your next session with {$review['expert_name']}? 🚀";
            $sent = sendFollowUpEmail(
                $review['learner_email'],
                $review['learner_name'],
                $subject,
                $emailContent,
                $review
            );
            
            if ($sent) {
                // Mark as sent
                $updateStmt = $pdo->prepare("
                    UPDATE reviews 
                    SET follow_up_email_sent = 1, 
                        follow_up_email_sent_at = NOW() 
                    WHERE id = ?
                ");
                $updateStmt->execute([$review['review_id']]);
                
                echo "✅ Follow-up email sent successfully!\n";
                $successCount++;
            } else {
                echo "❌ Failed to send email\n";
                $errorCount++;
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            $errorCount++;
            
            // Log error
            error_log("Follow-up email error for review {$review['review_id']}: " . $e->getMessage());
        }
        
        echo "\n";
        
        // Rate limiting - 1 second between emails
        sleep(1);
    }
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 SUMMARY\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Successfully sent: $successCount\n";
    echo "❌ Failed: $errorCount\n";
    echo "📧 Total processed: " . count($reviews) . "\n";
    echo "\n🏁 Cron job completed at " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    error_log("Follow-up email cron critical error: " . $e->getMessage());
    exit(1);
}

/**
 * Generate AI-powered personalized follow-up email
 */
function generateFollowUpEmail($review) {
    global $OPENAI_API_KEY;
    
    // Extract key information
    $learnerName = $review['learner_name'];
    $expertName = $review['expert_name'];
    $rating = $review['rating'];
    $expertise = $review['expertise'];
    $reviewText = $review['review_text'];
    $sessionDate = date('F j, Y', strtotime($review['session_datetime']));
    
    // Prepare AI prompt
    $prompt = "Write a warm, personalized follow-up email to encourage a learner to book their next session with their expert.

Context:
- Learner name: $learnerName
- Expert name: $expertName
- Previous session date: $sessionDate
- Rating given: $rating stars
- Expert expertise: $expertise
- Learner's review: \"$reviewText\"
- IMPORTANT: Learner has NOT booked again in the past 7 days

Key Message: \"Your expert is waiting for you!\"

Requirements:
- Warm and encouraging tone
- Emphasize that the expert is ready and waiting for the learner
- Reference their positive experience ({$rating}-star rating)
- Create gentle urgency - don't lose momentum
- Mention that consistent sessions lead to better results
- Include clear call-to-action to book next session
- Keep it under 200 words
- Professional but friendly

Example phrases to include:
- \"Your expert is waiting for you\"
- \"$expertName is ready to continue your journey\"
- \"Don't let your progress slow down\"

Do not include subject line, only the email body.";
    
    // Call OpenAI API (or use fallback template)
    try {
        $aiContent = callOpenAI($prompt, $OPENAI_API_KEY);
        if ($aiContent) {
            return $aiContent;
        }
    } catch (Exception $e) {
        error_log("OpenAI API error: " . $e->getMessage());
    }
    
    // Fallback template if AI fails
    return generateFallbackEmail($review);
}

/**
 * Call OpenAI API for email generation
 */
function callOpenAI($prompt, $apiKey) {
    if (empty($apiKey) || $apiKey === 'YOUR_OPENAI_API_KEY') {
        return null; // Use fallback
    }
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful email marketing assistant for an expert-learner platform.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 300
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }
    
    return null;
}

/**
 * Fallback template when AI is unavailable
 */
function generateFallbackEmail($review) {
    $learnerName = $review['learner_name'];
    $expertName = $review['expert_name'];
    $rating = $review['rating'];
    $expertise = $review['expertise'];
    $stars = str_repeat('⭐', $rating);
    
    return "Hi $learnerName,

We hope you're doing well! 

**Your expert is waiting for you!** 🎯

$expertName is ready to continue your learning journey with you. Many learners find that regular sessions help maintain momentum and achieve faster progress.

$expertName specializes in $expertise and is available to guide you to the next level.

**Why book your next session now?**
✓ Keep the learning momentum going
✓ Build on what you've already learned
✓ Stay accountable to your goals
✓ $expertName is available and ready to help!

Don't let your progress slow down. Your expert is just a click away!

**$expertName is waiting to hear from you!**
👉 Book your next session now

Looking forward to seeing you continue your success!

Best regards,
The Nexpert.ai Team

P.S. The best time to schedule your next session is right now while your momentum is strong! 🚀";
}

/**
 * Send follow-up email using EmailHelper
 */
function sendFollowUpEmail($toEmail, $toName, $subject, $body, $review) {
    global $emailHelper;
    
    // Build HTML email
    $expertName = $review['expert_name'];
    $expertId = $review['expert_id'];
    $rating = $review['rating'];
    $stars = str_repeat('⭐', $rating);
    
    $bookingUrl = "https://nexpert.ai/index.php?panel=learner&page=browse-experts&expert_id=$expertId";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: white; padding: 30px; border: 1px solid #e0e0e0; }
            .cta-button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .cta-button:hover { background: #5568d3; }
            .footer { background: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 Your Expert is Waiting!</h1>
            </div>
            <div class='content'>
                " . nl2br(htmlspecialchars($body)) . "
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='$bookingUrl' class='cta-button'>📅 Book Next Session with $expertName</a>
                </div>
            </div>
            <div class='footer'>
                <p>You're receiving this email because you recently completed a session on Nexpert.ai</p>
                <p>&copy; " . date('Y') . " Nexpert.ai - Your Learning Partner</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email using EmailHelper
    $result = $emailHelper->sendEmail($toEmail, $subject, $htmlBody, $toName);
    
    return $result['success'];
}
?>
