<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/admin-auth-check.php';

$page_title = "KYC Verification - Admin Panel - Nexpert.ai";
$panel_type = "admin";
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/admin-sidebar.php';
?>

<div class="flex-1 p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">KYC Verification Management</h1>
        <p class="text-gray-600">Review and approve expert identity verification requests</p>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <div class="flex space-x-4">
            <button onclick="filterKYC('all')" id="filter-all" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition">
                All Requests
            </button>
            <button onclick="filterKYC('pending')" id="filter-pending" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Pending Review
            </button>
            <button onclick="filterKYC('approved')" id="filter-approved" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Approved
            </button>
            <button onclick="filterKYC('rejected')" id="filter-rejected" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Rejected
            </button>
        </div>
    </div>

    <!-- KYC Requests Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expert Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="kycTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Data will be loaded via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- KYC Details Modal -->
<div id="kycModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">KYC Details</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="kycModalContent" class="p-6">
            <!-- Content loaded dynamically -->
        </div>

        <div class="sticky bottom-0 bg-gray-50 px-6 py-4 flex justify-end space-x-4 border-t">
            <button onclick="rejectKYC()" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                Reject
            </button>
            <button onclick="approveKYC()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                Approve
            </button>
            <button onclick="closeModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
                Close
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo $BASE_PATH; ?>/admin-panel/js/admin-api.js"></script>
<script>
// Set BASE_PATH globally
window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

let currentKYCId = null;
let currentFilter = 'all';

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load KYC data
async function loadKYC(filter = 'all') {
    const tbody = document.getElementById('kycTableBody');
    try {
        const url = `${window.BASE_PATH}/admin-panel/apis/admin/kyc.php` + (filter !== 'all' ? `?status=${filter}` : '');
        const response = await window.AdminAPI.fetch(url);
        const text = await response.text();
        console.log('API Response Text:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-500">
                <div class="font-bold mb-2">JSON Parse Error</div>
                <div class="text-sm whitespace-pre-wrap">${escapeHtml(text.substring(0, 500))}</div>
            </td></tr>`;
            return;
        }
        
        console.log('Parsed Data:', data);
        
        if (data.success) {
            displayKYC(data.data);
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-500">
                <div class="font-bold mb-2">API Error</div>
                <div>${escapeHtml(data.message || 'Unknown error')}</div>
            </td></tr>`;
        }
    } catch (error) {
        console.error('Error:', error);
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-500">
            <div class="font-bold mb-2">Network Error</div>
            <div>${escapeHtml(error.message)}</div>
        </td></tr>`;
    }
}

// Display KYC data in table
function displayKYC(data) {
    const tbody = document.getElementById('kycTableBody');
    
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No experts found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.map(expert => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">${escapeHtml(expert.full_name || 'N/A')}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900">${escapeHtml(expert.email || 'N/A')}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-600">${expert.created_at ? new Date(expert.created_at).toLocaleDateString() : 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                    ${expert.verification_status === 'approved' ? 'bg-green-100 text-green-800' : ''}
                    ${expert.verification_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''}
                    ${expert.verification_status === 'rejected' ? 'bg-red-100 text-red-800' : ''}
                ">
                    ${escapeHtml((expert.verification_status || 'pending').charAt(0).toUpperCase() + (expert.verification_status || 'pending').slice(1))}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button onclick="viewKYC(${expert.user_id})" class="text-primary hover:text-secondary font-semibold">View Details</button>
            </td>
        </tr>
    `).join('');
}

