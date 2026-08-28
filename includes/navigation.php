<?php
if (!function_exists('getNavPhotoUrl')) {
    function getNavPhotoUrl($photoPath) {
        if (empty($photoPath)) {
            return '';
        }
        if (preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
            return $photoPath;
        }
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        if (!empty($base) && strpos($photoPath, $base) === 0) {
            return $photoPath;
        }
        return rtrim($base, '/') . '/' . ltrim($photoPath, '/');
    }
}

$panel_type = isset($panel_type) ? $panel_type : 'home';

if ($panel_type === 'home'): 
    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);
    $userRole = $_SESSION['role'] ?? null;
    $userName = '';
    $userPhoto = '';
    
    if ($isLoggedIn) {
        // Prioritize session data (updated immediately after profile save)
        $userName = $_SESSION['full_name'] ?? 'User';
        $rawPhoto = $_SESSION['profile_photo'] ?? '';
        
        if (empty($rawPhoto) && isset($pdo)) {
            // Fallback to database if session photo not set
            try {
                if ($userRole === 'learner') {
                    $stmt = $pdo->prepare("SELECT lp.full_name, lp.profile_photo FROM learner_profiles lp WHERE lp.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($profile) {
                        $userName = $profile['full_name'] ?? $userName;
                        if (!empty($profile['profile_photo'])) {
                            $rawPhoto = $profile['profile_photo'];
                            $_SESSION['profile_photo'] = $rawPhoto;
                        }
                    }
                } elseif ($userRole === 'expert') {
                    $stmt = $pdo->prepare("SELECT ep.full_name, ep.profile_photo FROM expert_profiles ep WHERE ep.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($profile) {
                        $userName = $profile['full_name'] ?? $userName;
                        if (!empty($profile['profile_photo'])) {
                            $rawPhoto = $profile['profile_photo'];
                            $_SESSION['profile_photo'] = $rawPhoto;
                        }
                    }
                }
            } catch (Exception $e) {}
        }
        
        $userPhoto = getNavPhotoUrl($rawPhoto);
    }
