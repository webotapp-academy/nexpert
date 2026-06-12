<?php
// Load domain path configuration
$base_path = require_once 'admin-panel/apis/connection/domain-path.php';

$page_title = "Project Introduction - Nexpert.ai";
$panel_type = "home";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<style>
    .gradient-text {
        background: linear-gradient(135deg, #3B82F6, #8B5CF6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .role-card {
        transition: all 0.3s ease;
    }
    .role-card:hover {
        transform: translateY(-8px);
    }
    .feature-item {
        transition: all 0.2s ease;
    }
    .feature-item:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }
    .tech-badge {
        transition: all 0.2s ease;
    }
    .tech-badge:hover {
        transform: scale(1.05);
    }
    .flow-step {
        position: relative;
    }
    .flow-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -20px;
        width: 40px;
        height: 2px;
        background: linear-gradient(90deg, #3B82F6, #8B5CF6);
    }
    @media (max-width: 768px) {
        .flow-step:not(:last-child)::after {
            display: none;
        }
    }
</style>

<!-- Hero Section -->
<section class="bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700 py-16 relative overflow-hidden">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-400/20 rounded-full blur-3xl animate-pulse"></div>
    </div>
    
    <div class="max-w-6xl mx-auto px-4 relative z-10 text-center">
        <div class="inline-flex items-center bg-white/10 backdrop-blur-md rounded-full px-4 py-2 mb-6">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
            <span class="text-white/90 text-sm font-medium">Project Documentation</span>
        </div>
        
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
            Nexpert.ai - Complete Platform Overview
        </h1>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto mb-8">
            Global Expert Learning Platform jo Learners ko Verified Experts se connect karta hai 
            personalized sessions, programs aur webinars ke liye
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <div class="bg-white/10 backdrop-blur-md rounded-xl px-6 py-3 border border-white/20">
                <p class="text-3xl font-bold text-white">3</p>
                <p class="text-blue-100 text-sm">User Roles</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl px-6 py-3 border border-white/20">
                <p class="text-3xl font-bold text-white">50+</p>
                <p class="text-blue-100 text-sm">Features</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl px-6 py-3 border border-white/20">
                <p class="text-3xl font-bold text-white">15+</p>
                <p class="text-blue-100 text-sm">API Endpoints</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl px-6 py-3 border border-white/20">
                <p class="text-3xl font-bold text-white">5+</p>
                <p class="text-blue-100 text-sm">Integrations</p>
            </div>
        </div>
    </div>
</section>

<!-- Quick Navigation -->
<section class="bg-white border-b sticky top-0 z-40 shadow-sm">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex overflow-x-auto py-4 gap-4 scrollbar-hide">
            <a href="#overview" class="whitespace-nowrap px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition">Overview</a>
            <a href="#learner" class="whitespace-nowrap px-4 py-2 bg-green-50 text-green-600 rounded-lg font-medium hover:bg-green-100 transition">Learner Panel</a>
            <a href="#expert" class="whitespace-nowrap px-4 py-2 bg-purple-50 text-purple-600 rounded-lg font-medium hover:bg-purple-100 transition">Expert Panel</a>
            <a href="#admin" class="whitespace-nowrap px-4 py-2 bg-red-50 text-red-600 rounded-lg font-medium hover:bg-red-100 transition">Admin Panel</a>
            <a href="#ai-agent" class="whitespace-nowrap px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg font-medium hover:bg-indigo-100 transition">🤖 AI Agent</a>
            <a href="#workflows" class="whitespace-nowrap px-4 py-2 bg-orange-50 text-orange-600 rounded-lg font-medium hover:bg-orange-100 transition">Workflows</a>
            <a href="#tech" class="whitespace-nowrap px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium hover:bg-gray-200 transition">Tech Stack</a>
        </div>
    </div>
</section>

