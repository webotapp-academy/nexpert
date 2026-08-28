<?php
// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';
require_once 'admin-panel/apis/connection/pdo.php';

$page_title = "For Experts - Nexpert.ai";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';

// Fetch live platform metrics
$approvedExpertsCount = 0;
$totalVerifiedOutcomes = 0;
$totalTrustEvents = 0;
$topExpert = null;

try {
    $approvedExpertsCount = (int)$pdo->query("SELECT COUNT(*) FROM expert_profiles WHERE verification_status = 'approved'")->fetchColumn();
    $totalVerifiedOutcomes = (int)$pdo->query("SELECT COUNT(*) FROM trust_events WHERE event_type = 'outcome_achieved'")->fetchColumn();
    $totalTrustEvents = (int)$pdo->query("SELECT COUNT(*) FROM trust_events")->fetchColumn();
    
    // Fetch top scored expert for the public profile spotlight
    $stmt = $pdo->query("
        SELECT ep.full_name, ep.tagline, ep.profile_photo,
               ts.overall_score, ts.band_name, ts.confidence_score, ts.stability_score,
               ts.structure_score, ts.outcome_score, ts.boundary_score, ts.consistency_score
        FROM expert_profiles ep
        JOIN trust_state ts ON ep.user_id = ts.expert_id
        WHERE ep.verification_status = 'approved' AND ts.overall_score > 0
        ORDER BY ts.overall_score DESC
        LIMIT 1
    ");
    $topExpert = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching stats in for-experts.php: " . $e->getMessage());
}

$displayExperts = max(1, $approvedExpertsCount);
$displayOutcomes = max(1, $totalVerifiedOutcomes);
$displayEvents = max(1, $totalTrustEvents);

$spotlightName = $topExpert['full_name'] ?? 'Paban bhuyan';
$spotlightTagline = $topExpert['tagline'] ?? 'Software & Architecture Practitioner';
$spotlightScore = round((float)($topExpert['overall_score'] ?? 74.8), 1);
$spotlightBand = $topExpert['band_name'] ?? 'Verified';
$spotlightStability = round((float)($topExpert['stability_score'] ?? 94), 0);
$spotlightOutcome = round((float)($topExpert['outcome_score'] ?? 77.9), 0);
$spotlightConsistency = round((float)($topExpert['consistency_score'] ?? 75.4), 0);
$spotlightStructure = round((float)($topExpert['structure_score'] ?? 69.5), 0);
$spotlightBoundary = round((float)($topExpert['boundary_score'] ?? 76.4), 0);
?>

<div class="bg-white min-h-screen text-gray-900 relative overflow-hidden">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left Hero Content -->
            <div class="space-y-7">
                <!-- Heading: 2 lines like the design -->
                <h1 class="text-5xl lg:text-[62px] font-black leading-[1.15] tracking-tight">
                    <span class="text-gray-900">Build your </span><span class="text-[#00A87E]">reputation.</span><br>
                    <span class="text-gray-900">Grow your </span><span class="text-[#00A87E]">impact.</span>
                </h1>
                
                <p class="text-[17px] text-gray-600 leading-relaxed max-w-md">
                    Join vetted experts who are building trust through verifiable behavioral signals and learner outcomes.
                </p>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-4">
                    <a href="?panel=expert&page=auth" class="bg-[#00A87E] hover:bg-[#009870] text-white px-7 py-3.5 rounded-xl font-bold text-[15px] transition-all flex items-center gap-2">
                        Become a Verified Expert
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                    </a>
                    <a href="index.php?page=how-trust-works" class="bg-white border border-gray-200 hover:border-gray-300 text-gray-900 px-7 py-3.5 rounded-xl font-bold text-[15px] transition-all flex items-center gap-2 shadow-sm">
                        How it works
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3v18l15-9z"></path></svg>
                    </a>
                </div>
                
                <!-- Social Proof Avatars -->
                <div class="flex items-center gap-4 pt-2">
                    <div class="flex -space-x-2">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=100" alt="Expert">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=100" alt="Expert">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=100" alt="Expert">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=100" alt="Expert">
                    </div>
                    <div class="text-[14px] text-gray-600 font-medium">
                        Evidence-backed Trust System
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual — Two stacked cards -->
            <div class="relative flex flex-col gap-4">

                <!-- Card 1: Reputation Overview -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6">
                    <!-- Card Header -->
                    <div class="flex justify-between items-start mb-5">
                        <div>
                            <h3 class="text-gray-900 font-bold text-[17px] leading-tight">Platform Credibility Record</h3>
                            <p class="text-gray-400 text-[12px] mt-0.5">Real-time insights backed by behavioral audit trails</p>
                        </div>
                        <div class="bg-[#f0fdf4] text-[#16a34a] text-[12px] font-semibold px-3 py-1 rounded-full flex items-center gap-1.5 border border-[#dcfce7] shrink-0">
                            <div class="w-1.5 h-1.5 bg-[#16a34a] rounded-full"></div>
                            Live
                        </div>
                    </div>
                    <!-- Stats Row: Gauge + 3 Stat Boxes -->
                    <div class="flex gap-3 items-stretch">
                        <!-- Gauge -->
                        <div class="flex flex-col items-center justify-center shrink-0 w-36">
                            <div class="relative w-36 h-[76px]">
                                <svg class="w-full h-full" viewBox="0 0 200 105" preserveAspectRatio="xMidYMax meet">
                                    <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e2e8f0" stroke-width="16" stroke-linecap="round"/>
                                    <path d="M 20 100 A 80 80 0 0 1 165 53" fill="none" stroke="#16a34a" stroke-width="16" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-0 flex items-end justify-center pb-1">
                                    <span class="text-[20px] font-black text-gray-900 leading-none tracking-tight"><?php echo htmlspecialchars($spotlightBand); ?></span>
                                </div>
                            </div>
                            <span class="text-[11px] text-gray-500 font-medium mt-2">Active Trust Tier</span>
                        </div>
                        <!-- Stat: Verified Signals -->
                        <div class="flex-1 bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col">
                            <span class="text-[10px] text-gray-500 font-medium mb-2">Logged Signals</span>
                            <div class="w-9 h-9 bg-[#f0fdf4] rounded-full flex items-center justify-center mb-2">
                                <svg class="w-4 h-4 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                            <div class="text-[18px] font-black text-gray-900 leading-none mb-1"><?php echo $displayEvents; ?></div>
                            <div class="text-[11px] font-bold text-[#16a34a] flex items-center gap-0.5">Audit Backed</div>
                            <span class="text-[9px] text-gray-400 mt-0.5">behavioral events</span>
                        </div>
                        <!-- Stat: Verified Outcomes -->
                        <div class="flex-1 bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col">
                            <span class="text-[10px] text-gray-500 font-medium mb-2">Verified Outcomes</span>
                            <div class="w-9 h-9 bg-[#f0fdf4] rounded-full flex items-center justify-center mb-2">
                                <svg class="w-4 h-4 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-[18px] font-black text-gray-900 leading-none mb-1"><?php echo $displayOutcomes; ?></div>
                            <div class="text-[11px] font-bold text-[#16a34a] flex items-center gap-0.5">Evidence Validated</div>
                            <span class="text-[9px] text-gray-400 mt-0.5">career & skill goals</span>
                        </div>
                        <!-- Stat: Active Experts -->
                        <div class="flex-1 bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col">
                            <span class="text-[10px] text-gray-500 font-medium mb-2">Vetted Experts</span>
                            <div class="w-9 h-9 bg-[#f0fdf4] rounded-full flex items-center justify-center mb-2">
                                <svg class="w-4 h-4 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="text-[18px] font-black text-gray-900 leading-none mb-1"><?php echo $displayExperts; ?></div>
                            <div class="text-[11px] font-bold text-[#16a34a] flex items-center gap-0.5">Approved & KYC'd</div>
                            <span class="text-[9px] text-gray-400 mt-0.5">practitioners</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Reputation Growth Chart -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-[#f0fdf4] rounded-lg flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-[#16a34a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold text-[15px] leading-tight">EMA Score Trajectory</h4>
                                <p class="text-gray-400 text-[11px]">Dynamic recalculation after every milestone event</p>
                            </div>
                        </div>
                        <div class="bg-[#f0fdf4] text-[#16a34a] text-[11px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 border border-[#dcfce7] shrink-0">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 20V4m0 0l-6 6m6-6l6 6"></path></svg>
                            <span class="font-black">α = 0.3</span>&nbsp;Exponential Smoothing
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex flex-col justify-between text-[9px] text-gray-400 text-right pb-5 pr-1" style="min-width:24px">
                            <span>100</span><span>80</span><span>60</span><span>40</span><span>20</span><span>0</span>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <div class="relative h-36">
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 400 100" preserveAspectRatio="none">
                                    <defs><linearGradient id="areaGreen" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#16a34a" stop-opacity="0.12"/><stop offset="100%" stop-color="#16a34a" stop-opacity="0"/></linearGradient></defs>
                                    <line x1="0" y1="0" x2="400" y2="0" stroke="#f3f4f6" stroke-width="0.5"/>
                                    <line x1="0" y1="20" x2="400" y2="20" stroke="#f3f4f6" stroke-width="0.5"/>
                                    <line x1="0" y1="40" x2="400" y2="40" stroke="#f3f4f6" stroke-width="0.5"/>
                                    <line x1="0" y1="60" x2="400" y2="60" stroke="#f3f4f6" stroke-width="0.5"/>
                                    <line x1="0" y1="80" x2="400" y2="80" stroke="#f3f4f6" stroke-width="0.5"/>
                                    <line x1="0" y1="100" x2="400" y2="100" stroke="#e5e7eb" stroke-width="0.5"/>
                                    <path d="M0,80 L50,68 L100,56 L150,48 L200,38 L250,30 L300,24 L400,20 L400,100 L0,100 Z" fill="url(#areaGreen)"/>
                                    <path d="M0,80 L50,68 L100,56 L150,48 L200,38 L250,30 L300,24 L400,20" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="0" cy="80" r="3" fill="#16a34a"/><circle cx="50" cy="68" r="3" fill="#16a34a"/><circle cx="100" cy="56" r="3" fill="#16a34a"/><circle cx="150" cy="48" r="3" fill="#16a34a"/><circle cx="200" cy="38" r="3" fill="#16a34a"/><circle cx="250" cy="30" r="3" fill="#16a34a"/><circle cx="300" cy="24" r="3" fill="#16a34a"/><circle cx="400" cy="20" r="5" fill="#16a34a"/>
                                </svg>
                            </div>
                            <div class="flex justify-between text-[9px] text-gray-400 pt-1">
                                <span>Event 1 (KYC)</span><span>Event 3 (Goal)</span><span>Event 5 (Outcome)</span><span>Event 8 (Certification)</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Why Experts Choose Nexpert -->
    <section class="border-y border-gray-100 bg-gray-50 py-20 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-3xl font-bold text-gray-900">Why top experts choose Nexpert</h2>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Point 1 -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 space-y-4 text-left hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-600/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-emerald-600/10"></div>
                    <div class="w-10 h-10 bg-[#0e2120] border border-[#00D4AA]/30 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform relative z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Fair Trust Scoring</h3>
                        <p class="text-gray-600 text-xs leading-relaxed">Escape subjective reviews. We calculate trust based on verified milestones, sessions, and tangible outcomes.</p>
                    </div>
                </div>

                <!-- Point 2 -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 space-y-4 text-left hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-indigo-500/10"></div>
                    <div class="w-10 h-10 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform relative z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Keep What You Earn</h3>
                        <p class="text-gray-600 text-xs leading-relaxed">With transparent platform fees, your expertise directly translates to your bottom line.</p>
                    </div>
                </div>

                <!-- Point 3 -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 space-y-4 text-left hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-pink-500/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-pink-500/10"></div>
                    <div class="w-10 h-10 bg-pink-500/10 border border-pink-500/20 rounded-xl flex items-center justify-center text-pink-400 group-hover:scale-110 transition-transform relative z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Effortless Scheduling</h3>
                        <p class="text-gray-600 text-xs leading-relaxed">Set your availability and let our automated booking system handle the rest.</p>
                    </div>
                </div>

                <!-- Point 4 -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 space-y-4 text-left hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl -mr-16 -mt-16 transition-all group-hover:bg-amber-500/10"></div>
                    <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform relative z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Global Reach</h3>
                        <p class="text-gray-600 text-xs leading-relaxed">Connect with highly motivated learners and organizations across the globe.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
        <div class="text-center mb-16 space-y-4">
            <h2 class="text-3xl font-bold text-gray-900">How it works for Experts</h2>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
            <!-- Step 1 -->
            <div class="bg-white border border-gray-200/80 rounded-2xl p-6 hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold flex items-center justify-center text-sm z-10 shrink-0">1</div>
                    <div class="h-[1px] bg-gradient-to-r from-[#00D4AA]/40 to-transparent flex-grow"></div>
                </div>
                <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Apply & Verify</h3>
                <p class="text-gray-600 text-xs leading-relaxed">Submit your credentials and portfolio. Our team conducts KYC verification to establish your baseline credibility tier.</p>
            </div>

            <!-- Step 2 -->
            <div class="bg-white border border-gray-200/80 rounded-2xl p-6 hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold flex items-center justify-center text-sm z-10 shrink-0">2</div>
                    <div class="h-[1px] bg-gradient-to-r from-[#00D4AA]/40 to-transparent flex-grow"></div>
                </div>
                <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Set Availability</h3>
                <p class="text-gray-600 text-xs leading-relaxed">Craft your expert profile, define your session rates, and sync your calendar. Take full control of when you mentor.</p>
            </div>

            <!-- Step 3 -->
            <div class="bg-white border border-gray-200/80 rounded-2xl p-6 hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold flex items-center justify-center text-sm z-10 shrink-0">3</div>
                    <div class="h-[1px] bg-gradient-to-r from-[#00D4AA]/40 to-transparent flex-grow"></div>
                </div>
                <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Deliver Sessions</h3>
                <p class="text-gray-600 text-xs leading-relaxed">Engage with motivated learners with clear agendas and milestones to help them achieve measurable learning goals.</p>
            </div>

            <!-- Step 4 -->
            <div class="bg-white border border-gray-200/80 rounded-2xl p-6 hover:border-emerald-500/30 transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold flex items-center justify-center text-sm z-10 shrink-0">4</div>
                    <div class="h-[1px] bg-gradient-to-r from-[#00D4AA]/40 to-transparent flex-grow"></div>
                </div>
                <h3 class="font-bold text-gray-900 text-[15px] mb-2 group-hover:text-emerald-600 transition-colors">Grow Trust Score</h3>
                <p class="text-gray-600 text-xs leading-relaxed">Our EMA engine updates your Trust Score across 4 core dimensions: Outcome, Structure, Boundary, and Consistency.</p>
            </div>
        </div>
    </section>

    <!-- Signals & Formula Breakdown -->
    <section class="border-y border-gray-100 bg-gray-50 py-20 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Left Column: Signals List -->
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Credibility Signals We Track</h2>
                    <p class="text-gray-600 mb-6">Your expertise quantified objectively through verified operational telemetry.</p>
                    <div class="flex flex-wrap gap-2.5">
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">🎯 Career Transition Verification</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">📜 Certification Completion</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">📋 Pre-Session Agenda & Goal Clarity</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">⏱️ On-Time Start & Boundary Compliance</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">🔄 Organic Repeat Booking Signals</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">🛡️ Government & Professional KYC</span>
                        <span class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 shadow-sm">📊 Long-term Performance Consistency</span>
                    </div>
                </div>

                <!-- Right Column: Formula Chart -->
                <div class="bg-white border border-gray-200 rounded-3xl p-8 space-y-6 shadow-sm">
                    <h2 class="text-2xl font-bold text-gray-900">4-Pillar Trust Weighting</h2>
                    <p class="text-sm text-gray-600">Calculated via Exponential Moving Average (α = 0.3) with 90-day decay.</p>

                    <div class="flex flex-col sm:flex-row items-center gap-8">
                        <!-- Donut Chart SVG -->
                        <div class="relative w-40 h-40 flex-shrink-0">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-gray-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-emerald-500" stroke-dasharray="35, 100" stroke-dashoffset="0" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-blue-500" stroke-dasharray="25, 100" stroke-dashoffset="-35" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-indigo-500" stroke-dasharray="20, 100" stroke-dashoffset="-60" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="text-purple-500" stroke-dasharray="20, 100" stroke-dashoffset="-80" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black text-gray-900"><?php echo $spotlightScore; ?></span>
                                <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider"><?php echo htmlspecialchars($spotlightBand); ?></span>
                            </div>
                        </div>

                        <!-- Legend Items -->
                        <div class="flex-grow space-y-3 text-sm w-full">
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="flex items-center gap-2 text-gray-700 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Outcome Achievement</span>
                                <span class="font-bold text-gray-900">35%</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="flex items-center gap-2 text-gray-700 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Structure & Preparedness</span>
                                <span class="font-bold text-gray-900">25%</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                                <span class="flex items-center gap-2 text-gray-700 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Boundary & Ethics</span>
                                <span class="font-bold text-gray-900">20%</span>
                            </div>
                            <div class="flex justify-between items-center pb-1">
                                <span class="flex items-center gap-2 text-gray-700 font-medium"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Consistency & Repeat Rate</span>
                                <span class="font-bold text-gray-900">20%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Example: Trust in Action -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10 border-b border-gray-100 mb-12">
        <div class="text-center mb-16 space-y-4">
            <h2 class="text-3xl font-bold text-gray-900">Verified Expert Spotlight</h2>
            <p class="text-gray-600">See how verified behavioral signals create transparent, tamper-evident credibility profiles.</p>
        </div>

        <!-- Expert Example Card -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 md:p-8 shadow-xl space-y-8">
            <div class="grid lg:grid-cols-12 gap-8">
                <!-- Info Left -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Profile Header -->
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl overflow-hidden border border-gray-200 bg-gray-800 flex items-center justify-center font-bold text-white text-xl">
                            <?php echo strtoupper(substr($spotlightName, 0, 1)); ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($spotlightName); ?></h3>
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> <?php echo htmlspecialchars($spotlightBand); ?> Expert
                                </span>
                            </div>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($spotlightTagline); ?></p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs text-gray-500">Identity & Credentials Verified</span>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Scores -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 border border-gray-200/60 p-4 rounded-2xl">
                            <span class="text-xs text-gray-600 block mb-1">Trust Score</span>
                            <span class="text-xl font-black text-gray-900"><?php echo $spotlightScore; ?></span>
                            <span class="text-[9px] font-bold text-emerald-600 block mt-0.5 uppercase tracking-wide"><?php echo htmlspecialchars($spotlightBand); ?></span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200/60 p-4 rounded-2xl">
                            <span class="text-xs text-gray-600 block mb-1">Outcome Score</span>
                            <span class="text-xl font-black text-gray-900"><?php echo $spotlightOutcome; ?>%</span>
                            <span class="text-[9px] font-bold text-blue-600 block mt-0.5 uppercase tracking-wide">35% Weight</span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200/60 p-4 rounded-2xl">
                            <span class="text-xs text-gray-600 block mb-1">Structure Score</span>
                            <span class="text-xl font-black text-gray-900"><?php echo $spotlightStructure; ?>%</span>
                            <span class="text-[9px] font-bold text-indigo-600 block mt-0.5 uppercase tracking-wide">25% Weight</span>
                        </div>
                        <div class="bg-gray-50 border border-gray-200/60 p-4 rounded-2xl">
                            <span class="text-xs text-gray-600 block mb-1">Consistency</span>
                            <span class="text-xl font-black text-gray-900"><?php echo $spotlightConsistency; ?>%</span>
                            <span class="text-[9px] font-bold text-purple-600 block mt-0.5 uppercase tracking-wide">20% Weight</span>
                        </div>
                    </div>

                    <!-- Why Trusted List -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-gray-900">Verified Evidence Signals</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Verified career transition and certification achievements on record</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Organic repeat booking and returning learner validation</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Structured session delivery with clear milestones and goals</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Graph Right -->
                <div class="lg:col-span-5 flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-900">Score Dimension Balance</span>
                    </div>

                    <div class="bg-gray-50 border border-gray-200/60 rounded-2xl p-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Outcome Signal</span>
                                <span><?php echo $spotlightOutcome; ?>%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: <?php echo $spotlightOutcome; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Structure Signal</span>
                                <span><?php echo $spotlightStructure; ?>%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo $spotlightStructure; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Boundary & Ethics</span>
                                <span><?php echo $spotlightBoundary; ?>%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: <?php echo $spotlightBoundary; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
                                <span>Consistency</span>
                                <span><?php echo $spotlightConsistency; ?>%</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full" style="width: <?php echo $spotlightConsistency; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA inside card -->
            <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-100">
                <a href="?panel=expert&page=auth" class="bg-[#00A87E] hover:bg-[#009870] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                    Apply to Get Verified <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="index.php?page=how-trust-works" class="border border-gray-200 hover:border-gray-300 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold transition-all hover:bg-gray-50">
                    Learn How Trust Works
                </a>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24 relative z-10">
        <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-8 md:p-16 relative overflow-hidden text-center flex flex-col items-center">
            <h3 class="text-3xl font-bold text-gray-900 leading-tight mb-4 max-w-2xl">
                Ready to monetize your knowledge in a system built on true merit?
            </h3>
            <p class="text-gray-600 text-sm mb-8 max-w-xl">
                Apply today. Our verification team reviews applications within 48 hours. Start helping learners achieve their goals this week.
            </p>
            <a href="index.php?panel=expert&page=auth" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-4 rounded-xl font-black text-base transition-all hover:scale-105 shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                Apply as an Expert
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
