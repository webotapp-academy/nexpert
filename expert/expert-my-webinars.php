<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "My Webinars - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header with Create Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Webinars</h1>
            <p class="text-gray-600">Create and manage live webinar sessions for your learners</p>
        </div>
        <div class="flex gap-3 mt-4 md:mt-0">
            <button id="create-webinar-btn" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Webinar
            </button>
            <button id="create-webinar-ai-btn" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Create with AI
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-total-webinars" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Total Webinars</p>
                </div>
                <div class="p-3 bg-accent rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-upcoming" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Upcoming</p>
                </div>
                <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-registered" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Total Registered</p>
                </div>
                <div class="p-3 bg-purple-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p id="stats-completed" class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Completed</p>
                </div>
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="search-webinars" placeholder="Search webinars..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <option value="all">All Status</option>
                <option value="upcoming">Upcoming</option>
                <option value="live">Live Now</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select id="sort-webinars" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <option value="date_desc">Newest First</option>
                <option value="date_asc">Oldest First</option>
                <option value="registrations">Most Registered</option>
                <option value="title">Title A-Z</option>
            </select>
        </div>
    </div>

    <!-- Webinars Grid -->
    <div id="webinars-container" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Webinars will be loaded here dynamically -->
        <div class="col-span-full text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-accent"></div>
            <p class="text-gray-600 mt-4">Loading webinars...</p>
        </div>
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="hidden col-span-full text-center py-16">
        <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Webinars Yet</h3>
        <p class="text-gray-500 mb-6">Create your first webinar to start connecting with learners</p>
        <button onclick="document.getElementById('create-webinar-btn').click()" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
            Create Your First Webinar
        </button>
    </div>
</div>

<!-- Create Webinar Modal -->
<div id="create-webinar-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Create New Webinar</h2>
            <button onclick="closeCreateWebinarModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="create-webinar-form" class="p-6 space-y-6">
            <!-- Webinar Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Webinar Title *</label>
                <input type="text" id="webinar-title" name="title" required 
                    placeholder="e.g., Web Development Fundamentals" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Make it clear and engaging</p>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                <textarea id="webinar-description" name="description" required rows="4"
                    placeholder="Describe what learners will learn in this webinar"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"></textarea>
                <p class="text-xs text-gray-500 mt-1">Include topics covered and learning outcomes</p>
            </div>

            <!-- Date and Time -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
                    <input type="date" id="webinar-date" name="date" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Time *</label>
                    <input type="time" id="webinar-time" name="time" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
            </div>

            <!-- Duration -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Duration (hours) *</label>
                <input type="number" id="webinar-duration" name="duration" required min="0.5" step="0.5" value="1"
                    placeholder="e.g., 1.5" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">How long will the webinar be?</p>
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (₹) *</label>
                <input type="number" id="webinar-price" name="price" required min="0" step="1" value="0"
                    placeholder="e.g., 499" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Set 0 for free webinar</p>
            </div>

            <!-- Max Participants -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Join Upto (optional)</label>
                <input type="number" id="webinar-max-participants" name="max_participants" min="1" step="1"
                    placeholder="e.g., 100" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Maximum number of participants (leave empty for unlimited)</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="button" onclick="closeCreateWebinarModal()" 
                    class="flex-1 px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" id="submit-webinar-btn"
                    class="flex-1 bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Webinar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadWebinars();
    loadStats();
    initializeEventListeners();
    
    // Check if there's edit data from webinar details page
    checkForEditData();
});

