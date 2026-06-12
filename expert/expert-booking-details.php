<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Temporary: Set test session for debugging
    $_SESSION['user_id'] = 20; // Test expert ID
    $_SESSION['role'] = 'expert';
    error_log("Session not found in expert booking page, using test session");
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
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
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

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Main Container with Border -->
    <div class="bg-white border-2 border-gray-200 rounded-2xl p-8">
        
        <?php if ($booking['reschedule_requested']): ?>
        <!-- Reschedule Alert Banner -->
        <div class="mb-8 p-4 rounded-xl border <?php echo $booking['reschedule_requested_by'] === 'expert' ? 'bg-indigo-50 border-indigo-200 text-indigo-800' : 'bg-amber-50 border-amber-200 text-amber-800'; ?> flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold">Reschedule Requested</p>
                    <p class="text-sm">
                        <?php if ($booking['reschedule_requested_by'] === 'expert'): ?>
                            You proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>. Waiting for learner approval.
                        <?php else: ?>
                            The learner proposed a new time: <strong><?php echo date('M j, Y @ g:i A', strtotime($booking['reschedule_new_datetime'])); ?></strong>.
                        <?php endif; ?>
                    </p>
                    <?php if ($booking['reschedule_reason']): ?>
                        <p class="text-xs mt-1 italic">Reason: "<?php echo htmlspecialchars($booking['reschedule_reason']); ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($booking['reschedule_requested_by'] === 'learner'): ?>
            <div class="flex gap-2">
                <button onclick="rescheduleAction('approve')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition">Approve</button>
                <button onclick="rescheduleAction('reject')" class="px-4 py-2 bg-white border border-amber-300 text-amber-800 rounded-lg text-sm font-bold hover:bg-amber-100 transition">Decline</button>
            </div>
            <?php else: ?>
                <button onclick="rescheduleAction('reject')" class="text-sm font-medium text-indigo-600 hover:underline">Cancel Request</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Header Row -->
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=learner-management" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Booking Details</h1>
                    <p class="text-gray-500"><?php echo $sessionDisplay; ?> (<?php echo $booking['duration_minutes']; ?> min)</p>
                </div>
            </div>
            <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium border border-gray-200">
                Auto-generated using AI
            </span>
        </div>

        <!-- Two Column Layout -->
        <div class="grid lg:grid-cols-2 gap-8">
            
            <!-- LEFT COLUMN -->
            <div class="space-y-6">
                
                <!-- Attendee Card -->
                <div class="border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Attendee</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                            <?php if (!empty($booking['learner_photo'])): ?>
                                <img src="<?php echo BASE_PATH . '/' . ltrim($booking['learner_photo'], '/'); ?>" 
                                     alt="<?php echo htmlspecialchars($booking['learner_name']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gray-300 text-gray-600 text-lg font-bold">
                                    <?php echo strtoupper(substr($booking['learner_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($booking['learner_name']); ?></h4>
                            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=messages" 
                               class="text-sm text-primary hover:underline">Send Message</a>
                        </div>
                    </div>
                    
                    <!-- What is this call about -->
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Description</p>
                        <p class="text-sm text-gray-600">
                            <?php if (!empty($booking['learner_goals'])): ?>
                                <?php echo htmlspecialchars($booking['learner_goals']); ?>
                            <?php else: ?>
                                <span class="text-gray-400 italic">No goals mentioned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Session Notes -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Session Notes</p>
                            <p class="text-xs text-gray-500">Previous notes and tasks</p>
                        </div>
                        <button id="edit-summary-btn" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                            <?php echo !empty($booking['session_summary']) ? 'Edit Notes' : 'Add notes'; ?>
                        </button>
                    </div>
                    
                    <!-- Summary Display -->
                    <div id="summary-display" class="mt-3">
                        <?php if (!empty($booking['session_summary'])): ?>
                            <p class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($booking['session_summary']); ?></p>
                        <?php else: ?>
                            <p class="text-gray-500 italic">No summary added yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Red Flags / Important Notes Card -->
                <div class="border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Red Flags / Important Notes</h3>
                    <div class="space-y-3">
                        <?php if (!empty($booking['learner_challenges'])): ?>
                        <div>
                            <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($booking['learner_challenges'])); ?></p>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-400 italic">No challenges mentioned</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Zoom Meeting Details -->
                <?php if ($callDetails): ?>
                <div class="border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Meeting Details</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Date & Time</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo date('M j, Y @ g:i A', strtotime($booking['session_datetime'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Duration</span>
                            <span class="text-sm font-medium text-gray-900"><?php echo $booking['duration_minutes']; ?> minutes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Meeting ID</span>
                            <span class="text-sm font-mono text-gray-900"><?php echo htmlspecialchars($callDetails['meeting_id'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Password</span>
                            <span class="text-sm font-mono text-gray-900"><?php echo htmlspecialchars($callDetails['password'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 flex gap-3">
                            <a href="<?php echo htmlspecialchars($callDetails['start_url'] ?? '#'); ?>" 
                               target="_blank"
                               class="flex-1 text-center bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                                Start Meeting
                            </a>
                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($callDetails['join_url'] ?? ''); ?>')" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Session Status -->
                <div class="border border-gray-200 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Session Status</h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <?php
                            $statusBadge = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'completed' => 'bg-green-100 text-green-800 border-green-200',
                                'cancelled' => 'bg-red-100 text-red-800 border-red-200'
                            ];
                            $badge = $statusBadge[$booking['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            ?>
                            <span class="px-3 py-1 <?php echo $badge; ?> rounded-full text-sm font-medium border capitalize">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </span>
                            <?php if ($booking['accept_booking'] === 'yes'): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 border border-green-200 rounded-full text-sm font-medium">Accepted</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-sm font-medium">Pending Acceptance</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                        <button onclick="openRescheduleModal()" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Reschedule
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            
            <!-- RIGHT COLUMN - Pre-Session Briefing (AI-Generated) -->
            <div class="space-y-6">
                
                <div class="border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-gray-900">Pre-Session Briefing <span class="text-sm font-normal text-gray-500">(AI-Generated)</span></h3>
                        <?php if ($hasLearnerData): ?>
                        <button id="refresh-insights-btn" onclick="generateLearnerInsights(true)" 
                                class="hidden p-2 text-gray-400 hover:text-gray-600 transition" title="Refresh">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($hasLearnerData): ?>
                    
                    <!-- Loading State -->
                    <div id="insights-loading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">AI is analyzing learner profile...</p>
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
                        <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
                            <p class="text-sm text-gray-500">No learner profile data available for AI analysis.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Optional Attachments / Resources -->
                <div class="border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Optional Attachments</h3>
                            <p class="text-xs text-gray-500">Uploaded files</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="add-resource-btn" class="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition" title="Add Resource">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="resources-container" class="space-y-2">
                        <?php if (count($sessionResources) > 0): ?>
                            <?php foreach ($sessionResources as $resource): ?>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg resource-item" data-resource-id="<?php echo htmlspecialchars($resource['id'] ?? ''); ?>">
                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($resource['title'] ?? 'Resource'); ?></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <?php if (!empty($resource['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($resource['url']); ?>" target="_blank" class="p-1 text-gray-400 hover:text-primary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="deleteResource('<?php echo htmlspecialchars($resource['id'] ?? ''); ?>')" class="p-1 text-gray-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">No attachments yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tasks Card -->
                <div class="border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900">Session Tasks</h3>
                        <button id="add-task-btn" class="p-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition" title="Add Task">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="tasks-container" class="space-y-2">
                        <?php if (count($sessionTasks) > 0): ?>
                            <?php foreach ($sessionTasks as $task): ?>
                            <div class="flex items-center gap-3 p-2 task-item" data-task-id="<?php echo htmlspecialchars($task['id'] ?? ''); ?>">
                                <input type="checkbox" 
                                       <?php echo ($task['completed'] ?? false) ? 'checked' : ''; ?> 
                                       onclick="toggleTask('<?php echo htmlspecialchars($task['id'] ?? ''); ?>')"
                                       class="w-4 h-4 text-primary rounded focus:ring-primary cursor-pointer">
                                <span class="flex-1 text-sm <?php echo ($task['completed'] ?? false) ? 'line-through text-gray-400' : 'text-gray-700'; ?>">
                                    <?php echo htmlspecialchars($task['title'] ?? ''); ?>
                                </span>
                                <button onclick="deleteTask('<?php echo htmlspecialchars($task['id'] ?? ''); ?>')" class="p-1 text-gray-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">No tasks assigned</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
<div id="summary-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900">Edit Session Summary</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Summary</label>
                <textarea id="summary-text" rows="6" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Write your session summary..."><?php echo htmlspecialchars($booking['session_summary'] ?? ''); ?></textarea>
            </div>
            <div class="flex items-center gap-2">
                <button id="enhance-ai-btn" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Enhance with AI
                </button>
                <span id="ai-loader" class="hidden text-purple-600 flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enhancing...
                </span>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="closeSummaryModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
            <button onclick="saveSummary()" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Save Summary</button>
        </div>
    </div>
</div>

<!-- Task Modal -->
<div id="task-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900">Add Tasks</h3>
            <p class="text-sm text-gray-600 mt-1">Add multiple tasks at once (one per line)</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tasks * (One per line)</label>
                <textarea id="task-titles" rows="8"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                    placeholder="Review chapter 3&#10;Complete assignment 1&#10;Practice exercises&#10;Submit project report"></textarea>
                <p class="text-xs text-gray-500 mt-1">Each line will create a separate task</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-between items-center">
            <span id="task-count-preview" class="text-sm text-gray-600"></span>
            <div class="flex gap-3">
                <button onclick="closeTaskModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                <button onclick="saveTasks()" id="save-tasks-btn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Add Tasks</button>
            </div>
        </div>
    </div>
</div>

<!-- Resource Modal -->
<div id="resource-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900">Add Resource</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Resource Title *</label>
                <input type="text" id="resource-title" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                    placeholder="e.g., Branding Guide PDF">
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <p class="text-sm font-medium text-gray-700 mb-3">Choose resource type:</p>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="resource-type" value="url" checked 
                            onclick="toggleResourceType('url')"
                            class="w-4 h-4 text-accent focus:ring-accent">
                        <span class="text-sm text-gray-700">Web Link (URL)</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="resource-type" value="file" 
                            onclick="toggleResourceType('file')"
                            class="w-4 h-4 text-accent focus:ring-accent">
                        <span class="text-sm text-gray-700">Upload File (PDF, DOC, PPT, etc.)</span>
                    </label>
                </div>
            </div>
            
            <div id="url-input-section">
                <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                <input type="url" id="resource-url" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                    placeholder="https://example.com/resource.pdf">
            </div>
            
            <div id="file-input-section" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">File (Max 10MB)</label>
                <input type="file" id="resource-file" 
                    accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.zip"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Allowed: PDF, DOC, PPT, XLS, images, ZIP</p>
            </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="closeResourceModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
            <button onclick="saveResource()" id="save-resource-btn" class="px-6 py-2 bg-accent text-white rounded-lg hover:bg-yellow-600 transition">Add Resource</button>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-xl font-bold text-gray-900">Request Reschedule</h3>
            <p class="text-sm text-gray-500">Propose a new time for the learner</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">New Date & Time</label>
                <input type="datetime-local" id="reschedule-datetime" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason (Optional)</label>
                <textarea id="reschedule-reason" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Briefly explain why you need to reschedule..."></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeRescheduleModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">Cancel</button>
            <button onclick="submitRescheduleRequest()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold shadow-lg">Send Request</button>
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

<script>
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
        <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
            <h4 class="font-semibold text-gray-900 mb-2">Learner Overview</h4>
            <p class="text-sm text-gray-600 leading-relaxed">${escapeHtml(insights.overview)}</p>
        </div>
        
        <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
            <h4 class="font-semibold text-gray-900 mb-2">Session Goal Summary</h4>
            <p class="text-sm text-gray-600 leading-relaxed">${escapeHtml(insights.session_goals)}</p>
        </div>
        
        <div class="border border-gray-100 rounded-lg p-4 bg-gray-50">
            <h4 class="font-semibold text-gray-900 mb-2">Recommended Approach for Expert</h4>
            <div class="text-sm text-gray-600 leading-relaxed">${formatApproach(insights.recommended_approach)}</div>
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
    // Format text into bullet points
    let formatted = escapeHtml(text);
    
    // Split by various patterns: ".,- " or ",- " or "\n- " or just "- " at start
    let points = formatted.split(/\.,\s*-\s*|,\s*-\s*|\n\s*-\s*|^\s*-\s*/);
    
    // Clean up and filter empty strings
    points = points.map(p => p.trim()).filter(p => p.length > 0);
    
    // Format as bullet points
    if (points.length > 0) {
        formatted = points.map(p => {
            // Remove trailing period if exists (we'll add consistent formatting)
            p = p.replace(/\.$/, '');
            return '• ' + p;
        }).join('<br>');
    }
    
    return formatted;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
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
    console.log('Saving summary:', summary);
    console.log('Booking ID:', bookingId);
    console.log('BASE_PATH:', BASE_PATH);
    
    try {
        const url = BASE_PATH + '/admin-panel/apis/expert/session-management.php';
        console.log('API URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'update_summary',
                booking_id: bookingId,
                summary: summary
            })
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers]);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            alert('Server response is not valid JSON: ' + responseText);
            return;
        }
        
        console.log('Parsed response:', data);
        
        if (data.success) {
            document.getElementById('summary-display').innerHTML = summary ? 
                `<p class="text-gray-700 whitespace-pre-line">${summary}</p>` :
                '<p class="text-gray-500 italic">No summary added yet</p>';
            closeSummaryModal();
            alert('Summary saved successfully!');
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error details:', error);
        alert('Failed to save summary: ' + error.message);
    }
}

document.getElementById('enhance-ai-btn')?.addEventListener('click', async () => {
    const summaryText = document.getElementById('summary-text').value.trim();
    
    if (!summaryText) {
        alert('Please write at least 1-2 lines before using AI enhancement');
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
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to enhance summary. Make sure OPENAI_API_KEY is configured.');
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
        alert('Please enter at least one task');
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
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add tasks');
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
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to toggle task');
    }
}

async function deleteTask(taskId) {
    if (!confirm('Delete this task?')) return;
    
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
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete task');
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
            <span class="flex-1 text-sm ${task.completed ? 'line-through text-gray-400' : 'text-gray-700'}">
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
    if (!confirm('Delete this resource?')) return;
    
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
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete resource');
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
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg resource-item" data-resource-id="${resource.id}">
                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${escapeHtml(resource.title)}</p>
                </div>
                <div class="flex items-center gap-1">
                    ${resource.url ? `
                    <a href="${BASE_PATH}${escapeHtml(resource.url)}" ${isFile ? 'download' : 'target="_blank"'} class="p-1 text-gray-400 hover:text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                    ` : ''}
                    <button onclick="deleteResource('${resource.id}')" class="p-1 text-gray-400 hover:text-red-600">
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
        alert('Please enter a resource title');
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
                alert('Please select a file to upload');
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
                alert('Please enter a URL');
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
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save resource');
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

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
