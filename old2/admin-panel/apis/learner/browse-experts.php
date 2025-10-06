<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once '../../includes/db-config.php';

// Response array
$response = [
    'success' => false,
    'message' => 'Unknown error',
    'data' => [],
    'total' => 0,
    'page' => 1,
    'totalPages' => 1
];

try {
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 12; // Experts per page
    $offset = ($page - 1) * $limit;

    // Filters
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $minPrice = $_GET['min_price'] ?? null;
    $maxPrice = $_GET['max_price'] ?? null;
    $minRating = $_GET['min_rating'] ?? null;
    $sortBy = $_GET['sort_by'] ?? 'relevance';

    // Base query
    $query = "
        SELECT 
            u.id, 
            ep.full_name as name, 
            ep.tagline as professional_title, 
            ep.bio_short as bio,
            ep.profile_photo,
            ep.rating_average as avg_rating,
            ep.total_reviews as review_count,
            ep.experience_years,
            GROUP_CONCAT(DISTINCT c.name) as skills,
            MIN(pr.amount) as hourly_rate,
            'Verified' as badge
        FROM users u
        JOIN expert_profiles ep ON u.id = ep.user_id
        LEFT JOIN expert_categories ec ON u.id = ec.expert_id
        LEFT JOIN categories c ON ec.category_id = c.id
        LEFT JOIN expert_pricing pr ON u.id = pr.expert_id
        WHERE 
            u.role = 'expert' 
            AND ep.verification_status = 'approved'
            AND u.status = 'active'
    ";

    // Apply filters
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $conditions[] = "(ep.full_name LIKE ? OR ep.tagline LIKE ? OR ep.bio_short LIKE ?)";
        $searchParam = "%$search%";
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $types .= 'sss';
    }

    if (!empty($category)) {
        $conditions[] = "c.name = ?";
        $params[] = &$category;
        $types .= 's';
    }

    if ($minPrice !== null) {
        $conditions[] = "pr.amount >= ?";
        $params[] = &$minPrice;
        $types .= 'd';
    }

    if ($maxPrice !== null) {
        $conditions[] = "pr.amount <= ?";
        $params[] = &$maxPrice;
        $types .= 'd';
    }

    if ($minRating !== null) {
        $conditions[] = "ep.rating_average >= ?";
        $params[] = &$minRating;
        $types .= 'd';
    }

    // Add conditions to query
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    // Group and sort
    $query .= " GROUP BY u.id";

    // Sorting
    switch ($sortBy) {
        case 'price_low_high':
            $query .= " ORDER BY hourly_rate ASC";
            break;
        case 'price_high_low':
            $query .= " ORDER BY hourly_rate DESC";
            break;
        case 'rating':
            $query .= " ORDER BY avg_rating DESC";
            break;
        case 'newest':
            $query .= " ORDER BY ep.created_at DESC";
            break;
        default:
            $query .= " ORDER BY ep.rating_average DESC";
    }

    // Total count query
    $countQuery = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(DISTINCT u.id) as total FROM', $query);
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalExperts = $countResult->fetch_assoc()['total'];

    // Pagination
    $totalPages = ceil($totalExperts / $limit);

    // Add limit and offset
    $query .= " LIMIT ? OFFSET ?";
    $params[] = &$limit;
    $params[] = &$offset;
    $types .= 'ii';

    // Prepare and execute main query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch experts
    $experts = [];
    while ($expert = $result->fetch_assoc()) {
        // Convert skills to array
        $expert['skills'] = explode(',', $expert['skills'] ?? '');
        $experts[] = $expert;
    }

    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Experts retrieved successfully',
        'data' => $experts,
        'total' => $totalExperts,
        'page' => $page,
        'totalPages' => $totalPages
    ];
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
