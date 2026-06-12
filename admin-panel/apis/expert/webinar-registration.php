<?php
header('Content-Type: application/json');

// Load domain path configuration
$base_path = require_once dirname(dirname(__DIR__)) . '/apis/connection/domain-path.php';

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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login as learner to register']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$webinar_id = isset($data['webinar_id']) ? intval($data['webinar_id']) : 0;
$learner_id = $_SESSION['user_id'];

if (!$webinar_id) {
    echo json_encode(['success' => false, 'message' => 'Webinar ID is required']);
    exit;
}

try {
    // Check if webinar exists and is available
    $stmt = $pdo->prepare("
        SELECT 
            w.id,
            w.expert_id,
            w.title,
            w.price_inr,
            w.max_participants,
            w.current_registrations,
            w.status,
            w.webinar_date,
            w.webinar_time
        FROM webinars w
        WHERE w.id = ? AND w.is_active = 1
    ");
    $stmt->execute([$webinar_id]);
    $webinar = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$webinar) {
        echo json_encode(['success' => false, 'message' => 'Webinar not found']);
        exit;
    }
    
    // Check if webinar is upcoming
    if ($webinar['status'] !== 'upcoming') {
        echo json_encode(['success' => false, 'message' => 'This webinar is not available for registration']);
        exit;
    }
    
    // Check if webinar is full
    if ($webinar['max_participants'] > 0 && $webinar['current_registrations'] >= $webinar['max_participants']) {
        echo json_encode(['success' => false, 'message' => 'Webinar is full']);
        exit;
    }
    
    // Check if already registered
    $stmt = $pdo->prepare("SELECT id FROM webinar_registrations WHERE webinar_id = ? AND learner_id = ?");
    $stmt->execute([$webinar_id, $learner_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are already registered for this webinar']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Determine payment status and amount based on price
    $payment_status = $webinar['price_inr'] > 0 ? 'pending' : 'completed';
    $payment_amount = $webinar['price_inr'];
    
    // Insert registration
    $stmt = $pdo->prepare("
        INSERT INTO webinar_registrations 
        (webinar_id, learner_id, payment_status, payment_amount)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $webinar_id,
        $learner_id,
        $payment_status,
        $payment_amount
    ]);
    
    // Update webinar registration count
    $stmt = $pdo->prepare("
        UPDATE webinars 
        SET current_registrations = current_registrations + 1 
        WHERE id = ?
    ");
    $stmt->execute([$webinar_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Successfully registered for webinar!',
        'webinar' => [
            'title' => $webinar['title'],
            'date' => $webinar['webinar_date'],
            'time' => $webinar['webinar_time'],
            'price' => $webinar['price_inr']
        ],
        'payment_required' => $webinar['price_inr'] > 0
    ]);
    
} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Webinar registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
?>
