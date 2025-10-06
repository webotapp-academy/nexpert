<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - expert_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get expert dashboard stats
if ($method === 'GET') {
    $expert_id = $_GET['expert_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        // Total bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM bookings WHERE expert_id = ?
        ");
        $stmt->execute([$expert_id]);
        $bookings_stats = $stmt->fetch();
        
        // Total learners
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT learner_id) as total_learners
            FROM bookings WHERE expert_id = ?
        ");
        $stmt->execute([$expert_id]);
        $learners_stats = $stmt->fetch();
        
        // Earnings this month
        $stmt = $pdo->prepare("
            SELECT SUM(expert_payout_amount) as earnings
            FROM payments
            WHERE expert_id = ? AND status = 'success'
            AND MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        $stmt->execute([$expert_id]);
        $earnings_stats = $stmt->fetch();
        
        // Upcoming sessions (next 7 days)
        $stmt = $pdo->prepare("
            SELECT b.*, lp.full_name as learner_name, lp.profile_photo as learner_photo
            FROM bookings b
            LEFT JOIN learner_profiles lp ON lp.user_id = b.learner_id
            WHERE b.expert_id = ? 
            AND b.status IN ('pending', 'confirmed')
            AND b.session_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
            ORDER BY b.session_datetime ASC
            LIMIT 5
        ");
        $stmt->execute([$expert_id]);
        $upcoming_sessions = $stmt->fetchAll();
        
        // Recent reviews
        $stmt = $pdo->prepare("
            SELECT r.*, lp.full_name as learner_name, lp.profile_photo as learner_photo
            FROM reviews r
            LEFT JOIN learner_profiles lp ON lp.user_id = r.learner_id
            WHERE r.expert_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$expert_id]);
        $recent_reviews = $stmt->fetchAll();
        
        // Average rating
        $stmt = $pdo->prepare("
            SELECT rating_average, total_reviews
            FROM expert_profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$expert_id]);
        $rating_stats = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'bookings' => $bookings_stats,
            'learners' => $learners_stats,
            'earnings' => $earnings_stats,
            'rating' => $rating_stats,
            'upcoming_sessions' => $upcoming_sessions,
            'recent_reviews' => $recent_reviews
        ]);
    } catch (PDOException $e) {
        error_log('Dashboard API Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching dashboard data']);
    }
}
?>
