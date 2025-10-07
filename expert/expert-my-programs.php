<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/session-config.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . $BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "My Programs - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/navigation.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header with Create Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Programs</h1>
            <p class="text-gray-600">Create and manage structured learning programs for your learners</p>
        </div>
        <button id="create-program-btn" class="mt-4 md:mt-0 bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition flex items-center gap-2 w-full md:w-auto justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create New Program
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-total-programs" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Total Programs</p>
                </div>
                <div class="p-3 bg-accent rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-active-learners" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Active Learners</p>
                </div>
                <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-total-assignments" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Total Assignments</p>
                </div>
                <div class="p-3 bg-purple-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-completion-rate" class="text-2xl font-bold text-gray-900">0%</p>
                    <p class="text-gray-600 text-sm mt-1">Completion Rate</p>
                </div>
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs List -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Your Programs</h2>
        
        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12">
            <div class="inline-block p-4 bg-amber-50 rounded-full mb-4">
                <svg class="w-12 h-12 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Programs Created Yet</h3>
            <p class="text-gray-600 mb-6">Create your first program to start offering structured learning paths to your learners</p>
            <button class="create-program-trigger bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
                Create Your First Program
            </button>
        </div>

        <!-- Programs Grid (Hidden by default, will show when programs exist) -->
        <div id="programs-grid" class="hidden grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Program cards will be inserted here dynamically -->
        </div>
    </div>
</div>

<!-- Create Program Modal -->
<div id="create-program-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Create New Program</h2>
            <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Program Details Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Program Details
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program Title</label>
                        <input type="text" id="program-title" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="e.g., Web Development Mastery">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="program-description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Describe what learners will achieve in this program"></textarea>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (weeks)</label>
                            <input type="number" id="program-duration" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="8">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price (₹)</label>
                            <input type="number" id="program-price" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="15000">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestones & Timeline Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        Milestones & Timeline
                    </h3>
                    <button id="add-milestone-btn" class="text-accent hover:text-yellow-600 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Milestone
                    </button>
                </div>
                <div id="milestones-container" class="space-y-3">
                    <!-- Milestone items will be added here -->
                    <div class="text-center py-8 text-gray-400 text-sm">
                        No milestones added yet. Click "Add Milestone" to create the program timeline.
                    </div>
                </div>
            </div>

            <!-- Assignments Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Assignments
                    </h3>
                    <button id="add-assignment-btn" class="text-accent hover:text-yellow-600 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Assignment
                    </button>
                </div>
                <div id="assignments-container" class="space-y-3">
                    <div class="text-center py-8 text-gray-400 text-sm">
                        No assignments added yet. Click "Add Assignment" to create tasks for learners.
                    </div>
                </div>
            </div>

            <!-- Learning Resources Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Learning Resources
                    </h3>
                    <button id="add-resource-btn" class="text-accent hover:text-yellow-600 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Resource
                    </button>
                </div>
                <div id="resources-container" class="space-y-3">
                    <div class="text-center py-8 text-gray-400 text-sm">
                        No resources added yet. Upload study materials, videos, or documents.
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t">
            <button id="cancel-modal-btn" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </button>
            <button id="save-program-btn" class="px-6 py-3 bg-accent text-white rounded-lg hover:bg-yellow-600 transition">
                Create Program
            </button>
        </div>
    </div>
</div>

<script>
// Modal functionality
const modal = document.getElementById('create-program-modal');
const createProgramBtn = document.getElementById('create-program-btn');
const closeModalBtn = document.getElementById('close-modal-btn');
const cancelModalBtn = document.getElementById('cancel-modal-btn');
const createTriggers = document.querySelectorAll('.create-program-trigger');

// Open modal
createProgramBtn?.addEventListener('click', () => {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
});

createTriggers.forEach(trigger => {
    trigger.addEventListener('click', () => {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    });
});

// Close modal
const closeModal = () => {
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
};

closeModalBtn?.addEventListener('click', closeModal);
cancelModalBtn?.addEventListener('click', closeModal);

// Close on outside click
modal?.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

// Add Milestone
let milestoneCount = 0;
document.getElementById('add-milestone-btn')?.addEventListener('click', () => {
    const container = document.getElementById('milestones-container');
    if (milestoneCount === 0) {
        container.innerHTML = '';
    }
    milestoneCount++;
    
    const milestoneHtml = `
        <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex-1 grid md:grid-cols-3 gap-3">
                <input type="text" placeholder="Milestone title" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="number" placeholder="Week #" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="text" placeholder="Deliverable" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button class="text-red-500 hover:text-red-700 p-2" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', milestoneHtml);
});

// Add Assignment
let assignmentCount = 0;
document.getElementById('add-assignment-btn')?.addEventListener('click', () => {
    const container = document.getElementById('assignments-container');
    if (assignmentCount === 0) {
        container.innerHTML = '';
    }
    assignmentCount++;
    
    const assignmentHtml = `
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid md:grid-cols-2 gap-3 mb-3">
                <input type="text" placeholder="Assignment title" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Select type</option>
                    <option value="project">Project</option>
                    <option value="quiz">Quiz</option>
                    <option value="essay">Essay</option>
                    <option value="presentation">Presentation</option>
                </select>
            </div>
            <div class="flex gap-3">
                <textarea placeholder="Description" rows="2" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                <button class="text-red-500 hover:text-red-700 p-2" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', assignmentHtml);
});

// Add Resource
let resourceCount = 0;
document.getElementById('add-resource-btn')?.addEventListener('click', () => {
    const container = document.getElementById('resources-container');
    if (resourceCount === 0) {
        container.innerHTML = '';
    }
    resourceCount++;
    
    const resourceHtml = `
        <div class="flex gap-3 items-start p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex-1 grid md:grid-cols-3 gap-3">
                <input type="text" placeholder="Resource title" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Type</option>
                    <option value="video">Video</option>
                    <option value="document">Document</option>
                    <option value="link">Link</option>
                    <option value="file">File</option>
                </select>
                <input type="text" placeholder="URL or upload" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <button class="text-red-500 hover:text-red-700 p-2" onclick="this.parentElement.remove()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', resourceHtml);
});


</script>

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

    // Rest of the existing script remains the same
</script>

<script src="<?php echo $BASE_PATH; ?>/admin-panel/js/expert-programs.js"></script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/footer.php'; ?>
