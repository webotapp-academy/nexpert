<?php
/* Methodology Page Shell
 * Route: index.php?page=methodology
 * 7 Section placeholders, sidebar navigation, download PDF button, version & changelog table.
 */
$base_path  = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';

$page_title = "Trust Methodology & Framework — Nexpert.ai";
$panel_type = "home";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>document.body.className="bg-[#080B10] min-h-screen text-white antialiased";</script>

<!-- HERO HEADER -->
<section class="py-16 bg-[#080B10] border-b border-gray-900 relative overflow-hidden">
    <div class="absolute top-0 right-1/4 w-[600px] h-[300px] bg-[#00D4AA]/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6">
            <span class="w-1.5 h-1.5 bg-[#00D4AA] rounded-full animate-pulse"></span>
            Version 2.0 MVP2 Specification
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
                    Trust Intelligence <span class="text-[#00D4AA]">Methodology</span>
                </h1>
                <p class="text-gray-400 text-base md:text-lg max-w-2xl leading-relaxed">
                    How Nexpert objectively measures expert credibility, evaluates behavioral outcomes, and prevents reputation gaming through multi-signal mathematical aggregation.
                </p>
            </div>
            <div class="flex-shrink-0">
                <button onclick="window.print()" class="inline-flex items-center gap-2 bg-[#0d131f] hover:bg-[#131b2e] border border-gray-700 hover:border-[#00D4AA]/40 text-white px-5 py-3 rounded-xl font-bold text-sm shadow-md transition">
                    <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Methodology PDF
                </button>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT WITH SIDEBAR -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-12">
        
        <!-- STICKY SIDEBAR NAVIGATION -->
        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-[#0d131f] border border-gray-800 rounded-2xl p-5 shadow-lg">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Table of Contents</h3>
                    <nav class="space-y-2">
                        <a href="#section-1" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">1. Trust Philosophy & Core Formula</a>
                        <a href="#section-2" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">2. Exponential Moving Average (EMA)</a>
                        <a href="#section-3" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">3. The 4 Behavioral Dimensions</a>
                        <a href="#section-4" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">4. 5-Tier Trust Bands</a>
                        <a href="#section-5" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">5. Confidence & Stability Scoring</a>
                        <a href="#section-6" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">6. Anti-Gaming & Freeze Protections</a>
                        <a href="#section-7" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">7. Outcome Validation Protocol</a>
                        <a href="#section-versions" class="block text-sm text-gray-300 hover:text-[#00D4AA] transition font-medium py-1">8. Version & Changelog</a>
                    </nav>
                </div>
                
                <div class="bg-gradient-to-br from-[#0d131f] to-[#131b2e] border border-gray-800 rounded-2xl p-5 text-center">
                    <p class="text-xs text-gray-400 mb-3">Want an enterprise audit of your external vendors?</p>
                    <a href="index.php?page=for-enterprise" class="inline-block bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#00bfa0] transition">Explore For Enterprise →</a>
                </div>
            </div>
        </aside>

        <!-- MAIN METHODOLOGY SECTIONS -->
        <main class="lg:col-span-3 space-y-16">

            <!-- SECTION 1 -->
            <section id="section-1" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 01</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">1. Trust Philosophy & Core Formula</h2>
                <p class="text-gray-300 leading-relaxed mb-6">
                    Conventional platforms rely on static 5-star ratings, which suffer from severe inflation (94% of ratings cluster between 4.8 and 5.0) and vulnerability to review collusion. Nexpert replaces subjective star reviews with an evidence-based behavioral framework.
                </p>
                <div class="bg-[#080B10] border border-gray-800 rounded-xl p-5 font-mono text-sm text-[#00D4AA] mb-6">
                    Trust Score = α · Signal_Current + (1 - α) · Score_Historical
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Where <code class="text-emerald-400">α = 0.3</code>, allowing an expert's score to evolve dynamically while maintaining long-term stability and resilience against single-session anomalies.
                </p>
            </section>

            <!-- SECTION 2 -->
            <section id="section-2" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 02</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">2. Exponential Moving Average (EMA) & Signal Decay</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    Trust is temporal. An expert who delivered exceptional outcomes two years ago but is currently disengaged should not maintain identical standing to an actively engaged practitioner.
                </p>
                <div class="grid sm:grid-cols-2 gap-4 my-6">
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800">
                        <h4 class="text-white font-bold text-sm mb-1">Recent Signals (0–90 Days)</h4>
                        <p class="text-gray-400 text-xs">Weighted at 100% full value to reflect active demonstrated capability.</p>
                    </div>
                    <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800">
                        <h4 class="text-white font-bold text-sm mb-1">Aged Signals (>90 Days)</h4>
                        <p class="text-gray-400 text-xs">Weighted at 60% decay value to encourage continuous consistency over time.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION 3 -->
            <section id="section-3" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 03</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">3. The 4 Behavioral Dimensions</h2>
                <p class="text-gray-300 leading-relaxed mb-6">
                    Every learner-expert interaction emits raw behavioral events analyzed by specialized AI agents across four orthogonal dimensions:
                </p>
                <div class="space-y-4">
                    <div class="p-4 bg-[#080B10] border-l-4 border-emerald-500 rounded-r-xl">
                        <h4 class="text-white font-bold text-base">Structure (25%)</h4>
                        <p class="text-gray-400 text-xs mt-1">Clarity of curriculum, adherence to session agendas, actionable milestones, and resource preparedness.</p>
                    </div>
                    <div class="p-4 bg-[#080B10] border-l-4 border-blue-500 rounded-r-xl">
                        <h4 class="text-white font-bold text-base">Outcome (35%)</h4>
                        <p class="text-gray-400 text-xs mt-1">Tangible learner progress, verifiable certifications, career transitions, and goal milestone completion.</p>
                    </div>
                    <div class="p-4 bg-[#080B10] border-l-4 border-amber-500 rounded-r-xl">
                        <h4 class="text-white font-bold text-base">Boundary (20%)</h4>
                        <p class="text-gray-400 text-xs mt-1">Punctuality, respectful communication, scope compliance, and ethical interaction standards.</p>
                    </div>
                    <div class="p-4 bg-[#080B10] border-l-4 border-purple-500 rounded-r-xl">
                        <h4 class="text-white font-bold text-base">Consistency (20%)</h4>
                        <p class="text-gray-400 text-xs mt-1">Reliability across multiple cohorts, low cancellation rates, and sustained quality over repeat sessions.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION 4 -->
            <section id="section-4" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 04</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">4. 5-Tier Trust Bands</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-400 border border-gray-800 rounded-xl overflow-hidden">
                        <thead class="bg-[#080B10] text-gray-200 uppercase text-xs">
                            <tr>
                                <th class="p-3 border-b border-gray-800">Band Name</th>
                                <th class="p-3 border-b border-gray-800">Score Range</th>
                                <th class="p-3 border-b border-gray-800">Minimum Confidence</th>
                                <th class="p-3 border-b border-gray-800">Significance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-[#0d131f]">
                            <tr>
                                <td class="p-3 font-bold text-emerald-400">Sovereign</td>
                                <td class="p-3 font-mono text-white">90.0 – 100.0</td>
                                <td class="p-3">70%</td>
                                <td class="p-3 text-xs">Industry-defining mastery and highest verified outcome rate.</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-blue-400">Established</td>
                                <td class="p-3 font-mono text-white">75.0 – 89.9</td>
                                <td class="p-3">50%</td>
                                <td class="p-3 text-xs">Proven track record with consistent learner success.</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-teal-400">Verified</td>
                                <td class="p-3 font-mono text-white">60.0 – 74.9</td>
                                <td class="p-3">30%</td>
                                <td class="p-3 text-xs">Validated baseline quality and verified credentials.</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-amber-400">Emerging</td>
                                <td class="p-3 font-mono text-white">40.0 – 59.9</td>
                                <td class="p-3">10%</td>
                                <td class="p-3 text-xs">New experts building an initial portfolio of evidence.</td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-gray-400">Unverified</td>
                                <td class="p-3 font-mono text-white">0.0 – 39.9</td>
                                <td class="p-3">0%</td>
                                <td class="p-3 text-xs">Pending onboarding or insufficient interaction volume.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SECTION 5 -->
            <section id="section-5" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 05</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">5. Confidence & Stability Scoring</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    A score with 2 completed sessions carries different statistical weight than a score with 100 sessions. Nexpert calculates real data completeness:
                </p>
                <div class="bg-[#080B10] p-4 rounded-xl border border-gray-800 text-xs text-gray-300 space-y-2">
                    <p>• <strong>Confidence Score (%):</strong> Measures signal density relative to the target expected signals (target = 20 signals for 100% confidence).</p>
                    <p>• <strong>Stability Score (%):</strong> Computes historical variance across snapshot history. High stability indicates low volatility.</p>
                    <p>• <strong>Trend Direction:</strong> Computes the trajectory (<code>rising</code>, <code>stable</code>, or <code>declining</code>) based on recent score deltas.</p>
                </div>
            </section>

            <!-- SECTION 6 -->
            <section id="section-6" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 06</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">6. Anti-Gaming Protections & Freeze State</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    To prevent malicious rating manipulation, sybil attacks, or sudden performance irregularities:
                </p>
                <ul class="list-disc list-inside space-y-2 text-gray-400 text-sm">
                    <li><strong>Freeze State (<code class="text-red-400">is_frozen = 1</code>):</strong> Temporarily locks an expert's score calculation during audit reviews or active dispute investigation.</li>
                    <li><strong>Sybil Detection:</strong> Repeat bookings from identical IPs or linked payment accounts have diminished signal weighting.</li>
                    <li><strong>Deterministic Fallback:</strong> If third-party AI APIs become unresponsive, deterministic fallback scoring ensures uninterrupted platform stability.</li>
                </ul>
            </section>

            <!-- SECTION 7 -->
            <section id="section-7" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 07</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">7. Outcome Validation Protocol</h2>
                <p class="text-gray-300 leading-relaxed mb-4">
                    Outcomes represent the core pillar of educational trust. Nexpert validates outcomes through a tripartite protocol:
                </p>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-[#080B10] rounded-xl border border-gray-800 text-center">
                        <div class="text-xl font-bold text-[#00D4AA] mb-1">1. Self-Report</div>
                        <p class="text-gray-400 text-xs">Learner submits evidence URL or milestone completion proof.</p>
                    </div>
                    <div class="p-4 bg-[#080B10] rounded-xl border border-gray-800 text-center">
                        <div class="text-xl font-bold text-[#00D4AA] mb-1">2. AI Verification</div>
                        <p class="text-gray-400 text-xs">Automated parsing of artifact links and session transcripts.</p>
                    </div>
                    <div class="p-4 bg-[#080B10] rounded-xl border border-gray-800 text-center">
                        <div class="text-xl font-bold text-[#00D4AA] mb-1">3. Admin Audit</div>
                        <p class="text-gray-400 text-xs">Human-in-the-loop review for high-impact certifications.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION 8: VERSION & CHANGELOG -->
            <section id="section-versions" class="scroll-mt-28 bg-[#0d131f] border border-gray-800/80 rounded-2xl p-8 shadow-xl">
                <div class="inline-block text-[#00D4AA] text-xs font-bold uppercase tracking-wider mb-2">Section 08</div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">8. Version & Changelog</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-400 border border-gray-800 rounded-xl overflow-hidden">
                        <thead class="bg-[#080B10] text-gray-200 uppercase text-xs">
                            <tr>
                                <th class="p-3 border-b border-gray-800">Version</th>
                                <th class="p-3 border-b border-gray-800">Release Date</th>
                                <th class="p-3 border-b border-gray-800">Key Additions & Changes</th>
                                <th class="p-3 border-b border-gray-800">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 bg-[#0d131f]">
                            <tr>
                                <td class="p-3 font-bold text-white font-mono">v2.0 MVP2</td>
                                <td class="p-3 text-xs">August 2026</td>
                                <td class="p-3 text-xs">5-band mapping, 90-day signal decay, trust notification queue, real confidence & stability scoring.</td>
                                <td class="p-3"><span class="px-2 py-0.5 bg-emerald-950/60 border border-emerald-800 text-emerald-400 text-[10px] font-bold rounded-lg">CURRENT</span></td>
                            </tr>
                            <tr>
                                <td class="p-3 font-bold text-gray-300 font-mono">v1.0 MVP1</td>
                                <td class="p-3 text-xs">May 2026</td>
                                <td class="p-3 text-xs">Initial 3-tier EMA aggregator with basic session signal extraction.</td>
                                <td class="p-3"><span class="px-2 py-0.5 bg-gray-900 border border-gray-800 text-gray-500 text-[10px] rounded-lg">SUPERSEDED</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
