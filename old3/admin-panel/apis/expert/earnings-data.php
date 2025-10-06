<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

session_start();

// Check if expert is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'month';
$view = $_GET['view'] ?? 'monthly';

try {
    // Get expert profile ID
    $stmt = $pdo->prepare("SELECT id FROM expert_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        echo json_encode(['success' => false, 'message' => 'Expert profile not found']);
        exit;
    }
    
    $expertId = $profile['id'];
    
    // Build date filter based on period
    $dateFilter = '';
    switch ($period) {
        case 'last_month':
            $dateFilter = "AND MONTH(p.created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) AND YEAR(p.created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
            break;
        case 'last_3_months':
            $dateFilter = "AND p.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)";
            break;
        case 'year':
            $dateFilter = "AND YEAR(p.created_at) = YEAR(CURRENT_DATE)";
            break;
        case 'month':
        default:
            $dateFilter = "AND MONTH(p.created_at) = MONTH(CURRENT_DATE) AND YEAR(p.created_at) = YEAR(CURRENT_DATE)";
            break;
    }
    
    // Build grouping based on view
    $groupBy = '';
    $dateFormat = '';
    switch ($view) {
        case 'daily':
            $dateFormat = "DATE_FORMAT(p.created_at, '%b %d')";
            $groupBy = "DATE(p.created_at)";
            break;
        case 'weekly':
            $dateFormat = "DATE_FORMAT(p.created_at, 'Week %u')";
            $groupBy = "WEEK(p.created_at)";
            break;
        case 'monthly':
        default:
            $dateFormat = "DATE_FORMAT(p.created_at, '%b')";
            $groupBy = "MONTH(p.created_at)";
            break;
    }
    
    // Fetch earnings data grouped by the specified period
    $stmt = $pdo->prepare("
        SELECT 
            $dateFormat as label,
            COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE b.expert_id = ? AND p.status = 'completed' $dateFilter
        GROUP BY $groupBy
        ORDER BY p.created_at ASC
    ");
    $stmt->execute([$expertId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $data = [];
    
    foreach ($results as $row) {
        $labels[] = $row['label'];
        $data[] = (float)$row['total'];
    }
    
    // If no data, provide empty arrays
    if (empty($labels)) {
        $labels = ['No Data'];
        $data = [0];
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
