<?php
require_once __DIR__ . '/../../../includes/session-config.php';
require_once __DIR__ . '/auth-check.php';
header('Content-Type: application/json');

validateCSRF();

require_once '../connection/pdo.php';
require_once '../connection/email-helper.php';

$method = $_SERVER['REQUEST_METHOD'];

// Get payout requests
if ($method === 'GET') {
    $status = $_GET['status'] ?? null;
    $payoutId = $_GET['payout_id'] ?? null;
    
    try {
        // Get single payout with bank details
        if ($payoutId) {
            $stmt = $pdo->prepare("
                SELECT 
                    po.*, 
                    ep.full_name as expert_name, 
                    u.email as expert_email,
                    ekv.account_number,
                    ekv.ifsc_code,
                    ekv.account_holder_name,
                    ekv.bank_name
                FROM expert_payouts po
                JOIN expert_profiles ep ON ep.user_id = po.expert_id
                JOIN users u ON u.id = po.expert_id
                LEFT JOIN expert_kyc_verification ekv ON ekv.expert_id = po.expert_id
                WHERE po.id = ?
            ");
            $stmt->execute([$payoutId]);
            $payout = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($payout) {
                // Format bank details
                $bankDetails = null;
                if ($payout['account_number']) {
                    $bankDetails = [
                        'account_number' => $payout['account_number'],
                        'ifsc_code' => $payout['ifsc_code'],
                        'account_holder_name' => $payout['account_holder_name'],
                        'bank_name' => $payout['bank_name']
                    ];
                }
                
                echo json_encode([
                    'success' => true, 
                    'payout' => [
                        'id' => $payout['id'],
                        'expert_name' => $payout['expert_name'],
                        'expert_email' => $payout['expert_email'],
                        'amount' => $payout['amount'],
                        'status' => $payout['status'],
                        'created_at' => $payout['created_at'],
                        'bank_details' => $bankDetails
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Payout not found']);
            }
            exit;
        }
        
        // Get all payouts (list view)
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
        $payout_date = ($status === 'completed' || $status === 'processed') ? date('Y-m-d H:i:s') : null;
        
        // Map status to database enum values
        $dbStatus = $status;
        if ($status === 'completed') {
            $dbStatus = 'processed'; // Map 'completed' to 'processed' for database
        }
        
        $stmt = $pdo->prepare("
            UPDATE expert_payouts
            SET status = ?, payout_reference = ?, payout_date = ?, notes = ?
            WHERE id = ?
        ");
        $stmt->execute([$dbStatus, $transaction_id, $payout_date, $admin_notes, $payout_id]);
        
        // Get payout and expert details for email
        $stmt = $pdo->prepare("
            SELECT po.amount, ep.full_name, u.email, 
                   COALESCE(kyc.account_number, '****') as account_number
            FROM expert_payouts po
            JOIN expert_profiles ep ON ep.user_id = po.expert_id
            JOIN users u ON u.id = po.expert_id
            LEFT JOIN expert_kyc_verification kyc ON kyc.expert_id = po.expert_id
            WHERE po.id = ?
        ");
        $stmt->execute([$payout_id]);
        $payoutDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Send email based on status
        if ($payoutDetails) {
            $emailHelper = new EmailHelper();
            $bankLast4 = !empty($payoutDetails['account_number']) && $payoutDetails['account_number'] !== '****' 
                ? substr($payoutDetails['account_number'], -4) 
                : '****';
            
            if ($status === 'processing') {
                $emailHelper->sendWithdrawalInitiatedEmail(
                    $payoutDetails['email'],
                    $payoutDetails['full_name'],
                    $payoutDetails['amount'],
                    $bankLast4
                );
            } elseif ($status === 'completed' && $transaction_id) {
                $emailHelper->sendWithdrawalCompletedEmail(
                    $payoutDetails['email'],
                    $payoutDetails['full_name'],
                    $payoutDetails['amount'],
                    $transaction_id,
                    $bankLast4
                );
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Payout status updated successfully']);
    } catch (PDOException $e) {
        error_log('Admin Update Payout Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating payout: ' . $e->getMessage()]);
    }
    exit;
}
?>
