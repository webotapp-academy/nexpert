<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$page_title = "My Sessions - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">My Sessions</h1>
                <p class="text-sm sm:text-base text-gray-400">View and manage all your sessions</p>
            </div>
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="bg-[#00D4AA] text-[#080B10] px-4 sm:px-6 py-2 sm:py-3 rounded-lg hover:bg-[#00bda0] transition duration-200 font-bold text-sm sm:text-base shadow-md">
                Book New Session
            </a>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-[#131b2e] border border-gray-800 rounded-xl shadow-md mb-6 overflow-hidden">
            <div class="flex border-b border-gray-800 overflow-x-auto">
                <button class="session-filter-tab px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-medium border-b-2 border-[#00D4AA] text-[#00D4AA] whitespace-nowrap" data-filter="all">
                    All Sessions
                </button>
                <button class="session-filter-tab px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-medium text-gray-400 hover:text-white border-b-2 border-transparent whitespace-nowrap" data-filter="upcoming">
                    Upcoming
                </button>
                <button class="session-filter-tab px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-medium text-gray-400 hover:text-white border-b-2 border-transparent whitespace-nowrap" data-filter="completed">
                    Completed
                </button>
                <button class="session-filter-tab px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-medium text-gray-400 hover:text-white border-b-2 border-transparent whitespace-nowrap" data-filter="cancelled">
                    Cancelled
                </button>
            </div>
        </div>

        <!-- Sessions List -->
        <div id="sessions-container" class="space-y-4">
            <!-- Loading State -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-xl shadow-md p-8 text-center animate-pulse">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#00D4AA] mx-auto mb-4"></div>
                <p class="text-gray-400">Loading sessions...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="hidden bg-[#131b2e] border border-gray-800 rounded-xl shadow-md p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No Sessions Found</h3>
            <p class="text-gray-400 mb-6">You don't have any sessions yet. Start learning by booking a session with an expert!</p>
            <a href="<?php echo BASE_PATH; ?>/index.php?panel=learner&page=browse-experts" class="inline-block bg-[#00D4AA] text-[#080B10] px-6 py-3 rounded-lg hover:bg-[#00bda0] transition duration-200 font-bold shadow-md">
                Browse Experts
            </a>
        </div>
    </div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    let allSessions = [];
    let currentFilter = 'all';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        const normalizedPath = imagePath.replace(/^\/+/, '');
        return `${window.BASE_PATH}/${normalizedPath}`;
    }

    // Load all sessions
    async function loadSessions() {
        try {
            console.log('Loading all sessions from:', `${window.BASE_PATH}/admin-panel/apis/learner/dashboard.php`);
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/dashboard.php`, {
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Sessions result:', result);
            
            if (result.success && result.data) {
                // Use all_sessions from dashboard API
                allSessions = (result.data.all_sessions || []).map(s => ({
                    id: s.id,
                    expert_id: s.expert_id,
                    session_datetime: s.session_datetime,
                    duration: s.duration_minutes,
                    status: s.status,
                    expert_name: s.expert_name,
                    expert_photo: s.profile_photo,
                    expert_title: s.tagline
                }));
                
                renderSessions();
            } else {
                showError('Failed to load sessions');
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            showError('Error loading sessions. Please try again.');
        }
    }

    // Render sessions based on filter
    function renderSessions() {
        const container = document.getElementById('sessions-container');
        const emptyState = document.getElementById('empty-state');
        
        let filteredSessions = allSessions;
        
        // Apply filter
        if (currentFilter !== 'all') {
            filteredSessions = allSessions.filter(session => {
                const status = session.status.toLowerCase();
                if (currentFilter === 'upcoming') {
                    return status === 'scheduled' || status === 'confirmed' || status === 'pending';
                } else if (currentFilter === 'completed') {
                    return status === 'completed';
                } else if (currentFilter === 'cancelled') {
                    return status === 'cancelled';
                }
                return true;
            });
        }
        
        if (filteredSessions.length === 0) {
            container.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }
        
        container.classList.remove('hidden');
        emptyState.classList.add('hidden');
        
        container.innerHTML = filteredSessions.map(session => {
            const sessionDate = new Date(session.session_datetime);
            const formattedDate = sessionDate.toLocaleDateString('en-US', { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
            const formattedTime = sessionDate.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const statusColors = {
                'pending': 'bg-yellow-950/40 text-yellow-400 border border-yellow-800/40',
                'scheduled': 'bg-blue-950/40 text-blue-400 border border-blue-800/40',
                'confirmed': 'bg-emerald-950/40 text-emerald-400 border border-emerald-800/40',
                'completed': 'bg-slate-900/60 text-slate-300 border border-slate-700/50',
                'cancelled': 'bg-red-950/40 text-red-400 border border-red-800/40'
            };
            
            const statusColor = statusColors[session.status.toLowerCase()] || 'bg-gray-800 text-gray-400 border border-gray-700';
            
            // Check if session can be rescheduled (all except completed and cancelled)
            const status = session.status.toLowerCase();
            const isUpcoming = (status === 'confirmed' || status === 'scheduled') && sessionDate > new Date();
            const isPending = status === 'pending';
            const isCompleted = status === 'completed';
            const isCancelled = status === 'cancelled';
            const canShowReschedule = !isCompleted && !isCancelled && sessionDate > new Date();
            
            // Reschedule is allowed for all upcoming sessions
            const now = new Date();
            const canReschedule = canShowReschedule;
            const daysUntilSession = Math.ceil((sessionDate - now) / (1000 * 60 * 60 * 24));
            
            return `
                <div class="bg-[#131b2e] border border-gray-800 rounded-xl hover:shadow-xl transition duration-200">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- Expert Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <img src="${resolveImagePath(session.expert_photo)}" 
                                     alt="${session.expert_name}" 
                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover flex-shrink-0 border border-gray-800">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg sm:text-xl font-semibold text-white mb-1 truncate">
                                        ${session.expert_name}
                                    </h3>
                                    <p class="text-sm text-gray-400 mb-2">${session.expert_title || 'Expert'}</p>
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>${formattedDate}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>${formattedTime}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span>${session.duration || 60} mins</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status & Actions -->
                            <div class="flex flex-col items-end gap-3 sm:ml-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                                    ${session.status}
                                </span>
                                <div class="flex gap-2">
                                    ${canShowReschedule ? `
                                    <button onclick="openCancelModal(${session.id}, '${session.expert_name.replace(/'/g, "\\'")}')" 
                                       class="px-4 py-2 bg-red-950/40 text-red-400 border border-red-900/30 hover:bg-red-900/20 rounded-lg transition text-sm font-semibold">
                                        Cancel
                                    </button>
                                    ` : ''}
                                    ${canShowReschedule ? `
                                    <button onclick="openRescheduleModal(${session.id}, '${session.expert_name.replace(/'/g, "\\'")}', ${session.expert_id})"
                                       class="px-4 py-2 bg-yellow-950/40 text-yellow-400 border border-yellow-900/30 hover:bg-yellow-900/20 rounded-lg transition text-sm font-semibold">
                                        Reschedule
                                    </button>
                                    ` : ''}
                                    <a href="${window.BASE_PATH}/index.php?panel=learner&page=booking-details&booking_id=${session.id}" 
                                       class="px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition text-sm font-bold shadow-md">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Show error message
    function showError(message) {
        const container = document.getElementById('sessions-container');
        container.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-red-800 font-medium">${message}</p>
            </div>
        `;
    }

    // Handle filter tabs
    document.querySelectorAll('.session-filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            document.querySelectorAll('.session-filter-tab').forEach(t => {
                t.classList.remove('border-primary', 'text-primary');
                t.classList.add('text-gray-500', 'border-transparent');
            });
            this.classList.remove('text-gray-500', 'border-transparent');
            this.classList.add('border-primary', 'text-primary');
            
            // Update filter and render
            currentFilter = this.getAttribute('data-filter');
            renderSessions();
        });
    });

    // Reschedule Modal Functions
    let currentRescheduleBookingId = null;
    let currentExpertId = null;
    let expertAvailability = [];
    let expertBookedSlots = [];
    
    // Cancel Modal Functions
    let currentCancelBookingId = null;
    let currentCancelExpertName = null;
    
    function openCancelModal(bookingId, expertName) {
        currentCancelBookingId = bookingId;
        currentCancelExpertName = expertName;
        document.getElementById('cancel-expert-name').textContent = expertName;
        document.getElementById('cancel-modal').classList.remove('hidden');
    }
    
    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
        currentCancelBookingId = null;
        currentCancelExpertName = null;
        document.getElementById('cancel-reason').value = '';
    }
    
    async function submitCancel() {
        const reason = document.getElementById('cancel-reason').value.trim();
        
        if (!reason) {
            alert('Please provide a reason for cancellation.');
            return;
        }
        
        const submitBtn = document.getElementById('cancel-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Cancelling...';
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    booking_id: currentCancelBookingId,
                    action: 'cancel',
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Session cancelled successfully!');
                closeCancelModal();
                loadSessions(); // Reload sessions
            } else {
                alert(result.message || 'Failed to cancel session.');
            }
        } catch (error) {
            console.error('Error cancelling session:', error);
            alert('Error cancelling session. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Cancel Session';
        }
    }
    
    async function openRescheduleModal(bookingId, expertName, expertId) {
        console.log('Opening reschedule modal:', { bookingId, expertName, expertId });
        
        if (!expertId) {
            alert('Expert ID not found. Please refresh the page and try again.');
            return;
        }
        
        currentRescheduleBookingId = bookingId;
        currentExpertId = expertId;
        document.getElementById('reschedule-expert-name').textContent = expertName;
        document.getElementById('reschedule-modal').classList.remove('hidden');
        
        // Show loading state
        document.getElementById('availability-loading').classList.remove('hidden');
        document.getElementById('availability-content').classList.add('hidden');
        
        // Fetch expert availability
        try {
            const url = `${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`;
            console.log('Fetching availability from:', url);
            
            const response = await fetch(url, {
                credentials: 'include'
            });
            
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('JSON parse error:', e);
                alert('Error parsing server response. Check console for details.');
                return;
            }
            
            console.log('Parsed result:', result);
            
            if (result.success && result.data) {
                expertAvailability = result.data.availability || [];
                expertBookedSlots = result.data.booked_slots || [];
                
                // Generate available dates (next 90 days)
                generateAvailableDates();
            } else {
                alert('Failed to load expert availability');
            }
        } catch (error) {
            console.error('Error loading availability:', error);
            alert('Error loading expert availability');
        } finally {
            document.getElementById('availability-loading').classList.add('hidden');
            document.getElementById('availability-content').classList.remove('hidden');
        }
    }
    
    function generateAvailableDates() {
        const dateSelect = document.getElementById('reschedule-date-select');
        dateSelect.innerHTML = '<option value="">Select a date</option>';
        
        // Day name mapping
        const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        // Get available days from expert availability (day_of_week is a number: 0=Sunday, 1=Monday, etc.)
        const availableDays = new Set();
        expertAvailability.forEach(slot => {
            // day_of_week comes as number from database
            availableDays.add(parseInt(slot.day_of_week));
        });
        
        console.log('Available days:', Array.from(availableDays));
        
        // Generate next 90 days
        const today = new Date();
        const minDate = new Date(today.getTime() + (7 * 24 * 60 * 60 * 1000)); // 7 days from now
        
        for (let i = 0; i < 90; i++) {
            const date = new Date(minDate.getTime() + (i * 24 * 60 * 60 * 1000));
            const dayNumber = date.getDay(); // 0=Sunday, 1=Monday, etc.
            
            if (availableDays.has(dayNumber)) {
                const dateStr = date.toISOString().split('T')[0];
                const formattedDate = date.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric' 
                });
                
                const option = document.createElement('option');
                option.value = dateStr;
                option.textContent = formattedDate;
                dateSelect.appendChild(option);
            }
        }
    }
    
    function onDateChange() {
        const selectedDate = document.getElementById('reschedule-date-select').value;
        const timeSelect = document.getElementById('reschedule-time-select');
        timeSelect.innerHTML = '<option value="">Select a time</option>';
        
        if (!selectedDate) return;
        
        const date = new Date(selectedDate + 'T00:00:00'); // Ensure proper date parsing
        const dayNumber = date.getDay(); // 0=Sunday, 1=Monday, etc.
        
        console.log('Selected date:', selectedDate, 'Day number:', dayNumber);
        
        // Get available slots for this day (day_of_week is a number)
        const daySlots = expertAvailability.filter(slot => 
            parseInt(slot.day_of_week) === dayNumber
        );
        
        console.log('Day slots:', daySlots);
        
        // Get booked slots for this date
        const bookedTimes = expertBookedSlots
            .filter(slot => slot.session_datetime.startsWith(selectedDate))
            .map(slot => {
                const time = new Date(slot.session_datetime);
                return time.getHours() * 60 + time.getMinutes();
            });
        
        // Generate time slots
        daySlots.forEach(slot => {
            const startParts = slot.start_time.split(':');
            const endParts = slot.end_time.split(':');
            const startMinutes = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
            const endMinutes = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
            
            // Generate 60-minute slots
            for (let time = startMinutes; time < endMinutes; time += 60) {
                // Check if this slot is booked
                const isBooked = bookedTimes.some(bookedTime => 
                    Math.abs(bookedTime - time) < 60
                );
                
                if (!isBooked) {
                    const hours = Math.floor(time / 60);
                    const minutes = time % 60;
                    const timeStr = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    const displayTime = new Date(2000, 0, 1, hours, minutes).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                    
                    const option = document.createElement('option');
                    option.value = timeStr;
                    option.textContent = displayTime;
                    timeSelect.appendChild(option);
                }
            }
        });
        
        if (timeSelect.options.length === 1) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No available slots';
            option.disabled = true;
            timeSelect.appendChild(option);
        }
    }
    
    function closeRescheduleModal() {
        document.getElementById('reschedule-modal').classList.add('hidden');
        currentRescheduleBookingId = null;
        currentExpertId = null;
        document.getElementById('reschedule-date-select').value = '';
        document.getElementById('reschedule-time-select').innerHTML = '<option value="">Select a time</option>';
        document.getElementById('reschedule-reason').value = '';
    }
    
    async function submitReschedule() {
        const newDate = document.getElementById('reschedule-date-select').value;
        const newTime = document.getElementById('reschedule-time-select').value;
        const reason = document.getElementById('reschedule-reason').value;
        
        if (!newDate || !newTime) {
            alert('Please select both date and time for rescheduling.');
            return;
        }
        
        const submitBtn = document.getElementById('reschedule-submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    booking_id: currentRescheduleBookingId,
                    action: 'reschedule',
                    new_date: newDate,
                    new_time: newTime,
                    reason: reason
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Reschedule request submitted successfully! The expert will be notified.');
                closeRescheduleModal();
                loadSessions(); // Reload sessions
            } else {
                alert(result.message || 'Failed to submit reschedule request.');
            }
        } catch (error) {
            console.error('Error submitting reschedule:', error);
            alert('Error submitting reschedule request. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
        }
    }

    // Load sessions on page load
    loadSessions();
</script>

<!-- Cancel Modal -->
<div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 animate-fadeIn">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-850">
            <h3 class="text-xl font-bold text-white">Cancel Session</h3>
            <button onclick="closeCancelModal()" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="bg-yellow-950/20 border border-yellow-800/40 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="text-sm text-yellow-200">Are you sure you want to cancel your session with <span id="cancel-expert-name" class="font-semibold text-white"></span>? This action cannot be undone.</p>
            </div>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Reason for Cancellation <span class="text-red-500">*</span></label>
                <textarea id="cancel-reason" rows="3" placeholder="Please provide a reason for cancellation..." class="w-full px-4 py-2 bg-[#0d131f] border border-gray-850 text-white rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 resize-none" required></textarea>
            </div>
        </div>
        
        <div class="flex gap-3 mt-6">
            <button onclick="closeCancelModal()" class="flex-1 px-4 py-2 border border-gray-850 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                Keep Session
            </button>
            <button id="cancel-submit-btn" onclick="submitCancel()" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-bold shadow-md">
                Cancel Session
            </button>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="reschedule-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 animate-fadeIn">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-850">
            <h3 class="text-xl font-bold text-white">Reschedule Session</h3>
            <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <p class="text-gray-400 mb-4">Request to reschedule your session with <span id="reschedule-expert-name" class="font-semibold text-white"></span></p>
        
        <!-- Loading State -->
        <div id="availability-loading" class="hidden text-center py-8">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#00D4AA] mx-auto mb-3"></div>
            <p class="text-gray-400">Loading expert availability...</p>
        </div>
        
        <!-- Availability Content -->
        <div id="availability-content" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Select Date</label>
                <select id="reschedule-date-select" onchange="onDateChange()" class="w-full px-4 py-2 bg-[#0d131f] border border-gray-850 text-white rounded-lg focus:outline-none focus:border-gray-700">
                    <option value="">Select a date</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Only showing dates when expert is available</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Select Time</label>
                <select id="reschedule-time-select" class="w-full px-4 py-2 bg-[#0d131f] border border-gray-850 text-white rounded-lg focus:outline-none focus:border-gray-700">
                    <option value="">Select a time</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Only showing available time slots</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Reason (Optional)</label>
                <textarea id="reschedule-reason" rows="3" placeholder="Please provide a reason for rescheduling..." class="w-full px-4 py-2 bg-[#0d131f] border border-gray-850 text-white rounded-lg focus:outline-none focus:border-gray-700 resize-none"></textarea>
            </div>
        </div>
        
        <div class="flex gap-3 mt-6">
            <button onclick="closeRescheduleModal()" class="flex-1 px-4 py-2 border border-gray-850 text-gray-400 rounded-lg hover:bg-gray-800 transition font-medium">
                Cancel
            </button>
            <button id="reschedule-submit-btn" onclick="submitReschedule()" class="flex-1 px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition font-bold">
                Submit Request
            </button>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
