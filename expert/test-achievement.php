<?php
/**
 * Test Achievement Popup
 * This file helps test the achievement system by clearing shown achievements
 */

// Load session configuration
require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

// Clear shown achievements to retrigger popup
if (isset($_GET['clear'])) {
    $_SESSION['shown_achievements'] = [];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=dashboard');
    exit;
}

// Manually set achievements
if (isset($_GET['set']) && in_array($_GET['set'], [10, 20, 50, 100])) {
    $_SESSION['shown_achievements'] = [];
    // Update session count in database for testing
    $milestone = (int)$_GET['set'];
    
    require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';
    
    // Get current count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'completed'");
    $stmt->execute([$_SESSION['user_id']]);
    $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    echo "<h2>Current Completed Sessions: {$currentCount}</h2>";
    echo "<p>To trigger {$milestone} sessions achievement, you need to have exactly {$milestone} completed sessions.</p>";
    echo "<p><a href='" . BASE_PATH . "/index.php?panel=expert&page=dashboard'>Go to Dashboard</a></p>";
    exit;
}

$page_title = "Test Achievement - Nexpert.ai";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Achievement Testing</h1>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Clear Achievements</h2>
        <p class="mb-4">This will clear all shown achievements so the popup shows again on dashboard.</p>
        <a href="?clear=1" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Clear All Achievements
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Current Status</h2>
        <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'completed'");
        $stmt->execute([$_SESSION['user_id']]);
        $completedSessions = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        
        echo "<p class='mb-2'><strong>Completed Sessions:</strong> {$completedSessions}</p>";
        echo "<p class='mb-4'><strong>Shown Achievements:</strong> " . json_encode($_SESSION['shown_achievements'] ?? []) . "</p>";
        
        echo "<h3 class='font-bold mt-4 mb-2'>Achievement Milestones:</h3>";
        $milestones = [10, 20, 50, 100];
        foreach ($milestones as $milestone) {
            $status = $completedSessions >= $milestone ? '✅ Achieved' : '❌ Not Yet';
            $shown = in_array($milestone, $_SESSION['shown_achievements'] ?? []) ? '(Already shown)' : '(Not shown yet)';
            echo "<p>{$milestone} sessions: {$status} {$shown}</p>";
        }
        ?>
        
        <div class="mt-6">
            <p class="mb-2 font-semibold">Note:</p>
            <p class="text-sm text-gray-600">To test achievement popup, make sure you have completed sessions in the database and then clear achievements above.</p>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
