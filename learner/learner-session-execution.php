<?php
// Simplified placeholder join page (Zoom removed)
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';
require_once dirname(__DIR__) . '/includes/session-config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$page_title = 'Join Session - Nexpert.ai';
$panel_type = 'learner';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
<div class="max-w-3xl mx-auto px-4 py-12">
  <h1 class="text-2xl font-bold mb-4">Session Join</h1>
  <?php if ($bookingId): ?>
    <p class="mb-6 text-gray-700">Preparing to join session <span class="font-semibold">#<?php echo htmlspecialchars($bookingId); ?></span>.</p>
    <div class="p-6 bg-white rounded-lg shadow space-y-4">
      <p class="text-gray-600">Video integration currently disabled. Add your meeting link or embed here later.</p>
      <ul class="list-disc ml-6 text-sm text-gray-600">
        <li>Validate booking details (expert, time, status).</li>
        <li>Show countdown until start.</li>
        <li>Provide manual meeting URL / embedded call UI.</li>
      </ul>
      <a href="<?php echo $BASE_PATH; ?>/learner/learner-dashboard.php" class="inline-block bg-primary text-white px-5 py-2 rounded hover:bg-secondary transition">Back to Dashboard</a>
    </div>
  <?php else: ?>
    <p class="text-red-600 mb-4">No booking specified.</p>
    <a href="<?php echo $BASE_PATH; ?>/learner/learner-dashboard.php" class="inline-block bg-primary text-white px-5 py-2 rounded hover:bg-secondary transition">Return to Dashboard</a>
  <?php endif; ?>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
