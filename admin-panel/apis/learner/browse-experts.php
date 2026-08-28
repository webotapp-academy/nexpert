<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once dirname(dirname(dirname(__DIR__))) . '/includes/dynamic-pricing.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $search       = $_GET['search'] ?? '';
        $category     = $_GET['category'] ?? '';
        $minPrice     = $_GET['min_price'] ?? null;
        $maxPrice     = $_GET['max_price'] ?? null;
        $minTrust     = isset($_GET['min_trust_score']) ? (float)$_GET['min_trust_score'] : null;
        $minRating    = $_GET['min_rating'] ?? null;
        $sortBy       = $_GET['sort_by'] ?? 'trust_score'; // Default to trust score
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
        $perPage = $limit ?? 9; // Use limit if provided, otherwise 9 experts per page
        $offset = ($page - 1) * $perPage;

        // Debug: Check if we have any expert users
        $debugStmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'expert'");
        $expertCount = $debugStmt->fetch(PDO::FETCH_ASSOC);
        error_log("Total experts in users table: " . $expertCount['count']);

        // Debug: Check if we have any expert profiles
        $debugStmt2 = $pdo->query("SELECT COUNT(*) as count FROM expert_profiles");
        $profileCount = $debugStmt2->fetch(PDO::FETCH_ASSOC);
        error_log("Total expert profiles: " . $profileCount['count']);

        // Build query - get pricing from expert_pricing table and programs from workflows
        // Also get booking_count and base_price for dynamic pricing
        // Note: GROUP BY fixed properly — no sql_mode override needed
        
        $query = "
            SELECT DISTINCT
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
                ep.category,
                ep.booking_count,
                ep.base_price,
                ep.strengths,
                ep.expected_outcomes,
                (SELECT COUNT(*) FROM bookings WHERE expert_id = u.id 
                 AND (
                     (MONTH(session_datetime) = MONTH(CURRENT_DATE()) AND YEAR(session_datetime) = YEAR(CURRENT_DATE()))
                     OR (MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()))
                 )
                 AND status IN ('confirmed', 'completed')) as bookings_this_month,
                ep.satisfaction_percent,
                MIN(pricing.amount) as hourly_rate,
                ts.overall_score,
                ts.trust_tier,
                ts.band_name,
                (SELECT GROUP_CONCAT(DISTINCT title SEPARATOR ' | ') 
                 FROM workflows 
                 WHERE expert_id = u.id AND is_active = 1) as programs
            FROM users u
            INNER JOIN expert_profiles ep ON u.id = ep.user_id
            LEFT JOIN trust_state ts ON u.id = ts.expert_id
            LEFT JOIN expert_pricing pricing ON u.id = pricing.expert_id 
                AND pricing.pricing_type = 'per_session' 
                AND pricing.is_active = 1
            LEFT JOIN workflows w ON u.id = w.expert_id 
                AND w.is_active = 1
            WHERE u.role = 'expert'
            AND u.status = 'active'
            AND ep.verification_status = 'approved'
        ";

        $params = [];

        // Add search filter - search in multiple fields including expertise and programs
        if (!empty($search)) {
            $query .= " AND (ep.full_name LIKE ? OR ep.tagline LIKE ? OR ep.bio_short LIKE ? OR ep.expertise_verticals LIKE ? OR ep.category LIKE ? OR w.title LIKE ? OR w.description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add min trust score filter
        if ($minTrust !== null) {
            $query .= " AND ts.overall_score >= ?";
            $params[] = $minTrust;
        }

        // Add category filter — case-insensitive
        if (!empty($category)) {
            $query .= " AND LOWER(ep.category) = LOWER(?)";
            $params[] = $category;
            error_log("Category filter applied: " . $category);
            error_log("Query with category filter: " . $query);
            error_log("Params: " . print_r($params, true));
        }

        $query .= " GROUP BY u.id, ep.full_name, ep.tagline, ep.bio_short, ep.profile_photo, 
                    ep.experience_years, ep.verification_status, ep.rating_average, 
                    ep.total_reviews, ep.total_sessions, ep.expertise_verticals, ep.category";

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
            error_log("Rating filter applied: >= " . $minRating);
        }

        // Add sorting
        switch ($sortBy) {
            case 'trust_score':
                $query .= " ORDER BY ts.overall_score DESC";
                break;
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
                // Relevance score: Rating (40%) + Reviews count (30%) + Experience (20%) + Sessions (10%)
                // Normalize each factor to 0-100 scale and calculate weighted score
                $query .= " ORDER BY (
                    (COALESCE(ep.rating_average, 0) * 20) * 0.4 + 
                    (LEAST(COALESCE(ep.total_reviews, 0), 100)) * 0.3 + 
                    (LEAST(COALESCE(ep.experience_years, 0) * 5, 100)) * 0.2 + 
                    (LEAST(COALESCE(ep.total_sessions, 0), 100)) * 0.1
                ) DESC, ep.rating_average DESC, ep.total_reviews DESC";
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
            $base_price = floatval($expert['base_price'] ?? $expert['hourly_rate'] ?? 0);
            $booking_count = intval($expert['booking_count'] ?? 0);
            
            // Check if learner is logged in to show personalized pricing
            if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner') {
                $learner_id = $_SESSION['user_id'];
                $expert_id = $expert['id'];
                
                // Get learner-specific dynamic pricing for this expert
                $pricing_info = calculate_learner_dynamic_price($pdo, $learner_id, $expert_id, $base_price);
                
                $expert['hourly_rate'] = $pricing_info['current_price'];
                $expert['base_price'] = $pricing_info['base_price'];
                $expert['learner_booking_count'] = $pricing_info['learner_booking_count'];
                $expert['price_tier'] = $pricing_info['tier'];
                $expert['tier_label'] = $pricing_info['tier_label'];
                $expert['is_near_price_increase'] = $pricing_info['is_near_increase'];
                $expert['bookings_until_next_tier'] = $pricing_info['bookings_until_next'];
            } else {
                // Guest user - show base price only
                $expert['hourly_rate'] = $base_price;
                $expert['base_price'] = $base_price;
                $expert['learner_booking_count'] = 0;
                $expert['price_tier'] = 0;
                $expert['tier_label'] = '';
                $expert['is_near_price_increase'] = false;
                $expert['bookings_until_next_tier'] = 0;
            }
            
            // Show expert's overall popularity (for all users)
            $global_tier_info = get_price_tier_info($booking_count);
            $expert['booking_count'] = $booking_count;
            $expert['popularity_tier'] = $global_tier_info['tier'];
            $expert['popularity_label'] = $global_tier_info['tier_label'];
            
            // Extract skills from expertise_verticals JSON
            $verticals = $expert['expertise_verticals'] ? json_decode($expert['expertise_verticals'], true) : [];
            $expert['skills'] = is_array($verticals) ? array_slice($verticals, 0, 5) : [];
            unset($expert['expertise_verticals']);
            
            // Format rating
            $expert['avg_rating'] = round((float)$expert['avg_rating'], 1);
            
            // Set badge based on category (capitalized)
            $expert['badge'] = ucfirst($expert['category'] ?? 'Expert');
            
            // Ensure hourly_rate is set
            $expert['hourly_rate'] = $expert['hourly_rate'] ?? 0;
            
            // Normalize profile photo path
            if (!empty($expert['profile_photo'])) {
                // Check if it's a full URL (http/https) - keep as is
                if (preg_match('/^https?:\/\//', $expert['profile_photo'])) {
                    // It's a full URL (like DiceBear API), keep it as is
                    $expert['profile_photo'] = $expert['profile_photo'];
                } else {
                    // It's a local file path
                    // Remove any BASE_PATH prefix if present
                    $photo = str_replace(BASE_PATH . '/', '', $expert['profile_photo']);
                    $photo = str_replace(BASE_PATH, '', $photo);
                    // Remove any leading slashes
                    $photo = ltrim($photo, '/');
                    // Remove 'uploads/profiles/' prefix if already present (to avoid duplication)
                    $photo = preg_replace('/^uploads\/profiles\//', '', $photo);
                    
                    // Check if the file exists
                    $full_path = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/' . $photo;
                    
                    if (file_exists($full_path)) {
                        // Return path that JavaScript can use with BASE_PATH
                        $expert['profile_photo'] = 'uploads/profiles/' . $photo;
                    } else {
                        // Try to find any profile file matching the pattern "profile_{$expert['id']}_*"
                        $profile_dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/profiles/';
                        $matches = glob($profile_dir . 'profile_' . $expert['id'] . '_*');
                        if (empty($matches)) {
                            $matches = glob($profile_dir . 'profile_' . $expert['id'] . '.*');
                        }
                        
                        if (!empty($matches)) {
                            $found_photo = basename($matches[0]);
                            $expert['profile_photo'] = 'uploads/profiles/' . $found_photo;
                        } else {
                            // Fallback to stock images deterministically based on expert ID
                            $stock_dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/attached_assets/stock_images/';
                            $stock_images = glob($stock_dir . '*.jpg');
                            if (!empty($stock_images)) {
                                $stock_index = $expert['id'] % count($stock_images);
                                $expert['profile_photo'] = 'attached_assets/stock_images/' . basename($stock_images[$stock_index]);
                            } else {
                                $expert['profile_photo'] = null;
                            }
                        }
                    }
                }
            }
        }

        error_log("Final experts count: " . count($experts));
        error_log("Category filter used: " . ($category ?: 'none'));
        error_log("Total experts found: " . $totalExperts);

        echo json_encode([
            'success' => true,
            'data' => $experts,
            'count' => count($experts),
            'total' => $totalExperts,
            'page' => $page,
            'totalPages' => $totalPages,
            'debug' => [
                'category_filter' => $category,
                'query_params' => $params
            ]
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Browse Experts API Error: " . $e->getMessage());
    error_log("Browse Experts SQL Error: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
} catch (Exception $e) {
    error_log("Browse Experts General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'debug' => $e->getMessage()
    ]);
}
