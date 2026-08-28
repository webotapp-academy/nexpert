<?php
/**
 * Terms of Service Page Shell — Task 4.1
 * Route: index.php?page=terms
 */
$base_path  = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';

$page_title = "Terms of Service — Nexpert.ai";
$panel_type = "home";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6">
        Legal & Compliance
    </div>
    <h1 class="text-4xl font-extrabold text-white mb-4">Terms of Service</h1>
    <p class="text-gray-400 text-sm mb-10">Last updated: August 2026</p>

    <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-8 space-y-8 text-gray-300 leading-relaxed text-sm shadow-xl">
        <section>
            <h2 class="text-lg font-bold text-white mb-3">1. Agreement to Terms</h2>
            <p>By accessing or using Nexpert.ai, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, you may not access the service.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">2. Expert Standards & Trust Integrity</h2>
            <p>Experts agree to maintain professional conduct, deliver scheduled sessions faithfully, and uphold ethical boundaries. Attempting to artificially game Trust Scores via fraudulent bookings or falsified outcomes will result in immediate permanent account suspension and score freeze.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">3. Payments & Escrow Protection</h2>
            <p>Payments for sessions, webinars, and specialized outcome workflows are held securely until milestone delivery is completed in accordance with platform guarantee policies.</p>
        </section>

        <section>
            <h2 class="text-lg font-bold text-white mb-3">4. Limitation of Liability</h2>
            <p>Nexpert facilitates mentorship and advisory engagements. We provide mathematical behavioral credibility scoring but do not guarantee individual career employment outcomes.</p>
        </section>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
