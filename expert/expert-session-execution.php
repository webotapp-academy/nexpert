<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Session Execution - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
<div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Session Header Card -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6 sm:p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-white mb-2">Live Session Execution</h1>
                    <div class="flex flex-wrap gap-4 text-xs text-gray-400 font-mono">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Learner: Aarav Patel</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Today, 2:00 PM - 3:00 PM IST</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Duration: 60 minutes</span>
                        </div>
                    </div>
                </div>
                
                <!-- Session Status and Timer -->
                <div class="flex md:flex-col items-center md:items-end justify-between gap-2">
                    <span class="px-3 py-1 bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs font-mono font-bold rounded-full">
                        ● Session Active
                    </span>
                    <div class="text-right">
                        <div class="text-3xl font-mono font-extrabold text-[#00D4AA]">24:35</div>
                        <div class="text-[11px] text-gray-400 font-mono">Remaining</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3 mt-6 pt-6 border-t border-gray-800">
                <button class="bg-[#00D4AA] text-[#080B10] px-5 py-2.5 rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs flex items-center shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Join Video Call
                </button>
                <button class="bg-[#080B10] border border-gray-700 text-gray-300 px-5 py-2.5 rounded-xl hover:text-white hover:border-gray-500 transition text-xs font-bold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share Screen
                </button>
                <button class="bg-[#080B10] border border-gray-700 text-gray-300 px-5 py-2.5 rounded-xl hover:text-white hover:border-gray-500 transition text-xs font-bold flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                    Start Recording
                </button>
                <button class="bg-red-500/15 border border-red-500/30 text-red-400 px-5 py-2.5 rounded-xl hover:bg-red-500/25 transition text-xs font-extrabold flex items-center ml-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    End Session
                </button>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Session Notes -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6 sm:p-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-base font-extrabold text-white">Live Advisory Notes</h2>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 text-xs font-mono">Auto-save: On</span>
                            <button class="bg-[#00D4AA] text-[#080B10] px-3.5 py-1 rounded-xl text-xs font-extrabold hover:bg-[#00bfa0] transition shadow-md">Save</button>
                        </div>
                    </div>
                    
                    <!-- Text Editor Toolbar -->
                    <div class="border border-gray-700 rounded-t-2xl p-2.5 bg-[#080B10] flex items-center space-x-2 text-gray-300">
                        <button class="p-1 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 110 8H6z"></path>
                            </svg>
                        </button>
                        <div class="border-l border-gray-700 h-4 mx-1"></div>
                        <button class="px-2 py-0.5 hover:bg-gray-800 rounded font-bold text-xs">B</button>
                        <button class="px-2 py-0.5 hover:bg-gray-800 rounded italic text-xs">I</button>
                        <button class="px-2 py-0.5 hover:bg-gray-800 rounded underline text-xs">U</button>
                        <div class="border-l border-gray-700 h-4 mx-1"></div>
                        <button class="p-1 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <textarea rows="12" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 border-t-0 rounded-b-2xl focus:outline-none focus:border-[#00D4AA] text-white text-xs font-mono leading-relaxed resize-none" placeholder="Document session notes, key discussions, learner progress, and action items...">
Session Date: September 28, 2025
Learner: Aarav Patel
Topic: UX Design Portfolio Review

Key Discussion Points:
• Reviewed current portfolio structure and visual hierarchy
• Discussed case study presentation best practices
• Identified areas for improvement in user research documentation

Learner Strengths:
• Strong visual design skills
• Good understanding of design principles
• Enthusiastic and receptive to feedback

Areas for Improvement:
• Need to strengthen UX research methodology
• Case studies lack clear problem statements
• Portfolio needs better storytelling structure

Action Items for Learner:
1. Revise portfolio case studies with clear problem/solution framework
2. Add user research documentation for 2 main projects
3. Create a design process timeline for each case study
4. Schedule follow-up session in 2 weeks

Resources Shared:
• UX Portfolio Guidelines document
• Case study template
• User research methodology cheat sheet

