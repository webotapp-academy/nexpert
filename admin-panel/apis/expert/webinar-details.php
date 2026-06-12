<?php
// Start output buffering to catch any errors
ob_start();

// Load domain path configuration
try {
    $base_path = require_once dirname(dirname(__DIR__)) . '/apis/connection/domain-path.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Config load failed: ' . $e->getMessage()]);
    exit;
}

require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';

// Database connection
$host = 'srv1368.hstgr.io';
$dbname = 'u621169360_replit';
$username = 'u621169360_replit';
$password = 'JAIhanuman89@@@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Clear any buffered output before sending JSON
ob_end_clean();
header('Content-Type: application/json');

// Check if this is a public view request
$expertIdParam = isset($_GET['expert_id']) ? intval($_GET['expert_id']) : null;
$webinarIdParam = isset($_GET['id']) ? intval($_GET['id']) : null;
$isPublicListView = $expertIdParam !== null && isset($_GET['all']) && $_GET['all'] === 'true';
$isPublicSingleView = $webinarIdParam !== null; // Any logged in user (learner/expert/guest) can view single webinar

// Check if user is logged in as expert
$isExpertLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';

// Allow public view for: listing all webinars OR viewing single webinar
// Only block if trying to access without proper parameters
if (!$isPublicListView && !$isPublicSingleView) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Handle public view request (all webinars for an expert)
if ($isPublicListView) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                title,
                description,
                webinar_date,
                webinar_time,
                duration_hours,
                price_inr,
                current_registrations,
                max_participants,
                status,
                is_active
            FROM webinars
            WHERE expert_id = ?
            AND status = 'upcoming'
            AND is_active = 1
            AND webinar_date >= CURDATE()
            ORDER BY webinar_date ASC, webinar_time ASC
        ");
        $stmt->execute([$expertIdParam]);
        $webinars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'webinars' => $webinars
        ]);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Original expert-only code continues below
$webinar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if expert is logged in
$isExpertLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
$expert_id = $isExpertLoggedIn ? $_SESSION['user_id'] : null;

if (!$webinar_id) {
    echo json_encode(['success' => false, 'message' => 'Webinar ID is required']);
    exit;
}

try {
    // Fetch webinar details - if expert logged in, verify ownership
    if ($isExpertLoggedIn && $expert_id) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM webinars
            WHERE id = ? AND expert_id = ?
        ");
        $stmt->execute([$webinar_id, $expert_id]);
    } else {
        // Public view - just fetch by ID
        $stmt = $pdo->prepare("
            SELECT *
            FROM webinars
            WHERE id = ?
        ");
        $stmt->execute([$webinar_id]);
    }
    
    $webinar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$webinar) {
        echo json_encode(['success' => false, 'message' => 'Webinar not found' . ($isExpertLoggedIn ? ' or access denied' : '')]);
        exit;
    }
    
    // Get registration stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_registrations,
            SUM(CASE WHEN attended = 1 THEN 1 ELSE 0 END) as total_attended,
            AVG(CASE WHEN feedback_rating IS NOT NULL THEN feedback_rating ELSE NULL END) as avg_rating
        FROM webinar_registrations
        WHERE webinar_id = ?
    ");
    
    $stmt->execute([$webinar_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Add stats to webinar
    $webinar['total_registrations'] = $stats['total_registrations'] ?? 0;
    $webinar['total_attended'] = $stats['total_attended'] ?? 0;
    $webinar['avg_rating'] = $stats['avg_rating'] ?? null;
    
    // Check if current user is already registered (for learners)
    $isLearnerRegistered = false;
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner') {
        $stmt = $pdo->prepare("SELECT id FROM webinar_registrations WHERE webinar_id = ? AND learner_id = ?");
        $stmt->execute([$webinar_id, $_SESSION['user_id']]);
        $isLearnerRegistered = $stmt->fetch() !== false;
    }
    $webinar['is_registered'] = $isLearnerRegistered;
    
    // Fetch registrations with learner details
    $stmt = $pdo->prepare("
        SELECT 
            wr.*,
            COALESCE(lp.full_name, u.email) as full_name,
            u.email,
            lp.profile_photo as profile_picture
        FROM webinar_registrations wr
        JOIN users u ON wr.learner_id = u.id
        LEFT JOIN learner_profiles lp ON u.id = lp.user_id
        WHERE wr.webinar_id = ?
        ORDER BY wr.registration_date DESC
    ");
    
    $stmt->execute([$webinar_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'webinar' => $webinar,
        'registrations' => $registrations
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in webinar-details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
