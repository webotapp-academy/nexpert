<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/admin-auth-check.php';

$page_title = "Payout Management - Admin";
$panel_type = "admin";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/admin-sidebar.php';
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank Account</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="payouts-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Loading payouts...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Process Payout Modal -->
<div id="payout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">Process Payout</h3>
        <div id="payout-details" class="mb-4"></div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                <input type="text" id="transaction-id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Enter transaction ID">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
                <textarea id="admin-notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Add any notes..."></textarea>
            </div>
        </div>
        <div class="mt-4 flex space-x-2">
            <button onclick="processPayout('completed')" class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm font-medium">Approve & Pay</button>
            <button onclick="processPayout('rejected')" class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-medium">Reject</button>
            <button onclick="closePayoutModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 text-sm font-medium">Cancel</button>
        </div>
    </div>
</div>

<script>
let currentPayoutId = null;

async function loadPayouts(status = 'pending') {
    const url = status ? `${BASE_PATH}/admin-panel/apis/admin/payouts.php?status=${status}` : `${BASE_PATH}/admin-panel/apis/admin/payouts.php`;
    
    try {
        const response = await window.AdminAPI.fetch(url);
        const data = await response.json();
        
        if (data.success) {
            // Fetch bank details for each payout
            const payoutsWithBank = await Promise.all(data.payouts.map(async (payout) => {
                try {
                    const detailResponse = await window.AdminAPI.fetch(`${BASE_PATH}/admin-panel/apis/admin/payouts.php?payout_id=${payout.id}`);
                    const detailData = await detailResponse.json();
                    if (detailData.success && detailData.payout && detailData.payout.bank_details) {
                        payout.account_number = detailData.payout.bank_details.account_number;
                    }
                } catch (error) {
                    console.error('Error fetching bank details for payout', payout.id, error);
                }
                return payout;
            }));
            
            const tableHtml = payoutsWithBank.map(payout => `
                <tr>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-gray-900">${payout.expert_name}</p>
                            <p class="text-sm text-gray-600">${payout.expert_email}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-900">₹${parseFloat(payout.amount).toLocaleString()}</td>
                    <td class="px-6 py-4">
                        ${payout.account_number ? 
                            `<span class="font-mono text-sm text-gray-900">****${payout.account_number.slice(-4)}</span>` :
                            `<span class="text-xs text-gray-400">Not set</span>`
                        }
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">${new Date(payout.created_at).toLocaleDateString()}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full ${
                            payout.status === 'processed' || payout.status === 'completed' ? 'bg-green-100 text-green-800' :
                            payout.status === 'rejected' || payout.status === 'failed' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                        }">${payout.status}</span>
                    </td>
                    <td class="px-6 py-4">
                        ${payout.status === 'pending' ? 
                            `<button onclick="viewPayout(${payout.id}, '${payout.expert_name}', ${payout.amount})" class="text-primary hover:underline text-sm font-medium">Process</button>` :
                            payout.status === 'processed' || payout.status === 'completed' ?
                            `<span class="text-green-600 text-sm font-medium">✓ Processed</span>` :
                            payout.status === 'failed' || payout.status === 'rejected' ?
                            `<span class="text-red-600 text-sm font-medium">✗ ${payout.status}</span>` :
                            `<span class="text-gray-400 text-sm">${payout.status}</span>`
                        }
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('payouts-table').innerHTML = tableHtml || '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No payouts found</td></tr>';
        } else {
            document.getElementById('payouts-table').innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Error: ' + (data.message || 'Failed to load payouts') + '</td></tr>';
        }
    } catch (error) {
        console.error('Error loading payouts:', error);
        document.getElementById('payouts-table').innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Error loading payouts. Please check console for details.</td></tr>';
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

async function viewPayout(payoutId, expertName, amount) {
    currentPayoutId = payoutId;
    
    // Show loading state
    document.getElementById('payout-details').innerHTML = `
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-600">Loading bank details...</p>
        </div>
    `;
    document.getElementById('payout-modal').classList.remove('hidden');
    
    try {
        // Fetch payout details with bank information
        const response = await window.AdminAPI.fetch(`${BASE_PATH}/admin-panel/apis/admin/payouts.php?payout_id=${payoutId}`);
        const data = await response.json();
        
        if (data.success && data.payout) {
            const payout = data.payout;
            document.getElementById('payout-details').innerHTML = `
                <div class="space-y-3">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-600">Expert</p>
                        <p class="font-semibold text-gray-900 text-sm">${expertName}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-600">Amount</p>
                        <p class="text-xl font-bold text-primary">₹${parseFloat(amount).toLocaleString()}</p>
                    </div>
                    
                    ${payout.bank_details ? `
                        <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Bank Details</p>
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Holder:</span>
                                    <span class="font-medium text-gray-900">${payout.bank_details.account_holder_name || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Number:</span>
                                    <span class="font-mono font-medium text-gray-900 text-xs">${payout.bank_details.account_number || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">IFSC Code:</span>
                                    <span class="font-mono font-medium text-gray-900">${payout.bank_details.ifsc_code || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bank Name:</span>
                                    <span class="font-medium text-gray-900">${payout.bank_details.bank_name || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    ` : `
                        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                            <p class="text-xs text-yellow-800">⚠️ Bank details not available</p>
                        </div>
                    `}
                </div>
            `;
        } else {
            document.getElementById('payout-details').innerHTML = `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-semibold text-gray-900">${expertName}</p>
                    <p class="text-2xl font-bold text-primary mt-2">₹${parseFloat(amount).toLocaleString()}</p>
                    <p class="text-sm text-yellow-600 mt-2">Could not load bank details</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading payout details:', error);
        document.getElementById('payout-details').innerHTML = `
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="font-semibold text-gray-900">${expertName}</p>
                <p class="text-2xl font-bold text-primary mt-2">₹${parseFloat(amount).toLocaleString()}</p>
                <p class="text-sm text-red-600 mt-2">Error loading bank details</p>
            </div>
        `;
    }
}

async function processPayout(status) {
    if (!currentPayoutId) return;
    
    const transactionId = document.getElementById('transaction-id').value;
    const adminNotes = document.getElementById('admin-notes').value;
    
    // Validate transaction ID for completed status
    if (status === 'completed' && !transactionId.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please enter a transaction ID to approve the payout'
        });
        return;
    }
    
    try {
        const response = await window.AdminAPI.fetch(`${BASE_PATH}/admin-panel/apis/admin/payouts.php`, {
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
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: status === 'completed' ? 'Payout approved and processed successfully' : 'Payout rejected successfully',
                timer: 2000,
                showConfirmButton: false
            });
            closePayoutModal();
            loadPayouts('pending');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to process payout'
            });
        }
    } catch (error) {
        console.error('Error processing payout:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error processing payout. Please try again.'
        });
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
