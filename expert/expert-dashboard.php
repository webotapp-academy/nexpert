<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config (defines BASE_PATH / BASE_URL and starts session)
require_once dirname(__DIR__) . '/includes/session-config.php';

// DB connection
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Expert Dashboard - Nexpert.ai";
$panel_type = "expert";

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';

// Check onboarding steps completion
$userId = $_SESSION['user_id'] ?? null;
$profileComplete = false;
$kycComplete = false;
$availabilitySet = false;
$firstBooking = false;
$expertProfileId = null;

if ($userId) {
    // Check profile completion - query by user_id instead of id
    $stmt = $pdo->prepare("
        SELECT id, full_name, tagline, bio_short, expertise_verticals, verification_status
        FROM expert_profiles 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        $expertProfileId = $profile['id'];
        
        if (!empty($profile['full_name']) && !empty($profile['tagline']) && 
            !empty($profile['bio_short']) && !empty($profile['expertise_verticals'])) {
            $profileComplete = true;
        }
        
        // Check KYC verification
        if ($profile['verification_status'] === 'approved') {
            $kycComplete = true;
        }
    }
    
    // Check if availability is set
    if ($expertProfileId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM expert_availability WHERE expert_id = ?");
        $stmt->execute([$expertProfileId]);
        $availCount = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($availCount && $availCount['count'] > 0) {
            $availabilitySet = true;
        }
        
        // Check for first booking - use userId for bookings.expert_id
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ?");
        $stmt->execute([$userId]);
        $bookingCount = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($bookingCount && $bookingCount['count'] > 0) {
            $firstBooking = true;
        }
    }
}

// Get total completed sessions count for achievements
$totalCompletedSessions = 0;
$showAchievementPopup = false;
$achievementData = [];

if ($userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE expert_id = ? 
            AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        $totalCompletedSessions = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Fallback to trust_events session count if bookings is empty
        if ($totalCompletedSessions === 0) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM trust_events 
                WHERE expert_id = ? 
                AND event_type = 'session_completed'
            ");
            $stmt->execute([$userId]);
            $totalCompletedSessions = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        }
        
        // Check if user just achieved a milestone
        if (!isset($_SESSION['shown_achievements'])) {
            $_SESSION['shown_achievements'] = [];
        }
        
        $milestones = [10, 20, 50, 100];
        foreach ($milestones as $milestone) {
            if ($totalCompletedSessions >= $milestone && !in_array($milestone, $_SESSION['shown_achievements'])) {
                $showAchievementPopup = true;
                
                // Get some learner names
                $stmt = $pdo->prepare("
                    SELECT DISTINCT lp.full_name 
                    FROM bookings b
                    LEFT JOIN users u ON b.learner_id = u.id
                    LEFT JOIN learner_profiles lp ON u.id = lp.user_id
                    WHERE b.expert_id = ? AND b.status = 'completed'
                    ORDER BY b.session_datetime DESC
                    LIMIT 4
                ");
                $stmt->execute([$userId]);
                $learners = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Calculate returning learners
                $stmt = $pdo->prepare("
                    SELECT COUNT(DISTINCT learner_id) as count
                    FROM (
                        SELECT learner_id, COUNT(*) as booking_count
                        FROM bookings 
                        WHERE expert_id = ? AND status = 'completed'
                        GROUP BY learner_id
                        HAVING booking_count > 1
                    ) returning_learners
                ");
                $stmt->execute([$userId]);
                $returningLearners = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                
                $achievementData = [
                    'milestone' => $milestone,
                    'badge_name' => getBadgeName($milestone),
                    'badge_description' => getBadgeDescription($milestone),
                    'rating' => 'N/A',
                    'sessions_completed' => $totalCompletedSessions,
                    'returning_learners' => $returningLearners,
                    'learner_names' => array_filter($learners),
                    'next_milestone' => getNextMilestone($milestone),
                    'date' => date('F d, Y')
                ];
                
                $_SESSION['shown_achievements'][] = $milestone;
                break;
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching completed sessions: " . $e->getMessage());
    }
}

// Helper functions for badge data
function getBadgeName($milestone) {
    $badges = [
        10 => 'Rising Star',
        20 => 'Session Champion',
        50 => 'Expert Mentor',
        100 => 'Master Educator'
    ];
    return $badges[$milestone] ?? 'Achievement Unlocked';
}

function getBadgeDescription($milestone) {
    $descriptions = [
        10 => 'You\'ve started making an impact!',
        20 => 'Building strong connections!',
        50 => 'A true expert in your field!',
        100 => 'Century of excellence achieved!'
    ];
    return $descriptions[$milestone] ?? 'Great achievement!';
}

function getNextMilestone($current) {
    $milestones = [10, 20, 50, 100, 200];
    foreach ($milestones as $milestone) {
        if ($milestone > $current) {
            return $milestone;
        }
    }
    return null;
}

// Dashboard stats
$totalEarnings = 0;
$activeLearners = 0;
$sessionsThisMonth = 0;
$followUpsSent = 0;
$rebookingRate = 0;
$profileViews = 0;

if ($userId) {
    try {
        // Get total earnings from both bookings and programs
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(p.amount), 0) as total
            FROM payments p
            WHERE p.expert_id = ? AND p.status = 'success'
        ");
        $stmt->execute([$userId]);
        $totalEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Error fetching earnings: " . $e->getMessage());
        $totalEarnings = 0;
    }
    
    try {
        // Get active learners count
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT b.learner_id) as count
            FROM bookings b
            WHERE b.expert_id = ?
        ");
        $stmt->execute([$userId]);
        $activeLearners = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        error_log("Error fetching active learners: " . $e->getMessage());
        $activeLearners = 0;
    }
    
    try {
        // Get sessions this month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM bookings
            WHERE expert_id = ? 
            AND MONTH(session_datetime) = MONTH(CURRENT_DATE())
            AND YEAR(session_datetime) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $sessionsThisMonth = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        error_log("Error fetching sessions this month: " . $e->getMessage());
        $sessionsThisMonth = 0;
    }
    
    // Get follow-ups sent this month
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM messages m
            WHERE m.sender_id = ? 
            AND m.sender_type = 'expert'
            AND MONTH(m.created_at) = MONTH(CURRENT_DATE())
            AND YEAR(m.created_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $followUpsSent = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        $followUpsSent = 0;
    }
    
    // Calculate rebooking rate (learners who booked more than once)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT learner_id) as total_learners,
            COUNT(DISTINCT CASE WHEN booking_count > 1 THEN learner_id END) as rebooked_learners
        FROM (
            SELECT learner_id, COUNT(*) as booking_count
            FROM bookings 
            WHERE expert_id = ?
            GROUP BY learner_id
        ) learner_bookings
    ");
    $stmt->execute([$userId]);
    $rebookingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rebookingData && $rebookingData['total_learners'] > 0) {
        $rebookingRate = round(($rebookingData['rebooked_learners'] / $rebookingData['total_learners']) * 100);
    }
    
    // Get profile views
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM profile_views pv
            JOIN expert_profiles ep ON ep.id = pv.expert_profile_id
            WHERE ep.user_id = ?
            AND MONTH(pv.viewed_at) = MONTH(CURRENT_DATE())
            AND YEAR(pv.viewed_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $profileViews = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
    } catch (Exception $e) {
        $profileViews = 0;
    }

    // Get Trust State
    $trustState = null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM trust_state WHERE expert_id = ?");
        $stmt->execute([$userId]);
        $trustState = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching trust state: " . $e->getMessage());
    }
}

