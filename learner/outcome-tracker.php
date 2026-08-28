<?php
/**
 * Learner Outcome Tracker — Task 3.2
 * Route: index.php?panel=learner&page=outcome-tracker
 * 5-Step flow to report learning outcomes, submit verifiable evidence, and track past achievements.
 */
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'learner') {
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}
$learnerId = (int)$_SESSION['user_id'];

// Fetch learner's completed sessions / experts
$sessStmt = $pdo->prepare("
    SELECT b.id AS booking_id, b.expert_id, b.session_datetime, ep.full_name AS expert_name, ep.category, ep.profile_photo
    FROM bookings b
    INNER JOIN expert_profiles ep ON b.expert_id = ep.user_id
    WHERE b.learner_id = ? AND b.status IN ('completed', 'confirmed')
    ORDER BY b.session_datetime DESC
");
$sessStmt->execute([$learnerId]);
$sessions = $sessStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch previously recorded outcomes
$outStmt = $pdo->prepare("
    SELECT o.*, ep.full_name AS expert_name
    FROM outcomes o
    LEFT JOIN expert_profiles ep ON o.expert_id = ep.user_id
    WHERE o.learner_id = ?
    ORDER BY o.created_at DESC
");
$outStmt->execute([$learnerId]);
$outcomes = $outStmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Outcome Tracker — Nexpert.ai";
$panel_type = "learner";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-800">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                Verified Evidence Protocol
            </div>
            <h1 class="text-3xl font-extrabold text-white">Learner Outcome Tracker</h1>
            <p class="text-gray-400 text-sm mt-1">Submit verifiable learning milestones and strengthen your expert's behavioral credibility profile.</p>
        </div>
        <div>
            <a href="#submit-section" class="bg-[#00D4AA] text-[#080B10] px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-[#00bda0] transition shadow-lg inline-block">
                + Report New Outcome
            </a>
        </div>
    </div>

    <!-- 5-Step Submission Flow Card -->
    <div id="submit-section" class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 sm:p-8 shadow-xl mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Submit Verified Learning Milestone</h2>
            <span class="text-xs text-gray-500 font-mono">5-Step Verification</span>
        </div>

        <form id="outcomeForm" class="space-y-6">
            <!-- Step 1: Pick Completed Session -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Step 1 — Select Completed Session / Expert *</label>
                <?php if (empty($sessions)): ?>
                <div class="p-4 bg-[#080B10] border border-gray-800 rounded-xl text-xs text-gray-400">
                    No completed sessions found. You can still report an outcome for an expert you engaged with:
                    <select name="expert_id" required class="mt-2 w-full bg-[#131b2e] border border-gray-700 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                        <option value="">Select Expert...</option>
                        <?php
                        $allExp = $pdo->query("SELECT user_id, full_name, category FROM expert_profiles LIMIT 20")->fetchAll();
                        foreach ($allExp as $exp): ?>
                        <option value="<?= $exp['user_id'] ?>"><?= htmlspecialchars($exp['full_name']) ?> (<?= htmlspecialchars($exp['category']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <select name="session_id" id="sessionSelect" required class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
                    <option value="">Select a completed session...</option>
                    <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s['booking_id'] ?>" data-expert="<?= $s['expert_id'] ?>">
                        <?= htmlspecialchars($s['expert_name']) ?> — <?= htmlspecialchars($s['category']) ?> (<?= date('M j, Y', strtotime($s['session_datetime'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="expert_id" id="hiddenExpertId" value="">
                <?php endif; ?>
            </div>

            <!-- Step 2: Milestone Category -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Step 2 — Outcome Category *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <?php 
                    $categories = [
                        'Career Transition' => 'Role promotion, job offer, or industry transition',
                        'Skill Certification' => 'Completed AWS, GCP, PMP or professional cert',
                        'Project Shipped' => 'Deployed MVP, software release, or company launch',
                        'Knowledge Mastery' => 'Deep mastery of framework, algorithm, or architecture',
                        'Interview Cleared' => 'Successfully passed technical/system design rounds',
                        'Problem Resolved' => 'Solved critical technical blocker or strategy issue'
                    ];
                    $c_idx = 0;
                    foreach ($categories as $cat => $desc): 
                        $c_idx++;
                    ?>
                    <label class="p-3 bg-[#080B10] border border-gray-800 rounded-xl cursor-pointer hover:border-[#00D4AA]/40 transition flex flex-col justify-between">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="radio" name="outcome_type" value="<?= htmlspecialchars($cat) ?>" <?= $c_idx === 1 ? 'checked' : '' ?> class="accent-[#00D4AA]">
                            <span class="text-xs font-bold text-white"><?= htmlspecialchars($cat) ?></span>
                        </div>
                        <span class="text-[10px] text-gray-500"><?= htmlspecialchars($desc) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Step 3: Detailed Description -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Step 3 — Describe Your Accomplishment *</label>
                <textarea name="description" required rows="3" placeholder="What specific result did you achieve? (e.g. Cleared Senior PM interview at Stripe with expert's system design roadmap...)" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]"></textarea>
            </div>

            <!-- Step 4: Verifiable Evidence Link -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Step 4 — Evidence Link (Optional but boosts validation)</label>
                <input type="url" name="evidence_url" placeholder="https://github.com/username/project or https://linkedin.com/in/post-url" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-sm focus:outline-none focus:border-[#00D4AA]">
            </div>

            <!-- Step 5: Submit -->
            <div class="pt-4 border-t border-gray-800/80 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">Step 5: Review & submit. This milestone is written to the behavioral audit ledger.</p>
                <button type="submit" id="submitOutcomeBtn" class="bg-[#00D4AA] hover:bg-[#00bda0] text-[#080B10] px-6 py-3 rounded-xl font-bold text-sm transition shadow-lg">
                    Submit Verified Milestone →
                </button>
            </div>
        </form>
    </div>

    <!-- Previously Recorded Outcomes List -->
    <div>
        <h2 class="text-xl font-bold text-white mb-6">Your Recorded Milestones & Outcomes</h2>
        <?php if (empty($outcomes)): ?>
        <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-12 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-base font-bold text-gray-300">No milestones reported yet</p>
            <p class="text-xs text-gray-500 mt-1">Use the form above to record career and skill achievements gained from your sessions.</p>
        </div>
        <?php else: ?>
        <div class="grid sm:grid-cols-2 gap-4">
            <?php foreach ($outcomes as $out): ?>
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-5 shadow-lg flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-950/50 border border-emerald-800 text-emerald-400">
                            <?= htmlspecialchars($out['outcome_type']) ?>
                        </span>
                        <span class="text-[10px] text-gray-500"><?= date('M j, Y', strtotime($out['created_at'])) ?></span>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-1">With <?= htmlspecialchars($out['expert_name'] ?: 'Expert') ?></h3>
                    <p class="text-xs text-gray-400 leading-relaxed"><?= htmlspecialchars($out['description']) ?></p>
                </div>
                <?php if (!empty($out['evidence_url'])): ?>
                <div class="mt-4 pt-3 border-t border-gray-800/60">
                    <a href="<?= htmlspecialchars($out['evidence_url']) ?>" target="_blank" class="text-[11px] text-[#00D4AA] hover:underline flex items-center gap-1">
                        <span>View Evidence URL</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const sessionSelect = document.getElementById('sessionSelect');
const hiddenExpert = document.getElementById('hiddenExpertId');
if (sessionSelect) {
    sessionSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        hiddenExpert.value = opt.getAttribute('data-expert') || '';
    });
}

document.getElementById('outcomeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitOutcomeBtn');
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    const formData = new FormData(this);
    const payload = {};
    formData.forEach((val, key) => payload[key] = val);

    try {
        const res = await fetch('admin-panel/apis/outcomes/submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✓ Milestone verified! Trust event emitted for expert score aggregation.');
            window.location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to submit outcome'));
        }
    } catch (err) {
        console.error(err);
        alert('Network error submitting outcome.');
    } finally {
        btn.disabled = false;
        btn.textContent = origText;
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
