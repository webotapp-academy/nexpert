<?php
/**
 * Privacy Policy Page Shell — Task 4.1
 * Route: index.php?page=privacy-policy
 */
$base_path  = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';

$page_title = "Privacy Policy — Nexpert.ai";
$panel_type = "home";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6">
        Legal & Compliance
    </div>
    <h1 class="text-4xl font-extrabold text-white mb-4">Privacy Policy</h1>
    <p class="text-gray-400 text-sm mb-10">Last updated: August 2026</p>

    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-8 space-y-8 text-gray-300 leading-relaxed text-sm shadow-xl">
        <section>
            <h2 class="text-lg font-bold text-white mb-3">1. Information We Collect</h2>
            <p>We collect information you provide directly to us when you create an account, complete onboarding, book expert sessions, submit verified outcomes, or interact with our Trust Intelligence platform.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">2. How We Use Behavioral Data</h2>
            <p>Nexpert uses interaction logs (session completion, attendance punctuality, learner milestone submissions) to compute verifiable Trust Scores using Exponential Moving Average algorithms. We do not sell your personal data.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">3. AI Signal Extraction & Privacy</h2>
            <p>Our autonomous agent architecture extracts structural and behavioral signals from sessions without retaining private conversation content or raw payment card details.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">4. Data Retention & User Rights</h2>
            <p>You may request export or deletion of your account and personal identifiers at any time by contacting <a href="mailto:privacy@nexpertapp.com" class="text-[#00D4AA] hover:underline">privacy@nexpertapp.com</a>.</p>
        </section>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
