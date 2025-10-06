<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get earnings dashboard
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    $period = $_GET['period'] ?? 'all';
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        $dateFilter = "";
        $params = [$expert_id];
        
        switch ($period) {
            case 'today':
                $dateFilter = " AND DATE(p.created_at) = CURDATE()";
                break;
            case 'this_month':
                $dateFilter = " AND MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())";
                break;
            case 'this_year':
                $dateFilter = " AND YEAR(p.created_at) = YEAR(CURDATE())";
                break;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(amount) as total_revenue,
                SUM(commission_amount) as total_commission,
                SUM(expert_payout_amount) as total_earnings
            FROM payments
            WHERE expert_id = ? AND status = 'success'" . $dateFilter
        );
        $stmt->execute($params);
        $totals = $stmt->fetch();
        
        $stmt = $pdo->prepare("
            SELECT SUM(expert_payout_amount) as pending_payout
            FROM payments
            WHERE expert_id = ? AND status = 'success'
            AND id NOT IN (SELECT payment_id FROM expert_payouts WHERE expert_id = ?)
        ");
        $stmt->execute([$expert_id, $expert_id]);
        $pending = $stmt->fetch();
        
        $stmt = $pdo->prepare("
            SELECT * FROM expert_payouts
            WHERE expert_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$expert_id]);
        $payouts = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(amount) as revenue,
                SUM(expert_payout_amount) as earnings,
                COUNT(*) as bookings
            FROM payments
            WHERE expert_id = ? AND status = 'success'
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY month
            ORDER BY month DESC
        ");
        $stmt->execute([$expert_id]);
        $monthly_revenue = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'totals' => $totals,
            'pending_payout' => $pending['pending_payout'] ?? 0,
            'payouts' => $payouts,
            'monthly_revenue' => $monthly_revenue
        ]);
    } catch (PDOException $e) {
        error_log('Get Earnings Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching earnings']);
    }
    exit;
}

// Request payout
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'USD';
    
    if (!$expert_id || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid expert ID and amount are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO expert_payouts (expert_id, amount, currency, status)
            VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$expert_id, $amount, $currency]);
        
        echo json_encode(['success' => true, 'message' => 'Payout request submitted successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log('Request Payout Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while submitting payout request']);
    }
    exit;
}
?>
