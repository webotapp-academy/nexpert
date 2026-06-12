<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ?panel=learner&page=auth');
    exit;
}

$page_title = "My Programs - Nexpert.ai";
$panel_type = "learner";

// Database connection
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

$learnerId = $_SESSION['user_id'];
$enrolledPrograms = [];
$totalPrograms = 0;
$totalAssignments = 0;
$averageProgress = 0;

try {
    // Get enrolled programs with progress
    $stmt = $pdo->prepare("
        SELECT 
            lp.id as progress_id,
            lp.progress_percentage,
            lp.created_at as enrolled_date,
            w.id as workflow_id,
            w.title,
            w.description,
            w.duration_weeks,
            w.price_inr,
            ep.full_name as expert_name,
            ep.profile_photo as expert_photo,
            (SELECT COUNT(*) FROM workflow_steps WHERE workflow_id = w.id) as total_steps
        FROM learner_progress lp
        JOIN workflows w ON lp.workflow_id = w.id
        JOIN expert_profiles ep ON w.expert_id = ep.user_id
        WHERE lp.learner_id = ? AND lp.workflow_id IS NOT NULL
        ORDER BY lp.created_at DESC
    ");
    $stmt->execute([$learnerId]);
    $enrolledPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the fetched programs
    error_log("Learner ID: " . $learnerId);
    error_log("Enrolled Programs Count: " . count($enrolledPrograms));
    error_log("Enrolled Programs: " . print_r($enrolledPrograms, true));
    
    $totalPrograms = count($enrolledPrograms);
    
    // Calculate average progress
    if ($totalPrograms > 0) {
        $totalProgressSum = array_sum(array_column($enrolledPrograms, 'progress_percentage'));
        $averageProgress = round($totalProgressSum / $totalPrograms);
    }
    
    // Get pending assignments count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM learner_step_progress lsp
        JOIN workflow_steps ws ON lsp.step_id = ws.id
        JOIN learner_progress lp ON lsp.progress_id = lp.id
        WHERE lp.learner_id = ? AND lsp.status IN ('not_started', 'in_progress')
        AND ws.step_type IN ('assignment', 'assignment_template', 'task')
    ");
    $stmt->execute([$learnerId]);
    $totalAssignments = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Error fetching enrolled programs: " . $e->getMessage());
}

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">My Programs</h1>
        <p class="text-gray-400">Track your enrolled programs, view assignments, and monitor your progress</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-extrabold text-white"><?php echo $totalPrograms; ?></p>
                    <p class="text-gray-400 text-sm mt-1">Active Programs</p>
                </div>
                <div class="p-3 bg-[#00D4AA]/10 text-[#00D4AA] rounded-xl">
                    <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-extrabold text-white"><?php echo $totalAssignments; ?></p>
                    <p class="text-gray-400 text-sm mt-1">Pending Assignments</p>
                </div>
                <div class="p-3 bg-amber-500/10 text-amber-400 rounded-xl">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-extrabold text-white"><?php echo $averageProgress; ?>%</p>
                    <p class="text-gray-400 text-sm mt-1">Overall Progress</p>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs List -->
    <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Enrolled Programs</h2>
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-lg hover:bg-[#00bda0] font-bold transition">
                Browse Experts
            </a>
        </div>

        <?php if (count($enrolledPrograms) > 0): ?>
            <!-- Programs Grid -->
            <div class="grid md:grid-cols-2 gap-6">
                <?php foreach ($enrolledPrograms as $program): ?>
                <div class="border border-gray-800 bg-[#0d131f] rounded-xl p-6 hover:shadow-lg transition">
                    <!-- Expert Info -->
                    <div class="flex items-center gap-3 mb-4">
                        <?php if (!empty($program['expert_photo'])): ?>
                            <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($program['expert_photo']); ?>" 
                                 alt="<?php echo htmlspecialchars($program['expert_name']); ?>" 
                                 class="w-10 h-10 rounded-full object-cover border border-gray-800">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($program['expert_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-400">by <?php echo htmlspecialchars($program['expert_name']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Program Title -->
                    <h3 class="text-lg font-bold text-white mb-2">
                        <?php echo htmlspecialchars($program['title']); ?>
                    </h3>
                    
                    <!-- Program Description -->
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                        <?php echo htmlspecialchars(substr($program['description'], 0, 120)) . (strlen($program['description']) > 120 ? '...' : ''); ?>
                    </p>
                    
                    <!-- Program Stats -->
                    <div class="flex items-center gap-4 mb-4 text-sm text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo $program['duration_weeks']; ?> weeks
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo $program['total_steps']; ?> steps
                        </span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-300">Progress</span>
                            <span class="text-sm font-bold text-[#00D4AA]"><?php echo round($program['progress_percentage']); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-800 rounded-full h-2">
                            <div class="bg-[#00D4AA] h-2 rounded-full transition-all" style="width: <?php echo $program['progress_percentage']; ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Enrolled Date -->
                    <p class="text-xs text-gray-500 mb-4">
                        Enrolled on <?php echo date('M d, Y', strtotime($program['enrolled_date'])); ?>
                    </p>
                    
                    <!-- Action Button -->
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=program-execution&id=<?php echo $program['workflow_id']; ?>" 
                       class="block w-full text-center bg-[#00D4AA] text-[#080B10] font-bold py-2 px-4 rounded-lg hover:bg-[#00bda0] transition">
                        Continue Learning
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="inline-block p-4 bg-[#0d131f] border border-gray-800 rounded-full mb-4">
                    <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">No Programs Yet</h3>
                <p class="text-gray-400 mb-6">Start your learning journey by enrolling in programs with our expert instructors</p>
                <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="inline-block bg-[#00D4AA] text-[#080B10] font-bold px-6 py-3 rounded-lg hover:bg-[#00bda0] transition">
                    Find an Expert
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Assignments Section -->
    <div class="mt-8 bg-[#131b2e] border border-gray-800 rounded-xl p-6 shadow-lg">
        <h2 class="text-xl font-bold text-white mb-6">Upcoming Assignments</h2>
        
        <!-- Empty State -->
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <p class="text-gray-400">No assignments due</p>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
