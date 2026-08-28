<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

// Check if expert is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$period = $_GET['period'] ?? 'month';
$view = $_GET['view'] ?? 'monthly';

try {
    $labels = [];
    $data = [];
    
    if ($view === 'daily') {
        // Last 14 days
        $days = 14;
        $stmt = $pdo->prepare("
            SELECT DATE(p.created_at) as pay_date, COALESCE(SUM(p.amount), 0) as total
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            WHERE (p.expert_id = ? OR b.expert_id = ?) AND p.status = 'success'
              AND p.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? DAY)
            GROUP BY DATE(p.created_at)
        ");
        $stmt->execute([$userId, $userId, $days]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateStr = date('Y-m-d', strtotime("-$i days"));
            $label = date('M d', strtotime($dateStr));
            $labels[] = $label;
            $data[] = isset($rows[$dateStr]) ? (float)$rows[$dateStr] : 0.0;
        }
    } elseif ($view === 'weekly') {
        // Last 8 weeks
        $weeks = 8;
        $stmt = $pdo->prepare("
            SELECT YEARWEEK(p.created_at, 1) as yw, COALESCE(SUM(p.amount), 0) as total
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            WHERE (p.expert_id = ? OR b.expert_id = ?) AND p.status = 'success'
              AND p.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? WEEK)
            GROUP BY YEARWEEK(p.created_at, 1)
        ");
        $stmt->execute([$userId, $userId, $weeks]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $time = strtotime("-$i weeks");
            $yw = date('oW', $time);
            $label = 'Wk ' . date('W', $time);
            $labels[] = $label;
            $data[] = isset($rows[$yw]) ? (float)$rows[$yw] : 0.0;
        }
    } else {
        // Monthly view - last 6 months
        $months = 6;
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(p.created_at, '%Y-%m') as ym, COALESCE(SUM(p.amount), 0) as total
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            WHERE (p.expert_id = ? OR b.expert_id = ?) AND p.status = 'success'
              AND p.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
        ");
        $stmt->execute([$userId, $userId, $months]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("-$i months"));
            $label = date('M Y', strtotime($ym . '-01'));
            $labels[] = $label;
            $data[] = isset($rows[$ym]) ? (float)$rows[$ym] : 0.0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $data
    ]);
} catch (PDOException $e) {
    error_log("Earnings Data API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