?>
    <nav class="backdrop-blur-2xl bg-[#070913]/80 border-b border-white/[0.08] sticky top-0 z-50 transition-all duration-300 shadow-[0_10px_35px_-10px_rgba(0,0,0,0.8)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3.5">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-[#00D4AA] via-[#00e5b7] to-[#06b6d4] rounded-xl flex items-center justify-center font-black text-[#080B10] text-2xl transition group-hover:scale-105 shadow-[0_0_20px_rgba(0,212,170,0.35)]">N</div>
                    <span class="text-xl sm:text-2xl font-black text-white tracking-tight">nexpert<span class="text-[#00D4AA]">.ai</span></span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="#categories" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">Explore</a>
                    <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">Find Experts</a>
                    <a href="index.php?page=how-trust-works" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">How Trust Works</a>
                    <a href="index.php?page=methodology" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">Methodology</a>
                    <a href="index.php?page=for-enterprise" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">For Enterprise</a>
                    
                    <div class="h-5 w-px bg-white/10 mx-2"></div>

                    <?php if ($isLoggedIn): ?>
                        <!-- Logged in user options -->
                        <div class="flex items-center space-x-3">
                            <a href="?panel=<?php echo $userRole; ?>&page=dashboard" class="bg-white/[0.05] hover:bg-white/[0.1] border border-white/10 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Dashboard</a>
                            <div class="flex items-center space-x-2 pl-2">
                                <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 flex items-center justify-center bg-gray-900 shrink-0">
                                    <?php if (!empty($userPhoto)): ?>
                                        <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="hidden w-full h-full items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($userName ?: 'U', 0, 1)); ?></span>
                                    <?php else: ?>
                                        <span class="w-full h-full flex items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($userName ?: 'U', 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($userName); ?></span>
                            </div>
                            <button id="home-logout-btn" title="Logout" class="text-gray-400 hover:text-red-400 p-2 rounded-lg hover:bg-red-950/30 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Guest user login buttons -->
                        <a href="?panel=learner&page=auth" class="bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-white/20 text-gray-200 hover:text-white px-4 py-2 rounded-xl text-sm font-semibold backdrop-blur-md transition-all">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] hover:from-[#00bda0] hover:to-[#047857] text-[#080B10] font-black px-5 py-2 rounded-xl shadow-[0_0_20px_rgba(0,212,170,0.3)] hover:shadow-[0_0_28px_rgba(0,212,170,0.5)] hover:scale-[1.02] active:scale-[0.98] transition-all text-sm">Apply as Expert</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="home-mobile-menu-btn" class="md:hidden p-2 text-gray-400 hover:text-white rounded-xl bg-white/[0.04] border border-white/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="home-mobile-menu" class="hidden md:hidden pb-4 pt-2">
                <div class="flex flex-col space-y-2 bg-[#0c1222]/95 backdrop-blur-2xl border border-white/10 rounded-2xl p-4 shadow-2xl">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-3 px-2 py-2.5 border-b border-white/10">
                            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 flex items-center justify-center bg-gray-900 shrink-0">
                                <?php if (!empty($userPhoto)): ?>
                                    <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span class="hidden w-full h-full items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($userName ?: 'U', 0, 1)); ?></span>
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($userName ?: 'U', 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold text-white"><?php echo htmlspecialchars($userName); ?></p>
                                <p class="text-xs text-gray-400"><?php echo ucfirst($userRole); ?></p>
                            </div>
                        </div>
                        <a href="?panel=<?php echo $userRole; ?>&page=dashboard" class="text-gray-200 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Dashboard</a>
                        <a href="#categories" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Categories</a>
                        <a href="#how-it-works" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">How It Works</a>
                        <a href="#experts" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Top Experts</a>
                        <button id="home-logout-btn-mobile" class="text-left text-red-400 hover:text-red-300 px-3 py-2 rounded-xl hover:bg-red-950/30 transition">Logout</button>
                    <?php else: ?>
                        <a href="#categories" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Categories</a>
                        <a href="#how-it-works" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">How It Works</a>
                        <a href="#experts" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Top Experts</a>
                        <div class="pt-2 flex flex-col gap-2">
                            <a href="?panel=learner&page=auth" class="bg-white/[0.05] border border-white/10 text-white px-4 py-2.5 rounded-xl hover:bg-white/[0.1] transition text-center font-semibold text-sm">Learner Login</a>
                            <a href="?panel=expert&page=apply" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] text-[#080B10] font-black px-4 py-2.5 rounded-xl shadow-lg transition text-center text-sm">Apply as Expert</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'learner'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner';
    $currentPage = $_GET['page'] ?? 'dashboard';
    
    // Get learner profile data if logged in - prioritize session data
    $learnerName = '';
    $learnerPhoto = '';
    if ($isLoggedIn) {
        $learnerName = $_SESSION['full_name'] ?? 'Learner';
        $rawPhoto = $_SESSION['profile_photo'] ?? '';
        
        if (empty($rawPhoto) && isset($pdo)) {
            // Fallback to database if session doesn't have photo
            try {
                $stmt = $pdo->prepare("SELECT lp.full_name, lp.profile_photo FROM learner_profiles lp WHERE lp.user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $learnerProfile = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($learnerProfile) {
                    $learnerName = $learnerProfile['full_name'] ?? $learnerName;
                    if (!empty($learnerProfile['profile_photo'])) {
                        $rawPhoto = $learnerProfile['profile_photo'];
                        $_SESSION['profile_photo'] = $rawPhoto;
                    }
                }
            } catch (Exception $e) {}
        }
        
        $learnerPhoto = getNavPhotoUrl($rawPhoto);
    }
?>
    <nav class="backdrop-blur-2xl bg-[#070913]/80 border-b border-white/[0.08] sticky top-0 z-50 transition-all duration-300 shadow-[0_10px_35px_-10px_rgba(0,0,0,0.8)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3.5">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-[#00D4AA] via-[#00e5b7] to-[#06b6d4] rounded-xl flex items-center justify-center font-black text-[#080B10] text-2xl transition group-hover:scale-105 shadow-[0_0_20px_rgba(0,212,170,0.35)]">N</div>
                    <span class="text-xl sm:text-2xl font-black text-white tracking-tight">nexpert<span class="text-[#00D4AA]">.ai</span></span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=learner&page=dashboard" class="<?php echo in_array($currentPage, ['dashboard', '']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="<?php echo in_array($currentPage, ['browse-experts', 'expert-profile']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="<?php echo in_array($currentPage, ['my-programs', 'program-details']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">My Programs</a>
                        <a href="?panel=learner&page=profile" class="<?php echo in_array($currentPage, ['profile', 'settings']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Profile</a>
                        <a href="?panel=learner&page=messages" class="relative <?php echo ($currentPage === 'messages') ? 'bg-white/[0.08] text-[#00D4AA]' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> p-2 rounded-xl transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="learner-unread-badge absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold hidden">0</span>
                        </a>

                        <div class="h-5 w-px bg-white/10 mx-2"></div>

                        <div class="flex items-center space-x-3 pl-1">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 flex items-center justify-center bg-gray-900 shrink-0">
                                    <?php if (!empty($learnerPhoto)): ?>
                                        <img src="<?php echo htmlspecialchars($learnerPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="hidden w-full h-full items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($learnerName ?: 'L', 0, 1)); ?></span>
                                    <?php else: ?>
                                        <span class="w-full h-full flex items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($learnerName ?: 'L', 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-medium text-gray-200"><?php echo htmlspecialchars($learnerName); ?></span>
                            </div>
                            <button id="learner-logout-btn" title="Logout" class="text-gray-400 hover:text-red-400 p-2 rounded-lg hover:bg-red-950/30 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-white hover:bg-white/[0.06] px-3.5 py-2 rounded-xl transition font-medium text-sm">Browse Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] hover:from-[#00bda0] hover:to-[#047857] text-[#080B10] font-black px-5 py-2 rounded-xl shadow-[0_0_20px_rgba(0,212,170,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all text-sm">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-white/20 text-gray-200 hover:text-white px-4 py-2 rounded-xl text-sm font-semibold backdrop-blur-md transition-all">Apply as Expert</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="learner-mobile-menu-btn" class="md:hidden p-2 text-gray-400 hover:text-white rounded-xl bg-white/[0.04] border border-white/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="learner-mobile-menu" class="hidden md:hidden pb-4 pt-2">
                <div class="flex flex-col space-y-2 bg-[#0c1222]/95 backdrop-blur-2xl border border-white/10 rounded-2xl p-4 shadow-2xl">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-3 px-2 py-2.5 border-b border-white/10">
                            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 flex items-center justify-center bg-gray-900 shrink-0">
                                <?php if (!empty($learnerPhoto)): ?>
                                    <img src="<?php echo htmlspecialchars($learnerPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span class="hidden w-full h-full items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($learnerName ?: 'L', 0, 1)); ?></span>
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($learnerName ?: 'L', 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold text-white"><?php echo htmlspecialchars($learnerName); ?></p>
                                <p class="text-xs text-gray-400">Learner</p>
                            </div>
                        </div>
                        <a href="?panel=learner&page=dashboard" class="<?php echo in_array($currentPage, ['dashboard', '']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-200 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="<?php echo in_array($currentPage, ['browse-experts', 'expert-profile']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="<?php echo in_array($currentPage, ['my-programs', 'program-details']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">My Programs</a>
                        <a href="?panel=learner&page=profile" class="<?php echo in_array($currentPage, ['profile', 'settings']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Profile</a>
                        <button id="learner-logout-btn-mobile" class="text-left text-red-400 hover:text-red-300 px-3 py-2 rounded-xl hover:bg-red-950/30 transition">Logout</button>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Browse Experts</a>
                        <div class="pt-2 flex flex-col gap-2">
                            <a href="?panel=learner&page=auth" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] text-[#080B10] font-black px-4 py-2.5 rounded-xl shadow-lg transition text-center text-sm">Learner Login</a>
                            <a href="?panel=expert&page=apply" class="bg-white/[0.05] border border-white/10 text-white px-4 py-2.5 rounded-xl hover:bg-white/[0.1] transition text-center font-semibold text-sm">Apply as Expert</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'expert'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
    $currentPage = $_GET['page'] ?? 'dashboard';
    
    // Get expert profile data if logged in
    $expertName = '';
    $expertPhoto = '';
    if ($isLoggedIn) {
        $expertName = $_SESSION['full_name'] ?? 'Expert';
        $rawPhoto = $_SESSION['profile_photo'] ?? '';
        
        if (empty($rawPhoto) && isset($pdo)) {
            try {
                $stmt = $pdo->prepare("SELECT ep.full_name, ep.profile_photo FROM expert_profiles ep WHERE ep.user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $expertProfile = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($expertProfile) {
                    $expertName = $expertProfile['full_name'] ?? $expertName;
                    if (!empty($expertProfile['profile_photo'])) {
                        $rawPhoto = $expertProfile['profile_photo'];
                        $_SESSION['profile_photo'] = $rawPhoto;
                    }
                }
            } catch (Exception $e) {}
        }
        
        $expertPhoto = getNavPhotoUrl($rawPhoto);
    }
?>
    <nav class="backdrop-blur-2xl bg-[#070913]/80 border-b border-white/[0.08] sticky top-0 z-50 transition-all duration-300 shadow-[0_10px_35px_-10px_rgba(0,0,0,0.8)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3.5">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-[#00D4AA] via-[#00e5b7] to-[#06b6d4] rounded-xl flex items-center justify-center font-black text-[#080B10] text-2xl transition group-hover:scale-105 shadow-[0_0_20px_rgba(0,212,170,0.35)]">N</div>
                    <span class="text-xl sm:text-2xl font-black text-white tracking-tight">nexpert<span class="text-[#00D4AA]">.ai</span></span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=expert&page=dashboard" class="<?php echo in_array($currentPage, ['dashboard', '']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Dashboard</a>
                        <a href="?panel=expert&page=daily-credibility-card" class="<?php echo ($currentPage === 'daily-credibility-card') ? 'bg-[#00D4AA]/15 border border-[#00D4AA]/35 text-[#00D4AA] font-bold shadow-[0_0_15px_rgba(0,212,170,0.15)]' : 'text-gray-300 hover:text-[#00D4AA] hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 text-sm">
                            <span class="<?php echo ($currentPage === 'daily-credibility-card') ? 'animate-pulse' : ''; ?>">✨</span><span>Credibility Card</span>
                        </a>
                        <a href="?panel=expert&page=my-programs" class="<?php echo in_array($currentPage, ['my-programs', 'program-details']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Programs</a>
                        <a href="?panel=expert&page=my-webinars" class="<?php echo in_array($currentPage, ['my-webinars', 'webinar-details']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Webinars</a>
                        <a href="?panel=expert&page=earnings" class="<?php echo ($currentPage === 'earnings') ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Earnings</a>
                        <a href="?panel=expert&page=booking-management" class="<?php echo in_array($currentPage, ['booking-management', 'booking-details']) ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Bookings</a>
                        <a href="?panel=expert&page=learner-management" class="<?php echo ($currentPage === 'learner-management') ? 'bg-white/[0.08] text-[#00D4AA] font-bold' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> px-3.5 py-2 rounded-xl transition text-sm">Learners</a>
                        <a href="?panel=expert&page=messages" class="relative <?php echo ($currentPage === 'messages') ? 'bg-white/[0.08] text-[#00D4AA]' : 'text-gray-300 hover:text-white hover:bg-white/[0.06]'; ?> p-2 rounded-xl transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="expert-unread-badge absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center font-bold hidden">0</span>
                        </a>

                        <div class="h-5 w-px bg-white/10 mx-2"></div>

                        <div class="flex items-center space-x-3 pl-1">
                            <a href="?panel=expert&page=settings" class="flex items-center space-x-2 group/prof">
                                <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 group-hover/prof:ring-[#00D4AA] transition flex items-center justify-center bg-gray-900 shrink-0">
                                    <?php if (!empty($expertPhoto)): ?>
                                        <img id="expert-nav-photo" src="<?php echo htmlspecialchars($expertPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="hidden w-full h-full items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($expertName ?: 'E', 0, 1)); ?></span>
                                    <?php else: ?>
                                        <span class="w-full h-full flex items-center justify-center font-bold text-xs text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($expertName ?: 'E', 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-medium text-gray-200 group-hover/prof:text-white"><?php echo htmlspecialchars($expertName); ?></span>
                            </a>
                            <button id="expert-logout-btn" title="Logout" class="text-gray-400 hover:text-red-400 p-2 rounded-lg hover:bg-red-950/30 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="?panel=expert&page=auth" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] hover:from-[#00bda0] hover:to-[#047857] text-[#080B10] font-black px-5 py-2 rounded-xl shadow-[0_0_20px_rgba(0,212,170,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all text-sm">Expert Login</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="expert-mobile-menu-btn" class="md:hidden p-2 text-gray-400 hover:text-white rounded-xl bg-white/[0.04] border border-white/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="expert-mobile-menu" class="hidden md:hidden pb-4 pt-2">
                <div class="flex flex-col space-y-2 bg-[#0c1222]/95 backdrop-blur-2xl border border-white/10 rounded-2xl p-4 shadow-2xl">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-3 px-2 py-2.5 border-b border-white/10">
                            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-[#00D4AA]/40 flex items-center justify-center bg-gray-900 shrink-0">
                                <?php if (!empty($expertPhoto)): ?>
                                    <img src="<?php echo htmlspecialchars($expertPhoto); ?>" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span class="hidden w-full h-full items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($expertName ?: 'E', 0, 1)); ?></span>
                                <?php else: ?>
                                    <span class="w-full h-full flex items-center justify-center font-bold text-sm text-[#00D4AA] bg-[#0c1222]"><?php echo strtoupper(substr($expertName ?: 'E', 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold text-white"><?php echo htmlspecialchars($expertName); ?></p>
                                <p class="text-xs text-[#00D4AA]">Verified Expert</p>
                            </div>
                        </div>
                        <a href="?panel=expert&page=dashboard" class="<?php echo in_array($currentPage, ['dashboard', '']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-200 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Dashboard</a>
                        <a href="?panel=expert&page=daily-credibility-card" class="<?php echo ($currentPage === 'daily-credibility-card') ? 'text-[#00D4AA] font-bold bg-[#00D4AA]/15 border border-[#00D4AA]/30' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl transition flex items-center gap-2">✨ Credibility Card</a>
                        <a href="?panel=expert&page=my-programs" class="<?php echo in_array($currentPage, ['my-programs', 'program-details']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">My Programs</a>
                        <a href="?panel=expert&page=my-webinars" class="<?php echo in_array($currentPage, ['my-webinars', 'webinar-details']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">My Webinars</a>
                        <a href="?panel=expert&page=earnings" class="<?php echo ($currentPage === 'earnings') ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Earnings</a>
                        <a href="?panel=expert&page=booking-management" class="<?php echo in_array($currentPage, ['booking-management', 'booking-details']) ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Bookings</a>
                        <a href="?panel=expert&page=learner-management" class="<?php echo ($currentPage === 'learner-management') ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Learners</a>
                        <a href="?panel=expert&page=settings" class="<?php echo ($currentPage === 'settings') ? 'text-[#00D4AA] font-bold bg-white/[0.06]' : 'text-gray-300 hover:text-[#00D4AA]'; ?> px-3 py-2 rounded-xl hover:bg-white/[0.04] transition">Settings</a>
                        <button id="expert-logout-btn-mobile" class="text-left text-red-400 hover:text-red-300 px-3 py-2 rounded-xl hover:bg-red-950/30 transition">Logout</button>
                    <?php else: ?>
                        <div class="pt-1 flex flex-col gap-2">
                            <a href="?panel=expert&page=auth" class="bg-gradient-to-r from-[#00D4AA] to-[#059669] text-[#080B10] font-black px-4 py-2.5 rounded-xl shadow-lg transition text-center text-sm">Expert Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>

<script>
// Hamburger menu toggles
const homeMobileMenuBtn = document.getElementById('home-mobile-menu-btn');
const homeMobileMenu = document.getElementById('home-mobile-menu');
if (homeMobileMenuBtn && homeMobileMenu) {
    homeMobileMenuBtn.addEventListener('click', () => {
        homeMobileMenu.classList.toggle('hidden');
    });
}

const learnerMobileMenuBtn = document.getElementById('learner-mobile-menu-btn');
const learnerMobileMenu = document.getElementById('learner-mobile-menu');
if (learnerMobileMenuBtn && learnerMobileMenu) {
    learnerMobileMenuBtn.addEventListener('click', () => {
        learnerMobileMenu.classList.toggle('hidden');
    });
}

const expertMobileMenuBtn = document.getElementById('expert-mobile-menu-btn');
const expertMobileMenu = document.getElementById('expert-mobile-menu');
if (expertMobileMenuBtn && expertMobileMenu) {
    expertMobileMenuBtn.addEventListener('click', () => {
        expertMobileMenu.classList.toggle('hidden');
    });
}

// Learner logout handlers (desktop and mobile)
const learnerLogoutBtn = document.getElementById('learner-logout-btn');
const learnerLogoutBtnMobile = document.getElementById('learner-logout-btn-mobile');
const handleLearnerLogout = async function() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            const response = await fetch(BASE_PATH + '/admin-panel/apis/learner/auth.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = BASE_PATH + '/';
            } else {
                alert('Logout failed. Please try again.');
            }
        } catch (error) {
            console.error('Logout error:', error);
            alert('Logout failed. Please try again.');
        }
    }
};
if (learnerLogoutBtn) learnerLogoutBtn.addEventListener('click', handleLearnerLogout);
if (learnerLogoutBtnMobile) learnerLogoutBtnMobile.addEventListener('click', handleLearnerLogout);

// Expert logout handlers (desktop and mobile)
const expertLogoutBtn = document.getElementById('expert-logout-btn');
const expertLogoutBtnMobile = document.getElementById('expert-logout-btn-mobile');
const handleExpertLogout = async function() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/auth.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = BASE_PATH + '/';
            } else {
                alert('Logout failed. Please try again.');
            }
        } catch (error) {
            console.error('Logout error:', error);
            alert('Logout failed. Please try again.');
        }
    }
};
if (expertLogoutBtn) expertLogoutBtn.addEventListener('click', handleExpertLogout);
if (expertLogoutBtnMobile) expertLogoutBtnMobile.addEventListener('click', handleExpertLogout);

// Home page logout handlers (desktop and mobile) - for any logged in user
const homeLogoutBtn = document.getElementById('home-logout-btn');
const homeLogoutBtnMobile = document.getElementById('home-logout-btn-mobile');
const handleHomeLogout = async function() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            // Determine which API to call based on user role
            const userRole = '<?php echo $_SESSION["role"] ?? ""; ?>';
            const apiUrl = userRole === 'learner' 
                ? BASE_PATH + '/admin-panel/apis/learner/auth.php'
                : BASE_PATH + '/admin-panel/apis/expert/auth.php';
            
            const response = await fetch(apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = BASE_PATH + '/';
            } else {
                alert('Logout failed. Please try again.');
            }
        } catch (error) {
            console.error('Logout error:', error);
            alert('Logout failed. Please try again.');
        }
    }
};
if (homeLogoutBtn) homeLogoutBtn.addEventListener('click', handleHomeLogout);
if (homeLogoutBtnMobile) homeLogoutBtnMobile.addEventListener('click', handleHomeLogout);

