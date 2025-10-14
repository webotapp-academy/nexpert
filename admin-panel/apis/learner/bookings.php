<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

// Check if learner is logged in
if (!isset($_SESSION['learner_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$learner_id = $_SESSION['learner_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $status = $_GET['status'] ?? 'upcoming'; // upcoming or past

        $query = "
            SELECT 
                b.id,
                b.session_date,
                b.session_time,
                b.duration,
                b.status,
                b.session_notes,
                b.amount,
                u.id as expert_id,
                u.name as expert_name,
                ep.professional_title as expert_title,
                ep.profile_photo as expert_photo
            FROM bookings b
            INNER JOIN users u ON b.expert_id = u.id
            INNER JOIN expert_profiles ep ON u.id = ep.user_id
            WHERE b.learner_id = ?
        ";

        if ($status === 'upcoming') {
            $query .= " AND (b.session_date > CURDATE() OR (b.session_date = CURDATE() AND b.session_time >= CURTIME()))";
            $query .= " AND b.status IN ('pending', 'confirmed')";
        } else {
            $query .= " AND ((b.session_date < CURDATE() OR (b.session_date = CURDATE() AND b.session_time < CURTIME())) OR b.status = 'completed')";
        }

        $query .= " ORDER BY b.session_date ASC, b.session_time ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$learner_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $bookings]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    error_log("Learner Bookings API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
