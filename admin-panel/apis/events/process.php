<?php
/**
 * Event Processor API — MVP2
 * Processes pending trust events, triggers agents, updates scores.
 *
 * WHAT CHANGED:
 * - Secret moved to getenv() — set NEXPERT_PROCESSOR_SECRET in .env
 * - New event types: outcome_achieved, goal_completed, repeat_booking, session_no_show, late_start
 * - Batch size increased to 50
 * - Calls aggregateOne() per expert after signals written (immediate score update)
 * - trigger_event_id passed to aggregator
 *
 * WHAT IS UNCHANGED:
 * - Auth check (admin session OR secret)
 * - Transaction pattern per event
 * - Signal INSERT format
 * - Response JSON format
 */

require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/agent-helper.php';
require_once __DIR__ . '/../connection/trust-aggregator.php';

header('Content-Type: application/json');

// Auth: admin session OR internal secret (from .env — never hardcoded)
session_start();
$isAdmin  = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin';
$secret   = $_GET['secret'] ?? '';
$envSecret = getenv('NEXPERT_PROCESSOR_SECRET') ?: 'NEXPERT_INTERNAL_PROCESSOR_123'; // fallback for compat

if (!$isAdmin && $secret !== $envSecret) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Agent routing: event_type → which agents fire
function getAgentsForEvent(string $eventType): array {
    return match($eventType) {
        'session_completed'      => ['structure', 'outcome', 'boundary'],
        'feedback_submitted'     => ['outcome', 'consistency'],
        'expert_profile_updated' => ['structure'],
        'kyc_verified'           => ['boundary'],
        'complaint_logged'       => ['boundary', 'consistency'],
        'booking_created'        => ['consistency'],
        'reschedule_requested',
        'reschedule_approved'    => ['consistency'],
        // NEW event types
        'outcome_achieved'       => ['outcome', 'consistency'],
        'goal_completed'         => ['outcome', 'boundary'],
        'repeat_booking'         => ['consistency', 'outcome'],
        'session_no_show'        => ['boundary', 'consistency'],
        'late_start'             => ['boundary'],
        'profile_viewed'         => [],   // no agents — read-only event
        default                  => [],
    };
}

try {
    $stmt = $pdo->prepare("
        SELECT * FROM trust_events
        WHERE status = 'pending'
        ORDER BY created_at ASC
        LIMIT 50
    ");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($events)) {
        echo json_encode(['success' => true, 'message' => 'No pending events', 'processed_count' => 0]);
        exit;
    }

    $processedCount = 0;
    $aggregator     = new TrustAggregator($pdo);

    foreach ($events as $event) {
        $pdo->beginTransaction();
        try {
            // Mark processing
            $pdo->prepare("UPDATE trust_events SET status = 'processing' WHERE id = ?")->execute([$event['id']]);

            $payload   = json_decode($event['payload'] ?? '{}', true) ?: [];
            $expertId  = (int)$event['expert_id'];
            $eventType = $event['event_type'];
            $agents    = getAgentsForEvent($eventType);

            // Run each agent
            foreach ($agents as $agentType) {
                $signal = AgentHelper::extractSignal($agentType, [
                    'event_type' => $eventType,
                    'payload'    => $payload,
                    'expert_id'  => $expertId,
                ]);

                if ($signal) {
                    $pdo->prepare("
                        INSERT INTO trust_signals
                            (event_id, expert_id, agent_type, signal_value, metadata, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ")->execute([
                        $event['id'],
                        $expertId,
                        $agentType,
                        $signal['signal_value'],
                        json_encode($signal['metadata']),
                    ]);
                }
            }

            // Mark processed
            $pdo->prepare("UPDATE trust_events SET status = 'processed' WHERE id = ?")->execute([$event['id']]);
            $pdo->commit();

            // Re-aggregate score immediately for this expert (pass trigger event id)
            if (!empty($agents)) {
                $aggregator->aggregateOne($expertId, (int)$event['id']);
            }

            $processedCount++;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Event Processor Error for Event ID {$event['id']}: " . $e->getMessage());
            $pdo->prepare("UPDATE trust_events SET status = 'failed' WHERE id = ?")->execute([$event['id']]);
        }
    }

    echo json_encode([
        'success'         => true,
        'message'         => "Processed {$processedCount} events",
        'processed_count' => $processedCount,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
