<?php
header('Content-Type: application/json');

// Load domain path configuration
$base_path = require_once dirname(dirname(dirname(__DIR__))) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once dirname(dirname(__DIR__)) . '/connection/pdo.php';

// Get expert ID from query parameter
$expert_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$expert_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Expert ID is required'
    ]);
    exit;
}

try {
    // Get expert profile data
    $stmt = $pdo->prepare("
        SELECT 
            u.id as user_id,
            u.email,
            ep.full_name,
            ep.tagline,
            ep.bio_short,
            ep.bio_detailed,
            ep.profile_photo,
            ep.expertise_verticals,
            ep.industry_experience_years,
            ep.total_sessions,
            ep.rating_average,
            ep.total_reviews,
            ep.linkedin_url,
            ep.twitter_url,
            ep.portfolio_url
        FROM users u
        INNER JOIN expert_profiles ep ON u.id = ep.user_id
        WHERE u.id = ? AND u.role = 'expert' AND ep.verification_status = 'approved'
    ");
    
    $stmt->execute([$expert_id]);
    $expert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expert) {
        echo json_encode([
            'success' => false,
            'message' => 'Expert not found or not approved'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'expert' => $expert
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get-expert-profile.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
