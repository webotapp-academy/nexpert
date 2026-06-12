<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

// Database connection
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

$learnerId = $_SESSION['user_id'];
$workflowId = $_GET['id'] ?? null;

if (!$workflowId) {
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=my-programs');
    exit;
}

// Get program details
$program = null;
$milestones = [];
$assignments = [];
$progressId = null;
$overallProgress = 0;

try {
    // Get program and enrollment details
    $stmt = $pdo->prepare("
        SELECT 
            w.*,
            ep.full_name as expert_name,
            ep.profile_photo as expert_photo,
            ep.tagline as expert_tagline,
            lp.id as progress_id,
            lp.progress_percentage,
            lp.created_at as enrolled_date
        FROM workflows w
        JOIN expert_profiles ep ON w.expert_id = ep.user_id
        LEFT JOIN learner_progress lp ON (lp.workflow_id = w.id AND lp.learner_id = ?)
        WHERE w.id = ? AND w.is_active = 1
    ");
    $stmt->execute([$learnerId, $workflowId]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program) {
        header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=my-programs');
        exit;
    }
    
    $progressId = $program['progress_id'];
    $overallProgress = $program['progress_percentage'] ?? 0;
    
    // Get milestones (same query as program-details page)
    $stmt = $pdo->prepare("
        SELECT 
            ws.*,
            COALESCE(lsp.status, 'not_started') as completion_status,
            lsp.completed_at
        FROM workflow_steps ws
        LEFT JOIN learner_step_progress lsp ON (ws.id = lsp.step_id AND lsp.progress_id = ?)
        WHERE ws.workflow_id = ? 
        AND (ws.step_type = 'milestone' OR ws.step_type = '' OR ws.step_type IS NULL)
        ORDER BY ws.step_order ASC
    ");
    $stmt->execute([$progressId, $workflowId]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get assignments
    $stmt = $pdo->prepare("
        SELECT 
            ws.*,
            COALESCE(lsp.status, 'not_started') as completion_status,
            lsp.submitted_content,
            lsp.submitted_file_url,
            lsp.submitted_at,
            lsp.feedback,
            lsp.grade,
            lsp.reviewed_at
        FROM workflow_steps ws
        LEFT JOIN learner_step_progress lsp ON (ws.id = lsp.step_id AND lsp.progress_id = ?)
        WHERE ws.workflow_id = ? 
        AND ws.step_type IN ('assignment_template', 'assignment', 'task', 'session', 'survey', 'followup')
        ORDER BY ws.step_order ASC
    ");
    $stmt->execute([$progressId, $workflowId]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching program execution data: " . $e->getMessage());
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=my-programs');
    exit;
}

$page_title = htmlspecialchars($program['title']) . " - Program Execution";
$panel_type = "learner";

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Back Button -->
        <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=my-programs" 
           class="inline-flex items-center gap-2 text-[#00D4AA] hover:text-[#00bda0] font-semibold mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to My Programs
        </a>    <!-- Program Header (Similar to program-details) -->
    <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl overflow-hidden mb-8">
        <div class="bg-gradient-to-br from-[#1b253d] to-[#131b2e] border-b border-gray-800 p-8 text-white">
            <div class="flex items-start gap-6">
                <?php if (!empty($program['expert_photo'])): ?>
                    <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($program['expert_photo']); ?>" 
                         alt="<?php echo htmlspecialchars($program['expert_name']); ?>" 
                         class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-full border-4 border-white shadow-lg bg-white/20 flex items-center justify-center text-white text-2xl font-semibold">
                        <?php echo strtoupper(substr($program['expert_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <h1 class="text-3xl md:text-4xl font-black mb-2"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <p class="text-blue-100 text-lg mb-4">by <?php echo htmlspecialchars($program['expert_name']); ?></p>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-2 bg-[#0e1322] border border-[#00D4AA]/30 text-[#00D4AA] px-4 py-2 rounded-full text-sm font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo $program['duration_weeks'] ?? 0; ?> weeks
                        </span>
                        <span class="inline-flex items-center gap-2 bg-[#0e1322] border border-[#00D4AA]/30 text-[#00D4AA] px-4 py-2 rounded-full text-sm font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
                            </svg>
                            <?php echo count($milestones); ?> Milestones
                        </span>
                        <?php if ($program['enrolled_date']): ?>
                        <span class="inline-flex items-center gap-2 bg-green-500/30 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Enrolled
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-8">
            <h2 class="text-2xl font-bold text-white mb-4">Program Overview</h2>
            <p class="text-gray-300 leading-relaxed mb-6"><?php echo nl2br(htmlspecialchars($program['description'] ?? '')); ?></p>
            
            <?php if (!empty($program['goal_outcome'])): ?>
            <div class="bg-[#0e1322] border-l-4 border-[#00D4AA] p-6 rounded-lg mb-6">
                <h3 class="font-bold text-[#00D4AA] mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Learning Goal
                </h3>
                <p class="text-gray-300"><?php echo htmlspecialchars($program['goal_outcome']); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Overall Progress -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-lg font-bold text-white">Your Progress</span>
                    <span class="text-lg font-bold text-[#00D4AA]"><?php echo round($overallProgress); ?>%</span>
                </div>
                <div class="w-full bg-[#0e1322] border border-gray-800 rounded-full h-4">
                    <div class="bg-[#00D4AA] h-4 rounded-full transition-all shadow-lg" style="width: <?php echo $overallProgress; ?>%"></div>
                </div>
            </div>
        </div>
    </div>

        <!-- Milestones Section (Similar to program-details) -->
        <?php if (count($milestones) > 0): ?>
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-7 h-7 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                Program Milestones
            </h2>
            <div class="space-y-4">
                <?php foreach ($milestones as $index => $milestone): 
                    $isCompleted = $milestone['completion_status'] === 'completed';
                ?>
                <div class="flex gap-4 p-6 bg-[#0e1322] rounded-xl border <?php echo $isCompleted ? 'border-emerald-800 bg-emerald-950/20' : 'border-gray-800'; ?> hover:border-gray-700 transition-all">
                    <div class="flex-shrink-0 w-12 h-12 <?php echo $isCompleted ? 'bg-emerald-600' : 'bg-[#131b2e] border border-gray-800 text-[#00D4AA]'; ?> text-white rounded-full flex items-center justify-center font-bold text-lg">
                        <?php if ($isCompleted): ?>
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        <?php else: ?>
                            <?php echo $index + 1; ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-white text-lg mb-2"><?php echo htmlspecialchars($milestone['title'] ?? ''); ?></h3>
                                <?php if (!empty($milestone['description'])): ?>
                                    <p class="text-gray-400 text-sm"><?php echo nl2br(htmlspecialchars($milestone['description'])); ?></p>
                                <?php endif; ?>
                                <?php if ($isCompleted && $milestone['completed_at']): ?>
                                <p class="text-emerald-400 text-sm mt-2 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Completed on <?php echo date('M d, Y', strtotime($milestone['completed_at'])); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isCompleted && $progressId): ?>
                            <button 
                                class="mark-complete-btn flex-shrink-0 inline-flex items-center gap-2 bg-[#00D4AA] hover:bg-[#00bda0] text-[#080B10] px-4 py-2 rounded-lg font-bold transition shadow-md text-sm"
                                data-step-id="<?php echo $milestone['id']; ?>"
                                data-progress-id="<?php echo $progressId; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Mark Complete
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Assignments Section (Similar to program-details with upload option) -->
        <?php if (count($assignments) > 0): ?>
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-7 h-7 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Program Assignments
            </h2>
            <div class="space-y-6">
                <?php foreach ($assignments as $assignment): 
                    $status = $assignment['completion_status'];
                    $statusColors = [
                        'not_started' => 'bg-[#131b2e] text-gray-400 border-gray-800',
                        'in_progress' => 'bg-yellow-950/40 text-yellow-400 border-yellow-800/30',
                        'submitted' => 'bg-blue-950/40 text-blue-400 border-blue-800/30',
                        'completed' => 'bg-emerald-950/40 text-emerald-400 border-emerald-800/30'
                    ];
                    $borderColor = $statusColors[$status] ?? 'border-gray-800';
                ?>
                <div class="p-6 bg-[#0e1322] rounded-xl border <?php echo $borderColor; ?> transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <?php if ($status === 'completed'): ?>
                                    <span class="inline-flex items-center gap-1 bg-emerald-900/50 text-emerald-400 px-3 py-1 rounded-full text-xs font-bold border border-emerald-800/30">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Completed
                                    </span>
                                <?php elseif ($status === 'submitted'): ?>
                                    <span class="inline-flex items-center gap-1 bg-blue-900/50 text-blue-400 px-3 py-1 rounded-full text-xs font-bold border border-blue-800/30">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Under Review
                                    </span>
                                <?php elseif ($status === 'in_progress'): ?>
                                    <span class="inline-flex items-center gap-1 bg-yellow-900/50 text-yellow-400 px-3 py-1 rounded-full text-xs font-bold border border-yellow-800/30">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        In Progress
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 bg-[#131b2e] text-gray-400 px-3 py-1 rounded-full text-xs font-bold border border-gray-800">
                                        Not Started
                                    </span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $assignment['step_type'])); ?></span>
                            </div>
                            <h3 class="font-bold text-white text-lg"><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></h3>
                            <?php if (!empty($assignment['description'])): ?>
                                <p class="text-gray-400 text-sm mt-2"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Assignment Submission/Review Section -->
                    <?php if ($status === 'not_started' || $status === 'in_progress'): ?>
                        <!-- Upload Form -->
                        <div class="mt-4 border-t border-gray-800 pt-4">
                            <form class="assignment-submit-form" data-step-id="<?php echo $assignment['id']; ?>" data-progress-id="<?php echo $progressId; ?>">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-white mb-2">Your Submission</label>
                                        <textarea 
                                            name="submission_content" 
                                            rows="4" 
                                            class="w-full px-4 py-3 bg-[#0e1322] border border-gray-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00D4AA] text-white placeholder-gray-500 resize-none"
                                            placeholder="Write your submission here..."><?php echo htmlspecialchars($assignment['submitted_content'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-white mb-2">Attach File (Optional)</label>
                                        <input 
                                            type="file" 
                                            name="submission_file" 
                                            accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                            class="w-full px-4 py-3 bg-[#0e1322] border border-gray-800 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#00D4AA] text-white">
                                        <p class="text-xs text-gray-500 mt-1">Allowed: PDF, DOC, DOCX, ZIP, Images (Max 10MB)</p>
                                    </div>
                                    
                                    <div class="flex gap-3">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-2 bg-[#00D4AA] text-[#080B10] px-6 py-3 rounded-xl font-bold hover:bg-[#00bda0] transition shadow-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Submit Assignment
                                        </button>
                                       
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php elseif ($status === 'submitted' || $status === 'completed'): ?>
                        <!-- Submission Display -->
                        <div class="mt-4 border-t border-gray-800 pt-4">
                            <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-4 mb-4">
                                <h4 class="font-bold text-[#00D4AA] mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Your Submission
                                </h4>
                                <p class="text-gray-300 text-sm mb-2"><?php echo nl2br(htmlspecialchars($assignment['submitted_content'])); ?></p>
                                <?php if ($assignment['submitted_file_url']): ?>
                                    <a href="<?php echo BASE_PATH . '/' . $assignment['submitted_file_url']; ?>" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-[#00D4AA] hover:text-[#00bda0] text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        View Attached File
                                    </a>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 mt-2">Submitted on <?php echo date('M d, Y \a\t g:i A', strtotime($assignment['submitted_at'])); ?></p>
                            </div>
                            
                            <?php if ($status === 'completed' && $assignment['feedback']): ?>
                                <!-- Expert Feedback -->
                                <div class="bg-[#131b2e] border border-emerald-800/40 rounded-xl p-4">
                                    <h4 class="font-bold text-emerald-400 mb-2 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Expert Feedback
                                    </h4>
                                    <p class="text-gray-300 text-sm mb-2"><?php echo nl2br(htmlspecialchars($assignment['feedback'])); ?></p>
                                    <?php if ($assignment['grade']): ?>
                                        <p class="text-emerald-400 font-bold">Grade: <?php echo htmlspecialchars($assignment['grade']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-500 mt-2">Reviewed on <?php echo date('M d, Y', strtotime($assignment['reviewed_at'])); ?></p>
                                </div>
                            <?php elseif ($status === 'submitted'): ?>
                                <!-- Waiting for Review -->
                                <div class="bg-[#131b2e] border border-yellow-800/30 rounded-xl p-4">
                                    <p class="text-yellow-400 text-sm flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        Waiting for expert review...
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
// Set BASE_PATH globally
window.BASE_PATH = '<?php echo BASE_PATH; ?>';

console.log('Assignment submission script loaded');
console.log('BASE_PATH:', window.BASE_PATH);

// Handle assignment submission
document.querySelectorAll('.assignment-submit-form').forEach(form => {
    console.log('Found assignment form:', form.dataset);
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        const stepId = this.dataset.stepId;
        const progressId = this.dataset.progressId;
        
        console.log('Step ID:', stepId);
        console.log('Progress ID:', progressId);
        
        if (!stepId || !progressId) {
            alert('Error: Missing step ID or progress ID');
            console.error('Missing data attributes');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('step_id', stepId);
        formData.append('progress_id', progressId);
        formData.append('action', 'submit');
        
        // Log form data
        console.log('Form data:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ':', pair[1]);
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        try {
            const apiUrl = `${window.BASE_PATH}/admin-panel/apis/learner/submit-assignment.php`;
            console.log('Sending request to:', apiUrl);
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            const responseText = await response.text();
            console.log('Response text:', responseText);
            
            // Check if response is empty
            if (!responseText || responseText.trim() === '') {
                throw new Error('Empty response from server');
            }
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response was:', responseText);
                // Show first 200 characters of response for debugging
                const preview = responseText.substring(0, 200);
                throw new Error('Server returned invalid JSON. Response starts with: ' + preview);
            }
            
            console.log('Parsed result:', result);
            
            if (result.success) {
                alert('✅ Assignment submitted successfully!');
                window.location.reload();
            } else {
                alert('❌ Error: ' + (result.message || 'Failed to submit assignment'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('❌ Failed to submit assignment. Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
});

// Handle save as draft
document.querySelectorAll('.save-draft-btn').forEach(btn => {
    console.log('Found draft button');
    
    btn.addEventListener('click', async function() {
        const form = this.closest('.assignment-submit-form');
        const stepId = form.dataset.stepId;
        const progressId = form.dataset.progressId;
        
        console.log('Saving draft - Step ID:', stepId, 'Progress ID:', progressId);
        
        if (!stepId || !progressId) {
            alert('Error: Missing step ID or progress ID');
            return;
        }
        
        const formData = new FormData(form);
        formData.append('step_id', stepId);
        formData.append('progress_id', progressId);
        formData.append('action', 'draft');
        
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = 'Saving...';
        
        try {
            const apiUrl = `${window.BASE_PATH}/admin-panel/apis/learner/submit-assignment.php`;
            console.log('Sending draft request to:', apiUrl);
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Draft response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response from server');
            }
            
            if (result.success) {
                alert('✅ Draft saved successfully!');
            } else {
                alert('❌ Error: ' + (result.message || 'Failed to save draft'));
            }
        } catch (error) {
            console.error('Save draft error:', error);
            alert('❌ Failed to save draft. Error: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });
});

// Handle milestone completion
document.querySelectorAll('.mark-complete-btn').forEach(btn => {
    console.log('Found mark complete button:', btn.dataset);
    
    btn.addEventListener('click', async function() {
        const stepId = this.dataset.stepId;
        const progressId = this.dataset.progressId;
        
        console.log('Marking milestone complete - Step ID:', stepId, 'Progress ID:', progressId);
        
        if (!stepId || !progressId) {
            alert('Error: Missing step ID or progress ID');
            return;
        }
        
        if (!confirm('Mark this milestone as complete?')) {
            return;
        }
        
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        try {
            const apiUrl = `${window.BASE_PATH}/admin-panel/apis/learner/complete-milestone.php`;
            console.log('Sending complete request to:', apiUrl);
            
            const formData = new FormData();
            formData.append('step_id', stepId);
            formData.append('progress_id', progressId);
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('Complete response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response from server: ' + responseText.substring(0, 100));
            }
            
            if (result.success) {
                // Show success message with updated progress
                const progressMsg = result.progress_percentage 
                    ? `\n\nYour progress is now ${result.progress_percentage}%! 🎉` 
                    : '';
                alert('✅ Milestone marked as complete!' + progressMsg);
                
                // Reload to show updated UI
                window.location.reload();
            } else {
                alert('❌ Error: ' + (result.message || 'Failed to complete milestone'));
                this.disabled = false;
                this.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Complete milestone error:', error);
            alert('❌ Failed to complete milestone. Error: ' + error.message);
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
