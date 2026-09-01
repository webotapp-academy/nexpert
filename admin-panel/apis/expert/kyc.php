<?php
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
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

$expertId = (int)$_SESSION['user_id'];

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
        // Support both multipart/form-data and JSON input
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $data = $_POST;
        }
        
        if (empty($data)) {
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
        
        // Fetch existing record if any
        $stmt = $pdo->prepare("SELECT * FROM expert_kyc_verification WHERE expert_id = ?");
        $stmt->execute([$expertId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // Handle file uploads
        $frontUrl = $existing['id_document_front_url'] ?? ($data['id_document_front_url'] ?? null);
        $backUrl = $existing['id_document_back_url'] ?? ($data['id_document_back_url'] ?? null);

        $uploadDir = dirname(dirname(dirname(__DIR__))) . '/uploads/kyc/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (isset($_FILES['id_document_front']) && $_FILES['id_document_front']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['id_document_front'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf', 'webp'])) {
                $filename = 'kyc_' . $expertId . '_front_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $frontUrl = 'uploads/kyc/' . $filename;
                }
            }
        }

        if (isset($_FILES['id_document_back']) && $_FILES['id_document_back']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['id_document_back'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf', 'webp'])) {
                $filename = 'kyc_' . $expertId . '_back_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $backUrl = 'uploads/kyc/' . $filename;
                }
            }
        }

        $idDocPath = !empty($frontUrl) ? $frontUrl : ($existing['id_document_path'] ?? '');

        // Determine status (draft or pending based on submit flag)
        $isSubmit = (isset($data['submit']) && ($data['submit'] === true || $data['submit'] === 'true' || $data['submit'] === '1'));
        $status = $isSubmit ? 'pending' : 'draft';
        $submitted_at = $isSubmit ? date('Y-m-d H:i:s') : ($existing['submitted_at'] ?? null);
        
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
                    id_document_path = ?,
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
                $frontUrl,
                $backUrl,
                $idDocPath,
                $data['account_holder_name'],
                $data['bank_name'],
                $data['account_number'],
                $data['ifsc_code'],
                $data['account_type'],
                $status,
                $submitted_at,
                $expertId
            ]);
            
        } else {
            // Insert new record
            $stmt = $pdo->prepare("
                INSERT INTO expert_kyc_verification (
                    expert_id, full_legal_name, date_of_birth, nationality, gender,
                    address_line1, city, state, postal_code, country,
                    id_document_type, id_number, id_document_front_url, id_document_back_url,
                    id_document_path, account_holder_name, bank_name, account_number, ifsc_code,
                    account_type, verification_status, submitted_at, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
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
                $frontUrl,
                $backUrl,
                $idDocPath,
                $data['account_holder_name'],
                $data['bank_name'],
                $data['account_number'],
                $data['ifsc_code'],
                $data['account_type'],
                $status,
                $submitted_at
            ]);
        }

        // Keep expert_profiles verification_status in sync
        if ($status === 'pending') {
            $pdo->prepare("UPDATE expert_profiles SET verification_status = 'pending' WHERE user_id = ?")->execute([$expertId]);
        }
        
        $message = $status === 'pending' ? 'KYC submitted successfully! Our team will review your verification.' : 'KYC saved as draft!';

        echo json_encode([
            'success' => true,
            'message' => $message,
            'status' => $status
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
