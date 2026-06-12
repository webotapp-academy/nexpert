<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if viewing as expert (owner) or public view
$isOwner = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
$isPublicView = !$isOwner; // Anyone can view webinar details

// Get webinar ID
$webinar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$webinar_id) {
    if ($isOwner) {
        header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=my-webinars');
    } else {
        header('Location: ' . BASE_PATH . '/index.php');
    }
    exit;
}

$page_title = "Webinar Details - Nexpert.ai";
$panel_type = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="?panel=expert&page=my-webinars" class="inline-flex items-center text-accent hover:text-yellow-600 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to My Webinars
        </a>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-accent"></div>
        <p class="mt-4 text-gray-600">Loading webinar details...</p>
    </div>

    <!-- Error State -->
    <div id="error-state" class="hidden text-center py-12">
        <div class="inline-block p-4 bg-red-50 rounded-full mb-4">
            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Failed to Load Webinar</h3>
        <p class="text-gray-600 mb-6" id="error-message"></p>
        <a href="?panel=expert&page=my-webinars" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
            Back to My Webinars
        </a>
    </div>

    <!-- Webinar Details -->
    <div id="webinar-details" class="hidden">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h1 id="webinar-title" class="text-3xl font-bold text-gray-900"></h1>
                        <span id="webinar-status-badge"></span>
                    </div>
                    <p id="webinar-description" class="text-gray-600 mb-4"></p>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date</p>
                                <p id="webinar-date" class="font-semibold text-gray-900"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Time</p>
                                <p id="webinar-time" class="font-semibold text-gray-900"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Duration</p>
                                <p id="webinar-duration" class="font-semibold text-gray-900"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <?php if ($isOwner): ?>
                    <button id="edit-webinar-btn" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Webinar
                    </button>
                    <button id="delete-webinar-btn" class="border border-red-500 text-red-500 px-6 py-3 rounded-lg hover:bg-red-50 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Webinar
                    </button>
                    <?php else: ?>
                    <!-- Register/Join Button for Public View -->
                    <button class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        Register for Webinar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p id="stat-registrations" class="text-2xl font-bold text-gray-900">0</p>
                        <p class="text-gray-600 text-sm mt-1">Registrations</p>
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
                        <p id="stat-price" class="text-2xl font-bold text-gray-900">₹0</p>
                        <p class="text-gray-600 text-sm mt-1">Price</p>
                    </div>
                    <div class="p-3 bg-green-500 rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p id="stat-max-participants" class="text-2xl font-bold text-gray-900">Unlimited</p>
                        <p class="text-gray-600 text-sm mt-1">Max Seats</p>
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
                        <p id="stat-attended" class="text-2xl font-bold text-gray-900">0</p>
                        <p class="text-gray-600 text-sm mt-1">Attended</p>
                    </div>
                    <div class="p-3 bg-accent rounded-full">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations List -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Registered Learners</h2>
            
            <div id="registrations-list">
                <!-- Registrations will be loaded here -->
            </div>

            <!-- Empty State for Registrations -->
            <div id="no-registrations" class="hidden text-center py-12">
                <div class="inline-block p-4 bg-gray-50 rounded-full mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Registrations Yet</h3>
                <p class="text-gray-600">Learners will appear here once they register for this webinar</p>
            </div>
        </div>
    </div>
</div>

<script>
// BASE_PATH is already defined in header.php
const WEBINAR_ID = <?php echo $webinar_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
    loadWebinarDetails();
});

async function loadWebinarDetails() {
    try {
        console.log('Fetching webinar details for ID:', WEBINAR_ID);
        console.log('API URL:', `${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?id=${WEBINAR_ID}`);
        
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?id=${WEBINAR_ID}`);
        
        // Log the response for debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Get response text first to see what we're getting
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            showError('Invalid response from server');
            return;
        }
        
        console.log('API Result:', result);
        
        if (result.success && result.webinar) {
            displayWebinarDetails(result.webinar, result.registrations);
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('webinar-details').classList.remove('hidden');
        } else {
            showError(result.message || 'Webinar not found');
        }
    } catch (error) {
        console.error('Error loading webinar:', error);
        showError('Failed to load webinar details: ' + error.message);
    }
}

function displayWebinarDetails(webinar, registrations) {
    // Header
    document.getElementById('webinar-title').textContent = webinar.title;
    document.getElementById('webinar-description').textContent = webinar.description;
    
    // Status badge
    const statusBadge = document.getElementById('webinar-status-badge');
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
    document.getElementById('webinar-date').textContent = date.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric' 
    });
    document.getElementById('webinar-time').textContent = webinar.webinar_time;
    document.getElementById('webinar-duration').textContent = `${webinar.duration_hours} hour${webinar.duration_hours > 1 ? 's' : ''}`;
    
    // Stats
    document.getElementById('stat-registrations').textContent = webinar.total_registrations || 0;
    document.getElementById('stat-price').textContent = webinar.price_inr > 0 ? `₹${webinar.price_inr}` : 'FREE';
    document.getElementById('stat-max-participants').textContent = webinar.max_participants || 'Unlimited';
    document.getElementById('stat-attended').textContent = webinar.total_attended || 0;
    
    // Registrations list
    if (registrations && registrations.length > 0) {
        displayRegistrations(registrations);
    } else {
        document.getElementById('no-registrations').classList.remove('hidden');
    }
    
    // Edit button - redirect to my-webinars page with edit data in sessionStorage
    document.getElementById('edit-webinar-btn').addEventListener('click', () => {
        // Store webinar data in sessionStorage for editing
        sessionStorage.setItem('editWebinar', JSON.stringify({
            id: webinar.id,
            title: webinar.title,
            description: webinar.description,
            date: webinar.webinar_date,
            time: webinar.webinar_time,
            duration: webinar.duration_hours,
            price: webinar.price_inr,
            max_participants: webinar.max_participants
        }));
        
        // Redirect to my-webinars page
        window.location.href = `?panel=expert&page=my-webinars`;
    });
    
    // Delete button
    document.getElementById('delete-webinar-btn').addEventListener('click', () => deleteWebinar(WEBINAR_ID));
}

function displayRegistrations(registrations) {
    const listHtml = registrations.map(reg => `
        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-3 hover:bg-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center text-white font-bold text-lg">
                    ${reg.full_name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">${escapeHtml(reg.full_name)}</p>
                    <p class="text-sm text-gray-600">${escapeHtml(reg.email)}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="px-3 py-1 rounded-full text-sm font-medium ${
                    reg.payment_status === 'completed' ? 'bg-green-100 text-green-800' :
                    reg.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                    reg.payment_status === 'failed' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                }">
                    ${reg.payment_status.charAt(0).toUpperCase() + reg.payment_status.slice(1)}
                </span>
                ${reg.attended ? 
                    '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Attended</span>' :
                    '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">Not Attended</span>'
                }
            </div>
        </div>
    `).join('');
    
    document.getElementById('registrations-list').innerHTML = listHtml;
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
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinars.php`, {
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
            }).then(() => {
                window.location.href = '?panel=expert&page=my-webinars';
            });
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

function showError(message) {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('error-message').textContent = message;
    document.getElementById('error-state').classList.remove('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