Next Session Focus:
• Review updated portfolio case studies
• Deep dive into user research techniques
• Prepare for upcoming job interviews
                    </textarea>
                </div>

                <!-- Resource Upload Section -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6 sm:p-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-base font-extrabold text-white">Share Resources</h2>
                        <button class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-xl hover:bg-[#00bfa0] transition text-xs font-extrabold shadow-md">
                            Upload File
                        </button>
                    </div>
                    
                    <!-- Drag and Drop Area -->
                    <div class="border-2 border-dashed border-gray-700 rounded-2xl p-8 text-center mb-6 hover:border-[#00D4AA] transition bg-[#080B10]">
                        <svg class="mx-auto h-10 w-10 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="mt-3">
                            <p class="text-xs font-bold text-gray-300">Drop files here or click to browse</p>
                            <p class="text-[11px] text-gray-500 mt-1">PDFs, Documents, Images up to 50MB</p>
                        </div>
                    </div>
                    
                    <!-- Uploaded Files -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3.5 bg-[#080B10] border border-gray-800 rounded-2xl">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-red-400 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-bold text-white text-xs">UX Portfolio Guidelines.pdf</p>
                                    <p class="text-[11px] text-gray-400 font-mono">2.4 MB • Uploaded just now</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-[#00D4AA] hover:underline text-xs font-bold">Share</button>
                                <button class="text-red-400 hover:text-red-300 text-xs font-bold">Remove</button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3.5 bg-[#080B10] border border-gray-800 rounded-2xl">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-emerald-400 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-bold text-white text-xs">Case Study Template.docx</p>
                                    <p class="text-[11px] text-gray-400 font-mono">856 KB • Uploaded just now</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-[#00D4AA] hover:underline text-xs font-bold">Share</button>
                                <button class="text-red-400 hover:text-red-300 text-xs font-bold">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Creation -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6 sm:p-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-base font-extrabold text-white">Create Assignment</h2>
                        <button class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-xl hover:bg-[#00bfa0] transition text-xs font-extrabold shadow-md">
                            Send Assignment
                        </button>
                    </div>
                    
                    <form class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Assignment Title</label>
                            <input type="text" placeholder="e.g., Portfolio Case Study Revision" class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Due Date</label>
                            <input type="date" class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Instructions</label>
                            <textarea rows="4" placeholder="Provide detailed instructions for the assignment..." class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">Based on our session discussion, please revise your portfolio with the following:
1. Update your main case study with a clear problem statement
2. Add user research documentation for at least 2 projects
3. Create a compelling narrative that shows your design thinking process
4. Include mockups and prototypes with brief explanations</textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Estimated Time</label>
                                <select class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
                                    <option>2-3 hours</option>
                                    <option>4-5 hours</option>
                                    <option>6-8 hours</option>
                                    <option>1-2 days</option>
                                    <option>1 week</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Difficulty Level</label>
                                <select class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] text-xs">
                                    <option>Beginner</option>
                                    <option>Intermediate</option>
                                    <option>Advanced</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Learner Profile -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-[#00D4AA]/20 text-[#00D4AA] flex items-center justify-center font-bold text-xl mx-auto mb-3 border border-[#00D4AA]/30">
                            A
                        </div>
                        <h3 class="font-bold text-white text-sm">Aarav Patel</h3>
                        <p class="text-gray-400 text-xs">UX Designer</p>
                        <p class="text-gray-500 text-[11px] font-mono mt-0.5">Member since Aug 2025</p>
                    </div>
                    
                    <div class="space-y-2.5 text-xs border-t border-gray-800 pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Sessions completed:</span>
                            <span class="font-bold text-white font-mono">3</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Current level:</span>
                            <span class="font-bold text-white">Beginner</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Primary Goal:</span>
                            <span class="font-bold text-white">Career transition</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Timezone:</span>
                            <span class="font-bold text-white font-mono">IST (UTC+5:30)</span>
                        </div>
                    </div>
                    
                    <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=learner-management" class="w-full mt-4 bg-[#080B10] border border-gray-700 text-gray-300 py-2.5 rounded-xl hover:text-white hover:border-gray-500 transition text-xs font-bold text-center block">
                        View Full Roster
                    </a>
                </div>

                <!-- Session Agenda -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Session Agenda</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mr-3 cursor-pointer">
                            <span class="text-xs text-gray-500 line-through">Introduction & Goals (5 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mr-3 cursor-pointer">
                            <span class="text-xs text-gray-500 line-through">Portfolio Review (20 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mr-3 cursor-pointer">
                            <span class="text-xs text-gray-500 line-through">Case Study Deep Dive (20 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mr-3 cursor-pointer">
                            <span class="text-xs text-gray-300 font-medium">Next Steps & Deliverables (10 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mr-3 cursor-pointer">
                            <span class="text-xs text-gray-300 font-medium">Q&A & Wrap-up (5 min)</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (No Emojis) -->
                <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Quick Tools</h3>
                    <div class="space-y-2">
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=messages&learner_id=<?php echo urlencode($booking['learner_id']); ?>" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-gray-300 hover:text-white bg-[#080B10] hover:bg-[#131B2E] border border-gray-800 rounded-xl transition flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Direct Message
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=booking-management" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-gray-300 hover:text-white bg-[#080B10] hover:bg-[#131B2E] border border-gray-800 rounded-xl transition flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule Follow-up
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=expert&page=trust-insights" class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-gray-300 hover:text-white bg-[#080B10] hover:bg-[#131B2E] border border-gray-800 rounded-xl transition flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Trust Telemetry Report
                        </a>
                    </div>
                </div>

                <!-- Technical Telemetry -->
                <div class="bg-[#080B10] border border-gray-800 rounded-3xl p-5">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Live Telemetry</h4>
                    <div class="space-y-2 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Connection:</span>
                            <span class="text-emerald-400 font-bold">Stable</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Recording:</span>
                            <span class="text-gray-400">Idle</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Session ID:</span>
                            <span class="text-[#00D4AA]">SES-001-2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        // If it's a full URL or a data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        
        // If no image path, use a default
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        
        // Remove leading slashes
        const normalizedPath = imagePath.replace(/^\/+/, '');
        
        // Construct full path
        return `${window.BASE_PATH}/${normalizedPath}`;
    }

    // Update placeholder images
    document.querySelectorAll('img[src^="https://via.placeholder.com"]').forEach(img => {
        const originalSrc = img.getAttribute('src');
        img.setAttribute('src', resolveImagePath(originalSrc));
    });
</script>
