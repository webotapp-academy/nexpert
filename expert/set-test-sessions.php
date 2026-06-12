<?php
/**
 * Set Test Sessions for Achievement Testing
 * This script creates or updates sessions to completed status for testing
 */

// Load session configuration
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    die("Please login as expert first");
}

$expertId = $_SESSION['user_id'];
$targetSessions = isset($_GET['count']) ? (int)$_GET['count'] : 10;

// Get current completed sessions count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ? AND status = 'completed'");
$stmt->execute([$expertId]);
$currentCompleted = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

echo "<h2>Current Status</h2>";
echo "<p>Current Completed Sessions: <strong>{$currentCompleted}</strong></p>";
echo "<p>Target Sessions: <strong>{$targetSessions}</strong></p>";

if (isset($_GET['action']) && $_GET['action'] === 'update') {
    // First, get total bookings for this expert
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE expert_id = ?");
    $stmt->execute([$expertId]);
    $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    if ($totalBookings == 0) {
        echo "<div style='color: red; padding: 20px; background: #fee; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>⚠️ No Bookings Found!</h3>";
        echo "<p>You don't have any bookings yet. Please create some bookings first.</p>";
        echo "<p><strong>Quick Fix:</strong></p>";
        echo "<ol>";
        echo "<li>Login as a learner</li>";
        echo "<li>Book some sessions with your expert account</li>";
        echo "<li>Come back here to mark them as completed</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        // Update existing bookings to completed status
        $needToUpdate = max(0, $targetSessions - $currentCompleted);
        
        if ($needToUpdate > 0) {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'completed' 
                WHERE expert_id = ? 
                AND status != 'completed' 
                LIMIT ?
            ");
            $stmt->execute([$expertId, $needToUpdate]);
            $updated = $stmt->rowCount();
            
            echo "<div style='color: green; padding: 20px; background: #efe; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3>✅ Success!</h3>";
            echo "<p>Updated <strong>{$updated}</strong> sessions to completed status.</p>";
            echo "<p>Total completed sessions now: <strong>" . ($currentCompleted + $updated) . "</strong></p>";
            echo "</div>";
            
            // Clear shown achievements
            $_SESSION['shown_achievements'] = [];
            
            echo "<div style='padding: 20px; background: #e3f2fd; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3>🎉 Next Step:</h3>";
            echo "<p>Achievements have been cleared. Now go to dashboard to see the popup!</p>";
            echo "<p><a href='" . BASE_PATH . "/index.php?panel=expert&page=dashboard' style='display: inline-block; padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to Dashboard →</a></p>";
            echo "</div>";
        } else {
            echo "<div style='color: orange; padding: 20px; background: #fff3cd; border-radius: 8px; margin: 20px 0;'>";
            echo "<h3>ℹ️ Already Completed!</h3>";
            echo "<p>You already have {$currentCompleted} completed sessions (target: {$targetSessions}).</p>";
            echo "<p>Clear achievements and go to dashboard to see popup!</p>";
            echo "</div>";
            
            // Clear shown achievements
            $_SESSION['shown_achievements'] = [];
            
            echo "<p><a href='" . BASE_PATH . "/index.php?panel=expert&page=dashboard' style='display: inline-block; padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to Dashboard →</a></p>";
        }
    }
} else {
    // Show form
    ?>
    <div style="max-width: 800px; margin: 50px auto; padding: 20px; font-family: Arial, sans-serif;">
        <h1>🎯 Set Test Sessions for Achievement</h1>
        
        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>How many completed sessions do you want?</h3>
            <form method="get" action="">
                <input type="hidden" name="action" value="update">
                <div style="margin: 15px 0;">
                    <label style="display: block; margin-bottom: 5px;">Select Target Sessions:</label>
                    <select name="count" style="padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; width: 100%; max-width: 300px;">
                        <option value="10" <?php echo $targetSessions == 10 ? 'selected' : ''; ?>>10 Sessions (Rising Star 🌟)</option>
                        <option value="20" <?php echo $targetSessions == 20 ? 'selected' : ''; ?>>20 Sessions (Session Champion 🏆)</option>
                        <option value="50" <?php echo $targetSessions == 50 ? 'selected' : ''; ?>>50 Sessions (Expert Mentor 👑)</option>
                        <option value="100" <?php echo $targetSessions == 100 ? 'selected' : ''; ?>>100 Sessions (Master Educator 🎖️)</option>
                    </select>
                </div>
                <button type="submit" style="padding: 12px 30px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; font-weight: bold;">
                    Set Sessions & Clear Achievements
                </button>
            </form>
        </div>
        
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>📋 Instructions:</h3>
            <ol style="line-height: 1.8;">
                <li>Select how many sessions you want to complete</li>
                <li>Click "Set Sessions & Clear Achievements"</li>
                <li>This will mark your existing bookings as completed</li>
                <li>Go to dashboard to see the achievement popup! 🎉</li>
            </ol>
        </div>
        
        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>⚠️ Important Notes:</h3>
            <ul style="line-height: 1.8;">
                <li>You need to have existing bookings in database</li>
                <li>If you don't have bookings, create them first as a learner</li>
                <li>This script will convert existing bookings to "completed" status</li>
                <li>Achievements are cleared automatically after setting sessions</li>
            </ul>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="test-achievement.php" style="color: #2196F3; text-decoration: none;">← Back to Test Page</a> | 
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=dashboard" style="color: #2196F3; text-decoration: none;">Go to Dashboard →</a>
        </div>
    </div>
    <?php
}
?>
