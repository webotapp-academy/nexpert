<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-auth-check.php';

$page_title = "Expert Management - Admin";
$panel_type = "admin";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Expert Management</h1>
        <p class="text-gray-600 mt-1">Verify and manage expert profiles</p>
    </div>


    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center space-x-4">
                <select id="verification-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Verification Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Account Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
                <button onclick="loadExperts()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-600">Apply Filters</button>
            </div>
        </div>

        <!-- Experts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expert</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earnings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="experts-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading experts...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="verification-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Expert Profile</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modal-content"></div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Expert Profile</h3>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="edit-modal-content"></div>
        <div class="mt-6 flex space-x-4">
            <button onclick="saveExpertProfile()" class="flex-1 bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-600">Save Changes</button>
            <button onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">Cancel</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Set BASE_PATH globally
window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function loadExperts() {
    const verificationStatus = document.getElementById('verification-filter').value;
    const accountStatus = document.getElementById('status-filter').value;
    
    let url = `${window.BASE_PATH}/admin-panel/apis/admin/experts.php?`;
    if (verificationStatus) url += `verification_status=${verificationStatus}&`;
    if (accountStatus) url += `status=${accountStatus}`;
    
    try {
        const response = await window.AdminAPI.fetch(url);
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success) {
            if (data.experts && data.experts.length > 0) {
                const tableHtml = data.experts.map(expert => {
                    const profilePhoto = expert.profile_photo ? escapeHtml(expert.profile_photo) : '';
                    const kycStatus = expert.verification_status || 'pending';
                    
                    return `
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                ${profilePhoto ? 
                                    `<img src="${profilePhoto}" alt="${escapeHtml(expert.full_name || 'Expert')}" class="w-10 h-10 rounded-full mr-3 object-cover">` :
                                    `<div class="w-10 h-10 bg-gray-200 rounded-full mr-3 flex items-center justify-center"><span class="text-gray-500 text-sm">${escapeHtml((expert.full_name || 'E')[0].toUpperCase())}</span></div>`
                                }
                                <div>
                                    <p class="font-medium text-gray-900">${escapeHtml(expert.full_name || expert.email)}</p>
                                    <p class="text-sm text-gray-600">${escapeHtml((expert.expertise_verticals || []).slice(0, 2).join(', ') || 'No specialties')}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">${escapeHtml(expert.email)}</td>
                        <td class="px-6 py-4">
                            <a href="?panel=admin&page=kyc-verification&expert_id=${expert.user_id}" class="px-2 py-1 text-xs rounded-full hover:opacity-75 transition inline-block ${
                                kycStatus === 'verified' ? 'bg-green-100 text-green-800' :
                                kycStatus === 'rejected' ? 'bg-red-100 text-red-800' :
                                'bg-yellow-100 text-yellow-800'
                            }">${escapeHtml(kycStatus)}</a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full ${
                                expert.account_status === 'active' ? 'bg-green-100 text-green-800' :
                                'bg-gray-100 text-gray-800'
                            }">${escapeHtml(expert.account_status)}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">${expert.total_bookings || 0}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">₹${(expert.total_earnings || 0).toLocaleString()}</td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-3">
                                <button onclick="viewExpert(${expert.user_id})" class="text-blue-600 hover:underline text-sm">View</button>
                                <button onclick="editExpert(${expert.user_id})" class="text-green-600 hover:underline text-sm">Edit</button>
                                <button onclick="deleteExpert(${expert.user_id})" class="text-red-600 hover:underline text-sm">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
                }).join('');
                
                document.getElementById('experts-table').innerHTML = tableHtml;
            } else {
                document.getElementById('experts-table').innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No experts found</td></tr>';
            }
        } else {
            console.error('API Error:', data.message);
            document.getElementById('experts-table').innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Error: ${escapeHtml(data.message || 'Failed to load experts')}</td></tr>`;
        }
    } catch (error) {
        console.error('Error loading experts:', error);
        document.getElementById('experts-table').innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load experts. Please try again.</td></tr>';
    }
}

function isValidHttpUrl(url) {
    try {
        const parsedUrl = new URL(url, window.location.origin);
        return parsedUrl.protocol === 'http:' || parsedUrl.protocol === 'https:';
    } catch {
        return false;
    }
}

async function viewExpert(expertId) {
    currentExpertId = expertId;
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/kyc.php?expert_id=${expertId}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const expert = data.data;
            const verticals = Array.isArray(expert.expertise_verticals) ? expert.expertise_verticals : [];
            
            const statusColor = expert.verification_status === 'approved' ? 'green' : 
                               expert.verification_status === 'rejected' ? 'red' : 'yellow';
            
            const modalContent = `
                <div class="space-y-6 max-h-[60vh] overflow-y-auto">
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
            
            document.getElementById('modal-content').innerHTML = modalContent;
            document.getElementById('verification-modal').classList.remove('hidden');
        } else {
            console.error('API Error:', data.message);
            alert('Error: ' + (data.message || 'Failed to load expert details'));
        }
    } catch (error) {
        console.error('Error viewing expert:', error);
        alert('Error loading expert details');
    }
}

async function updateVerification(status) {
    if (!currentExpertId) return;
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/experts.php?action=verify`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                expert_id: currentExpertId,
                verification_status: status,
                admin_notes: ''
            })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Verification status updated successfully');
            closeModal();
            loadExperts();
        }
    } catch (error) {
        console.error('Error updating verification:', error);
    }
}

