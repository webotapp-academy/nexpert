<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Central session + config (defines BASE_PATH / BASE_URL and starts session)
require_once dirname(__DIR__) . '/includes/session-config.php';

// DB connection
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Earnings Dashboard - Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';

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
$bookingEarnings = 0;
$programEarnings = 0;
$availablePayout = 0;
$sessionsThisMonth = 0;
$programsSold = 0;
$avgPerSession = 0;
$payoutHistory = [];

if ($userId) {
    try {
        // 1. Get total earnings from completed bookings (session payments)
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(p.amount), 0) as total, COUNT(DISTINCT b.id) as session_count
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            WHERE b.expert_id = ? AND p.status = 'success'
        ");
        $stmt->execute([$userId]);
        $bookingRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $bookingEarnings = (float)($bookingRow['total'] ?? 0);
        $completedSessions = (int)($bookingRow['session_count'] ?? 0);

        // 2. Get total earnings from program enrollments
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(p.amount), 0) as total, COUNT(*) as count
            FROM payments p
            WHERE p.expert_id = ? 
            AND p.booking_id IS NULL 
            AND p.status = 'success'
        ");
        $stmt->execute([$userId]);
        $programRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $programEarnings = (float)($programRow['total'] ?? 0);
        $programsSold = (int)($programRow['count'] ?? 0);

        // 3. Total earnings (bookings + programs)
        $totalEarnings = $bookingEarnings + $programEarnings;

        // 4. Calculate already requested/paid out amounts
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) as paid_out
            FROM expert_payouts
            WHERE expert_id = ? AND status IN ('pending', 'processed', 'completed')
        ");
        $stmt->execute([$userId]);
        $paidOut = (float)($stmt->fetch(PDO::FETCH_ASSOC)['paid_out'] ?? 0);

        // 5. Available for payout (total earnings minus already requested/paid)
        $availablePayout = max(0, $totalEarnings - $paidOut);

        // 6. Sessions this month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM bookings
            WHERE expert_id = ? 
            AND MONTH(session_datetime) = MONTH(CURRENT_DATE())
            AND YEAR(session_datetime) = YEAR(CURRENT_DATE())
            AND status IN ('completed', 'confirmed')
        ");
        $stmt->execute([$userId]);
        $sessionsThisMonth = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // 7. Average per session
        if ($completedSessions > 0) {
            $avgPerSession = round($bookingEarnings / $completedSessions, 2);
        }

        // 8. Get payout & settlement history (live SQL telemetry)
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.booking_id,
                p.learner_id,
                p.expert_id,
                p.amount,
                p.currency,
                p.payment_gateway_id,
                p.status,
                p.created_at,
                b.session_datetime,
                b.session_topic,
                COALESCE(lp.full_name, u.email, 'Verified Learner') as learner_name,
                COALESCE(w.title, 'Expert Advisory Session') as program_name,
                CASE 
                    WHEN p.booking_id IS NOT NULL THEN 'session'
                    ELSE 'program'
                END as payment_type
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            LEFT JOIN learner_profiles lp ON p.learner_id = lp.user_id
            LEFT JOIN users u ON p.learner_id = u.id
            LEFT JOIN learner_progress lprog ON (lprog.learner_id = p.learner_id AND lprog.expert_id = p.expert_id)
            LEFT JOIN workflows w ON lprog.workflow_id = w.id
            WHERE (p.expert_id = ? OR b.expert_id = ?) AND p.status = 'success'
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$userId, $userId]);
        $payoutHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Earnings Data Query Error: " . $e->getMessage());
        $totalEarnings = 0;
        $bookingEarnings = 0;
        $programEarnings = 0;
        $availablePayout = 0;
        $sessionsThisMonth = 0;
        $programsSold = 0;
        $avgPerSession = 0;
        $payoutHistory = [];
    }
}
?>

