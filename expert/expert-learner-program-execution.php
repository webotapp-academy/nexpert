<?php
// expert-learner-program-execution.php
// Show learner's program details for expert panel
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check expert login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$expertId = $_SESSION['user_id'];
$workflowId = $_GET['id'] ?? null;
$learnerId = $_GET['learner_id'] ?? null;

// Handle approve/reject actions for assignments BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step_id']) && isset($_POST['action'])) {
    $stepId = $_POST['step_id'];
    $progressId = $_POST['progress_id'] ?? null;
    
    if ($progressId && isset($_POST['action'])) {
        if ($_POST['action'] === 'approve') {
            $stmt = $pdo->prepare("UPDATE learner_step_progress SET status = 'completed' WHERE step_id = ? AND progress_id = ?");
            $stmt->execute([$stepId, $progressId]);
        } elseif ($_POST['action'] === 'reject') {
            $stmt = $pdo->prepare("UPDATE learner_step_progress SET status = 'in_progress' WHERE step_id = ? AND progress_id = ?");
            $stmt->execute([$stepId, $progressId]);
        }
    }
    // Refresh to show updated status
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$page_title = "Learner Program Execution - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

if (!$workflowId || !$learnerId) {
    echo '<div class="max-w-2xl mx-auto p-8 text-center text-red-600">Invalid request: missing program or learner ID.</div>';
    require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php';
    exit;
}

// Get program details and enrollment details (same as learner page)
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
        echo '<div class="max-w-2xl mx-auto p-8 text-center text-red-600">Program not found or you do not have access to it.</div>';
        require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php';
        exit;
    }
    
    $progressId = $program['progress_id'];
    $overallProgress = $program['progress_percentage'] ?? 0;
    
    // Get learner details
    $stmt = $pdo->prepare("SELECT lp.full_name, lp.profile_photo, u.email FROM learner_profiles lp JOIN users u ON lp.user_id = u.id WHERE lp.user_id = ?");
    $stmt->execute([$learnerId]);
    $learner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get milestones (same query as learner page)
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
    
    // Get assignments (same query as learner page)
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
    echo '<div class="max-w-2xl mx-auto p-8 text-center text-red-600">Error loading program data.</div>';
    require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php';
    exit;
}

