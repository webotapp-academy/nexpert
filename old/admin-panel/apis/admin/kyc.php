<?php
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/auth-check.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get specific expert by ID or list all with optional filter
        if (isset($_GET['expert_id'])) {
            $stmt = $pdo->prepare("
                SELECT ep.*, u.email, u.phone, u.status as account_status, u.created_at as account_created,
                       kyc.full_legal_name, kyc.date_of_birth, kyc.nationality, kyc.gender,
                       kyc.address_line1, kyc.address_line2, kyc.city, kyc.state, kyc.postal_code, kyc.country,
                       kyc.id_document_type, kyc.id_number, kyc.id_document_path
                FROM expert_profiles ep
                INNER JOIN users u ON ep.user_id = u.id
                LEFT JOIN expert_kyc_verification kyc ON ep.user_id = kyc.expert_id
                WHERE ep.user_id = ?
            ");
            $stmt->execute([$_GET['expert_id']]);
            $expert = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($expert) {
                $expert['expertise_verticals'] = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
                $expert['certification_urls'] = $expert['certification_urls'] ? json_decode($expert['certification_urls'], true) : [];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $expert
            ]);
        } else {
            // List all experts with optional status filter
            $status = $_GET['status'] ?? null;
            
            $query = "
                SELECT ep.*, u.email, u.phone, u.status as account_status, u.created_at as account_created
                FROM expert_profiles ep
                INNER JOIN users u ON ep.user_id = u.id
                WHERE u.role = 'expert'
            ";
            
            $params = [];
            if ($status && $status !== 'all') {
                $query .= " AND ep.verification_status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY ep.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $expertList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($expertList as &$expert) {
                $expert['expertise_verticals'] = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $expertList
            ]);
        }
        
    } elseif ($method === 'PUT') {
        // Update expert verification status or profile
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['expert_id'])) {
            throw new Exception('Expert ID is required');
        }
        
        // Check if this is a verification status update
        if (isset($data['status'])) {
            if (!in_array($data['status'], ['approved', 'rejected', 'pending'])) {
                throw new Exception('Invalid status. Must be approved, rejected, or pending');
            }
            
            $verified_at = $data['status'] === 'approved' ? date('Y-m-d H:i:s') : null;
            
            $stmt = $pdo->prepare("
                UPDATE expert_profiles SET
                    verification_status = ?,
                    verified_at = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([
                $data['status'],
                $verified_at,
                $data['expert_id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Verification status updated successfully'
            ]);
        }
        // Check if this is a profile update
        elseif (isset($data['update_profile'])) {
            $stmt = $pdo->prepare("
                UPDATE expert_profiles SET
                    full_name = ?,
                    bio_short = ?,
                    bio_full = ?,
                    expertise_verticals = ?,
                    credentials = ?,
                    years_of_experience = ?,
                    linkedin_url = ?,
                    website_url = ?
                WHERE user_id = ?
            ");
            
            $stmt->execute([
                $data['full_name'] ?? null,
                $data['bio_short'] ?? null,
                $data['bio_full'] ?? null,
                json_encode($data['expertise_verticals'] ?? []),
                $data['credentials'] ?? null,
                $data['years_of_experience'] ?? null,
                $data['linkedin_url'] ?? null,
                $data['website_url'] ?? null,
                $data['expert_id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Expert profile updated successfully'
            ]);
        } else {
            throw new Exception('No valid update data provided');
        }
        
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
