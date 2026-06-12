<?php
header('Content-Type: application/json');
require_once dirname(dirname(dirname(__DIR__))) . '/includes/session-config.php';
require_once __DIR__ . '/../connection/pdo.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get learner profile
    $stmt = $pdo->prepare("SELECT full_name, goals, challenges, education, profession FROM learner_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check for pending reviews (sessions with summary but no review)
    $stmt = $pdo->prepare("
        SELECT 
            b.id as booking_id,
            b.session_datetime,
            b.expert_id,
            ep.full_name as expert_name,
            ep.profile_photo as expert_photo
        FROM bookings b
        INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
        LEFT JOIN reviews r ON b.id = r.booking_id AND r.learner_id = ?
        WHERE b.learner_id = ?
        AND b.status = 'completed'
        AND b.session_summary IS NOT NULL
        AND b.session_summary != ''
        AND b.review_pending = 1
        AND r.id IS NULL
        ORDER BY b.updated_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId, $userId]);
    $pendingReview = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sessions,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
            COUNT(DISTINCT expert_id) as active_experts
        FROM bookings 
        WHERE learner_id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate progress percentage
    $progress = $stats['total_sessions'] > 0 
        ? round(($stats['completed_sessions'] / $stats['total_sessions']) * 100) 
        : 0;
    
    // Get upcoming sessions
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            b.session_datetime,
            b.duration_minutes,
            b.status,
            ep.full_name as expert_name,
            ep.profile_photo,
            ep.tagline
        FROM bookings b
        INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
        WHERE b.learner_id = ?
        AND b.status IN ('pending', 'confirmed')
        AND b.session_datetime > NOW()
        ORDER BY b.session_datetime ASC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $upcomingSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent sessions
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            b.session_datetime,
            b.duration_minutes,
            b.status,
            ep.full_name as expert_name,
            ep.profile_photo,
            ep.tagline
        FROM bookings b
        INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
        WHERE b.learner_id = ?
        AND b.status = 'completed'
        ORDER BY b.session_datetime DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (bookings and status changes)
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            b.session_datetime,
            b.status,
            b.created_at,
            b.updated_at,
            ep.full_name as expert_name
        FROM bookings b
        INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
        WHERE b.learner_id = ?
        ORDER BY b.updated_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all sessions for my-sessions page
    $stmt = $pdo->prepare("
        SELECT 
            b.id,
            b.expert_id,
            b.session_datetime,
            b.duration_minutes,
            b.status,
            ep.full_name as expert_name,
            ep.profile_photo,
            ep.tagline
        FROM bookings b
        INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
        WHERE b.learner_id = ?
        ORDER BY b.session_datetime DESC
    ");
    $stmt->execute([$userId]);
    $allSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'profile' => [
                'full_name' => $profile['full_name'] ?? 'Learner',
                'goals' => $profile['goals'] ?? '',
                'challenges' => $profile['challenges'] ?? '',
                'education' => $profile['education'] ?? '',
                'profession' => $profile['profession'] ?? ''
            ],
            'stats' => [
                'total_sessions' => (int)$stats['total_sessions'],
                'completed_sessions' => (int)$stats['completed_sessions'],
                'progress' => $progress,
                'active_experts' => (int)$stats['active_experts']
            ],
            'upcoming_sessions' => $upcomingSessions,
            'recent_sessions' => $recentSessions,
            'all_sessions' => $allSessions,
            'recent_activity' => $recentActivity,
            'pending_review' => $pendingReview // New field for review popup
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Learner Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
