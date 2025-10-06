<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once '../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get admin dashboard stats
if ($method === 'GET') {
    try {
        // Total users
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'learner' THEN 1 ELSE 0 END) as total_learners,
                SUM(CASE WHEN role = 'expert' THEN 1 ELSE 0 END) as total_experts,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
            FROM users
        ");
        $users_stats = $stmt->fetch();
        
        // Experts by verification status
        $stmt = $pdo->query("
            SELECT 
                verification_status,
                COUNT(*) as count
            FROM expert_profiles
            GROUP BY verification_status
        ");
        $verification_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Bookings stats
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM bookings
        ");
        $bookings_stats = $stmt->fetch();
        
        // Revenue stats
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(amount) as total_revenue,
                SUM(commission_amount) as total_commission,
                SUM(expert_payout_amount) as total_expert_earnings
            FROM payments
            WHERE status = 'success'
        ");
        $revenue_stats = $stmt->fetch();
        
        // Revenue this month
        $stmt = $pdo->query("
            SELECT 
                SUM(amount) as monthly_revenue,
                SUM(commission_amount) as monthly_commission
            FROM payments
            WHERE status = 'success'
            AND MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $monthly_stats = $stmt->fetch();
        
        // Recent bookings
        $stmt = $pdo->query("
            SELECT b.*, 
                   lp.full_name as learner_name,
                   ep.full_name as expert_name
            FROM bookings b
            LEFT JOIN learner_profiles lp ON lp.user_id = b.learner_id
            LEFT JOIN expert_profiles ep ON ep.user_id = b.expert_id
            ORDER BY b.created_at DESC
            LIMIT 10
        ");
        $recent_bookings = $stmt->fetchAll();
        
        // Pending payouts
        $stmt = $pdo->query("
            SELECT COUNT(*) as count, SUM(amount) as total
            FROM expert_payouts
            WHERE status = 'pending'
        ");
        $pending_payouts = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'users' => $users_stats,
            'verification' => $verification_stats,
            'bookings' => $bookings_stats,
            'revenue' => $revenue_stats,
            'monthly' => $monthly_stats,
            'recent_bookings' => $recent_bookings,
            'pending_payouts' => $pending_payouts
        ]);
    } catch (PDOException $e) {
        error_log('Admin Dashboard Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching dashboard data']);
    }
    exit;
}
?>
