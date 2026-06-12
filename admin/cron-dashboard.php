<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../admin-panel/apis/connection/pdo.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$page_title = "Cron Jobs Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Cron Jobs Dashboard</h1>
            <p class="text-gray-600 mt-2">Monitor automated background tasks</p>
        </div>

        <!-- Manual Trigger Button -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Manual Control</h2>
            <button onclick="triggerCron()" id="trigger-btn" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                🔄 Run Now
            </button>
            <div id="trigger-result" class="mt-4"></div>
        </div>

        <!-- Cron Jobs Status -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Active Cron Jobs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Run</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Run Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Result</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM cron_jobs ORDER BY last_run DESC");
                        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($jobs as $job):
                            $statusColor = $job['status'] === 'completed' ? 'green' : ($job['status'] === 'failed' ? 'red' : 'yellow');
                            $lastRun = new DateTime($job['last_run']);
                            $nextRun = new DateTime($job['next_run']);
                            $now = new DateTime();
                            $isOverdue = $nextRun < $now;
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($job['job_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800">
                                    <?php echo htmlspecialchars($job['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $lastRun->format('Y-m-d H:i:s'); ?>
                                <div class="text-xs text-gray-400"><?php echo $lastRun->diff($now)->format('%h hours ago'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="<?php echo $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500'; ?>">
                                    <?php echo $nextRun->format('Y-m-d H:i:s'); ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <div class="text-xs text-red-500">⚠️ Overdue</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($job['run_count']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo htmlspecialchars(substr($job['last_result'], 0, 50)) . (strlen($job['last_result']) > 50 ? '...' : ''); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow mt-6 p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Log Entries</h2>
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-x-auto max-h-96 overflow-y-auto">
                <?php
                $logFile = __DIR__ . '/../logs/auto-scheduler.log';
                if (file_exists($logFile)) {
                    $logs = file($logFile);
                    $recentLogs = array_slice($logs, -50);
                    echo htmlspecialchars(implode('', $recentLogs));
                } else {
                    echo "No logs found";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        async function triggerCron() {
            const btn = document.getElementById('trigger-btn');
            const result = document.getElementById('trigger-result');
            
            btn.disabled = true;
            btn.innerHTML = '⏳ Running...';
            result.innerHTML = '<div class="text-blue-600">Executing cron job...</div>';
            
            try {
                const response = await fetch('<?php echo BASE_PATH; ?>/cron/auto-scheduler.php?key=nexpert_cron_2025');
                const data = await response.json();
                
                result.innerHTML = `
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="text-green-800 font-semibold">✅ ${data.message}</div>
                        <div class="text-sm text-green-600 mt-1">Timestamp: ${data.timestamp}</div>
                    </div>
                `;
                
                // Refresh page after 2 seconds
                setTimeout(() => location.reload(), 2000);
            } catch (error) {
                result.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="text-red-800 font-semibold">❌ Error: ${error.message}</div>
                    </div>
                `;
            } finally {
                btn.disabled = false;
                btn.innerHTML = '🔄 Run Now';
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>