// View expert details
async function viewKYC(expertId) {
    currentKYCId = expertId;
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/kyc.php?expert_id=${expertId}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const expert = data.data;
            const verticals = Array.isArray(expert.expertise_verticals) ? expert.expertise_verticals : [];
            
            const statusColor = expert.verification_status === 'approved' ? 'green' : 
                               expert.verification_status === 'rejected' ? 'red' : 'yellow';
            
            document.getElementById('kycModalContent').innerHTML = `
                <div class="space-y-6">
                    <!-- KYC Verification Status -->
                    <div class="bg-${statusColor}-50 border-2 border-${statusColor}-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Verification Status</h3>
                                <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full bg-${statusColor}-100 text-${statusColor}-800">
                                    ${escapeHtml((expert.verification_status || 'pending').toUpperCase())}
                                </span>
                            </div>
                            ${expert.verified_at ? `
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Verified On</p>
                                <p class="font-semibold text-gray-900">${new Date(expert.verified_at).toLocaleDateString()}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Expert Identity Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Identity Information</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Legal Name</p>
                                <p class="font-semibold text-gray-900">${escapeHtml(expert.full_legal_name || expert.full_name || 'N/A')}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Date of Birth</p>
                                <p class="font-semibold text-gray-900">${expert.date_of_birth ? new Date(expert.date_of_birth).toLocaleDateString() : 'N/A'}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Nationality</p>
                                <p class="font-semibold text-gray-900">${escapeHtml(expert.nationality || 'N/A')}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Gender</p>
                                <p class="font-semibold text-gray-900">${escapeHtml(expert.gender || 'N/A')}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Email</p>
                                <p class="font-semibold text-gray-900">${escapeHtml(expert.email || 'N/A')}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Phone</p>
                                <p class="font-semibold text-gray-900">${escapeHtml(expert.phone || 'N/A')}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                            <p class="text-gray-900">${escapeHtml(expert.address_line1 || 'Address not provided')}</p>
                            ${expert.address_line2 ? `<p class="text-gray-900">${escapeHtml(expert.address_line2)}</p>` : ''}
                            <p class="text-gray-900">${escapeHtml(expert.city || '')}, ${escapeHtml(expert.state || '')} ${escapeHtml(expert.postal_code || '')}</p>
                            <p class="text-gray-900">${escapeHtml(expert.country || '')}</p>
                        </div>
                    </div>

                    <!-- KYC Documents -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📄 KYC Documents</h3>
                        ${expert.id_document_path ? `
                        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-1">${escapeHtml(expert.id_document_type || 'Government ID')} Document</p>
                                    <p class="text-xs text-gray-500">ID Number: ${escapeHtml(expert.id_number || 'Not provided')}</p>
                                </div>
                                <a href="${escapeHtml(expert.id_document_path)}" target="_blank" rel="noopener noreferrer" 
                                   class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition">
                                    View Document
                                </a>
                            </div>
                        </div>
                        ` : `
                        <div class="bg-gray-50 p-4 rounded-lg text-center text-gray-500">
                            <p>No KYC documents uploaded yet</p>
                        </div>
                        `}
                    </div>

                    <!-- Professional Qualifications -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Qualifications</h3>
                        <div class="space-y-3">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Expertise Areas</p>
                                <p class="text-gray-900">${verticals.length > 0 ? verticals.join(', ') : 'Not specified'}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Years of Experience</p>
                                <p class="text-gray-900">${expert.experience_years || 'Not specified'}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Credentials</p>
                                <p class="text-gray-900">${escapeHtml(expert.credentials || 'Not specified')}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Links for Verification -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Links</h3>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">LinkedIn Profile</p>
                                ${expert.linkedin_url ? `<a href="${escapeHtml(expert.linkedin_url)}" target="_blank" class="text-primary hover:underline break-all">${escapeHtml(expert.linkedin_url)}</a>` : '<p class="text-gray-400">Not provided</p>'}
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Website</p>
                                ${expert.website_url ? `<a href="${escapeHtml(expert.website_url)}" target="_blank" class="text-primary hover:underline break-all">${escapeHtml(expert.website_url)}</a>` : '<p class="text-gray-400">Not provided</p>'}
                            </div>
                        </div>
                    </div>

                    <!-- Admin Review Notes -->
                    ${expert.admin_notes ? `
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Admin Review Notes</h3>
                        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                            <p class="text-gray-900">${escapeHtml(expert.admin_notes)}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('kycModal').classList.remove('hidden');
            document.getElementById('kycModal').classList.add('flex');
        } else {
            console.error('API Error:', data.message);
            alert('Error: ' + (data.message || 'Failed to load expert details'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading expert details');
    }
}

// Approve expert
async function approveKYC() {
    if (!currentKYCId) return;
    
    const notes = prompt('Add review notes (optional):');
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/kyc.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                expert_id: currentKYCId,
                status: 'approved',
                notes: notes
            })
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Expert approved successfully!');
            closeModal();
            loadKYC(currentFilter);
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error approving expert');
    }
}

// Reject expert
async function rejectKYC() {
    if (!currentKYCId) return;
    
    const reason = prompt('Enter rejection reason (required):');
    
    if (!reason) {
        alert('Please provide a rejection reason');
        return;
    }
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/kyc.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                expert_id: currentKYCId,
                status: 'rejected',
                notes: reason
            })
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Expert rejected successfully!');
            closeModal();
            loadKYC(currentFilter);
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error rejecting expert');
    }
}

// Close modal
function closeModal() {
    document.getElementById('kycModal').classList.add('hidden');
    document.getElementById('kycModal').classList.remove('flex');
    currentKYCId = null;
}

// Filter KYC
function filterKYC(status) {
    currentFilter = status;
    
    // Update button states
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        btn.classList.remove('bg-primary', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    document.getElementById(`filter-${status}`).classList.remove('bg-gray-200', 'text-gray-700');
    document.getElementById(`filter-${status}`).classList.add('bg-primary', 'text-white');
    
    loadKYC(status);
}

// Load data on page load
loadKYC();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/footer.php'; ?>