?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Back Button -->
    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=learner-management" 
       class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold mb-6">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Learner Management
    </a>

    <!-- Program Header (Same as learner page) -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-white">
            <div class="flex items-start gap-6">
                <?php if (!empty($learner['profile_photo'])): ?>
                    <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($learner['profile_photo']); ?>" 
                         alt="<?php echo htmlspecialchars($learner['full_name']); ?>" 
                         class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-full border-4 border-white shadow-lg bg-white/20 flex items-center justify-center text-white text-2xl font-semibold">
                        <?php echo strtoupper(substr($learner['full_name'] ?? 'L', 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <h1 class="text-3xl md:text-4xl font-black mb-2"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <p class="text-blue-100 text-lg mb-2">Learner: <?php echo htmlspecialchars($learner['full_name']); ?></p>
                    <p class="text-blue-200 text-sm mb-4"><?php echo htmlspecialchars($learner['email']); ?></p>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo $program['duration_weeks'] ?? 0; ?> weeks
                        </span>
                        <span class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-bold">
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
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Program Overview</h2>
            <p class="text-gray-700 leading-relaxed mb-6"><?php echo nl2br(htmlspecialchars($program['description'] ?? '')); ?></p>
            
            <?php if (!empty($program['goal_outcome'])): ?>
            <div class="bg-blue-50 border-l-4 border-blue-600 p-6 rounded-lg mb-6">
                <h3 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Learning Goal
                </h3>
                <p class="text-blue-800"><?php echo htmlspecialchars($program['goal_outcome']); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Overall Progress -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-lg font-bold text-gray-900">Learner Progress</span>
                    <span class="text-lg font-bold text-primary"><?php echo round($overallProgress); ?>%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-4 rounded-full transition-all shadow-lg" style="width: <?php echo $overallProgress; ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Milestones Section (Same as learner page) -->
    <?php if (count($milestones) > 0): ?>
    <div class="bg-white rounded-3xl shadow-2xl p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
            <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Program Milestones
        </h2>
        <div class="space-y-4">
            <?php foreach ($milestones as $index => $milestone): 
                $isCompleted = $milestone['completion_status'] === 'completed';
            ?>
            <div class="flex gap-4 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border-2 <?php echo $isCompleted ? 'border-green-300 bg-green-50' : 'border-blue-100'; ?> hover:border-blue-300 transition-all">
                <div class="flex-shrink-0 w-12 h-12 <?php echo $isCompleted ? 'bg-green-500' : 'bg-gradient-to-br from-blue-500 to-purple-600'; ?> text-white rounded-full flex items-center justify-center font-bold text-lg">
                    <?php if ($isCompleted): ?>
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    <?php else: ?>
                        <?php echo $index + 1; ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 text-lg mb-2"><?php echo htmlspecialchars($milestone['title'] ?? ''); ?></h3>
                    <?php if (!empty($milestone['description'])): ?>
                        <p class="text-gray-600 text-sm"><?php echo nl2br(htmlspecialchars($milestone['description'])); ?></p>
                    <?php endif; ?>
                    <?php if ($isCompleted && $milestone['completed_at']): ?>
                    <p class="text-green-600 text-sm mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Completed on <?php echo date('M d, Y', strtotime($milestone['completed_at'])); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assignments Section (Same as learner page with expert actions) -->
    <?php if (count($assignments) > 0): ?>
    <div class="bg-white rounded-3xl shadow-2xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
            <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Program Assignments
        </h2>
        <div class="space-y-6">
            <?php foreach ($assignments as $assignment): 
                $status = $assignment['completion_status'];
                $statusColors = [
                    'not_started' => 'bg-gray-100 text-gray-700 border-gray-200',
                    'in_progress' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'submitted' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'completed' => 'bg-green-100 text-green-700 border-green-200'
                ];
                $borderColor = $statusColors[$status] ?? 'border-purple-100';
            ?>
            <div class="p-6 bg-purple-50 rounded-xl border-2 <?php echo $borderColor; ?> transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <?php if ($status === 'completed'): ?>
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Approved
                                </span>
                            <?php elseif ($status === 'submitted'): ?>
                                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Under Review
                                </span>
                            <?php elseif ($status === 'in_progress'): ?>
                                <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    In Progress
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">
                                    Not Started
                                </span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-500"><?php echo ucfirst(str_replace('_', ' ', $assignment['step_type'])); ?></span>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></h3>
                        <?php if (!empty($assignment['description'])): ?>
                            <p class="text-gray-600 text-sm mt-2"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Assignment Submission Display -->
                <?php if ($status === 'submitted' || $status === 'completed'): ?>
                    <div class="mt-4 border-t-2 border-purple-200 pt-4">
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-4">
                            <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                Learner's Submission
                            </h4>
                            <p class="text-gray-700 text-sm mb-2"><?php echo nl2br(htmlspecialchars($assignment['submitted_content'])); ?></p>
                            <?php if ($assignment['submitted_file_url']): ?>
                                <a href="<?php echo BASE_PATH . '/' . $assignment['submitted_file_url']; ?>" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 text-sm font-semibold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Submitted File
                                </a>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500 mt-2">Submitted on <?php echo date('M d, Y \a\t g:i A', strtotime($assignment['submitted_at'])); ?></p>
                        </div>
                        
                        <!-- Expert Actions for Under Review -->
                        <?php if ($status === 'submitted'): ?>
                            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-4">
                                <h4 class="font-bold text-yellow-900 mb-3">Review Actions</h4>
                                <form method="post" class="flex gap-3">
                                    <input type="hidden" name="step_id" value="<?php echo $assignment['id']; ?>">
                                    <input type="hidden" name="progress_id" value="<?php echo $progressId; ?>">
                                    <button type="submit" name="action" value="approve" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition shadow-md">
                                        ✓ Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold transition shadow-md">
                                        ✗ Reject
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Feedback Display for Completed -->
                        <?php if ($status === 'completed'): ?>
                            <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4">
                                <h4 class="font-bold text-green-900 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Status: Approved
                                </h4>
                                <?php if ($assignment['feedback']): ?>
                                    <p class="text-gray-700 text-sm mb-2"><strong>Feedback:</strong> <?php echo nl2br(htmlspecialchars($assignment['feedback'])); ?></p>
                                <?php endif; ?>
                                <?php if ($assignment['grade']): ?>
                                    <p class="text-green-700 font-bold">Grade: <?php echo htmlspecialchars($assignment['grade']); ?></p>
                                <?php endif; ?>
                                <?php if ($assignment['reviewed_at']): ?>
                                    <p class="text-xs text-gray-500 mt-2">Reviewed on <?php echo date('M d, Y', strtotime($assignment['reviewed_at'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-4 border-t-2 border-purple-200 pt-4">
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-4">
                            <p class="text-gray-600 text-sm">No submission yet.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
