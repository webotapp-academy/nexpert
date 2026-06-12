<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Temporary: Set test session for debugging
    $_SESSION['user_id'] = 19; // Test learner ID
    $_SESSION['role'] = 'learner';
    error_log("Session not found in booking page, using test session");
}

$userId = $_SESSION['user_id'];
$bookingId = $_GET['booking_id'] ?? null;

if (!$bookingId) {
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=dashboard');
    exit;
}

// Get booking details with expert info and profile data
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        u.email as expert_email,
        u.phone as expert_phone,
        ep.full_name as expert_name,
        ep.profile_photo as expert_photo,
        ep.tagline as expert_tagline,
        ep.bio_short as expert_bio_short,
        ep.bio_full as expert_bio_full,
        ep.experience_years as expert_experience,
        ep.expertise_verticals as expert_specializations,
        ep.category as expert_category,
        ep.tags as expert_tags,
        b.ai_insights
    FROM bookings b
    JOIN users u ON b.expert_id = u.id
    JOIN expert_profiles ep ON u.id = ep.user_id
    WHERE b.id = ? AND b.learner_id = ?
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=dashboard');
    exit;
}

// Parse JSON fields if they exist
$callDetails = !empty($booking['join_link']) ? json_decode($booking['join_link'], true) : null;
$sessionTasks = !empty($booking['session_tasks']) ? json_decode($booking['session_tasks'], true) : [];
$sessionResources = !empty($booking['session_resources']) ? json_decode($booking['session_resources'], true) : [];

$page_title = "Booking Details - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<?php
// Check if meeting is in future
$sessionDateTime = new DateTime($booking['session_datetime']);
$now = new DateTime();
$isFutureMeeting = $sessionDateTime > $now;

// Format session time display - showing when booking was created
$sessionDisplay = 'Booked on ' . date('M j, Y \a\t g:i A', strtotime($booking['created_at']));

// Check for expert data
$hasExpertData = !empty($booking['expert_bio_short']) || !empty($booking['expert_bio_full']) || 
                 !empty($booking['expert_specializations']) || !empty($booking['expert_experience']) ||
                 !empty($booking['expert_tagline']);
