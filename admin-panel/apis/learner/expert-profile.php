<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $expertId = $_GET['expert_id'] ?? null;

        if (!$expertId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
            exit;
        }

        // Get expert profile
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                ep.full_name as name,
                u.email,
                ep.tagline as professional_title,
                ep.bio_full as bio,
                ep.profile_photo,
                ep.experience_years,
                ep.verification_status,
                ep.rating_average as avg_rating,
                ep.total_reviews as review_count,
                ep.total_sessions,
                ep.expertise_verticals,
                MIN(pricing.amount) as hourly_rate
            FROM users u
            INNER JOIN expert_profiles ep ON u.id = ep.user_id
            LEFT JOIN expert_pricing pricing ON u.id = pricing.expert_id 
                AND pricing.pricing_type = 'per_session' 
                AND pricing.is_active = 1
            WHERE u.id = ? 
            AND u.role = 'expert'
            AND ep.verification_status = 'approved'
            AND u.status = 'active'
            GROUP BY u.id, ep.full_name, u.email, ep.tagline, ep.bio_full, 
                     ep.profile_photo, ep.experience_years, ep.verification_status,
                     ep.rating_average, ep.total_reviews, ep.total_sessions, ep.expertise_verticals
        ");
        $stmt->execute([$expertId]);
        $expert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expert) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Expert not found']);
            exit;
        }

        // Extract skills from expertise_verticals JSON
        $verticals = json_decode($expert['expertise_verticals'], true);
        $expert['skills'] = is_array($verticals) ? $verticals : [];
        unset($expert['expertise_verticals']);

        // Format rating
        $expert['avg_rating'] = round((float)$expert['avg_rating'], 1);
        
        // Determine badge
        if ($expert['avg_rating'] >= 4.8 && $expert['review_count'] >= 50) {
            $expert['badge'] = 'Top Rated';
        } elseif ($expert['total_sessions'] >= 30) {
            $expert['badge'] = 'Expert';
        } else {
            $expert['badge'] = 'Verified';
        }
        
        // Ensure hourly_rate is set
        $expert['hourly_rate'] = $expert['hourly_rate'] ?? 0;
        
        // Don't expose email for privacy (could add a flag check here later)
        unset($expert['email']);
        
        // Normalize profile photo path
        if (!empty($expert['profile_photo'])) {
            // Remove any leading slashes and 'uploads/profiles/' if already present
            $photo = ltrim($expert['profile_photo'], '/');
            $photo = preg_replace('/^uploads\/profiles\//', '', $photo);
            
            // Check if the file exists
            $full_path = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/' . $photo;
            
            if (file_exists($full_path)) {
                $expert['profile_photo'] = 'uploads/profiles/' . $photo;
            } else {
                // If file doesn't exist, set to null or a default image
                $expert['profile_photo'] = null;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $expert
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    // Log the full error details
    error_log("Expert Profile API Error: " . $e->getMessage());
    error_log("Expert Profile API Trace: " . $e->getTraceAsString());
    
    // Log additional context
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Expert ID: " . ($_GET['expert_id'] ?? 'Not provided'));
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error_details' => $e->getMessage() // Only in development, remove in production
    ]);
}