<!-- Main Content Wrapper -->
<div class="min-h-screen bg-[#070913] text-gray-100 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-8 gap-4 pb-6 border-b border-white/[0.08]">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                    Live Financial Telemetry
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Earnings Dashboard</h1>
                <p class="text-sm text-gray-400 mt-1">Real-time revenue, verified session settlement records, and payout management</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <select id="periodFilter" class="px-4 py-2.5 bg-[#0D131F] border border-white/[0.1] text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs font-medium">
                    <option value="month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="last_3_months">Last 3 Months</option>
                    <option value="year">This Year</option>
                </select>
                <button id="requestPayoutBtn" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-5 py-2.5 rounded-xl font-extrabold text-xs shadow-[0_0_20px_rgba(0,212,170,0.3)] transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                    Request Payout &rarr;
                </button>
            </div>
        </div>

        <!-- Earnings Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-[#00D4AA]/15 via-[#0D131F] to-[#080B10] border border-[#00D4AA]/30 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-[#00D4AA] text-xs font-bold uppercase tracking-wider">Total Gross Earnings</p>
                        <p class="text-3xl font-extrabold text-white font-mono mt-1">₹<?php echo number_format($totalEarnings, 0); ?></p>
                        <div class="mt-3 space-y-1">
                            <p class="text-gray-400 text-xs flex justify-between"><span>Sessions:</span> <span class="font-mono text-white font-semibold">₹<?php echo number_format($bookingEarnings, 0); ?></span></p>
                            <p class="text-gray-400 text-xs flex justify-between"><span>Programs:</span> <span class="font-mono text-white font-semibold">₹<?php echo number_format($programEarnings, 0); ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500/10 border border-blue-500/25 rounded-xl">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Available for Payout</p>
                        <p class="text-2xl font-extrabold text-[#00D4AA] font-mono mt-1">₹<?php echo number_format($availablePayout, 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500/10 border border-purple-500/25 rounded-xl">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Avg. Per Session</p>
                        <p class="text-2xl font-extrabold text-white font-mono mt-1">₹<?php echo number_format($avgPerSession, 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-xl">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Programs Sold</p>
                        <p class="text-2xl font-extrabold text-white font-mono mt-1"><?php echo $programsSold; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
                    <div>
                        <h2 class="text-base font-extrabold text-white">Revenue Performance Trend</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Live verified earnings tracked via escrow telemetry</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="viewMonthly" class="view-btn px-3 py-1.5 bg-[#00D4AA] text-[#080B10] font-bold rounded-lg text-xs transition" data-view="monthly">Monthly</button>
                        <button id="viewWeekly" class="view-btn px-3 py-1.5 bg-white/[0.04] border border-white/[0.1] text-gray-300 rounded-lg text-xs font-medium transition hover:text-white" data-view="weekly">Weekly</button>
                        <button id="viewDaily" class="view-btn px-3 py-1.5 bg-white/[0.04] border border-white/[0.1] text-gray-300 rounded-lg text-xs font-medium transition hover:text-white" data-view="daily">Daily</button>
                    </div>
                </div>
                
                <div class="h-80 relative">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="space-y-6">
                <!-- This Month Performance -->
                <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                    <h3 class="text-sm font-extrabold text-white mb-4 flex items-center gap-2">
                        <span class="text-[#00D4AA]">📊</span>
                        <span>Performance Summary</span>
                    </h3>
                    <div class="space-y-3.5 text-xs">
                        <div class="flex justify-between items-center text-gray-400">
                            <span>Sessions This Month</span>
                            <span class="font-bold text-white font-mono"><?php echo $sessionsThisMonth; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-400">
                            <span>Programs Sold</span>
                            <span class="font-bold text-white font-mono"><?php echo $programsSold; ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-white/[0.06] text-gray-400">
                            <span>Session Earnings</span>
                            <span class="font-bold text-white font-mono">₹<?php echo number_format($bookingEarnings, 0); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-400">
                            <span>Program Earnings</span>
                            <span class="font-bold text-white font-mono">₹<?php echo number_format($programEarnings, 0); ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-white/[0.06] font-bold">
                            <span class="text-white">Total Gross Earnings</span>
                            <span class="text-[#00D4AA] font-mono text-sm">₹<?php echo number_format($totalEarnings, 0); ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 text-gray-400">
                            <span>Available Payout</span>
                            <span class="font-bold text-emerald-400 font-mono">₹<?php echo number_format($availablePayout, 0); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments Summary -->
                <div class="bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
                    <h3 class="text-sm font-extrabold text-white mb-4 flex items-center gap-2">
                        <span class="text-[#00D4AA]">⚡</span>
                        <span>Recent Receipts</span>
                    </h3>
                    <?php if (!empty($payoutHistory)): ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($payoutHistory, 0, 5) as $payment): ?>
                        <div class="flex justify-between items-center py-2 border-b border-white/[0.06] last:border-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-white text-xs truncate"><?php echo htmlspecialchars($payment['learner_name'] ?? 'Learner'); ?></p>
                                    <?php if ($payment['payment_type'] === 'program'): ?>
                                        <span class="px-2 py-0.5 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded-full flex-shrink-0">Program</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold rounded-full flex-shrink-0">Session</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-500 text-[11px] mt-0.5 font-mono"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></p>
                            </div>
                            <span class="font-extrabold text-[#00D4AA] font-mono text-xs ml-2">₹<?php echo number_format($payment['amount'], 0); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-6 text-gray-500 text-xs">
                        No payment history recorded yet
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payout History Table -->
        <div class="mt-8 bg-[#0D131F] border border-white/[0.08] rounded-2xl shadow-xl p-6">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-3">
                <div>
                    <h2 class="text-base font-extrabold text-white">Settlement & Transaction Records</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Direct telemetry from completed sessions and paid enrollments</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#080B10] border-b border-white/[0.08] text-gray-400 text-xs uppercase tracking-wider font-bold">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Learner / Topic</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.06] text-xs text-gray-300">
                        <?php if (empty($payoutHistory)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    No settlement records found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payoutHistory as $payment): ?>
                                <tr class="hover:bg-[#131B2E] transition-colors">
                                    <td class="py-3.5 px-4 font-mono text-gray-400"><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($payment['payment_type'] === 'program'): ?>
                                            <span class="px-2.5 py-1 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded-full">Program</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold rounded-full">Session</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-extrabold text-[#00D4AA] font-mono text-sm">
                                        ₹<?php echo number_format($payment['amount'], 0); ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div>
                                            <p class="font-bold text-white"><?php echo htmlspecialchars($payment['learner_name'] ?? 'Learner'); ?></p>
                                            <p class="text-gray-400 text-[11px] mt-0.5"><?php echo htmlspecialchars($payment['session_topic'] ?? $payment['program_name'] ?? 'Consultation'); ?></p>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-400">
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Escrow
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-full">Completed</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-gray-500 text-[11px]">
                                        <?php echo !empty($payment['payment_gateway_id']) ? htmlspecialchars($payment['payment_gateway_id']) : ('TXN_' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT)); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js Script -->
    <script>
        // Set BASE_PATH globally
        window.BASE_PATH = '<?php echo BASE_PATH; ?>';

        let revenueChart = null;
        let currentPeriod = 'month';
        let currentView = 'monthly';

        // Request Payout Handler
        async function requestPayout() {
            const availablePayout = <?php echo $availablePayout; ?>;
            const expertId = <?php echo $userId ?? 0; ?>;
            
            if (availablePayout <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Available Payout',
                    text: 'You do not have any earnings available for payout.',
                    background: '#0D131F',
                    color: '#fff',
                    confirmButtonColor: '#00D4AA'
                });
                return;
            }

            const { value: confirmed } = await Swal.fire({
                title: 'Request Payout',
                html: `
                    <div class="text-left">
                        <p class="mb-4 text-gray-300">You are about to request a settlement payout of:</p>
                        <div class="bg-[#00D4AA]/10 border border-[#00D4AA]/30 rounded-xl p-4 mb-4">
                            <p class="text-3xl font-extrabold text-[#00D4AA] font-mono">₹${availablePayout.toLocaleString()}</p>
                        </div>
                        <p class="text-xs text-gray-400">The amount will be transferred to your registered bank account within 1-2 business days.</p>
                    </div>
                `,
                icon: 'question',
                background: '#0D131F',
                color: '#fff',
                showCancelButton: true,
                confirmButtonText: 'Confirm Payout Request',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#00D4AA',
                cancelButtonColor: '#374151'
            });

            if (!confirmed) return;

            try {
                const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/payout-request.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        expert_id: expertId,
                        amount: availablePayout,
                        currency: 'INR'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payout Requested!',
                        html: `
                            <p class="text-gray-300">Your payout request has been submitted successfully.</p>
                            <p class="mt-2 text-xs text-gray-400">You will receive a confirmation email shortly.</p>
                        `,
                        background: '#0D131F',
                        color: '#fff',
                        confirmButtonColor: '#00D4AA'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: result.message || 'Failed to submit payout request. Please try again.',
                        background: '#0D131F',
                        color: '#fff',
                        confirmButtonColor: '#EF4444'
                    });
                }
            } catch (error) {
                console.error('Payout request error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    background: '#0D131F',
                    color: '#fff',
                    confirmButtonColor: '#EF4444'
                });
            }
        }

        // Attach event listeners
        document.getElementById('requestPayoutBtn')?.addEventListener('click', requestPayout);

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
            const canvas = document.getElementById('revenueChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            
            if (revenueChart) {
                revenueChart.destroy();
            }

            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(0, 212, 170, 0.25)');
            gradient.addColorStop(1, 'rgba(0, 212, 170, 0.0)');
            
            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: data,
                        borderColor: '#00D4AA',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#00D4AA',
                        pointBorderColor: '#080B10',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0c1222',
                            titleColor: '#fff',
                            bodyColor: '#00D4AA',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Earnings: ₹' + Number(context.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.04)'
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.04)'
                            },
                            ticks: {
                                color: '#9CA3AF',
                                font: {
                                    size: 11
                                },
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
                document.querySelectorAll('.view-btn').forEach(b => {
                    b.className = 'view-btn px-3 py-1.5 bg-white/[0.04] border border-white/[0.1] text-gray-300 rounded-lg text-xs font-medium transition hover:text-white';
                });
                this.className = 'view-btn px-3 py-1.5 bg-[#00D4AA] text-[#080B10] font-bold rounded-lg text-xs transition';
                
                currentView = this.dataset.view;
                loadChartData();
            });
        });

        // Load initial data
        loadChartData();
    </script>
</div>
<!-- End Main Content Wrapper -->

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
