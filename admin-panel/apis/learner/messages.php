<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/email-helper.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Check if requesting unread count
        $action = $_GET['action'] ?? null;
        
        if ($action === 'unread_count') {
            // Get total unread messages for this learner
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as unread_count
                FROM messages
                WHERE recipient_id = ? AND is_read = 0
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'unread_count' => (int)$result['unread_count']
            ]);
            exit;
        }
        
        // Get messages with a specific expert
        $expertId = $_GET['expert_id'] ?? null;
        
        if (!$expertId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID required']);
            exit;
        }
        
        // Get all messages between learner and expert
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.sender_id,
                m.recipient_id,
                m.message,
                m.is_read,
                m.created_at,
                u.role as sender_role
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.recipient_id = ?)
               OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$userId, $expertId, $expertId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE recipient_id = ? AND sender_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId, $expertId]);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        
    } elseif ($method === 'POST') {
        // Send a new message
        $data = json_decode(file_get_contents('php://input'), true);
        
        $expertId = $data['expert_id'] ?? null;
        $message = $data['message'] ?? null;
        
        if (!$expertId || !$message) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID and message are required']);
            exit;
        }
        
        // Validate message length
        if (strlen($message) > 1000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message too long (max 1000 characters)']);
            exit;
        }
        
        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, recipient_id, message, is_read, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$userId, $expertId, $message]);
        $messageId = $pdo->lastInsertId();
        
        // Get recipient (expert) details for email notification
        $stmt = $pdo->prepare("
            SELECT u.email, ep.full_name 
            FROM users u
            LEFT JOIN expert_profiles ep ON u.id = ep.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$expertId]);
        $expert = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get sender (learner) name
        $stmt = $pdo->prepare("
            SELECT lp.full_name 
            FROM learner_profiles lp
            WHERE lp.user_id = ?
        ");
        $stmt->execute([$userId]);
        $learner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Send email notification to expert
        if ($expert && $expert['email']) {
            try {
                $emailHelper = new EmailHelper();
                $expertName = $expert['full_name'] ?? 'Expert';
                $learnerName = $learner['full_name'] ?? 'A learner';
                
                $subject = "New Message from {$learnerName} - Nexpert.ai";
                $htmlBody = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                        .message-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #3b82f6; }
                        .view-button { display: inline-block; background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                        .footer { text-align: center; color: #6b7280; margin-top: 30px; font-size: 14px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>💬 You Have a New Message!</h1>
                        </div>
                        <div class='content'>
                            <p>Hi <strong>{$expertName}</strong>,</p>
                            <p>You received a new message from <strong>{$learnerName}</strong>.</p>
                            
                            <div class='message-box'>
                                <p style='color: #6b7280; font-size: 12px; margin-bottom: 10px;'>MESSAGE:</p>
                                <p style='font-size: 15px;'>" . htmlspecialchars($message) . "</p>
                            </div>
                            
                            <a href='https://" . $_SERVER['HTTP_HOST'] . "?panel=expert&page=messages' class='view-button'>View and Reply</a>
                            
                            <div class='footer'>
                                <p>Best regards,<br>The Nexpert.ai Team</p>
                                <p style='font-size: 12px;'>To manage your email preferences, visit your account settings</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $emailHelper->sendEmail($expert['email'], $subject, $htmlBody, $expertName);
            } catch (Exception $e) {
                error_log("Failed to send message notification email: " . $e->getMessage());
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => $messageId
        ]);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (PDOException $e) {
    error_log("Message error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
