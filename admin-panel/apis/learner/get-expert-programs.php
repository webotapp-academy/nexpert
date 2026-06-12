<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

// This is a public API endpoint - no authentication required
// Expert programs are publicly viewable on their profile page

try {
    $expertId = $_GET['expert_id'] ?? null;
    
    if (!$expertId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    // Get all active programs from this expert
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            description,
            duration_weeks,
            price_inr,
            goal_outcome,
            created_at
        FROM workflows
        WHERE expert_id = ?
        AND is_active = 1
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$expertId]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'programs' => $programs,
        'count' => count($programs)
    ]);
    
} catch (PDOException $e) {
    error_log("Get Expert Programs API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch programs'
    ]);
}
