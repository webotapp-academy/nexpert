<?php
/**
 * Trust Score Aggregator Cron — MVP2
 * Run every hour: php cron/update_trust_scores.php
 * Logs output for monitoring.
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/trust-aggregator.php';

$startTime  = microtime(true);
$logLines   = [];
$logLines[] = "[" . date('Y-m-d H:i:s') . "] Trust aggregation started";

try {
    $aggregator = new TrustAggregator($pdo);
    $results    = $aggregator->aggregateAll();

    $scored   = 0; $frozen = 0; $noSig = 0; $errors = 0;
    foreach ($results as $res) {
        if (isset($res['score'])) {
            $logLines[] = "  ✓ Expert {$res['expert_id']}: {$res['score']} ({$res['band']}) conf={$res['confidence']}% trend={$res['trend']}";
            $scored++;
        } elseif (($res['status'] ?? '') === 'frozen') {
            $frozen++;
        } elseif (($res['status'] ?? '') === 'no_signals') {
            // Auto-seed a baseline event so new approved experts get scored
            $pdo->prepare("
                INSERT INTO trust_events (event_type, expert_id, payload, status, created_at)
                VALUES ('expert_profile_updated', ?, '{\"source\":\"auto_baseline\"}', 'pending', NOW())
            ")->execute([$res['expert_id']]);
            $logLines[] = "  → Expert {$res['expert_id']}: baseline event seeded (no signals)";
            $noSig++;
        }
    }

    $elapsed    = round(microtime(true) - $startTime, 2);
    $logLines[] = "[" . date('Y-m-d H:i:s') . "] Completed in {$elapsed}s — Scored: {$scored}, Frozen: {$frozen}, No signals (seeded): {$noSig}";

} catch (Exception $e) {
    $logLines[] = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage();
    // Alert founder via email on cron failure
    @mail('admin@nexpertapp.com','[NEXPERT ALERT] Trust cron failed',$e->getMessage(),'From: cron@nexpertapp.com');
}

// Write log
$logPath = __DIR__ . '/../logs/trust_cron.log';
@mkdir(dirname($logPath), 0755, true);
file_put_contents($logPath, implode("\n", $logLines) . "\n", FILE_APPEND | LOCK_EX);

// Echo to stdout (for cron email)
echo implode("\n", $logLines) . "\n";
