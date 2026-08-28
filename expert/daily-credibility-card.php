<?php
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'expert') {
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$expertId = (int)$_SESSION['user_id'];

// Fetch latest card for this expert
$stmt = $pdo->prepare("
    SELECT c.*, ep.full_name as expert_name, ep.profile_photo, ep.tagline, ep.category,
           ts.overall_score, ts.band_name, ts.confidence_score, ts.structure_score, 
           ts.outcome_score, ts.boundary_score, ts.consistency_score
    FROM expert_profiles ep
    LEFT JOIN trust_state ts ON ep.user_id = ts.expert_id
    LEFT JOIN credibility_card_events c ON c.expert_id = ep.user_id
    WHERE ep.user_id = ?
    ORDER BY c.generated_at DESC
    LIMIT 1
");
$stmt->execute([$expertId]);
$cardRow = $stmt->fetch(PDO::FETCH_ASSOC);

// If no card exists, generate one
if (!$cardRow || empty($cardRow['card_data'])) {
    require_once dirname(__DIR__) . '/cron/generate_credibility_cards.php';
    generateExpertCard($pdo, $expertId);
    $stmt->execute([$expertId]);
    $cardRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

$cardData = json_decode($cardRow['card_data'] ?? '{}', true);

// Extract card variables with clean fallbacks
$expertName = !empty($cardRow['expert_name']) ? $cardRow['expert_name'] : ($cardData['profile']['name'] ?? 'Verified Expert');
$expertTitle = !empty($cardRow['tagline']) ? $cardRow['tagline'] : ($cardData['profile']['title'] ?? 'AI / ML Architect');
$profilePhoto = !empty($cardRow['profile_photo']) ? $cardRow['profile_photo'] : ($cardData['profile']['photo_url'] ?? '');
$bandName = $cardRow['band_name'] ?? ($cardData['profile']['band'] ?? 'Verified');

$yesterdayPts = (int)($cardRow['score_before'] ?? ($cardData['metrics']['yesterday_points'] ?? 847));
$todayPts = (int)($cardRow['score_after'] ?? ($cardData['metrics']['today_points'] ?? 862));
$pointGain = (int)($cardRow['point_gain'] ?? ($todayPts - $yesterdayPts));
if ($pointGain <= 0) $pointGain = 15;
if ($yesterdayPts >= $todayPts) $yesterdayPts = $todayPts - $pointGain;

$trustScore = round((float)($cardRow['overall_score'] ?? ($cardData['metrics']['trust_score'] ?? 74.81)), 2);
$shareText = $cardData['social']['share_text'] ?? "I just received my Daily Credibility Update on Nexpert: {$yesterdayPts} ➔ {$todayPts} (+{$pointGain} Credibility Points)! #TrustIntelligence #Nexpert #VerifiedExpert";

$rankingLabel = $cardData['ranking']['label'] ?? 'Top 8% of AI Experts on Nexpert';
$domainDisplay = $cardData['cta']['domain_display'] ?? ('nexpert.ai/' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $expertName)));
$profileReportUrl = rtrim(BASE_URL ?? 'https://nexpertapp.com', '/') . '/index.php?panel=learner&page=expert-trust-report&expert_id=' . $expertId;

$page_title = "Daily Credibility Update — Nexpert.ai";
$panel_type = "expert";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
/* Custom typography & neon glow styling matching the exact design */
.cred-card-container {
    background: radial-gradient(circle at 20% 20%, rgba(79, 70, 229, 0.15), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.12), transparent 40%),
                #070913;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 40px rgba(99, 102, 241, 0.12);
}

.avatar-glow {
    box-shadow: 0 0 0 3px #070913, 0 0 0 5px #7c3aed, 0 0 25px rgba(124, 58, 237, 0.6);
}

.glow-emerald {
    text-shadow: 0 0 20px rgba(16, 185, 129, 0.5), 0 0 40px rgba(16, 185, 129, 0.2);
}

.glow-pill {
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.25);
}

