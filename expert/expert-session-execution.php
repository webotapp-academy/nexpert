<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "Session Execution - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Learner: Aarav Patel
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Today, 2:00 PM - 3:00 PM IST
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Duration: 60 minutes
                        </div>
                    </div>
                </div>
                
                <!-- Session Status and Timer -->
                <div class="text-right">
                    <div class="mb-4">
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Session Active</span>
                    </div>
                    <div class="text-2xl font-mono font-bold text-primary mb-2">24:35</div>
                    <div class="text-sm text-gray-600">Remaining</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex space-x-4 mt-6 pt-6 border-t border-gray-200">
                <button class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Join Video Call
                </button>
                <button class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share Screen
                </button>
                <button class="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                    Start Recording
                </button>
                <button class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Session Notes</h2>
                        <div class="flex space-x-2">
                            <button class="text-gray-600 hover:text-gray-800 text-sm">Auto-save: On</button>
                            <button class="bg-accent text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">Save</button>
                        </div>
                    </div>
                    
                    <!-- Rich Text Editor Toolbar -->
                    <div class="border border-gray-300 rounded-t-lg p-2 bg-gray-50 flex space-x-2">
                        <button class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 110 8H6z"></path>
                            </svg>
                        </button>
                        <button class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                            </svg>
                        </button>
                        <div class="border-l border-gray-300 mx-2"></div>
                        <button class="p-1 hover:bg-gray-200 rounded font-bold text-sm">B</button>
                        <button class="p-1 hover:bg-gray-200 rounded italic text-sm">I</button>
                        <button class="p-1 hover:bg-gray-200 rounded underline text-sm">U</button>
                        <div class="border-l border-gray-300 mx-2"></div>
                        <button class="p-1 hover:bg-gray-200 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <textarea rows="12" class="w-full px-4 py-3 border border-gray-300 border-t-0 rounded-b-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent resize-none" placeholder="Document your session notes, key discussions, learner progress, and action items...">
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
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Share Resources</h2>
                        <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition text-sm">
                            Upload File
                        </button>
                    </div>
                    
                    <!-- Drag and Drop Area -->
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-6 hover:border-accent transition">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="mt-4">
                            <p class="text-lg text-gray-600">Drop files here or click to browse</p>
                            <p class="text-sm text-gray-500 mt-1">PDFs, Documents, Images up to 50MB</p>
                        </div>
                    </div>
                    
                    <!-- Uploaded Files -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">UX Portfolio Guidelines.pdf</p>
                                    <p class="text-sm text-gray-600">2.4 MB • Uploaded just now</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-primary hover:text-secondary text-sm">Share</button>
                                <button class="text-red-600 hover:text-red-700 text-sm">Remove</button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Case Study Template.docx</p>
                                    <p class="text-sm text-gray-600">856 KB • Uploaded just now</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-primary hover:text-secondary text-sm">Share</button>
                                <button class="text-red-600 hover:text-red-700 text-sm">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Creation -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Create Assignment</h2>
                        <button class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                            Send Assignment
                        </button>
                    </div>
                    
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Title</label>
                            <input type="text" placeholder="e.g., Portfolio Case Study Revision" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                            <textarea rows="4" placeholder="Provide detailed instructions for the assignment..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
Based on our session discussion, please revise your portfolio with the following:

1. Update your main case study to include:
   - Clear problem statement (what user problem you're solving)
   - Research methodology and findings
   - Design process timeline
   - Final solution with key metrics/results

2. Add user research documentation for at least 2 projects

3. Create a compelling narrative that shows your design thinking process

4. Include mockups and prototypes with brief explanations

Please share your updated portfolio link before our next session.
                            </textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Time</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
                                    <option>2-3 hours</option>
                                    <option>4-5 hours</option>
                                    <option>6-8 hours</option>
                                    <option>1-2 days</option>
                                    <option>1 week</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty Level</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
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
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center mb-4">
                        <img src="https://via.placeholder.com/80x80" alt="Aarav Patel" class="w-20 h-20 rounded-full mx-auto mb-3">
                        <h3 class="font-semibold text-gray-900">Aarav Patel</h3>
                        <p class="text-gray-600 text-sm">UX Designer</p>
                        <p class="text-gray-500 text-xs">Member since Aug 2025</p>
                    </div>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sessions with you:</span>
                            <span class="font-medium">3</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Current level:</span>
                            <span class="font-medium">Beginner</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Goals:</span>
                            <span class="font-medium">Career transition</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Timezone:</span>
                            <span class="font-medium">IST</span>
                        </div>
                    </div>
                    
                    <button class="w-full mt-4 bg-primary text-white py-2 rounded-lg hover:bg-secondary transition text-sm">
                        View Full Profile
                    </button>
                </div>

                <!-- Session Agenda -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Agenda</h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700 line-through">Introduction & Goals (5 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700 line-through">Portfolio Review (20 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" checked class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700 line-through">Case Study Deep Dive (20 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">Next Steps & Assignment (10 min)</span>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mr-3">
                            <span class="text-sm text-gray-700">Q&A & Wrap-up (5 min)</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📝 Send Quick Message
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📅 Schedule Follow-up
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            📊 View Progress Report
                        </button>
                        <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            ⭐ Rate This Session
                        </button>
                    </div>
                </div>

                <!-- Technical Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Session Info</h4>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Connection:</span>
                            <span class="text-green-600">Stable</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Recording:</span>
                            <span class="text-red-600">Not Started</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Screen Share:</span>
                            <span class="text-gray-500">Inactive</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Session ID:</span>
                            <span class="font-mono">SES-001-2025</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/footer.php'; ?>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

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
