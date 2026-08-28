<?php
// Admin Sidebar Navigation Component
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- Admin Sidebar -->
<div class="fixed left-0 top-0 h-full w-64 bg-[#0D131F] border-r border-gray-800 text-white flex flex-col z-50">
    <!-- Logo/Brand -->
    <div class="p-6 border-b border-gray-800 flex items-center gap-3">
        <div class="w-9 h-9 bg-gradient-to-br from-[#00D4AA] to-teal-700 rounded-xl flex items-center justify-center font-black text-[#080B10] text-base shadow-[0_0_15px_rgba(0,212,170,0.3)]">N</div>
        <div>
            <h1 class="text-lg font-bold text-white leading-tight">Nexpert.ai</h1>
            <p class="text-[11px] text-[#00D4AA] font-mono uppercase tracking-wider">Admin Console</p>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
        <?php
        $navLinks = [
            ['dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['experts', 'Experts', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['users', 'Users', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['bookings', 'Bookings', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['payments', 'Payments', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['payouts', 'Payouts', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['kyc-verification', 'KYC Verification', 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],
            ['categories', 'Categories', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['credibility', 'Credibility Console', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['settings', 'Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z']
        ];
        foreach ($navLinks as [$key, $name, $path]):
            $isActive = ($currentPage === $key);
        ?>
        <a href="?panel=admin&page=<?= $key ?>" 
           class="flex items-center px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= $isActive ? 'bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 font-bold shadow-sm' : 'text-gray-400 hover:text-white hover:bg-[#131B2E]' ?>">
            <svg class="w-4 h-4 mr-3 <?= $isActive ? 'text-[#00D4AA]' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $path ?>"></path>
            </svg>
            <?= $name ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- User Section & Logout -->
    <div class="p-4 border-t border-gray-800 bg-[#080B10]/50">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-[#00D4AA] rounded-xl flex items-center justify-center text-[#080B10] font-extrabold text-xs">
                    A
                </div>
                <div class="ml-2.5">
                    <p class="text-xs font-bold text-white">Admin</p>
                    <p class="text-[11px] text-gray-500 truncate max-w-[140px]"><?php echo $_SESSION['email'] ?? 'admin@nexpert.ai'; ?></p>
                </div>
            </div>
        </div>
        <button id="adminLogout" class="w-full flex items-center justify-center px-3 py-2 bg-red-900/30 border border-red-800/60 text-red-300 rounded-xl hover:bg-red-900/60 transition text-xs font-bold">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
        </button>
    </div>
</div>

<!-- Main Content Area (with sidebar offset) -->
<div class="ml-64 min-h-screen bg-[#080B10] text-gray-100">

<script src="/admin-panel/js/admin-logout.js"></script>
