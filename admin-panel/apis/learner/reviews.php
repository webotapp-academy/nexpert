<?php
// Reviews API for learners to submit reviews after completing bookings/programs
require_once __DIR__ . '/../../../includes/session-config.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/pdo.php';
require_once __DIR__ . '/../connection/trust-helper.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $action = $data['action'] ?? 'submit_review';

        if ($action === 'submit_review') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $learnerId = $_SESSION['user_id'];
            $bookingId = $data['booking_id'] ?? null;
            $programId = $data['program_id'] ?? null;
            $rating = $data['rating'] ?? null;
            $reviewText = $data['review_text'] ?? '';

            if (!$rating || $rating < 1 || $rating > 5) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid rating']);
                exit;
            }

            if (!$bookingId && !$programId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Either booking_id or program_id is required']);
                exit;
            }

            $expertId = null;

            if ($bookingId) {
                // Verify booking exists, is completed, and belongs to this learner
                $stmt = $pdo->prepare("
                    SELECT expert_id, status 
                    FROM bookings 
                    WHERE id = ? AND learner_id = ? AND status = 'completed'
                ");
                $stmt->execute([$bookingId, $learnerId]);
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$booking) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Booking not found or not completed']);
                    exit;
                }

                $expertId = $booking['expert_id'];

                // Check if review already exists
                $stmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ? AND learner_id = ?");
                $stmt->execute([$bookingId, $learnerId]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'You have already reviewed this session']);
                    exit;
                }
            } elseif ($programId) {
                // Verify program enrollment exists and belongs to this learner
                $stmt = $pdo->prepare("
                    SELECT w.expert_id 
                    FROM learner_progress lp
                    JOIN workflows w ON lp.workflow_id = w.id
                    WHERE lp.workflow_id = ? AND lp.learner_id = ?
                ");
                $stmt->execute([$programId, $learnerId]);
                $program = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$program) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Program enrollment not found']);
                    exit;
                }

                $expertId = $program['expert_id'];

                // Check if review already exists for this program
                // Note: Since reviews table only has booking_id, we'll use a temporary workaround
                // TODO: Add program_id column to reviews table
                $stmt = $pdo->prepare("
                    SELECT id FROM reviews 
                    WHERE learner_id = ? AND expert_id = ? 
                    AND review_text LIKE CONCAT('Program Review - ', ?, '%')
                ");
                $stmt->execute([$learnerId, $expertId, $programId]);
                if ($stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'You have already reviewed this program']);
                    exit;
                }
            }

            // Insert review (using booking_id for both types for now)
            // For program reviews, we use booking_id as 0 and prefix the review text
            $finalBookingId = $bookingId ?: 0;
            $finalReviewText = $programId ? "Program Review - $programId: $reviewText" : $reviewText;
            
            $stmt = $pdo->prepare("
                INSERT INTO reviews (booking_id, learner_id, expert_id, rating, review_text, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'approved', NOW(), NOW())
            ");
            $stmt->execute([$finalBookingId, $learnerId, $expertId, $rating, $finalReviewText]);

            // Update expert's average rating and total reviews
            $stmt = $pdo->prepare("
                UPDATE expert_profiles 
                SET 
                    rating_average = (SELECT AVG(rating) FROM reviews WHERE expert_id = ? AND status = 'approved'),
                    total_reviews = (SELECT COUNT(*) FROM reviews WHERE expert_id = ? AND status = 'approved'),
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$expertId, $expertId, $expertId]);

            // Log trust event
            TrustHelper::logEvent($pdo, 'feedback_submitted', $expertId, $learnerId, [
                'booking_id' => $finalBookingId,
                'program_id' => $programId,
                'rating' => $rating,
                'review_text' => $finalReviewText
            ]);

            echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
            exit;
        }

        if ($action === 'check_review_status') {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }

            $learnerId = $_SESSION['user_id'];
            $bookingId = $data['booking_id'] ?? null;
            $programId = $data['program_id'] ?? null;

            if (!$bookingId && !$programId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Either booking_id or program_id is required']);
                exit;
            }

            $review = null;

            if ($bookingId) {
                // Check if review exists for booking
                $stmt = $pdo->prepare("SELECT id, rating, review_text FROM reviews WHERE booking_id = ? AND learner_id = ?");
                $stmt->execute([$bookingId, $learnerId]);
                $review = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif ($programId) {
                // Check if review exists for program
                // Get expert_id first
                $stmt = $pdo->prepare("
                    SELECT w.expert_id 
                    FROM learner_progress lp
                    JOIN workflows w ON lp.workflow_id = w.id
                    WHERE lp.workflow_id = ? AND lp.learner_id = ?
                ");
                $stmt->execute([$programId, $learnerId]);
                $program = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($program) {
                    $stmt = $pdo->prepare("
                        SELECT id, rating, review_text FROM reviews 
                        WHERE learner_id = ? AND expert_id = ? 
                        AND review_text LIKE CONCAT('Program Review - ', ?, '%')
                    ");
                    $stmt->execute([$learnerId, $program['expert_id'], $programId]);
                    $review = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Clean up the review text for display
                    if ($review && $review['review_text']) {
                        $review['review_text'] = preg_replace('/^Program Review - \d+: /', '', $review['review_text']);
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'has_review' => $review ? true : false,
                'review' => $review
            ]);
            exit;
        }
    }

    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get_reviews';

        if ($action === 'get_expert_reviews') {
            $expertId = $_GET['expert_id'] ?? null;
            $limit = $_GET['limit'] ?? 10;
            $offset = $_GET['offset'] ?? 0;

            if (!$expertId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Expert ID required']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT 
                    r.id, r.rating, r.review_text, r.created_at,
                    lp.full_name as learner_name, lp.profile_photo as learner_photo
                FROM reviews r
                JOIN learner_profiles lp ON r.learner_id = lp.user_id
                WHERE r.expert_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$expertId, (int)$limit, (int)$offset]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'reviews' => $reviews]);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);

} catch (PDOException $e) {
    error_log('Reviews API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