function closeModal() {
    document.getElementById('verification-modal').classList.add('hidden');
    currentExpertId = null;
}

async function editExpert(expertId) {
    currentExpertId = expertId;
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/experts.php?expert_id=${expertId}`);
        const data = await response.json();
        
        if (data.success) {
            const expert = data.expert;
            const verticals = expert.expertise_verticals || [];
            
            const editForm = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="edit-full-name" value="${escapeHtml(expert.full_name || '')}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
                        <input type="number" id="edit-years-experience" value="${expert.years_of_experience || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Short Bio (Max 160 characters)</label>
                        <textarea id="edit-bio-short" rows="3" maxlength="160" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">${escapeHtml(expert.bio_short || '')}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Bio</label>
                        <textarea id="edit-bio-full" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">${escapeHtml(expert.bio_full || '')}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expertise Areas (comma-separated)</label>
                        <input type="text" id="edit-expertise" value="${verticals.join(', ')}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">e.g., Web Development, AI/ML, DevOps</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Credentials</label>
                        <textarea id="edit-credentials" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">${escapeHtml(expert.credentials || '')}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                        <input type="url" id="edit-linkedin" value="${escapeHtml(expert.linkedin_url || '')}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                        <input type="url" id="edit-website" value="${escapeHtml(expert.website_url || '')}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            `;
            
            document.getElementById('edit-modal-content').innerHTML = editForm;
            document.getElementById('edit-modal').classList.remove('hidden');
        } else {
            console.error('API Error:', data.message);
            alert('Error: ' + (data.message || 'Failed to load expert data'));
        }
    } catch (error) {
        console.error('Error loading expert for edit:', error);
        alert('Error loading expert data');
    }
}

async function saveExpertProfile() {
    if (!currentExpertId) return;
    
    const fullName = document.getElementById('edit-full-name').value.trim();
    const bioShort = document.getElementById('edit-bio-short').value.trim();
    const bioFull = document.getElementById('edit-bio-full').value.trim();
    const expertiseRaw = document.getElementById('edit-expertise').value.trim();
    const credentials = document.getElementById('edit-credentials').value.trim();
    const yearsExperience = document.getElementById('edit-years-experience').value;
    const linkedinUrl = document.getElementById('edit-linkedin').value.trim();
    const websiteUrl = document.getElementById('edit-website').value.trim();
    
    const expertiseVerticals = expertiseRaw.split(',').map(v => v.trim()).filter(v => v);
    
    if (!fullName) {
        alert('Full name is required');
        return;
    }
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/kyc.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                expert_id: currentExpertId,
                update_profile: true,
                full_name: fullName,
                bio_short: bioShort,
                bio_full: bioFull,
                expertise_verticals: expertiseVerticals,
                credentials: credentials,
                years_of_experience: yearsExperience || null,
                linkedin_url: linkedinUrl,
                website_url: websiteUrl
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Expert profile updated successfully!');
            closeEditModal();
            loadExperts();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error saving expert profile:', error);
        alert('Error saving expert profile');
    }
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
    currentExpertId = null;
}

async function deleteExpert(expertId) {
    if (!confirm('Are you sure you want to delete this expert? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/experts.php`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ expert_id: expertId })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Expert deleted successfully');
            loadExperts();
        } else {
            alert('Error deleting expert: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting expert:', error);
        alert('Error deleting expert. Please try again.');
    }
}

// Load experts on page load
loadExperts();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
</div> <!-- Close admin-sidebar main content div -->
