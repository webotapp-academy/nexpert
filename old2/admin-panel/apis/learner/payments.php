<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

// Start session to get learner_id
session_start();

// Check if learner is logged in
if (!isset($_SESSION['learner_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$learner_id = $_SESSION['learner_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get recent transactions
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.amount,
                p.payment_status,
                p.payment_method,
                p.transaction_id,
                p.created_at,
                u.name as expert_name,
                ep.professional_title as expert_title,
                b.session_date,
                b.session_time
            FROM payments p
            INNER JOIN bookings b ON p.booking_id = b.id
            INNER JOIN users u ON b.expert_id = u.id
            INNER JOIN expert_profiles ep ON u.id = ep.user_id
            WHERE p.learner_id = ?
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$learner_id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get payment statistics
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_spent,
                SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as pending_amount
            FROM payments
            WHERE learner_id = ?
        ");
        $statsStmt->execute([$learner_id]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'stats' => $stats
            ]
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Learner Payments API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
