<?php
require_once 'includes/admin-auth-check.php';

$page_title = "Payments Management - Admin";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Payments & Transactions</h1>
        <p class="text-gray-600 mt-1">Monitor and manage all platform transactions</p>
    </div>

    <div class="p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900" id="total-revenue">$0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Platform Commission</p>
                <p class="text-3xl font-bold text-gray-900" id="total-commission">$0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Pending Payments</p>
                <p class="text-3xl font-bold text-gray-900" id="pending-amount">$0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Total Refunds</p>
                <p class="text-3xl font-bold text-gray-900" id="total-refunds">$0</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex items-center space-x-4">
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
                <button onclick="loadPayments()" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-600">Apply Filters</button>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Learner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expert</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="payments-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Loading payments...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
// Load payments
async function loadPayments() {
    const status = document.getElementById('status-filter').value;
    
    try {
        let url = '/admin-panel/apis/admin/payments.php?';
        if (status) url += `status=${status}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            // Update stats
            if (data.stats) {
                document.getElementById('total-revenue').textContent = '$' + (parseFloat(data.stats.total_revenue) || 0).toFixed(2);
                document.getElementById('total-commission').textContent = '$' + (parseFloat(data.stats.total_commission) || 0).toFixed(2);
                document.getElementById('pending-amount').textContent = '$' + (parseFloat(data.stats.pending_amount) || 0).toFixed(2);
                document.getElementById('total-refunds').textContent = '$' + (parseFloat(data.stats.total_refunds) || 0).toFixed(2);
            }
            
            const tbody = document.getElementById('payments-table-body');
            
            if (data.payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">No payments found</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.payments.map(payment => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#${payment.id}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${payment.learner_name || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${payment.learner_email || ''}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${payment.expert_name || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${payment.expert_email || ''}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(payment.amount).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${parseFloat(payment.commission_amount || 0).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            payment.status === 'success' ? 'bg-green-100 text-green-800' :
                            payment.status === 'refunded' ? 'bg-red-100 text-red-800' :
                            payment.status === 'failed' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                        }">
                            ${payment.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${new Date(payment.created_at).toLocaleDateString()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="viewPayment(${payment.id})" class="text-primary hover:text-blue-700">View</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading payments:', error);
    }
}

function viewPayment(id) {
    alert('Viewing payment #' + id + ' - Full details page to be implemented');
}

// Load payments on page load
loadPayments();
</script>

</div> <!-- Close admin-sidebar main content div -->

<?php require_once 'includes/footer.php'; ?>
