<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get all payments/transactions
if ($method === 'GET') {
    $status = $_GET['status'] ?? null;
    $learner_id = $_GET['learner_id'] ?? null;
    $expert_id = $_GET['expert_id'] ?? null;
    
    try {
        $sql = "
            SELECT p.*, 
                   lp.full_name as learner_name,
                   ep.full_name as expert_name,
                   lu.email as learner_email,
                   eu.email as expert_email,
                   b.session_datetime
            FROM payments p
            LEFT JOIN bookings b ON b.id = p.booking_id
            LEFT JOIN learner_profiles lp ON lp.user_id = p.learner_id
            LEFT JOIN expert_profiles ep ON ep.user_id = p.expert_id
            LEFT JOIN users lu ON lu.id = p.learner_id
            LEFT JOIN users eu ON eu.id = p.expert_id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        if ($learner_id) {
            $sql .= " AND p.learner_id = ?";
            $params[] = $learner_id;
        }
        
        if ($expert_id) {
            $sql .= " AND p.expert_id = ?";
            $params[] = $expert_id;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();
        
        // Calculate summary stats
        $statsStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'success' THEN commission_amount ELSE 0 END) as total_commission,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'refunded' THEN refund_amount ELSE 0 END) as total_refunds
            FROM payments
        ");
        $stats = $statsStmt->fetch();
        
        echo json_encode(['success' => true, 'payments' => $payments, 'stats' => $stats]);
    } catch (PDOException $e) {
        error_log('Admin Get Payments Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching payments']);
    }
    exit;
}

// Process refund
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $payment_id = $input['payment_id'] ?? null;
    $refund_amount = $input['refund_amount'] ?? null;
    $refund_reason = $input['refund_reason'] ?? null;
    
    if (!$payment_id || !$refund_amount) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update payment record
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'refunded', 
                refund_amount = ?, 
                refund_reason = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$refund_amount, $refund_reason, $payment_id]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Refund processed successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Admin Process Refund Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing refund']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>
