<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Get webinar ID
$webinar_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$webinar_id) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$page_title = "Webinar Details - Nexpert.ai";
$panel_type = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>

<div class="bg-[#080B10] min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#00D4AA]"></div>
            <p class="mt-4 text-gray-400">Loading webinar details...</p>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden text-center py-12">
            <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-8 max-w-md mx-auto">
                <div class="inline-block p-4 bg-red-950/40 rounded-full mb-4">
                    <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Failed to Load Webinar</h3>
                <p class="text-gray-400 mb-6" id="error-message"></p>
                <a href="<?php echo BASE_PATH; ?>/index.php" class="inline-block bg-[#00D4AA] text-[#080B10] font-bold px-6 py-3 rounded-lg hover:bg-[#00bda0] transition">
                    Back to Home
                </a>
            </div>
        </div>

        <!-- Webinar Details -->
        <div id="webinar-details" class="hidden animate-fadeIn">
            
            <!-- Back Button -->
            <div class="mb-6">
                <button onclick="history.back()" class="inline-flex items-center text-[#00D4AA] hover:text-white font-medium transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Expert Profile
                </button>
            </div>

            <!-- Webinar Header Card -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl overflow-hidden mb-8">
                <!-- Banner Background with Gradient -->
                <div class="h-40 bg-gradient-to-r from-[#0e1322] via-[#131b2e] to-[#0e1322] border-b border-gray-850 relative">
                    <div class="absolute top-4 right-4">
                        <span id="webinar-status-badge" class="px-4 py-2 rounded-full text-sm font-bold text-[#00D4AA] bg-[#00D4AA]/10 border border-[#00D4AA]/20 backdrop-blur-sm"></span>
                    </div>
                    <div class="absolute bottom-4 left-8">
                        <span class="inline-block px-3 py-1 bg-[#00D4AA] text-[#080B10] text-xs font-bold rounded-full uppercase">
                            🎥 Live Webinar
                        </span>
                    </div>
                </div>
                
                <!-- Webinar Content -->
                <div class="px-8 pb-8 -mt-8">
                    <div class="bg-[#0e1322]/80 border border-gray-800/80 backdrop-blur-md rounded-xl p-6 mb-6 shadow-lg">
                        <h1 id="webinar-title" class="text-4xl font-extrabold text-white mb-4"></h1>
                        <p id="webinar-description" class="text-gray-300 text-lg leading-relaxed mb-6"></p>
                        
                        <!-- Key Info Grid -->
                        <div class="grid md:grid-cols-4 gap-4 mb-6">
                            <div class="flex items-center gap-3 bg-[#131b2e] border border-gray-850 rounded-lg p-4">
                                <div class="p-2 bg-[#00D4AA]/10 rounded-lg">
                                    <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Date</p>
                                    <p id="webinar-date" class="font-bold text-white text-sm"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 bg-[#131b2e] border border-gray-850 rounded-lg p-4">
                                <div class="p-2 bg-purple-500/10 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Time</p>
                                    <p id="webinar-time" class="font-bold text-white text-sm"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 bg-[#131b2e] border border-gray-850 rounded-lg p-4">
                                <div class="p-2 bg-emerald-500/10 rounded-lg">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Duration</p>
                                    <p id="webinar-duration" class="font-bold text-white text-sm"></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 bg-[#131b2e] border border-gray-850 rounded-lg p-4">
                                <div class="p-2 bg-[#00D4AA]/10 rounded-lg">
                                    <svg class="w-6 h-6 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium">Price</p>
                                    <p id="webinar-price" class="font-bold text-white text-sm"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Expert Info Section -->
                    <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">About the Expert</h2>
                        <div id="expert-info" class="flex items-start gap-6">
                            <!-- Expert photo and info will be loaded here -->
                        </div>
                    </div>

                    <!-- What You'll Learn Section -->
                    <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">What You'll Learn</h2>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-green-500/10 rounded-lg mt-1">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1">Comprehensive Content</h4>
                                    <p class="text-gray-400 text-sm">Learn from industry expert with hands-on examples and real-world applications.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-green-500/10 rounded-lg mt-1">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1">Interactive Session</h4>
                                    <p class="text-gray-400 text-sm">Ask questions and get instant answers during the live Q&A session.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-green-500/10 rounded-lg mt-1">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1">Practical Insights</h4>
                                    <p class="text-gray-400 text-sm">Get actionable takeaways you can implement immediately.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Stats -->
                    <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Registration Info</h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-purple-500/10 rounded-full">
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p id="stat-registrations" class="text-3xl font-bold text-white">0</p>
                                    <p class="text-gray-400 text-sm">Already Registered</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-[#00D4AA]/10 rounded-full">
                                    <svg class="w-8 h-8 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p id="stat-max-participants" class="text-3xl font-bold text-white">∞</p>
                                    <p class="text-gray-400 text-sm">Max Participants</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Availability Bar -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-400">Seats Available</span>
                                <span id="seats-percentage" class="text-sm font-semibold text-white">100%</span>
                            </div>
                            <div class="w-full bg-gray-800 rounded-full h-3">
                                <div id="seats-bar" class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Registration Card -->
                <div class="space-y-6">
                    <div class="bg-[#131b2e] border border-gray-800 rounded-xl p-6 sticky top-4">
                        <!-- Price Display -->
                        <div class="text-center mb-6 p-6 bg-[#0e1322] border border-gray-850 rounded-xl">
                            <p class="text-gray-400 text-sm mb-2">Webinar Price</p>
                            <p id="price-display" class="text-4xl font-extrabold text-[#00D4AA]"></p>
                        </div>

                        <!-- Register Button -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'learner'): ?>
                        <div id="register-button-container">
                            <!-- Button will be rendered dynamically based on registration status -->
                        </div>
                        <?php else: ?>
                        <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=auth" class="block w-full bg-[#00D4AA] text-[#080B10] px-6 py-4 rounded-lg hover:bg-[#00bda0] transition-all duration-300 font-bold shadow-lg text-lg text-center mb-4">
                            Login to Register
                        </a>
                        <?php endif; ?>

                        <!-- Features List -->
                        <div class="space-y-3 border-t border-gray-850 pt-4">
                            <h3 class="font-bold text-white mb-3 text-sm">What's Included:</h3>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Live interactive session</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Q&A with expert</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Certificate of attendance</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-[#00D4AA] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Recording access (24 hours)</span>
                            </div>
                        </div>

                        <!-- Share Section -->
                        <div class="border-t border-gray-850 pt-4 mt-4">
                            <p class="text-sm text-gray-400 mb-3 font-semibold">Share this webinar:</p>
                            <div class="flex gap-2">
                                <button class="flex-1 p-2 bg-[#0e1322] border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-850 rounded-lg transition">
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </button>
                                <button class="flex-1 p-2 bg-[#0e1322] border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-850 rounded-lg transition">
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </button>
                                <button class="flex-1 p-2 bg-[#0e1322] border border-gray-800 text-gray-400 hover:text-white hover:bg-gray-850 rounded-lg transition">
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
        
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?id=${WEBINAR_ID}`);
        
        console.log('Response status:', response.status);
        
        const result = await response.json();
        
        console.log('API Result:', result);
        
        if (result.success && result.webinar) {
            console.log('Webinar data:', result.webinar);
            await displayWebinarDetails(result.webinar);
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('webinar-details').classList.remove('hidden');
        } else {
            console.error('API Error:', result.message);
            showError(result.message || 'Webinar not found');
        }
    } catch (error) {
        console.error('Error loading webinar:', error);
        showError('Failed to load webinar details: ' + error.message);
    }
}

async function displayWebinarDetails(webinar) {
    // Header
    document.getElementById('webinar-title').textContent = webinar.title;
    document.getElementById('webinar-description').textContent = webinar.description;
    
    // Status badge
    const statusBadge = document.getElementById('webinar-status-badge');
    const statusColors = {
        upcoming: '🗓️ Upcoming',
        live: '🔴 Live Now',
        completed: '✅ Completed',
        cancelled: '❌ Cancelled'
    };
    statusBadge.textContent = statusColors[webinar.status] || webinar.status;
    
    // Date and time
    const date = new Date(webinar.webinar_date);
    document.getElementById('webinar-date').textContent = date.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'short', day: 'numeric' 
    });
    
    // Format time
    const timeStr = webinar.webinar_time;
    const timeParts = timeStr.split(':');
    const hour = parseInt(timeParts[0]);
    const minute = timeParts[1];
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    document.getElementById('webinar-time').textContent = `${hour12}:${minute} ${ampm}`;
    
    document.getElementById('webinar-duration').textContent = `${webinar.duration_hours} hour${webinar.duration_hours > 1 ? 's' : ''}`;
    
    // Price
    const priceText = webinar.price_inr > 0 ? `₹${webinar.price_inr}` : 'FREE';
    document.getElementById('webinar-price').textContent = priceText;
    document.getElementById('price-display').textContent = priceText;
    
    // Stats
    const registrations = webinar.total_registrations || 0;
    const maxParticipants = webinar.max_participants || 0;
    
    document.getElementById('stat-registrations').textContent = registrations;
    document.getElementById('stat-max-participants').textContent = maxParticipants || '∞';
    
    // Calculate seats availability
    if (maxParticipants > 0) {
        const availablePercentage = ((maxParticipants - registrations) / maxParticipants * 100).toFixed(0);
        document.getElementById('seats-percentage').textContent = `${availablePercentage}% available`;
        document.getElementById('seats-bar').style.width = `${availablePercentage}%`;
        
        // Change color based on availability
        const bar = document.getElementById('seats-bar');
        if (availablePercentage < 20) {
            bar.className = 'bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full transition-all duration-500';
        } else if (availablePercentage < 50) {
            bar.className = 'bg-gradient-to-r from-yellow-500 to-yellow-600 h-3 rounded-full transition-all duration-500';
        }
    }
    
    // Render register button based on registration status
    renderRegisterButton(webinar.is_registered, webinar.status);
    
    // Load expert info
    await loadExpertInfo(webinar.expert_id);
}

function renderRegisterButton(isRegistered, webinarStatus) {
    const container = document.getElementById('register-button-container');
    if (!container) return; // Not a learner
    
    if (isRegistered) {
        // Already registered - show success state
        container.innerHTML = `
            <button disabled class="w-full bg-emerald-950/40 text-emerald-400 border border-emerald-800/40 px-6 py-4 rounded-lg font-semibold shadow-lg text-lg flex items-center justify-center gap-2 mb-4 cursor-not-allowed">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Already Registered
            </button>
            <p class="text-sm text-center text-gray-400 mb-4">
                You will receive joining details via email before the webinar starts.
            </p>
        `;
    } else if (webinarStatus !== 'upcoming') {
        // Webinar not available for registration
        container.innerHTML = `
            <button disabled class="w-full bg-gray-800 text-gray-500 px-6 py-4 rounded-lg font-semibold shadow-lg text-lg flex items-center justify-center gap-2 mb-4 cursor-not-allowed">
                Registration Closed
            </button>
        `;
    } else {
        // Available for registration
        container.innerHTML = `
            <button id="register-btn" class="w-full bg-[#00D4AA] text-[#080B10] px-6 py-4 rounded-lg hover:bg-[#00bda0] transition-all duration-300 font-bold shadow-lg text-lg flex items-center justify-center gap-2 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                </svg>
                Register Now
            </button>
        `;
        
        // Add event listener
        const registerBtn = document.getElementById('register-btn');
        if (registerBtn) {
            registerBtn.addEventListener('click', () => registerForWebinar(WEBINAR_ID));
        }
    }
}

async function loadExpertInfo(expertId) {
    try {
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/get-expert-profile.php?id=${expertId}`);
        const result = await response.json();
        
        if (result.success && result.expert) {
            const expert = result.expert;
            const expertHtml = `
                <div class="w-20 h-20 rounded-full bg-gray-800 overflow-hidden flex-shrink-0 border border-gray-700">
                    ${expert.profile_photo ? 
                        `<img src="${BASE_PATH}/${expert.profile_photo}" class="w-full h-full object-cover" alt="${expert.full_name}">` :
                        `<div class="w-full h-full flex items-center justify-center bg-[#00D4AA] text-[#080B10] text-2xl font-bold">${expert.full_name.charAt(0)}</div>`
                    }
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-white mb-1">${escapeHtml(expert.full_name)}</h3>
                    <p class="text-gray-400 mb-3">${escapeHtml(expert.tagline || 'Expert Professional')}</p>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400">
                        <div class="flex items-center gap-1">
                            <span class="text-[#00D4AA]">★★★★★</span>
                            <span class="font-semibold text-white">${expert.rating_average || '5.0'}</span>
                        </div>
                        <span>•</span>
                        <span>${expert.total_sessions || 0}+ sessions</span>
                        ${expert.industry_experience_years ? `<span>•</span><span>${expert.industry_experience_years} years exp.</span>` : ''}
                    </div>
                    <a href="${BASE_PATH}/index.php?panel=learner&page=expert-profile&expert_id=${expertId}" class="inline-block mt-3 text-[#00D4AA] hover:text-white font-medium text-sm transition">
                        View Full Profile →
                    </a>
                </div>
            `;
            document.getElementById('expert-info').innerHTML = expertHtml;
        }
    } catch (error) {
        console.error('Error loading expert info:', error);
    }
}

async function registerForWebinar(webinarId) {
    // Show confirmation
    const result = await Swal.fire({
        title: 'Register for Webinar?',
        text: 'You will receive confirmation email with joining details.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#00D4AA',
        cancelButtonColor: '#1F2937',
        confirmButtonText: 'Yes, Register Me!',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) return;
    
    try {
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we register you',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinar-registration.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ webinar_id: webinarId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: 'Registration Successful!',
                html: `
                    <div class="text-left text-white">
                        <p class="mb-3">You have successfully registered for:</p>
                        <div class="bg-[#0e1322] border border-gray-800 p-4 rounded-lg mb-3">
                            <p class="font-bold text-[#00D4AA]">${data.webinar.title}</p>
                            <p class="text-sm text-gray-400 mt-1">
                                📅 ${new Date(data.webinar.date).toLocaleDateString('en-US', { 
                                    year: 'numeric', month: 'long', day: 'numeric' 
                                })}
                            </p>
                            <p class="text-sm text-gray-400">
                                🕐 ${data.webinar.time}
                            </p>
                        </div>
                        <p class="text-sm text-gray-400">
                            ${data.payment_required ? 
                                '💳 Please complete payment to confirm your registration.' : 
                                '✅ Your registration is confirmed! Check your email for joining details.'
                            }
                        </p>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#00D4AA',
                confirmButtonText: data.payment_required ? 'Proceed to Payment' : 'Done'
            }).then((result) => {
                if (result.isConfirmed && data.payment_required) {
                    window.location.href = `${BASE_PATH}/index.php?panel=learner&page=webinar-payment&webinar_id=${webinarId}`;
                } else {
                    location.reload();
                }
            });
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error registering:', error);
        Swal.fire({
            title: 'Registration Failed',
            text: error.message || 'Failed to register for webinar. Please try again.',
            icon: 'error',
            confirmButtonColor: '#00D4AA'
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

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
