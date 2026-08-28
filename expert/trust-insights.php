<?php
/**
 * Expert Trust Insights Deep-Dive — Task 3.1
 * Route: index.php?panel=expert&page=trust-insights
 * Detailed score breakdown, history changelog, score simulator, and certificate link.
 */
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'expert') {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}
$expertId = (int)$_SESSION['user_id'];

// Fetch current trust state
$stmt = $pdo->prepare("
    SELECT ep.full_name AS name, ep.category, ep.profile_photo,
           ts.overall_score, ts.band_name, ts.confidence_score, ts.stability_score,
           ts.trend_direction, ts.structure_score, ts.outcome_score,
           ts.boundary_score, ts.consistency_score, ts.is_frozen, ts.last_updated
    FROM expert_profiles ep
    LEFT JOIN trust_state ts ON ep.user_id = ts.expert_id
    WHERE ep.user_id = ?
");
$stmt->execute([$expertId]);
$expert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expert) {
    echo "Expert profile not found.";
    exit;
}

$score       = round((float)($expert['overall_score'] ?? 0), 1);
$band        = $expert['band_name'] ?: 'Unverified';
$confidence  = round((float)($expert['confidence_score'] ?? 0));
$stability   = round((float)($expert['stability_score'] ?? 0));
$structure   = round((float)($expert['structure_score'] ?? 0), 1);
$outcome     = round((float)($expert['outcome_score'] ?? 0), 1);
$boundary    = round((float)($expert['boundary_score'] ?? 0), 1);
$consistency = round((float)($expert['consistency_score'] ?? 0), 1);

