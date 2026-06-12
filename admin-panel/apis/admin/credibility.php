<?php
/**
 * Admin Credibility API
 * Fetches trust state and history for experts
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
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get_experts_trust';

        if ($action === 'get_experts_trust') {
            $stmt = $pdo->prepare("
                SELECT 
                    u.id as expert_id, u.email,
                    ep.full_name, ep.profile_photo,
                    ts.overall_score, ts.trust_tier, ts.stability_score,
                    ts.structure_score, ts.outcome_score, ts.boundary_score, ts.consistency_score,
                    ts.last_updated, ts.is_frozen
                FROM users u
                JOIN expert_profiles ep ON u.id = ep.user_id
                LEFT JOIN trust_state ts ON u.id = ts.expert_id
                WHERE u.role = 'expert'
                ORDER BY ts.overall_score DESC
            ");
            $stmt->execute();
            $experts = $stmt->fetchAll();

            echo json_encode(['success' => true, 'experts' => $experts]);
            exit;
        }

        if ($action === 'get_expert_timeline') {
            $expertId = $_GET['expert_id'] ?? null;
            if (!$expertId) throw new Exception("Expert ID required");

            $stmt = $pdo->prepare("
                SELECT * FROM trust_state_history 
                WHERE expert_id = ? 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $stmt->execute([$expertId]);
            $history = $stmt->fetchAll();

            echo json_encode(['success' => true, 'history' => $history]);
            exit;
        }

        if ($action === 'get_expert_signals') {
            $expertId = $_GET['expert_id'] ?? null;
            if (!$expertId) throw new Exception("Expert ID required");

            $stmt = $pdo->prepare("
                SELECT * FROM trust_signals 
                WHERE expert_id = ? 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            $stmt->execute([$expertId]);
            $signals = $stmt->fetchAll();

            foreach ($signals as &$signal) {
                if ($signal['metadata']) {
                    $signal['metadata'] = json_decode($signal['metadata'], true);
                }
            }

            echo json_encode(['success' => true, 'signals' => $signals]);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
