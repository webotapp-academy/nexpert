<?php
// Program enrollment payment API with Razorpay integration
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../../../includes/payment-config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $action = $data['action'] ?? 'create_order';

        // -------- CREATE ORDER (Razorpay) OR TEST FLOW --------
        if ($action === 'create_order') {
            error_log("Program Payment API - Create Order Request");
            error_log("Session data: " . print_r($_SESSION, true));
            error_log("POST data: " . print_r($data, true));
            
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                error_log("Authorization failed - user_id: " . ($_SESSION['user_id'] ?? 'not set') . ", role: " . ($_SESSION['role'] ?? 'not set'));
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
                exit;
            }
            
            $learnerId = $_SESSION['user_id'];
            $programId = $data['program_id'] ?? null;
            $paymentMethod = $data['payment_method'] ?? 'razorpay';

            if (!$programId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }

            // Verify program exists and get expert_id and CANONICAL price from database
            $stmt = $pdo->prepare("SELECT expert_id, title, price_inr FROM workflows WHERE id = ? AND is_active = 1");
            $stmt->execute([$programId]);
            $program = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$program || !isset($program['price_inr']) || $program['price_inr'] <= 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Program not found or invalid price']);
                exit;
            }

            // ALWAYS use database price, NEVER trust client input
            $amount = $program['price_inr'];
            $expertId = $program['expert_id'];
            
            // Check if learner is already enrolled in this program
            $stmt = $pdo->prepare("SELECT id FROM learner_progress WHERE learner_id = ? AND workflow_id = ?");
            $stmt->execute([$learnerId, $programId]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'You are already enrolled in this program']);
                exit;
            }

            $pdo->beginTransaction();
            try {
                // Create enrollment entry
                $stmt = $pdo->prepare("
                    INSERT INTO learner_progress (learner_id, workflow_id, expert_id, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$learnerId, $programId, $expertId]);
                $enrollmentId = $pdo->lastInsertId();

                if ($paymentMethod === 'cash_test') {
                    // Immediate success for test mode
                    $gatewayId = 'CASH_PROGRAM_' . time() . '_' . rand(1000, 9999);
                    $stmt = $pdo->prepare("
                        INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, currency, payment_type, status, payment_date, created_at, updated_at)
                        VALUES (NULL, ?, ?, ?, ?, ?, 'package', 'success', NOW(), NOW(), NOW())
                    ");
                    $stmt->execute([$learnerId, $expertId, $gatewayId, $amount, PLATFORM_CURRENCY]);
                    $paymentId = $pdo->lastInsertId();
                    
                    // Enrollment is already created, just update timestamp
                    $pdo->prepare("UPDATE learner_progress SET updated_at=NOW() WHERE id=?")->execute([$enrollmentId]);
                    
                    $pdo->commit();
                    echo json_encode(['success' => true, 'mode' => 'test', 'data' => [
                        'payment_id' => $paymentId,
                        'enrollment_id' => $enrollmentId,
                        'amount' => $amount,
                        'status' => 'success'
                    ]]);
                    exit;
                }

                // Create Razorpay order (amount in paise)
                $orderPayload = [
                    'amount' => (int)round($amount * 100),
                    'currency' => PLATFORM_CURRENCY,
                    'receipt' => 'ENR_' . $enrollmentId,
                    'payment_capture' => 1
                ];
                $ch = curl_init('https://api.razorpay.com/v1/orders');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
                    CURLOPT_POSTFIELDS => json_encode($orderPayload),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $orderResp = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                error_log("Razorpay order response: HTTP $httpCode, Body: $orderResp");
                $orderData = json_decode($orderResp, true);

                if (!isset($orderData['id'])) {
                    $pdo->rollBack();
                    http_response_code(502);
                    echo json_encode(['success' => false, 'message' => 'Failed to create Razorpay order']);
                    exit;
                }

                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'mode' => 'razorpay',
                    'data' => [
                        'razorpay_order_id' => $orderData['id'],
                        'razorpay_key_id' => RAZORPAY_KEY_ID,
                        'enrollment_id' => $enrollmentId,
                        'amount' => $amount
                    ]
                ]);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Program Payment Create Order Error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        }

        // -------- VERIFY PAYMENT --------
        if ($action === 'verify_payment') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $learnerId = $_SESSION['user_id'];
            $razorpayPaymentId = $data['razorpay_payment_id'] ?? null;
            $razorpayOrderId = $data['razorpay_order_id'] ?? null;
            $razorpaySignature = $data['razorpay_signature'] ?? null;
            $enrollmentId = $data['enrollment_id'] ?? null;

            if (!$razorpayPaymentId || !$razorpayOrderId || !$razorpaySignature || !$enrollmentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing verification parameters']);
                exit;
            }

            // Verify signature
            $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
            if ($expectedSignature !== $razorpaySignature) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid payment signature']);
                exit;
            }

            // Fetch enrollment details
            $stmt = $pdo->prepare("
                SELECT lp.*, w.price_inr, w.expert_id 
                FROM learner_progress lp
                JOIN workflows w ON lp.workflow_id = w.id
                WHERE lp.id = ? AND lp.learner_id = ?
            ");
            $stmt->execute([$enrollmentId, $learnerId]);
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$enrollment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
                exit;
            }

            $pdo->beginTransaction();
            try {
                // Insert payment record (booking_id is NULL for program payments)
                $stmt = $pdo->prepare("
                    INSERT INTO payments (booking_id, learner_id, expert_id, payment_gateway_id, amount, currency, payment_type, status, payment_date, created_at, updated_at)
                    VALUES (NULL, ?, ?, ?, ?, ?, 'package', 'success', NOW(), NOW(), NOW())
                ");
                $stmt->execute([
                    $learnerId,
                    $enrollment['expert_id'],
                    $razorpayPaymentId,
                    $enrollment['price_inr'],
                    PLATFORM_CURRENCY
                ]);

                // Update enrollment timestamp
                $stmt = $pdo->prepare("UPDATE learner_progress SET updated_at=NOW() WHERE id=?");
                $stmt->execute([$enrollmentId]);

                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment verified and enrollment activated',
                    'enrollment_id' => $enrollmentId
                ]);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Program Payment Verification Error: ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database error during verification']);
                exit;
            }
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;

} catch (Exception $e) {
    error_log('Program Payment API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