// Fetch score changelog snapshots from history
$histStmt = $pdo->prepare("
    SELECT tsh.id, tsh.overall_score, tsh.band_name, tsh.confidence_score, tsh.stability_score,
           tsh.trigger_event_id, tsh.created_at, te.event_type
    FROM trust_state_history tsh
    LEFT JOIN trust_events te ON tsh.trigger_event_id = te.id
    WHERE tsh.expert_id = ?
    ORDER BY tsh.created_at DESC
    LIMIT 10
");
$histStmt->execute([$expertId]);
$history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent signals
$sigStmt = $pdo->prepare("
    SELECT agent_type, signal_value, metadata, created_at
    FROM trust_signals
    WHERE expert_id = ?
    ORDER BY created_at DESC
    LIMIT 6
");
$sigStmt->execute([$expertId]);
$signals = $sigStmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Trust Insights — Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-800">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                Trust Intelligence Deep-Dive
            </div>
            <h1 class="text-3xl font-extrabold text-white">Trust Insights & Score Analytics</h1>
            <p class="text-gray-400 text-sm mt-1">Understanding your behavioral record, signal dimensions, and growth opportunities.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="index.php?panel=expert&page=certificate" class="bg-[#00D4AA] text-[#080B10] px-4 py-2.5 rounded-xl font-bold text-xs hover:bg-[#00bda0] transition shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                View Trust Certificate
            </a>
            <a href="index.php?panel=learner&page=expert-trust-report&expert_id=<?= $expertId ?>" target="_blank" class="bg-[#0d131f] border border-gray-700 text-gray-300 hover:text-white px-4 py-2.5 rounded-xl text-xs font-semibold hover:border-gray-500 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Public Trust Report
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Score & Dimensions -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Overall Score Card -->
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="relative w-36 h-36 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#1f293d" stroke-width="10" />
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#00D4AA" stroke-width="10" stroke-linecap="round"
                                    stroke-dasharray="314" stroke-dashoffset="<?= max(0, 314 - (314 * $score / 100)) ?>" class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-black text-white"><?= $score ?></span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Trust Score</span>
                        </div>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wide bg-gradient-to-r from-emerald-950/40 to-teal-950/40 border border-emerald-800/40 text-emerald-400 mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <?= htmlspecialchars($band) ?> Band
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Overall Behavioral Standing</h3>
                        <p class="text-gray-400 text-xs leading-relaxed mb-4">
                            Calculated from your verified sessions delivered, outcome milestones recorded, and behavioral consistency over time.
                        </p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="bg-[#080B10] p-2.5 rounded-xl border border-gray-800/80">
                                <span class="text-[10px] text-gray-500 block uppercase font-semibold">Confidence</span>
                                <span class="text-sm font-bold text-white"><?= $confidence ?>%</span>
                            </div>
                            <div class="bg-[#080B10] p-2.5 rounded-xl border border-gray-800/80">
                                <span class="text-[10px] text-gray-500 block uppercase font-semibold">Stability</span>
                                <span class="text-sm font-bold text-white"><?= $stability ?>%</span>
                            </div>
                            <div class="bg-[#080B10] p-2.5 rounded-xl border border-gray-800/80 col-span-2 sm:col-span-1">
                                <span class="text-[10px] text-gray-500 block uppercase font-semibold">Trajectory</span>
                                <span class="text-sm font-bold text-[#00D4AA] capitalize"><?= htmlspecialchars($expert['trend_direction'] ?: 'stable') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Dimensional Breakdown -->
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-lg font-bold text-white mb-4">Dimensional Performance</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <!-- Structure -->
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800/80">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-300">Structure</span>
                            <span class="text-xs font-bold text-emerald-400 font-mono"><?= $structure ?>/100</span>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: <?= min(100, $structure) ?>%"></div>
                        </div>
                        <p class="text-[11px] text-gray-500">Curriculum clarity, session agenda execution, and milestone preparation.</p>
                    </div>

                    <!-- Outcome -->
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800/80">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-300">Outcome</span>
                            <span class="text-xs font-bold text-blue-400 font-mono"><?= $outcome ?>/100</span>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-blue-400 rounded-full" style="width: <?= min(100, $outcome) ?>%"></div>
                        </div>
                        <p class="text-[11px] text-gray-500">Learner goal completion, skill milestones, and verified career transitions.</p>
                    </div>

                    <!-- Boundary -->
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800/80">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-300">Boundary</span>
                            <span class="text-xs font-bold text-amber-400 font-mono"><?= $boundary ?>/100</span>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-amber-400 rounded-full" style="width: <?= min(100, $boundary) ?>%"></div>
                        </div>
                        <p class="text-[11px] text-gray-500">Punctuality, respectful communication, and professional conduct.</p>
                    </div>

                    <!-- Consistency -->
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800/80">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-300">Consistency</span>
                            <span class="text-xs font-bold text-purple-400 font-mono"><?= $consistency ?>/100</span>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-purple-400 rounded-full" style="width: <?= min(100, $consistency) ?>%"></div>
                        </div>
                        <p class="text-[11px] text-gray-500">Repeat booking retention, low cancellation, and cohort reliability.</p>
                    </div>
                </div>
            </div>

            <!-- Score History Changelog -->
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-lg font-bold text-white mb-4">Score Changelog History</h3>
                <?php if (empty($history)): ?>
                <div class="py-8 text-center text-gray-500 text-xs">
                    No historical score snapshots recorded yet. Scores are snapshotted on each verified event and hourly aggregation.
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-800 text-left text-xs">
                        <thead class="bg-[#080B10] text-gray-400 uppercase">
                            <tr>
                                <th class="p-3">Date</th>
                                <th class="p-3">Score</th>
                                <th class="p-3">Band</th>
                                <th class="p-3">Confidence</th>
                                <th class="p-3">Trigger Event</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/60 bg-[#0d131f] text-gray-300">
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td class="p-3 whitespace-nowrap text-gray-400"><?= date('M j, Y g:i a', strtotime($h['created_at'])) ?></td>
                                <td class="p-3 font-bold text-white font-mono"><?= number_format((float)$h['overall_score'], 1) ?></td>
                                <td class="p-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-800 text-emerald-400"><?= htmlspecialchars($h['band_name'] ?: 'Verified') ?></span></td>
                                <td class="p-3"><?= round((float)$h['confidence_score']) ?>%</td>
                                <td class="p-3 text-gray-400 capitalize"><?= htmlspecialchars(str_replace('_', ' ', $h['event_type'] ?: 'Aggregation Cron')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Right Col: Simulator & Signals -->
        <div class="space-y-8">
            
            <!-- What-If Score Simulator -->
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-[10px] font-bold uppercase tracking-wider mb-2">Interactive Tool</div>
                <h3 class="text-base font-bold text-white mb-2">What-If Score Simulator</h3>
                <p class="text-gray-400 text-xs leading-relaxed mb-4">
                    Simulate how high-scoring upcoming sessions impact your overall score via the Exponential Moving Average formula.
                </p>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-300">Hypothetical Next Event Quality</span>
                            <span id="sim-event-val" class="font-bold text-[#00D4AA]">85/100</span>
                        </div>
                        <input id="sim-slider" type="range" min="0" max="100" value="85" 
                               class="w-full accent-[#00D4AA] cursor-pointer bg-gray-800 rounded-lg">
                    </div>

                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800 text-center">
                        <span class="text-[10px] text-gray-500 uppercase font-semibold block">Predicted New Trust Score</span>
                        <span id="sim-predicted-score" class="text-2xl font-black text-white"><?= number_format(($score * 0.7) + (85 * 0.3), 1) ?></span>
                        <span id="sim-delta" class="text-xs font-bold block text-emerald-400 mt-0.5">+<?= number_format((($score * 0.7) + (85 * 0.3)) - $score, 1) ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Agent Signals -->
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-base font-bold text-white mb-4">Recent AI Agent Signals</h3>
                <?php if (empty($signals)): ?>
                <p class="text-gray-500 text-xs text-center py-6">No recent signals. Signals are parsed upon completed sessions and submitted feedback.</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($signals as $s): ?>
                    <div class="p-3 bg-[#080B10] border border-gray-800 rounded-xl">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-bold text-white capitalize"><?= htmlspecialchars($s['agent_type']) ?> Agent</span>
                            <span class="text-xs font-bold text-[#00D4AA] font-mono"><?= round((float)$s['signal_value'], 1) ?></span>
                        </div>
                        <p class="text-[11px] text-gray-400 truncate">
                            <?= date('M j, Y', strtotime($s['created_at'])) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<script>
const currentScore = <?= json_encode((float)$score) ?>;
const slider = document.getElementById('sim-slider');
const valDisplay = document.getElementById('sim-event-val');
const predictedDisplay = document.getElementById('sim-predicted-score');
const deltaDisplay = document.getElementById('sim-delta');

slider.addEventListener('input', function() {
    const val = parseFloat(this.value);
    valDisplay.textContent = val + '/100';
    const newScore = (currentScore * 0.7) + (val * 0.3);
    const delta = newScore - currentScore;
    predictedDisplay.textContent = newScore.toFixed(1);
    deltaDisplay.textContent = (delta >= 0 ? '+' : '') + delta.toFixed(1);
    deltaDisplay.className = 'text-xs font-bold block mt-0.5 ' + (delta >= 0 ? 'text-emerald-400' : 'text-rose-400');
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