// Get recent activity (last 10 bookings)
$recentActivity = [];
$highInterestLearners = [];
$learnerActivityData = [];

if ($userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                b.id,
                b.session_datetime,
                b.status,
                b.accept_booking,
                b.created_at,
                lp.full_name as learner_name,
                lp.profile_photo as learner_photo,
                u.email as learner_email
            FROM bookings b
            LEFT JOIN users u ON b.learner_id = u.id
            LEFT JOIN learner_profiles lp ON u.id = lp.user_id
            WHERE b.expert_id = ? AND b.session_datetime >= NOW()
            ORDER BY b.session_datetime ASC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching recent activity: " . $e->getMessage());
        $recentActivity = [];
    }
    
    try {
        // Get high-interest learners
        $stmt = $pdo->prepare("
            SELECT 
                lp.full_name as learner_name,
                lp.profile_photo as learner_photo,
                u.email as learner_email,
                u.id as learner_id,
                MAX(b.session_datetime) as session_datetime,
                MAX(b.created_at) as created_at,
                ep.expertise_verticals as expertise,
                COUNT(b.id) as total_sessions,
                CASE 
                    WHEN COUNT(b.id) > 1 THEN 'high'
                    WHEN MAX(b.session_datetime) >= DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 'medium'
                    ELSE 'low'
                END as interest_level,
                CASE 
                    WHEN COUNT(b.id) > 3 THEN 85
                    WHEN MAX(b.session_datetime) >= DATE_SUB(NOW(), INTERVAL 3 DAY) THEN 70
                    ELSE 50
                END as engagement_score
            FROM bookings b
            LEFT JOIN users u ON b.learner_id = u.id
            LEFT JOIN learner_profiles lp ON u.id = lp.user_id
            LEFT JOIN expert_profiles ep ON ep.user_id = ?
            WHERE b.expert_id = ?
            AND b.session_datetime >= NOW()
            GROUP BY u.id, lp.full_name, lp.profile_photo, u.email, ep.expertise_verticals
            ORDER BY MAX(b.session_datetime) ASC
            LIMIT 5
        ");
        $stmt->execute([$userId, $userId]);
        $highInterestLearners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching high interest learners: " . $e->getMessage());
        $highInterestLearners = [];
    }
    
    try {
        // Get learner activity data for the right panel
        $stmt = $pdo->prepare("
            SELECT 
                lp.full_name as learner_name,
                COUNT(DISTINCT b.id) as total_bookings,
                MAX(b.session_datetime) as last_session,
                CASE 
                    WHEN COUNT(b.id) > 2 THEN 85
                    WHEN COUNT(b.id) > 1 THEN 70
                    ELSE 50
                END as engagement_score
            FROM bookings b
            LEFT JOIN users u ON b.learner_id = u.id
            LEFT JOIN learner_profiles lp ON u.id = lp.user_id
            WHERE b.expert_id = ?
            GROUP BY u.id, lp.full_name
            ORDER BY engagement_score DESC
            LIMIT 8
        ");
        $stmt->execute([$userId]);
        $learnerActivityData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching learner activity data: " . $e->getMessage());
        $learnerActivityData = [];
    }

    // Get latest Daily Credibility Card
    $latestCard = null;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM credibility_card_events 
            WHERE expert_id = ? 
            ORDER BY generated_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $cardRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cardRow) {
            $latestCard = $cardRow;
            $latestCard['card_data'] = json_decode($cardRow['card_data'], true);
        }
    } catch (Exception $e) {
        error_log("Error fetching credibility card: " . $e->getMessage());
    }
}

// Helper for Trust Score Labels
function getScoreLabel($score) {
    if ($score >= 80) return ['text' => 'High', 'class' => 'text-green-400 bg-green-950/40 border border-green-800/40 px-2 py-0.5 rounded text-[10px] font-bold uppercase'];
    if ($score >= 50) return ['text' => 'Medium', 'class' => 'text-yellow-400 bg-yellow-950/40 border border-yellow-800/40 px-2 py-0.5 rounded text-[10px] font-bold uppercase'];
    return ['text' => 'Low', 'class' => 'text-red-400 bg-red-950/40 border border-red-800/40 px-2 py-0.5 rounded text-[10px] font-bold uppercase'];
}

?>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>

<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #080B10;
        min-height: 100vh;
    }
    .card {
        background: #131b2e;
        border: 1px solid #1e293b;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .cred-dash-card {
        background: radial-gradient(circle at 10% 20%, rgba(79, 70, 229, 0.15), transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.12), transparent 40%),
                    #0a0e1c;
        box-shadow: 0 20px 50px -10px rgba(0,0,0,0.7), 0 0 30px rgba(99, 102, 241, 0.1);
    }
