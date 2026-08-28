<?php
/* For Enterprise landing page
 * Route: index.php?page=for-enterprise
 * Include this file from your main router when $page === 'for-enterprise'
 */
$base_path  = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';

$page_title = "For Enterprise — Nexpert Trust Intelligence";
$panel_type = "home";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';

$success = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';
    $company = trim($_POST['company_name'] ?? '');
    $contact = trim($_POST['contact_name'] ?? '');
    $role    = trim($_POST['role'] ?? '');
    $size    = trim($_POST['company_size'] ?? '');
    $count   = (int)($_POST['expert_count'] ?? 0);
    $problem = trim($_POST['problem_text'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');

    if ($company && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $pdo->prepare("INSERT INTO enterprise_leads (company_name,contact_name,role,company_size,expert_count,problem_text,email,phone,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())")
                ->execute([$company,$contact,$role,$size,$count,$problem,$email,$phone]);
            $success = true;
            @mail('enterprise@nexpertapp.com',"New Enterprise Lead: {$company}","Company: {$company}\nContact: {$contact}\nRole: {$role}\nEmail: {$email}\nPhone: {$phone}\nExperts: {$count}\nProblem: {$problem}","From: noreply@nexpertapp.com");
        } catch (Exception $e) { $error = 'Something went wrong. Please email enterprise@nexpertapp.com directly.'; }
    } else { $error = 'Please provide your company name and a valid work email.'; }
}
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white";</script>

<section class="py-24 bg-[#080B10] relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#00D4AA]/3 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest mb-8">Enterprise Trust Intelligence</div>
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6 leading-[1.1]">You spend crores on experts.<br><span class="text-[#00D4AA]">Do you know which ones to trust?</span></h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto mb-10 leading-relaxed">Enterprise organizations lose an average of ₹2.2 crore annually to wrong expert choices — not from bad intentions, but from no verified evidence. Nexpert builds the trust infrastructure that changes this.</p>
        <a href="#audit-form" class="inline-flex items-center gap-2 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-8 py-4 rounded-xl font-bold text-base transition shadow-lg">Get a Free Trust Intelligence Audit →</a>
        <p class="text-gray-600 text-sm mt-4">Free for the first 5 organizations. No commitment.</p>
    </div>
</section>

<section class="py-20 bg-[#0A0D15] border-t border-gray-900">
    <div class="max-w-5xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-white text-center mb-12">The expert selection problem nobody solves.</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach([['₹2.2Cr','Average annual loss from wrong expert choices in mid-size enterprises'],['73%','L&D leaders who cannot objectively verify an external expert\'s quality before engaging'],['4.7 months','Average time wasted before realising an expert engagement is failing']] as [$s,$d]): ?>
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 text-center">
                <div class="text-4xl font-black text-[#00D4AA] mb-3"><?= $s ?></div>
                <p class="text-gray-400 text-sm leading-relaxed"><?= $d ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-20 bg-[#080B10] border-t border-gray-900">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">How the Trust Intelligence Audit works</h2>
        <p class="text-gray-400 mb-12 max-w-xl mx-auto">We evaluate your current expert vendors. No platform integration needed. You receive intelligence. You make better decisions.</p>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach([['01','Share your roster','Give us 10–20 external trainers, coaches, or consultants you use. Names and LinkedIn profiles is enough.'],['02','We build Trust Scores','Over 30 days we compute a Trust Intelligence Report for each using behavioral signals, verified credentials, and outcome evidence.'],['03','Receive full intelligence','A Trust Score, signal breakdown, confidence level, and risk flag per vendor. You decide how to reallocate budget.']] as [$n,$t,$b]): ?>
            <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-6 text-left">
                <div class="text-[#00D4AA] font-mono text-sm font-bold mb-4"><?= $n ?></div>
                <h3 class="text-base font-bold text-white mb-3"><?= $t ?></h3>
                <p class="text-gray-400 text-sm leading-relaxed"><?= $b ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="audit-form" class="py-20 bg-[#0A0D15] border-t border-gray-900">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-white mb-4">Request Your Free Audit</h2>
            <p class="text-gray-400">Free for first 5 organizations. We respond within 48 hours.</p>
        </div>
        <?php if ($success): ?>
        <div class="bg-[#00D4AA]/10 border border-[#00D4AA]/30 rounded-2xl p-10 text-center">
            <div class="text-5xl mb-4">✓</div>
            <h3 class="text-xl font-bold text-white mb-2">Request received.</h3>
            <p class="text-gray-400">We will be in touch within 48 hours.</p>
        </div>
        <?php else: ?>
        <?php if ($error): ?><div class="bg-red-900/20 border border-red-800 rounded-xl p-4 mb-6 text-red-400 text-sm"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" class="bg-[#0d131f] border border-gray-800 rounded-2xl p-8 space-y-5">
            <div class="grid md:grid-cols-2 gap-5">
                <?php $inp="w-full px-4 py-3 bg-[#080B10] border border-gray-700 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-[#00D4AA]/50 text-sm transition"; ?>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Company Name *</label><input type="text" name="company_name" required placeholder="Acme Corp" class="<?= $inp ?>"></div>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Your Name</label><input type="text" name="contact_name" placeholder="Priya Sharma" class="<?= $inp ?>"></div>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Your Role</label><input type="text" name="role" placeholder="Chief Learning Officer" class="<?= $inp ?>"></div>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Company Size</label>
                    <select name="company_size" class="<?= $inp ?>">
                        <option value="">Select</option>
                        <?php foreach(['100–500 employees','500–2,000 employees','2,000–10,000 employees','10,000+ employees'] as $o): ?><option><?= $o ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Work Email *</label><input type="email" name="email" required placeholder="priya@company.com" class="<?= $inp ?>"></div>
                <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Phone</label><input type="tel" name="phone" placeholder="+91 98765 43210" class="<?= $inp ?>"></div>
            </div>
            <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">External experts currently engaged (approx.)</label><input type="number" name="expert_count" min="1" placeholder="12" class="<?= $inp ?>"></div>
            <div><label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Describe a recent expert engagement that underdelivered</label><textarea name="problem_text" rows="4" placeholder="What happened? What were you expecting? What was the approximate cost?" class="<?= $inp ?> resize-none"></textarea></div>
            <button type="submit" class="w-full bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] py-4 rounded-xl font-bold text-base transition">Request Free Trust Intelligence Audit →</button>
            <p class="text-gray-600 text-xs text-center">No platform required. No commitment. We audit your vendors and deliver a full Trust Intelligence Report.</p>
        </form>
        <?php endif; ?>
    </div>
</section>
</body></html>
