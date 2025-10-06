<?php
require_once 'includes/admin-auth-check.php';

$page_title = "Payout Management - Admin";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Payout Management</h1>
        <p class="text-gray-600 mt-1">Process and manage expert payouts</p>
    </div>


    <div class="p-6">
        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b">
                <nav class="flex space-x-8 px-6">
                    <button onclick="filterPayouts('pending')" class="border-b-2 border-primary text-primary py-4 px-1 font-medium">Pending</button>
                    <button onclick="filterPayouts('completed')" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 py-4 px-1">Completed</button>
                    <button onclick="filterPayouts('rejected')" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 py-4 px-1">Rejected</button>
                    <button onclick="filterPayouts('')" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 py-4 px-1">All</button>
                </nav>
            </div>
        </div>

        <!-- Payouts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expert</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="payouts-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Loading payouts...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Process Payout Modal -->
<div id="payout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-lg w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Process Payout</h3>
        <div id="payout-details" class="mb-6"></div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction ID</label>
                <input type="text" id="transaction-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter transaction ID">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                <textarea id="admin-notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Add any notes..."></textarea>
            </div>
        </div>
        <div class="mt-6 flex space-x-4">
            <button onclick="processPayout('completed')" class="flex-1 bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">Approve & Pay</button>
            <button onclick="processPayout('rejected')" class="flex-1 bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600">Reject</button>
            <button onclick="closePayoutModal()" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">Cancel</button>
        </div>
    </div>
</div>

<script>
let currentPayoutId = null;

async function loadPayouts(status = 'pending') {
    const url = status ? `/admin-panel/apis/admin/payouts.php?status=${status}` : '/admin-panel/apis/admin/payouts.php';
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const tableHtml = data.payouts.map(payout => `
                <tr>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">${payout.expert_name}</p>
                            <p class="text-sm text-gray-600">${payout.expert_email}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900">$${parseFloat(payout.amount).toLocaleString()}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${new Date(payout.created_at).toLocaleDateString()}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            payout.status === 'completed' ? 'bg-green-100 text-green-800' :
                            payout.status === 'rejected' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                        }">${payout.status}</span>
                    </td>
                    <td class="px-6 py-4">
                        ${payout.status === 'pending' ? 
                            `<button onclick="viewPayout(${payout.id}, '${payout.expert_name}', ${payout.amount})" class="text-primary hover:underline text-sm">Process</button>` :
                            `<span class="text-gray-400 text-sm">${payout.status}</span>`
                        }
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('payouts-table').innerHTML = tableHtml || '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No payouts found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading payouts:', error);
    }
}

function filterPayouts(status) {
    // Update active tab styling
    document.querySelectorAll('nav button').forEach(btn => {
        btn.className = 'border-b-2 border-transparent text-gray-500 hover:text-gray-700 py-4 px-1';
    });
    event.target.className = 'border-b-2 border-primary text-primary py-4 px-1 font-medium';
    
    loadPayouts(status);
}

function viewPayout(payoutId, expertName, amount) {
    currentPayoutId = payoutId;
    document.getElementById('payout-details').innerHTML = `
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="font-semibold text-gray-900">${expertName}</p>
            <p class="text-2xl font-bold text-primary mt-2">$${parseFloat(amount).toLocaleString()}</p>
        </div>
    `;
    document.getElementById('payout-modal').classList.remove('hidden');
}

async function processPayout(status) {
    if (!currentPayoutId) return;
    
    const transactionId = document.getElementById('transaction-id').value;
    const adminNotes = document.getElementById('admin-notes').value;
    
    try {
        const response = await fetch('/admin-panel/apis/admin/payouts.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                payout_id: currentPayoutId,
                status: status,
                transaction_id: transactionId,
                admin_notes: adminNotes
            })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Payout processed successfully');
            closePayoutModal();
            loadPayouts('pending');
        }
    } catch (error) {
        console.error('Error processing payout:', error);
        alert('Error processing payout');
    }
}

function closePayoutModal() {
    document.getElementById('payout-modal').classList.add('hidden');
    document.getElementById('transaction-id').value = '';
    document.getElementById('admin-notes').value = '';
    currentPayoutId = null;
}

// Load payouts on page load
loadPayouts('pending');
</script>

<?php require_once 'includes/footer.php'; ?>
</div> <!-- Close admin-sidebar main content div -->
