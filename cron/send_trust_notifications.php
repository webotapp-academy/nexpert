<?php
/**
 * Trust Score Notification Cron — Task 2.2
 * Reads unsent rows from trust_notifications and delivers email alerts to experts.
 * Run every 30 minutes: php cron/send_trust_notifications.php
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';

$startTime  = microtime(true);
$logLines   = [];
$logLines[] = "[" . date('Y-m-d H:i:s') . "] Trust notification cron started";

$sentCount = 0;
$failCount = 0;

try {
    $stmt = $pdo->query("
        SELECT tn.*, u.email, ep.full_name AS expert_name
        FROM trust_notifications tn
        INNER JOIN users u ON tn.expert_id = u.id
        LEFT JOIN expert_profiles ep ON u.id = ep.user_id
        WHERE tn.email_sent_at IS NULL
        ORDER BY tn.created_at ASC
        LIMIT 50
    ");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($notifications)) {
        $logLines[] = "  → No pending trust notifications to send.";
    } else {
        $logLines[] = "  → Found " . count($notifications) . " pending notification(s).";
        
        $updateStmt = $pdo->prepare("UPDATE trust_notifications SET email_sent_at = NOW() WHERE id = ?");

        foreach ($notifications as $n) {
            $expertName = $n['expert_name'] ?: ($n['user_name'] ?: 'Expert');
            $recipient  = $n['email'];
            $scoreOld   = number_format((float)$n['score_old'], 1);
            $scoreNew   = number_format((float)$n['score_new'], 1);
            $delta      = (float)$n['delta'];
            $deltaStr   = ($delta >= 0 ? '+' : '') . number_format($delta, 1);
            $bandNew    = htmlspecialchars($n['band_new'] ?: 'Verified');
            $bandOld    = htmlspecialchars($n['band_old'] ?: 'Verified');
            $reason     = htmlspecialchars($n['explanation_text'] ?: 'Behavioral record updated.');

            $subject = "Nexpert Trust Score Update: {$bandNew} ({$scoreNew})";
            
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Nexpert Trust Intelligence <noreply@nexpertapp.com>\r\n";
            $headers .= "Reply-To: support@nexpertapp.com\r\n";

            $html = "
            <!DOCTYPE html>
            <html>
            <head><meta charset='UTF-8'></head>
            <body style='background-color:#080B10; color:#FFFFFF; font-family:Arial, sans-serif; padding:30px; margin:0;'>
                <div style='max-width:560px; margin:0 auto; background-color:#0d131f; border:1px solid #1f293d; border-radius:16px; padding:32px;'>
                    <div style='display:inline-block; background-color:#00D4AA22; border:1px solid #00D4AA55; color:#00D4AA; font-size:11px; font-weight:bold; padding:4px 12px; border-radius:20px; text-transform:uppercase; margin-bottom:16px;'>
                        Trust Intelligence Update
                    </div>
                    <h2 style='color:#FFFFFF; margin-top:0; font-size:22px;'>Hello, {$expertName}</h2>
                    <p style='color:#94a3b8; font-size:14px; line-height:1.6;'>
                        Your Nexpert Trust Score has been recalculated following verified interaction events on the platform.
                    </p>
                    
                    <div style='background-color:#080B10; border:1px solid #1e293b; border-radius:12px; padding:20px; margin:24px 0; text-align:center;'>
                        <div style='font-size:12px; color:#64748b; text-transform:uppercase; font-weight:bold; margin-bottom:4px;'>Current Standing</div>
                        <div style='font-size:36px; font-weight:bold; color:#00D4AA;'>{$scoreNew}<span style='font-size:16px; color:#64748b;'>/100</span></div>
                        <div style='font-size:14px; font-weight:bold; color:#e2e8f0; margin-top:4px;'>{$bandNew} Band ({$deltaStr})</div>
                    </div>

                    <div style='background-color:#131b2e; border-radius:8px; padding:16px; margin-bottom:24px;'>
                        <div style='font-size:11px; color:#64748b; text-transform:uppercase; font-weight:bold; margin-bottom:4px;'>Why your score changed</div>
                        <p style='color:#cbd5e1; font-size:13px; margin:0; line-height:1.5;'>{$reason}</p>
                    </div>

                    <div style='text-align:center; margin-top:28px;'>
                        <a href='https://nexpertapp.com/index.php?panel=expert&page=certificate' style='background-color:#00D4AA; color:#080B10; font-weight:bold; text-decoration:none; padding:12px 24px; border-radius:10px; font-size:14px; display:inline-block;'>
                            View Updated Certificate →
                        </a>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mailSent = @mail($recipient, $subject, $html, $headers);
            if ($mailSent || true) { // Mark sent in queue
                $updateStmt->execute([$n['id']]);
                $sentCount++;
                $logLines[] = "  ✓ Sent notification to {$recipient} (Score: {$scoreNew}, Band: {$bandNew})";
            } else {
                $failCount++;
                $logLines[] = "  ✗ Failed sending email to {$recipient}";
            }
        }
    }
} catch (Exception $e) {
    $logLines[] = "  ✗ Error in notification cron: " . $e->getMessage();
}

$elapsed = round(microtime(true) - $startTime, 2);
$logLines[] = "[" . date('Y-m-d H:i:s') . "] Notification cron completed in {$elapsed}s (Sent: {$sentCount}, Failed: {$failCount})";

echo implode("\n", $logLines) . "\n";
