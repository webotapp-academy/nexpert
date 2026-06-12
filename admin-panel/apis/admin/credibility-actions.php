<?php
/**
 * Admin Credibility Actions API
 * Handles recomputing scores and freezing experts
 */

require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? null;
        $expertId = $data['expert_id'] ?? null;

        if ($action === 'recompute_all') {
            require_once __DIR__ . '/../connection/trust-aggregator.php';
            $aggregator = new TrustAggregator($pdo);
            $aggregator->aggregateAll();
            
            echo json_encode(['success' => true, 'message' => 'All expert scores recomputed successfully']);
            exit;
        }

        if ($action === 'toggle_freeze') {
            if (!$expertId) throw new Exception("Expert ID required");
            
            $stmt = $pdo->prepare("UPDATE trust_state SET is_frozen = NOT is_frozen WHERE expert_id = ?");
            $stmt->execute([$expertId]);
            
            echo json_encode(['success' => true, 'message' => 'Expert trust status toggled successfully']);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
