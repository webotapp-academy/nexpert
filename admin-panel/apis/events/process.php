<?php
/**
 * Event Processor API
 * Processes pending trust events and generates signals using agents
 */

require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/agent-helper.php';

header('Content-Type: application/json');

// Only allow admin or internal calls
// For now, we'll just check for a simple internal secret or session
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Check for internal secret (if triggered by cron)
    $secret = $_GET['secret'] ?? '';
    $internalSecret = 'NEXPERT_INTERNAL_PROCESSOR_123'; // Should be in .env
    if ($secret !== $internalSecret) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

try {
    // 1. Fetch pending events
    $stmt = $pdo->prepare("SELECT * FROM trust_events WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");
    $stmt->execute();
    $events = $stmt->fetchAll();

    if (empty($events)) {
        echo json_encode(['success' => true, 'message' => 'No pending events to process']);
        exit;
    }

    $processedCount = 0;

    foreach ($events as $event) {
        $pdo->beginTransaction();
        try {
            // Update status to processing
            $pdo->prepare("UPDATE trust_events SET status = 'processing' WHERE id = ?")->execute([$event['id']]);

            $payload = json_decode($event['payload'], true);
            $expertId = $event['expert_id'];

            // 2. Trigger Agents based on event type
            $agentsToTrigger = [];
            switch ($event['event_type']) {
                case 'session_completed':
                    $agentsToTrigger = ['structure', 'outcome', 'boundary'];
                    break;
                case 'feedback_submitted':
                    $agentsToTrigger = ['outcome', 'consistency'];
                    break;
                case 'expert_profile_updated':
                    $agentsToTrigger = ['structure'];
                    break;
                case 'kyc_verified':
                case 'complaint_logged':
                    $agentsToTrigger = ['boundary'];
                    break;
                case 'booking_created':
                    $agentsToTrigger = ['consistency'];
                    break;
                case 'reschedule_requested':
                case 'reschedule_approved':
                    $agentsToTrigger = ['consistency'];
                    break;
            }

            foreach ($agentsToTrigger as $agentType) {
                $signal = AgentHelper::extractSignal($agentType, [
                    'event_type' => $event['event_type'],
                    'payload' => $payload,
                    'expert_id' => $expertId
                ]);

                if ($signal) {
                    $pdo->prepare("
                        INSERT INTO trust_signals (event_id, expert_id, agent_type, signal_value, metadata, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ")->execute([
                                $event['id'],
                                $expertId,
                                $agentType,
                                $signal['signal_value'],
                                json_encode($signal['metadata'])
                            ]);
                }
            }

            // Update status to processed
            $pdo->prepare("UPDATE trust_events SET status = 'processed' WHERE id = ?")->execute([$event['id']]);

            $pdo->commit();
            $processedCount++;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Event Processor Error for Event ID {$event['id']}: " . $e->getMessage());
            $pdo->prepare("UPDATE trust_events SET status = 'failed' WHERE id = ?")->execute([$event['id']]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Processed $processedCount events",
        'processed_count' => $processedCount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
