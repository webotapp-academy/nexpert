<?php
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Get expert_id — validate it exists and is approved
$expertId = (int)($_GET['expert_id'] ?? 0);
if (!$expertId) { header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=browse-experts'); exit; }

// Fetch expert profile + trust state
$stmt = $pdo->prepare("
    SELECT u.id, ep.full_name AS name, ep.tagline AS title, ep.bio_short AS bio,
           ep.profile_photo, ep.experience_years, ep.category, ep.expertise_verticals,
           ep.total_sessions, ep.verification_status,
           ts.overall_score, ts.trust_tier, ts.band_name, ts.confidence_score,
           ts.trend_direction, ts.stability_score,
           ts.structure_score, ts.outcome_score, ts.boundary_score, ts.consistency_score
    FROM users u
    INNER JOIN expert_profiles ep ON u.id = ep.user_id
    LEFT JOIN trust_state ts ON u.id = ts.expert_id
    WHERE u.id = ? AND u.role = 'expert' AND u.status = 'active'
");
$stmt->execute([$expertId]);
$expert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expert) { header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=browse-experts'); exit; }

// Trust history for chart (last 12 months)
$histStmt = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%b %Y') AS label, ROUND(AVG(overall_score),1) AS avg_score
    FROM trust_state_history WHERE expert_id = ?
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY MIN(created_at) ASC
    LIMIT 12
");
$histStmt->execute([$expertId]);
$history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

// Recent completed sessions as outcome evidence
$sessStmt = $pdo->prepare("
    SELECT b.session_datetime, u.id AS learner_id,
           LEFT(lp.full_name, LOCATE(' ', lp.full_name)-1) AS first_name,
           LEFT(SUBSTRING_INDEX(lp.full_name,' ',-1),1) AS last_initial,
           ep2.category
    FROM bookings b
    INNER JOIN users u ON b.learner_id = u.id
    LEFT JOIN learner_profiles lp ON u.id = lp.user_id
    LEFT JOIN expert_profiles ep2 ON b.expert_id = ep2.user_id
    WHERE b.expert_id = ? AND b.status IN ('completed','confirmed')
    ORDER BY b.session_datetime DESC
    LIMIT 6
");
$sessStmt->execute([$expertId]);
$sessions = $sessStmt->fetchAll(PDO::FETCH_ASSOC);

// Explainability — templated PHP (no AI needed at MVP2)
function buildExplanation(array $expert): string {
    $score  = round($expert['overall_score'] ?? 0, 1);
    $band   = $expert['band_name'] ?? 'Unverified';
    $parts  = [];
    $dims   = ['outcome_score'=>['Outcome delivery','30%'],'consistency_score'=>['Consistent behaviour','20%'],'structure_score'=>['Session structure','25%'],'boundary_score'=>['Professional reliability','25%']];
    foreach ($dims as $col => [$label,$weight]) {
        $val = round($expert[$col] ?? 0);
        if ($val >= 75) $parts[] = "{$label} is strong at {$val}/100 (weight: {$weight})";
    }
    $lowestCol = array_keys($dims)[0]; $lowestVal = 100;
    foreach ($dims as $col => $_) { if (($expert[$col]??0) < $lowestVal) { $lowestVal = $expert[$col]??0; $lowestCol = $col; } }
    $lowestLabel = $dims[$lowestCol][0];
    $text  = "This score is {$score} ({$band}). ";
    $text .= count($parts) ? "Strongest signals: " . implode('; ', $parts) . ". " : "";
    $text .= "Area to watch: {$lowestLabel} at " . round($lowestVal) . "/100. ";
    $conf  = $expert['confidence_score'] ?? 0;
    $text .= "Confidence: {$conf}% (based on available trust events).";
    return $text;
}

$explanation = buildExplanation($expert);

// Score ring stroke offset (circumference = 2π × 54 = 339.3)
$circumference = 339.3;
$offset        = $circumference - (($expert['overall_score'] ?? 0) / 100) * $circumference;
$bandColors    = ['Sovereign'=>'#F5A623','Established'=>'#00D4AA','Verified'=>'#3B82F6','Emerging'=>'#9CA3AF','Unverified'=>'#6B7280'];
$ringColor     = $bandColors[$expert['band_name'] ?? 'Unverified'] ?? '#00D4AA';

$page_title = ($expert['name'] ?? 'Expert') . " — Trust Report · Nexpert.ai";
$panel_type = "learner";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';

$avatarUrl = !empty($expert['profile_photo'])
    ? BASE_PATH . '/' . ltrim($expert['profile_photo'],'/')
    : 'https://ui-avatars.com/api/?name=' . urlencode($expert['name'] ?? 'Expert') . '&background=00D4AA&color=080B10&size=200';
?>
<script>document.body.className = "bg-[#080B10] min-h-screen text-white";</script>

<div class="max-w-5xl mx-auto px-4 py-10">

    <!-- Back -->
    <a href="?panel=learner&page=browse-experts" class="inline-flex items-center gap-2 text-gray-500 hover:text-white text-sm mb-8 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Browse
    </a>

    <!-- ── SECTION 1: TRUST IDENTITY ── -->
    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-8 mb-6">
        <div class="flex flex-col md:flex-row items-start gap-8">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($expert['name'] ?? '') ?>"
                     class="w-24 h-24 rounded-2xl object-cover border-2 border-gray-700">
                <div class="absolute -bottom-2 -right-2 w-7 h-7 bg-[#00D4AA] rounded-full flex items-center justify-center border-2 border-[#080B10]">
                    <svg class="w-4 h-4" fill="none" stroke="#080B10" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <!-- Identity -->
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold text-white mb-1"><?= htmlspecialchars($expert['name'] ?? '') ?></h1>
                <p class="text-gray-400 mb-1"><?= htmlspecialchars($expert['title'] ?? $expert['category'] ?? '') ?></p>
                <?php if ($expert['experience_years']): ?>
                <p class="text-gray-600 text-sm mb-4"><?= (int)$expert['experience_years'] ?> years experience · <?= htmlspecialchars($expert['category'] ?? '') ?></p>
                <?php endif; ?>
                <!-- Trust band badge -->
                <?php
                $bandCfg = ['Sovereign'=>['bg-yellow-900/30 border-yellow-700/40','text-yellow-400','●'],'Established'=>['bg-teal-900/30 border-teal-700/40','text-[#00D4AA]','●'],'Verified'=>['bg-blue-900/30 border-blue-700/40','text-blue-400','●'],'Emerging'=>['bg-gray-800 border-gray-700','text-gray-400','●'],'Unverified'=>['bg-gray-900 border-gray-800','text-gray-500','○']];
                $bc = $bandCfg[$expert['band_name'] ?? 'Unverified'] ?? $bandCfg['Unverified'];
                ?>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-lg <?= $bc[0] ?>">
                    <span class="text-xs <?= $bc[1] ?> animate-pulse"><?= $bc[2] ?></span>
                    <span class="text-xs font-bold <?= $bc[1] ?>"><?= htmlspecialchars($expert['band_name'] ?? 'Unverified') ?></span>
                    <span class="text-gray-600 text-xs">|</span>
                    <span class="text-xs text-gray-400">
                        <?php
                        $trend = $expert['trend_direction'] ?? 'stable';
                        echo match($trend){ 'rising'=>'↑ Rising','declining'=>'↓ Declining',default=>'→ Stable'};
                        ?>
                    </span>
                </div>
            </div>
            <!-- Score ring -->
            <div class="flex flex-col items-center">
                <div class="relative w-28 h-28">
                    <svg class="w-28 h-28" style="transform:rotate(-90deg)" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
                        <circle cx="60" cy="60" r="54" fill="none"
                                stroke="<?= $ringColor ?>" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="<?= $circumference ?>"
                                stroke-dashoffset="<?= $circumference ?>"
                                id="trustRingMain"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-white leading-none" id="trustScoreNum">0</div>
                        <div class="text-[10px] text-gray-500 mt-1">/100</div>
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-2 text-center">
                    <?= round($expert['confidence_score'] ?? 0) ?>% confidence<br>
                    <span class="text-gray-600"><?= (int)($expert['total_sessions'] ?? 0) ?> sessions analysed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SECTION 2: WHY THIS SCORE ── -->
    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 mb-6">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-[#00D4AA]">💡</span> Why is this score <?= round($expert['overall_score'] ?? 0) ?>?
        </h2>
        <p class="text-gray-300 text-sm leading-relaxed"><?= htmlspecialchars($explanation) ?></p>
        <a href="index.php?page=methodology" class="inline-flex items-center gap-1 mt-4 text-[#00D4AA] text-xs font-semibold hover:text-white transition">
            Read full methodology →
        </a>
    </div>

    <!-- ── SECTION 3: SIGNAL BREAKDOWN ── -->
    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 mb-6">
        <h2 class="text-base font-bold text-white mb-5">Trust Signal Breakdown</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <?php
            $dims = [
                ['Outcome Delivery','outcome_score','#00D4AA','30% weight'],
                ['Session Consistency','consistency_score','#3B82F6','20% weight'],
                ['Session Structure','structure_score','#8B5CF6','25% weight'],
                ['Professional Reliability','boundary_score','#F5A623','25% weight'],
            ];
            foreach ($dims as [$label,$col,$color,$weight]):
                $val = round($expert[$col] ?? 0);
            ?>
            <div class="bg-[#080B10] rounded-xl p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-gray-300"><?= $label ?></span>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-gray-600"><?= $weight ?></span>
                        <span class="text-sm font-bold" style="color:<?= $color ?>"><?= $val ?></span>
                    </div>
                </div>
                <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000" style="width:0%;background:<?= $color ?>" data-width="<?= $val ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── SECTION 4: TRUST TIMELINE ── -->
    <?php if (count($history) >= 2): ?>
    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 mb-6">
        <h2 class="text-base font-bold text-white mb-5">Trust Timeline — Last 12 Months</h2>
        <canvas id="trustTimeline" height="80"></canvas>
    </div>
    <?php endif; ?>

    <!-- ── SECTION 5: OUTCOME EVIDENCE ── -->
    <?php if (!empty($sessions)): ?>
    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 mb-6">
        <h2 class="text-base font-bold text-white mb-2">Verified Session History</h2>
        <p class="text-xs text-gray-500 mb-5">Real sessions with real learners. Not testimonials selected by the expert.</p>
        <div class="grid md:grid-cols-2 gap-3">
            <?php foreach ($sessions as $s): ?>
            <div class="bg-[#080B10] rounded-xl p-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-[#00D4AA]/10 border border-[#00D4AA]/20 flex items-center justify-center text-[#00D4AA] text-xs font-bold flex-shrink-0">
                    <?= htmlspecialchars(strtoupper(substr($s['first_name'] ?? 'L',0,1))) ?>
                </div>
                <div>
                    <div class="text-xs font-semibold text-white">
                        <?= htmlspecialchars($s['first_name'] ?? 'Learner') ?> <?= htmlspecialchars($s['last_initial'] ?? '') ?>.
                    </div>
                    <div class="text-[10px] text-gray-500">
                        <?= htmlspecialchars($s['category'] ?? 'Session') ?> ·
                        <?= date('M Y', strtotime($s['session_datetime'] ?? 'now')) ?>
                    </div>
                </div>
                <div class="ml-auto">
                    <span class="text-[10px] text-[#00D4AA] font-bold bg-[#00D4AA]/10 px-2 py-0.5 rounded-full">Completed ✓</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── SECTION 6: BOOK ── -->
    <div class="bg-[#0d131f] border border-[#00D4AA]/25 rounded-2xl p-6">
        <h2 class="text-base font-bold text-white mb-2">Book a Session</h2>
        <p class="text-gray-400 text-sm mb-5">You have seen the evidence. Now book with confidence.</p>
        <a href="?panel=learner&page=booking&expert_id=<?= $expertId ?>"
           class="inline-flex items-center gap-2 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-6 py-3 rounded-xl font-bold text-sm transition shadow-lg">
            Book with <?= htmlspecialchars($expert['name'] ?? 'Expert') ?> →
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Animate score ring
const score  = <?= round($expert['overall_score'] ?? 0) ?>;
const offset = <?= round($offset, 2) ?>;
const ring   = document.getElementById('trustRingMain');
const numEl  = document.getElementById('trustScoreNum');
let current  = 0;

function animateScore() {
    if (current < score) {
        current = Math.min(current + 2, score);
        numEl.textContent = current;
        const pct = current / 100;
        ring.style.strokeDashoffset = <?= $circumference ?> - (pct * <?= $circumference ?>);
        requestAnimationFrame(animateScore);
    }
}
setTimeout(animateScore, 400);

// Animate signal bars
document.querySelectorAll('[data-width]').forEach(b => {
    setTimeout(() => { b.style.width = b.dataset.width + '%'; }, 600);
});

// Trust timeline chart
<?php if (count($history) >= 2): ?>
const ctx = document.getElementById('trustTimeline').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($history,'label')) ?>,
        datasets: [{
            label: 'Trust Score',
            data: <?= json_encode(array_column($history,'avg_score')) ?>,
            borderColor: '#00D4AA',
            backgroundColor: 'rgba(0,212,170,0.08)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#00D4AA',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#6B7280', font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { color: '#6B7280', font: { size: 11 } } }
        }
    }
});
<?php endif; ?>
</script>
</body></html>
