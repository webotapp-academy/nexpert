<?php
header('Content-Type: application/json');
require_once '../connection/pdo.php';

// NOTE: Authentication middleware required - user_id should come from session, not request

$method = $_SERVER['REQUEST_METHOD'];

// Get expert profile
if ($method === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT ep.*, u.email, u.phone, u.status 
            FROM expert_profiles ep
            JOIN users u ON u.id = ep.user_id
            WHERE ep.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch();
        
        if ($profile) {
            $profile['expertise_verticals'] = json_decode($profile['expertise_verticals'], true);
            $profile['certification_urls'] = json_decode($profile['certification_urls'], true);
            
            $stmt = $pdo->prepare("SELECT * FROM expert_pricing WHERE expert_id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            $profile['pricing'] = $stmt->fetchAll();
            
            $stmt = $pdo->prepare("SELECT * FROM expert_availability WHERE expert_id = ? AND is_active = 1");
            $stmt->execute([$user_id]);
            $profile['availability'] = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Profile not found']);
        }
    } catch (PDOException $e) {
        error_log('Get Profile Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while fetching profile']);
    }
    exit;
}

// Create or Update expert profile
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $data['user_id'] ?? null;
    $full_name = $data['full_name'] ?? '';
    $tagline = $data['tagline'] ?? '';
    $bio_short = $data['bio_short'] ?? '';
    $bio_full = $data['bio_full'] ?? '';
    $expertise_verticals = json_encode($data['expertise_verticals'] ?? []);
    $credentials = $data['credentials'] ?? '';
    $experience_years = $data['experience_years'] ?? 0;
    $timezone = $data['timezone'] ?? 'UTC';
    $profile_photo = $data['profile_photo'] ?? null;
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM expert_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            $stmt = $pdo->prepare("
                UPDATE expert_profiles SET
                    full_name = ?, tagline = ?, bio_short = ?, bio_full = ?,
                    expertise_verticals = ?, credentials = ?, experience_years = ?,
                    timezone = ?, profile_photo = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([
                $full_name, $tagline, $bio_short, $bio_full,
                $expertise_verticals, $credentials, $experience_years,
                $timezone, $profile_photo, $user_id
            ]);
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO expert_profiles (user_id, full_name, tagline, bio_short, bio_full,
                    expertise_verticals, credentials, experience_years, timezone, profile_photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $full_name, $tagline, $bio_short, $bio_full,
                $expertise_verticals, $credentials, $experience_years, $timezone, $profile_photo
            ]);
            echo json_encode(['success' => true, 'message' => 'Profile created successfully', 'id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        error_log('Save Profile Error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while saving profile']);
    }
    exit;
}
?>
