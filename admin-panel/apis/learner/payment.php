<?php
// Learner payment API with Razorpay integration (order creation + verification + legacy test flow)
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../../../includes/payment-config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Payment history for learner
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
            exit;
        }
        $learnerId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("\n            SELECT p.*, b.session_datetime, b.duration_minutes as duration, ep.full_name as expert_name, ep.tagline as expert_title\n            FROM payments p\n            INNER JOIN bookings b ON p.booking_id = b.id\n            INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id\n            WHERE b.learner_id = ?\n            ORDER BY p.created_at DESC\n        ");
        $stmt->execute([$learnerId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $payments]);
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $action = $data['action'] ?? 'legacy';

        // -------- CREATE ORDER (Razorpay) OR TEST FLOW --------
        if ($action === 'create_order') {
                    // Log request details for debugging
                    error_log("Payment API - Create Order Request");
                    error_log("Session data: " . print_r($_SESSION, true));
                    error_log("POST data: " . print_r($data, true));
                    
                    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                        error_log("Authorization failed - user_id: " . ($_SESSION['user_id'] ?? 'not set') . ", role: " . ($_SESSION['role'] ?? 'not set'));
                        http_response_code(401);
                        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
                        exit;
                    }
                    $learnerId = $_SESSION['user_id'];
                    $expertId = $data['expert_id'] ?? null;
                    $sessionDatetime = $data['session_datetime'] ?? null;
                    $amount = $data['amount'] ?? 0; // major units
                    $duration = $data['duration'] ?? 60;
                    $paymentMethod = $data['payment_method'] ?? 'card';

                    if (!$expertId || !$sessionDatetime || !$amount) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                        exit;
                    }

                    $pdo->beginTransaction();
                    try {
                        // Booking pending until payment verified
                        $stmt = $pdo->prepare("\n                    INSERT INTO bookings (expert_id, learner_id, session_datetime, duration_minutes, status, created_at, updated_at)\n                    VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())\n                ");
                        $stmt->execute([$expertId, $learnerId, $sessionDatetime, $duration]);
                        $bookingId = $pdo->lastInsertId();

                        if ($paymentMethod === 'cash_test') {
                            // Immediate success for test mode
                            $gatewayId = 'CASH_' . time() . '_' . rand(1000, 9999);
                            $stmt = $pdo->prepare("
                        INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, currency, payment_type, payment_method, status, payment_date, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, 'one_time', ?, 'success', NOW(), NOW(), NOW())
                    ");
                            $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayId, $amount, PLATFORM_CURRENCY, $paymentMethod]);
                            $paymentId = $pdo->lastInsertId();
                            $pdo->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE id=?")->execute([$bookingId]);
                            $pdo->commit();
                            echo json_encode(['success' => true, 'mode' => 'test', 'data' => [
                                'payment_id' => $paymentId,
                                'booking_id' => $bookingId,
                                'amount' => $amount,
                                'status' => 'success'
                            ]]);
                            exit;
                        }

                        // Create Razorpay order (amount in paise)
                        $orderPayload = [
                            'amount' => (int)round($amount * 100),
                            'currency' => PLATFORM_CURRENCY,
                            'receipt' => 'BK_' . $bookingId,
                            'payment_capture' => 1
                        ];
                        $ch = curl_init('https://api.razorpay.com/v1/orders');
                        curl_setopt_array($ch, [
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                            CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
                            CURLOPT_POSTFIELDS => http_build_query($orderPayload)
                        ]);
                        $orderResponse = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlErr = curl_error($ch);
                        curl_close($ch);
                        if ($curlErr || $httpCode >= 400) {
                            $pdo->rollBack();
                            http_response_code(502);
                            echo json_encode(['success' => false, 'message' => 'Failed to create Razorpay order', 'error' => $curlErr, 'code' => $httpCode]);
                            exit;
                        }
                        $orderData = json_decode($orderResponse, true);
                        if (!isset($orderData['id'])) {
                            $pdo->rollBack();
                            http_response_code(502);
                            echo json_encode(['success' => false, 'message' => 'Invalid Razorpay order response']);
                            exit;
                        }
                        $gatewayOrderId = $orderData['id'];
                        $stmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, currency, payment_type, payment_method, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'one_time', ?, 'pending', NOW(), NOW())
                ");
                        $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayOrderId, $amount, PLATFORM_CURRENCY, $paymentMethod]);
                        $paymentId = $pdo->lastInsertId();
                        $pdo->commit();
                        echo json_encode(['success' => true, 'action' => 'create_order', 'data' => [
                            'order' => $orderData,
                            'payment_id' => $paymentId,
                            'booking_id' => $bookingId,
                            'razorpay_key' => RAZORPAY_KEY_ID,
                            'amount' => $amount,
                            'currency' => PLATFORM_CURRENCY
                        ]]);
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
    }

    // -------- VERIFY PAYMENT --------
    if ($action === 'verify_payment') {
                    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                        http_response_code(401);
                        echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
                        exit;
                    }
                    $razorpayOrderId = $data['razorpay_order_id'] ?? null;
                    $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
                    $razorpaySignature = $data['razorpay_signature'] ?? null;
                    $paymentId = $data['payment_id'] ?? null; // internal id
                    if (!$razorpayOrderId || !$razorpayPaymentId || !$razorpaySignature || !$paymentId) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Missing verification data']);
                        exit;
                    }
                    $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
                    if (!hash_equals($generatedSignature, $razorpaySignature)) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Signature verification failed']);
                        exit;
                    }
                    $pdo->beginTransaction();
                    try {
                        $stmt = $pdo->prepare("SELECT booking_id FROM payments WHERE id=? AND payment_gateway_id=? FOR UPDATE");
                        $stmt->execute([$paymentId, $razorpayOrderId]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$row) {
                            $pdo->rollBack();
                            http_response_code(404);
                            echo json_encode(['success' => false, 'message' => 'Payment record not found']);
                            exit;
                        }
                        $bookingId = $row['booking_id'];
                        $pdo->prepare("UPDATE payments SET status='success', payment_date=NOW(), updated_at=NOW() WHERE id=?")->execute([$paymentId]);
                        $pdo->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE id=?")->execute([$bookingId]);
                        $pdo->commit();
                        echo json_encode(['success' => true, 'action' => 'verify_payment', 'message' => 'Payment verified', 'data' => [
                            'payment_id' => $paymentId,
                            'booking_id' => $bookingId,
                            'razorpay_payment_id' => $razorpayPaymentId
                        ]]);
                        exit;
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        throw $e;
                    }
    }

    // -------- LEGACY DIRECT (test) --------
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
                    exit;
                }
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
                    $stmt = $pdo->prepare("\n                INSERT INTO bookings (expert_id, learner_id, session_datetime, duration_minutes, status, created_at, updated_at)\n                VALUES (?, ?, ?, ?, 'confirmed', NOW(), NOW())\n            ");
                    $stmt->execute([$expertId, $learnerId, $sessionDatetime, $duration]);
                    $bookingId = $pdo->lastInsertId();
                    $gatewayId = 'CASHLEG_' . time() . '_' . rand(1000, 9999);
                    $paymentStatus = ($paymentMethod === 'cash_test') ? 'success' : 'pending';
                    $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, payment_type, payment_method, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'one_time', ?, ?, NOW(), NOW())
            ");
                    $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayId, $amount, $paymentMethod, $paymentStatus]);
                    $paymentId = $pdo->lastInsertId();
                    $pdo->commit();
                    echo json_encode(['success' => true, 'message' => 'Legacy payment processed successfully', 'data' => [
                        'payment_id' => $paymentId,
                        'booking_id' => $bookingId,
                        'gateway_id' => $gatewayId,
                        'amount' => $amount,
                        'status' => $paymentStatus
                    ]]);
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        exit; // end legacy path
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} catch (Throwable $e) {
    error_log('Payment API Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    error_log('Request data: ' . print_r($_POST, true));
    error_log('Session data: ' . print_r($_SESSION, true));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
}