<!-- Project Overview -->
<section id="overview" class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🎯 Project Overview</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Nexpert.ai ek comprehensive multi-role learning marketplace hai jo experts aur learners ko connect karta hai
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- What is Nexpert -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Kya Hai Nexpert?</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Ek online marketplace jahan verified experts apni expertise share karte hain 1-on-1 sessions, 
                    structured programs aur live webinars ke through. Learners apne goals ke hisaab se best expert choose kar sakte hain.
                </p>
            </div>
            
            <!-- Problem Solved -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Problem Solve</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Traditional learning mein personalized guidance nahi milti. Nexpert verified experts se direct 
                    connection provide karta hai with secure payments, video sessions aur progress tracking.
                </p>
            </div>
            
            <!-- Key Value -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Key Value</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    AI-powered expert matching, Razorpay secure payments, Zoom video integration, 
                    KYC verified experts aur complete admin oversight with analytics.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- LEARNER PANEL -->
<section id="learner" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900">👨‍🎓 Learner Panel</h2>
                <p class="text-gray-600">Students/Learners ke liye complete learning experience</p>
            </div>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Authentication -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Authentication</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Email/Password Login
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Google OAuth Login
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Session Management
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Password Reset
                    </li>
                </ul>
            </div>
            
            <!-- Dashboard -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Dashboard</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Total Sessions Overview
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Learning Progress %
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Upcoming Sessions
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Quick Actions
                    </li>
                </ul>
            </div>
            
            <!-- Expert Discovery -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Expert Discovery</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        AI Smart Search
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Category Filters
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Expert Profiles View
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Reviews & Ratings
                    </li>
                </ul>
            </div>
            
            <!-- Session Booking -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Session Booking</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        View Expert Availability
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Select Date & Time Slot
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Razorpay Payment
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Zoom Link Generation
                    </li>
                </ul>
            </div>
            
            <!-- Session Management -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Session Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        View All Sessions
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Cancel/Reschedule
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Join Zoom Meeting
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Session History
                    </li>
                </ul>
            </div>
            
            <!-- Programs & Courses -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Programs & Courses</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Browse Programs
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Enroll & Pay
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Track Milestones
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Submit Assignments
                    </li>
                </ul>
            </div>
            
            <!-- Messaging -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Messaging</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Direct Chat with Expert
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Message History
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Notifications
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Session Reminders
                    </li>
                </ul>
            </div>
            
            <!-- Profile -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Profile Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Personal Info Edit
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Learning Goals
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Photo Upload
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Preferences
                    </li>
                </ul>
            </div>
            
            <!-- Payments -->
            <div class="role-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Payments</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Payment History
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Transaction Details
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Refund Requests
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                        Invoices
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>


<!-- EXPERT PANEL -->
<section id="expert" class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900">👨‍🏫 Expert Panel</h2>
                <p class="text-gray-600">Coaches, Mentors, Consultants, Trainers & Freelancers ke liye</p>
            </div>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Onboarding -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Onboarding</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Registration & Login
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Profile Setup Wizard
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        KYC Document Upload
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Verification Status
                    </li>
                </ul>
            </div>
            
            <!-- Dashboard -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Dashboard Analytics</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Total Earnings
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Active Learners Count
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Sessions This Month
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Achievement Milestones
                    </li>
                </ul>
            </div>
            
            <!-- Profile Setup -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Profile Setup</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Bio & Tagline
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Expertise Verticals
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Credentials & Experience
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        LinkedIn/Website Links
                    </li>
                </ul>
            </div>
            
            <!-- Availability -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Availability Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Weekly Time Slots
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Pricing Tiers Setup
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Booked Slots View
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Conflict Detection
                    </li>
                </ul>
            </div>
            
            <!-- Booking Management -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Booking Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        View All Bookings
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Accept/Decline Requests
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Handle Reschedules
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Earnings Per Session
                    </li>
                </ul>
            </div>
            
            <!-- Session Execution -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Session Execution</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Join as Zoom Host
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Session Notes
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Follow-up Actions
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Mark Completed
                    </li>
                </ul>
            </div>
            
            <!-- Programs Creation -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Programs Creation</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Create Structured Programs
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Add Milestones
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        AI Program Generator
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Track Learner Progress
                    </li>
                </ul>
            </div>
            
            <!-- Webinars -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Webinars</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Create Live Webinars
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Set Registration Limits
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        AI Webinar Generator
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Track Attendance
                    </li>
                </ul>
            </div>
            
            <!-- Earnings & Payouts -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Earnings & Payouts</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Total Earnings View
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Monthly Breakdown
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Request Payout
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Payout History
                    </li>
                </ul>
            </div>
            
            <!-- KYC Verification -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">KYC Verification</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        ID Document Upload
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Professional Info
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Bank Details
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Verification Status
                    </li>
                </ul>
            </div>
            
            <!-- Learner Management -->
            <div class="role-card bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Learner Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        View All Learners
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Booking History
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Engagement Tracking
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Send Follow-ups
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>


