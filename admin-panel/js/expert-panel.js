// Expert Panel JavaScript - Database Integration
// For demo purposes, using user_id = 1 (will be replaced with session management later)

const API_BASE = (typeof BASE_PATH !== 'undefined' ? BASE_PATH : '') + '/admin-panel/apis/expert';
const DEMO_USER_ID = 1;  // Demo expert user ID

// Utility function for API calls
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(endpoint, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: error.message };
    }
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Expert Profile Functions
async function saveExpertProfile(profileData) {
    profileData.user_id = DEMO_USER_ID;
    const result = await apiCall(`${API_BASE}/profile.php`, 'POST', profileData);
    
    if (result.success) {
        showToast('Profile saved successfully!');
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

async function loadExpertProfile() {
    const result = await apiCall(`${API_BASE}/profile.php?user_id=${DEMO_USER_ID}`);
    return result;
}

// Pricing Functions
async function savePricing(pricingData) {
    pricingData.expert_id = DEMO_USER_ID;
    const result = await apiCall(`${API_BASE}/pricing.php`, 'POST', pricingData);
    
    if (result.success) {
        showToast('Pricing saved successfully!');
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

// Availability Functions
async function saveAvailability(slots) {
    const data = {
        expert_id: DEMO_USER_ID,
        slots
    };
    const result = await apiCall(`${API_BASE}/availability.php`, 'POST', data);
    
    if (result.success) {
        showToast('Availability saved successfully!');
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

// Booking Functions
async function loadBookings(status = null) {
    const url = status 
        ? `${API_BASE}/bookings.php?expert_id=${DEMO_USER_ID}&status=${status}`
        : `${API_BASE}/bookings.php?expert_id=${DEMO_USER_ID}`;
    return await apiCall(url);
}

async function updateBookingStatus(bookingId, status, reason = null, newDatetime = null) {
    const data = {
        booking_id: bookingId,
        status,
        reason,
        new_datetime: newDatetime
    };
    const result = await apiCall(`${API_BASE}/bookings.php`, 'PUT', data);
    
    if (result.success) {
        showToast(`Booking ${status} successfully!`);
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

// Dashboard Functions
async function loadDashboard() {
    return await apiCall(`${API_BASE}/dashboard.php?expert_id=${DEMO_USER_ID}`);
}

// Earnings Functions
async function loadEarnings(period = 'all') {
    return await apiCall(`${API_BASE}/earnings.php?expert_id=${DEMO_USER_ID}&period=${period}`);
}

async function requestPayout(amount, currency = 'USD') {
    const data = {
        expert_id: DEMO_USER_ID,
        amount,
        currency
    };
    const result = await apiCall(`${API_BASE}/earnings.php`, 'POST', data);
    
    if (result.success) {
        showToast('Payout request submitted successfully!');
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

// Learner Management Functions
async function loadLearners() {
    return await apiCall(`${API_BASE}/learners.php?expert_id=${DEMO_USER_ID}`);
}

async function loadLearnerDetails(learnerId) {
    return await apiCall(`${API_BASE}/learners.php?expert_id=${DEMO_USER_ID}&learner_id=${learnerId}`);
}

async function updateLearnerNotes(learnerId, notes) {
    const data = {
        expert_id: DEMO_USER_ID,
        learner_id: learnerId,
        expert_notes: notes
    };
    return await apiCall(`${API_BASE}/learners.php`, 'PUT', data);
}

async function addFollowUpReminder(learnerId, datetime, message) {
    const data = {
        expert_id: DEMO_USER_ID,
        learner_id: learnerId,
        reminder_datetime: datetime,
        message
    };
    return await apiCall(`${API_BASE}/learners.php`, 'POST', data);
}

// Workflow Functions
async function loadWorkflows() {
    return await apiCall(`${API_BASE}/workflows.php?expert_id=${DEMO_USER_ID}`);
}

async function createWorkflow(workflowData) {
    workflowData.expert_id = DEMO_USER_ID;
    const result = await apiCall(`${API_BASE}/workflows.php`, 'POST', workflowData);
    
    if (result.success) {
        showToast('Workflow created successfully!');
    } else {
        showToast(result.message, 'error');
    }
    return result;
}

// Session Functions
async function addSessionNotes(bookingId, notes) {
    const data = { booking_id: bookingId, notes };
    return await apiCall(`${API_BASE}/sessions.php?action=notes`, 'POST', data);
}

async function addSessionResource(bookingId, resourceData) {
    resourceData.booking_id = bookingId;
    resourceData.uploaded_by = DEMO_USER_ID;
    return await apiCall(`${API_BASE}/sessions.php?action=resource`, 'POST', resourceData);
}

async function createAssignment(bookingId, learnerId, assignmentData) {
    assignmentData.booking_id = bookingId;
    assignmentData.expert_id = DEMO_USER_ID;
    assignmentData.learner_id = learnerId;
    return await apiCall(`${API_BASE}/sessions.php?action=assignment`, 'POST', assignmentData);
}

async function completeSession(bookingId, recordingUrl = null) {
    const data = { booking_id: bookingId, recording_url: recordingUrl };
    return await apiCall(`${API_BASE}/sessions.php`, 'PUT', data);
}

// Make functions globally available
window.ExpertPanel = {
    saveExpertProfile,
    loadExpertProfile,
    savePricing,
    saveAvailability,
    loadBookings,
    updateBookingStatus,
    loadDashboard,
    loadEarnings,
    requestPayout,
    loadLearners,
    loadLearnerDetails,
    updateLearnerNotes,
    addFollowUpReminder,
    loadWorkflows,
    createWorkflow,
    addSessionNotes,
    addSessionResource,
    createAssignment,
    completeSession
};
