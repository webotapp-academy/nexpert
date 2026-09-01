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
            ep.portfolio_url,
            ep.timezone,
            kyc.city,
            kyc.state,
            kyc.country
        FROM users u
        INNER JOIN expert_profiles ep ON u.id = ep.user_id
        LEFT JOIN expert_kyc_verification kyc ON u.id = kyc.expert_id
        WHERE u.id = ? AND u.role = 'expert' AND u.status = 'active'
    ");
    
    $stmt->execute([$expert_id]);
    $expert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expert) {
        echo json_encode([
            'success' => false,
            'message' => 'Expert not found'
        ]);
        exit;
    }

    $countryMap = ['IN' => 'India', 'US' => 'United States', 'UK' => 'United Kingdom', 'GB' => 'United Kingdom', 'CA' => 'Canada', 'AU' => 'Australia', 'SG' => 'Singapore', 'AE' => 'United Arab Emirates', 'DE' => 'Germany', 'FR' => 'France'];
    $locParts = [];
    if (!empty($expert['city'])) $locParts[] = trim($expert['city']);
    if (!empty($expert['state'])) $locParts[] = trim($expert['state']);
    if (!empty($expert['country'])) {
        $c = strtoupper(trim($expert['country']));
        $locParts[] = $countryMap[$c] ?? trim($expert['country']);
    }
    if (!empty($locParts)) {
        $expert['location'] = implode(', ', $locParts);
    } elseif (!empty($expert['timezone']) && $expert['timezone'] !== 'UTC') {
        $tzParts = explode('/', $expert['timezone']);
        $expert['location'] = str_replace('_', ' ', end($tzParts));
    } else {
        $expert['location'] = 'Remote';
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