<!-- ADMIN PANEL -->
<section id="admin" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900">👨‍💼 Admin Panel</h2>
                <p class="text-gray-600">Platform Management & Complete Oversight</p>
            </div>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Dashboard -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Dashboard Analytics</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Total Users & Experts
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Total Revenue
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Monthly Revenue
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Pending Verifications
                    </li>
                </ul>
            </div>
            
            <!-- User Management -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">User Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        View All Users
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        User Status Tracking
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Activate/Deactivate
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Activity Logs
                    </li>
                </ul>
            </div>
            
            <!-- Expert Management -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Expert Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        View All Experts
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Filter by Status
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Approve/Reject
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Review Credentials
                    </li>
                </ul>
            </div>
            
            <!-- KYC Verification -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">KYC Verification</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Review Documents
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Verify Information
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Approve/Reject KYC
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Add Review Notes
                    </li>
                </ul>
            </div>
            
            <!-- Booking Management -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Booking Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        View All Bookings
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Filter by Status
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Booking Details
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Monitor Sessions
                    </li>
                </ul>
            </div>
            
            <!-- Payments -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Payments & Revenue</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        All Transactions
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Revenue Analytics
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Commission Tracking
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Refund Management
                    </li>
                </ul>
            </div>
            
            <!-- Payouts -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Payouts Management</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Pending Requests
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Approve/Process
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Payout History
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Expert Earnings
                    </li>
                </ul>
            </div>
            
            <!-- Settings -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Platform Settings</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Commission Rates
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Platform Policies
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Email Templates
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        System Config
                    </li>
                </ul>
            </div>
            
            <!-- Cron Jobs -->
            <div class="role-card bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Cron Dashboard</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Scheduled Tasks
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Background Jobs
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Email Reminders
                    </li>
                    <li class="feature-item flex items-center gap-2 p-2 rounded-lg">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        Session Cleanup
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>


