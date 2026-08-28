<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

$page_title = "Expert Profile - Nexpert.ai";
$panel_type = "learner";

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
<!-- Expert Profile Hero Section - High Converting Landing Page Design -->
<div class="relative bg-[#131b2e] border-b border-gray-800 pt-12 md:pt-16 pb-8 md:pb-12 overflow-hidden">
    <!-- Animated Background Objects -->
    <div class="absolute inset-0 overflow-hidden">
        <!-- Gradient Orbs -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400/30 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-400/30 rounded-full blur-3xl animate-float-delay"></div>
        <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-indigo-400/20 rounded-full blur-2xl animate-pulse-slow"></div>
        <div class="absolute top-40 right-1/3 w-80 h-80 bg-pink-400/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-32 left-1/2 w-56 h-56 bg-cyan-400/25 rounded-full blur-2xl animate-float"></div>
        
        <!-- Floating Icons -->
        <div class="absolute top-32 right-20 animate-float-slow opacity-20">
            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
            </svg>
        </div>
        <div class="absolute bottom-40 left-16 animate-float opacity-20">
            <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"></path>
            </svg>
        </div>
        <div class="absolute top-1/2 right-32 animate-float-delay opacity-20">
            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
            </svg>
        </div>
        <div class="absolute top-20 left-1/3 animate-float-slow opacity-20">
            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
            </svg>
        </div>
        <div class="absolute bottom-24 right-1/4 animate-float opacity-20">
            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
            </svg>
        </div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#0e1322]/40 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="px-6 py-10 md:px-12 md:py-14">
                <div class="flex flex-col lg:flex-row items-center lg:items-start gap-10 lg:gap-16">
                    <!-- Expert Photo Section -->
                    <div class="flex-shrink-0 text-center lg:text-left">
                        <div class="relative inline-block">
                            <!-- Animated gradient ring -->
                            <div class="absolute -inset-4 bg-gradient-to-r from-yellow-400 via-pink-400 to-purple-400 rounded-full opacity-75 blur-xl animate-pulse"></div>
                            <div id="expert-photo" class="relative w-48 h-48 md:w-56 md:h-56 rounded-full bg-gradient-to-br from-gray-900 to-gray-800 shadow-2xl overflow-hidden flex items-center justify-center ring-8 ring-gray-800">
                                <div class="animate-pulse">
                                    <svg class="w-24 h-24 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Verified Expert Badge -->
                            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 lg:left-auto lg:right-0 lg:translate-x-0 flex flex-col gap-2 items-center">
                                <span class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-[#080B10] text-sm font-bold px-5 py-2.5 rounded-full shadow-lg ring-2 ring-gray-800">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>Verified Expert</span>
                                </span>
                                <!-- Agentic Trust Tier Badge -->
                                <div id="trust-tier-badge" class="hidden">
                                    <span class="inline-flex items-center gap-2 bg-indigo-600 text-white text-xs font-black px-4 py-1.5 rounded-full shadow-lg ring-2 ring-gray-800 uppercase tracking-wider">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        <span id="trust-tier-label">Unverified</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div class="mt-8 inline-flex items-center gap-2 text-[#00D4AA] bg-[#0e1322] border border-gray-800 rounded-full px-4 py-2 text-sm font-medium">
                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="expert-location">Loading...</span>
                        </div>
                    </div>

                    <!-- Expert Info Section -->
                    <div class="flex-1 text-center lg:text-left max-w-3xl">
                        <!-- Name + Verified Badge -->
                        <div class="flex flex-col lg:flex-row items-center lg:items-start gap-3 mb-4">
                            <div>
                                <h1 id="expert-name" class="text-2xl md:text-3xl lg:text-4xl font-black text-white mb-2">
                                    <span class="inline-block animate-pulse bg-white/20 rounded-lg px-6 py-3">Loading...</span>
                                </h1>
                                <div class="inline-block relative">
                                    <span class="absolute inset-0 bg-gradient-to-r from-yellow-300 via-amber-300 to-orange-300 blur-lg opacity-70"></span>
                                    <p id="expert-title" class="relative text-lg md:text-xl font-bold text-[#080B10] bg-gradient-to-r from-[#00D4AA] to-emerald-500 px-5 py-2 rounded-lg inline-block shadow-lg">
                                        <span class="animate-pulse">Loading...</span>
                                    </p>
                                </div>
                            </div>
                            <!-- Verified Badge -->
                            <div id="verified-badge-container" class="flex-shrink-0 hidden">
                                <div class="inline-flex items-center gap-2 bg-[#0e1322] text-[#00D4AA] px-4 py-2 rounded-full border border-gray-800">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-bold text-sm">Verified Expert</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rating & Social Proof -->
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 mb-6">
                            <div class="flex items-center gap-2 text-white">
                                <svg class="w-5 h-5 text-cyan-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                </svg>
                                <span id="expert-total-sessions" class="font-semibold">0</span>
                                <span class="text-white/80">sessions completed</span>
                            </div>
                            <div class="text-white/60">•</div>
                            <div class="flex items-center gap-2 text-white">
                                <svg class="w-5 h-5 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                                <span id="expert-experience-header" class="font-semibold">0 years</span>
                                <span class="text-white/80">experience</span>
                            </div>
                        </div>

                        <!-- Skills Tags -->
                        <div class="mb-8">
                            <div class="text-sm font-semibold text-white/80 uppercase tracking-wide mb-3">Expert In</div>
                            <div id="expert-skills" class="flex flex-wrap gap-2 justify-center lg:justify-start">
                                <!-- Skills loaded dynamically -->
                            </div>
                        </div>

                        <!-- CTA Section -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <!-- Primary CTA -->
                            <a id="book-session-btn" href="?panel=learner&page=booking" class="group relative inline-flex items-center gap-3 bg-[#00D4AA] text-[#080B10] px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Book a Session Now</span>
                                <div class="absolute -top-3 -right-3 bg-[#00D4AA] text-[#080B10] text-xs font-bold px-3 py-1 rounded-full animate-bounce">
                                    <span id="expert-hourly-rate">₹0</span>/hr
                                </div>
                            </a>
                            
                            <!-- Secondary CTA -->
                            <button id="hero-message-btn" class="inline-flex items-center gap-3 bg-[#0e1322] border border-gray-800 text-white px-8 py-4 rounded-full font-bold text-lg shadow-lg hover:border-gray-700 hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Send Message</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Trust Bar -->
            <div class="border-t border-white/20 bg-white/5 backdrop-blur-sm px-6 py-6">
                <div class="flex flex-wrap items-center justify-center gap-8 text-center">
                    <div class="flex items-center gap-2">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                            <svg class="w-5 h-5 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-white">Identity Verified</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                            <svg class="w-5 h-5 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-white">Background Checked</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                            <svg class="w-5 h-5 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-white">Top Rated</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-full">
                            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-white">Fast Response</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-[#080B10] py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Programs Showcase -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-black text-white mb-4">
                    Transform Your Skills
                </h2>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                    Join structured learning programs designed to deliver real results
                </p>
            </div>
            
            <div id="programs-container" class="flex overflow-x-auto gap-6 pb-4 snap-x snap-mandatory scrollbar-hide" style="scroll-behavior: smooth; -webkit-overflow-scrolling: touch;">
                <!-- Programs loaded dynamically -->
                <div class="col-span-full text-center py-16">
                    <div class="inline-block">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <p class="text-gray-400 font-medium">Loading programs...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- About & Stats Section -->
        <div class="grid lg:grid-cols-3 gap-8 mb-16">
            <!-- Main Content - About -->
            <div class="lg:col-span-2">
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-8 md:p-12">
                    <h2 class="text-2xl font-black text-white mb-6 flex items-center gap-3">
                        <span class="w-2 h-8 bg-gradient-to-b from-[#00D4AA] to-emerald-500 rounded-full"></span>
                        About Me
                    </h2>
                    <div id="expert-bio" class="prose prose-lg max-w-none text-gray-300 leading-relaxed whitespace-pre-line">
                        <div class="space-y-3">
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-5/6"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-4/6"></div>
                        </div>
                    </div>

                    <!-- Achievement Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10 pt-8 border-t border-gray-800">
                        <div class="text-center p-6 bg-[#0e1322] border border-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                            <svg class="w-12 h-12 mx-auto mb-3 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            <div id="expert-total-sessions-stat" class="text-4xl font-black text-[#00D4AA] mb-1">0</div>
                            <div class="text-gray-400 text-sm font-bold uppercase tracking-wide">Sessions</div>
                        </div>
                        <div class="text-center p-6 bg-[#0e1322] border border-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                            <svg class="w-12 h-12 mx-auto mb-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            <div id="expert-experience-years" class="text-4xl font-black text-emerald-500 mb-1">0</div>
                            <div class="text-gray-400 text-sm font-bold uppercase tracking-wide">Experience</div>
                        </div>
                        <div class="text-center p-6 bg-[#0e1322] border border-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                            <svg class="w-12 h-12 mx-auto mb-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <div id="expert-trust-score-stat" class="text-4xl font-black text-indigo-400 mb-1">0%</div>
                            <div class="text-gray-400 text-sm font-bold uppercase tracking-wide">Trust Score</div>
                        </div>
                        <div class="text-center p-6 bg-[#0e1322] border border-gray-800 rounded-xl hover:shadow-lg transition-shadow">
                            <svg class="w-12 h-12 mx-auto mb-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div id="expert-stability-stat" class="text-4xl font-black text-cyan-400 mb-1">0%</div>
                            <div class="text-gray-400 text-sm font-bold uppercase tracking-wide">Stability</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Quick Actions & Info -->
            <div class="space-y-6">
                <!-- Quick Action Card -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
                    <div class="relative">
                        <h3 class="text-xl font-black mb-2 text-white">Ready to Start?</h3>
                        <p class="text-gray-400 mb-6 text-sm">Book a session or send a message to get started on your learning journey.</p>
                        <div class="space-y-3">
                            <a id="sidebar-book-btn" href="?panel=learner&page=booking" class="block w-full bg-[#00D4AA] text-[#080B10] px-6 py-3.5 rounded-xl hover:bg-[#00bda0] transition text-center font-black shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                                📅 Book Session
                            </a>
                            <button id="sidebar-message-btn" class="w-full bg-transparent border border-gray-800 text-gray-300 hover:text-white px-6 py-3.5 rounded-xl hover:bg-[#0e1322] hover:border-gray-700 transition text-center font-black">
                                💬 Send Message
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Webinars Card -->
                <div id="webinars-sidebar" class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-6" style="display: none;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">Upcoming Webinars</h3>
                        <span id="webinars-count-badge" class="bg-[#0e1322] text-[#00D4AA] border border-[#00D4AA]/30 px-2 py-1 rounded-full text-xs font-semibold">0</span>
                    </div>
                    <div id="webinars-list" class="space-y-4">
                        <!-- Webinars will be loaded here -->
                    </div>
                </div>

                <!-- Verification Card -->
                <div class="bg-[#131b2e] rounded-2xl shadow-xl p-6 border border-emerald-800/40">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 bg-gradient-to-br from-emerald-500 to-teal-600 text-[#080B10] rounded-full p-3 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-black text-white text-lg mb-1">Verified Expert</div>
                            <div class="text-sm text-gray-400 leading-relaxed">Identity & credentials verified by Nexpert.ai</div>
                        </div>
                    </div>
                </div>

                <!-- Response Time Card -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-[#0e1322] border border-gray-800 rounded-full mb-3">
                            <svg class="w-8 h-8 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="text-3xl font-black text-[#00D4AA] mb-1">&lt; 2 hrs</div>
                        <div class="text-sm text-gray-400 font-semibold">Average Response Time</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credibility Context Section -->
        <div class="mb-16">
            <div class="bg-[#131b2e] rounded-3xl p-8 md:p-12 border border-gray-800 shadow-sm">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-2xl md:text-3xl font-black text-white mb-6">Expert Credibility</h2>
                    <p class="text-lg text-gray-400 mb-8 leading-relaxed">
                        Nexpert uses an AI-driven, behavioral credibility engine to evaluate experts. Our system analyzes over 50 data points including session structure, outcome consistency, and professional boundaries to assign Trust Tiers.
                    </p>
                    <div class="grid sm:grid-cols-3 gap-6">
                        <div class="bg-[#0e1322] p-6 rounded-2xl shadow-sm border border-gray-800">
                            <div class="text-indigo-400 font-black text-xl mb-1">Tier A</div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Top 5% Experts</p>
                        </div>
                        <div class="bg-[#0e1322] p-6 rounded-2xl shadow-sm border border-gray-800">
                            <div class="text-blue-400 font-black text-xl mb-1">Stability</div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Long-term consistency</p>
                        </div>
                        <div class="bg-[#0e1322] p-6 rounded-2xl shadow-sm border border-gray-800">
                            <div class="text-[#00D4AA] font-black text-xl mb-1">Verified</div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Identity Confirmed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final CTA Banner -->
        <div class="relative bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute inset-0">
                <div class="absolute top-0 left-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl translate-x-1/3 translate-y-1/3"></div>
            </div>
            <div class="relative px-8 py-16 md:py-20 text-center">
                <h2 class="text-2xl md:text-3xl font-black text-white mb-4">
                    Ready to Begin Your Journey?
                </h2>
                <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                    Book your first session today and experience personalized learning that delivers results
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a id="final-cta-book" href="?panel=learner&page=booking" class="inline-flex items-center gap-3 bg-[#00D4AA] text-[#080B10] px-10 py-5 rounded-full font-black text-xl shadow-2xl hover:shadow-3xl hover:scale-110 active:scale-95 transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Book Your Session
                    </a>
                    <button id="final-cta-message" class="inline-flex items-center gap-3 bg-transparent border-2 border-gray-800 text-white px-10 py-5 rounded-full font-black text-xl hover:bg-[#0e1322] hover:border-gray-700 backdrop-blur-sm transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Ask a Question
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    console.log('Expert Profile BASE_PATH detected as:', window.BASE_PATH);
</script>
<script src="<?php echo BASE_PATH; ?>/admin-panel/js/learner-expert-profile.js?v=<?php echo time(); ?>"></script>
<?php require_once 'includes/footer.php'; ?>
