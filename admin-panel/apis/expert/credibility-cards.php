<?php
/**
 * Daily Credibility Cards API — Nexpert AI
 * Endpoints:
 * - GET: Fetch available cards for an expert
 * - POST action=generate: Trigger on-demand generation
 * - POST action=share-linkedin: Record LinkedIn share
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
$sessionPath = dirname(__DIR__, 3) . '/includes/session-config.php';
if (file_exists($sessionPath)) {
    require_once $sessionPath;
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];
$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$expertId = isset($_GET['expert_id']) ? (int)$_GET['expert_id'] : (isset($_POST['expert_id']) ? (int)$_POST['expert_id'] : $currentUserId);

if ($method === 'GET') {
    $cardId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $latestOnly = isset($_GET['latest']) ? (bool)$_GET['latest'] : false;

    if ($cardId > 0) {
        $stmt = $pdo->prepare("
            SELECT c.*, ep.full_name as expert_name, ep.profile_photo, ep.tagline
            FROM credibility_card_events c
            LEFT JOIN expert_profiles ep ON ep.user_id = c.expert_id
            WHERE c.id = ?
        ");
        $stmt->execute([$cardId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$card) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Card not found']);
            exit;
        }

        $card['card_data'] = json_decode($card['card_data'], true);
        $card['trigger_condition'] = json_decode($card['trigger_condition'], true);

        echo json_encode(['success' => true, 'data' => $card]);
        exit;
    }

    if ($expertId <= 0) {
        // Fallback to top scored expert if not logged in (for public preview)
        $topExpertId = $pdo->query("SELECT user_id FROM expert_profiles WHERE verification_status = 'approved' ORDER BY id ASC LIMIT 1")->fetchColumn();
        $expertId = $topExpertId ? (int)$topExpertId : 127;
    }

    $limit = $latestOnly ? 1 : 20;
    $stmt = $pdo->prepare("
        SELECT c.*, ep.full_name as expert_name, ep.profile_photo, ep.tagline
        FROM credibility_card_events c
        LEFT JOIN expert_profiles ep ON ep.user_id = c.expert_id
        WHERE c.expert_id = ?
        ORDER BY c.generated_at DESC
        LIMIT {$limit}
    ");
    $stmt->execute([$expertId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no card exists for expert, generate one on the fly
    if (empty($cards)) {
        require_once dirname(__DIR__, 3) . '/cron/generate_credibility_cards.php';
        $stmt->execute([$expertId]);
        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($cards as &$card) {
        $card['card_data'] = json_decode($card['card_data'], true);
        $card['trigger_condition'] = json_decode($card['trigger_condition'], true);
    }

    echo json_encode([
        'success' => true,
        'count' => count($cards),
        'data' => $latestOnly ? ($cards[0] ?? null) : $cards
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? ($_GET['action'] ?? 'generate');

    if ($action === 'generate') {
        if ($expertId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID required']);
            exit;
        }

        // Run card generator for this expert
        require_once dirname(__DIR__, 3) . '/cron/generate_credibility_cards.php';

        $stmt = $pdo->prepare("
            SELECT * FROM credibility_card_events
            WHERE expert_id = ?
            ORDER BY generated_at DESC
            LIMIT 1
        ");
        $stmt->execute([$expertId]);
        $card = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($card) {
            $card['card_data'] = json_decode($card['card_data'], true);
            $card['trigger_condition'] = json_decode($card['trigger_condition'], true);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Card generated successfully',
            'data' => $card
        ]);
        exit;
    }

    if ($action === 'share-linkedin') {
        $cardId = (int)($input['card_id'] ?? 0);
        $shareUrl = $input['share_url'] ?? 'https://www.linkedin.com/feed/';

        if ($cardId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Card ID required']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE credibility_card_events
            SET shared_to_linkedin = 1, shared_at = NOW(), share_url = ?
            WHERE id = ?
        ");
        $stmt->execute([$shareUrl, $cardId]);

        echo json_encode([
            'success' => true,
            'message' => 'LinkedIn share recorded successfully',
            'data' => [
                'card_id' => $cardId,
                'shared_at' => date('Y-m-d H:i:s'),
                'share_url' => $shareUrl
            ]
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