function checkForEditData() {
    const editData = sessionStorage.getItem('editWebinar');
    if (editData) {
        try {
            const webinar = JSON.parse(editData);
            
            // Populate form with edit data
            document.getElementById('webinar-title').value = webinar.title;
            document.getElementById('webinar-description').value = webinar.description;
            document.getElementById('webinar-date').value = webinar.date;
            document.getElementById('webinar-time').value = webinar.time;
            document.getElementById('webinar-duration').value = webinar.duration;
            document.getElementById('webinar-price').value = webinar.price;
            document.getElementById('webinar-max-participants').value = webinar.max_participants || '';
            
            // Set form to edit mode
            const form = document.getElementById('create-webinar-form');
            form.dataset.editId = webinar.id;
            
            // Change modal title
            document.querySelector('#create-webinar-modal h2').textContent = 'Edit Webinar';
            
            // Open modal
            openCreateWebinarModal();
            
            // Clear sessionStorage
            sessionStorage.removeItem('editWebinar');
        } catch (error) {
            console.error('Error loading edit data:', error);
            sessionStorage.removeItem('editWebinar');
        }
    }
}

function initializeEventListeners() {
    // Create webinar button
    document.getElementById('create-webinar-btn').addEventListener('click', openCreateWebinarModal);
    
    // Form submit
    document.getElementById('create-webinar-form').addEventListener('submit', handleCreateWebinar);
    
    // Search and filters
    document.getElementById('search-webinars').addEventListener('input', filterWebinars);
    document.getElementById('filter-status').addEventListener('change', filterWebinars);
    document.getElementById('sort-webinars').addEventListener('change', filterWebinars);
}

function openCreateWebinarModal() {
    document.getElementById('create-webinar-modal').classList.remove('hidden');
    document.getElementById('create-webinar-modal').classList.add('flex');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('webinar-date').setAttribute('min', today);
}

function closeCreateWebinarModal() {
    document.getElementById('create-webinar-modal').classList.add('hidden');
    document.getElementById('create-webinar-modal').classList.remove('flex');
    document.getElementById('create-webinar-form').reset();
    
    // Reset form title and remove edit ID
    document.querySelector('#create-webinar-modal h2').textContent = 'Create New Webinar';
    delete document.getElementById('create-webinar-form').dataset.editId;
}

