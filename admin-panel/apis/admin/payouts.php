<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once '../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get payout requests
if ($method === 'GET') {
    $status = $_GET['status'] ?? null;
    
    try {
        $sql = "
            SELECT po.*, ep.full_name as expert_name, u.email as expert_email
            FROM expert_payouts po
            JOIN expert_profiles ep ON ep.user_id = po.expert_id
            JOIN users u ON u.id = po.expert_id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " AND po.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY po.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $payouts = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'payouts' => $payouts]);
    } catch (PDOException $e) {
        error_log('Admin Get Payouts Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching payouts']);
    }
    exit;
}

// Update payout status
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $payout_id = $data['payout_id'] ?? null;
    $status = $data['status'] ?? null;
    $transaction_id = $data['transaction_id'] ?? null;
    $admin_notes = $data['admin_notes'] ?? '';
    
    if (!$payout_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Payout ID and status are required']);
        exit;
    }
    
    try {
        $processed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("
            UPDATE expert_payouts
            SET status = ?, transaction_id = ?, processed_at = ?, admin_notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $transaction_id, $processed_at, $admin_notes, $payout_id]);
        
        echo json_encode(['success' => true, 'message' => 'Payout status updated successfully']);
    } catch (PDOException $e) {
        error_log('Admin Update Payout Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating payout']);
    }
    exit;
}
?>
