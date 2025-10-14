<?php
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get payment history for learner
        // Check if learner is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
            exit;
        }

        $learnerId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                b.session_datetime,
                b.duration_minutes as duration,
                ep.full_name as expert_name,
                ep.tagline as expert_title
            FROM payments p
            INNER JOIN bookings b ON p.booking_id = b.id
            INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
            WHERE b.learner_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$learnerId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $payments
        ]);

    } elseif ($method === 'POST') {
        // Create new payment
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
        $amount = $data['amount'] ?? 0;
        $duration = $data['duration'] ?? 60;
        $paymentMethod = $data['payment_method'] ?? 'card';

        if (!$expertId || !$sessionDatetime || !$amount) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $pdo->beginTransaction();

        try {
            // Create booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (expert_id, learner_id, session_datetime, duration_minutes, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'confirmed', NOW(), NOW())
            ");
            $stmt->execute([$expertId, $learnerId, $sessionDatetime, $duration]);
            $bookingId = $pdo->lastInsertId();

            // Create payment record
            $gatewayId = 'CASH_' . time() . '_' . rand(1000, 9999);
            $paymentStatus = ($paymentMethod === 'cash_test') ? 'success' : 'pending';
            $paymentType = 'one_time';
            
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, payment_type, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayId, $amount, $paymentType, $paymentStatus]);
            $paymentId = $pdo->lastInsertId();

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $paymentId,
                    'booking_id' => $bookingId,
                    'gateway_id' => $gatewayId,
                    'amount' => $amount,
                    'status' => $paymentStatus
                ]
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Payment API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