<!-- WORKFLOWS SECTION -->
<section id="workflows" class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🔄 Key Workflows</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Platform ke main processes aur user journeys</p>
        </div>
        
        <!-- Session Booking Flow -->
        <div class="bg-white rounded-2xl p-8 shadow-lg mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </span>
                Session Booking Flow
            </h3>
            <div class="grid md:grid-cols-5 gap-4">
                <div class="flow-step text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">1</div>
                    <p class="text-sm font-medium text-gray-800">Browse Experts</p>
                    <p class="text-xs text-gray-500 mt-1">Search & Filter</p>
                </div>
                <div class="flow-step text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">2</div>
                    <p class="text-sm font-medium text-gray-800">Select Slot</p>
                    <p class="text-xs text-gray-500 mt-1">Date & Time</p>
                </div>
                <div class="flow-step text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">3</div>
                    <p class="text-sm font-medium text-gray-800">Pay via Razorpay</p>
                    <p class="text-xs text-gray-500 mt-1">Secure Payment</p>
                </div>
                <div class="flow-step text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">4</div>
                    <p class="text-sm font-medium text-gray-800">Zoom Link</p>
                    <p class="text-xs text-gray-500 mt-1">Auto Generated</p>
                </div>
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">5</div>
                    <p class="text-sm font-medium text-gray-800">Join Session</p>
                    <p class="text-xs text-gray-500 mt-1">Video Call</p>
                </div>
            </div>
        </div>

        <!-- Expert Onboarding Flow -->
        <div class="bg-white rounded-2xl p-8 shadow-lg mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </span>
                Expert Onboarding Flow
            </h3>
            <div class="grid md:grid-cols-5 gap-4">
                <div class="flow-step text-center p-4 bg-purple-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">1</div>
                    <p class="text-sm font-medium text-gray-800">Register</p>
                    <p class="text-xs text-gray-500 mt-1">Create Account</p>
                </div>
                <div class="flow-step text-center p-4 bg-purple-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">2</div>
                    <p class="text-sm font-medium text-gray-800">Profile Setup</p>
                    <p class="text-xs text-gray-500 mt-1">Bio & Expertise</p>
                </div>
                <div class="flow-step text-center p-4 bg-purple-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">3</div>
                    <p class="text-sm font-medium text-gray-800">KYC Submit</p>
                    <p class="text-xs text-gray-500 mt-1">Documents</p>
                </div>
                <div class="flow-step text-center p-4 bg-purple-50 rounded-xl">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">4</div>
                    <p class="text-sm font-medium text-gray-800">Admin Verify</p>
                    <p class="text-xs text-gray-500 mt-1">Approval</p>
                </div>
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">5</div>
                    <p class="text-sm font-medium text-gray-800">Go Live</p>
                    <p class="text-xs text-gray-500 mt-1">Start Earning</p>
                </div>
            </div>
        </div>
        
        <!-- Payment Flow -->
        <div class="bg-white rounded-2xl p-8 shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                Payment & Payout Flow
            </h3>
            <div class="grid md:grid-cols-5 gap-4">
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">1</div>
                    <p class="text-sm font-medium text-gray-800">Learner Pays</p>
                    <p class="text-xs text-gray-500 mt-1">Razorpay</p>
                </div>
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">2</div>
                    <p class="text-sm font-medium text-gray-800">Payment Verified</p>
                    <p class="text-xs text-gray-500 mt-1">Confirmation</p>
                </div>
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">3</div>
                    <p class="text-sm font-medium text-gray-800">Expert Earnings</p>
                    <p class="text-xs text-gray-500 mt-1">Accumulated</p>
                </div>
                <div class="flow-step text-center p-4 bg-green-50 rounded-xl">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">4</div>
                    <p class="text-sm font-medium text-gray-800">Payout Request</p>
                    <p class="text-xs text-gray-500 mt-1">Expert Submits</p>
                </div>
                <div class="flow-step text-center p-4 bg-blue-50 rounded-xl">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-white font-bold">5</div>
                    <p class="text-sm font-medium text-gray-800">Admin Approves</p>
                    <p class="text-xs text-gray-500 mt-1">Bank Transfer</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- AI AGENT FEATURES -->
