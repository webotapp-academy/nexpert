 <?php
header('Content-Type: application/json');

require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/email-helper.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $expert_id = $data['expert_id'] ?? null;
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'INR';
    
    if (!$expert_id || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid expert ID and amount are required']);
        exit;
    }
    
    // Verify that the logged-in user is requesting for themselves
    if ($expert_id != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized request']);
        exit;
    }
    
    try {
        // Get expert details and KYC information
        $stmt = $pdo->prepare("
            SELECT 
                u.email,
                ep.full_name,
                ekv.account_number
            FROM users u
            JOIN expert_profiles ep ON u.id = ep.user_id
            LEFT JOIN expert_kyc_verification ekv ON ep.user_id = ekv.expert_id
            WHERE u.id = ?
        ");
        $stmt->execute([$expert_id]);
        $expert = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$expert) {
            echo json_encode(['success' => false, 'message' => 'Expert not found']);
            exit;
        }
        
        // Check if expert has completed KYC
        if (empty($expert['account_number'])) {
            echo json_encode(['success' => false, 'message' => 'Please complete your KYC verification before requesting a payout']);
            exit;
        }
        
        // Insert payout request
        $stmt = $pdo->prepare("
            INSERT INTO expert_payouts (expert_id, amount, currency, status, created_at)
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$expert_id, $amount, $currency]);
        $payoutId = $pdo->lastInsertId();
        
        // Get last 4 digits of account number
        $accountLast4 = substr($expert['account_number'], -4);
        
        // Send withdrawal initiated email
        $emailHelper = new EmailHelper();
        $emailResult = $emailHelper->sendWithdrawalInitiatedEmail(
            $expert['email'],
            $expert['full_name'],
            $amount,
            $accountLast4
        );
        
        if (!$emailResult['success']) {
            error_log('Failed to send withdrawal email: ' . ($emailResult['error'] ?? 'Unknown error'));
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payout request submitted successfully', 
            'id' => $payoutId,
            'email_sent' => $emailResult['success']
        ]);
        
    } catch (PDOException $e) {
        error_log('Request Payout Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while submitting payout request']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>
