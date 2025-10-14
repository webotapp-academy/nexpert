<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

$expertId = $_SESSION['user_id'];

try {
    if ($method === 'GET') {
        // Get KYC details for the expert
        $stmt = $pdo->prepare("
            SELECT * FROM expert_kyc_verification 
            WHERE expert_id = ?
        ");
        $stmt->execute([$expertId]);
        $kyc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $kyc
        ]);
        
    } elseif ($method === 'POST') {
        // Save or submit KYC data
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new Exception('Invalid request data');
        }
        
        // Validate required fields
        $required = ['full_legal_name', 'date_of_birth', 'nationality', 'address_line1', 
                     'city', 'state', 'postal_code', 'country', 'id_document_type', 
                     'id_number', 'account_holder_name', 'bank_name', 'account_number', 
                     'ifsc_code', 'account_type'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Determine status (draft or pending based on submit flag)
        $status = isset($data['submit']) && $data['submit'] === true ? 'pending' : 'draft';
        $submitted_at = $status === 'pending' ? date('Y-m-d H:i:s') : null;
        
        // Check if KYC record exists
        $stmt = $pdo->prepare("SELECT id FROM expert_kyc_verification WHERE expert_id = ?");
        $stmt->execute([$expertId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $stmt = $pdo->prepare("
                UPDATE expert_kyc_verification SET
                    full_legal_name = ?,
                    date_of_birth = ?,
                    nationality = ?,
                    gender = ?,
                    address_line1 = ?,
                    city = ?,
                    state = ?,
                    postal_code = ?,
                    country = ?,
                    id_document_type = ?,
                    id_number = ?,
                    id_document_front_url = ?,
                    id_document_back_url = ?,
                    account_holder_name = ?,
                    bank_name = ?,
                    account_number = ?,
                    ifsc_code = ?,
                    account_type = ?,
                    verification_status = ?,
                    submitted_at = ?,
                    updated_at = NOW()
                WHERE expert_id = ?
            ");
            
            $stmt->execute([
                $data['full_legal_name'],
                $data['date_of_birth'],
                $data['nationality'],
                $data['gender'] ?? null,
                $data['address_line1'],
                $data['city'],
                $data['state'],
                $data['postal_code'],
                $data['country'],
                $data['id_document_type'],
                $data['id_number'],
                $data['id_document_front_url'] ?? null,
                $data['id_document_back_url'] ?? null,
                $data['account_holder_name'],
                $data['bank_name'],
                $data['account_number'],
                $data['ifsc_code'],
                $data['account_type'],
                $status,
                $submitted_at,
                $expertId
            ]);
            
            $message = $status === 'pending' ? 'KYC submitted successfully!' : 'KYC saved as draft!';
            
        } else {
            // Insert new record
            $stmt = $pdo->prepare("
                INSERT INTO expert_kyc_verification (
                    expert_id, full_legal_name, date_of_birth, nationality, gender,
                    address_line1, city, state, postal_code, country,
                    id_document_type, id_number, id_document_front_url, id_document_back_url,
                    account_holder_name, bank_name, account_number, ifsc_code, account_type,
                    verification_status, submitted_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $expertId,
                $data['full_legal_name'],
                $data['date_of_birth'],
                $data['nationality'],
                $data['gender'] ?? null,
                $data['address_line1'],
                $data['city'],
                $data['state'],
                $data['postal_code'],
                $data['country'],
                $data['id_document_type'],
                $data['id_number'],
                $data['id_document_front_url'] ?? null,
                $data['id_document_back_url'] ?? null,
                $data['account_holder_name'],
                $data['bank_name'],
                $data['account_number'],
                $data['ifsc_code'],
                $data['account_type'],
                $status,
                $submitted_at
            ]);
            
            $message = $status === 'pending' ? 'KYC submitted successfully!' : 'KYC saved as draft!';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
        
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