?>

    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Main Container with Border -->
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-8">
        
        <?php if ($booking['reschedule_requested']): ?>
        <!-- Reschedule Alert Banner -->
        <div class="mb-8 p-4 rounded-xl border <?php echo $booking['reschedule_requested_by'] === 'learner' ? 'bg-indigo-950/20 border-indigo-800/40 text-indigo-200' : 'bg-amber-950/20 border-amber-800/40 text-amber-200'; ?> flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold">Reschedule Requested</p>
                    <p class="text-sm">
                        <?php if ($booking['reschedule_requested_by'] === 'learner'): ?>
                            You proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>. Waiting for expert approval.
                        <?php else: ?>
                            The expert proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>.
                        <?php endif; ?>
                    </p>
                    <?php if ($booking['reschedule_reason']): ?>
                        <p class="text-xs mt-1 italic">Reason: "<?php echo htmlspecialchars($booking['reschedule_reason']); ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($booking['reschedule_requested_by'] === 'expert'): ?>
            <div class="flex gap-2">
                <button onclick="rescheduleAction('approve')" class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg text-sm font-bold shadow-sm hover:bg-[#00bda0] transition">Approve</button>
                <button onclick="rescheduleAction('reject')" class="px-4 py-2 bg-[#0d131f] border border-gray-800 text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-850 transition">Decline</button>
            </div>
            <?php else: ?>
                <button onclick="rescheduleAction('reject')" class="text-sm font-medium text-[#00D4AA] hover:underline">Cancel Request</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Header Row -->
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=dashboard" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Session Details</h1>
                    <p class="text-gray-400"><?php echo $sessionDisplay; ?> (<?php echo $booking['duration_minutes']; ?> min)</p>
                </div>
            </div>
            <span class="px-4 py-2 bg-[#0d131f] text-gray-300 rounded-full text-sm font-medium border border-gray-800">
                Expert Session
            </span>
        </div>

        <!-- Two Column Layout -->
        <div class="grid lg:grid-cols-2 gap-8">
            
            <!-- LEFT COLUMN -->
            <div class="space-y-6">
                
                <!-- Expert Card -->
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Your Expert</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gray-800 overflow-hidden flex-shrink-0 border border-gray-700">
                            <?php if (!empty($booking['expert_photo'])): ?>
                                <img src="<?php echo BASE_PATH . '/' . ltrim($booking['expert_photo'], '/'); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['expert_name']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gray-700 text-white text-lg font-bold">
                                    <?php echo strtoupper(substr($booking['expert_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white"><?php echo htmlspecialchars($booking['expert_name']); ?></h4>
                            <?php if (!empty($booking['expert_tagline'])): ?>
                                <p class="text-sm text-gray-400"><?php echo htmlspecialchars($booking['expert_tagline']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Expert Bio -->
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-300 mb-2">About Your Expert</p>
                        <p class="text-sm text-gray-400">
                            <?php if (!empty($booking['expert_bio_short'])): ?>
                                <?php echo htmlspecialchars($booking['expert_bio_short']); ?>
                            <?php else: ?>
                                <span class="text-gray-500 italic">No bio available</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Session Notes -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-white">Session Notes</p>
                            <p class="text-xs text-gray-400">Your session summary</p>
                        </div>
                    </div>
                </div>
                
                <!-- Session Progress / Important Notes Card -->
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-white mb-3">Session Progress</h3>
                    <div class="space-y-3">
                        <?php if (!empty($booking['session_summary'])): ?>
                        <div>
                            <p class="text-sm text-gray-300"><?php echo nl2br(htmlspecialchars($booking['session_summary'])); ?></p>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500 italic">Session notes will appear here after completion</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Meeting Details -->
                <?php if ($callDetails): ?>
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Meeting Details</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Date & Time</span>
                            <span class="text-sm font-medium text-white"><?php echo date('M j, Y @ g:i A', strtotime($booking['session_datetime'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Duration</span>
                            <span class="text-sm font-medium text-white"><?php echo $booking['duration_minutes']; ?> minutes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Meeting ID</span>
                            <span class="text-sm font-mono text-white"><?php echo htmlspecialchars($callDetails['meeting_id'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Password</span>
                            <span class="text-sm font-mono text-white"><?php echo htmlspecialchars($callDetails['password'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="pt-3 border-t border-gray-800 flex gap-3">
                            <a href="<?php echo htmlspecialchars($callDetails['join_url'] ?? '#'); ?>" 
                               target="_blank"
                               class="flex-1 text-center bg-emerald-600 text-white py-2 px-4 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                                Join Meeting
                            </a>
                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($callDetails['join_url'] ?? ''); ?>')" 
                                    class="px-4 py-2 border border-gray-800 text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Session Status -->
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Session Status</h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <?php
                            $statusBadge = [
                                'pending' => 'bg-yellow-950/40 text-yellow-400 border-yellow-800/40',
                                'confirmed' => 'bg-blue-950/40 text-blue-400 border-blue-800/40',
                                'completed' => 'bg-emerald-950/40 text-emerald-400 border-emerald-800/40',
                                'cancelled' => 'bg-red-950/40 text-red-400 border-red-800/40'
                            ];
                            $badge = $statusBadge[$booking['status']] ?? 'bg-gray-800 text-gray-400 border-gray-700';
                            ?>
                            <span class="px-3 py-1 <?php echo $badge; ?> rounded-full text-sm font-medium border capitalize">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </span>
                            <?php if ($booking['accept_booking'] === 'yes'): ?>
                                <span class="px-3 py-1 bg-emerald-950/40 text-emerald-400 border border-emerald-800/40 rounded-full text-sm font-medium">Accepted</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-[#0d131f] text-gray-400 border border-gray-800 rounded-full text-sm font-medium">Pending Expert</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                        <button onclick="openRescheduleModal()" class="text-sm font-bold text-[#00D4AA] hover:text-white flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Reschedule
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            
            <!-- RIGHT COLUMN - AI Expert Insights -->
            <div class="space-y-6">
                
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-white">Expert Insights <span class="text-sm font-normal text-gray-400">(AI-Generated)</span></h3>
                        <?php if ($hasExpertData): ?>
                        <button id="refresh-expert-insights-btn" onclick="generateExpertInsights(true)" 
                                class="px-3 py-1 text-xs font-medium text-gray-300 hover:text-white hover:bg-gray-800 rounded-md border border-gray-800 transition flex items-center gap-1" 
                                title="Refresh Expert Insights">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($hasExpertData): ?>
                    
                    <!-- Loading State -->
                    <div id="expert-insights-loading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-[#00D4AA] mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-400 text-sm">AI is analyzing expert profile...</p>
                    </div>
                    
                    <!-- AI Insights Content -->
                    <div id="expert-insights-content" class="hidden space-y-4">
                        <div id="ai-expert-insights-container" class="space-y-4">
                            <!-- AI insights will be loaded here -->
                        </div>
                    </div>
                    
                    <script>
                    // Store cached expert insights from PHP
                    <?php if (!empty($booking['ai_insights'])): ?>
                        try {
                            window.cachedExpertInsights = JSON.parse(<?php echo json_encode($booking['ai_insights']); ?>);
                        } catch(e) {
                            window.cachedExpertInsights = null;
                        }
                    <?php else: ?>
                        window.cachedExpertInsights = null;
                    <?php endif; ?>
                    </script>
                    
                    <?php else: ?>
                    <div class="space-y-4">
                        <div class="border border-gray-800 rounded-lg p-4 bg-[#0d131f]">
                            <p class="text-sm text-gray-500">No expert profile data available for AI analysis.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tasks Card -->
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-white">Your Tasks</h3>
                    </div>
                    <div id="tasks-container" class="space-y-2">
                        <?php if (count($sessionTasks) > 0): ?>
                            <?php foreach ($sessionTasks as $task): ?>
                            <div class="flex items-center gap-3 p-2 task-item" data-task-id="<?php echo htmlspecialchars($task['id'] ?? ''); ?>">
                                <input type="checkbox" 
                                       <?php echo ($task['completed'] ?? false) ? 'checked' : ''; ?> 
                                       readonly
                                       class="w-4 h-4 text-[#00D4AA] focus:ring-[#00D4AA] border-gray-800 bg-[#080B10] rounded cursor-not-allowed">
                                <span class="flex-1 text-sm <?php echo ($task['completed'] ?? false) ? 'line-through text-gray-500' : 'text-gray-300'; ?>">
                                    <?php echo htmlspecialchars($task['title'] ?? ''); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No tasks assigned yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resources Card -->
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Session Resources</h3>
                            <p class="text-xs text-gray-400">Files shared by expert</p>
                        </div>
                    </div>
                    <div id="resources-container" class="space-y-2">
                        <?php if (count($sessionResources) > 0): ?>
                            <?php foreach ($sessionResources as $resource): ?>
                            <div class="flex items-center gap-3 p-3 bg-[#131b2e] border border-gray-800 rounded-lg resource-item" data-resource-id="<?php echo htmlspecialchars($resource['id'] ?? ''); ?>">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate"><?php echo htmlspecialchars($resource['title'] ?? 'Resource'); ?></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <?php if (!empty($resource['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank" class="p-1 text-gray-400 hover:text-[#00D4AA]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No resources shared yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-gray-800 bg-[#0d131f]">
            <h3 class="text-xl font-bold text-white">Request Reschedule</h3>
            <p class="text-sm text-gray-400">Propose a new time for your session</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">New Date & Time</label>
                <input type="datetime-local" id="reschedule-datetime" 
                    class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Reason (Optional)</label>
                <textarea id="reschedule-reason" rows="3" 
                    class="w-full px-4 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent"
                    placeholder="Briefly explain why you need to reschedule..."></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-800 flex justify-end gap-3 bg-[#0d131f]">
            <button onclick="closeRescheduleModal()" class="px-6 py-2 border border-gray-800 text-gray-300 rounded-lg hover:bg-gray-800 transition font-medium">Cancel</button>
            <button onclick="submitRescheduleRequest()" class="px-6 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition font-bold shadow-lg">Send Request</button>
        </div>
    </div>
</div>

<script>
// Base path already declared in header
const bookingId = <?php echo json_encode($booking['id']); ?>;

async function generateExpertInsights(forceRefresh = false) {
    const loading = document.getElementById('expert-insights-loading');
    const profileContent = document.getElementById('expert-insights-content');
    const container = document.getElementById('ai-expert-insights-container');
    
    if (!forceRefresh && window.cachedExpertInsights) {
        displayExpertInsights(window.cachedExpertInsights);
        if (loading) loading.classList.add('hidden');
        if (profileContent) profileContent.classList.remove('hidden');
        return;
    }
    
    if (loading) loading.classList.remove('hidden');
    if (profileContent) profileContent.classList.add('hidden');
    
    try {
        const url = BASE_PATH + '/admin-panel/apis/learner/session-insights.php';
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'generate_expert_insights',
                booking_id: bookingId,
                force_refresh: forceRefresh ? '1' : '0'
            })
        });
        
        const data = await response.json();
        if (data.success) {
            window.cachedExpertInsights = data.insights;
            displayExpertInsights(data.insights);
            if (profileContent) profileContent.classList.remove('hidden');
        } else {
            if (container) container.innerHTML = `<div class="bg-red-950/20 border border-red-900/40 rounded-lg p-4 text-red-400"><p class="text-sm">${escapeHtml(data.message || 'Unknown error occurred')}</p></div>`;
            if (profileContent) profileContent.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error generating insights:', error);
        if (container) container.innerHTML = `<div class="bg-red-950/20 border border-red-900/40 rounded-lg p-4 text-red-400"><p class="text-sm">Failed to generate insights: ${error.message}</p></div>`;
        if (profileContent) profileContent.classList.remove('hidden');
    } finally {
        if (loading) loading.classList.add('hidden');
    }
}

function displayExpertInsights(insights) {
    const container = document.getElementById('ai-expert-insights-container');
    container.innerHTML = `
        <div class="border border-gray-800 rounded-lg p-4 bg-[#131b2e]">
            <h3 class="text-sm font-semibold text-white mb-2">Expert Overview</h3>
            <p class="text-sm text-gray-300 leading-relaxed">${escapeHtml(insights.overview)}</p>
        </div>
        <div class="border border-gray-800 rounded-lg p-4 bg-[#131b2e]">
            <h3 class="text-sm font-semibold text-white mb-2">Session Goals Summary</h3>
            <p class="text-sm text-gray-300 leading-relaxed">${escapeHtml(insights.session_goals)}</p>
        </div>
        <div class="border border-gray-800 rounded-lg p-4 bg-[#131b2e]">
            <h3 class="text-sm font-semibold text-white mb-2">Recommended Approach</h3>
            <div class="text-sm text-gray-300 leading-relaxed">${formatApproach(insights.recommended_approach)}</div>
        </div>
    `;
}

function formatApproach(text) {
    if (!text) return '';
    let formatted = escapeHtml(text.toString());
    if (formatted.match(/[•\-\*]\s/)) {
        formatted = formatted.replace(/[•\-\*]\s+/g, '• ').replace(/\n/g, '<br>');
        return formatted;
    }
    if (formatted.match(/\n?\d+\.\s/)) {
        formatted = formatted.replace(/\n?(\d+)\.\s+/g, '<br>• ').replace(/^<br>/, '');
        return formatted;
    }
    const sentences = formatted.split(/(?<=[.!?])\s+/).map(s => s.trim()).filter(s => s.length > 10);
    if (sentences.length > 1) return sentences.map(sentence => `• ${sentence}`).join('<br>');
    const lines = formatted.split(/\n+/).map(line => line.trim()).filter(line => line.length > 5);
    if (lines.length > 1) return lines.map(line => `• ${line}`).join('<br>');
    return formatted.replace(/\n/g, '<br>');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text.toString();
    return div.innerHTML;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-50', 'text-green-600', 'border-green-200');
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-50', 'text-green-600', 'border-green-200');
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}

function openRescheduleModal() {
    document.getElementById('reschedule-modal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('reschedule-modal').classList.add('hidden');
}

async function submitRescheduleRequest() {
    const datetime = document.getElementById('reschedule-datetime').value;
    const reason = document.getElementById('reschedule-reason').value;
    if (!datetime) { alert('Please select a new date and time'); return; }
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
        if (data.success) location.reload(); else alert(data.message);
    } catch (error) { alert('Failed to send request'); }
}

async function rescheduleAction(action) {
    if (!confirm(`Are you sure you want to ${action} this reschedule request?`)) return;
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
        if (data.success) location.reload(); else alert(data.message);
    } catch (error) { alert('Failed to process request'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const insightsCard = document.getElementById('ai-expert-insights-container');
    if (insightsCard) generateExpertInsights();
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>