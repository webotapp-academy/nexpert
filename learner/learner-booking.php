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

$page_title = "Book Session - Nexpert.ai";
$panel_type = "learner";

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<!-- Flatpickr Date Picker Assets -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
.flatpickr-calendar {
    background: #0c1222 !important;
    border: 1px solid rgba(0, 212, 170, 0.3) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.95), 0 0 25px rgba(0, 212, 170, 0.1) !important;
    font-family: inherit !important;
    padding: 14px !important;
    width: 320px !important;
    backdrop-filter: blur(16px) !important;
}
.flatpickr-calendar::before, .flatpickr-calendar::after {
    border-bottom-color: #0c1222 !important;
}
.flatpickr-months {
    margin-bottom: 8px !important;
}
.flatpickr-months .flatpickr-month {
    color: #fff !important;
    fill: #fff !important;
    height: 38px !important;
}
.flatpickr-current-month {
    font-size: 105% !important;
    padding-top: 4px !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months,
.flatpickr-current-month input.cur-year {
    color: #fff !important;
    font-weight: 800 !important;
}
.flatpickr-months .flatpickr-prev-month, 
.flatpickr-months .flatpickr-next-month {
    color: #00D4AA !important;
    fill: #00D4AA !important;
    padding: 6px !important;
    border-radius: 0.5rem !important;
}
.flatpickr-months .flatpickr-prev-month:hover, 
.flatpickr-months .flatpickr-next-month:hover {
    background: rgba(0, 212, 170, 0.15) !important;
}
.flatpickr-weekdays {
    margin-bottom: 6px !important;
}
.flatpickr-weekday {
    color: #00D4AA !important;
    font-weight: 800 !important;
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}
.flatpickr-day {
    color: #e2e8f0 !important;
    border-radius: 0.75rem !important;
    font-weight: 600 !important;
    margin: 2px 0 !important;
    height: 38px !important;
    line-height: 38px !important;
    transition: all 0.15s ease !important;
}
.flatpickr-day:hover, .flatpickr-day:focus {
    background: rgba(0, 212, 170, 0.2) !important;
    border-color: #00D4AA !important;
    color: #00D4AA !important;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
    background: #00D4AA !important;
    border-color: #00D4AA !important;
    color: #080B10 !important;
    font-weight: 900 !important;
    box-shadow: 0 0 16px rgba(0, 212, 170, 0.5) !important;
}
.flatpickr-day.today {
    border-color: #00D4AA !important;
    color: #00D4AA !important;
}
.flatpickr-day.today:hover {
    background: #00D4AA !important;
    color: #080B10 !important;
}
.flatpickr-day.flatpickr-disabled, 
.flatpickr-day.prevMonthDay, 
.flatpickr-day.nextMonthDay {
    color: #334155 !important;
    opacity: 0.4 !important;
}
</style>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Enhanced Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="?panel=learner&page=browse-experts" class="text-gray-400 hover:text-[#00D4AA] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white">
                    Book a Session
                </h1>
            </div>
            <p class="text-gray-400 ml-9">Schedule a personalized session with your expert</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Expert Info Card - Enhanced -->
            <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-8 h-fit transition-transform duration-300">
                <!-- Expert Profile Section -->
                <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-800">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-[#00D4AA] to-blue-600 rounded-full opacity-75 group-hover:opacity-100 blur transition duration-300"></div>
                        <div id="expert-photo" class="relative w-24 h-24 rounded-full bg-[#0d131f] overflow-hidden flex items-center justify-center ring-4 ring-[#131b2e] shadow-lg">
                            <div class="animate-pulse">
                                <svg class="w-12 h-12 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 id="expert-name" class="text-xl font-bold text-white mb-1">
                            <span class="inline-block animate-pulse bg-gray-800 rounded px-3 py-1 text-gray-500">Loading...</span>
                        </h3>
                        <p id="expert-title" class="text-gray-400 text-sm mb-2">
                            <span class="inline-block animate-pulse bg-gray-800 rounded px-2 py-0.5 text-gray-600">Loading...</span>
                        </p>
                        <div id="expert-trust-badge-container" class="mt-2">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#00D4AA]/10 border border-[#00D4AA]/30 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
                                <span id="expert-trust-band" class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider">Verified</span>
                                <span id="expert-trust-score" class="text-xs font-semibold text-gray-300">| 85% Trust</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Session Details Card -->
                <div class="bg-[#0d131f] border border-gray-800/80 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Session Details
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 text-sm">Duration:</span>
                            <span class="text-white font-semibold bg-[#131b2e] border border-gray-800 px-3 py-1 rounded-lg">60 minutes</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 text-sm">Format:</span>
                            <span class="text-white font-semibold bg-[#131b2e] border border-gray-800 px-3 py-1 rounded-lg flex items-center gap-1">
                                <svg class="w-4 h-4 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                </svg>
                                Video Call
                            </span>
                        </div>
                        <div class="pt-3 border-t border-gray-800">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-400 text-sm">Session Fee:</span>
                                <div class="text-right">
                                    <span id="session-price" class="text-2xl font-bold text-[#00D4AA]">₹0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Time Display -->
                <div class="bg-emerald-950/20 border border-emerald-800/40 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Selected Time
                    </h4>
                    <div id="selected-datetime" class="text-gray-300 text-sm">
                        Please select a date and time
                    </div>
                </div>
                
                <!-- Confirm Button -->
                <button id="confirm-booking-btn" disabled class="group relative w-full bg-[#00D4AA] text-[#080B10] px-6 py-4 rounded-xl hover:bg-[#00bda0] transition-all duration-300 font-bold text-lg shadow-lg hover:shadow-2xl disabled:bg-gray-800 disabled:text-gray-600 disabled:cursor-not-allowed disabled:transform-none">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Proceed to Payment
                    </span>
                </button>
            </div>

            <!-- Booking Calendar - Enhanced -->
            <div class="lg:col-span-2 bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-8">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1 h-8 bg-[#00D4AA] rounded-full"></div>
                    <h3 class="text-2xl font-bold text-white">Select Date & Time</h3>
                </div>
                
                <!-- Date Selection -->
                <div class="mb-10">
                    <label for="session-date" class="block text-sm font-semibold text-gray-300 mb-4 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Choose a Date
                        </span>
                        <span class="text-xs text-[#00D4AA] font-semibold bg-[#00D4AA]/10 px-2.5 py-1 rounded-lg border border-[#00D4AA]/30 flex items-center gap-1.5 cursor-pointer" onclick="if(window.bookingFp) window.bookingFp.open();">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Calendar Dropdown</span>
                        </span>
                    </label>
                    <div class="relative group cursor-pointer" onclick="if(window.bookingFp) window.bookingFp.open();">
                        <input type="text" id="session-date" readonly
                               placeholder="Click to open calendar & pick a date..." 
                               class="w-full px-5 py-4 pl-12 pr-12 bg-[#0d131f] border border-gray-800 hover:border-[#00D4AA]/50 group-hover:border-[#00D4AA]/60 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00D4AA] text-base sm:text-lg font-medium cursor-pointer transition-all duration-200 shadow-inner">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#00D4AA] pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-[#00D4AA] transition-colors pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Time Slots -->
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Available Time Slots
                    </label>
                    <div id="time-slots-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-8">
                        <div class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-400">Please select a date first</p>
                        </div>
                    </div>
                </div>

                <!-- Availability Info -->
                <div id="availability-info" class="bg-[#0d131f] border border-gray-800 rounded-xl p-6">
                    <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        Weekly Availability
                    </h4>
                    <div id="availability-schedule" class="text-sm text-gray-300">
                        <div class="flex items-center gap-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-2 border-[#00D4AA] border-t-transparent"></div>
                            Loading availability...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    console.log('Booking BASE_PATH detected as:', window.BASE_PATH);

    (function() {
        'use strict';

        let expertData = null;
        let selectedDate = null;
        let selectedTime = null;

        // Get expert ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const expertId = urlParams.get('expert_id');

        if (!expertId) {
            alert('No expert selected');
            window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=browse-experts`;
            return;
        }

        let fpInstance = null;

        function initCalendarPicker() {
            if (typeof flatpickr !== 'undefined') {
                fpInstance = flatpickr("#session-date", {
                    minDate: "today",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "l, F j, Y",
                    altInputClass: "w-full px-5 py-4 pl-12 pr-12 bg-[#0d131f] border border-gray-800 hover:border-[#00D4AA]/50 group-hover:border-[#00D4AA]/60 text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-[#00D4AA] text-base sm:text-lg font-semibold cursor-pointer transition-all duration-200 shadow-inner",
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr) {
                        selectedDate = dateStr;
                        selectedTime = null;
                        renderTimeSlots();
                        updateSelectedDateTime();
                    }
                });
                window.bookingFp = fpInstance;
            }
        }

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

        // Load expert and availability data
        async function loadExpertData() {
            try {
                console.log('Loading booking data from:', `${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
                const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
                console.log('Booking response status:', response.status);
                console.log('Booking response ok:', response.ok);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Booking result:', result);

                if (!result.success) {
                    alert(result.message || 'Failed to load expert data');
                    window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=browse-experts`;
                    return;
                }

                expertData = result.data;
                renderExpertInfo();
                renderAvailabilitySchedule();
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load expert data');
            }
        }

        function formatTime12h(timeStr) {
            if (!timeStr) return '';
            const parts = timeStr.split(':');
            let hours = parseInt(parts[0], 10);
            const minutes = parts[1] || '00';
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            return `${hours}:${minutes} ${ampm}`;
        }

        function renderExpertInfo() {
            document.getElementById('expert-name').textContent = expertData.name || 'Expert';
            document.getElementById('expert-title').textContent = expertData.professional_title || 'Professional';
            
            const score = Math.round(Number(expertData.overall_score || expertData.trust_score || 85));
            const band = expertData.band_name || (score >= 90 ? 'Sovereign' : score >= 75 ? 'Established' : score >= 60 ? 'Verified' : 'Emerging');
            
            const bandEl = document.getElementById('expert-trust-band');
            const scoreEl = document.getElementById('expert-trust-score');
            if (bandEl) bandEl.textContent = band;
            if (scoreEl) scoreEl.textContent = `| ${score}% Trust`;
            
            const hourlyRate = Number(expertData.hourly_rate) || 0;
            document.getElementById('session-price').textContent = `₹${hourlyRate}`;
            
            const initials = window.getInitials ? window.getInitials(expertData.name) : (expertData.name ? expertData.name.substring(0, 2).toUpperCase() : 'EX');
            const hasPhoto = expertData.profile_photo && expertData.profile_photo.trim() !== '' && expertData.profile_photo !== 'null';
            const photoContainer = document.getElementById('expert-photo');
            if (hasPhoto) {
                photoContainer.innerHTML = `
                    <img src="${resolveImagePath(expertData.profile_photo)}" alt="${expertData.name}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="hidden w-full h-full items-center justify-center font-black text-xl text-[#00D4AA] bg-gradient-to-br from-[#0c1222] to-[#131b2e] border border-[#00D4AA]/30">
                        ${initials}
                    </div>
                `;
            } else {
                photoContainer.innerHTML = `
                    <div class="w-full h-full flex items-center justify-center font-black text-xl text-[#00D4AA] bg-gradient-to-br from-[#0c1222] to-[#131b2e] border border-[#00D4AA]/30">
                        ${initials}
                    </div>
                `;
            }
        }

        function renderAvailabilitySchedule() {
            const container = document.getElementById('availability-schedule');
            // Database uses 0=Monday, 6=Sunday
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            if (!expertData.availability || expertData.availability.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-sm">No fixed availability set. Please select a date above to check time slots.</p>';
                return;
            }

            const schedule = {};
            expertData.availability.forEach(slot => {
                const day = days[parseInt(slot.day_of_week)];
                if (!schedule[day]) {
                    schedule[day] = [];
                }
                schedule[day].push(`${slot.start_time} - ${slot.end_time}`);
            });

            const html = Object.entries(schedule).map(([day, times]) => {
                const badges = times.map(t => {
                    const [start, end] = t.split(' - ');
                    return `<span class="inline-flex items-center px-3 py-1 rounded-lg bg-[#00D4AA]/10 border border-[#00D4AA]/30 text-[#00D4AA] font-mono text-xs font-semibold">${formatTime12h(start)} – ${formatTime12h(end)}</span>`;
                }).join(' ');

                return `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between py-3 border-b border-gray-800/80 last:border-0 gap-2">
                    <span class="font-bold text-white text-sm tracking-wide">${day}</span>
                    <div class="flex flex-wrap gap-1.5">${badges}</div>
                </div>`;
            }).join('');
            
            container.innerHTML = html;
        }

        function getAvailableTimeSlotsForDate(date) {
            // JavaScript getDay() returns 0=Sunday, 6=Saturday
            // Database uses 0=Monday, 6=Sunday
            let dayOfWeek = new Date(date).getDay();
            dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Convert to database format
            const availability = expertData.availability.filter(slot => parseInt(slot.day_of_week) === dayOfWeek);
            
            const timeSlots = [];
            availability.forEach(slot => {
                const [startHour, startMin] = slot.start_time.split(':').map(Number);
                const [endHour, endMin] = slot.end_time.split(':').map(Number);
                
                // Generate only 1-hour slots (every hour, not half-hour)
                for (let hour = startHour; hour < endHour; hour++) {
                    const timeSlot = `${String(hour).padStart(2, '0')}:00`;
                    
                    // Check if this slot is already booked or overlaps with a booking
                    const slotDateTime = new Date(`${date} ${timeSlot}`);
                    const slotEndTime = new Date(slotDateTime.getTime() + 60 * 60 * 1000); // Add 1 hour
                    
                    let isBooked = false;
                    if (expertData.booked_slots) {
                        isBooked = expertData.booked_slots.some(booking => {
                            const bookingStart = new Date(booking.session_datetime);
                            const bookingEnd = new Date(bookingStart.getTime() + booking.duration_minutes * 60 * 1000);
                            
                            // Check if times overlap
                            return (slotDateTime < bookingEnd && slotEndTime > bookingStart);
                        });
                    }
                    
                    // Only add slot if not booked
                    if (!isBooked) {
                        timeSlots.push(timeSlot);
                    }
                }
            });
            
            return timeSlots;
        }

        function renderTimeSlots() {
            const container = document.getElementById('time-slots-container');
            
            if (!selectedDate) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500">Please select a date first</p>
                    </div>`;
                return;
            }

            const slots = getAvailableTimeSlotsForDate(selectedDate);
            
            if (slots.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-red-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 font-medium">No slots available for this date</p>
                        <p class="text-gray-400 text-sm mt-1">Please try another day</p>
                    </div>`;
                return;
            }

            container.innerHTML = slots.map(time => `
                <button class="time-slot-btn group px-4 py-3 border border-gray-800 bg-[#0d131f] rounded-xl hover:border-[#00D4AA] hover:bg-[#131b2e] transition duration-200 text-sm font-semibold text-gray-300 hover:text-white hover:shadow-md transform hover:-translate-y-0.5"
                        data-time="${time}">
                    <div class="flex items-center justify-center gap-1">
                        <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ${time}
                    </div>
                </button>
            `).join('');

            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot-btn').forEach(b => {
                        b.classList.remove('border-[#00D4AA]', 'bg-[#00D4AA]/10', 'text-[#00D4AA]');
                        b.classList.add('border-gray-800', 'text-gray-300');
                    });
                    this.classList.remove('border-gray-800', 'text-gray-300');
                    this.classList.add('border-[#00D4AA]', 'bg-[#00D4AA]/10', 'text-[#00D4AA]');
                    
                    selectedTime = this.dataset.time;
                    updateSelectedDateTime();
                });
            });
        }

        function updateSelectedDateTime() {
            const container = document.getElementById('selected-datetime');
            const confirmBtn = document.getElementById('confirm-booking-btn');
            
            if (selectedDate && selectedTime) {
                const date = new Date(selectedDate);
                const formattedDate = date.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
                container.innerHTML = `
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-bold text-emerald-400">Time Selected</span>
                    </div>
                    <div class="font-semibold text-white text-lg">${formattedDate}</div>
                    <div class="text-[#00D4AA] font-bold text-xl mt-1">${selectedTime}</div>
                `;
                confirmBtn.disabled = false;
            } else {
                container.innerHTML = `
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Please select a date and time
                    </div>
                `;
                confirmBtn.disabled = true;
            }
        }

        // Confirm Booking CTA
        document.getElementById('confirm-booking-btn').addEventListener('click', function() {
            if (!selectedDate || !selectedTime) {
                alert('Please select a date and time');
                return;
            }

            const sessionDateTime = `${selectedDate} ${selectedTime}:00`;
            const hourlyRate = Number(expertData.hourly_rate) || 0;
            
            // Ensure the payment page uses the correct base path
            window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=payments&expert_id=${expertId}&datetime=${encodeURIComponent(sessionDateTime)}&amount=${hourlyRate}`;
        });

        // Initialize Flatpickr and load expert telemetry
        initCalendarPicker();
        loadExpertData();
    })();
</script>
<?php require_once 'includes/footer.php'; ?>
