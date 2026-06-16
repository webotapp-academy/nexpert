<?php
// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';

$page_title = "Nexpert.ai - Global Expert Learning Platform";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
    <!-- Hero Section -->
    <section class="bg-[#080B10] py-20 relative overflow-hidden min-h-[75vh] flex items-center">
        <!-- Subtle Background Glow -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-[#00D4AA]/5 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 relative z-10 w-full text-center">
            <!-- Subtitle -->
            <div class="text-[#00D4AA] text-xs font-bold uppercase tracking-[0.2em] mb-6">
                THE TRUSTABLE OS FOR LEARNING & EXPERTISE
            </div>
            
            <!-- Main Headline -->
            <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-[1.15] tracking-tight">
                Learn from experts you can trust.<br>
                <span class="text-[#00D4AA]">Build a reputation that lasts.</span>
            </h1>
            
            <!-- Description -->
            <p class="text-gray-400 text-lg md:text-xl max-w-3xl mx-auto mb-12 leading-relaxed">
                A platform where learners grow with verified guidance and experts build long-term trust through real outcomes. Powered by AI-verified signals.
            </p>
            
            <!-- Search Container -->
            <div class="max-w-3xl mx-auto mb-8">
                <form id="expertSearchForm" class="relative flex items-center bg-[#0d131f] border border-gray-800 rounded-2xl p-2.5 shadow-2xl focus-within:border-gray-700 transition duration-300">
                    <div class="flex-grow pl-3 flex items-center">
                        <input id="searchInput" type="text" 
                               placeholder="What do you want to learn?" 
                               class="w-full bg-transparent text-white placeholder-gray-500 focus:outline-none border-0 focus:ring-0 text-base py-3 pr-44">
                    </div>
                    <div class="absolute right-2.5">
                        <button type="submit" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-7 py-3.5 rounded-xl transition font-bold text-sm shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                            Find Trusted Experts
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tag Pills -->
            <div class="flex flex-wrap justify-center gap-3">
                <button onclick="searchForExpertise('AI & ML')" class="px-5 py-2.5 bg-[#0d131f] border border-gray-800 hover:border-gray-700 text-gray-400 hover:text-white text-sm font-medium rounded-xl transition duration-300 hover:scale-105">
                    AI & ML
                </button>
                <button onclick="searchForExpertise('Product Strategy')" class="px-5 py-2.5 bg-[#0d131f] border border-gray-800 hover:border-gray-700 text-gray-400 hover:text-white text-sm font-medium rounded-xl transition duration-300 hover:scale-105">
                    Product Strategy
                </button>
                <button onclick="searchForExpertise('Data Science')" class="px-5 py-2.5 bg-[#0d131f] border border-gray-800 hover:border-gray-700 text-gray-400 hover:text-white text-sm font-medium rounded-xl transition duration-300 hover:scale-105">
                    Data Science
                </button>
                <button onclick="searchForExpertise('UX Design')" class="px-5 py-2.5 bg-[#0d131f] border border-gray-800 hover:border-gray-700 text-gray-400 hover:text-white text-sm font-medium rounded-xl transition duration-300 hover:scale-105">
                    UX Design
                </button>
            </div>
        </div>
    </section>

    <!-- Expert Categories Section -->
    <section id="categories" class="py-20 bg-[#0E1322] border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-[#00D4AA]/20 to-[#00D4AA]/10 border border-[#00D4AA]/30 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-4xl font-bold text-white mb-4">Expert Categories</h3>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                    Find the right expert for your specific needs across our comprehensive categories
                </p>
            </div>
            
            <!-- Category Cards Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- AI & Technology -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">AI & Technology</h4>
                    <p class="text-gray-400 text-sm mb-4">Artificial intelligence, machine learning, software development, cloud computing</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">1,200+ Experts</span>
                        <a href="?panel=learner&page=browse-experts&category=AI%20%26%20Technology" class="text-[#00D4AA] font-semibold text-sm hover:text-white flex items-center transition">
                            Explore AI & Tech
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Leadership -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Leadership</h4>
                    <p class="text-gray-400 text-sm mb-4">Executive coaching, management training, team leadership, organizational growth</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">800+ Experts</span>
                        <a href="?panel=learner&page=browse-experts&category=Leadership" class="text-[#00D4AA] font-semibold text-sm hover:text-white flex items-center transition">
                            Find Leaders
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- career Growth -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">career Growth</h4>
                    <p class="text-gray-400 text-sm mb-4">Resume building, interview prep, career transitions, professional development</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">600+ Experts</span>
                        <a href="?panel=learner&page=browse-experts&category=career%20Growth" class="text-[#00D4AA] font-semibold text-sm hover:text-white flex items-center transition">
                            Grow Career
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Entrepreneurship -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Entrepreneurship</h4>
                    <p class="text-gray-400 text-sm mb-4">Startup advice, raising capital, business modeling, launching ventures</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">900+ Experts</span>
                        <a href="?panel=learner&page=browse-experts&category=Entrepreneurship" class="text-[#00D4AA] font-semibold text-sm hover:text-white flex items-center transition">
                            Explore Startups
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Product&Strategy -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">Product&Strategy</h4>
                    <p class="text-gray-400 text-sm mb-4">Product management, market strategy, positioning, user research, marketing strategy</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">1,500+ Experts</span>
                        <a href="?panel=learner&page=browse-experts&category=Product%26Strategy" class="text-[#00D4AA] font-semibold text-sm hover:text-white flex items-center transition">
                            Find Strategists
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- All Categories -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl p-6 shadow-md hover:border-[#00D4AA]/30 hover:shadow-xl transition duration-300">
                    <div class="w-16 h-16 bg-[#00D4AA]/10 border border-[#00D4AA]/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-3">All Categories</h4>
                    <p class="text-gray-400 text-sm mb-4">Browse all expert categories and find the perfect match for your learning goals</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-300 bg-gray-800 px-3 py-1 rounded-full">5,000+ Total</span>
                        <a href="?panel=learner&page=browse-experts" class="text-white font-semibold text-sm hover:text-[#00D4AA] flex items-center transition">
                            View All
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-[#080B10] border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-4xl font-bold text-white mb-4">How Nexpert.ai Works</h3>
                <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                    Get started in 3 simple steps and connect with the right expert for your goals
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="w-20 h-20 bg-[#00D4AA] text-[#080B10] rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-extrabold shadow-lg">
                        <span>1</span>
                    </div>
                    <h4 class="text-xl font-semibold text-white mb-4">Browse & Choose</h4>
                    <p class="text-gray-400 leading-relaxed">Search through our curated list of verified experts across 5 categories. Read reviews, check availability, and find your perfect match.</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 bg-[#00D4AA] text-[#080B10] rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-extrabold shadow-lg">
                        <span>2</span>
                    </div>
                    <h4 class="text-xl font-semibold text-white mb-4">Book & Pay</h4>
                    <p class="text-gray-400 leading-relaxed">Schedule your session at a convenient time. Make secure payments through our integrated payment gateway with full refund protection.</p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 bg-[#00D4AA] text-[#080B10] rounded-full flex items-center justify-center mx-auto mb-6 text-2xl font-extrabold shadow-lg">
                        <span>3</span>
                    </div>
                    <h4 class="text-xl font-semibold text-white mb-4">Learn & Grow</h4>
                    <p class="text-gray-400 leading-relaxed">Join your 1-on-1 video session, receive personalized guidance, assignments, and track your progress over time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Experts Section -->
    <section id="experts" class="py-20 bg-[#0E1322] border-t border-gray-900">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-4xl font-bold text-white mb-4">Meet Our Top Experts</h3>
                <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                    Connect with India's most experienced professionals who are passionate about sharing their knowledge
                </p>
            </div>
            
            <div id="featured-experts-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Experts will be loaded dynamically -->
                <div class="col-span-full text-center py-8">
                    <div class="inline-block">
                        <div class="w-12 h-12 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mx-auto"></div>
                        <p class="text-gray-400 mt-4">Loading top experts...</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="?panel=learner&page=browse-experts" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-8 py-3.5 rounded-xl transition text-lg font-bold shadow-lg inline-block">
                    View All Experts
                </a>
            </div>
        </div>
    </section>




    <!-- Call to Action Section -->
    <section class="py-20 bg-[#0E1322] border-t border-gray-900">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h3 class="text-4xl font-bold text-white mb-6">Ready to Accelerate Your Growth?</h3>
            <p class="text-xl text-gray-400 mb-8">
                Join thousands of learners who have transformed their careers with Nexpert.ai
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="?panel=learner&page=auth" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg">
                    Start Learning Today
                </a>
                <a href="?panel=expert&page=auth" class="border-2 border-gray-700 text-gray-300 px-8 py-4 rounded-xl font-bold text-lg hover:text-white hover:border-white transition">
                    Become an Expert
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#080B10] border-t border-gray-900 text-white py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="grid md:grid-cols-4 gap-12">
                <div class="md:col-span-2">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-[#00D4AA] rounded-xl flex items-center justify-center font-extrabold text-[#080B10] text-2xl mr-4 shadow-lg">
                            N
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-white">nexpert.ai</h4>
                            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full mt-1 inline-block">Global Platform</span>
                        </div>
                    </div>
                    <p class="text-gray-400 mb-8 max-w-md leading-relaxed">
                        The world's premier platform connecting learners with expert coaches, mentors, consultants, trainers, and freelancers worldwide.
                    </p>

                </div>
                
                <div>
                    <h5 class="font-bold mb-6 text-xl flex items-center">
                        <span class="w-2 h-2 bg-[#00D4AA] rounded-full mr-3"></span>
                        Categories
                    </h5>
                    <ul class="space-y-3">
                        <li><a href="?panel=learner&page=browse-experts&category=AI%20%26%20Technology" class="text-gray-400 hover:text-white transition duration-300 flex items-center group">
                            AI & Technology
                        </a></li>
                        <li><a href="?panel=learner&page=browse-experts&category=Leadership" class="text-gray-400 hover:text-white transition duration-300 flex items-center group">
                            Leadership
                        </a></li>
                        <li><a href="?panel=learner&page=browse-experts&category=career%20Growth" class="text-gray-400 hover:text-white transition duration-300 flex items-center group">
                            career Growth
                        </a></li>
                        <li><a href="?panel=learner&page=browse-experts&category=Entrepreneurship" class="text-gray-400 hover:text-white transition duration-300 flex items-center group">
                            Entrepreneurship
                        </a></li>
                        <li><a href="?panel=learner&page=browse-experts&category=Product%26Strategy" class="text-gray-400 hover:text-white transition duration-300 flex items-center group">
                            Product&Strategy
                        </a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="font-bold mb-6 text-xl flex items-center">
                        <span class="w-2 h-2 bg-[#00D4AA] rounded-full mr-3"></span>
                        Support
                    </h5>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Help Center
                        </a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Contact Us
                        </a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Privacy Policy
                        </a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Terms of Service
                        </a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            Refund Policy
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-16 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <p class="text-gray-500">
                            <span class="mr-2">©</span>
                            <span>2025 Nexpert.ai. All rights reserved.</span>
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center text-gray-500 text-sm">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                            <span>All systems operational</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-20px) translateX(10px); }
        }

        @keyframes float-delay {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(20px) translateX(-10px); }
        }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 0.15; }
            50% { opacity: 0.3; }
        }

        @keyframes scroll-testimonials {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-384px * 3 - 32px * 3)); }
        }

        .animate-float {
            animation: float 8s ease-in-out infinite;
        }

        .animate-float-delay {
            animation: float-delay 10s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float-slow 12s ease-in-out infinite;
        }

        .animate-pulse-slow {
            animation: pulse-slow 6s ease-in-out infinite;
        }

        .animate-scroll-testimonials {
            display: flex;
            width: max-content;
            animation: scroll-testimonials 25s linear infinite;
        }

        .animate-scroll-testimonials:hover {
            animation-play-state: paused;
        }
    </style>

    <script>
    // Utility function to resolve image paths
    function resolveImagePath(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '<?php echo BASE_PATH; ?>' + (path.startsWith('/') ? '' : '/') + path;
    }

    // Typing effect for search placeholder
    const placeholderTexts = [
        'e.g. Career Change',
        'e.g. Parenting Advice',
        'e.g. Fitness Coaching',
        'e.g. Business Strategy',
        'e.g. Language Learning',
        'e.g. Personal Finance'
    ];
    
    let currentTextIndex = 0;
    let currentCharIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;
    
    function typeEffect() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput || searchInput === document.activeElement) return;
        
        const currentText = placeholderTexts[currentTextIndex];
        
        if (isDeleting) {
            searchInput.placeholder = currentText.substring(0, currentCharIndex - 1);
            currentCharIndex--;
            typingSpeed = 50;
        } else {
            searchInput.placeholder = currentText.substring(0, currentCharIndex + 1);
            currentCharIndex++;
            typingSpeed = 100;
        }
        
        if (!isDeleting && currentCharIndex === currentText.length) {
            isDeleting = true;
            typingSpeed = 2000;
        } else if (isDeleting && currentCharIndex === 0) {
            isDeleting = false;
            currentTextIndex = (currentTextIndex + 1) % placeholderTexts.length;
            typingSpeed = 500;
        }
        
        setTimeout(typeEffect, typingSpeed);
    }
    
    // Start typing effect when page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(typeEffect, 1000);
    });

    // Handle form submission using AI matching
    document.getElementById('expertSearchForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const query = document.getElementById('searchInput').value.trim();
        if (!query) return;

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#080B10] inline-block" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Matching...
        `;

        try {
            const response = await fetch('<?php echo BASE_PATH; ?>/admin-panel/apis/learner/browse-experts.php?action=smart_search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: query })
            });

            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                // Store results in session storage for the browse page to consume
                sessionStorage.setItem('smart_search_results', JSON.stringify(result.data));
                sessionStorage.setItem('smart_search_query', query);
                window.location.href = '?panel=learner&page=browse-experts&mode=smart';
            } else {
                // Fallback to regular search
                console.error('Smart search failed:', result.error);
                window.location.href = `?panel=learner&page=browse-experts&search=${encodeURIComponent(query)}`;
            }
        } catch (error) {
            console.error('Smart search error:', error);
            window.location.href = `?panel=learner&page=browse-experts&search=${encodeURIComponent(query)}`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    function searchForExpertise(query) {
        document.getElementById('searchInput').value = query;
        // Trigger the form submit which will use AI search
        document.getElementById('expertSearchForm').dispatchEvent(new Event('submit'));
    }

    // Load featured experts from database
    async function loadFeaturedExperts() {
        try {
            const response = await fetch('<?php echo BASE_PATH; ?>/admin-panel/apis/learner/browse-experts.php?sort_by=latest&limit=6');
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                const grid = document.getElementById('featured-experts-grid');
                grid.innerHTML = '';
                
                result.data.slice(0, 6).forEach(expert => {
                    const skills = Array.isArray(expert.skills) ? expert.skills : (expert.skills ? expert.skills.split(',').map(s => s.trim()) : []);
                    
                    const imageSource = expert.profile_photo 
                        ? resolveImagePath(expert.profile_photo)
                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(expert.name)}&background=00D4AA&color=080B10&size=200`;
                    
                    // Parse strengths and outcomes from database (no fallback values)
                    const strengths = expert.strengths ? (typeof expert.strengths === 'string' ? JSON.parse(expert.strengths) : expert.strengths) : [];
                    const outcomes = expert.expected_outcomes ? (typeof expert.expected_outcomes === 'string' ? JSON.parse(expert.expected_outcomes) : expert.expected_outcomes) : [];
                    
                    const expertCard = `
                        <div class="bg-[#131b2e] rounded-2xl shadow-lg overflow-hidden hover:border-[#00D4AA]/30 hover:shadow-2xl transition duration-300 border border-gray-800">
                            <!-- Header Section -->
                            <div class="bg-[#131b2e] p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="relative rounded-2xl overflow-hidden border-2 border-gray-800 shadow-md" style="width: 80px; height: 80px; min-width: 80px;">
                                            <img src="${imageSource}" 
                                                 alt="${escapeHtml(expert.name)}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="font-bold text-xl text-white truncate">${escapeHtml(expert.name)}</h3>
                                                <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <p class="text-gray-300 font-medium text-sm mb-2">${escapeHtml(expert.professional_title || expert.category || 'Expert')}</p>
                                            <div class="flex items-center gap-2">
                                                ${expert.trust_tier ? `
                                                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-gradient-to-r ${expert.trust_tier === 'A' ? 'from-emerald-950/20 to-teal-950/20 border-emerald-900/50' : expert.trust_tier === 'B' ? 'from-blue-950/20 to-indigo-950/20 border-blue-900/50' : 'from-slate-900/20 to-gray-900/20 border-gray-800'} border shadow-sm">
                                                        <span class="flex h-2 w-2 rounded-full ${expert.trust_tier === 'A' ? 'bg-emerald-500 animate-pulse' : expert.trust_tier === 'B' ? 'bg-blue-500' : 'bg-gray-500'}"></span>
                                                        <span class="text-[10px] font-bold tracking-wider ${expert.trust_tier === 'A' ? 'text-emerald-400' : expert.trust_tier === 'B' ? 'text-blue-400' : 'text-gray-400'} uppercase">Tier ${expert.trust_tier}</span>
                                                        <span class="text-[9px] font-medium text-gray-400">| ${Math.round(expert.overall_score || 0)}% Trust</span>
                                                    </div>
                                                ` : `
                                                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-900/50 border border-gray-800 shadow-sm">
                                                        <span class="text-[10px] font-bold text-gray-500 uppercase">Calculating...</span>
                                                    </div>
                                                `}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expert Skills Section -->
                            ${skills.length > 0 ? `
                            <div class="px-5 py-3 bg-[#131b2e] border-b border-gray-800/80">
                                <h4 class="text-xs font-semibold text-gray-400 mb-2">EXPERT IN</h4>
                                <div class="flex flex-wrap gap-2">
                                    ${skills.slice(0, 3).map(skill => `
                                        <span class="px-2.5 py-1 bg-[#0e1322] text-[#00D4AA] text-xs font-medium rounded-lg border border-[#00D4AA]/30">
                                            ${escapeHtml(skill)}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                            ` : ''}

                            <!-- Strengths & Outcomes Grid -->
                            ${strengths.length > 0 || outcomes.length > 0 ? `
                            <div class="grid grid-cols-2 gap-3 px-5 py-4 bg-[#131b2e]">
                                ${strengths.length > 0 ? `
                                <div>
                                    <h4 class="text-sm font-bold text-white mb-2">Strengths</h4>
                                    <ul class="space-y-1.5">
                                        ${strengths.slice(0, 3).map(strength => `
                                            <li class="text-xs text-gray-300 flex items-start">
                                                <span class="mr-1.5 mt-0.5 text-[#00D4AA]">•</span>
                                                <span>${escapeHtml(strength)}</span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                                ` : ''}
                                ${outcomes.length > 0 ? `
                                <div>
                                    <h4 class="text-sm font-bold text-white mb-2">Expected Outcomes</h4>
                                    <ul class="space-y-1.5">
                                        ${outcomes.slice(0, 3).map(outcome => `
                                            <li class="text-xs text-gray-300 flex items-start">
                                                <span class="mr-1.5 mt-0.5 text-[#00D4AA]">•</span>
                                                <span>${escapeHtml(outcome)}</span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                                ` : ''}
                            </div>
                            ` : ''}

                            <!-- Stats Row -->
                            <div class="px-5 py-3 bg-[#0e1322] border-t border-gray-800/80">
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium">${expert.bookings_this_month || Math.floor(Math.random() * 15) + 5} bookings this month</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                        </svg>
                                        <span class="font-medium">${expert.satisfaction_percent || 97}% satisfaction</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer with Price & CTA -->
                            <div class="px-5 py-4 bg-[#131b2e] border-t border-gray-800/80 flex items-center justify-between">
                                <div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-2xl font-bold text-white">₹${escapeHtml(String(expert.hourly_rate || 0))}</span>
                                        <span class="text-sm text-gray-400">/hour</span>
                                    </div>
                                </div>
                                <a href="?panel=learner&page=expert-profile&expert_id=${encodeURIComponent(expert.id)}" 
                                   class="bg-[#00D4AA] text-[#080B10] px-6 py-2.5 rounded-xl hover:bg-[#00bda0] transition duration-200 text-sm font-bold shadow-md hover:shadow-lg">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    `;
                    
                    grid.innerHTML += expertCard;
                });
            } else {
                document.getElementById('featured-experts-grid').innerHTML = '<div class="col-span-full text-center py-8"><p class="text-gray-400">No experts available at the moment.</p></div>';
            }
        } catch (error) {
            console.error('Error loading featured experts:', error);
            document.getElementById('featured-experts-grid').innerHTML = '<div class="col-span-full text-center py-8"><p class="text-gray-400">Error loading experts. Please try again later.</p></div>';
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Load featured experts on page load
    document.addEventListener('DOMContentLoaded', loadFeaturedExperts);
    </script>

</body>
</html>