<section id="ai-agent" class="py-16 bg-gradient-to-br from-indigo-50 to-purple-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🤖 AI Agent Features</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                OpenAI GPT-powered intelligent features jo platform ko smart banate hain
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Smart Search -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-indigo-100 hover:shadow-xl transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Smart Search</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    AI-powered expert search jo natural language queries ko samajhta hai aur best matching experts dhundhta hai.
                </p>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-2">Example Queries:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• "I want to learn React"</li>
                        <li>• "Digital marketing expert"</li>
                        <li>• "Startup mentor for my business"</li>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs">GPT-3.5</span>
                    <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">Learner Panel</span>
                </div>
            </div>
            
            <!-- Generate Program AI -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-indigo-100 hover:shadow-xl transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">AI Program Generator</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    Expert sirf idea deta hai, AI complete program structure generate karta hai with milestones, assignments aur resources.
                </p>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-2">AI Generates:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• Program Title & Description</li>
                        <li>• Learning Milestones</li>
                        <li>• Assignments & Resources</li>
                        <li>• Duration & Pricing Suggestion</li>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-purple-100 text-purple-600 rounded text-xs">OpenAI</span>
                    <span class="px-2 py-1 bg-orange-100 text-orange-600 rounded text-xs">Expert Panel</span>
                </div>
            </div>
            
            <!-- Generate Webinar AI -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-indigo-100 hover:shadow-xl transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">AI Webinar Generator</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    Webinar idea se complete webinar outline generate karta hai with title, description, duration aur pricing.
                </p>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-2">AI Generates:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• Engaging Title</li>
                        <li>• Comprehensive Description</li>
                        <li>• Suggested Date & Time</li>
                        <li>• Price & Max Participants</li>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-red-100 text-red-600 rounded text-xs">GPT-3.5</span>
                    <span class="px-2 py-1 bg-orange-100 text-orange-600 rounded text-xs">Expert Panel</span>
                </div>
            </div>
            
            <!-- Session Insights -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-indigo-100 hover:shadow-xl transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">Session Insights</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    Booking ke time learner ko AI-generated insights milte hain about expert - kya expect karein, kaise prepare karein.
                </p>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-2">AI Provides:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• Expert Overview</li>
                        <li>• Session Goals</li>
                        <li>• Recommended Approach</li>
                        <li>• Preparation Tips</li>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded text-xs">GPT-4o-mini</span>
                    <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-xs">Learner Panel</span>
                </div>
            </div>
            
            <!-- Follow-up Emails -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-indigo-100 hover:shadow-xl transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg">AI Follow-up Emails</h3>
                </div>
                <p class="text-gray-600 text-sm mb-4">
                    4-5 star reviews ke 7 din baad AI personalized follow-up email generate karta hai to encourage rebooking.
                </p>
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-gray-500 mb-2">Features:</p>
                    <ul class="text-xs text-gray-700 space-y-1">
                        <li>• Personalized Content</li>
                        <li>• References Past Session</li>
                        <li>• Encourages Next Booking</li>
                        <li>• Automated via Cron Job</li>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-600 rounded text-xs">GPT-4</span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Cron Job</span>
                </div>
            </div>
            
            <!-- AI Architecture -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 shadow-lg text-white">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg">AI Architecture</h3>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        <span>OpenAI GPT-3.5 Turbo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        <span>GPT-4o-mini for Insights</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        <span>GPT-4 for Email Generation</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        <span>Caching for Performance</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-white rounded-full"></span>
                        <span>Fallback Templates</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TECH STACK -->
<section id="tech" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🛠️ Technology Stack</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Platform mein use hone wali technologies aur integrations</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Backend -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                    </span>
                    Backend
                </h3>
                <div class="space-y-2">
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-600 shadow-sm">PHP 7.4+</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-600 shadow-sm">MySQL</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-600 shadow-sm">PDO</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-blue-600 shadow-sm">REST APIs</span>
                </div>
            </div>
            
            <!-- Frontend -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Frontend
                </h3>
                <div class="space-y-2">
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-green-600 shadow-sm">HTML5</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-green-600 shadow-sm">CSS3</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-green-600 shadow-sm">JavaScript</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-green-600 shadow-sm">Tailwind CSS</span>
                </div>
            </div>
            
            <!-- Integrations -->
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                        </svg>
                    </span>
                    Integrations
                </h3>
                <div class="space-y-2">
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-purple-600 shadow-sm">Razorpay</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-purple-600 shadow-sm">Zoom API</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-purple-600 shadow-sm">OpenAI</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-purple-600 shadow-sm">PHPMailer</span>
                </div>
            </div>
            
            <!-- Security -->
            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl p-6 border border-red-100">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    Security
                </h3>
                <div class="space-y-2">
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-red-600 shadow-sm">bcrypt</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-red-600 shadow-sm">CSRF Token</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-red-600 shadow-sm">RBAC</span>
                    <span class="tech-badge inline-block px-3 py-1 bg-white rounded-full text-sm font-medium text-red-600 shadow-sm">HTTPOnly</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- DATABASE STRUCTURE -->