// Load expert profile photo in navigation
const expertNavPhoto = document.getElementById('expert-nav-photo');
if (expertNavPhoto) {
    fetch(BASE_PATH + '/admin-panel/apis/expert/profile.php?user_id=<?php echo $_SESSION["user_id"] ?? ""; ?>')
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data && result.data.profile_photo) {
                let photo = result.data.profile_photo;
                if (!photo.startsWith('http') && !photo.startsWith('data:') && !photo.startsWith(BASE_PATH)) {
                    photo = BASE_PATH + '/' + photo.replace(/^\/+/, '');
                }
                expertNavPhoto.src = photo;
            }
        })
        .catch(error => console.error('Error loading profile photo:', error));
}

// Load unread message counts for learner panel
const learnerUnreadBadge = document.querySelector('.learner-unread-badge');
if (learnerUnreadBadge) {
    async function updateLearnerUnreadCount() {
        try {
            const response = await fetch(BASE_PATH + '/admin-panel/apis/learner/messages.php?action=unread_count');
            const result = await response.json();
            if (result.success && result.unread_count > 0) {
                learnerUnreadBadge.textContent = result.unread_count;
                learnerUnreadBadge.classList.remove('hidden');
            } else {
                learnerUnreadBadge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }
    updateLearnerUnreadCount();
    // Refresh every 30 seconds
    setInterval(updateLearnerUnreadCount, 30000);
}

// Load unread message counts for expert panel
const expertUnreadBadge = document.querySelector('.expert-unread-badge');
if (expertUnreadBadge) {
    async function updateExpertUnreadCount() {
        try {
            const response = await fetch(BASE_PATH + '/admin-panel/apis/expert/messages.php?action=unread_count');
            const result = await response.json();
            if (result.success && result.unread_count > 0) {
                expertUnreadBadge.textContent = result.unread_count;
                expertUnreadBadge.classList.remove('hidden');
            } else {
                expertUnreadBadge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }
    updateExpertUnreadCount();
    // Refresh every 30 seconds
    setInterval(updateExpertUnreadCount, 30000);
}
</script>
