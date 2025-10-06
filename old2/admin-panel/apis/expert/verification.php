<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - user_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Upload KYC documents
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $data['user_id'] ?? null;
    $govt_id_url = $data['govt_id_url'] ?? null;
    $certification_urls = json_encode($data['certification_urls'] ?? []);
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE expert_profiles
            SET govt_id_url = ?, certification_urls = ?, verification_status = 'pending'
            WHERE user_id = ?
        ");
        $stmt->execute([$govt_id_url, $certification_urls, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Documents uploaded successfully. Awaiting admin verification.']);
    } catch (PDOException $e) {
        error_log('Upload KYC Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while uploading documents']);
    }
    exit;
}

// Get verification status
if ($method === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT verification_status, verified_at, govt_id_url, certification_urls
            FROM expert_profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $verification = $stmt->fetch();
        
        if ($verification) {
            $verification['certification_urls'] = json_decode($verification['certification_urls'], true);
            echo json_encode(['success' => true, 'verification' => $verification]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Expert profile not found']);
        }
    } catch (PDOException $e) {
        error_log('Get Verification Status Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching verification status']);
    }
    exit;
}
?>
