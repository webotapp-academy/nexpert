<?php
// Learner payment API with Razorpay integration (order creation + verification + legacy test flow)
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../../../includes/payment-config.php';
require_once __DIR__ . '/../connection/trust-helper.php';
require_once __DIR__ . '/../connection/email-helper.php';

function notifyExpertOfNewBooking($pdo, $bookingId, $expertId, $learnerId, $amount) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.session_datetime, b.duration_minutes, b.session_topic,
                   lp.full_name as learner_name,
                   ep.full_name as expert_name,
                   eu.email as expert_email
            FROM bookings b
            LEFT JOIN users lu ON b.learner_id = lu.id
            LEFT JOIN learner_profiles lp ON lu.id = lp.user_id
            LEFT JOIN users eu ON b.expert_id = eu.id
            LEFT JOIN expert_profiles ep ON eu.id = ep.user_id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($info && !empty($info['expert_email'])) {
            $dt = new DateTime($info['session_datetime']);
            $date = $dt->format('l, F j, Y');
            $time = $dt->format('g:i A');
            $topic = !empty($info['session_topic']) ? $info['session_topic'] : '1-on-1 Mentorship Session';
            $learnerName = !empty($info['learner_name']) ? $info['learner_name'] : 'Learner';
            $expertName = !empty($info['expert_name']) ? $info['expert_name'] : 'Expert';
            $duration = (int)($info['duration_minutes'] ?? 60);

            $emailHelper = new EmailHelper();
            $emailHelper->sendExpertNewBookingRequestAlert(
                $info['expert_email'],
                $expertName,
                $learnerName,
                $topic,
                $date,
                $time,
                $duration,
                $amount
            );
        }
    } catch (Exception $e) {
        error_log("Error in notifyExpertOfNewBooking: " . $e->getMessage());
    }
}

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
                    
                    // Verify learner exists in users table
                    $verifyStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'learner'");
                    $verifyStmt->execute([$learnerId]);
                    if (!$verifyStmt->fetch()) {
                        error_log("Payment Error: Learner ID {$learnerId} not found in users table");
                        error_log("Session data: " . print_r($_SESSION, true));
                        http_response_code(400);
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Your account session is invalid. Please logout and login again to fix this issue.',
                            'error_code' => 'INVALID_SESSION'
                        ]);
                        exit;
                    }
                    
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
                            
                            // Note: Removed global booking count increment
                            // Pricing is now per-learner, not global
                            
                            $pdo->commit();
                            
                            // Send instant notification email to expert
                            notifyExpertOfNewBooking($pdo, $bookingId, $expertId, $learnerId, $amount);
                            
                            // Log trust event
                            TrustHelper::logEvent($pdo, 'booking_created', $expertId, $learnerId, [
                                'booking_id' => $bookingId,
                                'amount' => $amount,
                                'payment_method' => $paymentMethod
                            ]);

                            echo json_encode(['success' => true, 'mode' => 'test', 'data' => [
                                'payment_id' => $paymentId,
                                'booking_id' => $bookingId,
                                'amount' => $amount,
                                'status' => 'success'
                            ]]);
                            exit;
                        } elseif ($paymentMethod === 'cod') {
                            // Cash on Delivery / Pay Later - Create booking with success payment status
                            $gatewayId = 'COD_' . time() . '_' . rand(1000, 9999);
                            $stmt = $pdo->prepare("
                                INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, currency, payment_type, payment_method, status, payment_date, created_at, updated_at)
                                VALUES (?, ?, ?, ?, ?, ?, 'one_time', 'cod', 'success', NOW(), NOW(), NOW())
                            ");
                            $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayId, $amount, PLATFORM_CURRENCY]);
                            $paymentId = $pdo->lastInsertId();
                            
                            // Booking is confirmed with successful COD payment
                            $pdo->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE id=?")->execute([$bookingId]);
                            
                            $pdo->commit();
                            
                            // Send instant notification email to expert
                            notifyExpertOfNewBooking($pdo, $bookingId, $expertId, $learnerId, $amount);
                            
                            // Log trust event
                            TrustHelper::logEvent($pdo, 'booking_created', $expertId, $learnerId, [
                                'booking_id' => $bookingId,
                                'amount' => $amount,
                                'payment_method' => 'cod'
                            ]);

                            echo json_encode(['success' => true, 'mode' => 'cod', 'data' => [
                                'payment_id' => $paymentId,
                                'booking_id' => $bookingId,
                                'amount' => $amount,
                                'status' => 'success',
                                'message' => 'Booking confirmed. Pay in cash after session.'
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
        $learnerId = $_SESSION['user_id'];
        $paymentId = $data['payment_id'] ?? null;
        $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
        $razorpayOrderId = $data['razorpay_order_id'] ?? null;
        $razorpaySignature = $data['razorpay_signature'] ?? null;
        if (!$paymentId || !$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing verification parameters']);
            exit;
        }

        // Verify Razorpay signature
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
        if (!hash_equals($expectedSignature, $razorpaySignature)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid payment signature']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT booking_id, expert_id, amount FROM payments WHERE id = ? AND learner_id = ? AND status = 'pending' FOR UPDATE");
            $stmt->execute([$paymentId, $learnerId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Payment record not found']);
                exit;
            }
            $bookingId = $row['booking_id'];
            $expertId = $row['expert_id'];
            $amount = $row['amount'];
            $pdo->prepare("UPDATE payments SET status='success', payment_date=NOW(), updated_at=NOW() WHERE id=?")->execute([$paymentId]);
            $pdo->prepare("UPDATE bookings SET status='confirmed', updated_at=NOW() WHERE id=?")->execute([$bookingId]);
            
            $pdo->commit();
            
            // Send instant notification email to expert
            notifyExpertOfNewBooking($pdo, $bookingId, $expertId, $learnerId, $amount);
            
            // Log trust event
            TrustHelper::logEvent($pdo, 'booking_created', $expertId, $learnerId, [
                'booking_id' => $bookingId,
                'payment_id' => $paymentId,
                'razorpay_payment_id' => $razorpayPaymentId
            ]);

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
                    $paymentStatus = ($paymentMethod === 'cash_test' || $paymentMethod === 'cod') ? 'success' : 'pending';
                    $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, payment_type, payment_method, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'one_time', ?, ?, NOW(), NOW())
            ");
                    $stmt->execute([$bookingId, $learnerId, $expertId, $gatewayId, $amount, $paymentMethod, $paymentStatus]);
                    $paymentId = $pdo->lastInsertId();
                    $pdo->commit();
                    
                    // Log trust event
                    TrustHelper::logEvent($pdo, 'booking_created', $expertId, $learnerId, [
                        'booking_id' => $bookingId,
                        'amount' => $amount,
                        'payment_method' => $paymentMethod
                    ]);

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
