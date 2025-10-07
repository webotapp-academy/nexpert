<?php
$page_title = "Book Session - Nexpert.ai";
$panel_type = "learner";

// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
<div class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Enhanced Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="?panel=learner&page=browse-experts" class="text-gray-600 hover:text-primary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent">
                    Book a Session
                </h1>
            </div>
            <p class="text-gray-600 ml-9">Schedule a personalized session with your expert</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Expert Info Card - Enhanced -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 h-fit border border-gray-100 transform hover:scale-105 transition-transform duration-300">
                <!-- Expert Profile Section -->
                <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-200">
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full opacity-75 group-hover:opacity-100 blur transition duration-300"></div>
                        <div id="expert-photo" class="relative w-24 h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden flex items-center justify-center ring-4 ring-white shadow-lg">
                            <div class="animate-pulse">
                                <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 id="expert-name" class="text-xl font-bold text-gray-900 mb-1">
                            <span class="inline-block animate-pulse bg-gray-200 rounded px-3 py-1">Loading...</span>
                        </h3>
                        <p id="expert-title" class="text-gray-600 text-sm mb-2">
                            <span class="inline-block animate-pulse bg-gray-100 rounded px-2 py-0.5">Loading...</span>
                        </p>
                        <div class="flex items-center gap-2">
                            <div id="expert-rating-stars" class="flex text-yellow-400 text-lg">
                                ☆☆☆☆☆
                            </div>
                            <span id="expert-rating-value" class="text-gray-700 text-sm font-semibold">(0.0)</span>
                        </div>
                    </div>
                </div>
                
                <!-- Session Details Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 mb-6">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Session Details
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Duration:</span>
                            <span class="text-gray-900 font-semibold bg-white px-3 py-1 rounded-lg">60 minutes</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Format:</span>
                            <span class="text-gray-900 font-semibold bg-white px-3 py-1 rounded-lg flex items-center gap-1">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                </svg>
                                Video Call
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t border-blue-200">
                            <span class="text-gray-600 text-sm">Session Fee:</span>
                            <span id="session-price" class="text-2xl font-bold text-primary">₹0</span>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Time Display -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-5 mb-6 border-2 border-green-200">
                    <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Selected Time
                    </h4>
                    <div id="selected-datetime" class="text-gray-600 text-sm">
                        Please select a date and time
                    </div>
                </div>
                
                <!-- Confirm Button -->
                <button id="confirm-booking-btn" disabled class="group relative w-full bg-gradient-to-r from-primary to-blue-600 text-white px-6 py-4 rounded-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-bold text-lg shadow-lg hover:shadow-2xl disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed disabled:transform-none transform hover:-translate-y-1">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Proceed to Payment
                    </span>
                </button>
            </div>

            <!-- Booking Calendar - Enhanced -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1 h-8 bg-gradient-to-b from-primary to-blue-600 rounded-full"></div>
                    <h3 class="text-2xl font-bold text-gray-900">Select Date & Time</h3>
                </div>
                
                <!-- Date Selection -->
                <div class="mb-10">
                    <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Choose a Date
                    </label>
                    <input type="date" id="session-date" 
                           class="w-full px-5 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-primary text-lg transition-all duration-200">
                </div>

                <!-- Time Slots -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Available Time Slots
                    </label>
                    <div id="time-slots-container" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-8">
                        <div class="col-span-full text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-gray-500">Please select a date first</p>
                        </div>
                    </div>
                </div>

                <!-- Availability Info -->
                <div id="availability-info" class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6">
                    <h4 class="text-sm font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        Weekly Availability
                    </h4>
                    <div id="availability-schedule" class="text-sm text-blue-800">
                        <div class="flex items-center gap-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"></div>
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
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

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

        // Set minimum date to today
        const dateInput = document.getElementById('session-date');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;

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
                const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
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

        function renderExpertInfo() {
            document.getElementById('expert-name').textContent = expertData.name || 'Expert';
            document.getElementById('expert-title').textContent = expertData.professional_title || 'Professional';
            
            const rating = Math.max(0, Math.min(5, Math.floor(Number(expertData.avg_rating) || 0)));
            document.getElementById('expert-rating-stars').textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);
            document.getElementById('expert-rating-value').textContent = `(${(Number(expertData.avg_rating) || 0).toFixed(1)})`;
            
            const hourlyRate = Number(expertData.hourly_rate) || 0;
            document.getElementById('session-price').textContent = `₹${hourlyRate}`;
            
            const photoContainer = document.getElementById('expert-photo');
            photoContainer.innerHTML = `<img src="${resolveImagePath(expertData.profile_photo)}" alt="${expertData.name}" class="w-full h-full object-cover">`;
        }

        function renderAvailabilitySchedule() {
            const container = document.getElementById('availability-schedule');
            // Database uses 0=Monday, 6=Sunday
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            if (!expertData.availability || expertData.availability.length === 0) {
                container.innerHTML = '<p class="text-gray-600">No availability set. Please contact the expert.</p>';
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

            const html = Object.entries(schedule).map(([day, times]) => 
                `<div class="flex justify-between py-2 border-b border-blue-100 last:border-0">
                    <span class="font-semibold text-blue-900">${day}:</span>
                    <span class="text-blue-700">${times.join(', ')}</span>
                </div>`
            ).join('');
            
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
                
                for (let hour = startHour; hour < endHour; hour++) {
                    timeSlots.push(`${String(hour).padStart(2, '0')}:00`);
                    if (hour + 0.5 < endHour || (hour + 1 === endHour && endMin >= 30)) {
                        timeSlots.push(`${String(hour).padStart(2, '0')}:30`);
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
                <button class="time-slot-btn group px-4 py-3 border-2 border-gray-200 rounded-xl hover:border-primary hover:bg-blue-50 transition-all duration-200 text-sm font-semibold text-gray-700 hover:text-primary hover:shadow-md transform hover:-translate-y-0.5"
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
                        b.classList.remove('border-primary', 'bg-blue-100', 'text-primary', 'ring-4', 'ring-blue-100');
                        b.classList.add('border-gray-200', 'text-gray-700');
                    });
                    this.classList.remove('border-gray-200', 'text-gray-700');
                    this.classList.add('border-primary', 'bg-blue-100', 'text-primary', 'ring-4', 'ring-blue-100');
                    
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
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-bold text-green-800">Time Selected</span>
                    </div>
                    <div class="font-semibold text-gray-900 text-lg">${formattedDate}</div>
                    <div class="text-primary font-bold text-xl mt-1">${selectedTime}</div>
                `;
                confirmBtn.disabled = false;
            } else {
                container.innerHTML = `
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Please select a date and time
                    </div>
                `;
                confirmBtn.disabled = true;
            }
        }

        // Event listeners
        dateInput.addEventListener('change', function() {
            selectedDate = this.value;
            selectedTime = null;
            renderTimeSlots();
            updateSelectedDateTime();
        });

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

        // Initialize
        loadExpertData();
    })();
</script>
<?php require_once 'includes/footer.php'; ?>