</style>

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8 bg-[#080B10] min-h-screen dashboard-container space-y-6">
    <!-- Welcome Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Welcome to Your Dashboard</h1>
            <p class="text-sm sm:text-base text-gray-400">Manage your profile, live trust telemetry, and grow your expert reputation</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="index.php?panel=expert&page=daily-credibility-card" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-lg shadow-indigo-900/40">
                <span>✨ View Credibility Card</span>
                <span>➔</span>
            </a>
        </div>
    </div>

    <!-- DAILY CREDIBILITY UPDATE HERO CARD -->
    <?php if ($latestCard && !empty($latestCard['card_data'])): 
        $cData = $latestCard['card_data'];
        $cBefore = (int)($latestCard['score_before'] ?? ($cData['metrics']['yesterday_points'] ?? 712));
        $cAfter = (int)($latestCard['score_after'] ?? ($cData['metrics']['today_points'] ?? 748));
        $cGain = (int)($latestCard['point_gain'] ?? ($cAfter - $cBefore));
        if ($cGain <= 0) $cGain = 15;
    ?>
    <div class="cred-dash-card border border-indigo-500/30 rounded-2xl p-6 sm:p-8 relative overflow-hidden">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            
            <!-- Left Side: Profile & Score Jump -->
            <div class="flex items-center gap-5">
                <?php
                $dashPhotoSrc = '';
                if (!empty($expertProfile['profile_photo'])) {
                    $rawP = $expertProfile['profile_photo'];
                    if (preg_match('/^(https?:\/\/|data:)/', $rawP)) {
                        $dashPhotoSrc = $rawP;
                    } elseif (strpos($rawP, BASE_PATH) === 0) {
                        $dashPhotoSrc = $rawP;
                    } else {
                        $dashPhotoSrc = BASE_PATH . '/' . ltrim($rawP, '/');
                    }
                }
                ?>
                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-indigo-500 bg-gray-900 shrink-0 shadow-lg shadow-purple-900/50">
                    <?php if (!empty($dashPhotoSrc)): ?>
                        <img src="<?= htmlspecialchars($dashPhotoSrc) ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full hidden items-center justify-center font-black text-xl text-purple-300 bg-indigo-950">
                            <?= strtoupper(substr($expertProfile['full_name'] ?? 'E', 0, 1)) ?>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center font-black text-xl text-purple-300 bg-indigo-950">
                            <?= strtoupper(substr($expertProfile['full_name'] ?? 'E', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">DAILY CREDIBILITY UPDATE</span>
                        <span class="bg-indigo-950 border border-indigo-500/40 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded-full">AI-VERIFIED</span>
                    </div>
                    <div class="flex items-baseline gap-3 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-white"><?= $cBefore ?></span>
                        <span class="text-gray-500 text-sm">➔</span>
                        <span class="text-3xl sm:text-4xl font-black text-[#10b981]"><?= $cAfter ?></span>
                        <span class="text-xs font-black text-emerald-400 bg-emerald-950/60 border border-emerald-500/40 px-2 py-0.5 rounded-full uppercase">+<?= $cGain ?> Points</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Top <?= $cData['ranking']['percentile'] ?? 8 ?>% ranking · Verified status with 90% confidence</p>
                </div>
            </div>

            <!-- Right Side: CTAs & Share -->
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <a href="index.php?panel=expert&page=daily-credibility-card" class="bg-[#0077B5] hover:bg-[#006097] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-md">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.25c-.95 0-1.72.77-1.72 1.72s.77 1.72 1.72 1.72 1.72-.77 1.72-1.72-.77-1.72-1.72-1.72Z"/></svg>
                    Share to LinkedIn
                </a>
                <a href="index.php?panel=expert&page=daily-credibility-card" class="bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-200 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span>Export Card PNG</span>
                    <span>⬇</span>
                </a>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- Analytics Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8 grid-4">
        <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-4 sm:p-6 card" style="padding: 24px; border-radius: 8px;">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm font-medium" style="font-size: 14px;">Overview</h3>
                <div class="w-8 h-8 bg-blue-950/40 rounded-full flex items-center justify-center border border-blue-800/40" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white" style="font-size: 24px; font-weight: bold;"><?php echo $activeLearners; ?></p>
        </div>

        <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-4 sm:p-6 card" style="padding: 24px; border-radius: 8px;">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm font-medium" style="font-size: 14px;">Follow-ups Sent</h3>
                <div class="w-8 h-8 bg-green-950/40 rounded-full flex items-center justify-center border border-green-800/40" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white" style="font-size: 24px; font-weight: bold;"><?php echo $followUpsSent; ?></p>
        </div>

        <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-4 sm:p-6 card" style="padding: 24px; border-radius: 8px;">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm font-medium" style="font-size: 14px;">Session Rebooked</h3>
                <div class="w-8 h-8 bg-purple-950/40 rounded-full flex items-center justify-center border border-purple-800/40" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white" style="font-size: 24px; font-weight: bold;"><?php echo $sessionsThisMonth; ?></p>
            <p class="text-sm text-gray-500" style="font-size: 12px; margin-top: 4px;">Rebooking Rate</p>
            <p class="text-lg font-semibold text-[#00D4AA]" style="font-size: 18px; font-weight: 600;"><?php echo $rebookingRate; ?>%</p>
        </div>

        <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-4 sm:p-6 card" style="padding: 24px; border-radius: 8px;">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm font-medium" style="font-size: 14px;">Total Earnings</h3>
                <div class="w-8 h-8 bg-yellow-950/40 rounded-full flex items-center justify-center border border-yellow-800/40" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-white" style="font-size: 24px; font-weight: bold;">₹<?php echo number_format($totalEarnings, 0); ?></p>
        </div>
    </div>

    <!-- Trust Intelligence Card — MVP2 -->
    <?php if ($trustState):
        $tsScore     = round($trustState['overall_score'] ?? 0, 1);
        $tsBand      = $trustState['band_name'] ?? 'Unverified';
        $tsConf      = round($trustState['confidence_score'] ?? 0);
        $tsTrend     = $trustState['trend_direction'] ?? 'stable';
        $trendIcon   = match($tsTrend){ 'rising'=>'↑ Rising','declining'=>'↓ Declining',default=>'→ Stable'};
        $trendColor  = match($tsTrend){ 'rising'=>'text-[#00D4AA]','declining'=>'text-red-400',default=>'text-gray-400'};
        $circumf     = 339.3;
        $ringOffset  = round($circumf - ($tsScore / 100) * $circumf, 2);
        $ringColor   = match(true){ $tsScore>=90=>'#F5A623',$tsScore>=75=>'#00D4AA',$tsScore>=60=>'#3B82F6',$tsScore>=40=>'#9CA3AF',default=>'#6B7280'};
        // Next milestone
        $nextBand    = match(true){ $tsScore>=90=>'You are Sovereign — the highest band',$tsScore>=75=>"Need ".round(90-$tsScore,1)." more points for Sovereign",$tsScore>=60=>"Need ".round(75-$tsScore,1)." more points for Established",$tsScore>=40=>"Need ".round(60-$tsScore,1)." more points for Verified",default=>"Need ".round(40-$tsScore,1)." more points for Emerging"};
    ?>
    <div class="bg-[#131b2e] rounded-xl shadow-lg border border-gray-800 p-6 mb-8">
        <!-- Header row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-xl font-bold text-white mb-1">Trust Intelligence</h2>
                <p class="text-sm text-gray-400">Based on <?php echo (int)$totalCompletedSessions; ?> sessions · <?php echo $tsConf; ?>% confidence · <span class="<?php echo $trendColor; ?>"><?php echo $trendIcon; ?></span></p>
            </div>
            <!-- SVG Score Ring -->
            <div class="flex items-center gap-4 flex-shrink-0">
                <div class="relative w-20 h-20">
                    <svg class="w-20 h-20" style="transform:rotate(-90deg)" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="7"/>
                        <circle cx="40" cy="40" r="34" fill="none"
                                stroke="<?php echo $ringColor; ?>" stroke-width="7" stroke-linecap="round"
                                stroke-dasharray="<?php echo $circumf; ?>"
                                stroke-dashoffset="<?php echo $ringOffset; ?>"
                                style="transition:stroke-dashoffset 1.5s ease"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-xl font-black text-white leading-none"><?php echo $tsScore; ?></div>
                        <div class="text-[9px] text-gray-500">/100</div>
                    </div>
                </div>
                <div>
                    <div class="text-xl font-bold" style="color:<?php echo $ringColor; ?>"><?php echo htmlspecialchars($tsBand); ?></div>
                    <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($nextBand); ?></div>
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=certificate"
                       class="inline-flex items-center gap-1 mt-2 text-[#00D4AA] text-xs font-semibold hover:text-white transition">
                        View Certificate →
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $metrics = [
                ['label' => 'Structure', 'score' => $trustState['structure_score'] ?? 0],
                ['label' => 'Outcome', 'score' => $trustState['outcome_score'] ?? 0],
                ['label' => 'Boundary', 'score' => $trustState['boundary_score'] ?? 0],
                ['label' => 'Consistency', 'score' => $trustState['consistency_score'] ?? 0],
            ];
            foreach ($metrics as $metric): 
                $labelData = getScoreLabel($metric['score']);
            ?>
            <div class="bg-[#0e1322]/80 backdrop-blur-sm p-4 rounded-xl border border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider"><?php echo $metric['label']; ?></p>
                    <span class="<?php echo $labelData['class']; ?>">
                        <?php echo $labelData['text']; ?>
                    </span>
                </div>
                <div class="flex items-end justify-between">
                    <span class="text-xl font-bold text-white"><?php echo number_format($metric['score'], 0); ?>%</span>
                    <div class="w-16 h-1 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-[#00D4AA]" style="width: <?php echo $metric['score']; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Stability + growth card -->
        <div class="mt-4 grid md:grid-cols-2 gap-3">
            <div class="p-3 bg-[#0e1322] rounded-lg border border-gray-800 flex items-center gap-3">
                <span class="text-lg">📊</span>
                <p class="text-sm text-gray-300">
                    <strong class="text-white">Stability: <?php echo number_format($trustState['stability_score'] ?? 0, 0); ?>%</strong>
                    <span class="text-gray-500 text-xs block mt-0.5">Consistency of your score over recent sessions</span>
                </p>
            </div>
            <?php
            // Growth recommendation — PHP logic, no AI needed
            $lowestScore  = 100; $lowestDim = 'outcome';
            $dims = ['outcome'=>$trustState['outcome_score']??0,'consistency'=>$trustState['consistency_score']??0,'structure'=>$trustState['structure_score']??0,'boundary'=>$trustState['boundary_score']??0];
            foreach($dims as $d=>$v){ if($v < $lowestScore){ $lowestScore=$v; $lowestDim=$d; } }
            $growthActions = [
                'outcome'     => 'Ask your next 3 learners to submit a goal before their session so outcomes can be tracked.',
                'consistency' => 'Respond to all learner messages within 24 hours this week to improve your consistency signal.',
                'structure'   => 'Complete your profile — add a detailed bio, expertise areas, and at least one certification.',
                'boundary'    => 'Confirm your next session 2 hours before it starts to improve your reliability signal.',
            ];
            ?>
            <div class="p-3 bg-[#0e1322] rounded-lg border border-[#00D4AA]/20">
                <div class="flex items-start gap-2">
                    <span class="text-lg">🌱</span>
                    <div>
                        <p class="text-xs font-bold text-[#00D4AA] mb-1">Growth Action</p>
                        <p class="text-xs text-gray-300 leading-relaxed"><?php echo $growthActions[$lowestDim]; ?></p>
                        <p class="text-[10px] text-gray-600 mt-1">Weakest signal: <?php echo ucfirst($lowestDim); ?> (<?php echo round($lowestScore); ?>/100)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Dashboard Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Left Section - High Interest Learners & AI Suggestions -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- High Interest Learners -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-800">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-xl font-bold text-white">High Interest Learners</h2>
                        <div class="w-5 h-5 bg-[#00D4AA] rounded-full flex items-center justify-center">
                            <span class="text-[#080B10] text-xs font-bold">i</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mt-1">These learners have shown high engagement after their last session. Send follow-ups to ensure continued progress.</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <?php if (empty($highInterestLearners)): ?>
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="text-gray-400">No high-interest learners yet</p>
                            <p class="text-sm text-gray-500 mt-2">Complete more sessions to see engagement insights</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($highInterestLearners as $index => $learner): 
                            $sessionDate = date('M d, Y - g:i A', strtotime($learner['session_datetime']));
                            $timeAgo = '';
                            $timeDiff = time() - strtotime($learner['session_datetime']);
                            if ($timeDiff < 3600) {
                                $timeAgo = floor($timeDiff / 60) . ' minutes ago';
                            } elseif ($timeDiff < 86400) {
                                $timeAgo = floor($timeDiff / 3600) . ' hours ago';
                            } else {
                                $timeAgo = floor($timeDiff / 86400) . ' days ago';
                            }
                            
                            $initials = strtoupper(substr($learner['learner_name'] ?? 'L', 0, 1) . substr(explode(' ', $learner['learner_name'] ?? 'L')[1] ?? '', 0, 1));
                            $engagementScore = round($learner['engagement_score']);
                            
                            // Format expertise - remove JSON brackets and quotes
                            $expertise = $learner['expertise'] ?? 'General Mentoring';
                            if (strpos($expertise, '[') !== false) {
                                // It's a JSON array, decode and format it
                                $expertiseArray = json_decode($expertise, true);
                                if (is_array($expertiseArray)) {
                                    $expertise = implode(', ', $expertiseArray); // Show all items, no truncation
                                } else {
                                    // If JSON decode fails, clean up manually
                                    $expertise = str_replace(['[', ']', '"'], '', $expertise);
                                    $expertise = trim($expertise);
                                }
                            }
                            
                            // AI suggestions based on engagement and session count
                            $aiSuggestions = [
                                "showing strong continued interest. Recommend sending a follow-up message with next-step guidance.",
                                "demonstrating high engagement. Consider offering advanced topics or specialized sessions.",
                                "ready for the next level. Send personalized learning recommendations.",
                                "highly engaged. Suggest creating a customized learning path.",
                                "showing excellent progress. Recommend additional resources and follow-up sessions."
                            ];
                            $suggestion = $learner['learner_name'] . " is " . $aiSuggestions[0];
                        ?>
                        <!-- Learner Card -->
                        <div class="border border-gray-800 rounded-lg p-4 bg-[#0e1322]">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <?php if (!empty($learner['learner_photo'])): ?>
                                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($learner['learner_photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($learner['learner_name']); ?>" 
                                             class="w-12 h-12 rounded-full object-cover border border-gray-800">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <span class="text-white font-semibold"><?php echo $initials; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-semibold text-white"><?php echo htmlspecialchars($learner['learner_name'] ?? 'Anonymous Learner'); ?></h3>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($expertise); ?></p>
                                        <div class="flex items-center space-x-1 mt-1">
                                            <div class="text-sm text-gray-400">
                                                <span class="font-medium text-gray-300">Last Booking:</span> <?php echo $sessionDate; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-950/40 text-blue-400 border border-blue-800/40">
                                        LAST SESSION
                                    </span>
                                </div>
                            </div>
                            
                            <div class="bg-[#131b2e] border border-blue-900/40 rounded-lg p-3 mb-3">
                                <div class="flex items-start space-x-2">
                                    <div class="w-5 h-5 bg-[#00D4AA] rounded flex items-center justify-center mt-0.5">
                                        <svg class="w-3 h-3 text-[#080B10]" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-blue-400">AI Suggestion</p>
                                        <p class="text-sm text-gray-300 mt-1"><?php echo htmlspecialchars($suggestion); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">Engagement score: <span class="text-white font-bold"><?php echo $engagementScore; ?>%</span></p>
                                    <p class="text-sm text-gray-500"><?php echo $sessionDate; ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="sendFollowUpEmail(<?php echo $learner['learner_id']; ?>, '<?php echo htmlspecialchars($learner['learner_name'], ENT_QUOTES); ?>')" 
                                       class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg text-sm font-medium hover:bg-[#00bda0] transition">
                                        Send Follow-up
                                    </button>
                                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=learner-management&learner_id=<?php echo $learner['learner_id']; ?>" 
                                       class="px-4 py-2 border border-gray-700 text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                                        View Activity
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Right Section - Learner Activity -->
        <div class="space-y-6">
            
            <!-- Learner Activity -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-800">
                    <h2 class="text-xl font-bold text-white">Learner Activity</h2>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Engagement Score</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-400">Profile Views</p>
                        </div>
                    </div>
                    
                    <?php if (empty($learnerActivityData)): ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-gray-400 text-sm">No learner activity yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($learnerActivityData as $learner): 
                                $engagementScore = round($learner['engagement_score']);
                            ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium text-gray-300"><?php echo htmlspecialchars($learner['learner_name'] ?? 'Anonymous'); ?></span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-lg font-bold text-white"><?php echo $engagementScore; ?>%</span>
                                    <span class="text-lg font-bold text-gray-400"><?php echo $learner['profile_views']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- AI Suggestion Card -->
            <?php if (!empty($highInterestLearners)): 
                $topLearner = $highInterestLearners[0];
                $initials = strtoupper(substr($topLearner['learner_name'] ?? 'L', 0, 1) . substr(explode(' ', $topLearner['learner_name'] ?? 'L')[1] ?? '', 0, 1));
                $engagementScore = round($topLearner['engagement_score']);
                
                // Format the session date properly
                $sessionDate = date('M d, Y - g:i A', strtotime($topLearner['session_datetime']));
                
                // Format expertise - remove JSON brackets and quotes
                $expertise = $topLearner['expertise'] ?? 'General Mentoring';
                if (strpos($expertise, '[') !== false) {
                     $expertiseArray = json_decode($expertise, true);
                     if (is_array($expertiseArray)) {
                         $expertise = implode(', ', array_slice($expertiseArray, 0, 2)) . (count($expertiseArray) > 2 ? '...' : '');
                     } else {
                         $expertise = str_replace(['[', ']', '"'], '', $expertise);
                         $expertise = explode(',', $expertise)[0]; 
                         $expertise = trim($expertise);
                     }
                }
            ?>
            <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <?php if (!empty($topLearner['learner_photo'])): ?>
                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($topLearner['learner_photo']); ?>" 
                             alt="<?php echo htmlspecialchars($topLearner['learner_name']); ?>" 
                             class="w-12 h-12 rounded-full object-cover border border-gray-800">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold"><?php echo $initials; ?></span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="font-semibold text-white"><?php echo htmlspecialchars($topLearner['learner_name'] ?? 'Top Learner'); ?></h3>
                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($expertise); ?></p>
                        <div class="flex items-center space-x-1 mt-1">
                            <span class="text-sm text-gray-500">📅 <?php echo $sessionDate; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-[#0e1322] border border-blue-900/40 rounded-lg p-3 mb-4">
                    <p class="text-sm font-medium text-blue-400 mb-1">AI Suggestion</p>
                    <p class="text-sm text-gray-300">Nudge <?php echo htmlspecialchars($topLearner['learner_name']); ?> with a follow-up message and further tips.</p>
                </div>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Engagement score</p>
                        <p class="text-2xl font-bold text-white"><?php echo $engagementScore; ?>%</p>
                    </div>
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" 
                       class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg text-sm font-medium hover:bg-[#00bda0] transition">
                        Next Sessions
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Default AI Suggestion Card when no learners -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-2">AI-Powered Insights</h3>
                    <p class="text-sm text-gray-400 mb-4">Complete your first session to get personalized AI suggestions and learner engagement insights.</p>
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" 
                       class="inline-flex items-center px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg text-sm font-medium hover:bg-[#00bda0] transition">
                        View Booking Requests
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>

    <!-- Getting Started Guide -->
    <div class="bg-gradient-to-r from-yellow-950/20 to-amber-950/20 border border-amber-900/40 rounded-lg p-4 sm:p-6 mb-6 sm:mb-8">
        <h2 class="text-lg sm:text-xl font-bold text-white mb-3 sm:mb-4">🚀 Getting Started as an Expert</h2>
        <div class="space-y-3">
            <!-- Step 1: Profile Setup -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $profileComplete ? 'bg-green-500' : 'bg-gray-700'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $profileComplete ? '✓' : '1'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-white">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=settings#profile" class="<?php echo $profileComplete ? 'text-green-400 hover:text-green-300' : 'text-[#00D4AA] hover:text-[#00bda0]'; ?>">
                            <?php echo $profileComplete ? 'Profile Setup Complete' : 'Complete Your Profile →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-400"><?php echo $profileComplete ? 'Great! Your profile is set up' : 'Add your expertise and details'; ?></p>
                </div>
            </div>
            
            <!-- Step 2: KYC Verification -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $kycComplete ? 'bg-green-500' : 'bg-gray-700'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $kycComplete ? '✓' : '2'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-white">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=settings#kyc" class="<?php echo $kycComplete ? 'text-green-400 hover:text-green-300' : 'text-[#00D4AA] hover:text-[#00bda0]'; ?>">
                            <?php echo $kycComplete ? 'KYC Verification Complete' : 'Complete KYC Verification →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-400"><?php echo $kycComplete ? 'You are verified to earn' : 'Verify your identity to start earning'; ?></p>
                </div>
            </div>
            
            <!-- Step 3: Set Availability -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $availabilitySet ? 'bg-green-500' : 'bg-gray-700'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $availabilitySet ? '✓' : '3'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-white">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=settings#availability" class="<?php echo $availabilitySet ? 'text-green-400 hover:text-green-300' : 'text-[#00D4AA] hover:text-[#00bda0]'; ?>">
                            <?php echo $availabilitySet ? 'Availability Set' : 'Set Your Availability →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-400"><?php echo $availabilitySet ? 'Learners can see when you\'re free' : 'Let learners know when you\'re available'; ?></p>
                </div>
            </div>
            
            <!-- Step 4: First Booking -->
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 <?php echo $firstBooking ? 'bg-green-500' : 'bg-gray-700'; ?> text-white rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?php echo $firstBooking ? '✓' : '4'; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-white">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" class="<?php echo $firstBooking ? 'text-green-400 hover:text-green-300' : 'text-[#00D4AA] hover:text-[#00bda0]'; ?>">
                            <?php echo $firstBooking ? 'First Booking Completed' : 'Accept Your First Booking →'; ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-400"><?php echo $firstBooking ? 'You\'ve started helping learners!' : 'Start helping learners achieve their goals'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-800">
            <h2 class="text-xl font-bold text-white">Recent Activity</h2>
        </div>
        <?php if (empty($recentActivity)): ?>
            <div class="p-8 text-center">
                <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-400">No recent activity</p>
                <p class="text-sm text-gray-500 mt-2">Your bookings and sessions will appear here</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-800">
                <?php foreach ($recentActivity as $activity): 
                    $statusColors = [
                        'pending' => 'bg-yellow-950/40 text-yellow-400 border border-yellow-800/40 px-2.5 py-0.5 rounded-full text-xs font-medium',
                        'confirmed' => 'bg-green-950/40 text-green-400 border border-green-800/40 px-2.5 py-0.5 rounded-full text-xs font-medium',
                        'completed' => 'bg-blue-950/40 text-blue-400 border border-blue-800/40 px-2.5 py-0.5 rounded-full text-xs font-medium',
                        'cancelled' => 'bg-red-950/40 text-red-400 border border-red-800/40 px-2.5 py-0.5 rounded-full text-xs font-medium'
                    ];
                    $statusColor = $statusColors[$activity['status']] ?? 'bg-gray-800 text-gray-400 px-2.5 py-0.5 rounded-full text-xs font-medium';
                    
                    $acceptanceStatus = ($activity['accept_booking'] === 'yes') ? 'Accepted' : 'Pending Acceptance';
                    $acceptanceColor = ($activity['accept_booking'] === 'yes') ? 'text-green-400' : 'text-yellow-400';
                    
                    $sessionDate = date('M d, Y - g:i A', strtotime($activity['session_datetime']));
                    $createdDate = date('M d, Y g:i A', strtotime($activity['created_at']));
                    $timeAgo = '';
                    $timeDiff = time() - strtotime($activity['created_at']);
                    if ($timeDiff < 3600) {
                        $timeAgo = floor($timeDiff / 60) . ' minutes ago';
                    } elseif ($timeDiff < 86400) {
                        $timeAgo = floor($timeDiff / 3600) . ' hours ago';
                    } else {
                        $timeAgo = floor($timeDiff / 86400) . ' days ago';
                    }
                ?>
                <div class="p-4 hover:bg-[#0e1322] transition">
                    <div class="flex items-start space-x-3">
                        <!-- Learner Avatar -->
                        <div class="flex-shrink-0">
                            <?php if (!empty($activity['learner_photo'])): ?>
                                <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($activity['learner_photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($activity['learner_name'] ?? 'Learner'); ?>" 
                                     class="w-10 h-10 rounded-full object-cover border border-gray-800">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($activity['learner_name'] ?? 'L', 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Activity Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-200">
                                    <?php echo htmlspecialchars($activity['learner_name'] ?? 'Unknown Learner'); ?>
                                </p>
                                <span class="text-xs text-gray-400"><?php echo $timeAgo; ?></span>
                            </div>
                            <p class="text-sm text-gray-400 mt-1">
                                Booked a session for <strong><?php echo $sessionDate; ?></strong>
                            </p>
                            <div class="flex items-center space-x-2 mt-2">
                                <span class="<?php echo $statusColor; ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                                <span class="inline-flex items-center text-xs font-medium <?php echo $acceptanceColor; ?>">
                                    • <?php echo $acceptanceStatus; ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <div class="flex-shrink-0">
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" 
                               class="text-sm text-[#00D4AA] hover:text-[#00bda0] font-medium">
                                View →
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="p-4 bg-[#0e1322] text-center border-t border-gray-800">
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" 
                   class="text-sm text-[#00D4AA] hover:text-[#00bda0] font-medium">
                    View All Bookings →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Achievement Popup Modal -->
<?php if ($showAchievementPopup && !empty($achievementData)): ?>
<div id="achievementModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="display: flex;">
    <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow-2xl max-w-sm w-full relative overflow-hidden animate-bounce-in">
        <!-- Close Button -->
        <button onclick="closeAchievementModal()" class="absolute top-2 right-2 text-gray-400 hover:text-white z-10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Confetti Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="confetti">🎉</div>
            <div class="confetti" style="left: 20%; animation-delay: 0.2s;">⭐</div>
            <div class="confetti" style="left: 40%; animation-delay: 0.4s;">✨</div>
            <div class="confetti" style="left: 60%; animation-delay: 0.6s;">🎊</div>
            <div class="confetti" style="left: 80%; animation-delay: 0.8s;">💫</div>
            <div class="confetti" style="left: 30%; animation-delay: 1s;">🌟</div>
            <div class="confetti" style="left: 70%; animation-delay: 1.2s;">✨</div>
        </div>

        <div class="p-2 relative">
            <!-- Header -->
            <div class="text-center mb-1">
                <h1 class="text-sm font-bold text-white">Congratulations! 🎊</h1>
                <p class="text-xs text-gray-400">You've unlocked a new achievement</p>
            </div>

            <!-- Main Content -->
            <div class="flex flex-col items-center gap-1 mb-1">
                <!-- Badge Icon -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 via-orange-500 to-red-500 rounded-lg shadow-lg flex items-center justify-center transform hover:scale-105 transition-transform relative">
                        <!-- Badge Shine Effect -->
                        <div class="absolute inset-0 bg-white opacity-20 rounded-lg animate-pulse"></div>
                        
                        <!-- Badge Content -->
                        <div class="text-center text-white relative z-10">
                            <div class="text-lg">
                                <?php 
                                $badgeEmojis = [
                                    10 => '🌟',
                                    20 => '🏆',
                                    50 => '👑',
                                    100 => '🎖️'
                                ];
                                echo $badgeEmojis[$achievementData['milestone']] ?? '🏅';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                <div class="flex-1 text-center">
                    <h2 class="text-xs font-bold text-white"><?php echo htmlspecialchars($achievementData['badge_name']); ?></h2>
                    <p class="text-xs text-gray-400 mb-0.5"><?php echo $achievementData['date']; ?></p>
                    
                    <div class="flex items-center justify-center gap-0.5 mb-0.5">
                        <span class="text-yellow-500 text-xs">⭐</span>
                        <span class="text-sm font-bold text-white"><?php echo $achievementData['rating']; ?></span>
                        <span class="text-xs text-gray-400">/5</span>
                    </div>

                    <p class="text-xs text-gray-400 mb-1">Based on last <?php echo $achievementData['sessions_completed']; ?> sessions</p>

                    <!-- AI Comments -->
                    <div class="bg-blue-950/40 border border-blue-900/40 rounded p-1 mb-1">
                        <p class="text-xs font-semibold text-blue-300">AI comments:</p>
                        <ul class="text-xs text-blue-200">
                            <li>• Clear explanations!</li>
                            <li>• Actionable roadmap.</li>
                        </ul>
                    </div>

                    <!-- Learner Names -->
                    <?php if (!empty($achievementData['learner_names'])): ?>
                    <div class="flex flex-wrap justify-center gap-0.5 mb-0.5">
                        <?php foreach (array_slice($achievementData['learner_names'], 0, 3) as $learnerName): ?>
                            <span class="flex items-center px-1 py-0.5 bg-green-950/40 text-green-400 border border-green-800/40 rounded-full text-xs">
                                <?php echo htmlspecialchars($learnerName); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Growth Insights -->
                    <div class="grid grid-cols-2 gap-1">
                        <div class="text-center p-0.5 bg-[#0e1322] border border-gray-800 rounded">
                            <p class="text-xs text-gray-400">Sessions</p>
                            <p class="text-xs font-bold text-white"><?php echo $achievementData['sessions_completed']; ?></p>
                        </div>
                        <div class="text-center p-0.5 bg-[#0e1322] border border-gray-800 rounded">
                            <p class="text-xs text-gray-400">Returning</p>
                            <p class="text-sm font-bold text-white"><?php echo $achievementData['returning_learners']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievement Reasons -->
            <div class="bg-gradient-to-r from-green-950/20 to-blue-950/20 border border-gray-800 rounded p-1 mb-1">
                <h3 class="font-bold text-white text-xs">Unlocked:</h3>
                <div class="space-y-0.5">
                    <div class="flex items-center">
                        <span class="text-xs text-gray-300">✓ <?php echo $achievementData['milestone']; ?>+ sessions</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-xs text-gray-300">✓ > <?php echo $achievementData['rating']; ?> rating</span>
                    </div>
                    <?php if ($achievementData['next_milestone']): ?>
                    <div class="flex items-center">
                        <span class="text-xs text-gray-300">→ Next: <?php echo $achievementData['next_milestone']; ?> sessions</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- LinkedIn Share Message -->
            <div class="bg-gradient-to-r from-blue-950/20 to-indigo-950/20 border border-gray-800 rounded p-1.5 mb-1.5">
                <p class="text-xs font-semibold text-gray-200 mb-0.5">Share: I've unlocked <?php echo htmlspecialchars($achievementData['badge_name']); ?> on Nexpert! 🎉</p>
                <p class="text-xs text-gray-400">
                    ⭐ <?php echo $achievementData['rating']; ?>/5 • 'Clear, actionable guidance!'
                </p>
            </div>

            <!-- Share Buttons -->
            <div class="flex items-center justify-between gap-1 mb-1.5">
                <div class="flex gap-1">
                    <!-- LinkedIn Button -->
                    <button onclick="shareOnLinkedIn()" class="w-7 h-7 bg-blue-600 text-white rounded flex items-center justify-center hover:bg-blue-700 transition" title="LinkedIn">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </button>
                    
                    <!-- Twitter Button -->
                    <button onclick="shareOnTwitter()" class="w-7 h-7 bg-sky-500 text-white rounded flex items-center justify-center hover:bg-sky-600 transition" title="Twitter">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </button>
                    
                    <!-- Discord Button -->
                    <button onclick="shareOnDiscord()" class="w-7 h-7 bg-indigo-600 text-white rounded flex items-center justify-center hover:bg-indigo-700 transition" title="Discord">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                    </button>
                    
                    <!-- Bookmark Button -->
                    <button onclick="bookmarkAchievement()" class="w-7 h-7 bg-gray-800 text-gray-300 rounded flex items-center justify-center hover:bg-gray-700 transition" title="Bookmark">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Copy Post Button -->
                <button onclick="copyPostToClipboard()" class="flex items-center gap-0.5 px-1.5 py-1 bg-gray-800 text-gray-300 rounded text-xs hover:bg-gray-700 transition border border-gray-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Copy
                </button>
            </div>

            <!-- Main Share LinkedIn Button -->
            <button onclick="shareOnLinkedIn()" class="w-full py-1.5 bg-[#00D4AA] text-[#080B10] rounded font-semibold text-xs hover:bg-[#00bda0] transition">
                Share on LinkedIn
            </button>
        </div>
    </div>
</div>

<style>
@keyframes bounce-in {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fall {
    to {
        transform: translateY(100vh) rotate(360deg);
    }
}

.animate-bounce-in {
    animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.confetti {
    position: absolute;
    top: -10%;
    font-size: 2rem;
    animation: fall 3s linear infinite;
}
</style>

<script>
// Achievement Modal Functions
function closeAchievementModal() {
    document.getElementById('achievementModal').style.display = 'none';
}

function shareOnLinkedIn() {
    const achievementText = `Thrilled to share that I've unlocked a new achievement on Nexpert! 🎉
    
🏅 Badge: <?php echo isset($achievementData['badge_name']) ? htmlspecialchars($achievementData['badge_name']) : ''; ?>
⭐ Rating: <?php echo isset($achievementData['rating']) ? $achievementData['rating'] : ''; ?>/5
💬 What students say: 'Clear, actionable guidance. Highly recommend!'

#ExpertGrowth #Nexpert #OnlineLearning`;
    
    const linkedInUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}`;
    window.open(linkedInUrl, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const achievementText = `Just unlocked a new achievement on Nexpert! 🎉 Badge: <?php echo isset($achievementData['badge_name']) ? htmlspecialchars($achievementData['badge_name']) : ''; ?> ⭐ Rating: <?php echo isset($achievementData['rating']) ? $achievementData['rating'] : ''; ?>/5`;
    const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(achievementText)}`;
    window.open(twitterUrl, '_blank', 'width=600,height=400');
}

function shareOnDiscord() {
    alert('Discord sharing: Copy the achievement message and share in your Discord server! 🎮');
    copyPostToClipboard();
}

function bookmarkAchievement() {
    alert('Achievement bookmarked! ⭐');
    // You can add actual bookmark functionality here
}

function copyPostToClipboard() {
    const achievementText = `Thrilled to share that I've unlocked a new achievement on Nexpert! 🎉

🏅 Badge: <?php echo isset($achievementData['badge_name']) ? htmlspecialchars($achievementData['badge_name']) : ''; ?>
⭐ Rating: <?php echo isset($achievementData['rating']) ? $achievementData['rating'] : ''; ?>/5
💬 What students say: 'Clear, actionable guidance. Highly recommend!'

#ExpertGrowth #Nexpert #OnlineLearning`;
    
    navigator.clipboard.writeText(achievementText).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg> Copied!';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        alert('Failed to copy. Please try again.');
    });
}

// Auto-close modal after 30 seconds (optional)
setTimeout(() => {
    const modal = document.getElementById('achievementModal');
    if (modal && modal.style.display !== 'none') {
        closeAchievementModal();
    }
}, 30000);
</script>
<?php endif; ?>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        // If it's a full URL or a data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        
        // If no image path, use a default
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        
        // Remove leading slashes
        const normalizedPath = imagePath.replace(/^\/+/, '');
        
        // Construct full path
        return `${window.BASE_PATH}/${normalizedPath}`;
    }

    // Send follow-up email function
    function sendFollowUpEmail(learnerId, learnerName) {
        console.log('Sending follow-up email to learner:', learnerId, learnerName);
        
        if (!confirm(`Send follow-up email to ${learnerName}?`)) {
            return;
        }

        const button = event.target;
        button.disabled = true;
        button.textContent = 'Sending...';

        const url = `${window.BASE_PATH}/expert/send-followup-email.php`;
        console.log('Fetch URL:', url);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                learner_id: learnerId
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            if (data.success) {
                alert('Follow-up message sent successfully to ' + learnerName + '!');
                button.textContent = 'Email Sent ✓';
                button.classList.remove('bg-[#00D4AA]', 'text-[#080B10]', 'hover:bg-[#00bda0]');
                button.classList.add('bg-green-600', 'text-white');
                
                // Show debug info if available
                if (data.debug) {
                    console.log('Email details:', data.debug);
                }
                if (!data.email_sent) {
                    console.warn('Email not sent via mail(), but notification logged:', data.email_error);
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to send email'));
                button.disabled = false;
                button.textContent = 'Send Follow-up';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to send follow-up email: ' + error.message);
            button.disabled = false;
            button.textContent = 'Send Follow-up';
        });
    }
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