async function handleCreateWebinar(e) {
    e.preventDefault();
    
    const form = e.target;
    const editId = form.dataset.editId; // Check if we're editing
    const isEdit = !!editId;
    
    const submitBtn = document.getElementById('submit-webinar-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ${isEdit ? 'Updating...' : 'Creating...'}`;
    
    try {
        const formData = {
            title: document.getElementById('webinar-title').value,
            description: document.getElementById('webinar-description').value,
            date: document.getElementById('webinar-date').value,
            time: document.getElementById('webinar-time').value,
            duration: parseFloat(document.getElementById('webinar-duration').value),
            price: parseFloat(document.getElementById('webinar-price').value),
            max_participants: document.getElementById('webinar-max-participants').value || null
        };
        
        // Add ID for edit
        if (isEdit) {
            formData.id = editId;
        }
        
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/webinars.php', {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                title: 'Success!',
                text: isEdit ? 'Webinar updated successfully' : 'Webinar created successfully',
                icon: 'success',
                confirmButtonColor: '#F59E0B'
            });
            
            closeCreateWebinarModal();
            loadWebinars(); // Reload webinars list
            loadStats(); // Reload stats
        } else {
            throw new Error(result.error || `Failed to ${isEdit ? 'update' : 'create'} webinar`);
        }
        
    } catch (error) {
        console.error(`Error ${isEdit ? 'updating' : 'creating'} webinar:`, error);
        Swal.fire({
            title: 'Error',
            text: error.message || `Failed to ${isEdit ? 'update' : 'create'} webinar. Please try again.`,
            icon: 'error',
            confirmButtonColor: '#F59E0B'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

async function loadWebinars() {
    const container = document.getElementById('webinars-container');
    const emptyState = document.getElementById('empty-state');
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/webinars.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Failed to load webinars');
        }
        
        const webinars = result.webinars || [];
        
        if (webinars.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        container.innerHTML = webinars.map(webinar => {
            const statusColors = {
                'upcoming': 'bg-blue-100 text-blue-800',
                'live': 'bg-green-100 text-green-800',
                'completed': 'bg-gray-100 text-gray-800',
                'cancelled': 'bg-red-100 text-red-800'
            };
            
            const statusColor = statusColors[webinar.status] || 'bg-gray-100 text-gray-800';
            const isFree = parseFloat(webinar.price_inr) === 0;
            
            return `
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <span class="px-3 py-1 ${statusColor} text-xs font-semibold rounded-full uppercase">
                                ${webinar.status}
                            </span>
                            ${isFree ? '<span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">FREE</span>' : ''}
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">${webinar.title}</h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">${webinar.description}</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                ${new Date(webinar.webinar_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })}
                                at ${webinar.webinar_time}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ${webinar.duration_hours} hour${webinar.duration_hours > 1 ? 's' : ''}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                ${webinar.total_registrations} registered
                                ${webinar.max_participants ? ` / ${webinar.max_participants}` : ''}
                            </div>
                            
                            ${!isFree ? `
                                <div class="flex items-center text-sm font-semibold text-accent">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    ₹${parseFloat(webinar.price_inr).toLocaleString('en-IN')}
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t">
                            <button onclick="viewWebinar(${webinar.id})" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition">
                                View Details
                            </button>
                            <button onclick="editWebinar(${webinar.id})" class="px-4 py-2 text-accent hover:bg-yellow-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteWebinar(${webinar.id})" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
    } catch (error) {
        console.error('Error loading webinars:', error);
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <p class="text-red-600">${error.message || 'Failed to load webinars'}</p>
                <button onclick="loadWebinars()" class="mt-4 text-accent hover:text-yellow-600">
                    Try Again
                </button>
            </div>
        `;
    }
}

async function loadStats() {
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/webinars.php');
        const result = await response.json();
        
        if (result.success && result.stats) {
            document.getElementById('stats-total-webinars').textContent = result.stats.total_webinars || 0;
            document.getElementById('stats-upcoming').textContent = result.stats.upcoming || 0;
            document.getElementById('stats-registered').textContent = result.stats.total_registrations || 0;
            document.getElementById('stats-completed').textContent = result.stats.completed || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

function viewWebinar(id) {
    window.location.href = `?panel=expert&page=webinar-details&id=${id}`;
}

function closeViewWebinarModal() {
    document.getElementById('view-webinar-modal').classList.add('hidden');
}

function displayWebinarDetailsInModal(webinar, registrations) {
    // Hide loading, show content
    document.getElementById('details-loading').classList.add('hidden');
    document.getElementById('details-loaded').classList.remove('hidden');
    
    // Header
    document.getElementById('detail-title').textContent = webinar.title;
    document.getElementById('detail-description').textContent = webinar.description;
    
    // Status badge
    const statusBadge = document.getElementById('detail-status-badge');
    const statusColors = {
        upcoming: 'bg-blue-100 text-blue-800',
        live: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800'
    };
    statusBadge.className = `px-3 py-1 rounded-full text-sm font-medium ${statusColors[webinar.status] || 'bg-gray-100 text-gray-800'}`;
    statusBadge.textContent = webinar.status.charAt(0).toUpperCase() + webinar.status.slice(1);
    
    // Date and time
    const date = new Date(webinar.webinar_date);
    document.getElementById('detail-date').textContent = date.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric' 
    });
    document.getElementById('detail-time').textContent = webinar.webinar_time;
    document.getElementById('detail-duration').textContent = `${webinar.duration_hours} hour${webinar.duration_hours > 1 ? 's' : ''}`;
    
    // Stats
    document.getElementById('detail-registrations').textContent = webinar.total_registrations || 0;
    document.getElementById('detail-price').textContent = webinar.price_inr > 0 ? `₹${webinar.price_inr}` : 'FREE';
    document.getElementById('detail-max-participants').textContent = webinar.max_participants || 'Unlimited';
    document.getElementById('detail-attended').textContent = webinar.total_attended || 0;
    
    // Registrations list
    const registrationsList = document.getElementById('detail-registrations-list');
    const noRegistrations = document.getElementById('detail-no-registrations');
    
    if (registrations && registrations.length > 0) {
        noRegistrations.classList.add('hidden');
        registrationsList.innerHTML = registrations.map(reg => `
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-accent rounded-full flex items-center justify-center text-white font-bold">
                        ${reg.full_name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${escapeHtml(reg.full_name)}</p>
                        <p class="text-sm text-gray-600">${escapeHtml(reg.email)}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-medium ${
                        reg.payment_status === 'completed' ? 'bg-green-100 text-green-800' :
                        reg.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                    }">
                        ${reg.payment_status}
                    </span>
                    ${reg.attended ? 
                        '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Attended</span>' :
                        ''
                    }
                </div>
            </div>
        `).join('');
    } else {
        noRegistrations.classList.remove('hidden');
        registrationsList.innerHTML = '';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function editWebinar(id) {
    try {
        // Fetch webinar details
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?id=${id}`);
        const result = await response.json();
        
        if (result.success && result.webinar) {
            const webinar = result.webinar;
            
            // Populate the form with existing data
            document.getElementById('webinar-title').value = webinar.title;
            document.getElementById('webinar-description').value = webinar.description;
            document.getElementById('webinar-date').value = webinar.webinar_date;
            document.getElementById('webinar-time').value = webinar.webinar_time;
            document.getElementById('webinar-duration').value = webinar.duration_hours;
            document.getElementById('webinar-price').value = webinar.price_inr;
            document.getElementById('webinar-max-participants').value = webinar.max_participants || '';
            
            // Change form submit to update instead of create
            const form = document.getElementById('create-webinar-form');
            form.dataset.editId = id; // Store the ID for updating
            
            // Change modal title
            document.querySelector('#create-webinar-modal h2').textContent = 'Edit Webinar';
            
            // Open the modal
            openCreateWebinarModal();
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load webinar data',
                icon: 'error',
                confirmButtonColor: '#F59E0B'
            });
        }
    } catch (error) {
        console.error('Error loading webinar for edit:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to load webinar data',
            icon: 'error',
            confirmButtonColor: '#F59E0B'
        });
    }
}

async function deleteWebinar(id) {
    const result = await Swal.fire({
        title: 'Delete Webinar?',
        text: 'This action cannot be undone. If there are registrations, the webinar will be cancelled instead.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) return;
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/webinars.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#F59E0B'
            });
            loadWebinars();
            loadStats();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Error deleting webinar:', error);
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to delete webinar',
            icon: 'error',
            confirmButtonColor: '#F59E0B'
        });
    }
}
</script>

