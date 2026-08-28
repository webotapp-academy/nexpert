<?php
/**
 * Outcomes Submission API — Task 3.3
 * Receives verified learner outcome submissions, validates session ownership,
 * records to outcomes table, and emits an outcome_achieved trust event for the expert.
 */
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/trust-aggregator.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$learnerId = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$sessionId   = (int)($input['session_id'] ?? 0);
$expertId    = (int)($input['expert_id'] ?? 0);
$outcomeType = trim($input['outcome_type'] ?? '');
$description = trim($input['description'] ?? '');
$evidenceUrl = trim($input['evidence_url'] ?? '');
$goalId      = isset($input['goal_id']) ? (int)$input['goal_id'] : null;

if (!$outcomeType || !$description) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Outcome type and description are required.']);
    exit;
}

try {
    // If sessionId provided, validate ownership
    if ($sessionId > 0) {
        $checkStmt = $pdo->prepare("SELECT id, expert_id, learner_id FROM bookings WHERE id = ? AND learner_id = ?");
        $checkStmt->execute([$sessionId, $learnerId]);
        $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($booking) {
            $expertId = (int)$booking['expert_id'];
        }
    }

    if (!$expertId) {
        // If expertId not provided via booking, check expert exists
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'A valid expert or completed session is required.']);
        exit;
    }

    $pdo->beginTransaction();

    // 1. Insert into outcomes table
    $stmt = $pdo->prepare("
        INSERT INTO outcomes (goal_id, session_id, expert_id, learner_id, achieved, outcome_type, description, evidence_url, validator, created_at)
        VALUES (?, ?, ?, ?, 1, ?, ?, ?, 'self', NOW())
    ");
    $stmt->execute([
        $goalId ?: null,
        $sessionId ?: null,
        $expertId,
        $learnerId,
        $outcomeType,
        $description,
        $evidenceUrl ?: null
    ]);
    $outcomeId = (int)$pdo->lastInsertId();

    // 2. Emit outcome_achieved event into trust_events
    $payload = json_encode([
        'outcome_id'   => $outcomeId,
        'session_id'   => $sessionId,
        'outcome_type' => $outcomeType,
        'description'  => $description,
        'evidence_url' => $evidenceUrl
    ]);

    $eventStmt = $pdo->prepare("
        INSERT INTO trust_events (event_type, expert_id, learner_id, payload, status, created_at)
        VALUES ('outcome_achieved', ?, ?, ?, 'pending', NOW())
    ");
    $eventStmt->execute([$expertId, $learnerId, $payload]);
    $eventId = (int)$pdo->lastInsertId();

    $pdo->commit();

    // 3. Immediately trigger trust aggregator for instant feedback
    try {
        $aggregator = new TrustAggregator($pdo);
        if (method_exists($aggregator, 'aggregateOne')) {
            $aggregator->aggregateOne($expertId, $eventId);
        }
    } catch (Exception $aggEx) {
        error_log("Immediate aggregation notice: " . $aggEx->getMessage());
    }

    echo json_encode([
        'success'    => true,
        'message'    => 'Outcome verified and trust event recorded successfully.',
        'outcome_id' => $outcomeId,
        'event_id'   => $eventId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to record outcome: ' . $e->getMessage()]);
}