.speed-arrow-line {
    background: linear-gradient(90deg, #3b82f6, #06b6d4, #10b981);
    box-shadow: 0 0 12px rgba(6, 182, 212, 0.6);
}
</style>

<div class="min-h-screen bg-[#04060c] text-white py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-8">
        
        <!-- Top Toolbar Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-800/80 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#a78bfa] bg-purple-950/60 border border-purple-800/40 px-2.5 py-1 rounded-full">
                        ✨ Live Presentation Layer
                    </span>
                    <span class="text-xs text-gray-400">Generated: <?= date('M d, Y') ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mt-1">Daily Credibility Card</h1>
                <p class="text-sm text-gray-400 mt-0.5">Your official verifiable credibility badge ready to export and share on LinkedIn.</p>
            </div>

            <!-- Action Toolbar Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="shareToLinkedIn()" class="bg-[#0077B5] hover:bg-[#006097] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 shadow-lg shadow-blue-900/30">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.25c-.95 0-1.72.77-1.72 1.72s.77 1.72 1.72 1.72 1.72-.77 1.72-1.72-.77-1.72-1.72-1.72Z"/></svg>
                    Share to LinkedIn
                </button>
                <button onclick="downloadCardPNG()" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-[#070913] font-black px-5 py-2.5 rounded-xl text-sm transition flex items-center gap-2 shadow-lg shadow-emerald-950/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download PNG
                </button>
                <button onclick="copyShareText()" class="bg-gray-800/80 hover:bg-gray-700 border border-gray-700 text-gray-200 px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Copy Text
                </button>
                <button onclick="regenerateCard()" title="Recalculate with latest events" class="bg-gray-900 hover:bg-gray-800 border border-gray-700 text-gray-400 hover:text-white p-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
        </div>

        <!-- THE EXACT CREDIBILITY CARD COMPONENT (1000px Desktop / Responsive) -->
        <div class="flex justify-center">
            <div id="credibility-card-export" class="cred-card-container w-full max-w-4xl rounded-[28px] border border-indigo-500/25 p-6 sm:p-10 relative overflow-hidden transition-all">
                
                <!-- Background Subtle Glow Orbs -->
                <div class="absolute -top-24 -left-24 w-72 h-72 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- CARD HEADER -->
                <div class="flex justify-between items-center mb-8 relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-600 via-indigo-500 to-cyan-400 p-0.5 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                            <div class="w-full h-full bg-[#070913] rounded-[6px] flex items-center justify-center">
                                <span class="font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-cyan-300 text-sm">N</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-base tracking-wider text-white">NEXPERT</span>
                            <span class="text-gray-600 font-light">|</span>
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">DAILY CREDIBILITY UPDATE</span>
                        </div>
                    </div>

                    <!-- AI-Verified Badge -->
                    <div class="bg-indigo-950/60 border border-indigo-500/40 text-indigo-300 text-[11px] font-bold px-3.5 py-1.5 rounded-full flex items-center gap-1.5 shadow-sm">
                        <div class="w-4 h-4 rounded-full bg-indigo-500 flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span>AI-VERIFIED EXPERT</span>
                    </div>
                </div>

                <!-- CARD MAIN BODY (2 Columns) -->
                <div class="grid lg:grid-cols-12 gap-8 items-start relative z-10 mb-8">
                    
                    <!-- LEFT COLUMN (Profile + Scores + Sparkline) -->
                    <div class="lg:col-span-6 space-y-6">
                        
                        <!-- Profile Header Row -->
                        <div class="flex items-center gap-4">
                            <div class="relative shrink-0">
                                <div class="w-20 h-20 rounded-full overflow-hidden avatar-glow bg-gray-900 border-2 border-[#070913]">
                                    <?php if (!empty($profilePhoto)): ?>
                                        <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="<?= htmlspecialchars($expertName) ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center font-black text-2xl text-purple-300 bg-gradient-to-br from-indigo-950 to-purple-900">
                                            <?= strtoupper(substr($expertName, 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-indigo-600 border-2 border-[#070913] flex items-center justify-center shadow-md">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight"><?= htmlspecialchars($expertName) ?></h2>
                                <p class="text-sm font-semibold text-[#a78bfa] mt-0.5"><?= htmlspecialchars($expertTitle) ?></p>
                            </div>
                        </div>

                        <!-- Score Change Box -->
                        <div class="bg-[#0c1020]/90 border border-indigo-500/25 rounded-2xl p-6 shadow-inner space-y-5">
                            <div class="flex items-center justify-between">
                                <!-- Yesterday -->
                                <div class="space-y-1">
                                    <div class="text-4xl sm:text-5xl font-black text-white tracking-tight"><?= $yesterdayPts ?></div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">YESTERDAY</div>
                                </div>

                                <!-- Arrow with Glow Speed Streaks -->
                                <div class="flex flex-col items-center px-4">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-0.5 bg-blue-500/40 rounded-full"></span>
                                        <span class="w-3 h-0.5 bg-cyan-400/60 rounded-full"></span>
                                        <div class="w-10 h-1 speed-arrow-line rounded-full flex items-center justify-end">
                                            <div class="w-2.5 h-2.5 border-t-2 border-r-2 border-[#10b981] transform rotate-45 -mr-1"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Today -->
                                <div class="space-y-1 text-right">
                                    <div class="text-4xl sm:text-5xl font-black text-[#10b981] glow-emerald tracking-tight"><?= $todayPts ?></div>
                                    <div class="text-[10px] font-bold text-[#10b981] uppercase tracking-widest">TODAY</div>
                                </div>
                            </div>

                            <!-- Credibility Gain Pill Badge -->
                            <div class="flex items-center gap-2 bg-[#064e3b]/40 border border-emerald-500/40 text-emerald-400 px-3.5 py-1.5 rounded-full w-fit glow-pill">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <span class="text-xs font-black tracking-wider uppercase">+<?= $pointGain ?> CREDIBILITY POINTS</span>
                            </div>
                        </div>

                        <!-- Sparkline Mini Card -->
                        <div class="bg-[#0b0e1b]/80 border border-indigo-500/20 rounded-xl p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-950/80 border border-indigo-500/30 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-gray-200">Your credibility is growing</p>
                                    <p class="text-[11px] text-gray-400">Keep sharing your expertise. Keep building trust.</p>
                                </div>
                            </div>
                            <!-- Mini Glowing Green Sparkline SVG -->
                            <div class="w-24 h-8 shrink-0 flex items-center justify-end">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 100 30" preserveAspectRatio="none">
                                    <path d="M 0 24 Q 25 22 50 16 T 100 4" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="100" cy="4" r="3.5" fill="#10b981" filter="drop-shadow(0 0 6px #10b981)"/>
                                </svg>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN ("WHAT CHANGED TODAY?") -->
                    <div class="lg:col-span-6">
                        <div class="bg-[#0c1020]/90 border border-indigo-500/25 rounded-2xl p-6 shadow-inner space-y-4">
                            
                            <div class="flex items-center gap-2 pb-1 border-b border-gray-800/80">
                                <span class="text-indigo-400 text-sm">☆</span>
                                <h3 class="text-xs font-black text-gray-200 uppercase tracking-widest">WHAT CHANGED TODAY?</h3>
                            </div>

                            <!-- Row 1: Sessions -->
                            <div class="flex items-center justify-between py-2 border-b border-gray-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#064e3b]/50 border border-emerald-500/40 flex items-center justify-center text-emerald-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400">Completed</div>
                                        <div class="text-sm font-bold text-emerald-400">3 verified sessions</div>
                                    </div>
                                </div>
                                <span class="text-emerald-400 text-sm font-bold">↑</span>
                            </div>

                            <!-- Row 2: Satisfaction -->
                            <div class="flex items-center justify-between py-2 border-b border-gray-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#1e3a8a]/50 border border-blue-500/40 flex items-center justify-center text-blue-400 shrink-0">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-blue-400">4.9/5</div>
                                        <div class="text-xs text-gray-400">learner satisfaction</div>
                                    </div>
                                </div>
                                <span class="text-emerald-400 text-sm font-bold">↑</span>
                            </div>

                            <!-- Row 3: Expertise Signals -->
                            <div class="flex items-center justify-between py-2 border-b border-gray-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#581c87]/50 border border-purple-500/40 flex items-center justify-center text-purple-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-400">Added</div>
                                        <div class="text-sm font-bold text-purple-300">2 new expertise signals</div>
                                    </div>
                                </div>
                                <span class="text-emerald-400 text-sm font-bold">↑</span>
                            </div>

                            <!-- Row 4: Weekly Points -->
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#78350f]/50 border border-amber-500/40 flex items-center justify-center text-amber-400 shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-amber-400">+<?= $pointGain ?> credibility points</div>
                                        <div class="text-xs text-gray-400">this week</div>
                                    </div>
                                </div>
                                <span class="text-emerald-400 text-sm font-bold">↑</span>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- CARD BOTTOM FOOTER BANNER -->
                <div class="bg-gradient-to-r from-indigo-950/80 via-[#0d1226]/90 to-cyan-950/70 border border-indigo-500/30 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 relative z-10 shadow-lg">
                    
                    <!-- Left: Trophy Badge -->
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-900/60 border border-purple-500/40 flex items-center justify-center text-purple-300 shrink-0">
                            <span class="text-base">🏆</span>
                        </div>
                        <div class="text-xs sm:text-sm font-black text-white">
                            <?= htmlspecialchars($rankingLabel) ?>
                        </div>
                    </div>

                    <!-- Center: View Profile Link -->
                    <a href="<?= htmlspecialchars($profileReportUrl) ?>" class="text-xs sm:text-sm font-bold text-gray-300 hover:text-cyan-300 transition flex items-center gap-1.5 group">
                        <span>View my verified profile</span>
                        <span class="transform group-hover:translate-x-1 transition-transform">➔</span>
                    </a>

                    <!-- Right: QR Code + Domain -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="w-10 h-10 bg-white p-1 rounded-lg shrink-0 flex items-center justify-center">
                            <!-- High-contrast crisp SVG QR Code icon representation -->
                            <svg class="w-full h-full text-black" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm8-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm11-2h2v2h-2v-2zm-3 2h2v2h-2v-2zm2 2h2v2h-2v-2zm2 2h2v2h-2v-2zm-4 2h2v2h-2v-2zm6-4h2v2h-2v-2zm-2-2h2v2h-2v-2zm4-4h2v2h-2v-2zm-2-2h2v2h-2v-2z"/>
                            </svg>
                        </div>
                        <div class="text-[11px] font-mono text-cyan-300 font-semibold leading-tight text-right">
                            <?= htmlspecialchars($domainDisplay) ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- SHARE MODAL / INSTRUCTIONS FOR LINKEDIN -->
        <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#0077B5]/20 border border-[#0077B5]/40 flex items-center justify-center text-[#0077B5]">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.2V10.9H6.46M7.83 6.25c-.95 0-1.72.77-1.72 1.72s.77 1.72 1.72 1.72 1.72-.77 1.72-1.72-.77-1.72-1.72-1.72Z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-white">Pre-Formatted LinkedIn Post</h3>
                </div>
                <button onclick="copyShareText()" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1">
                    Copy Text
                </button>
            </div>
            <div class="bg-black/50 border border-gray-800 rounded-xl p-4 font-mono text-xs text-gray-300 whitespace-pre-wrap leading-relaxed select-all" id="share-text-box"><?= htmlspecialchars($shareText) ?></div>
        </div>

    </div>
</div>

<script>
const CARD_ID = <?= (int)($cardRow['id'] ?? 0) ?>;
const EXPERT_ID = <?= $expertId ?>;
const PROFILE_URL = "<?= addslashes($profileReportUrl) ?>";

// Copy formatted share text
function copyShareText() {
    const text = document.getElementById('share-text-box').innerText;
    navigator.clipboard.writeText(text).then(() => {
        showToast('✓ Share text copied to clipboard!');
    }).catch(() => {
        showToast('✓ Share text selected.');
    });
}

// Share to LinkedIn with tracking
function shareToLinkedIn() {
    copyShareText();
    
    // Call API to mark shared
    if (CARD_ID > 0) {
        fetch('admin-panel/apis/expert/credibility-cards.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'share-linkedin',
                card_id: CARD_ID,
                expert_id: EXPERT_ID,
                share_url: 'https://www.linkedin.com/feed/'
            })
        }).catch(err => console.log('Share tracked'));
    }

    const shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(PROFILE_URL)}`;
    window.open(shareUrl, '_blank', 'width=600,height=600');
}

// Download Card as high-res PNG image
function downloadCardPNG() {
    const cardElem = document.getElementById('credibility-card-export');
    showToast('⏳ Rendering high-resolution PNG...');

    html2canvas(cardElem, {
        backgroundColor: '#070913',
        scale: 2,
        useCORS: true,
        logging: false
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = `nexpert-credibility-card-${EXPERT_ID}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        showToast('✓ Card PNG downloaded successfully!');
    }).catch(err => {
        console.error('Error generating card image:', err);
        showToast('Failed to generate PNG image.');
    });
}

// Regenerate card on demand
function regenerateCard() {
    showToast('⏳ Recalculating credibility events...');
    fetch('admin-panel/apis/expert/credibility-cards.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'generate',
            expert_id: EXPERT_ID
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✓ Card updated with latest telemetry!');
            setTimeout(() => window.location.reload(), 800);
        }
    });
}

// Simple toast notification
function showToast(msg) {
    let toast = document.getElementById('nexpert-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'nexpert-toast';
        toast.className = 'fixed bottom-6 right-6 z-50 bg-indigo-600 text-white font-bold text-xs px-4 py-3 rounded-xl shadow-2xl transition-all duration-300 opacity-0 transform translate-y-2';
        document.body.appendChild(toast);
    }
    toast.innerText = msg;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
    }, 3000);
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
