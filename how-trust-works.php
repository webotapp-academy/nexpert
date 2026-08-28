<?php
// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';

$page_title = "How Trust Works - Nexpert.ai";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<div class="bg-white min-h-screen text-gray-900 font-sans selection:bg-indigo-100 selection:text-indigo-900 pb-20">

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 relative overflow-hidden">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <!-- Left Content -->
            <div class="space-y-8 z-10 relative">
                <h1 class="text-5xl lg:text-6xl font-black leading-tight text-gray-900 tracking-tight">
                    How Nexpert<br>
                    <span class="text-emerald-500">Trust Works</span>
                </h1>
                
                <div class="space-y-4">
                    <p class="text-xl font-bold text-gray-900">
                        AI evaluates. Signals verify. Trust evolves.
                    </p>
                    <p class="text-gray-600 text-lg leading-relaxed max-w-md">
                        We don't rely on likes or ratings. Our AI infrastructure analyzes real behavioral signals and outcomes over time to surface experts you can truly trust.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="?panel=learner&page=browse-experts" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                        See Trusted Experts
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="?panel=expert&page=auth" class="border-2 border-orange-200 text-orange-600 hover:bg-orange-50 px-8 py-3.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
                        Become an Expert
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
            
            <!-- Right Content - Orbit Graphic -->
            <div class="relative h-[450px] w-full flex items-center justify-center">
                <!-- Outer dashed ring -->
                <div class="absolute w-[400px] h-[400px] border border-dashed border-gray-300 rounded-full animate-[spin_60s_linear_infinite]"></div>
                <!-- Inner dashed ring -->
                <div class="absolute w-[260px] h-[260px] border border-dashed border-gray-300 rounded-full animate-[spin_40s_linear_infinite_reverse]"></div>
                
                <!-- Central Logo -->
                <div class="absolute z-20 w-24 h-24 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-500/30 transform rotate-12 transition-transform hover:rotate-0 duration-500">
                    <span class="text-white text-5xl font-extrabold transform -rotate-12 hover:rotate-0 duration-500">N</span>
                </div>
                <!-- Pulse rings around center -->
                <div class="absolute z-10 w-24 h-24 bg-indigo-100 rounded-2xl animate-ping opacity-75"></div>
                <div class="absolute z-10 w-36 h-36 border border-indigo-100 rounded-full"></div>
                <div class="absolute z-10 w-48 h-48 border border-indigo-50 rounded-full"></div>

                <!-- Floating Badges -->
                <div class="absolute z-30 w-full h-full">
                    <!-- Top Left -->
                    <div class="absolute top-[10%] left-[5%] bg-white px-4 py-3 rounded-2xl shadow-lg border border-gray-100 flex items-center gap-3 transform -translate-x-4 animate-[bounce_3s_infinite_alternate]">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-16 leading-tight">Behavior Analyzed</span>
                    </div>
                    
                    <!-- Top Right -->
                    <div class="absolute top-[5%] right-[5%] bg-white px-4 py-3 rounded-2xl shadow-lg border border-gray-100 flex items-center gap-3 transform translate-x-4 animate-[bounce_4s_infinite_alternate-reverse]">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-16 leading-tight">Outcomes Verified</span>
                    </div>
                    
                    <!-- Bottom Left -->
                    <div class="absolute bottom-[10%] left-[0%] bg-white px-4 py-3 rounded-2xl shadow-lg border border-gray-100 flex items-center gap-3 transform -translate-x-2 animate-[bounce_3.5s_infinite_alternate-reverse]">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-20 leading-tight">Consistency Confirmed</span>
                    </div>
                    
                    <!-- Bottom Right -->
                    <div class="absolute bottom-[5%] right-[10%] bg-white px-4 py-3 rounded-2xl shadow-lg border border-gray-100 flex items-center gap-3 transform translate-x-2 animate-[bounce_4.5s_infinite_alternate]">
                        <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-12 leading-tight">Trust Evolves</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Architecture Timeline -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-gray-100">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-4 tracking-tight">Our Trust Architecture</h2>
            <p class="text-gray-500 font-medium max-w-xl mx-auto">Nexpert's AI agents work 24/7 to evaluate, validate, and evolve trust.</p>
        </div>
        
        <!-- Timeline Grid -->
        <div class="relative">
            <!-- Connecting Line -->
            <div class="hidden md:block absolute top-[52px] left-0 w-full h-[2px] bg-gray-100 z-0"></div>
            
            <div class="grid grid-cols-2 md:grid-cols-6 gap-6 relative z-10">
                <!-- Step 1 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">1</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-blue-500 mb-4 group-hover:border-blue-500 transition-colors bg-blue-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">Discover &<br>Onboard</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">Experts go through identity verification, credential checks and profile review.</p>
                </div>
                
                <!-- Step 2 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">2</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-emerald-500 mb-4 group-hover:border-emerald-500 transition-colors bg-emerald-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">Signal<br>Collection</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">We collect thousands of behavioral signals across sessions, communications, engagements, and outcomes.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-purple-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">3</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-purple-500 mb-4 group-hover:border-purple-500 transition-colors bg-purple-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">AI<br>Analysis</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">Our AI models analyze patterns, consistency, quality, and reliability continuously.</p>
                </div>
                
                <!-- Step 4 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">4</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-orange-500 mb-4 group-hover:border-orange-500 transition-colors bg-orange-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">Trust<br>Scoring</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">Each expert receives a dynamic Trust Score based on verified signals and long-term behavior.</p>
                </div>
                
                <!-- Step 5 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">5</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-blue-500 mb-4 group-hover:border-blue-500 transition-colors bg-blue-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">Continuous<br>Updates</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">Scores evolve in real-time as new signals and outcomes are observed over time.</p>
                </div>
                
                <!-- Step 6 -->
                <div class="flex flex-col items-center text-center relative group">
                    <div class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center absolute -top-10 shadow-md ring-4 ring-white group-hover:-translate-y-1 transition-transform">6</div>
                    <div class="w-24 h-24 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-emerald-500 mb-4 group-hover:border-emerald-500 transition-colors bg-emerald-50/30">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-2">You Get<br>Trusted Experts</h3>
                    <p class="text-[11px] text-gray-500 leading-tight">We surface only the most trustworthy experts for your goals.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Signals vs Scoring -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-gray-100">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-24">
            
            <!-- Left: Signals We Analyze -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 lg:p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">Signals We Analyze</h3>
                <p class="text-sm text-gray-500 mb-8 font-medium">Real behaviors. Real outcomes. Real impact.</p>
                
                <div class="grid grid-cols-2 gap-x-4 gap-y-5 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Session completion rate</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-emerald-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Outcome achievement</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-purple-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Repeat learner engagement</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-blue-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Problem resolution rate</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-emerald-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.514"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Learner feedback quality</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-indigo-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Content quality & expertise</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-pink-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Communication reliability</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Cancellation & no-show rate</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-orange-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Response time & professional</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Consistency over time</span>
                    </div>
                    <div class="col-span-2 flex items-center gap-3">
                        <div class="text-purple-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg></div>
                        <span class="text-xs font-semibold text-gray-700">Long-term impact on learners</span>
                    </div>
                </div>
                
                <a href="#" class="inline-flex items-center gap-2 border border-gray-200 text-indigo-600 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-bold transition-colors">
                    View all signals we track <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            
            <!-- Right: How We Calculate Trust Score -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 lg:p-8 h-full flex flex-col">
                <h3 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">How We Calculate Trust Score</h3>
                <p class="text-sm text-gray-500 mb-8 font-medium">Weighted across key pillars.</p>
                
                <div class="flex flex-col sm:flex-row items-center gap-8 mb-8">
                    <!-- Pure CSS Donut Chart -->
                    <div class="relative w-40 h-40 rounded-full flex items-center justify-center shrink-0" 
                         style="background: conic-gradient(
                            #f59e0b 0% 10%,      /* Orange: Reliability 10% */
                            #10b981 10% 20%,     /* Green: Community 10% */
                            #3b82f6 20% 30%,     /* Blue: Identity 10% */
                            #93c5fd 30% 35%,     /* Light Blue: Verification 5% */
                            #4f46e5 35% 65%,     /* Indigo: Outcome Achievement 30% */
                            #14b8a6 65% 85%,     /* Teal: Consistency 20% */
                            #8b5cf6 85% 100%     /* Purple: Engagement Quality 15% */
                         );">
                        <div class="w-32 h-32 bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                            <span class="text-xs font-semibold text-gray-500 mb-1">Trust<br>Score</span>
                            <span class="text-4xl font-black text-gray-900 leading-none">92</span>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Excellent</span>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div class="flex-1 w-full space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-indigo-600"></span><span class="font-semibold text-gray-700">Outcome Achievement</span></div>
                            <span class="font-bold text-gray-900">30%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-teal-500"></span><span class="font-semibold text-gray-700">Consistency</span></div>
                            <span class="font-bold text-gray-900">20%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-purple-500"></span><span class="font-semibold text-gray-700">Engagement Quality</span></div>
                            <span class="font-bold text-gray-900">15%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-amber-500"></span><span class="font-semibold text-gray-700">Reliability</span></div>
                            <span class="font-bold text-gray-900">10%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="font-semibold text-gray-700">Community Signals</span></div>
                            <span class="font-bold text-gray-900">10%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="font-semibold text-gray-700">Identity Verification</span></div>
                            <span class="font-bold text-gray-900">10%</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-300"></span><span class="font-semibold text-gray-700">Verification & Compliance</span></div>
                            <span class="font-bold text-gray-900">5%</span>
                        </div>
                    </div>
                </div>
                
                <a href="#" class="inline-flex items-center gap-2 border border-gray-200 text-indigo-600 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-bold transition-colors">
                    Learn more about Trust Score <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            
        </div>
    </section>

    <!-- Example: Trust in Action -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Example: Trust in Action</h3>
            <button class="border border-gray-200 bg-white text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1 shadow-sm">
                Last 3 Months <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 lg:p-8">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16">
                <!-- Expert Info & Metrics -->
                <div>
                    <div class="flex items-center gap-4 mb-8">
                        <img src="<?php echo BASE_PATH; ?>/attached_assets/stock_images/diverse_professional_1d96e39f.jpg" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-lg font-bold text-gray-900">Arjun Mehta</h4>
                                <span class="bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Verified
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">Leadership & Organizational Development</div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs text-gray-500 font-medium">Online</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <h5 class="text-sm font-bold text-gray-900 mb-4">Why this expert is trusted</h5>
                        <div class="grid sm:grid-cols-2 gap-y-3 gap-x-4">
                            <div class="flex items-start gap-2">
                                <div class="text-emerald-500 mt-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                                <span class="text-xs text-gray-600 font-medium">Consistent high-quality sessions over 12+ months</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <div class="text-emerald-500 mt-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                                <span class="text-xs text-gray-600 font-medium">Reliable communication and professionalism</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <div class="text-emerald-500 mt-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                                <span class="text-xs text-gray-600 font-medium">Strong repeat learner engagement (89%)</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <div class="text-emerald-500 mt-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                                <span class="text-xs text-gray-600 font-medium">Low cancellation rate and high punctuality</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <div class="text-emerald-500 mt-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                                <span class="text-xs text-gray-600 font-medium">Verified positive outcomes and goal achievement</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart & Metrics (Right side) -->
                <div>
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Trust Score</div>
                            <div class="text-3xl font-black text-gray-900">92</div>
                            <div class="text-[10px] font-bold text-emerald-500 mt-1">Excellent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Stability (12m)</div>
                            <div class="text-3xl font-black text-gray-900">94%</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Repeat Learners</div>
                            <div class="text-3xl font-black text-gray-900">89%</div>
                        </div>
                        <div class="text-center">
                            <div class="text-[10px] text-gray-500 font-semibold mb-1 uppercase tracking-wider">Outcome Rate</div>
                            <div class="text-3xl font-black text-gray-900">93%</div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-6 border border-gray-100 flex flex-col">
                        <h4 class="text-sm font-bold text-gray-900 mb-6">Trust Score Over Time</h4>
                        <div class="flex-grow relative w-full h-48">
                            <svg viewBox="0 0 400 200" class="w-full h-full overflow-visible">
                                <line x1="0" y1="200" x2="400" y2="200" stroke="#f3f4f6" stroke-width="1" />
                                <line x1="0" y1="150" x2="400" y2="150" stroke="#f3f4f6" stroke-width="1" />
                                <line x1="0" y1="100" x2="400" y2="100" stroke="#f3f4f6" stroke-width="1" />
                                <line x1="0" y1="50" x2="400" y2="50" stroke="#f3f4f6" stroke-width="1" />
                                <line x1="0" y1="0" x2="400" y2="0" stroke="#f3f4f6" stroke-width="1" />
                                
                                <text x="-10" y="204" font-size="10" fill="#9ca3af" text-anchor="end">0</text>
                                <text x="-10" y="154" font-size="10" fill="#9ca3af" text-anchor="end">25</text>
                                <text x="-10" y="104" font-size="10" fill="#9ca3af" text-anchor="end">50</text>
                                <text x="-10" y="54" font-size="10" fill="#9ca3af" text-anchor="end">75</text>
                                <text x="-10" y="4" font-size="10" fill="#9ca3af" text-anchor="end">100</text>
                                
                                <text x="20" y="220" font-size="10" fill="#9ca3af" text-anchor="middle">Apr 1</text>
                                <text x="92" y="220" font-size="10" fill="#9ca3af" text-anchor="middle">Apr 15</text>
                                <text x="164" y="220" font-size="10" fill="#9ca3af" text-anchor="middle">May 1</text>
                                <text x="236" y="220" font-size="10" fill="#9ca3af" text-anchor="middle">May 15</text>
                                <text x="308" y="220" font-size="10" fill="#9ca3af" text-anchor="middle">Jun 1</text>
                                
                                <polyline points="20,150 92,100 164,96 236,44 308,70 380,30" fill="none" stroke="#10b981" stroke-width="2" />
                                <circle cx="20" cy="150" r="3" fill="#10b981" />
                                <circle cx="92" cy="100" r="3" fill="#10b981" />
                                <circle cx="164" cy="96" r="3" fill="#10b981" />
                                <circle cx="236" cy="44" r="3" fill="#10b981" />
                                <circle cx="308" cy="70" r="3" fill="#10b981" />
                                <circle cx="380" cy="30" r="3" fill="#10b981" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Pillars Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Trust is not given. It's earned. Continuously.</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] text-center transition-transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h4 class="font-bold text-gray-900 text-sm mb-2">Privacy First</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Your data is encrypted and never sold.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] text-center transition-transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h4 class="font-bold text-gray-900 text-sm mb-2">Fair & Unbiased</h4>
                <p class="text-xs text-gray-500 leading-relaxed">We evaluate quality, not popularity.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] text-center transition-transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h4 class="font-bold text-gray-900 text-sm mb-2">Human + AI</h4>
                <p class="text-xs text-gray-500 leading-relaxed">AI analyzes signals, humans ensure fairness.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] text-center transition-transform hover:-translate-y-1">
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
                <h4 class="font-bold text-gray-900 text-sm mb-2">Transparent</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Our methodology is explainable.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] text-center transition-transform hover:-translate-y-1 md:col-span-1 col-span-2">
                <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h4 class="font-bold text-gray-900 text-sm mb-2">Enterprise Grade</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Built with security, scalability and compliance.</p>
            </div>
        </div>
    </section>

    <!-- CTAs -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 mb-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">What would you like to do next?</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-blue-50/40 border border-blue-100 rounded-2xl p-8 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-12 h-12 rounded-xl bg-white border border-blue-200 text-blue-600 flex items-center justify-center mb-6 shadow-sm relative z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3 relative z-10">For Learners</h3>
                <p class="text-sm text-gray-600 mb-8 h-10 relative z-10">Find experts you can trust and achieve your goals.</p>
                <a href="?panel=learner&page=browse-experts" class="inline-flex items-center gap-2 text-emerald-600 font-bold hover:text-emerald-700 bg-white border border-emerald-200 hover:border-emerald-300 rounded-lg px-4 py-2.5 text-sm shadow-sm transition-colors relative z-10 w-max">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]">1</span> Find Trusted Experts <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            
            <div class="bg-indigo-50/40 border border-indigo-100 rounded-2xl p-8 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-12 h-12 rounded-xl bg-white border border-indigo-200 text-indigo-600 flex items-center justify-center mb-6 shadow-sm relative z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3 relative z-10">For Experts</h3>
                <p class="text-sm text-gray-600 mb-8 h-10 relative z-10">Build your reputation and grow your impact on Nexpert.</p>
                <a href="?panel=expert&page=auth" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-700 bg-white border border-indigo-200 hover:border-indigo-300 rounded-lg px-4 py-2.5 text-sm shadow-sm transition-colors relative z-10 w-max">
                    <span class="w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px]">2</span> Become a Verified Expert <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
            
            <div class="bg-teal-50/40 border border-teal-100 rounded-2xl p-8 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-teal-500/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="w-12 h-12 rounded-xl bg-white border border-teal-200 text-teal-600 flex items-center justify-center mb-6 shadow-sm relative z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3 relative z-10">For Enterprise</h3>
                <p class="text-sm text-gray-600 mb-8 h-10 relative z-10">Empower your teams with trusted expertise.</p>
                <a href="#" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:text-blue-700 bg-white border border-blue-200 hover:border-blue-300 rounded-lg px-4 py-2.5 text-sm shadow-sm transition-colors relative z-10 w-max">
                    <span class="w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-[10px]">12</span> Book an Enterprise Demo <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
