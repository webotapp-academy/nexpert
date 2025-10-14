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

        // Get expert basic info
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                ep.full_name as name,
                ep.tagline as professional_title,
                ep.profile_photo,
                ep.rating_average as avg_rating,
                ep.total_reviews as review_count,
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
            GROUP BY u.id, ep.full_name, ep.tagline, ep.profile_photo, 
                     ep.rating_average, ep.total_reviews
        ");
        $stmt->execute([$expertId]);
        $expert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expert) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Expert not found']);
            exit;
        }

        // Get availability
        $stmt = $pdo->prepare("
            SELECT day_of_week, start_time, end_time 
            FROM expert_availability 
            WHERE expert_id = ? AND is_active = 1 
            ORDER BY day_of_week, start_time
        ");
        $stmt->execute([$expertId]);
        $availability = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format rating
        $expert['avg_rating'] = round((float)$expert['avg_rating'], 1);
        $expert['hourly_rate'] = $expert['hourly_rate'] ?? 0;
        $expert['availability'] = $availability;

        echo json_encode([
            'success' => true,
            'data' => $expert
        ]);

    } elseif ($method === 'POST') {
        // Create new booking
        // Check if learner is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $learnerId = $_SESSION['user_id'];
        $expertId = $data['expert_id'] ?? null;
        $sessionDatetime = $data['session_datetime'] ?? null;
        $duration = $data['duration'] ?? 60;
        $amount = $data['amount'] ?? 0;

        if (!$expertId || !$sessionDatetime) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID and session datetime are required']);
            exit;
        }

        // Validate datetime format
        $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $sessionDatetime);
        if (!$datetime) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid datetime format']);
            exit;
        }

        // Insert booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (expert_id, learner_id, session_datetime, duration_minutes, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
        ");
        $stmt->execute([$expertId, $learnerId, $sessionDatetime, $duration]);
        $bookingId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => [
                'booking_id' => $bookingId,
                'expert_id' => $expertId,
                'session_datetime' => $sessionDatetime,
                'duration' => $duration,
                'amount' => $amount
            ]
        ]);

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Booking API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
