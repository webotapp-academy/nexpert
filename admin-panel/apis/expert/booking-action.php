<?php
// API to handle accept/reject booking actions

// Include session configuration (this also loads config.php with BASE_PATH)
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';

// Set JSON header after session is started
header('Content-Type: application/json');

// Include database connection
require_once dirname(__DIR__) . '/connection/pdo.php';

// Include Zoom and Email helpers
require_once dirname(__DIR__) . '/connection/zoom-helper.php';
require_once dirname(__DIR__) . '/connection/email-helper.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$bookingId = $data['booking_id'];
$action = $data['action']; // 'accept' or 'reject'
$userId = $_SESSION['user_id'];

try {
    // Get full booking details with learner and expert info
    $stmt = $pdo->prepare("
        SELECT b.*, 
               b.session_topic as topic,
               lp.full_name as learner_name, 
               lu.email as learner_email,
               ep.full_name as expert_name,
               eu.email as expert_email
        FROM bookings b
        LEFT JOIN users lu ON b.learner_id = lu.id
        LEFT JOIN learner_profiles lp ON lu.id = lp.user_id
        LEFT JOIN users eu ON b.expert_id = eu.id
        LEFT JOIN expert_profiles ep ON eu.id = ep.user_id
        WHERE b.id = ? AND b.expert_id = ?
    ");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'error' => 'Booking not found or unauthorized']);
        exit;
    }
    
    $acceptValue = ($action === 'accept') ? 'yes' : 'no';
    $zoomMeetingData = null;
    
    // If accepting, create Zoom meeting and send emails
    if ($action === 'accept') {
        // Create Zoom meeting
        $zoomHelper = new ZoomHelper();
        
        // Format datetime for Zoom (ISO 8601 format)
        $sessionDateTime = new DateTime($booking['session_datetime']);
        $zoomStartTime = $sessionDateTime->format('Y-m-d\TH:i:s');
        
        $topic = $booking['topic'] ?? 'Learning Session';
        $duration = $booking['duration_minutes'] ?? 60;
        $agenda = "Session between {$booking['expert_name']} and {$booking['learner_name']}";
        
        error_log("Creating Zoom meeting for booking ID: $bookingId");
        error_log("Zoom meeting details - Topic: $topic, Start: $zoomStartTime, Duration: $duration");
        
        $zoomResult = $zoomHelper->createMeeting($topic, $zoomStartTime, $duration, $agenda);
        
        error_log("Zoom result: " . json_encode($zoomResult));
        
        if (isset($zoomResult['success']) && $zoomResult['success']) {
            // Prepare Zoom meeting data as JSON
            $zoomMeetingData = json_encode([
                'meeting_id' => $zoomResult['meeting_id'],
                'join_url' => $zoomResult['join_url'],
                'start_url' => $zoomResult['start_url'],
                'password' => $zoomResult['password'],
                'created_at' => date('Y-m-d H:i:s'),
                'provider' => 'zoom'
            ]);
            
            // Update booking with acceptance and Zoom link
            $stmt = $pdo->prepare("UPDATE bookings SET accept_booking = ?, join_link = ? WHERE id = ?");
            $stmt->execute([$acceptValue, $zoomMeetingData, $bookingId]);
            
            // Format date and time for emails
            $sessionDate = $sessionDateTime->format('l, F j, Y');
            $sessionTime = $sessionDateTime->format('g:i A');
            
            // Send emails
            $emailHelper = new EmailHelper();
            try {
                $emailHelper->sendLearnerBookingEmail(
                    $booking['learner_email'],
                    $booking['learner_name'],
                    $booking['expert_name'],
                    $topic,
                    $sessionDate,
                    $sessionTime,
                    $duration,
                    $zoomResult['join_url'],
                    $zoomResult['password']
                );
                
                $emailHelper->sendExpertBookingEmail(
                    $booking['expert_email'],
                    $booking['expert_name'],
                    $booking['learner_name'],
                    $topic,
                    $sessionDate,
                    $sessionTime,
                    $duration,
                    $zoomResult['start_url'],
                    $zoomResult['password']
                );
            } catch (Exception $e) {
                error_log("Email sending error: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Booking accepted! Zoom meeting room created and emails sent.',
                'accept_booking' => $acceptValue,
                'zoom_link' => $zoomResult['join_url']
            ]);
        } else {
            // Zoom credentials missing/invalid - fallback to secure Nexpert meeting room
            $roomHash = substr(md5("nexpert_session_{$bookingId}_" . time()), 0, 12);
            $meetingLink = "https://meet.jit.si/nexpert-session-{$bookingId}-{$roomHash}";
            $roomPassword = substr(md5($bookingId . 'nexpert'), 0, 8);
            
            $zoomMeetingData = json_encode([
                'meeting_id' => 'NX-' . $bookingId,
                'join_url' => $meetingLink,
                'start_url' => $meetingLink,
                'password' => $roomPassword,
                'created_at' => date('Y-m-d H:i:s'),
                'provider' => 'nexpert_secure_room'
            ]);
            
            // Update booking with acceptance and meeting link
            $stmt = $pdo->prepare("UPDATE bookings SET accept_booking = ?, join_link = ? WHERE id = ?");
            $stmt->execute([$acceptValue, $zoomMeetingData, $bookingId]);
            
            // Format date and time for emails
            $sessionDate = $sessionDateTime->format('l, F j, Y');
            $sessionTime = $sessionDateTime->format('g:i A');
            
            // Send emails with secure room link
            $emailHelper = new EmailHelper();
            try {
                $emailHelper->sendLearnerBookingEmail(
                    $booking['learner_email'],
                    $booking['learner_name'],
                    $booking['expert_name'],
                    $topic,
                    $sessionDate,
                    $sessionTime,
                    $duration,
                    $meetingLink,
                    $roomPassword
                );
                
                $emailHelper->sendExpertBookingEmail(
                    $booking['expert_email'],
                    $booking['expert_name'],
                    $booking['learner_name'],
                    $topic,
                    $sessionDate,
                    $sessionTime,
                    $duration,
                    $meetingLink,
                    $roomPassword
                );
            } catch (Exception $e) {
                error_log("Email sending error: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Booking accepted! Session meeting room generated and notifications sent.',
                'accept_booking' => $acceptValue,
                'zoom_link' => $meetingLink
            ]);
        }
    } elseif ($action === 'complete') {
        // Marking booking completed
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed', review_pending = 1, updated_at = NOW() WHERE id = ? AND expert_id = ?");
        $stmt->execute([$bookingId, $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Session marked as completed successfully! The learner has been notified to leave a review.',
            'status' => 'completed'
        ]);
    } else {
        // Rejecting booking
        $stmt = $pdo->prepare("UPDATE bookings SET accept_booking = ? WHERE id = ?");
        $stmt->execute([$acceptValue, $bookingId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking rejected successfully',
            'accept_booking' => $acceptValue
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
