<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Earnings Dashboard - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

// Get expert profile ID
$userId = $_SESSION['user_id'] ?? null;
$expertProfileId = null;

if ($userId) {
    $stmt = $pdo->prepare("SELECT id FROM expert_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile) {
        $expertProfileId = $profile['id'];
    }
}

// Initialize earnings data
$totalEarnings = 0;
$availablePayout = 0;
$sessionsThisMonth = 0;
$avgPerSession = 0;
$payoutHistory = [];

if ($expertProfileId) {
    // Get total earnings from completed bookings
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE b.expert_id = ? AND p.status = 'completed'
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalEarnings = $result['total'] ?? 0;
    
    // Available for payout (completed but not yet paid out)
    $availablePayout = $totalEarnings; // In a real system, subtract already paid amounts
    
    // Sessions this month
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM bookings
        WHERE expert_id = ? 
        AND MONTH(session_datetime) = MONTH(CURRENT_DATE())
        AND YEAR(session_datetime) = YEAR(CURRENT_DATE())
        AND status = 'completed'
    ");
    $stmt->execute([$expertProfileId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $sessionsThisMonth = $result['count'] ?? 0;
    
    // Average per session
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as session_count
        FROM bookings
        WHERE expert_id = ? AND status = 'completed'
    ");
    $stmt->execute([$expertProfileId]);
    $sessionCount = $stmt->fetch(PDO::FETCH_ASSOC)['session_count'] ?? 0;
    
    if ($sessionCount > 0) {
        $avgPerSession = round($totalEarnings / $sessionCount, 2);
    }
    
    // Get payout history (most recent payments)
    $stmt = $pdo->prepare("
        SELECT p.*, b.session_datetime, lp.full_name as learner_name
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN learner_profiles lp ON b.learner_id = lp.id
        WHERE b.expert_id = ? AND p.status = 'completed'
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$expertProfileId]);
    $payoutHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 sm:mb-8 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Earnings Dashboard</h1>
                <p class="text-sm sm:text-base text-gray-600">Track your income, payments, and financial performance</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <select id="periodFilter" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent text-sm">
                    <option value="month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="last_3_months">Last 3 Months</option>
                    <option value="year">This Year</option>
                </select>
                <button class="bg-accent text-white px-4 py-3 rounded-lg hover:bg-yellow-600 transition text-sm">
                    Request Payout
                </button>
            </div>
        </div>

        <!-- Earnings Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-xs sm:text-sm">Total Earnings</p>
                        <p class="text-2xl sm:text-3xl font-bold">₹<?php echo number_format($totalEarnings, 0); ?></p>
                        <p class="text-green-100 text-xs sm:text-sm mt-1">All time earnings</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-green-400 rounded-full">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-blue-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900">₹<?php echo number_format($availablePayout, 0); ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">Available for Payout</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-purple-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900">₹<?php echo number_format($avgPerSession, 0); ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">Avg. Per Session</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 bg-orange-500 rounded-full">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xl sm:text-2xl font-semibold text-gray-900"><?php echo $sessionsThisMonth; ?></p>
                        <p class="text-gray-600 text-xs sm:text-sm">Sessions This Month</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4 sm:mb-6 gap-3">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Revenue Trend</h2>
                    <div class="flex gap-2">
                        <button id="viewMonthly" class="view-btn px-4 py-3 bg-accent text-white rounded text-sm" data-view="monthly">Monthly</button>
                        <button id="viewWeekly" class="view-btn px-4 py-3 bg-gray-200 text-gray-700 rounded text-sm" data-view="weekly">Weekly</button>
                        <button id="viewDaily" class="view-btn px-4 py-3 bg-gray-200 text-gray-700 rounded text-sm" data-view="daily">Daily</button>
                    </div>
                </div>
                
                <div class="h-80">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="space-y-4 sm:space-y-6">
                <!-- This Month Performance -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">This Month Performance</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Sessions Completed</span>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900"><?php echo $sessionsThisMonth; ?></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Earnings</span>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900">₹<?php echo number_format($totalEarnings, 0); ?></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Avg. Per Session</span>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900">₹<?php echo number_format($avgPerSession, 0); ?></span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Available Payout</span>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900">₹<?php echo number_format($availablePayout, 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments Summary -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Recent Payments</h3>
                    <?php if (!empty($payoutHistory)): ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($payoutHistory, 0, 5) as $payment): ?>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($payment['learner_name'] ?? 'Unknown'); ?></p>
                                <p class="text-gray-600 text-xs"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></p>
                            </div>
                            <span class="font-semibold text-green-600">₹<?php echo number_format($payment['amount'], 0); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <p class="text-gray-600 text-sm">No payment history yet</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full bg-accent text-white px-4 py-3 rounded-lg hover:bg-yellow-600 transition text-sm font-semibold">
                            Request Payout
                        </button>
                        <button class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
                            Download Report
                        </button>
                        <button class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
                            View Tax Documents
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout History -->
        <div class="mt-6 sm:mt-8 bg-white rounded-lg shadow-lg p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4 sm:mb-6 gap-3">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Payout History</h2>
                <button class="text-accent hover:text-yellow-600 text-sm font-medium text-left sm:text-right">View All Transactions</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Date</th>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Amount</th>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Sessions</th>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Method</th>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Status</th>
                            <th class="text-left py-3 px-3 sm:px-4 font-medium text-gray-700 text-xs sm:text-sm">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payoutHistory)): ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500 text-sm">
                                    No payment history yet. Start accepting bookings to earn!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payoutHistory as $payment): ?>
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm">
                                        <span class="font-semibold text-gray-900">₹<?php echo number_format($payment['amount'], 0); ?></span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm"><?php echo $payment['learner_name'] ?? 'Unknown'; ?></td>
                                    <td class="py-3 px-3 sm:px-4 text-xs sm:text-sm"><?php echo ucfirst($payment['payment_method']); ?></td>
                                    <td class="py-3 px-3 sm:px-4">
                                        <?php 
                                        $statusClass = $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                        ?>
                                        <span class="px-2 py-1 <?php echo $statusClass; ?> text-xs rounded-full"><?php echo ucfirst($payment['status']); ?></span>
                                    </td>
                                    <td class="py-3 px-3 sm:px-4">
                                        <span class="font-mono text-xs text-gray-600"><?php echo $payment['transaction_id'] ?? 'N/A'; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        // Set BASE_PATH globally
        window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

        let revenueChart = null;
        let currentPeriod = 'month';
        let currentView = 'monthly';

        // Initialize chart
        async function loadChartData() {
            try {
                const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/earnings-data.php?period=${currentPeriod}&view=${currentView}`);
                const result = await response.json();
                
                if (result.success) {
                    updateChart(result.labels, result.data);
                }
            } catch (error) {
                console.error('Failed to load chart data:', error);
            }
        }

        function updateChart(labels, data) {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            if (revenueChart) {
                revenueChart.destroy();
            }
            
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: data,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Period filter change handler
        document.getElementById('periodFilter')?.addEventListener('change', function() {
            currentPeriod = this.value;
            loadChartData();
        });

        // View buttons click handler
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.view-btn').forEach(b => {
                    b.classList.remove('bg-accent', 'text-white');
                    b.classList.add('bg-gray-200', 'text-gray-700');
                });
                this.classList.remove('bg-gray-200', 'text-gray-700');
                this.classList.add('bg-accent', 'text-white');
                
                // Update view and reload data
                currentView = this.dataset.view;
                loadChartData();
            });
        });

        // Load initial data
        loadChartData();
    </script>
    </div>
    </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
