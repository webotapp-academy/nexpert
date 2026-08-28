<?php
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'expert') {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth'); exit;
}
$expertId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT ep.full_name AS name, ep.category, ep.experience_years, ep.profile_photo,
           ts.overall_score, ts.band_name, ts.confidence_score, ts.trend_direction,
           ts.structure_score, ts.outcome_score, ts.boundary_score, ts.consistency_score,
           ts.last_updated
    FROM expert_profiles ep
    LEFT JOIN trust_state ts ON ep.user_id = ts.expert_id
    WHERE ep.user_id = ?
");
$stmt->execute([$expertId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) { echo "Profile not found."; exit; }

$score  = round($data['overall_score'] ?? 0, 1);
$band   = $data['band_name'] ?? 'Unverified';
$certNo = 'NTS-2026-' . str_pad($expertId, 5, '0', STR_PAD_LEFT);
$issued = $data['last_updated'] ? date('F j, Y', strtotime($data['last_updated'])) : date('F j, Y');
$profileUrl = rtrim(BASE_URL ?? 'https://nexpertapp.com', '/') . '/index.php?panel=learner&page=expert-trust-report&expert_id=' . $expertId;
$shareText = urlencode("I just received my Nexpert Trust Score: {$score} — {$band}.\n\nThis score is computed from my actual behavioral record — sessions delivered, learner outcomes achieved, consistency over time. Not a rating. Not a testimonial. Evidence.\n\nSee the full methodology: https://nexpertapp.com/index.php?page=methodology\n\n#TrustIntelligence #Nexpert");

$page_title  = "Trust Certificate — Nexpert.ai";
$panel_type  = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white";</script>
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-white">Your Trust Certificate</h1>
        <div class="flex gap-3">
            <button onclick="downloadCert()" class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-xl text-sm font-bold hover:bg-[#00bfa0] transition">⬇ Download PNG</button>
            <button onclick="copyShare()" class="border border-gray-700 text-gray-300 px-4 py-2 rounded-xl text-sm font-semibold hover:text-white transition">📋 Copy Share Text</button>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($profileUrl) ?>" target="_blank"
               class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">LinkedIn</a>
        </div>
    </div>

    <!-- CERTIFICATE CARD -->
    <div id="certificate" class="bg-[#0A0D15] border border-[#00D4AA]/30 rounded-2xl p-8" style="font-family:'Inter',sans-serif">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-[#00D4AA] rounded-xl flex items-center justify-center font-extrabold text-[#080B10] text-lg">N</div>
                <div>
                    <div class="text-white font-bold text-sm">Nexpert.ai</div>
                    <div class="text-gray-500 text-xs">Trust Intelligence Certificate</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-gray-600 text-xs font-mono"><?= htmlspecialchars($certNo) ?></div>
                <div class="text-gray-600 text-xs">Issued: <?= $issued ?></div>
            </div>
        </div>

        <!-- Band badge -->
        <div class="flex items-center gap-3 bg-[#00D4AA]/8 border border-[#00D4AA]/20 rounded-xl px-4 py-3 mb-6">
            <span class="text-xl">
                <?= match($band){ 'Sovereign'=>'👑','Established'=>'✦','Verified'=>'◈','Emerging'=>'◎',default=>'○'} ?>
            </span>
            <div>
                <div class="text-[#00D4AA] font-bold text-sm"><?= htmlspecialchars($band) ?> Expert — Verified</div>
                <div class="text-gray-500 text-xs">Computed from behavioral signals, not self-reported claims</div>
            </div>
        </div>

        <!-- Score + bars -->
        <div class="flex items-center gap-8 mb-6">
            <div class="text-center flex-shrink-0">
                <div class="text-6xl font-black text-white leading-none"><?= $score ?></div>
                <div class="text-gray-500 text-xs mt-1">Trust Score / 100</div>
            </div>
            <div class="flex-1 space-y-2">
                <?php foreach([
                    ['Outcomes',    'outcome_score',     '#00D4AA'],
                    ['Consistency', 'consistency_score', '#3B82F6'],
                    ['Structure',   'structure_score',   '#8B5CF6'],
                    ['Reliability', 'boundary_score',    '#F5A623'],
                ] as [$lbl,$col,$clr]): $v=round($data[$col]??0); ?>
                <div class="flex items-center gap-2">
                    <div class="text-[10px] text-gray-500 w-20 font-mono uppercase"><?= $lbl ?></div>
                    <div class="flex-1 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:<?= $v ?>%;background:<?= $clr ?>"></div>
                    </div>
                    <div class="text-[10px] text-gray-400 w-6 text-right"><?= $v ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Expert identity -->
        <div class="flex items-center justify-between border-t border-gray-800 pt-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#00D4AA]/10 border border-[#00D4AA]/20 flex items-center justify-center text-[#00D4AA] font-bold text-sm">
                    <?= strtoupper(substr($data['name'] ?? 'E', 0, 1)) ?>
                </div>
                <div>
                    <div class="text-white font-semibold text-sm"><?= htmlspecialchars($data['name'] ?? '') ?></div>
                    <div class="text-gray-500 text-xs"><?= htmlspecialchars($data['category'] ?? '') ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- QR code via Google Charts -->
                <img src="https://chart.googleapis.com/chart?chs=56x56&cht=qr&chl=<?= urlencode($profileUrl) ?>&choe=UTF-8"
                     alt="QR" class="w-14 h-14 rounded opacity-70">
                <div class="text-[#00D4AA] text-xs font-bold flex items-center gap-1">
                    <div class="w-4 h-4 bg-[#00D4AA] rounded-full flex items-center justify-center">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="#080B10" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    Nexpert Verified
                </div>
            </div>
        </div>
    </div>

    <p class="text-gray-600 text-xs text-center mt-4">
        Scan the QR code to view the full Trust Intelligence Report for this expert.
        Certificate validity: continuously updated as new trust events are processed.
    </p>
</div>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
const shareText = `I just received my Nexpert Trust Score: <?= $score ?> — <?= $band ?>.

This score is computed from my actual behavioral record — sessions delivered, learner outcomes achieved, consistency over time. Not a rating. Not a testimonial. Evidence.

See the full methodology: https://nexpertapp.com/index.php?page=methodology

#TrustIntelligence #Nexpert #<?= $band ?>Expert`;

function downloadCert(){
    html2canvas(document.getElementById('certificate'),{backgroundColor:'#0A0D15',scale:2}).then(canvas=>{
        const a=document.createElement('a');
        a.download='nexpert-trust-certificate-<?= $certNo ?>.png';
        a.href=canvas.toDataURL('image/png');
        a.click();
    });
}
function copyShare(){
    navigator.clipboard.writeText(shareText).then(()=>{
        const btn=event.target;const orig=btn.textContent;
        btn.textContent='✓ Copied!';btn.classList.add('text-[#00D4AA]');
        setTimeout(()=>{btn.textContent=orig;btn.classList.remove('text-[#00D4AA]');},2000);
    });
}
</script>
</body></html>
