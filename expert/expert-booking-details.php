<?php
// Central session + config (defines BASE_PATH / BASE_URL and starts session)
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$userId = $_SESSION['user_id'];
$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=learner-management');
    exit;
}

// Get booking details with learner profile data
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        u.email as learner_email,
        u.phone as learner_phone,
        lp.full_name as learner_name,
        lp.profile_photo as learner_photo,
        lp.goals as learner_goals,
        lp.challenges as learner_challenges,
        lp.education as learner_education,
        lp.profession as learner_profession,
        b.ai_insights,
        b.created_at as booking_created_at
    FROM bookings b
    JOIN users u ON b.learner_id = u.id
    JOIN learner_profiles lp ON u.id = lp.user_id
    WHERE b.id = ? AND b.expert_id = ?
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=learner-management');
    exit;
}

// Parse JSON fields if they exist
$callDetails = !empty($booking['join_link']) ? json_decode($booking['join_link'], true) : null;
$sessionTasks = !empty($booking['session_tasks']) ? json_decode($booking['session_tasks'], true) : [];
$sessionResources = !empty($booking['session_resources']) ? json_decode($booking['session_resources'], true) : [];

$page_title = "Booking Details - Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>

<?php
// Check if meeting is in future
$sessionDateTime = new DateTime($booking['session_datetime']);
$now = new DateTime();
$isFutureMeeting = $sessionDateTime > $now;

// Format session time display - showing when booking was created
$sessionDisplay = 'Booked on ' . date('M j, Y \a\t g:i A', strtotime($booking['booking_created_at']));

// Check for learner data
$hasLearnerData = !empty($booking['learner_goals']) || !empty($booking['learner_challenges']) || 
                 !empty($booking['learner_education']) || !empty($booking['learner_profession']);
?>

