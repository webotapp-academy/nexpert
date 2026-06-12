<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';
require_once dirname(dirname(dirname(__DIR__))) . '/includes/dynamic-pricing.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $expertId = $_GET['expert_id'] ?? null;

        if (!$expertId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expert ID is required']);
            exit;
        }

        // Get expert basic info with dynamic pricing fields
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                ep.full_name as name,
                ep.tagline as professional_title,
                ep.profile_photo,
                ep.rating_average as avg_rating,
                ep.total_reviews as review_count,
                ep.booking_count,
                ep.base_price,
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
                     ep.rating_average, ep.total_reviews, ep.booking_count, ep.base_price
        ");
        $stmt->execute([$expertId]);
        $expert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expert) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Expert not found']);
            exit;
        }

        // Calculate dynamic pricing (learner-specific)
        $booking_count = intval($expert['booking_count'] ?? 0);
        $base_price = floatval($expert['base_price'] ?? $expert['hourly_rate'] ?? 0);
        
        // Check if learner is logged in to get learner-specific pricing
        if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner') {
            $learner_id = $_SESSION['user_id'];
            
            // Get learner-specific dynamic pricing
            $pricing_info = calculate_learner_dynamic_price($pdo, $learner_id, $expertId, $base_price);
            
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
            $expert['tier_label'] = 'Standard';
            $expert['is_near_price_increase'] = false;
            $expert['bookings_until_next_tier'] = 9;
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

        // Get already booked slots (next 90 days)
        $stmt = $pdo->prepare("
            SELECT session_datetime, duration_minutes 
            FROM bookings 
            WHERE expert_id = ? 
            AND status IN ('confirmed', 'pending')
            AND session_datetime >= NOW()
            AND session_datetime <= DATE_ADD(NOW(), INTERVAL 90 DAY)
            ORDER BY session_datetime
        ");
        $stmt->execute([$expertId]);
        $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format rating
        $expert['avg_rating'] = round((float)$expert['avg_rating'], 1);
        $expert['hourly_rate'] = $expert['hourly_rate'] ?? 0;
        $expert['availability'] = $availability;
        $expert['booked_slots'] = $bookedSlots;

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
        
        // Verify learner exists in users table
        $verifyStmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'learner'");
        $verifyStmt->execute([$learnerId]);
        if (!$verifyStmt->fetch()) {
            error_log("Booking Error: Learner ID {$learnerId} not found in users table");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid user session. Please logout and login again.']);
            exit;
        }
        
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

        // Check for overlapping time slots (not just identical start times)
        // A booking from 2:00 PM for 60 mins (ends 3:00 PM) overlaps with:
        // - Any booking starting between 2:00 PM and 2:59 PM
        // - Any booking ending between 2:01 PM and 3:00 PM
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE expert_id = ? 
            AND status IN ('confirmed', 'pending')
            AND (
                (session_datetime < DATE_ADD(?, INTERVAL ? MINUTE) AND 
                 DATE_ADD(session_datetime, INTERVAL duration_minutes MINUTE) > ?)
            )
        ");
        $stmt->execute([$expertId, $sessionDatetime, $duration, $sessionDatetime]);
        $existingBooking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingBooking['count'] > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This time slot overlaps with an existing booking. Please choose another time.']);
            exit;
        }

        // Insert booking with pending status (waiting for expert acceptance)
        $stmt = $pdo->prepare("
            INSERT INTO bookings (expert_id, learner_id, session_datetime, duration_minutes, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())
        ");
        $stmt->execute([$expertId, $learnerId, $sessionDatetime, $duration]);
        $bookingId = $pdo->lastInsertId();

        // Send email notification to expert
        try {
            require_once __DIR__ . '/../connection/email-helper.php';
            
            // Get expert and learner details
            $stmt = $pdo->prepare("
                SELECT u.email as expert_email, ep.full_name as expert_name
                FROM users u
                JOIN expert_profiles ep ON u.id = ep.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$expertId]);
            $expertDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("
                SELECT u.email as learner_email, lp.full_name as learner_name
                FROM users u
                JOIN learner_profiles lp ON u.id = lp.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$learnerId]);
            $learnerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($expertDetails && $learnerDetails) {
                $emailHelper = new EmailHelper();
                
                // Format datetime for email
                $formattedDate = date('F j, Y', strtotime($sessionDatetime));
                $formattedTime = date('g:i A', strtotime($sessionDatetime));
                
                // Email to expert
                $expertSubject = "New Booking Request - Nexpert.ai";
                $expertMessage = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #1e3a8a;'>New Booking Request!</h2>
                        <p>Hello " . htmlspecialchars($expertDetails['expert_name']) . ",</p>
                        <p>You have received a new booking request from <strong>" . htmlspecialchars($learnerDetails['learner_name']) . "</strong>.</p>
                        
                        <div style='background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #1e3a8a;'>Session Details</h3>
                            <p><strong>Learner:</strong> " . htmlspecialchars($learnerDetails['learner_name']) . "</p>
                            <p><strong>Date:</strong> " . $formattedDate . "</p>
                            <p><strong>Time:</strong> " . $formattedTime . " IST</p>
                            <p><strong>Duration:</strong> " . $duration . " minutes</p>
                        </div>
                        
                        <p>Please log in to your dashboard to accept or reject this booking.</p>
                        <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
                    </div>
                ";
                
                $emailHelper->sendEmail(
                    $expertDetails['expert_email'],
                    $expertSubject,
                    $expertMessage
                );
                
                // Email to learner (confirmation)
                $learnerSubject = "Booking Confirmation - Nexpert.ai";
                $learnerMessage = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #1e3a8a;'>Booking Confirmed!</h2>
                        <p>Hello " . htmlspecialchars($learnerDetails['learner_name']) . ",</p>
                        <p>Your booking request has been submitted successfully and is now waiting for expert confirmation.</p>
                        
                        <div style='background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #1e3a8a;'>Session Details</h3>
                            <p><strong>Expert:</strong> " . htmlspecialchars($expertDetails['expert_name']) . "</p>
                            <p><strong>Date:</strong> " . $formattedDate . "</p>
                            <p><strong>Time:</strong> " . $formattedTime . " IST</p>
                            <p><strong>Duration:</strong> " . $duration . " minutes</p>
                        </div>
                        
                        <p>Once the expert accepts your booking, you'll receive another email with the Zoom meeting link.</p>
                        <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
                    </div>
                ";
                
                $emailHelper->sendEmail(
                    $learnerDetails['learner_email'],
                    $learnerSubject,
                    $learnerMessage
                );
            }
        } catch (Exception $e) {
            error_log("Email notification failed: " . $e->getMessage());
            // Don't fail the booking if email fails
        }

        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully. Check your email for confirmation.',
            'data' => [
                'booking_id' => $bookingId,
                'expert_id' => $expertId,
                'session_datetime' => $sessionDatetime,
                'duration' => $duration,
                'amount' => $amount
            ]
        ]);

    } elseif ($method === 'PUT') {
        // Handle reschedule request
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login as learner.']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $learnerId = $_SESSION['user_id'];
        
        $bookingId = $data['booking_id'] ?? null;
        $action = $data['action'] ?? null;
        
        if (!$bookingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
            exit;
        }
        
        // Verify booking belongs to this learner
        $stmt = $pdo->prepare("SELECT b.*, ep.full_name as expert_name, u.email as expert_email 
                               FROM bookings b 
                               INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
                               INNER JOIN users u ON b.expert_id = u.id
                               WHERE b.id = ? AND b.learner_id = ?");
        $stmt->execute([$bookingId, $learnerId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Booking not found or access denied']);
            exit;
        }
        
        if ($action === 'reschedule') {
            $newDate = $data['new_date'] ?? null;
            $newTime = $data['new_time'] ?? null;
            $reason = $data['reason'] ?? '';
            
            if (!$newDate || !$newTime) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New date and time are required']);
                exit;
            }
            
            // Validate the booking can be rescheduled (not completed or cancelled)
            if (in_array($booking['status'], ['completed', 'cancelled'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot reschedule a completed or cancelled session']);
                exit;
            }
            
            // Create new datetime
            $newDatetime = $newDate . ' ' . $newTime . ':00';
            
            // Check if new datetime is in the future
            if (strtotime($newDatetime) <= time()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New session time must be in the future']);
                exit;
            }
            
            // Update booking with reschedule request
            $stmt = $pdo->prepare("UPDATE bookings 
                                   SET reschedule_requested = 1, 
                                       reschedule_new_datetime = ?, 
                                       reschedule_reason = ?,
                                       reschedule_requested_by = 'learner',
                                       reschedule_requested_at = NOW(),
                                       updated_at = NOW() 
                                   WHERE id = ?");
            $stmt->execute([$newDatetime, $reason, $bookingId]);
            
            // Send notification to expert (optional - if email helper exists)
            try {
                require_once __DIR__ . '/../connection/email-helper.php';
                $emailHelper = new EmailHelper($pdo);
                
                // Get learner details
                $stmt = $pdo->prepare("SELECT lp.full_name FROM learner_profiles lp WHERE lp.user_id = ?");
                $stmt->execute([$learnerId]);
                $learnerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                $learnerName = $learnerDetails['full_name'] ?? 'Learner';
                
                $formattedNewDate = date('l, F j, Y', strtotime($newDate));
                $formattedNewTime = date('g:i A', strtotime($newTime));
                
                $expertSubject = "Reschedule Request - Session with " . $learnerName;
                $expertMessage = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #1e3a8a;'>Reschedule Request</h2>
                        <p>Hello " . htmlspecialchars($booking['expert_name']) . ",</p>
                        <p>" . htmlspecialchars($learnerName) . " has requested to reschedule their session with you.</p>
                        
                        <div style='background-color: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #92400e;'>Requested New Time</h3>
                            <p><strong>Date:</strong> " . $formattedNewDate . "</p>
                            <p><strong>Time:</strong> " . $formattedNewTime . " IST</p>
                            " . ($reason ? "<p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>" : "") . "
                        </div>
                        
                        <p>Please login to your dashboard to accept or decline this request.</p>
                        <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
                    </div>
                ";
                
                $emailHelper->sendEmail(
                    $booking['expert_email'],
                    $expertSubject,
                    $expertMessage
                );
            } catch (Exception $e) {
                error_log("Reschedule email notification failed: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Reschedule request submitted successfully'
            ]);
        } else if ($action === 'cancel') {
            $reason = $data['reason'] ?? '';
            
            // Allow cancellation for pending, confirmed, and scheduled bookings
            $allowedStatuses = ['pending', 'confirmed', 'scheduled'];
            if (!in_array($booking['status'], $allowedStatuses)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'This session cannot be cancelled']);
                exit;
            }
            
            if (empty(trim($reason))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cancellation reason is required']);
                exit;
            }
            
            // Update booking status to cancelled
            $stmt = $pdo->prepare("UPDATE bookings 
                                   SET status = 'cancelled', 
                                       cancellation_reason = ?,
                                       cancelled_by = 'learner',
                                       cancelled_at = NOW(),
                                       updated_at = NOW() 
                                   WHERE id = ?");
            $stmt->execute([$reason, $bookingId]);
            
            // Send notification to expert
            try {
                require_once __DIR__ . '/../connection/email-helper.php';
                $emailHelper = new EmailHelper($pdo);
                
                // Get learner details
                $stmt = $pdo->prepare("SELECT lp.full_name FROM learner_profiles lp WHERE lp.user_id = ?");
                $stmt->execute([$learnerId]);
                $learnerDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                $learnerName = $learnerDetails['full_name'] ?? 'Learner';
                
                $sessionDate = date('l, F j, Y', strtotime($booking['session_datetime']));
                $sessionTime = date('g:i A', strtotime($booking['session_datetime']));
                
                $expertSubject = "Session Cancelled - " . $learnerName;
                $expertMessage = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #dc2626;'>Session Cancelled</h2>
                        <p>Hello " . htmlspecialchars($booking['expert_name']) . ",</p>
                        <p>" . htmlspecialchars($learnerName) . " has cancelled their pending session with you.</p>
                        
                        <div style='background-color: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #b91c1c;'>Cancelled Session Details</h3>
                            <p><strong>Date:</strong> " . $sessionDate . "</p>
                            <p><strong>Time:</strong> " . $sessionTime . " IST</p>
                            <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
                        </div>
                        
                        <p style='margin-top: 30px;'>Best regards,<br>Nexpert.ai Team</p>
                    </div>
                ";
                
                $emailHelper->sendEmail(
                    $booking['expert_email'],
                    $expertSubject,
                    $expertMessage
                );
            } catch (Exception $e) {
                error_log("Cancel email notification failed: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Session cancelled successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Booking API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
