<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$programId = $_GET['id'] ?? null;
if (!$programId) {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=my-programs');
    exit;
}

$expertId = $_SESSION['user_id'];

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_program') {
    try {
        // Verify CSRF token (if you have CSRF protection)
        // Begin transaction
        $pdo->beginTransaction();
        
        // Soft delete the program
        $stmt = $pdo->prepare("UPDATE workflows SET is_active = 0 WHERE id = ? AND expert_id = ?");
        $stmt->execute([$programId, $expertId]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Program deleted successfully.";
        header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=my-programs');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Failed to delete program: " . $e->getMessage();
    }
}

// Get program details
$stmt = $pdo->prepare("
    SELECT * FROM workflows 
    WHERE id = ? AND expert_id = ? AND is_active = 1
");
$stmt->execute([$programId, $expertId]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=my-programs');
    exit;
}

// Get milestones (step_type = 'milestone' or empty for legacy/AI-generated programs)
$stmt = $pdo->prepare("
    SELECT * FROM workflow_steps 
    WHERE workflow_id = ? AND (step_type = 'milestone' OR step_type = '' OR step_type IS NULL)
    ORDER BY step_order ASC
");
$stmt->execute([$programId]);
$milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assignments (step_type = 'assignment_template', 'assignment', 'task', or specific resource types)
$stmt = $pdo->prepare("
    SELECT * FROM workflow_steps 
    WHERE workflow_id = ? AND step_type IN ('assignment_template', 'assignment', 'task', 'session', 'survey', 'followup')
    ORDER BY step_order ASC
");
$stmt->execute([$programId]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get enrolled learners
$stmt = $pdo->prepare("
    SELECT 
        lp.learner_id,
        u.email,
        lpr.full_name,
        lp.progress_percentage,
        lp.created_at as enrolled_date
    FROM learner_progress lp
    JOIN users u ON lp.learner_id = u.id
    JOIN learner_profiles lpr ON u.id = lpr.user_id
    WHERE lp.workflow_id = ?
    ORDER BY lp.created_at DESC
");
$stmt->execute([$programId]);
$learners = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = htmlspecialchars($program['title']) . " - Program Details";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=my-programs" class="text-accent hover:text-yellow-600 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to My Programs
        </a>
    </div>

    <!-- Program Header -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 lg:gap-6">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 md:mb-4"><?php echo htmlspecialchars($program['title']); ?></h1>
                <p class="text-gray-600 mb-4 md:mb-6 text-sm sm:text-base"><?php echo htmlspecialchars($program['description']); ?></p>
                <div class="flex flex-wrap gap-3 md:gap-4">
                    <div class="flex items-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-700"><?php echo $program['duration_weeks']; ?> weeks</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-gray-700"><?php echo count($milestones); ?> milestones</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="text-gray-700"><?php echo count($learners); ?> enrolled learners</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full lg:w-auto">
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=workflow-builder&workflow_id=<?php echo $programId; ?>" 
                   class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-primary text-white rounded-lg hover:bg-secondary transition text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Program
                </a>
                <button onclick="confirmDelete()" 
                   class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span class="hidden sm:inline">Delete Program</span>
                    <span class="sm:hidden">Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Milestones Section -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Program Milestones</h2>
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=workflow-builder&workflow_id=<?php echo $programId; ?>" 
               class="text-primary hover:text-secondary text-sm font-semibold flex items-center justify-center sm:justify-start gap-1 py-2 sm:py-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Milestone
            </a>
        </div>
        <?php if (count($milestones) > 0): ?>
            <div class="space-y-3 sm:space-y-4">
                <?php foreach ($milestones as $index => $milestone): ?>
                    <div class="flex items-start gap-3 sm:gap-4 p-3 sm:p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 bg-accent text-white rounded-full flex items-center justify-center font-bold text-sm sm:text-base">
                            <?php echo $index + 1; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 mb-1 text-sm sm:text-base break-words"><?php echo htmlspecialchars($milestone['title']); ?></h3>
                            <?php if (!empty($milestone['description'])): ?>
                                <p class="text-gray-600 text-xs sm:text-sm break-words"><?php echo htmlspecialchars($milestone['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="text-gray-500 mb-4">No milestones added yet</p>
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=workflow-builder&workflow_id=<?php echo $programId; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-white rounded-lg hover:bg-yellow-600 transition text-sm sm:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Your First Milestone
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Assignments Section -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8 mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-3">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Assignments & Tasks</h2>
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=workflow-builder&workflow_id=<?php echo $programId; ?>" 
               class="text-primary hover:text-secondary text-sm font-semibold flex items-center justify-center sm:justify-start gap-1 py-2 sm:py-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Assignment
            </a>
        </div>
        <?php if (count($assignments) > 0): ?>
            <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="border border-gray-200 rounded-lg p-3 sm:p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 text-sm sm:text-base break-words"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                        <?php if (!empty($assignment['description'])): ?>
                            <p class="text-gray-600 text-xs sm:text-sm mb-2 break-words"><?php echo htmlspecialchars($assignment['description']); ?></p>
                        <?php endif; ?>
                        <span class="inline-block px-2 sm:px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-full">
                            <?php echo ucfirst($assignment['step_type']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <p class="text-gray-500 mb-4">No assignments added yet</p>
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=workflow-builder&workflow_id=<?php echo $programId; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-accent text-white rounded-lg hover:bg-yellow-600 transition text-sm sm:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Your First Assignment
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Enrolled Learners Section -->
    <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Enrolled Learners</h2>
        <?php if (count($learners) > 0): ?>
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Learner</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Progress</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($learners as $learner): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($learner['full_name']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($learner['email']); ?></p>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $learner['progress_percentage']; ?>%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700"><?php echo round($learner['progress_percentage']); ?>%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-gray-600"><?php echo date('M j, Y', strtotime($learner['enrolled_date'])); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Card View -->
            <div class="md:hidden space-y-3">
                <?php foreach ($learners as $learner): ?>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="mb-3">
                            <p class="font-semibold text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($learner['full_name']); ?></p>
                            <p class="text-xs text-gray-500 break-all"><?php echo htmlspecialchars($learner['email']); ?></p>
                        </div>
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-600">Progress</span>
                                <span class="text-xs font-medium text-gray-700"><?php echo round($learner['progress_percentage']); ?>%</span>
                            </div>
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $learner['progress_percentage']; ?>%"></div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Enrolled:</span> <?php echo date('M j, Y', strtotime($learner['enrolled_date'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-8">No learners enrolled yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-4 sm:p-6 mx-4">
        <div class="flex items-center gap-3 mb-3 sm:mb-4">
            <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900">Delete Program</h3>
                <p class="text-xs sm:text-sm text-gray-600">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-sm sm:text-base text-gray-700 mb-4 sm:mb-6">
            Are you sure you want to delete "<strong class="break-words"><?php echo htmlspecialchars($program['title']); ?></strong>"? 
            <?php if (count($learners) > 0): ?>
                This program has <strong><?php echo count($learners); ?> enrolled learner<?php echo count($learners) > 1 ? 's' : ''; ?></strong>.
            <?php endif; ?>
        </p>
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 sm:justify-end">
            <button onclick="closeDeleteModal()" 
                class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm sm:text-base">
                Cancel
            </button>
            <form method="POST" id="deleteForm" class="w-full sm:w-auto">
                <input type="hidden" name="action" value="delete_program">
                <button type="submit" 
                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm sm:text-base">
                    Delete Program
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
