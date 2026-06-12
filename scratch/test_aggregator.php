<?php
require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';
require_once __DIR__ . '/../admin-panel/apis/connection/trust-aggregator.php';

try {
    $aggregator = new TrustAggregator($pdo);
    $results = $aggregator->aggregateAll();
    echo "Success: Aggregated " . count($results) . " experts.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
