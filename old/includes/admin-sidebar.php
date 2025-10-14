<?php
// Admin Sidebar Navigation Component
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- Admin Sidebar -->
<div class="fixed left-0 top-0 h-full w-64 bg-gray-900 text-white flex flex-col z-50">
    <!-- Logo/Brand -->
    <div class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold text-white">Nexpert.ai</h1>
        <p class="text-sm text-gray-400 mt-1">Admin Panel</p>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="?panel=admin&page=dashboard" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'dashboard' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard
        </a>

        <a href="?panel=admin&page=experts" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'experts' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Experts
        </a>

        <a href="?panel=admin&page=users" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'users' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            Users
        </a>

        <a href="?panel=admin&page=bookings" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'bookings' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Bookings
        </a>

        <a href="?panel=admin&page=payments" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'payments' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            Payments
        </a>

        <a href="?panel=admin&page=payouts" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'payouts' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Payouts
        </a>

        <a href="?panel=admin&page=kyc-verification" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'kyc-verification' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
            </svg>
            KYC Verification
        </a>

        <a href="?panel=admin&page=settings" 
           class="flex items-center px-4 py-3 rounded-lg transition <?php echo $currentPage === 'settings' ? 'bg-primary text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Settings
        </a>
    </nav>

    <!-- User Section & Logout -->
    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                    A
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">Admin</p>
                    <p class="text-xs text-gray-400"><?php echo $_SESSION['email'] ?? 'admin@nexpert.ai'; ?></p>
                </div>
            </div>
        </div>
        <button id="adminLogout" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
        </button>
    </div>
</div>

<!-- Main Content Area (with sidebar offset) -->
<div class="ml-64 min-h-screen bg-gray-50">

<script src="/admin-panel/js/admin-logout.js"></script>
