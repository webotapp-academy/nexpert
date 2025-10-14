<?php
require_once 'includes/admin-auth-check.php';

$page_title = "User Management - Admin";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <p class="text-gray-600 mt-1">Manage all platform users</p>
    </div>


    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center space-x-4">
                <select id="role-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Roles</option>
                    <option value="learner">Learners</option>
                    <option value="expert">Experts</option>
                    <option value="admin">Admins</option>
                </select>
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
                <button onclick="loadUsers()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-600">Apply Filters</button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Loading users...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateIST(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { 
        timeZone: 'Asia/Kolkata',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

async function loadUsers() {
    const role = document.getElementById('role-filter').value;
    const status = document.getElementById('status-filter').value;
    
    let url = '/admin-panel/apis/admin/users.php?';
    if (role) url += `role=${role}&`;
    if (status) url += `status=${status}`;
    
    try {
        const response = await window.AdminAPI.fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const tableHtml = data.users.map(user => `
                <tr>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">${escapeHtml(user.email)}</p>
                            <p class="text-sm text-gray-600">${escapeHtml(user.phone || 'No phone')}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">${escapeHtml(user.role)}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            user.status === 'active' ? 'bg-green-100 text-green-800' :
                            user.status === 'suspended' ? 'bg-red-100 text-red-800' :
                            'bg-gray-100 text-gray-800'
                        }">${escapeHtml(user.status)}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formatDateIST(user.created_at)}</td>
                    <td class="px-6 py-4">
                        <select onchange="updateUserStatus(${user.id}, this.value)" class="text-sm border border-gray-300 rounded px-2 py-1">
                            <option value="">Change Status...</option>
                            <option value="active">Activate</option>
                            <option value="inactive">Deactivate</option>
                            <option value="suspended">Suspend</option>
                        </select>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('users-table').innerHTML = tableHtml || '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

async function updateUserStatus(userId, status) {
    if (!status) return;
    
    try {
        const response = await window.AdminAPI.fetch('/admin-panel/apis/admin/users.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, status: status })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('User status updated successfully');
            loadUsers();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating user status:', error);
        alert('Error updating user status. Please try again.');
    }
}

// Load users on page load
loadUsers();
</script>

<?php require_once 'includes/footer.php'; ?>
</div> <!-- Close admin-sidebar main content div -->
