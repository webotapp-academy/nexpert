<?php
$base_path  = require_once 'admin-panel/apis/connection/domain-path.php';
$page_title = "Nexpert.ai — Learn from Experts You Can Truly Trust";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<!-- HERO -->
<section class="bg-[#080B10] min-h-[82vh] flex items-center relative overflow-hidden py-20">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] bg-[#00D4AA]/4 rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 w-full text-center">
        <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest mb-8">
            <span class="w-1.5 h-1.5 bg-[#00D4AA] rounded-full animate-pulse"></span>
            Trust Intelligence Platform — Early Access
        </div>
        <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-[1.05] tracking-tight">
            Learn from experts<br>you can <span class="text-[#00D4AA]" style="font-style:italic">truly</span> trust.
        </h1>
        <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
            Not the most popular. Not the most followed.<br>
            The experts most likely to help you achieve the outcome you actually need.
        </p>
        <!-- Search — all IDs and JS unchanged -->
        <div class="max-w-2xl mx-auto mb-6">
            <form id="expertSearchForm" class="relative flex items-center bg-[#0d131f] border border-gray-800 rounded-2xl p-2 shadow-2xl focus-within:border-[#00D4AA]/30 transition">
                <div class="flex-grow pl-4 flex items-center">
                    <input id="searchInput" type="text"
                           placeholder="What outcome do you want to achieve?"
                           class="w-full bg-transparent text-white placeholder-gray-500 focus:outline-none border-0 focus:ring-0 text-base py-3 pr-48">
                </div>
                <div class="absolute right-2">
                    <button type="submit" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:scale-[1.02] active:scale-[0.98] transition">
                        Find Trusted Experts
                    </button>
                </div>
            </form>
        </div>
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            <?php foreach([
                'AI & ML' => 'AI+%26+ML',
                'Leadership' => 'Leadership',
                'Career Growth' => 'Career+Growth',
                'Product & Strategy' => 'Product+%26+Strategy',
                'Data Science' => 'Data+Science',
            ] as $label => $cat): ?>
            <button onclick="searchForExpertise('<?= $label ?>')"
                    class="px-4 py-2 bg-[#0d131f] border border-gray-800 hover:border-[#00D4AA]/40 text-gray-400 hover:text-white text-sm font-medium rounded-xl transition">
                <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>
        <p class="text-gray-600 text-sm">Join our founding expert cohort. <a href="?panel=expert&page=apply" class="text-[#00D4AA] hover:underline">Apply as an Expert →</a></p>
    </div>
</section>

<!-- WHY NEXPERT -->
<section class="py-20 bg-[#0A0D15] border-t border-gray-900">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-14">
            <p class="text-[#00D4AA] text-xs font-bold uppercase tracking-widest mb-4">Why Nexpert</p>
            <h2 class="text-4xl font-bold text-white mb-4">Every expert is verified.<br>Not just listed.</h2>
            <p class="text-gray-400 max-w-xl mx-auto">The internet is full of experts. Finding one you can actually trust before you commit your time and money is the hard part. That is what Nexpert solves.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-px bg-gray-800 border border-gray-800 rounded-2xl overflow-hidden">
            <?php foreach([
                ['📊','Behavior, not biography','We track what experts actually do — sessions completed, outcomes achieved, learners who returned. The score reflects reality, not a self-reported CV.'],
                ['🔄','Updated continuously','Every session, every outcome, every interaction updates the score. You see who this expert is today — not when they joined.'],
                ['🎯','Matched to your goal','Not all expertise is equal for all outcomes. You see who has actually helped people achieve what you want to achieve.'],
            ] as [$icon,$title,$body]): ?>
            <div class="bg-[#0d131f] p-10 hover:bg-[#131b2e] transition">
                <div class="text-3xl mb-5"><?= $icon ?></div>
                <h3 class="text-lg font-bold text-white mb-3"><?= $title ?></h3>
                <p class="text-gray-400 text-sm leading-relaxed"><?= $body ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED EXPERTS — DB-driven -->
<section id="experts" class="py-20 bg-[#080B10] border-t border-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[#00D4AA] text-xs font-bold uppercase tracking-widest mb-3">Featured Experts</p>
                <h2 class="text-4xl font-bold text-white">Trust built through<br>real outcomes.</h2>
            </div>
            <a href="?panel=learner&page=browse-experts" class="text-[#00D4AA] font-semibold text-sm hover:text-white transition flex items-center gap-1">
                View all experts <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <!-- Populated by loadFeaturedExperts() — endpoint unchanged -->
        <div id="featured-experts-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="col-span-full text-center py-16">
                <div class="w-10 h-10 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-500 text-sm">Loading verified experts...</p>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section id="categories" class="py-20 bg-[#0A0D15] border-t border-gray-900">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-end justify-between mb-12">
            <div>
                <p class="text-[#00D4AA] text-xs font-bold uppercase tracking-widest mb-3">Explore</p>
                <h2 class="text-4xl font-bold text-white">Find expertise<br>for your goal.</h2>
            </div>
            <a href="?panel=learner&page=browse-experts" class="text-[#00D4AA] font-semibold text-sm hover:text-white transition flex items-center gap-1">
                All categories <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach([
                ['🤖','AI & Technology','AI+%26+Technology','Machine learning, software development, cloud computing'],
                ['🧭','Leadership','Leadership','Executive coaching, management, team leadership'],
                ['📈','Career Growth','Career+Growth','Resume, interview prep, career transitions'],
                ['🚀','Entrepreneurship','Entrepreneurship','Startup advice, fundraising, product-market fit'],
                ['📊','Product & Strategy','Product+%26+Strategy','Product management, positioning, user research'],
                ['📚','All Categories','','Browse every expert domain'],
            ] as [$icon,$name,$cat,$desc]):
                $href = $cat ? "?panel=learner&page=browse-experts&category={$cat}" : "?panel=learner&page=browse-experts";
            ?>
            <a href="<?= $href ?>" class="group bg-[#0d131f] border border-gray-800 rounded-2xl p-6 hover:border-[#00D4AA]/30 hover:shadow-xl transition block">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-xl flex items-center justify-center text-xl"><?= $icon ?></div>
                    <span class="text-xs font-semibold text-[#00D4AA] opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                </div>
                <h4 class="text-base font-bold text-white mb-2 group-hover:text-[#00D4AA] transition"><?= $name ?></h4>
                <p class="text-gray-500 text-xs leading-relaxed"><?= $desc ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- VS COMPARISON -->
<section class="py-20 bg-[#080B10] border-t border-gray-900">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <p class="text-[#00D4AA] text-xs font-bold uppercase tracking-widest mb-4">The Difference</div>
            <h2 class="text-4xl font-bold text-white">Ratings are opinions.<br>Trust is evidence.</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-[#0d131f] border border-red-900/30 rounded-2xl p-8">
                <div class="text-red-400 text-xs font-bold uppercase tracking-widest mb-5">✕ Traditional Platforms</div>
                <h3 class="text-xl font-bold text-white mb-5">Star Ratings + Follower Count</h3>
                <?php foreach(['Self-reported credentials','Ratings given in the moment, not by outcome','Same weight to a bad expert with 5 reviews as a great one','No outcome tracking','No independent verification'] as $item): ?>
                <div class="flex items-start gap-3 mb-3 text-sm text-gray-400">
                    <span class="text-red-400 mt-0.5 flex-shrink-0">✕</span><?= $item ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="bg-[#0d131f] border border-[#00D4AA]/25 rounded-2xl p-8">
                <div class="text-[#00D4AA] text-xs font-bold uppercase tracking-widest mb-5">✓ Nexpert Trust Intelligence</div>
                <h3 class="text-xl font-bold text-white mb-5">Behavioral Evidence Score</h3>
                <?php foreach(['Third-party verified, not self-reported','Score from real outcomes, not purchase-moment feelings','Continuous update — reflects today not signup day','Full outcome tracking per goal','Independent methodology, published in full'] as $item): ?>
                <div class="flex items-start gap-3 mb-3 text-sm text-gray-300">
                    <span class="text-[#00D4AA] mt-0.5 flex-shrink-0">✓</span><?= $item ?>
                </div>
                <?php endforeach; ?>
                <a href="index.php?page=methodology" class="inline-flex items-center gap-2 mt-4 text-[#00D4AA] font-semibold text-sm hover:text-white transition">
                    Read the full methodology →
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER CTA -->
<section class="py-24 bg-[#0A0D15] border-t border-gray-900">
    <div class="max-w-3xl mx-auto text-center px-4">
        <h2 class="text-5xl font-extrabold text-white mb-5 leading-tight">
            The right expert<br>changes <span class="text-[#00D4AA]">everything.</span>
        </h2>
        <p class="text-gray-400 text-lg mb-10 leading-relaxed">
            Stop guessing. Stop relying on follower counts and star ratings.<br>
            Find the expert most likely to help you achieve the outcome you need.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="?panel=learner&page=browse-experts" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-8 py-4 rounded-xl font-bold text-base transition shadow-lg">Find Your Expert →</a>
            <a href="?panel=expert&page=apply" class="border border-gray-700 text-gray-300 px-8 py-4 rounded-xl font-bold text-base hover:text-white hover:border-gray-500 transition">Apply as an Expert</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-[#080B10] border-t border-gray-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-10 mb-12">
            <div class="md:col-span-2">
                <div class="flex items-center mb-5">
                    <div class="w-9 h-9 bg-[#00D4AA] rounded-xl flex items-center justify-center font-extrabold text-[#080B10] text-lg mr-3">N</div>
                    <div>
                        <div class="text-xl font-bold">Nexpert.ai</div>
                        <div class="text-xs text-gray-500">Trust Intelligence Platform</div>
                    </div>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed max-w-sm">The trust layer for human expertise. We help learners find the right expert and experts build a verified reputation through real outcomes.</p>
                <p class="text-gray-600 text-xs mt-4">Trust is measured. Not claimed.</p>
            </div>
            <div>
                <h5 class="font-bold mb-5 text-sm uppercase tracking-wider text-gray-300">Categories</h5>
                <ul class="space-y-3">
                    <?php foreach(['AI & Technology'=>'AI+%26+Technology','Leadership'=>'Leadership','Career Growth'=>'Career+Growth','Entrepreneurship'=>'Entrepreneurship','Product & Strategy'=>'Product+%26+Strategy'] as $name=>$cat): ?>
                    <li><a href="?panel=learner&page=browse-experts&category=<?= $cat ?>" class="text-gray-500 hover:text-white text-sm transition"><?= $name ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h5 class="font-bold mb-5 text-sm uppercase tracking-wider text-gray-300">Platform</h5>
                <ul class="space-y-3">
                    <?php foreach(['How Trust Works'=>'index.php?page=how-trust-works','Methodology'=>'index.php?page=methodology','For Enterprise'=>'index.php?page=for-enterprise','Privacy Policy'=>'#','Terms of Service'=>'#'] as $name=>$href): ?>
                    <li><a href="<?= $href ?>" class="text-gray-500 hover:text-white text-sm transition"><?= $name ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-600 text-sm">© 2026 Nexpert.ai. All rights reserved.</p>
            <div class="flex items-center gap-2 text-gray-600 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                All systems operational
            </div>
        </div>
    </div>
</footer>

<script>
function resolveImagePath(p) {
    if (!p) return '';
    if (p.startsWith('http://') || p.startsWith('https://')) return p;
    const base = '<?php echo BASE_PATH; ?>';
    return base + (p.startsWith('/') ? p : '/' + p);
}
function escapeHtml(t) {
    if (!t) return '';
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
}

// Typing placeholder
const pts = ['e.g. Get promoted to Senior PM', 'e.g. Launch my startup', 'e.g. Clear AWS certification', 'e.g. Become a better leader', 'e.g. Transition to product management'];
let pti = 0, pci = 0, pd = false, ps = 100;
function typeEffect() {
    const el = document.getElementById('searchInput');
    if (!el || el === document.activeElement) return;
    const t = pts[pti];
    if (pd) {
        el.placeholder = t.substring(0, pci - 1);
        pci--;
        ps = 50;
    } else {
        el.placeholder = t.substring(0, pci + 1);
        pci++;
        ps = 80;
    }
    if (!pd && pci === t.length) {
        pd = true;
        ps = 2000;
    } else if (pd && pci === 0) {
        pd = false;
        pti = (pti + 1) % pts.length;
        ps = 400;
    }
    setTimeout(typeEffect, ps);
}

const searchForm = document.getElementById('expertSearchForm');
if (searchForm) {
    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const q = document.getElementById('searchInput').value.trim();
        if (!q) return;
        const btn = this.querySelector('button[type="submit"]'), orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-[#080B10] inline mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Finding...';
        try {
            const r = await fetch('<?php echo BASE_PATH; ?>/admin-panel/apis/learner/browse-experts.php?action=smart_search', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: q })
            });
            const res = await r.json();
            if (res.success && res.data?.length > 0) {
                sessionStorage.setItem('smart_search_results', JSON.stringify(res.data));
                sessionStorage.setItem('smart_search_query', q);
                window.location.href = '?panel=learner&page=browse-experts&mode=smart';
            } else {
                window.location.href = '?panel=learner&page=browse-experts&search=' + encodeURIComponent(q);
            }
        } catch {
            window.location.href = '?panel=learner&page=browse-experts&search=' + encodeURIComponent(q);
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
}

function searchForExpertise(q) {
    const input = document.getElementById('searchInput');
    if (input && searchForm) {
        input.value = q;
        searchForm.dispatchEvent(new Event('submit'));
    }
}

// Featured experts
async function loadFeaturedExperts() {
    const grid = document.getElementById('featured-experts-grid');
    if (!grid) return;

    try {
        const r = await fetch('<?php echo BASE_PATH; ?>/admin-panel/apis/learner/browse-experts.php?sort_by=latest&limit=6');
        const text = await r.text();
        const jsonStart = text.indexOf('{"success"');
        const res = jsonStart !== -1 ? JSON.parse(text.substring(jsonStart)) : JSON.parse(text);

        if (res.success && res.data && res.data.length > 0) {
            const bandCfg = {
                'Sovereign': { label: 'Sovereign', dot: 'bg-emerald-500 animate-pulse', text: 'text-emerald-400', bg: 'from-emerald-950/30 to-teal-950/30', border: 'border-emerald-900/40' },
                'Established': { label: 'Established', dot: 'bg-emerald-400', text: 'text-emerald-400', bg: 'from-emerald-950/30 to-teal-950/30', border: 'border-emerald-900/40' },
                'Verified': { label: 'Verified', dot: 'bg-blue-400', text: 'text-blue-400', bg: 'from-blue-950/30 to-indigo-950/30', border: 'border-blue-900/40' },
                'Emerging': { label: 'Emerging', dot: 'bg-indigo-400', text: 'text-indigo-400', bg: 'from-indigo-950/30 to-slate-900/30', border: 'border-indigo-900/40' },
                'Unverified': { label: 'Unverified', dot: 'bg-gray-500', text: 'text-gray-400', bg: 'from-slate-900/30 to-gray-900/30', border: 'border-gray-800' }
            };

            grid.innerHTML = res.data.slice(0, 6).map(e => {
                const skills = Array.isArray(e.skills) ? e.skills : (typeof e.skills === 'string' ? e.skills.split(',').map(s => s.trim()) : []);
                const avatar = e.profile_photo ? resolveImagePath(e.profile_photo) : ('https://ui-avatars.com/api/?name=' + encodeURIComponent(e.name) + '&background=00D4AA&color=080B10&size=200');
                
                const rawBand = (e.band_name || '').trim();
                const score = Math.round(Number(e.overall_score) || 0);
                let band = rawBand;
                if (!band) {
                    if (score >= 90) band = 'Sovereign';
                    else if (score >= 75) band = 'Established';
                    else if (score >= 60) band = 'Verified';
                    else if (score >= 40) band = 'Emerging';
                    else if (e.trust_tier === 'A') band = 'Established';
                    else if (e.trust_tier === 'B') band = 'Verified';
                    else band = 'Unverified';
                }
                const tier = bandCfg[band] || bandCfg['Unverified'];
                const strengths = e.strengths ? (typeof e.strengths === 'string' ? JSON.parse(e.strengths) : e.strengths) : [];

                return `
                <div class="bg-[#0d131f] rounded-2xl border border-gray-800 hover:border-[#00D4AA]/25 hover:shadow-xl transition duration-300 overflow-hidden flex flex-col">
                    <div class="p-5 flex items-start gap-4">
                        <div class="relative w-16 h-16 flex-shrink-0">
                            <img src="${avatar}" alt="${escapeHtml(e.name)}" class="w-16 h-16 rounded-xl object-cover border-2 border-gray-800">
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#00D4AA] rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="#080B10" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-white text-base truncate">${escapeHtml(e.name)}</h3>
                            <p class="text-gray-400 text-sm truncate mb-2">${escapeHtml(e.professional_title || e.category || 'Expert')}</p>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gradient-to-r ${tier.bg} border ${tier.border} rounded-lg">
                                <span class="w-1.5 h-1.5 rounded-full ${tier.dot}"></span>
                                <span class="text-[10px] font-bold ${tier.text} uppercase tracking-wide">${tier.label}</span>
                                <span class="text-[9px] text-gray-600 mx-0.5">·</span>
                                <span class="text-[10px] font-bold text-white font-mono">${score}</span><span class="text-[9px] text-gray-500">/100</span>
                            </div>
                        </div>
                    </div>
                    ${skills.length ? `<div class="px-5 pb-4"><div class="flex flex-wrap gap-1.5">${skills.slice(0, 3).map(s => `<span class="px-2 py-0.5 bg-[#080B10] text-[#00D4AA] text-[10px] rounded-lg border border-[#00D4AA]/20">${escapeHtml(s)}</span>`).join('')}</div></div>` : ''}
                    ${strengths.length ? `<div class="px-5 pb-4 border-t border-gray-800/50 pt-4"><p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Why learners trust them</p><ul class="space-y-1">${strengths.slice(0, 2).map(s => `<li class="text-xs text-gray-400 flex items-start gap-2"><span class="text-[#00D4AA] mt-0.5 flex-shrink-0">•</span>${escapeHtml(s)}</li>`).join('')}</ul></div>` : ''}
                    <div class="mt-auto px-5 py-4 bg-[#080B10] border-t border-gray-800/60 flex items-center justify-between">
                        <div><span class="text-lg font-bold text-white">₹${escapeHtml(String(e.hourly_rate || 0))}</span><span class="text-gray-500 text-xs">/hr</span></div>
                        <a href="?panel=learner&page=expert-profile&expert_id=${encodeURIComponent(e.id)}" class="bg-[#00D4AA] text-[#080B10] px-5 py-2 rounded-xl text-xs font-bold hover:bg-[#00bda0] transition">View Profile</a>
                    </div>
                </div>`;
            }).join('');
        } else {
            grid.innerHTML = '<div class="col-span-full text-center py-16"><p class="text-gray-500">Founding cohort of verified experts currently onboarding. <a href="?panel=expert&page=apply" class="text-[#00D4AA]">Apply as an expert →</a></p></div>';
        }
    } catch (err) {
        console.error('Error loading featured experts:', err);
        grid.innerHTML = '<div class="col-span-full text-center py-16"><p class="text-gray-500">Founding cohort of verified experts currently onboarding. <a href="?panel=expert&page=apply" class="text-[#00D4AA]">Apply as an expert →</a></p></div>';
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(typeEffect, 1000);
        loadFeaturedExperts();
    });
} else {
    setTimeout(typeEffect, 1000);
    loadFeaturedExperts();
}
</script>
</body></html>
