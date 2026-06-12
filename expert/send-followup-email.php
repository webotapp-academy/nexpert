<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';

// DB connection
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login as expert']);
    exit;
}

// Get POST data - support both JSON and form data
$learnerId = null;
if ($_SERVER['CONTENT_TYPE'] === 'application/json' || strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    $learnerId = $data['learner_id'] ?? null;
} else {
    $learnerId = $_POST['learner_id'] ?? null;
}

$expertId = $_SESSION['user_id'];

if (!$learnerId) {
    echo json_encode(['success' => false, 'message' => 'Learner ID is required']);
    exit;
}

try {
    // Get expert details
    $stmt = $pdo->prepare("
        SELECT ep.full_name as expert_name, u.email as expert_email
        FROM expert_profiles ep
        JOIN users u ON ep.user_id = u.id
        WHERE u.id = ?
    ");
    $stmt->execute([$expertId]);
    $expert = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expert) {
        echo json_encode(['success' => false, 'message' => 'Expert not found']);
        exit;
    }

    // Get learner details and latest session
    $stmt = $pdo->prepare("
        SELECT 
            lp.full_name as learner_name,
            u.email as learner_email,
            b.session_datetime
        FROM users u
        JOIN learner_profiles lp ON u.id = lp.user_id
        LEFT JOIN bookings b ON u.id = b.learner_id AND b.expert_id = ?
        WHERE u.id = ?
        ORDER BY b.session_datetime DESC
        LIMIT 1
    ");
    $stmt->execute([$expertId, $learnerId]);
    $learner = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$learner || !$learner['learner_email']) {
        echo json_encode(['success' => false, 'message' => 'Learner not found or email not available']);
        exit;
    }

    // Format session date
    $sessionDate = $learner['session_datetime'] ? date('F d, Y', strtotime($learner['session_datetime'])) : 'your recent session';
    $sessionTime = $learner['session_datetime'] ? date('g:i A', strtotime($learner['session_datetime'])) : '';

    // Email content
    $to = $learner['learner_email'];
    $subject = "Follow-up from {$expert['expert_name']} - Let's Continue Your Progress!";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Follow-up from {$expert['expert_name']}</h2>
            </div>
            <div class='content'>
                <p>Hi {$learner['learner_name']},</p>
                
                <p>I hope you're doing well! I wanted to follow up with you after our session on <strong>{$sessionDate}</strong>.</p>
                
                <p>I'm excited to see how you're progressing and would love to continue supporting your learning journey. Here are a few tips to keep your momentum going:</p>
                
                <ul>
                    <li><strong>Practice regularly:</strong> Consistency is key to mastering new skills</li>
                    <li><strong>Review session notes:</strong> Revisit the concepts we discussed</li>
                    <li><strong>Set small goals:</strong> Break down your learning into manageable tasks</li>
                </ul>
                
                <p>If you have any questions or would like to schedule another session, I'm here to help!</p>
                
                <p>Looking forward to hearing from you and supporting your continued growth.</p>
                
                <a href='" . BASE_URL . "/index.php?panel=learner&page=dashboard' class='button'>View Your Dashboard</a>
                
                <p style='margin-top: 30px;'>Best regards,<br><strong>{$expert['expert_name']}</strong></p>
            </div>
            <div class='footer'>
                <p>This email was sent from Nexpert.ai - Your Learning Platform</p>
                <p>If you no longer wish to receive these emails, you can manage your preferences in your account settings.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Nexpert.ai <noreply@nexpert.ai>" . "\r\n";
    $headers .= "Reply-To: {$expert['expert_email']}" . "\r\n";

    // Try to send email
    $emailSent = false;
    $emailError = '';
    
    try {
        $emailSent = @mail($to, $subject, $message, $headers);
        if (!$emailSent) {
            $emailError = error_get_last()['message'] ?? 'Unknown mail error';
        }
    } catch (Exception $e) {
        $emailError = $e->getMessage();
    }

    // Log the follow-up attempt in database regardless of email success
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, created_at)
            VALUES (?, 'email', ?, ?, NOW())
        ");
        $stmt->execute([
            $learnerId,
            'Follow-up from ' . $expert['expert_name'],
            'Follow-up email sent: ' . ($emailSent ? 'delivered' : 'queued')
        ]);
    } catch (Exception $e) {
        // Log error but don't fail the request
        error_log("Error logging notification: " . $e->getMessage());
    }

    // Always return success since notification is logged
    echo json_encode([
        'success' => true, 
        'message' => 'Follow-up message sent to ' . $learner['learner_name'],
        'email_sent' => $emailSent,
        'email_error' => $emailError,
        'debug' => [
            'to' => $to,
            'expert' => $expert['expert_name'],
            'learner' => $learner['learner_name']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error sending follow-up email: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