<!-- View Webinar Details Modal -->
<div id="view-webinar-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Webinar Details</h2>
            <button onclick="closeViewWebinarModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="webinar-details-content" class="p-6">
            <!-- Loading state -->
            <div id="details-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-accent"></div>
                <p class="mt-4 text-gray-600">Loading details...</p>
            </div>

            <!-- Content will be loaded here -->
            <div id="details-loaded" class="hidden">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <h3 id="detail-title" class="text-2xl font-bold text-gray-900"></h3>
                        <span id="detail-status-badge"></span>
                    </div>
                    <p id="detail-description" class="text-gray-600"></p>
                </div>

                <!-- Info Grid -->
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p id="detail-date" class="font-semibold text-gray-900"></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Time</p>
                            <p id="detail-time" class="font-semibold text-gray-900"></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-green-50 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p id="detail-duration" class="font-semibold text-gray-900"></p>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p id="detail-registrations" class="text-2xl font-bold text-purple-600">0</p>
                        <p class="text-sm text-gray-600">Registrations</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p id="detail-price" class="text-2xl font-bold text-green-600">₹0</p>
                        <p class="text-sm text-gray-600">Price</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p id="detail-max-participants" class="text-2xl font-bold text-blue-600">Unlimited</p>
                        <p class="text-sm text-gray-600">Max Seats</p>
                    </div>
                    <div class="text-center p-4 bg-amber-50 rounded-lg">
                        <p id="detail-attended" class="text-2xl font-bold text-accent">0</p>
                        <p class="text-sm text-gray-600">Attended</p>
                    </div>
                </div>

                <!-- Registrations -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Registered Learners</h4>
                    <div id="detail-registrations-list">
                        <!-- Will be populated -->
                    </div>
                    <div id="detail-no-registrations" class="hidden text-center py-8 text-gray-500">
                        No registrations yet
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Webinar Idea Modal -->
<div id="ai-idea-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-4 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h2 class="text-xl font-bold">Create Webinar with AI</h2>
                </div>
                <button id="close-ai-modal-btn" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-600 mb-4">Describe your webinar idea and AI will help create a comprehensive webinar outline for you.</p>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Webinar Idea</label>
                <textarea 
                    id="ai-webinar-idea" 
                    rows="6" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none" 
                    placeholder="Example: I want to conduct a live webinar on React Hooks for intermediate developers. Cover useState, useEffect, custom hooks with real-world examples. Duration should be 2 hours with Q&A session at the end."
                ></textarea>
                <p class="text-sm text-gray-500 mt-2">Be specific about the topic, target audience, key points to cover, and duration.</p>
            </div>

            <div class="flex gap-3">
                <button id="generate-ai-webinar-btn" class="flex-1 bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Generate Webinar with AI
                </button>
                <button id="cancel-ai-modal-btn" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ========== AI WEBINAR CREATION FUNCTIONALITY ==========

