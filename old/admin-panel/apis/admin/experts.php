<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once '../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get all experts
if ($method === 'GET' && !isset($_GET['expert_id'])) {
    $status = $_GET['status'] ?? null;
    $verification_status = $_GET['verification_status'] ?? null;
    
    try {
        $sql = "
            SELECT u.id as user_id, u.email, u.phone, u.status as account_status, u.created_at,
                   ep.full_name, ep.bio_short, ep.bio_full, ep.expertise_verticals,
                   ep.verification_status, ep.govt_id_url, ep.credentials, ep.profile_photo,
                   COUNT(DISTINCT b.id) as total_bookings,
                   SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                   COALESCE(SUM(p.expert_payout_amount), 0) as total_earnings
            FROM users u
            LEFT JOIN expert_profiles ep ON ep.user_id = u.id
            LEFT JOIN bookings b ON b.expert_id = u.id
            LEFT JOIN payments p ON p.expert_id = u.id AND p.status = 'success'
            WHERE u.role = 'expert'
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }
        
        if ($verification_status) {
            $sql .= " AND ep.verification_status = ?";
            $params[] = $verification_status;
        }
        
        $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $experts = $stmt->fetchAll();
        
        foreach ($experts as &$expert) {
            $expert['expertise_verticals'] = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
        }
        
        echo json_encode(['success' => true, 'experts' => $experts]);
    } catch (PDOException $e) {
        error_log('Admin Get Experts Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching experts: ' . $e->getMessage()]);
    }
    exit;
}

// Get single expert details
if ($method === 'GET' && isset($_GET['expert_id'])) {
    $expert_id = $_GET['expert_id'];
    
    try {
        $stmt = $pdo->prepare("
            SELECT ep.*, u.email, u.phone, u.status as account_status, u.created_at as account_created
            FROM expert_profiles ep
            JOIN users u ON u.id = ep.user_id
            WHERE ep.user_id = ?
        ");
        $stmt->execute([$expert_id]);
        $expert = $stmt->fetch();
        
        if (!$expert) {
            echo json_encode(['success' => false, 'message' => 'Expert not found']);
            exit;
        }
        
        $expert['expertise_verticals'] = json_decode($expert['expertise_verticals'], true);
        $expert['certification_urls'] = json_decode($expert['certification_urls'], true);
        
        // Get bookings stats
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            FROM bookings WHERE expert_id = ?
        ");
        $stmt->execute([$expert_id]);
        $expert['bookings_stats'] = $stmt->fetch();
        
        // Get earnings
        $stmt = $pdo->prepare("
            SELECT SUM(expert_payout_amount) as total_earnings
            FROM payments WHERE expert_id = ? AND status = 'success'
        ");
        $stmt->execute([$expert_id]);
        $expert['earnings'] = $stmt->fetch();
        
        echo json_encode(['success' => true, 'expert' => $expert]);
    } catch (PDOException $e) {
        error_log('Admin Get Expert Details Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching expert details']);
    }
    exit;
}

// Update expert verification status
if ($method === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'verify') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $verification_status = $data['verification_status'] ?? null;
    $admin_notes = $data['admin_notes'] ?? '';
    
    if (!$expert_id || !$verification_status) {
        echo json_encode(['success' => false, 'message' => 'Expert ID and verification status are required']);
        exit;
    }
    
    try {
        $verified_at = $verification_status === 'verified' ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("
            UPDATE expert_profiles
            SET verification_status = ?, verified_at = ?, admin_notes = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$verification_status, $verified_at, $admin_notes, $expert_id]);
        
        echo json_encode(['success' => true, 'message' => 'Verification status updated successfully']);
    } catch (PDOException $e) {
        error_log('Admin Update Verification Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating verification status']);
    }
    exit;
}

// Update expert account status
if ($method === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'status') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$expert_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Expert ID and status are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $expert_id]);
        
        echo json_encode(['success' => true, 'message' => 'Account status updated successfully']);
    } catch (PDOException $e) {
        error_log('Admin Update Status Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating account status']);
    }
    exit;
}

// Delete expert
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $expert_id = $data['expert_id'] ?? null;
    
    if (!$expert_id) {
        echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
        exit;
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete expert profile
        $stmt = $pdo->prepare("DELETE FROM expert_profiles WHERE user_id = ?");
        $stmt->execute([$expert_id]);
        
        // Delete user account
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$expert_id]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Expert deleted successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Admin Delete Expert Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting expert']);
    }
    exit;
}
?>