<div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Main Container with Border -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            
            <?php if ($booking['reschedule_requested']): ?>
            <!-- Reschedule Alert Banner -->
            <div class="mb-8 p-4 rounded-2xl border <?php echo $booking['reschedule_requested_by'] === 'expert' ? 'bg-indigo-950/40 border-indigo-800 text-indigo-200' : 'bg-amber-950/40 border-amber-800 text-amber-200'; ?> flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-bold text-xs sm:text-sm">Reschedule Requested</p>
                        <p class="text-xs text-gray-300">
                            <?php if ($booking['reschedule_requested_by'] === 'expert'): ?>
                                You proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>. Waiting for learner approval.
                            <?php else: ?>
                                The learner proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>.
                            <?php endif; ?>
                        </p>
                        <?php if ($booking['reschedule_reason']): ?>
                            <p class="text-[11px] mt-1 italic text-gray-400">Reason: "<?php echo htmlspecialchars($booking['reschedule_reason']); ?>"</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($booking['reschedule_requested_by'] === 'learner'): ?>
                <div class="flex gap-2">
                    <button onclick="rescheduleAction('approve')" class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-xl text-xs font-extrabold shadow-sm hover:bg-[#00bfa0] transition">Approve</button>
                    <button onclick="rescheduleAction('reject')" class="px-4 py-2 bg-[#080B10] border border-amber-500/40 text-amber-300 rounded-xl text-xs font-bold hover:bg-amber-500/10 transition">Decline</button>
                </div>
                <?php else: ?>
                    <button onclick="rescheduleAction('reject')" class="text-xs font-bold text-[#00D4AA] hover:underline">Cancel Request</button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php
            $isAccepted = in_array(strtolower((string)($booking['accept_booking'] ?? 'no')), ['yes', '1', 'true'], true);
            ?>

            <?php if (!$isAccepted && in_array($booking['status'], ['pending', 'confirmed'])): ?>
            <!-- Accept Booking Action Banner -->
            <div class="mb-8 p-5 rounded-2xl border bg-amber-500/10 border-amber-500/30 text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm text-white">Booking Awaiting Your Acceptance</p>
                        <p class="text-xs text-gray-300">Accept this session to generate your Zoom meeting room and send automated invites to the learner.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                    <button onclick="handleBookingAction(<?php echo $bookingId; ?>, 'accept')" class="flex-1 sm:flex-none px-5 py-2.5 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] rounded-xl text-xs font-extrabold shadow-[0_0_15px_rgba(0,212,170,0.3)] transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Accept Booking
                    </button>
                    <button onclick="handleBookingAction(<?php echo $bookingId; ?>, 'reject')" class="px-4 py-2.5 bg-[#080B10] border border-red-500/30 text-red-400 hover:bg-red-500/10 rounded-xl text-xs font-bold transition">
                        Decline
                    </button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Header Row -->
            <div class="flex items-start justify-between mb-8 pb-6 border-b border-gray-800">
                <div class="flex items-center gap-4">
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=learner-management" class="text-gray-400 hover:text-white transition-colors p-2 bg-[#080B10] border border-gray-800 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-extrabold text-white">Booking Details</h1>
                        <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo $sessionDisplay; ?> (<?php echo $booking['duration_minutes']; ?> min)</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-[#00D4AA]/10 text-[#00D4AA] rounded-full text-xs font-bold border border-[#00D4AA]/30">
                    Sovereign Telemetry
                </span>
            </div>

            <!-- Two Column Layout -->
            <div class="grid lg:grid-cols-2 gap-8">
                
                <!-- LEFT COLUMN -->
                <div class="space-y-6">
                    
                    <!-- Attendee Card -->
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Attendee Information</h3>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-gray-800 border border-gray-700 overflow-hidden shrink-0">
                                <?php if (!empty($booking['learner_photo'])): ?>
                                    <img src="<?php echo BASE_PATH . '/' . ltrim($booking['learner_photo'], '/'); ?>" 
                                         alt="<?php echo htmlspecialchars($booking['learner_name']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-[#00D4AA]/20 text-[#00D4AA] text-sm font-black">
                                        <?php echo getInitials($booking['learner_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm"><?php echo htmlspecialchars($booking['learner_name']); ?></h4>
                                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=messages&learner_id=<?php echo urlencode($booking['learner_id']); ?>" 
                                   class="text-xs text-[#00D4AA] hover:underline font-bold flex items-center gap-1 mt-0.5">
                                    Send Direct Message 
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Description / Goals -->
                        <div class="mb-4 pt-4 border-t border-gray-800">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Focus & Goals</p>
                            <p class="text-xs text-gray-300 leading-relaxed">
                                <?php if (!empty($booking['learner_goals'])): ?>
                                    <?php echo htmlspecialchars($booking['learner_goals']); ?>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">No specific goals specified</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <!-- Session Notes -->
                        <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-white">Advisory Notes</p>
                                <p class="text-[11px] text-gray-400">Previous notes and actionable deliverables</p>
                            </div>
                            <button id="edit-summary-btn" class="px-3.5 py-1.5 bg-[#0D131F] border border-gray-700 rounded-xl text-xs font-bold hover:border-[#00D4AA] text-gray-200 hover:text-white transition">
                                <?php echo !empty($booking['session_summary']) ? 'Edit Notes' : 'Add Notes'; ?>
                            </button>
                        </div>
                        
                        <!-- Summary Display -->
                        <div id="summary-display" class="mt-3">
                            <?php if (!empty($booking['session_summary'])): ?>
                                <p class="text-xs text-gray-100 whitespace-pre-line leading-relaxed bg-[#0D131F] p-3.5 rounded-xl border border-gray-800 font-medium"><?php echo htmlspecialchars($booking['session_summary']); ?></p>
                            <?php else: ?>
                                <p class="text-xs text-gray-500 italic">No session summary added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Red Flags / Important Notes Card -->
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Learner Context & Challenges</h3>
                        <div class="space-y-3">
                            <?php if (!empty($booking['learner_challenges'])): ?>
                            <div>
                                <p class="text-xs text-gray-300 leading-relaxed"><?php echo nl2br(htmlspecialchars($booking['learner_challenges'])); ?></p>
                            </div>
                            <?php else: ?>
                            <p class="text-xs text-gray-500 italic">No specific challenges highlighted</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Zoom Meeting Details -->
                    <?php if ($callDetails): ?>
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Meeting Telemetry</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">Date & Time</span>
                                <span class="text-xs font-mono font-bold text-white"><?php echo date('M j, Y @ g:i A', strtotime($booking['session_datetime'])); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">Duration</span>
                                <span class="text-xs font-mono font-bold text-white"><?php echo $booking['duration_minutes']; ?> minutes</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">Meeting ID</span>
                                <span class="text-xs font-mono text-[#00D4AA]"><?php echo htmlspecialchars($callDetails['meeting_id'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">Passcode</span>
                                <span class="text-xs font-mono text-gray-300"><?php echo htmlspecialchars($callDetails['password'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="pt-3 border-t border-gray-800 flex flex-wrap gap-2.5">
                                <a href="<?php echo htmlspecialchars($callDetails['start_url'] ?? '#'); ?>" 
                                   target="_blank"
                                   class="flex-1 min-w-[120px] text-center bg-[#00D4AA] text-[#080B10] py-2.5 px-4 rounded-xl text-xs font-extrabold hover:bg-[#00bfa0] transition shadow-md flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Start Session
                                </a>
                                <?php if ($booking['status'] === 'confirmed' && $isAccepted): ?>
                                <button onclick="handleBookingAction(<?php echo $bookingId; ?>, 'complete')" 
                                        class="px-3.5 py-2.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Mark Completed
                                </button>
                                <?php endif; ?>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($callDetails['join_url'] ?? ''); ?>')" 
                                        class="px-3.5 py-2.5 bg-[#0D131F] border border-gray-700 rounded-xl text-xs font-bold hover:border-gray-500 text-gray-300 hover:text-white transition">
                                    Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Session Status -->
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Session Status</h3>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <?php
                                $statusBadge = [
                                    'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/25',
                                    'confirmed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25',
                                    'completed' => 'bg-blue-500/10 text-blue-400 border-blue-500/25',
                                    'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/25'
                                ];
                                $badge = $statusBadge[$booking['status']] ?? 'bg-gray-800 text-gray-300 border-gray-700';
                                ?>
                                <span class="px-3 py-1 <?php echo $badge; ?> rounded-full text-xs font-mono font-bold border capitalize">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                                <?php if ($isAccepted): ?>
                                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full text-xs font-bold">Accepted</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full text-xs font-bold">Pending Acceptance</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <?php if (!$isAccepted && in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                    <button onclick="handleBookingAction(<?php echo $bookingId; ?>, 'accept')" class="px-3.5 py-1.5 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        Accept Booking
                                    </button>
                                <?php elseif ($isAccepted && $booking['status'] === 'confirmed'): ?>
                                    <button onclick="handleBookingAction(<?php echo $bookingId; ?>, 'complete')" class="px-3.5 py-1.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Mark Completed
                                    </button>
                                <?php endif; ?>
                                <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                <button onclick="openRescheduleModal()" class="text-xs font-bold text-[#00D4AA] hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Reschedule
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- RIGHT COLUMN - Pre-Session Briefing (AI-Generated) -->
                <div class="space-y-6">
                    
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-sm font-extrabold text-white">Pre-Session Briefing <span class="text-xs font-normal text-gray-400">(Telemetry-Assisted)</span></h3>
                            <?php if ($hasLearnerData): ?>
                            <button id="refresh-insights-btn" onclick="generateLearnerInsights(true)" 
                                    class="hidden p-2 text-gray-400 hover:text-white transition" title="Refresh">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($hasLearnerData): ?>
                        
                        <!-- Loading State -->
                        <div id="insights-loading" class="text-center py-8">
                            <svg class="animate-spin h-6 w-6 text-[#00D4AA] mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-400 text-xs">Analyzing learner telemetry & goals...</p>
                        </div>
                        
                        <!-- AI Insights Content -->
                        <div id="insights-content" class="hidden space-y-4">
                            <div id="ai-insights-container" class="space-y-4">
                                <!-- AI insights will be loaded here -->
                            </div>
                        </div>
                        
                        <script>
                        // Store cached insights from PHP
                        window.cachedInsights = <?php echo !empty($booking['ai_insights']) ? $booking['ai_insights'] : 'null'; ?>;
                        </script>
                        
                        <?php else: ?>
                        <div class="space-y-4">
                            <div class="border border-gray-800 rounded-xl p-4 bg-[#0D131F]">
                                <p class="text-xs text-gray-400">No learner profile data available for briefing.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Optional Attachments / Resources -->
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-xs font-bold text-white">Attachments & Resources</h3>
                                <p class="text-[11px] text-gray-400">Shared advisory deliverables</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="add-resource-btn" class="p-2 border border-gray-700 bg-[#0D131F] rounded-xl hover:border-[#00D4AA] text-gray-300 hover:text-white transition" title="Add Resource">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="resources-container" class="space-y-2">
                            <?php if (count($sessionResources) > 0): ?>
                                <?php foreach ($sessionResources as $resource): ?>
                                <div class="flex items-center gap-3 p-3 bg-[#0D131F] border border-gray-800 rounded-xl resource-item" data-resource-id="<?php echo htmlspecialchars($resource['id'] ?? ''); ?>">
                                    <svg class="w-4 h-4 text-[#00D4AA] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white truncate"><?php echo htmlspecialchars($resource['title'] ?? 'Resource'); ?></p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <?php if (!empty($resource['url'])): ?>
                                        <a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank" class="p-1 text-gray-400 hover:text-[#00D4AA]">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                        <?php endif; ?>
                                        <button onclick="deleteResource('<?php echo htmlspecialchars($resource['id'] ?? ''); ?>')" class="p-1 text-gray-400 hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">No attachments shared yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tasks Card -->
                    <div class="border border-gray-800 bg-[#080B10] rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xs font-bold text-white">Actionable Tasks</h3>
                            <button id="add-task-btn" class="p-2 border border-gray-700 bg-[#0D131F] rounded-xl hover:border-[#00D4AA] text-gray-300 hover:text-white transition" title="Add Task">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="tasks-container" class="space-y-2">
                            <?php if (count($sessionTasks) > 0): ?>
                                <?php foreach ($sessionTasks as $task): ?>
                                <div class="flex items-center gap-3 p-2.5 bg-[#0D131F] rounded-xl border border-gray-800 task-item" data-task-id="<?php echo htmlspecialchars($task['id'] ?? ''); ?>">
                                    <input type="checkbox" 
                                           <?php echo ($task['completed'] ?? false) ? 'checked' : ''; ?> 
                                           onclick="toggleTask('<?php echo htmlspecialchars($task['id'] ?? ''); ?>')"
                                           class="w-4 h-4 text-[#00D4AA] rounded bg-[#080B10] border-gray-700 focus:ring-[#00D4AA] cursor-pointer">
                                    <span class="flex-1 text-xs <?php echo ($task['completed'] ?? false) ? 'line-through text-gray-500' : 'text-gray-200'; ?>">
                                        <?php echo htmlspecialchars($task['title'] ?? ''); ?>
                                    </span>
                                    <button onclick="deleteTask('<?php echo htmlspecialchars($task['id'] ?? ''); ?>')" class="p-1 text-gray-400 hover:text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">No action tasks assigned yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div id="summary-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-800">
            <h3 class="text-lg font-extrabold text-white">Edit Session Summary</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Summary</label>
                <textarea id="summary-text" rows="6" 
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs"
                    placeholder="Write your session summary..."><?php echo htmlspecialchars($booking['session_summary'] ?? ''); ?></textarea>
            </div>
            <div class="flex items-center gap-2">
                <button id="enhance-ai-btn" class="px-4 py-2 bg-[#00D4AA] text-[#080B10] font-extrabold text-xs rounded-xl hover:bg-[#00bfa0] transition flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Enhance with Telemetry
                </button>
                <span id="ai-loader" class="hidden text-[#00D4AA] flex items-center gap-2 text-xs font-mono">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enhancing...
                </span>
            </div>
        </div>
        <div class="p-6 border-t border-gray-800 flex justify-end gap-3">
            <button onclick="closeSummaryModal()" class="px-5 py-2.5 bg-[#080B10] border border-gray-700 text-gray-300 rounded-xl hover:text-white transition font-bold text-xs">Cancel</button>
            <button onclick="saveSummary()" class="px-5 py-2.5 bg-[#00D4AA] text-[#080B10] rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs shadow-md">Save Summary</button>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div id="task-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-800">
            <h3 class="text-lg font-extrabold text-white">Add Action Tasks</h3>
            <p class="text-xs text-gray-400 mt-1">Add multiple action deliverables (one per line)</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tasks * (One per line)</label>
                <textarea id="task-titles" rows="8"
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] font-mono text-xs"
                    placeholder="Review architecture design document&#10;Complete benchmark profiling&#10;Submit final outcome review"></textarea>
                <p class="text-[11px] text-gray-500 mt-1">Each line will create a separate task</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-800 flex justify-between items-center">
            <span id="task-count-preview" class="text-xs text-gray-400 font-mono"></span>
            <div class="flex gap-3">
                <button onclick="closeTaskModal()" class="px-5 py-2.5 bg-[#080B10] border border-gray-700 text-gray-300 rounded-xl hover:text-white transition font-bold text-xs">Cancel</button>
                <button onclick="saveTasks()" id="save-tasks-btn" class="px-5 py-2.5 bg-[#00D4AA] text-[#080B10] rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs shadow-md">Add Tasks</button>
            </div>
        </div>
    </div>
</div>

<!-- Resource Modal -->
<div id="resource-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-2xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-800">
            <h3 class="text-lg font-extrabold text-white">Add Shared Resource</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Resource Title *</label>
                <input type="text" id="resource-title" 
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs"
                    placeholder="e.g., System Architecture Guide PDF">
            </div>
            
            <div class="border-t border-gray-800 pt-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Resource Type:</p>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="resource-type" value="url" checked 
                            onclick="toggleResourceType('url')"
                            class="w-4 h-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700">
                        <span class="text-xs text-gray-300 font-bold">Web Link (URL)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="resource-type" value="file" 
                            onclick="toggleResourceType('file')"
                            class="w-4 h-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700">
                        <span class="text-xs text-gray-300 font-bold">Upload File (PDF, DOC, PPT, etc.)</span>
                    </label>
                </div>
            </div>
            
            <div id="url-input-section">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">URL</label>
                <input type="url" id="resource-url" 
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs font-mono"
                    placeholder="https://example.com/resource.pdf">
            </div>
            
            <div id="file-input-section" class="hidden">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">File (Max 10MB)</label>
                <input type="file" id="resource-file" 
                    accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.zip"
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
                <p class="text-[11px] text-gray-500 mt-1">Allowed: PDF, DOC, PPT, XLS, images, ZIP</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-800 flex justify-end gap-3">
            <button onclick="closeResourceModal()" class="px-5 py-2.5 bg-[#080B10] border border-gray-700 text-gray-300 rounded-xl hover:text-white transition font-bold text-xs">Cancel</button>
            <button onclick="saveResource()" id="save-resource-btn" class="px-5 py-2.5 bg-[#00D4AA] text-[#080B10] rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs shadow-md">Add Resource</button>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-gray-800 bg-[#080B10]">
            <h3 class="text-lg font-extrabold text-white">Request Reschedule</h3>
            <p class="text-xs text-gray-400 mt-1">Propose an alternate session window</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">New Date & Time</label>
                <input type="datetime-local" id="reschedule-datetime" 
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Reason (Optional)</label>
                <textarea id="reschedule-reason" rows="3" 
                    class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs"
                    placeholder="Briefly explain the schedule adjustment..."></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-800 flex justify-end gap-3">
            <button onclick="closeRescheduleModal()" class="px-5 py-2.5 bg-[#080B10] border border-gray-700 text-gray-300 rounded-xl hover:text-white transition font-bold text-xs">Cancel</button>
            <button onclick="submitRescheduleRequest()" class="px-5 py-2.5 bg-[#00D4AA] text-[#080B10] rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs shadow-md">Send Request</button>
        </div>
    </div>
</div>

<script>
function openRescheduleModal() {
    document.getElementById('reschedule-modal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('reschedule-modal').classList.add('hidden');
}

async function submitRescheduleRequest() {
    const datetime = document.getElementById('reschedule-datetime').value;
    const reason = document.getElementById('reschedule-reason').value;
    if (!datetime) {
        Swal.fire({
            icon: 'warning',
            title: 'Select Date & Time',
            text: 'Please select a new date and time for the session.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        return;
    }
    
    Swal.fire({
        title: 'Sending Request...',
        html: '<div class="flex flex-col items-center py-3"><div class="w-10 h-10 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mb-3"></div><p class="text-xs text-gray-400">Notifying learner...</p></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });

    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/common/reschedule-actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'request_reschedule',
                booking_id: bookingId,
                new_datetime: datetime,
                reason: reason
            })
        });
        const data = await response.json();
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Request Sent',
                text: 'Reschedule request sent to learner.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Request Failed',
                text: data.message || 'Failed to send reschedule request',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to send reschedule request.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

async function rescheduleAction(action) {
    const actionLabel = action === 'approve' ? 'approve' : 'decline';
    const confirmResult = await Swal.fire({
        title: `${action === 'approve' ? 'Approve' : 'Decline'} Reschedule Request?`,
        text: `Are you sure you want to ${actionLabel} this reschedule request?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'approve' ? '#00D4AA' : '#EF4444',
        cancelButtonColor: '#374151',
        confirmButtonText: `Yes, ${action === 'approve' ? 'Approve' : 'Decline'}`,
        cancelButtonText: 'Cancel',
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });

    if (!confirmResult.isConfirmed) return;

    Swal.fire({
        title: 'Processing...',
        html: '<div class="flex flex-col items-center py-3"><div class="w-10 h-10 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mb-3"></div><p class="text-xs text-gray-400">Updating booking schedule...</p></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });

    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/common/reschedule-actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: action === 'approve' ? 'approve_reschedule' : 'reject_reschedule',
                booking_id: bookingId
            })
        });
        const data = await response.json();
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Schedule Updated',
                text: 'Reschedule request has been processed.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
            location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: data.message || 'Failed to process request',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to process reschedule request.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

async function handleBookingAction(bookingId, action) {
    if (action === 'accept') {
        const confirmResult = await Swal.fire({
            title: 'Accept Booking Request?',
            html: '<p class="text-gray-300 text-sm">Accepting will generate your secure Zoom video room and automatically send meeting invitations to you and the learner.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00D4AA',
            cancelButtonColor: '#374151',
            confirmButtonText: '✓ Yes, Accept & Create Zoom',
            cancelButtonText: 'Cancel',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        
        if (!confirmResult.isConfirmed) return;

        Swal.fire({
            title: 'Creating Zoom Meeting...',
            html: '<div class="flex flex-col items-center py-3"><div class="w-10 h-10 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mb-3"></div><p class="text-xs text-gray-400">Generating video room & sending email invitations...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    } else if (action === 'complete') {
        const confirmResult = await Swal.fire({
            title: 'Mark Session as Completed?',
            html: '<p class="text-gray-300 text-sm">This will complete the session lifecycle, update your stats, and invite the learner to leave a rating and review.</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00D4AA',
            cancelButtonColor: '#374151',
            confirmButtonText: '✓ Yes, Mark Completed',
            cancelButtonText: 'Cancel',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        
        if (!confirmResult.isConfirmed) return;

        Swal.fire({
            title: 'Finalizing Session...',
            html: '<div class="flex flex-col items-center py-3"><div class="w-10 h-10 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mb-3"></div><p class="text-xs text-gray-400">Updating booking lifecycle & stats...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    } else {
        const confirmResult = await Swal.fire({
            title: 'Decline Booking Request?',
            text: 'Are you sure you want to decline this booking request?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#374151',
            confirmButtonText: 'Decline Booking',
            cancelButtonText: 'Keep Booking',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        
        if (!confirmResult.isConfirmed) return;

        Swal.fire({
            title: 'Processing...',
            html: '<div class="flex flex-col items-center py-3"><div class="w-10 h-10 border-4 border-gray-800 border-t-red-500 rounded-full animate-spin mb-3"></div><p class="text-xs text-gray-400">Updating booking status...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
    
    try {
        const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/booking-action.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                booking_id: bookingId,
                action: action
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (action === 'accept') {
                const zoomLink = result.zoom_link ? `<div class="mt-3 p-3 bg-[#080B10] rounded-xl border border-gray-800 text-left"><p class="text-xs font-semibold text-gray-400 mb-1">Session Meeting Link:</p><a href="${result.zoom_link}" target="_blank" class="text-[#00D4AA] text-xs font-mono break-all hover:underline">${result.zoom_link}</a></div>` : '';
                await Swal.fire({
                    icon: 'success',
                    title: '🎉 Booking Confirmed!',
                    html: `
                        <p class="text-gray-300 text-sm mb-2">${result.message || 'Booking accepted successfully!'}</p>
                        ${zoomLink}
                        <p class="text-[11px] text-gray-400 mt-3">Meeting invitations and join links have been sent to you and the learner.</p>
                    `,
                    confirmButtonText: 'Great, Done!',
                    confirmButtonColor: '#00D4AA',
                    background: '#0D131F',
                    color: '#fff',
                    customClass: { popup: 'border border-gray-800 rounded-2xl' }
                });
            } else if (action === 'complete') {
                await Swal.fire({
                    icon: 'success',
                    title: '🎉 Session Completed!',
                    text: result.message || 'Session marked as completed successfully!',
                    confirmButtonColor: '#00D4AA',
                    background: '#0D131F',
                    color: '#fff',
                    customClass: { popup: 'border border-gray-800 rounded-2xl' }
                });
            } else {
                await Swal.fire({
                    icon: 'info',
                    title: 'Booking Declined',
                    text: result.message || 'The booking request was rejected.',
                    confirmButtonColor: '#374151',
                    background: '#0D131F',
                    color: '#fff',
                    customClass: { popup: 'border border-gray-800 rounded-2xl' }
                });
            }
            location.reload();
        } else {
            let errorMessage = result.error || 'Failed to update booking.';
            if (result.details) errorMessage += ' (' + result.details + ')';
            if (result.zoom_error) errorMessage += ' Zoom: ' + result.zoom_error;

            Swal.fire({
                icon: 'error',
                title: 'Action Failed',
                text: errorMessage,
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'An error occurred while connecting to the server. Please try again.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

window.BASE_PATH = '<?php echo BASE_PATH; ?>';
const bookingId = <?php echo $bookingId; ?>;

// AI Learner Insights Function
async function generateLearnerInsights(forceRefresh = false) {
    const btn = document.getElementById('refresh-insights-btn');
    const loading = document.getElementById('insights-loading');
    const container = document.getElementById('ai-insights-container');
    const profileContent = document.getElementById('insights-content');
    
    // Check if we have cached insights and not forcing refresh
    if (!forceRefresh && window.cachedInsights) {
        displayInsights(window.cachedInsights);
        loading.classList.add('hidden');
        profileContent.classList.remove('hidden');
        if (btn) btn.classList.remove('hidden');
        return;
    }
    
    if (btn) btn.classList.add('hidden');
    loading.classList.remove('hidden');
    profileContent.classList.add('hidden');
    container.innerHTML = '';
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'generate_learner_insights',
                booking_id: bookingId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.cachedInsights = data.insights;
            displayInsights(data.insights);
            profileContent.classList.remove('hidden');
            if (btn) btn.classList.remove('hidden');
        } else {
            container.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-700 text-sm">${escapeHtml(data.message)}</p>
                </div>
            `;
            profileContent.classList.remove('hidden');
            if (btn) btn.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-red-700 text-sm">Failed to generate insights. Please try again.</p>
            </div>
        `;
        profileContent.classList.remove('hidden');
        if (btn) btn.classList.remove('hidden');
    } finally {
        loading.classList.add('hidden');
    }
}

function displayInsights(insights) {
    const container = document.getElementById('ai-insights-container');
    container.innerHTML = `
        <div class="border border-gray-800 rounded-2xl p-5 bg-[#080B10] shadow-sm">
            <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-2.5 text-[#00D4AA] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Learner Overview
            </h4>
            <p class="text-xs text-gray-200 leading-relaxed">${escapeHtml(insights.overview)}</p>
        </div>
        
        <div class="border border-gray-800 rounded-2xl p-5 bg-[#080B10] shadow-sm">
            <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-2.5 text-[#00D4AA] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Session Goal Summary
            </h4>
            <p class="text-xs text-gray-200 leading-relaxed">${escapeHtml(insights.session_goals)}</p>
        </div>
        
        <div class="border border-gray-800 rounded-2xl p-5 bg-[#080B10] shadow-sm">
            <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-2.5 text-[#00D4AA] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Recommended Approach for Expert
            </h4>
            <div class="text-xs space-y-2">${formatApproach(insights.recommended_approach)}</div>
        </div>
    `;
}

// Auto-generate insights on page load
document.addEventListener('DOMContentLoaded', function() {
    const insightsCard = document.getElementById('ai-insights-container');
    if (insightsCard) {
        generateLearnerInsights();
    }
});

function formatApproach(text) {
    if (!text) return '';
    let raw = text.toString().trim();
    
    // Check if it has numbered list or standard bullet markers
    let points = [];
    if (/\d+[\.\)]\s+/.test(raw)) {
        points = raw.split(/\s*(?:\d+[\.\)]\s+|\n+)/).map(p => p.trim()).filter(p => p.length > 0);
    } else {
        points = raw.split(/\.,\s*-\s*|,\s*-\s*|\n\s*-\s*|^\s*[-•*]\s*|\n+/).map(p => p.trim()).filter(p => p.length > 0);
    }
    
    if (points.length > 0) {
        return points.map(p => {
            p = p.replace(/^[-•*]\s*/, '').replace(/\.$/, '');
            return `<div class="flex items-start gap-2.5 py-0.5"><span class="text-[#00D4AA] font-bold shrink-0 mt-0.5">•</span><span class="text-gray-200 leading-relaxed">${escapeHtml(p)}</span></div>`;
        }).join('');
    }
    
    return `<p class="text-xs text-gray-200 leading-relaxed">${escapeHtml(raw)}</p>`;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Link Copied!',
            text: 'Meeting link copied to your clipboard',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            background: '#0D131F',
            color: '#fff'
        });
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

// Summary Modal Functions
document.getElementById('edit-summary-btn')?.addEventListener('click', () => {
    document.getElementById('summary-modal').classList.remove('hidden');
});

function closeSummaryModal() {
    document.getElementById('summary-modal').classList.add('hidden');
}

async function saveSummary() {
    const summary = document.getElementById('summary-text').value;
    
    try {
        const url = BASE_PATH + '/admin-panel/apis/expert/session-management.php';
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'update_summary',
                booking_id: bookingId,
                summary: summary
            })
        });
        
        const responseText = await response.text();
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            Swal.fire({
                icon: 'error',
                title: 'Invalid Server Response',
                text: 'Server response is not valid JSON',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
            return;
        }
        
        if (data.success) {
            document.getElementById('summary-display').innerHTML = summary ? 
                `<p class="text-xs text-gray-100 whitespace-pre-line leading-relaxed bg-[#0D131F] p-3.5 rounded-xl border border-gray-800 font-medium">${escapeHtml(summary)}</p>` :
                '<p class="text-xs text-gray-500 italic">No session summary added yet</p>';
            closeSummaryModal();
            Swal.fire({
                icon: 'success',
                title: 'Summary Saved',
                text: 'Session summary updated successfully!',
                timer: 1800,
                showConfirmButton: false,
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: data.message || 'Unknown error occurred',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error details:', error);
        Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: error.message || 'Failed to save summary',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

document.getElementById('enhance-ai-btn')?.addEventListener('click', async () => {
    const summaryText = document.getElementById('summary-text').value.trim();
    
    if (!summaryText) {
        Swal.fire({
            icon: 'warning',
            title: 'Write Initial Notes',
            text: 'Please write at least 1-2 lines before using AI enhancement.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        return;
    }
    
    const btn = document.getElementById('enhance-ai-btn');
    const loader = document.getElementById('ai-loader');
    
    btn.classList.add('hidden');
    loader.classList.remove('hidden');
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'enhance_summary_ai',
                summary: summaryText
            })
        });
        
        const data = await response.json();
        if (data.success) {
            document.getElementById('summary-text').value = data.enhanced_summary;
            Swal.fire({
                icon: 'success',
                title: '✨ AI Enhanced!',
                text: 'Your summary has been polished and formatted.',
                timer: 1800,
                showConfirmButton: false,
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'AI Enhancement Failed',
                text: data.message || 'Unable to enhance summary.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'AI Enhancement Error',
            text: 'Failed to enhance summary. Please try again.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    } finally {
        btn.classList.remove('hidden');
        loader.classList.add('hidden');
    }
});

// Task Modal Functions
document.getElementById('add-task-btn')?.addEventListener('click', () => {
    document.getElementById('task-titles').value = '';
    document.getElementById('task-count-preview').textContent = '';
    document.getElementById('task-modal').classList.remove('hidden');
});

// Count tasks while typing
document.getElementById('task-titles')?.addEventListener('input', (e) => {
    const lines = e.target.value.split('\n').filter(line => line.trim());
    const count = lines.length;
    document.getElementById('task-count-preview').textContent = count > 0 ? `${count} task(s) will be added` : '';
});

function closeTaskModal() {
    document.getElementById('task-modal').classList.add('hidden');
}

async function saveTasks() {
    const tasksText = document.getElementById('task-titles').value;
    const tasks = tasksText.split('\n').filter(line => line.trim()).map(line => line.trim());
    
    if (tasks.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Enter Tasks',
            text: 'Please enter at least one action item or task.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        return;
    }
    
    const saveBtn = document.getElementById('save-tasks-btn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Adding...';
    
    try {
        // Add tasks one by one
        for (const taskTitle of tasks) {
            await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'add_task',
                    booking_id: bookingId,
                    title: taskTitle,
                    description: ''
                })
            });
        }
        
        // Refresh tasks after all are added
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php?' + new URLSearchParams({
            action: 'get_tasks',
            booking_id: bookingId
        }));
        
        const data = await response.json();
        if (data.success) {
            refreshTasks(data.tasks);
            closeTaskModal();
            Swal.fire({
                icon: 'success',
                title: 'Tasks Added',
                text: 'Action items assigned successfully!',
                timer: 1800,
                showConfirmButton: false,
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Failed to Add Tasks',
            text: 'An error occurred while adding tasks.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

async function toggleTask(taskId) {
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'toggle_task',
                booking_id: bookingId,
                task_id: taskId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            refreshTasks(data.tasks);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Toggle Failed',
                text: data.message || 'Unable to update task status.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to toggle task.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

async function deleteTask(taskId) {
    const confirmResult = await Swal.fire({
        title: 'Delete Task?',
        text: 'Are you sure you want to remove this action item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });
    
    if (!confirmResult.isConfirmed) return;
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'delete_task',
                booking_id: bookingId,
                task_id: taskId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            refreshTasks(data.tasks);
            Swal.fire({
                icon: 'success',
                title: 'Task Deleted',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#0D131F',
                color: '#fff'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: data.message || 'Unable to delete task.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to delete task.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

function refreshTasks(tasks) {
    const container = document.getElementById('tasks-container');
    if (tasks.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No tasks assigned</p>';
        return;
    }
    
    container.innerHTML = tasks.map(task => `
        <div class="flex items-center gap-3 p-2 task-item" data-task-id="${task.id}">
            <input type="checkbox" 
                   ${task.completed ? 'checked' : ''} 
                   onclick="toggleTask('${task.id}')"
                   class="w-4 h-4 text-primary rounded focus:ring-primary cursor-pointer">
            <span class="flex-1 text-xs ${task.completed ? 'line-through text-gray-500' : 'text-gray-200 font-medium'}">
                ${escapeHtml(task.title)}
            </span>
            <button onclick="deleteTask('${task.id}')" class="p-1 text-gray-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `).join('');
}

// Resource Modal Functions
document.getElementById('add-resource-btn')?.addEventListener('click', () => {
    editingResourceId = null;
    document.getElementById('resource-title').value = '';
    document.getElementById('resource-url').value = '';
    document.getElementById('resource-file').value = '';
    document.querySelector('input[name="resource-type"][value="url"]').checked = true;
    toggleResourceType('url');
    
    // Reset modal title and button
    document.querySelector('#resource-modal h3').textContent = 'Add Resource';
    document.getElementById('save-resource-btn').textContent = 'Add Resource';
    
    document.getElementById('resource-modal').classList.remove('hidden');
});

function closeResourceModal() {
    document.getElementById('resource-modal').classList.add('hidden');
    editingResourceId = null;
}

function toggleResourceType(type) {
    const urlSection = document.getElementById('url-input-section');
    const fileSection = document.getElementById('file-input-section');
    
    if (type === 'url') {
        urlSection.classList.remove('hidden');
        fileSection.classList.add('hidden');
    } else {
        urlSection.classList.add('hidden');
        fileSection.classList.remove('hidden');
    }
}

// Alias for compatibility
const saveResource = saveOrUpdateResource;

async function deleteResource(resourceId) {
    const confirmResult = await Swal.fire({
        title: 'Delete Resource?',
        text: 'Are you sure you want to delete this resource?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });
    
    if (!confirmResult.isConfirmed) return;
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'delete_resource',
                booking_id: bookingId,
                resource_id: resourceId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            refreshResources(data.resources);
            Swal.fire({
                icon: 'success',
                title: 'Resource Deleted',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#0D131F',
                color: '#fff'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: data.message || 'Unable to delete resource.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to delete resource.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    }
}

function refreshResources(resources) {
    const container = document.getElementById('resources-container');
    if (resources.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400">No attachments yet</p>';
        return;
    }
    
    container.innerHTML = resources.map(resource => {
        const isFile = resource.type === 'file';
        
        return `
            <div class="flex items-center gap-3 p-3 bg-[#0D131F] border border-gray-800 rounded-xl resource-item" data-resource-id="${resource.id}">
                <svg class="w-4 h-4 text-[#00D4AA] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-200 truncate">${escapeHtml(resource.title)}</p>
                </div>
                <div class="flex items-center gap-1">
                    ${resource.url ? `
                    <a href="${BASE_PATH}${escapeHtml(resource.url)}" ${isFile ? 'download' : 'target="_blank"'} class="p-1.5 text-gray-400 hover:text-[#00D4AA] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    ` : ''}
                    <button onclick="deleteResource('${resource.id}')" class="p-1.5 text-gray-400 hover:text-red-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

let editingResourceId = null;

function editResource(resource) {
    editingResourceId = resource.id;
    document.getElementById('resource-title').value = resource.title;
    
    if (resource.type === 'file') {
        document.querySelector('input[name="resource-type"][value="file"]').checked = true;
        toggleResourceType('file');
    } else {
        document.querySelector('input[name="resource-type"][value="url"]').checked = true;
        document.getElementById('resource-url').value = resource.url || '';
        toggleResourceType('url');
    }
    
    // Update modal title and button
    document.querySelector('#resource-modal h3').textContent = 'Edit Resource';
    document.getElementById('save-resource-btn').textContent = 'Update Resource';
    
    document.getElementById('resource-modal').classList.remove('hidden');
}

async function saveOrUpdateResource() {
    const title = document.getElementById('resource-title').value.trim();
    const resourceType = document.querySelector('input[name="resource-type"]:checked').value;
    
    if (!title) {
        Swal.fire({
            icon: 'warning',
            title: 'Title Required',
            text: 'Please enter a resource title.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
        return;
    }
    
    const saveBtn = document.getElementById('save-resource-btn');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = editingResourceId ? 'Updating...' : 'Saving...';
    
    try {
        let response;
        const action = editingResourceId ? 'edit_resource' : 'add_resource';
        
        if (resourceType === 'file') {
            const fileInput = document.getElementById('resource-file');
            if (!editingResourceId && (!fileInput.files || !fileInput.files[0])) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Required',
                    text: 'Please select a file to upload.',
                    confirmButtonColor: '#00D4AA',
                    background: '#0D131F',
                    color: '#fff',
                    customClass: { popup: 'border border-gray-800 rounded-2xl' }
                });
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
                return;
            }
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('booking_id', bookingId);
            formData.append('title', title);
            formData.append('type', 'file');
            if (editingResourceId) {
                formData.append('resource_id', editingResourceId);
                if (fileInput.files && fileInput.files[0]) {
                    formData.append('file', fileInput.files[0]);
                } else {
                    formData.append('keep_current_file', '1');
                }
            } else {
                formData.append('file', fileInput.files[0]);
            }
            
            response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
                method: 'POST',
                body: formData
            });
        } else {
            const url = document.getElementById('resource-url').value.trim();
            if (!url) {
                Swal.fire({
                    icon: 'warning',
                    title: 'URL Required',
                    text: 'Please enter a valid link/URL.',
                    confirmButtonColor: '#00D4AA',
                    background: '#0D131F',
                    color: '#fff',
                    customClass: { popup: 'border border-gray-800 rounded-2xl' }
                });
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
                return;
            }
            
            const params = new URLSearchParams({
                action: action,
                booking_id: bookingId,
                title: title,
                url: url,
                type: 'link'
            });
            
            if (editingResourceId) {
                params.append('resource_id', editingResourceId);
            }
            
            response = await fetch(BASE_PATH + '/admin-panel/apis/expert/session-management.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params
            });
        }
        
        const data = await response.json();
        if (data.success) {
            refreshResources(data.resources);
            closeResourceModal();
            editingResourceId = null;
            Swal.fire({
                icon: 'success',
                title: editingResourceId ? 'Resource Updated' : 'Resource Uploaded',
                text: 'Attachment saved successfully!',
                timer: 1800,
                showConfirmButton: false,
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: data.message || 'Failed to save resource.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to save resource.',
            confirmButtonColor: '#00D4AA',
            background: '#0D131F',
            color: '#fff',
            customClass: { popup: 'border border-gray-800 rounded-2xl' }
        });
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