<section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🗄️ Database Structure</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Main database tables aur unka purpose</p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">users</span>
                </div>
                <p class="text-xs text-gray-500">All user accounts (learners, experts, admins)</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">learner_profiles</span>
                </div>
                <p class="text-xs text-gray-500">Learner profile information & preferences</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">expert_profiles</span>
                </div>
                <p class="text-xs text-gray-500">Expert credentials, bio & verification status</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">bookings</span>
                </div>
                <p class="text-xs text-gray-500">Session bookings with status & Zoom links</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">payments</span>
                </div>
                <p class="text-xs text-gray-500">Payment transactions & Razorpay details</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">workflows</span>
                </div>
                <p class="text-xs text-gray-500">Programs/courses created by experts</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">workflow_steps</span>
                </div>
                <p class="text-xs text-gray-500">Program milestones & assignments</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">expert_availability</span>
                </div>
                <p class="text-xs text-gray-500">Expert weekly time slots</p>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </span>
                    <span class="font-bold text-gray-900">messages</span>
                </div>
                <p class="text-xs text-gray-500">Direct messaging between users</p>
            </div>
        </div>
    </div>
</section>


<!-- FEATURE COMPARISON TABLE -->
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">📊 Feature Comparison</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Har role ke features ka comparison</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full bg-white rounded-2xl shadow-lg overflow-hidden">
                <thead class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Feature</th>
                        <th class="px-6 py-4 text-center font-semibold">👨‍🎓 Learner</th>
                        <th class="px-6 py-4 text-center font-semibold">👨‍🏫 Expert</th>
                        <th class="px-6 py-4 text-center font-semibold">👨‍💼 Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Session Booking</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span> Book</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span> Manage</td>
                        <td class="px-6 py-4 text-center"><span class="text-blue-500 text-xl">👁</span> View</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Program Creation</td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Webinar Management</td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Earnings Tracking</td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">KYC Verification</td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span> Submit</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span> Approve</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">User Management</td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Payment Processing</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-blue-500 text-xl">👁</span> View</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Messaging</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Reviews & Ratings</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-red-400 text-xl">✗</span></td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">Dashboard Analytics</td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                        <td class="px-6 py-4 text-center"><span class="text-green-500 text-xl">✓</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>


<!-- API ENDPOINTS -->
<section class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">🔌 API Endpoints</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Backend APIs ka overview</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Learner APIs -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="font-bold text-green-600 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    Learner APIs
                </h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /learner/auth
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /learner/profile
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /learner/booking
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /learner/browse-experts
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /learner/payment
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /learner/messages
                    </li>
                </ul>
            </div>
            
            <!-- Expert APIs -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="font-bold text-purple-600 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Expert APIs
                </h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /expert/auth
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-xs font-mono">PUT</span>
                        /expert/profile
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /expert/availability
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /expert/bookings
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /expert/programs
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /expert/earnings
                    </li>
                </ul>
            </div>
            
            <!-- Admin APIs -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="font-bold text-red-600 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </span>
                    Admin APIs
                </h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs font-mono">POST</span>
                        /admin/auth
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /admin/dashboard
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /admin/users
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-xs font-mono">PUT</span>
                        /admin/kyc
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs font-mono">GET</span>
                        /admin/payments
                    </li>
                    <li class="flex items-center gap-2 text-gray-600">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-xs font-mono">PUT</span>
                        /admin/payouts
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-br from-blue-600 via-blue-700 to-purple-700">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to Explore?</h2>
        <p class="text-blue-100 mb-8 text-lg">
            Nexpert.ai platform ke different panels explore karein
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="?panel=learner&page=auth" class="px-8 py-3 bg-white text-blue-600 rounded-xl font-semibold hover:bg-blue-50 transition shadow-lg">
                👨‍🎓 Learner Panel
            </a>
            <a href="?panel=expert&page=auth" class="px-8 py-3 bg-purple-500 text-white rounded-xl font-semibold hover:bg-purple-600 transition shadow-lg">
                👨‍🏫 Expert Panel
            </a>
            <a href="?panel=admin&page=auth" class="px-8 py-3 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600 transition shadow-lg">
                👨‍💼 Admin Panel
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
