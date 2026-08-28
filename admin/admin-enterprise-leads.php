<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/includes/admin-auth-check.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Handle AJAX status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    $leadId = (int)($_POST['lead_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $notes  = trim($_POST['notes'] ?? '');
    
    $validStatuses = ['new', 'contacted', 'demo_scheduled', 'converted', 'lost'];
    if ($leadId && in_array($status, $validStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE enterprise_leads SET status = ?, notes = ? WHERE id = ?");
            $stmt->execute([$status, $notes, $leadId]);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    }
    exit;
}

$page_title = "Enterprise Leads — Admin Panel";
$panel_type = "admin";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/admin-sidebar.php';

// Fetch leads with filter
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM enterprise_leads WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $sql .= " AND (company_name LIKE ? OR contact_name LIKE ? OR email LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counts by status
$countStmt = $pdo->query("SELECT status, COUNT(*) as count FROM enterprise_leads GROUP BY status");
$statusCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$totalLeads = array_sum($statusCounts);
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Enterprise Inquiries & Leads</h1>
            <p class="text-gray-600 mt-1">Audit requests submitted from the For Enterprise landing page</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold">
                Total Leads: <?= $totalLeads ?>
            </span>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold">
                New: <?= $statusCounts['new'] ?? 0 ?>
            </span>
        </div>
    </div>

    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <input type="hidden" name="panel" value="admin">
                <input type="hidden" name="page" value="enterprise-leads">
                
                <div class="flex-1 min-w-[220px]">
                    <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" 
                           placeholder="Search company, contact, or email..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                        <option value="demo_scheduled" <?= $statusFilter === 'demo_scheduled' ? 'selected' : '' ?>>Demo Scheduled</option>
                        <option value="converted" <?= $statusFilter === 'converted' ? 'selected' : '' ?>>Converted</option>
                        <option value="lost" <?= $statusFilter === 'lost' ? 'selected' : '' ?>>Lost</option>
                    </select>
                </div>
                
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                    Filter Leads
                </button>
                <?php if ($statusFilter || $searchQuery): ?>
                <a href="?panel=admin&page=enterprise-leads" class="text-gray-500 hover:text-gray-800 text-sm font-medium">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Leads Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php if (empty($leads)): ?>
            <div class="p-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <p class="text-base font-semibold text-gray-700">No enterprise leads found</p>
                <p class="text-sm text-gray-400 mt-1">Leads submitted through /index.php?page=for-enterprise will appear here.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase text-[11px] tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Company & Contact</th>
                            <th class="px-6 py-3 text-left">Organization Info</th>
                            <th class="px-6 py-3 text-left">Pain Point / Problem</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Received</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php foreach ($leads as $l): ?>
                        <tr id="lead-row-<?= $l['id'] ?>" class="hover:bg-gray-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-base"><?= htmlspecialchars($l['company_name']) ?></div>
                                <div class="text-gray-600 font-medium"><?= htmlspecialchars($l['contact_name'] ?: 'N/A') ?> <?= $l['role'] ? '• ' . htmlspecialchars($l['role']) : '' ?></div>
                                <div class="text-xs text-indigo-600 mt-1">
                                    <a href="mailto:<?= htmlspecialchars($l['email']) ?>" class="hover:underline"><?= htmlspecialchars($l['email']) ?></a>
                                    <?= $l['phone'] ? ' • ' . htmlspecialchars($l['phone']) : '' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <div><span class="font-semibold text-gray-700">Size:</span> <?= htmlspecialchars($l['company_size'] ?: 'N/A') ?></div>
                                <div class="mt-1"><span class="font-semibold text-gray-700">Experts:</span> <?= (int)$l['expert_count'] ?></div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="text-xs text-gray-700 line-clamp-3" title="<?= htmlspecialchars($l['problem_text'] ?: '') ?>">
                                    <?= htmlspecialchars($l['problem_text'] ?: 'None specified') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <select onchange="updateLeadStatus(<?= $l['id'] ?>, this.value)" 
                                        class="text-xs font-semibold rounded-lg px-2.5 py-1.5 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                        <?= $l['status'] === 'new' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' ?>
                                        <?= $l['status'] === 'contacted' ? 'bg-blue-50 text-blue-800 border-blue-300' : '' ?>
                                        <?= $l['status'] === 'demo_scheduled' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' ?>
                                        <?= $l['status'] === 'converted' ? 'bg-teal-50 text-teal-800 border-teal-300' : '' ?>
                                        <?= $l['status'] === 'lost' ? 'bg-gray-100 text-gray-600' : '' ?>">
                                    <option value="new" <?= $l['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                    <option value="contacted" <?= $l['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                                    <option value="demo_scheduled" <?= $l['status'] === 'demo_scheduled' ? 'selected' : '' ?>>Demo Scheduled</option>
                                    <option value="converted" <?= $l['status'] === 'converted' ? 'selected' : '' ?>>Converted</option>
                                    <option value="lost" <?= $l['status'] === 'lost' ? 'selected' : '' ?>>Lost</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                                <?= date('M j, Y g:i a', strtotime($l['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="mailto:<?= htmlspecialchars($l['email']) ?>?subject=<?= urlencode('Nexpert Trust Intelligence Audit: ' . $l['company_name']) ?>" 
                                   class="inline-block bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                    Email Lead
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    async function updateLeadStatus(leadId, newStatus) {
        try {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('lead_id', leadId);
            formData.append('status', newStatus);
            
            const res = await fetch('', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Show subtle toast or feedback
                console.log('Lead status updated to', newStatus);
            } else {
                alert('Error updating status: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('Failed to update lead status.');
        }
    }
    </script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
