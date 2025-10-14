<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $search = $_GET['search'] ?? '';
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;
        $minRating = $_GET['min_rating'] ?? null;
        $sortBy = $_GET['sort_by'] ?? 'relevance';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $perPage = $limit ?? 9; // Use limit if provided, otherwise 9 experts per page
        $offset = ($page - 1) * $perPage;

        // Build query - get pricing from expert_pricing table
        $query = "
            SELECT 
                u.id,
                ep.full_name as name,
                ep.tagline as professional_title,
                ep.bio_short as bio,
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
            WHERE u.role = 'expert'
            AND ep.verification_status = 'approved'
            AND u.status = 'active'
        ";

        $params = [];

        // Add search filter
        if (!empty($search)) {
            $query .= " AND (ep.full_name LIKE ? OR ep.tagline LIKE ? OR ep.bio_short LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY u.id, ep.full_name, ep.tagline, ep.bio_short, ep.profile_photo, 
                    ep.experience_years, ep.verification_status, ep.rating_average, 
                    ep.total_reviews, ep.total_sessions, ep.expertise_verticals";

        // Add price filter (HAVING clause after GROUP BY)
        if ($minPrice !== null && $maxPrice !== null) {
            $query .= " HAVING MIN(pricing.amount) BETWEEN ? AND ?";
            $params[] = $minPrice;
            $params[] = $maxPrice;
        }

        // Add rating filter
        if ($minRating !== null) {
            $query .= ($minPrice !== null && $maxPrice !== null ? " AND" : " HAVING") . " ep.rating_average >= ?";
            $params[] = $minRating;
        }

        // Add sorting
        switch ($sortBy) {
            case 'price_low_high':
                $query .= " ORDER BY hourly_rate ASC";
                break;
            case 'price_high_low':
                $query .= " ORDER BY hourly_rate DESC";
                break;
            case 'rating':
                $query .= " ORDER BY ep.rating_average DESC";
                break;
            case 'newest':
            case 'latest':
                $query .= " ORDER BY u.id DESC";
                break;
            default:
                $query .= " ORDER BY ep.total_sessions DESC, ep.rating_average DESC";
        }

        // Get total count first
        $countStmt = $pdo->prepare($query);
        $countStmt->execute($params);
        $totalExperts = $countStmt->rowCount();
        
        // Add pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $experts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalPages = ceil($totalExperts / $perPage);

        // Process each expert
        foreach ($experts as &$expert) {
            // Extract skills from expertise_verticals JSON
            $verticals = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
            $expert['skills'] = is_array($verticals) ? array_slice($verticals, 0, 5) : [];
            unset($expert['expertise_verticals']);
            
            // Format rating
            $expert['avg_rating'] = round((float)$expert['avg_rating'], 1);
            
            // Determine badge
            if ($expert['avg_rating'] >= 4.8 && $expert['review_count'] >= 50) {
                $expert['badge'] = 'Top Rated';
            } elseif ($expert['total_sessions'] >= 30) {
                $expert['badge'] = 'Expert';
            } else {
                $expert['badge'] = 'Mentor';
            }
            
            // Ensure hourly_rate is set
            $expert['hourly_rate'] = $expert['hourly_rate'] ?? 0;
            
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
        }

        echo json_encode([
            'success' => true,
            'data' => $experts,
            'count' => count($experts),
            'total' => $totalExperts,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Browse Experts API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
