<?php
// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';

$page_title = "For Learners - Nexpert.ai";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<style>
    /* Styling for For Learners landing page */
    .expert-card-stack {
        position: relative;
        height: 520px;
        width: 100%;
        max-width: 600px;
    }
    .stack-card {
        position: absolute;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }
    .stack-card-left {
        left: 0;
        top: 80px;
        z-index: 10;
        transform: scale(0.85) translateX(-20px) rotate(-2deg);
    }
    .stack-card-center {
        left: 15%;
        right: 15%;
        top: 0;
        z-index: 30;
        transform: scale(1);
        box-shadow: 0 30px 60px rgba(79, 70, 229, 0.15);
    }
    .stack-card-right {
        right: 0;
        top: 80px;
        z-index: 20;
        transform: scale(0.85) translateX(20px) rotate(2deg);
    }
    .stack-card-right-bg {
        background: linear-gradient(145deg, #1e293b, #0f172a);
        color: white;
    }
    .stack-card-left-bg {
        background: linear-gradient(145deg, #475569, #334155);
        color: white;
    }
    .stack-card:hover {
        transform: translateY(-8px) scale(var(--hover-scale, 1));
        z-index: 40;
    }
    
    .dashed-line {
        position: absolute;
        top: 24px;
        left: 50%;
        width: 100%;
        height: 2px;
        background-image: linear-gradient(to right, #e2e8f0 50%, transparent 50%);
        background-size: 12px 2px;
        background-repeat: repeat-x;
        z-index: -1;
    }
    .step-item:last-child .dashed-line {
        display: none;
    }
</style>

<div class="bg-gray-50 min-h-screen text-gray-900 relative overflow-hidden">
    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
            <!-- Left Hero Content -->
            <div class="lg:col-span-5 space-y-8">
                <h1 class="text-5xl sm:text-[64px] font-black leading-[1.1] text-gray-900 tracking-tight">
                    Learn from<br>
                    experts you can <span class="bg-gradient-to-r from-indigo-700 to-indigo-500 bg-clip-text text-transparent">trust.</span>
                </h1>
                <p class="text-gray-600 text-[17px] leading-relaxed max-w-md">
                    Find verified experts backed by real outcomes—<br>not followers, ratings, or popularity.
                </p>
                
                <!-- Search Box -->
                <div class="max-w-md pt-2">
                    <form action="index.php" method="GET" class="relative flex items-center bg-white border border-gray-200 rounded-xl p-1.5 shadow-sm focus-within:border-indigo-300 focus-within:ring-2 focus-within:ring-indigo-100 transition duration-300">
                        <input type="hidden" name="panel" value="learner">
                        <input type="hidden" name="page" value="browse-experts">
                        <div class="flex-grow pl-4 flex items-center">
                            <input type="text" name="search"
                                   placeholder="What do you want to learn today?" 
                                   class="w-full bg-transparent text-gray-900 placeholder-gray-400 focus:outline-none border-0 focus:ring-0 text-sm py-2.5">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg transition font-medium text-sm shadow-sm flex-shrink-0">
                            Find Trusted Experts
                        </button>
                    </form>
                </div>

                <!-- Learner Social Proof -->
                <div class="flex items-center gap-4 pt-4">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=128" alt="Learner">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=128" alt="Learner">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=128" alt="Learner">
                        <img class="w-10 h-10 rounded-full border-2 border-white object-cover shadow-sm" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=128" alt="Learner">
                    </div>
                    <div class="text-xs text-gray-500 font-medium">
                        <span class="text-gray-700 font-bold">Verified Trust Intelligence</span><br>
                        <span class="text-[#00D4AA] font-bold">Real-time</span> outcome tracking & behavioral evidence
                    </div>
                </div>
            </div>

            <!-- Right Hero Card Stack -->
            <div class="lg:col-span-7 flex justify-center lg:justify-end items-center relative h-[600px]">
                <div class="expert-card-stack mt-20 lg:mt-0">
                    <!-- Left Background Card -->
                    <div class="stack-card stack-card-left stack-card-left-bg border border-gray-600 w-[240px] overflow-hidden" style="--hover-scale: 0.9;">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=400" alt="Neha Verma" class="w-full h-36 object-cover object-top opacity-90">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-bold">Neha Verma</h4>
                                    <p class="text-[10px] text-gray-300 mb-4">Career Coach</p>
                                </div>
                                <span class="bg-emerald-500/20 text-emerald-400 text-[8px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Trusted
                                </span>
                            </div>
                            <div class="mb-3">
                                <span class="text-[9px] text-gray-400 block mb-1">Trust Score</span>
                                <span class="text-3xl font-black">88</span>
                            </div>
                            <div class="text-[10px] text-gray-300 font-medium">
                                <span class="text-amber-400">★</span> 4.8 (1200+)
                            </div>
                        </div>
                    </div>

                    <!-- Right Background Card -->
                    <div class="stack-card stack-card-right stack-card-right-bg border border-gray-700 w-[240px] overflow-hidden" style="--hover-scale: 0.9;">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=400" alt="Rohit Singh" class="w-full h-36 object-cover object-top opacity-90">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-bold">Rohit Singh</h4>
                                    <p class="text-[10px] text-gray-300 mb-4 leading-tight">Product Strategy<br>Expert</p>
                                </div>
                                <span class="bg-emerald-500/20 text-emerald-400 text-[8px] font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Trusted
                                </span>
                            </div>
                            <div class="mb-3">
                                <span class="text-[9px] text-gray-400 block mb-1">Trust Score</span>
                                <span class="text-3xl font-black">90</span>
                            </div>
                            <div class="text-[10px] text-gray-300 font-medium">
                                <span class="text-amber-400">★</span> 4.8 (980+)
                            </div>
                        </div>
                    </div>

                    <!-- Center Main Card -->
                    <div class="stack-card stack-card-center bg-white border border-gray-100 w-[300px] overflow-hidden rounded-3xl" style="--hover-scale: 1.05;">
                        <div class="relative bg-gray-900 h-48">
                            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=600" alt="Arjun Mehta" class="w-full h-full object-cover object-top opacity-80 mix-blend-luminosity">
                            <div class="absolute top-4 right-4">
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm text-[9px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Highly Trusted
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-5 right-5 text-white">
                                <h4 class="text-xl font-bold mb-1 shadow-sm">Arjun Mehta</h4>
                                <p class="text-xs text-gray-200 mb-2">AI & Machine Learning Expert</p>
                                <div class="flex gap-2 text-[9px] font-medium">
                                    <span class="bg-white/20 backdrop-blur-sm px-2 py-1 rounded text-white flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        12+ Years Exp
                                    </span>
                                    <span class="bg-white/20 backdrop-blur-sm px-2 py-1 rounded text-white flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        Stanford Alumnus
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-5">
                                <div>
                                    <span class="text-[10px] text-gray-500 font-medium block mb-0.5">Trust Score</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-black text-emerald-600 tracking-tighter">92</span>
                                        <span class="text-xs text-gray-400 font-bold">/100</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-500 font-medium block mb-1">Trust Tier</span>
                                    <div class="flex items-center gap-1.5 justify-end">
                                        <span class="bg-emerald-100 text-emerald-700 w-6 h-6 rounded-md flex items-center justify-center font-bold text-xs">A</span>
                                        <span class="text-[10px] text-gray-600 font-medium">Excellent</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-3 gap-2 text-center mb-5 relative">
                                <div>
                                    <span class="font-black text-gray-900 text-sm block">94%</span>
                                    <span class="text-[9px] text-gray-500 font-medium leading-tight block">Outcome<br>Achievement</span>
                                </div>
                                <div>
                                    <span class="font-black text-gray-900 text-sm block">89%</span>
                                    <span class="text-[9px] text-gray-500 font-medium leading-tight block">Repeat<br>Learners</span>
                                </div>
                                <div>
                                    <span class="font-black text-gray-900 text-sm block">96%</span>
                                    <span class="text-[9px] text-gray-500 font-medium leading-tight block">Reliability<br>Score</span>
                                </div>
                                
                                <!-- Little decorative chart -->
                                <svg class="absolute -right-1 bottom-1 w-10 h-6 text-emerald-500 opacity-60" viewBox="0 0 100 40" preserveAspectRatio="none">
                                    <polyline fill="none" stroke="currentColor" stroke-width="3" points="0,35 20,25 40,30 60,15 80,20 100,5" stroke-linejoin="round" stroke-linecap="round"/>
                                </svg>
                            </div>
                            
                            <div class="text-center">
                                <span class="text-indigo-600 text-[11px] font-bold hover:text-indigo-800 transition cursor-pointer flex items-center justify-center gap-1">
                                    View Full Trust Report <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why learners trust Nexpert -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10 border-t border-gray-200/60 bg-white rounded-t-[40px] shadow-sm mt-8">
        <div class="text-center mb-14">
            <h2 class="text-[26px] font-bold text-gray-900 tracking-tight">Why learners trust Nexpert</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 text-center">
            <!-- Point 1 -->
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full border border-gray-100 flex items-center justify-center text-blue-600 mb-4 shadow-sm bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">Verified Experts</h3>
                <p class="text-gray-500 text-[11px] leading-relaxed max-w-[160px]">Identity, credentials and<br>experience verified</p>
            </div>

            <!-- Point 2 -->
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full border border-gray-100 flex items-center justify-center text-orange-500 mb-4 shadow-sm bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">Outcome Focused</h3>
                <p class="text-gray-500 text-[11px] leading-relaxed max-w-[160px]">Experts are evaluated based<br>on real learner outcomes</p>
            </div>

            <!-- Point 3 -->
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full border border-gray-100 flex items-center justify-center text-blue-500 mb-4 shadow-sm bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">Consistent Track Record</h3>
                <p class="text-gray-500 text-[11px] leading-relaxed max-w-[160px]">We analyze long-term<br>behavior, not just reviews</p>
            </div>

            <!-- Point 4 -->
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full border border-gray-100 flex items-center justify-center text-red-500 mb-4 shadow-sm bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">Transparent & Fair</h3>
                <p class="text-gray-500 text-[11px] leading-relaxed max-w-[160px]">Trust signals are visible,<br>not hidden</p>
            </div>

            <!-- Point 5 -->
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full border border-gray-100 flex items-center justify-center text-emerald-500 mb-4 shadow-sm bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">Safe & Secure</h3>
                <p class="text-gray-500 text-[11px] leading-relaxed max-w-[160px]">Your data and payments<br>are always protected</p>
            </div>
        </div>
        
        <!-- Divider inside white block -->
        <div class="border-t border-gray-100 mt-16 pt-16">
            <div class="text-center mb-14">
                <h2 class="text-[26px] font-bold text-gray-900 tracking-tight">How it works</h2>
            </div>
            
            <div class="flex justify-between relative max-w-5xl mx-auto px-4">
                <!-- Step 1 -->
                <div class="flex flex-col items-center flex-1 relative step-item">
                    <div class="dashed-line"></div>
                    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-lg mb-4 shadow-sm relative z-10 border-4 border-white">
                        1
                    </div>
                    <div class="text-indigo-600 mb-3 bg-indigo-50 p-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Discover</h3>
                    <p class="text-gray-500 text-[11px] text-center max-w-[140px]">Explore verified experts<br>in your area of interest</p>
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col items-center flex-1 relative step-item">
                    <div class="dashed-line"></div>
                    <div class="w-12 h-12 rounded-full bg-blue-500 text-white font-bold flex items-center justify-center text-lg mb-4 shadow-sm relative z-10 border-4 border-white">
                        2
                    </div>
                    <div class="text-blue-500 mb-3 bg-blue-50 p-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Compare Trust</h3>
                    <p class="text-gray-500 text-[11px] text-center max-w-[140px]">Compare experts using<br>Trust Scores and outcomes</p>
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col items-center flex-1 relative step-item">
                    <div class="dashed-line"></div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center text-lg mb-4 shadow-sm relative z-10 border-4 border-white">
                        3
                    </div>
                    <div class="text-emerald-500 mb-3 bg-emerald-50 p-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Book Expert</h3>
                    <p class="text-gray-500 text-[11px] text-center max-w-[140px]">Book sessions that fit<br>your schedule</p>
                </div>

                <!-- Step 4 -->
                <div class="flex flex-col items-center flex-1 relative step-item">
                    <div class="dashed-line"></div>
                    <div class="w-12 h-12 rounded-full bg-amber-500 text-white font-bold flex items-center justify-center text-lg mb-4 shadow-sm relative z-10 border-4 border-white">
                        4
                    </div>
                    <div class="text-amber-500 mb-3 bg-amber-50 p-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Generate Outcomes</h3>
                    <p class="text-gray-500 text-[11px] text-center max-w-[140px]">Learn, grow and achieve<br>your goals</p>
                </div>

                <!-- Step 5 -->
                <div class="flex flex-col items-center flex-1 relative step-item">
                    <div class="dashed-line"></div>
                    <div class="w-12 h-12 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-lg mb-4 shadow-sm relative z-10 border-4 border-white">
                        5
                    </div>
                    <div class="text-purple-600 mb-3 bg-purple-50 p-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Trust Evolves</h3>
                    <p class="text-gray-500 text-[11px] text-center max-w-[140px]">Your outcomes improve<br>expert trust for all</p>
                </div>
            </div>
        </div>
        
        <!-- Explore top categories -->
        <div class="border-t border-gray-100 mt-20 pt-16">
            <div class="flex justify-between items-center mb-10 max-w-7xl mx-auto px-2">
                <h2 class="text-[26px] font-bold text-gray-900 tracking-tight">Explore top categories</h2>
                <a href="index.php?panel=learner&page=browse-experts" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1.5 transition">
                    View all categories →
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Career Coaching -->
                <!-- Career Coaching -->
                <a href="?panel=learner&page=browse-experts&category=Career" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Career Coaching</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>
                
                <!-- Data Science -->
                <a href="?panel=learner&page=browse-experts&category=Data Science" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Data Science</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>
                
                <!-- Leadership -->
                <a href="?panel=learner&page=browse-experts&category=Leadership" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Leadership</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>
                
                <!-- Product Management -->
                <a href="?panel=learner&page=browse-experts&category=Product Management" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors text-nowrap">Product Management</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>
                
                <!-- Personal Branding -->
                <a href="?panel=learner&page=browse-experts&category=Personal Branding" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Personal Branding</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>

                <!-- Soft Skills -->
                <a href="?panel=learner&page=browse-experts&category=Soft Skills" class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-indigo-300 hover:shadow-sm transition bg-white group">
                    <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Soft Skills</h3>
                        <p class="text-[10px] text-[#00D4AA] font-semibold">Explore Experts →</p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Featured Experts -->
        <div class="border-t border-gray-100 mt-20 pt-16">
            <div class="flex justify-between items-center mb-10 max-w-7xl mx-auto px-2">
                <h2 class="text-[26px] font-bold text-gray-900 tracking-tight">Featured experts</h2>
                <a href="index.php?panel=learner&page=browse-experts" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1.5 transition">
                    View all experts →
                </a>
            </div>
            
            <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-5 relative">
                <!-- Expert 1 -->
                <div class="border border-gray-200 rounded-2xl bg-white p-5 flex items-center justify-between hover:shadow-lg hover:-translate-y-1 transition duration-300 group">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=128" alt="Arjun Mehta" class="w-12 h-12 rounded-xl object-cover">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">Arjun Mehta</h4>
                            <p class="text-[9px] text-gray-500 mb-1">AI & Machine Learning Expert</p>
                            <div class="text-[9px] text-gray-500 font-medium">
                                <span class="text-amber-500 text-[11px]">★</span> 4.9 (1200+)
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <span class="text-emerald-600 bg-emerald-50 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5 mb-1">A</span>
                        <span class="text-xl font-black text-gray-900 leading-none">92</span>
                        <span class="text-[8px] text-gray-400">Trust Score</span>
                    </div>
                </div>

                <!-- Expert 2 -->
                <div class="border border-gray-200 rounded-2xl bg-white p-5 flex items-center justify-between hover:shadow-lg hover:-translate-y-1 transition duration-300 group">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=128" alt="Neha Verma" class="w-12 h-12 rounded-xl object-cover">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">Neha Verma</h4>
                            <p class="text-[9px] text-gray-500 mb-1">Career Coach</p>
                            <div class="text-[9px] text-gray-500 font-medium">
                                <span class="text-amber-500 text-[11px]">★</span> 4.8 (980+)
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <span class="text-emerald-600 bg-emerald-50 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5 mb-1">A</span>
                        <span class="text-xl font-black text-gray-900 leading-none">88</span>
                        <span class="text-[8px] text-gray-400">Trust Score</span>
                    </div>
                </div>

                <!-- Expert 3 -->
                <div class="border border-gray-200 rounded-2xl bg-white p-5 flex items-center justify-between hover:shadow-lg hover:-translate-y-1 transition duration-300 group">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=128" alt="Rohit Singh" class="w-12 h-12 rounded-xl object-cover">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">Rohit Singh</h4>
                            <p class="text-[9px] text-gray-500 mb-1">Product Strategy Expert</p>
                            <div class="text-[9px] text-gray-500 font-medium">
                                <span class="text-amber-500 text-[11px]">★</span> 4.8 (860+)
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <span class="text-emerald-600 bg-emerald-50 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5 mb-1">A</span>
                        <span class="text-xl font-black text-gray-900 leading-none">90</span>
                        <span class="text-[8px] text-gray-400">Trust Score</span>
                    </div>
                </div>

                <!-- Expert 4 -->
                <div class="border border-gray-200 rounded-2xl bg-white p-5 flex items-center justify-between hover:shadow-lg hover:-translate-y-1 transition duration-300 group">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=128" alt="Priya Iyer" class="w-12 h-12 rounded-xl object-cover">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition">Priya Iyer</h4>
                            <p class="text-[9px] text-gray-500 mb-1">Leadership Coach</p>
                            <div class="text-[9px] text-gray-500 font-medium">
                                <span class="text-amber-500 text-[11px]">★</span> 4.9 (740+)
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                        <span class="text-emerald-600 bg-emerald-50 text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-0.5 mb-1">A</span>
                        <span class="text-xl font-black text-gray-900 leading-none">91</span>
                        <span class="text-[8px] text-gray-400">Trust Score</span>
                    </div>
                </div>
                
                <!-- Carousel Arrow -->
                <button class="absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-gray-900 transition hover:shadow-lg hidden lg:flex z-10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Traditional vs Nexpert trust intelligence banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10">
        <div class="flex flex-col md:flex-row bg-white border border-gray-200 rounded-3xl overflow-hidden shadow-sm">
            <!-- Left Side (Traditional) -->
            <div class="flex-1 p-8 md:p-12 border-b md:border-b-0 md:border-r border-gray-200 relative">
                <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center font-bold text-indigo-600 text-[10px] shadow-sm z-20 hidden md:flex">
                    VS
                </div>
                <h3 class="text-xl font-bold text-gray-900 leading-tight mb-6">
                    Traditional platforms rely on ratings.
                </h3>
                <div class="flex items-center gap-1 text-amber-400 mb-6">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-8 h-8 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-8 h-8 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <p class="text-gray-500 text-sm font-medium">Easy to manipulate. Hard to trust.</p>
            </div>
            
            <!-- Right Side (Nexpert) -->
            <div class="flex-[1.5] p-8 md:p-12 bg-indigo-50/50 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-indigo-700 leading-tight mb-8">
                        Nexpert relies on Trust Intelligence.
                    </h3>
                    
                    <div class="flex flex-wrap gap-8 text-center text-indigo-900">
                        <div class="flex flex-col items-center">
                            <svg class="w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="text-[10px] font-bold">Verified<br>Identity</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span class="text-[10px] font-bold">Real<br>Outcomes</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            <span class="text-[10px] font-bold">Consistent<br>Behavior</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-[10px] font-bold">Reliable<br>Sessions</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="w-5 h-5 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[10px] font-bold">Ethical<br>Practices</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6 md:mt-0">
                    <a href="index.php?page=how-trust-works" class="border border-indigo-600 text-indigo-700 hover:bg-indigo-600 hover:text-white px-5 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2">
                        Learn more about<br>Trust Score <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Success stories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10 bg-white shadow-sm border-t border-gray-100 rounded-3xl mb-12">
        <div class="flex justify-between items-center mb-10">
            <h2 class="text-[26px] font-bold text-gray-900 tracking-tight">Success stories</h2>
            <a href="#" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm flex items-center gap-1.5 transition">
                See all stories →
            </a>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Story 1 -->
            <div class="flex gap-4">
                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=128" alt="Ananya D." class="w-12 h-12 rounded-full object-cover shrink-0 shadow-sm border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs leading-relaxed italic mb-3">
                        "I landed my dream job after learning from a Nexpert coach. The guidance was practical and life-changing."
                    </p>
                    <h4 class="text-xs font-bold text-gray-900">Ananya D.</h4>
                    <p class="text-[10px] text-gray-500">Go: placed at Google</p>
                </div>
            </div>

            <!-- Story 2 -->
            <div class="flex gap-4">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=128" alt="Vikram S." class="w-12 h-12 rounded-full object-cover shrink-0 shadow-sm border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs leading-relaxed italic mb-3">
                        "The expert I worked with helped our team improve product strategy and increase revenue by 40%."
                    </p>
                    <h4 class="text-xs font-bold text-gray-900">Vikram S.</h4>
                    <p class="text-[10px] text-gray-500">Product Manager</p>
                </div>
            </div>

            <!-- Story 3 -->
            <div class="flex gap-4">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=128" alt="Meera K." class="w-12 h-12 rounded-full object-cover shrink-0 shadow-sm border border-gray-100">
                <div>
                    <p class="text-gray-600 text-xs leading-relaxed italic mb-3">
                        "Nexpert's Trust Score helped me choose the right mentor with total confidence."
                    </p>
                    <h4 class="text-xs font-bold text-gray-900">Meera K.</h4>
                    <p class="text-[10px] text-gray-500">Career Transitioner</p>
                </div>
            </div>
        </div>
        
        <!-- Pagination dots -->
        <div class="flex justify-center gap-2 pb-2">
            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
            <span class="w-2 h-2 rounded-full bg-gray-200"></span>
            <span class="w-2 h-2 rounded-full bg-gray-200"></span>
            <span class="w-2 h-2 rounded-full bg-gray-200"></span>
        </div>
    </section>

    <!-- Bottom Actions Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 relative z-10">
        <div class="bg-indigo-600 rounded-2xl p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8 shadow-xl shadow-indigo-600/10">
            <div class="text-white space-y-2">
                <h2 class="text-2xl font-bold">Ready to learn from experts you can trust?</h2>
                <p class="text-indigo-100 text-sm">Join thousands of learners who achieve more with Nexpert.</p>
            </div>
            <div class="flex flex-wrap justify-center md:justify-end gap-3 w-full md:w-auto">
                <a href="index.php?panel=learner&page=browse-experts" class="bg-white text-indigo-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg font-bold text-xs transition-all shadow-sm flex items-center gap-2">
                    Find Trusted Experts <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="index.php?panel=expert&page=auth" class="border border-indigo-400 text-white hover:bg-indigo-500 px-5 py-2.5 rounded-lg font-bold text-xs transition-all flex items-center gap-2">
                    Become an Expert <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <button class="border border-indigo-400 text-white hover:bg-indigo-500 px-5 py-2.5 rounded-lg font-bold text-xs transition-all flex items-center gap-2">
                    Enterprise Solutions <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Partner Logos -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-10 pt-4 relative z-10">
        <p class="text-center text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-8">Trusted by learners and organizations worldwide</p>
        <div class="flex flex-wrap justify-center items-center gap-x-12 gap-y-6 opacity-60 hover:opacity-100 transition duration-300">
            <span class="text-gray-900 font-bold text-xl">Google</span>
            <span class="text-gray-900 font-bold text-xl flex items-center gap-2"><svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor"><path d="M11.4 24H0V12.6h11.4V24zM24 24H12.6V12.6H24V24zM11.4 11.4H0V0h11.4v11.4zM24 11.4H12.6V0H24v11.4z"/></svg> Microsoft</span>
            <span class="text-gray-900 font-bold text-xl">Deloitte.</span>
            <span class="text-gray-900 font-bold text-xl italic text-blue-900">PayPal</span>
            <span class="text-gray-900 font-black text-xl tracking-tighter">IBM</span>
            <span class="text-gray-900 font-bold text-xl">accenture</span>
            <span class="text-gray-900 font-bold text-xl tracking-tight">amazon</span>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