// Open AI modal
document.getElementById('create-webinar-ai-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.remove('hidden');
    document.getElementById('ai-webinar-idea').value = '';
});

// Close AI modal
document.getElementById('close-ai-modal-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.add('hidden');
});

document.getElementById('cancel-ai-modal-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.add('hidden');
});

// Generate webinar with AI
document.getElementById('generate-ai-webinar-btn')?.addEventListener('click', async function() {
    const ideaTextarea = document.getElementById('ai-webinar-idea');
    const idea = ideaTextarea.value.trim();
    
    if (!idea) {
        Swal.fire({
            icon: 'warning',
            title: 'Idea Required',
            text: 'Please describe your webinar idea',
            confirmButtonColor: '#F59E0B'
        });
        return;
    }
    
    const button = this;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generating with AI...
    `;
    
    try {
        const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/generate-webinar-ai.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idea })
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            // Close AI modal
            document.getElementById('ai-idea-modal').classList.add('hidden');
            
            // Open create webinar modal
            openCreateWebinarModal();
            
            // Fill in webinar details
            document.getElementById('webinar-title').value = result.data.title || '';
            document.getElementById('webinar-description').value = result.data.description || '';
            document.getElementById('webinar-duration').value = result.data.duration_hours || '1';
            document.getElementById('webinar-price').value = result.data.price_inr || '0';
            
            // Set date to tomorrow if not provided
            if (result.data.suggested_date) {
                document.getElementById('webinar-date').value = result.data.suggested_date;
            } else {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                document.getElementById('webinar-date').value = tomorrow.toISOString().split('T')[0];
            }
            
            // Set time if provided
            if (result.data.suggested_time) {
                document.getElementById('webinar-time').value = result.data.suggested_time;
            }
            
            Swal.fire({
                icon: 'success',
                title: 'AI Webinar Generated!',
                text: 'Your webinar has been created with AI. Review and customize it before saving.',
                confirmButtonColor: '#F59E0B'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: result.message || 'Failed to generate webinar with AI',
                confirmButtonColor: '#F59E0B'
            });
        }
    } catch (error) {
        console.error('AI generation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating the webinar',
            confirmButtonColor: '#F59E0B'
        });
    } finally {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    }
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
