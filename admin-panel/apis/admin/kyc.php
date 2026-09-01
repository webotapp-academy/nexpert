<?php
// Load domain path configuration
$base_path = require_once __DIR__ . '/../connection/domain-path.php';

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
                       kyc.id_document_type, kyc.id_number,
                       kyc.id_document_front_url, kyc.id_document_back_url,
                       COALESCE(NULLIF(kyc.id_document_path, ''), kyc.id_document_front_url) as id_document_path,
                       kyc.account_holder_name, kyc.bank_name, kyc.account_number, kyc.ifsc_code, kyc.account_type,
                       COALESCE(kyc.verification_status, ep.verification_status, 'pending') as verification_status,
                       kyc.submitted_at, kyc.reviewed_at, kyc.admin_notes, kyc.rejection_reason
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
                
                // Map database column names to form field names for frontend
                $expert['bio_full'] = $expert['bio_short'] ?? '';
                $expert['years_of_experience'] = $expert['experience_years'] ?? '';
                $expert['website_url'] = $expert['portfolio_url'] ?? '';
            }
            
            echo json_encode([
                'success' => true,
                'data' => $expert
            ]);
        } else {
            // List all experts with optional status filter
            $status = $_GET['status'] ?? null;
            
            $query = "
                SELECT ep.*, u.email, u.phone, u.status as account_status, u.created_at as account_created,
                       COALESCE(kyc.verification_status, ep.verification_status, 'pending') as verification_status,
                       kyc.submitted_at, kyc.full_legal_name, kyc.id_document_type,
                       kyc.id_document_front_url, kyc.id_document_back_url,
                       COALESCE(NULLIF(kyc.id_document_path, ''), kyc.id_document_front_url) as id_document_path
                FROM expert_profiles ep
                INNER JOIN users u ON ep.user_id = u.id
                LEFT JOIN expert_kyc_verification kyc ON ep.user_id = kyc.expert_id
                WHERE u.role = 'expert'
            ";
            
            $params = [];
            if ($status && $status !== 'all') {
                $query .= " AND (COALESCE(kyc.verification_status, ep.verification_status) = ?)";
                $params[] = $status;
            }
            
            $query .= " ORDER BY COALESCE(kyc.submitted_at, ep.created_at) DESC";
            
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
        
    } elseif ($method === 'POST') {
        // Handle profile update with file upload (POST method for FormData)
        $expertId = $_POST['expert_id'] ?? null;
        
        if (!$expertId) {
            throw new Exception('Expert ID is required');
        }
        
        $profilePhotoPath = null;
        
        // Handle profile photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            // Use relative path from document root
            $uploadDir = __DIR__ . '/../../../uploads/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception('Failed to create upload directory');
                }
                // Set permissions explicitly
                chmod($uploadDir, 0777);
            }
            
            // Try to make directory writable if it's not
            if (!is_writable($uploadDir)) {
                chmod($uploadDir, 0777);
                // Check again after chmod
                if (!is_writable($uploadDir)) {
                    throw new Exception('Upload directory is not writable. Please check folder permissions.');
                }
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
            }
            
            if ($_FILES['profile_photo']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File size must be less than 5MB');
            }
            
            $fileName = 'expert_' . $expertId . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filePath)) {
                // Set file permissions
                chmod($filePath, 0644);
                // Store only filename in database (API will construct full path)
                $profilePhotoPath = $fileName;
            } else {
                $uploadError = error_get_last();
                throw new Exception('Failed to upload profile photo: ' . ($uploadError['message'] ?? 'Unknown error'));
            }
        } elseif (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $errorCode = $_FILES['profile_photo']['error'];
            throw new Exception($errorMessages[$errorCode] ?? 'Unknown upload error');
        }
        
        // Parse expertise_verticals from JSON string
        $expertiseVerticals = isset($_POST['expertise_verticals']) ? 
            json_decode($_POST['expertise_verticals'], true) : [];
        
        // Build update query - only update fields that exist in expert_profiles table
        $updateFields = [
            'full_name = ?',
            'bio_short = ?',
            'expertise_verticals = ?',
            'experience_years = ?'
        ];
        
        $params = [
            $_POST['full_name'] ?? null,
            $_POST['bio_short'] ?? null,
            json_encode($expertiseVerticals),
            $_POST['years_of_experience'] ?? null
        ];
        
        if ($profilePhotoPath) {
            $updateFields[] = 'profile_photo = ?';
            $params[] = $profilePhotoPath;
        }
        
        $params[] = $expertId;
        
        $sql = "UPDATE expert_profiles SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Update social links if provided
        try {
            $linkedinUrl = $_POST['linkedin_url'] ?? null;
            $websiteUrl = $_POST['website_url'] ?? null;
            
            if ($linkedinUrl || $websiteUrl) {
                $socialUpdateFields = [];
                $socialParams = [];
                
                if ($linkedinUrl) {
                    $socialUpdateFields[] = 'linkedin_url = ?';
                    $socialParams[] = $linkedinUrl;
                }
                
                if ($websiteUrl) {
                    $socialUpdateFields[] = 'portfolio_url = ?';  // Maps to portfolio_url
                    $socialParams[] = $websiteUrl;
                }
                
                if (count($socialUpdateFields) > 0) {
                    $socialParams[] = $expertId;
                    $socialSql = "UPDATE expert_profiles SET " . implode(', ', $socialUpdateFields) . " WHERE user_id = ?";
                    $socialStmt = $pdo->prepare($socialSql);
                    $socialStmt->execute($socialParams);
                }
            }
        } catch (PDOException $e) {
            // Log but don't fail the main update
            error_log("Social links update error: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Expert profile updated successfully',
            'profile_photo' => $profilePhotoPath
        ]);
        
    } elseif ($method === 'PUT') {
        // Update expert verification status or profile
        
        // Check if this is a multipart/form-data request (with file upload)
        if (!empty($_FILES)) {
            // This shouldn't happen now as we're using POST for file uploads
            throw new Exception('Use POST method for file uploads');
        } else {
            // Handle JSON request
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

                // Also update expert_kyc_verification table
                $stmtKyc = $pdo->prepare("
                    UPDATE expert_kyc_verification SET
                        verification_status = ?,
                        reviewed_at = NOW(),
                        admin_notes = ?,
                        rejection_reason = ?
                    WHERE expert_id = ?
                ");
                $adminNotes = $data['notes'] ?? ($data['admin_notes'] ?? null);
                $rejectionReason = $data['status'] === 'rejected' ? ($data['notes'] ?? 'KYC rejected by admin') : null;
                $stmtKyc->execute([
                    $data['status'],
                    $adminNotes,
                    $rejectionReason,
                    $data['expert_id']
                ]);

                // Log Trust Event and Trigger Baseline Trust Score Calculation if approved
                if ($data['status'] === 'approved') {
                    try {
                        require_once dirname(__DIR__) . '/connection/trust-aggregator.php';
                        
                        // 1. Emit kyc_verified event
                        $payload = json_encode([
                            'verified_at' => $verified_at,
                            'admin_notes' => $data['notes'] ?? 'KYC approved by admin'
                        ]);
                        $eventStmt = $pdo->prepare("
                            INSERT INTO trust_events (event_type, expert_id, payload, status, created_at)
                            VALUES ('kyc_verified', ?, ?, 'pending', NOW())
                        ");
                        $eventStmt->execute([$data['expert_id'], $payload]);
                        $eventId = (int)$pdo->lastInsertId();

                        // 2. Immediately calculate baseline Trust Score
                        $aggregator = new TrustAggregator($pdo);
                        if (method_exists($aggregator, 'aggregateOne')) {
                            $aggregator->aggregateOne((int)$data['expert_id'], $eventId);
                        }
                    } catch (Exception $e) {
                        error_log("Failed to aggregate baseline trust score on KYC approval: " . $e->getMessage());
                    }
                }
                
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
                        bio_detailed = ?,
                        expertise_verticals = ?,
                        industry_experience_years = ?
                    WHERE user_id = ?
                ");
                
                $stmt->execute([
                    $data['full_name'] ?? null,
                    $data['bio_short'] ?? null,
                    $data['bio_full'] ?? null,  // Maps to bio_detailed
                    json_encode($data['expertise_verticals'] ?? []),
                    $data['years_of_experience'] ?? null,  // Maps to industry_experience_years
                    $data['expert_id']
                ]);
                
                // Try to update social links if provided
                try {
                    if (isset($data['linkedin_url']) || isset($data['website_url'])) {
                        $socialFields = [];
                        $socialParams = [];
                        
                        if (isset($data['linkedin_url'])) {
                            $socialFields[] = 'linkedin_url = ?';
                            $socialParams[] = $data['linkedin_url'];
                        }
                        
                        if (isset($data['website_url'])) {
                            $socialFields[] = 'portfolio_url = ?';  // Maps to portfolio_url
                            $socialParams[] = $data['website_url'];
                        }
                        
                        if (count($socialFields) > 0) {
                            $socialParams[] = $data['expert_id'];
                            $socialSql = "UPDATE expert_profiles SET " . implode(', ', $socialFields) . " WHERE user_id = ?";
                            $socialStmt = $pdo->prepare($socialSql);
                            $socialStmt->execute($socialParams);
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Social links update error: " . $e->getMessage());
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Expert profile updated successfully'
                ]);
            } else {
                throw new Exception('No valid update data provided');
            }
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
