<?php
require_once 'includes/admin-auth-check.php';

$page_title = "Bookings Management - Admin";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Bookings Management</h1>
        <p class="text-gray-600 mt-1">View and manage all session bookings</p>
    </div>

    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center space-x-4">
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button onclick="loadBookings()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-600">Apply Filters</button>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Learner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expert</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="bookings-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading bookings...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
// Load bookings
async function loadBookings() {
    const status = document.getElementById('status-filter').value;
    
    try {
        let url = '/admin-panel/apis/admin/bookings.php?';
        if (status) url += `status=${status}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const tbody = document.getElementById('bookings-table-body');
            
            if (data.bookings.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No bookings found</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.bookings.map(booking => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#${booking.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${booking.learner_name || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${booking.learner_email || ''}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${booking.expert_name || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${booking.expert_email || ''}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${new Date(booking.session_datetime).toLocaleString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${booking.duration_minutes} min</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${booking.amount || '0'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            booking.status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                            booking.status === 'completed' ? 'bg-green-100 text-green-800' :
                            booking.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                        }">
                            ${booking.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="viewBooking(${booking.id})" class="text-primary hover:text-blue-700">View</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

function viewBooking(id) {
    alert('Viewing booking #' + id + ' - Full details page to be implemented');
}

// Load bookings on page load
loadBookings();
</script>

</div> <!-- Close admin-sidebar main content div -->

<?php require_once 'includes/footer.php'; ?>
