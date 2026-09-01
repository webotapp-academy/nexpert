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

        <div class="grid lg:grid-cols-3 gap-8 items-start">
            <!-- Left/Center Main Column: Step 1 & Step 2 Sequential Flow -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Step 1: Select Date & Time -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-800/80">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[#00D4AA] text-[#080B10] font-extrabold flex items-center justify-center text-sm shadow-md">1</span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white">Select Date & Time</h3>
                        </div>
                        <span class="text-xs text-gray-400 font-semibold bg-[#0d131f] border border-gray-800 px-3 py-1 rounded-lg">Step 1 of 2</span>
                    </div>
                    
                    <!-- Date Selection -->
                    <div class="mb-8">
                        <label for="session-date" class="block text-sm font-semibold text-gray-300 mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Choose a Date
                            </span>
                            <span class="text-xs text-[#00D4AA] font-semibold bg-[#00D4AA]/10 px-2.5 py-1 rounded-lg border border-[#00D4AA]/30 flex items-center gap-1.5 cursor-pointer hover:bg-[#00D4AA]/20 transition" onclick="if(window.bookingFp) window.bookingFp.open();">
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
                        <label class="block text-sm font-semibold text-gray-300 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Available Time Slots
                        </label>
                        <div id="time-slots-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div class="col-span-full text-center py-10 bg-[#0d131f] border border-gray-800/80 rounded-xl">
                                <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-gray-400 font-medium">Please select a date above to view available time slots</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Confirm Slot & Proceed to Payment (Directly Below Slot Selection) -->
                <div id="booking-step-2-payment" class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-800/80">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[#00D4AA] text-[#080B10] font-extrabold flex items-center justify-center text-sm shadow-md">2</span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white">Confirm & Proceed to Payment</h3>
                        </div>
                        <span class="text-xs text-gray-400 font-semibold bg-[#0d131f] border border-gray-800 px-3 py-1 rounded-lg">Step 2 of 2</span>
                    </div>

                    <!-- Summary Grid -->
                    <div class="grid sm:grid-cols-2 gap-4 mb-6">
                        <!-- Selected Time Display -->
                        <div class="bg-[#0d131f] border border-gray-800/80 rounded-xl p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Selected Appointment
                                </span>
                                <div id="selected-datetime" class="text-gray-300 text-sm">
                                    Please pick a date & time slot above
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-800/80 flex items-center gap-2 text-xs text-gray-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Duration: <strong>60 Minutes</strong> (1-on-1 Video)</span>
                            </div>
                        </div>

                        <!-- Session Fee Box -->
                        <div class="bg-[#0d131f] border border-gray-800/80 rounded-xl p-5 flex flex-col justify-between">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Session Fee
                                </span>
                                <div class="flex items-baseline gap-2">
                                    <span id="session-price" class="text-3xl sm:text-4xl font-black text-[#00D4AA]">₹0</span>
                                    <span class="text-xs text-gray-400 font-medium">/ 60 min</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-800/80 flex items-center gap-1.5 text-xs text-emerald-400">
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>100% Guaranteed Session & Action Items</span>
                            </div>
                        </div>
                    </div>

                    <!-- Proceed to Payment CTA Button -->
                    <button id="confirm-booking-btn" disabled 
                            class="group relative w-full bg-[#00D4AA] text-[#080B10] px-8 py-4 rounded-xl hover:bg-[#00bda0] transition-all duration-300 font-black text-lg shadow-xl hover:shadow-2xl disabled:bg-gray-800 disabled:text-gray-600 disabled:cursor-not-allowed disabled:transform-none transform hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-3">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span id="confirm-booking-text">Proceed to Payment</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right Sidebar: Expert Card & Availability Schedule -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Expert Info Card -->
                <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-6 sm:p-7">
                    <!-- Expert Profile Header -->
                    <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-800">
                        <div class="relative group shrink-0">
                            <div class="absolute -inset-1 bg-gradient-to-r from-[#00D4AA] to-blue-600 rounded-full opacity-75 group-hover:opacity-100 blur transition duration-300"></div>
                            <div id="expert-photo" class="relative w-20 h-20 rounded-full bg-[#0d131f] overflow-hidden flex items-center justify-center ring-4 ring-[#131b2e] shadow-lg">
                                <div class="animate-pulse">
                                    <svg class="w-10 h-10 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 id="expert-name" class="text-lg font-bold text-white mb-1 truncate">
                                <span class="inline-block animate-pulse bg-gray-800 rounded px-3 py-1 text-gray-500">Loading...</span>
                            </h3>
                            <p id="expert-title" class="text-gray-400 text-xs mb-2 truncate">
                                <span class="inline-block animate-pulse bg-gray-800 rounded px-2 py-0.5 text-gray-600">Loading...</span>
                            </p>
                            <div id="expert-trust-badge-container">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#00D4AA]/10 border border-[#00D4AA]/30 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
                                    <span id="expert-trust-band" class="text-xs font-bold text-[#00D4AA] uppercase tracking-wider">Verified</span>
                                    <span id="expert-trust-score" class="text-xs font-semibold text-gray-300">| 85% Trust</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Session Features -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between text-xs text-gray-300 py-1.5 border-b border-gray-800/60">
                            <span class="text-gray-400">Duration</span>
                            <span class="font-bold text-white">60 Minutes</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-300 py-1.5 border-b border-gray-800/60">
                            <span class="text-gray-400">Meeting Type</span>
                            <span class="font-bold text-[#00D4AA]">1-on-1 Video Session</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-300 py-1.5">
                            <span class="text-gray-400">Deliverables</span>
                            <span class="font-bold text-white">Actionable Tasks & Notes</span>
                        </div>
                    </div>

                    <!-- Trust Points -->
                    <div class="bg-[#0d131f] border border-gray-800/80 rounded-xl p-4 text-xs text-gray-400 space-y-2">
                        <div class="flex items-center gap-2 text-gray-300">
                            <svg class="w-4 h-4 text-[#00D4AA] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Verified Mentor Credibility</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-300">
                            <svg class="w-4 h-4 text-[#00D4AA] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Encrypted Video & Telemetry</span>
                        </div>
                    </div>
                </div>

                <!-- Weekly Availability Schedule -->
                <div id="availability-info" class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl p-6 sm:p-7">
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

<!-- Mobile Sticky Payment Bar -->
<div id="mobile-sticky-bar" class="lg:hidden fixed bottom-0 left-0 right-0 bg-[#0d131f]/95 backdrop-blur-md border-t border-gray-800 p-4 z-40 transform translate-y-full transition-transform duration-300 shadow-2xl">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <div>
            <div id="mobile-bar-datetime" class="text-xs text-gray-300 font-medium">Select a slot</div>
            <div id="mobile-bar-price" class="text-lg font-black text-[#00D4AA]">₹0</div>
        </div>
        <button id="mobile-confirm-btn" disabled class="bg-[#00D4AA] text-[#080B10] px-6 py-3 rounded-xl font-black text-sm hover:bg-[#00bda0] transition shadow-lg shrink-0 flex items-center gap-1.5 disabled:bg-gray-800 disabled:text-gray-600 disabled:cursor-not-allowed">
            <span>Proceed to Payment</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
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
            if (/^(https?:\/\/|data:)/.test(imagePath)) {
                return imagePath;
            }
            if (!imagePath) {
                return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
            }
            const normalizedPath = imagePath.replace(/^\/+/, '');
            return `${window.BASE_PATH}/${normalizedPath}`;
        }

        // Load expert and availability data
        async function loadExpertData() {
            try {
                console.log('Loading booking data from:', `${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
                const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();

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
            const mobilePriceEl = document.getElementById('mobile-bar-price');
            if (mobilePriceEl) mobilePriceEl.textContent = `₹${hourlyRate}`;
            
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
                    <span class="font-bold text-white text-xs tracking-wide">${day}</span>
                    <div class="flex flex-wrap gap-1.5">${badges}</div>
                </div>`;
            }).join('');
            
            container.innerHTML = html;
        }

        function getAvailableTimeSlotsForDate(date) {
            let dayOfWeek = new Date(date).getDay();
            dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
            const availability = expertData.availability.filter(slot => parseInt(slot.day_of_week) === dayOfWeek);
            
            const timeSlots = [];
            availability.forEach(slot => {
                const [startHour] = slot.start_time.split(':').map(Number);
                const [endHour] = slot.end_time.split(':').map(Number);
                
                for (let hour = startHour; hour < endHour; hour++) {
                    const timeSlot = `${String(hour).padStart(2, '0')}:00`;
                    const slotDateTime = new Date(`${date} ${timeSlot}`);
                    const slotEndTime = new Date(slotDateTime.getTime() + 60 * 60 * 1000);
                    
                    let isBooked = false;
                    if (expertData.booked_slots) {
                        isBooked = expertData.booked_slots.some(booking => {
                            const bookingStart = new Date(booking.session_datetime);
                            const bookingEnd = new Date(bookingStart.getTime() + booking.duration_minutes * 60 * 1000);
                            return (slotDateTime < bookingEnd && slotEndTime > bookingStart);
                        });
                    }
                    
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
                    <div class="col-span-full text-center py-10 bg-[#0d131f] border border-gray-800/80 rounded-xl">
                        <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-400 font-medium">Please select a date above to view available time slots</p>
                    </div>`;
                return;
            }

            const slots = getAvailableTimeSlotsForDate(selectedDate);
            
            if (slots.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-10 bg-[#0d131f] border border-gray-800/80 rounded-xl">
                        <svg class="w-12 h-12 text-red-400/70 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-300 font-bold">No slots available for this date</p>
                        <p class="text-gray-500 text-xs mt-1">Please pick another date on the calendar</p>
                    </div>`;
                return;
            }

            container.innerHTML = slots.map(time => `
                <button class="time-slot-btn group px-4 py-3.5 border border-gray-800 bg-[#0d131f] rounded-xl hover:border-[#00D4AA] hover:bg-[#131b2e] transition-all duration-200 text-sm font-bold text-gray-300 hover:text-white hover:shadow-lg transform hover:-translate-y-0.5 active:scale-95"
                        data-time="${time}">
                    <div class="flex items-center justify-center gap-1.5 font-mono">
                        <svg class="w-4 h-4 text-[#00D4AA] opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ${formatTime12h(time)}
                    </div>
                </button>
            `).join('');

            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot-btn').forEach(b => {
                        b.classList.remove('border-[#00D4AA]', 'bg-[#00D4AA]/15', 'text-[#00D4AA]', 'ring-2', 'ring-[#00D4AA]/50');
                        b.classList.add('border-gray-800', 'text-gray-300');
                    });
                    this.classList.remove('border-gray-800', 'text-gray-300');
                    this.classList.add('border-[#00D4AA]', 'bg-[#00D4AA]/15', 'text-[#00D4AA]', 'ring-2', 'ring-[#00D4AA]/50');
                    
                    selectedTime = this.dataset.time;
                    updateSelectedDateTime();

                    // Smoothly scroll down to Step 2 so payment CTA is immediately prominent
                    const step2 = document.getElementById('booking-step-2-payment');
                    if (step2) {
                        step2.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            });
        }

        function updateSelectedDateTime() {
            const container = document.getElementById('selected-datetime');
            const confirmBtn = document.getElementById('confirm-booking-btn');
            const mobileBar = document.getElementById('mobile-sticky-bar');
            const mobileBarDate = document.getElementById('mobile-bar-datetime');
            const mobileConfirmBtn = document.getElementById('mobile-confirm-btn');
            
            if (selectedDate && selectedTime) {
                const date = new Date(selectedDate);
                const formattedDate = date.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
                const time12 = formatTime12h(selectedTime);

                container.innerHTML = `
                    <div class="flex items-center gap-1.5 text-emerald-400 font-bold text-xs mb-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Slot Selected</span>
                    </div>
                    <div class="font-bold text-white text-base">${formattedDate}</div>
                    <div class="text-[#00D4AA] font-black text-lg mt-0.5">${time12} IST</div>
                `;
                confirmBtn.disabled = false;

                if (mobileBar && mobileBarDate && mobileConfirmBtn) {
                    mobileBarDate.textContent = `${formattedDate} • ${time12}`;
                    mobileBar.classList.remove('translate-y-full');
                    mobileConfirmBtn.disabled = false;
                }
            } else {
                container.innerHTML = `
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Please select a date and time above</span>
                    </div>
                `;
                confirmBtn.disabled = true;

                if (mobileBar && mobileConfirmBtn) {
                    mobileBar.classList.add('translate-y-full');
                    mobileConfirmBtn.disabled = true;
                }
            }
        }

        function proceedToPayment() {
            if (!selectedDate || !selectedTime) {
                alert('Please select a date and time slot first');
                return;
            }

            const sessionDateTime = `${selectedDate} ${selectedTime}:00`;
            const hourlyRate = Number(expertData.hourly_rate) || 0;
            
            window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=payments&expert_id=${expertId}&datetime=${encodeURIComponent(sessionDateTime)}&amount=${hourlyRate}`;
        }

        // Confirm Booking CTA handlers
        document.getElementById('confirm-booking-btn').addEventListener('click', proceedToPayment);
        const mobileConfirmBtn = document.getElementById('mobile-confirm-btn');
        if (mobileConfirmBtn) {
            mobileConfirmBtn.addEventListener('click', proceedToPayment);
        }

        // Initialize Flatpickr and load expert telemetry
        initCalendarPicker();
        loadExpertData();
    })();
</script>
<?php require_once 'includes/footer.php'; ?>
