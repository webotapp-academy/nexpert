<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Simplified placeholder join page (Zoom removed)
require_once dirname(__DIR__) . '/includes/session-config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$page_title = 'Join Session - Nexpert.ai';
$panel_type = 'learner';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
<div class="max-w-3xl mx-auto px-4 py-12 text-white">
  <h1 class="text-2xl font-bold mb-4">Session Join</h1>
  <?php if ($bookingId): ?>
    <p class="mb-6 text-gray-300">Preparing to join session <span class="font-semibold">#<?php echo htmlspecialchars($bookingId); ?></span>.</p>
    <div class="p-6 bg-[#131b2e] border border-gray-800 rounded-lg shadow space-y-4">
      <p class="text-gray-400">Video integration currently disabled. Add your meeting link or embed here later.</p>
      <ul class="list-disc ml-6 text-sm text-gray-400">
        <li>Validate booking details (expert, time, status).</li>
        <li>Show countdown until start.</li>
        <li>Provide manual meeting URL / embedded call UI.</li>
      </ul>
      <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=dashboard" class="inline-block bg-[#00D4AA] text-[#080B10] px-5 py-2 rounded font-bold hover:bg-[#00bda0] transition">Back to Dashboard</a>
    </div>
  <?php else: ?>
    <p class="text-red-400 mb-4">No booking specified.</p>
    <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=dashboard" class="inline-block bg-[#00D4AA] text-[#080B10] px-5 py-2 rounded font-bold hover:bg-[#00bda0] transition">Return to Dashboard</a>
  <?php endif; ?>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
