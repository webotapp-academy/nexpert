<?php
require_once 'includes/admin-auth-check.php';

$page_title = "Admin Dashboard - Nexpert.ai";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1">Platform Management & Analytics</p>
    </div>

    <div class="p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900" id="total-users">--</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    <span class="text-green-600" id="active-users">--</span> Active
                </p>
            </div>

            <!-- Total Experts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Experts</p>
                        <p class="text-3xl font-bold text-gray-900" id="total-experts">--</p>
                    </div>
                    <div class="bg-accent/20 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    <span class="text-yellow-600" id="pending-verification">--</span> Pending Verification
                </p>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900" id="total-revenue">$0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    <span class="text-green-600" id="monthly-revenue">$0</span> This Month
                </p>
            </div>

            <!-- Pending Payouts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Pending Payouts</p>
                        <p class="text-3xl font-bold text-gray-900" id="pending-payout-amount">$0</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    <span class="text-red-600" id="pending-payout-count">0</span> Requests
                </p>
            </div>
        </div>

        <!-- Recent Activity & Charts -->
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Bookings</h3>
                </div>
                <div class="p-6">
                    <div id="recent-bookings" class="space-y-4">
                        <!-- Bookings will be loaded here -->
                        <p class="text-gray-500 text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Verification Queue -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Verification Queue</h3>
                    <a href="?panel=admin&page=experts&status=pending" class="text-primary text-sm hover:underline">View All</a>
                </div>
                <div class="p-6">
                    <div class="space-y-3" id="verification-queue">
                        <p class="text-gray-500 text-center py-4">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load admin dashboard data
async function loadAdminDashboard() {
    try {
        const response = await fetch('/admin-panel/apis/admin/dashboard.php');
        const data = await response.json();
        
        if (data.success) {
            // Update stats
            document.getElementById('total-users').textContent = data.users.total_users || 0;
            document.getElementById('active-users').textContent = data.users.active_users || 0;
            document.getElementById('total-experts').textContent = data.users.total_experts || 0;
            document.getElementById('pending-verification').textContent = data.verification.pending || 0;
            document.getElementById('total-revenue').textContent = '$' + (data.revenue.total_revenue || 0).toLocaleString();
            document.getElementById('monthly-revenue').textContent = '$' + (data.monthly.monthly_revenue || 0).toLocaleString();
            document.getElementById('pending-payout-amount').textContent = '$' + (data.pending_payouts.total || 0).toLocaleString();
            document.getElementById('pending-payout-count').textContent = data.pending_payouts.count || 0;
            
            // Load recent bookings
            const bookingsHtml = data.recent_bookings.map(booking => `
                <div class="flex items-center justify-between py-3 border-b">
                    <div>
                        <p class="font-medium text-gray-900">${booking.learner_name || 'Unknown'} → ${booking.expert_name || 'Unknown'}</p>
                        <p class="text-sm text-gray-600">${new Date(booking.session_datetime).toLocaleDateString()}</p>
                    </div>
                    <span class="px-3 py-1 text-xs rounded-full ${
                        booking.status === 'completed' ? 'bg-green-100 text-green-800' :
                        booking.status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                        'bg-yellow-100 text-yellow-800'
                    }">${booking.status}</span>
                </div>
            `).join('');
            document.getElementById('recent-bookings').innerHTML = bookingsHtml || '<p class="text-gray-500 text-center">No recent bookings</p>';
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

// Load pending verifications
async function loadPendingVerifications() {
    try {
        const response = await fetch('/admin-panel/apis/admin/experts.php?verification_status=pending');
        const data = await response.json();
        
        if (data.success && data.experts.length > 0) {
            const html = data.experts.slice(0, 5).map(expert => `
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                        <div>
                            <p class="font-medium text-gray-900">${expert.full_name}</p>
                            <p class="text-sm text-gray-600">${expert.email}</p>
                        </div>
                    </div>
                    <a href="?panel=admin&page=experts&expert_id=${expert.user_id}" class="text-primary text-sm hover:underline">Review</a>
                </div>
            `).join('');
            document.getElementById('verification-queue').innerHTML = html;
        } else {
            document.getElementById('verification-queue').innerHTML = '<p class="text-gray-500 text-center">No pending verifications</p>';
        }
    } catch (error) {
        console.error('Error loading verifications:', error);
    }
}

// Load data on page load
loadAdminDashboard();
loadPendingVerifications();
</script>

</div> <!-- Close admin-sidebar main content div -->

<?php require_once 'includes/footer.php'; ?>
