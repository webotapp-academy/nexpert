<?php
/**
 * Trust Score Aggregator Cron
 * Aggregates signals into trust scores using Exponential Moving Average (EMA)
 */

require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/trust-aggregator.php';

echo "Starting Trust Score Aggregation...\n";

try {
    $aggregator = new TrustAggregator($pdo);
    $results = $aggregator->aggregateAll();

    foreach ($results as $res) {
        if (isset($res['score'])) {
            echo "Updated Expert {$res['expert_id']}: Score {$res['score']}, Tier {$res['tier']}\n";
        } else {
            echo "Skipped Expert {$res['expert_id']}: No signals found\n";
        }
    }

    echo "Aggregation completed successfully.\n";

} catch (Exception $e) {
    die("Aggregation failed: " . $e->getMessage() . "\n");
}
