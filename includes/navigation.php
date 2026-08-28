<?php
$panel_type = isset($panel_type) ? $panel_type : 'home';

if ($panel_type === 'home'): 
    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);
    $userRole = $_SESSION['role'] ?? null;
    $userName = '';
    $userPhoto = 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    
    if ($isLoggedIn) {
        // Prioritize session data (updated immediately after profile save)
        $userName = $_SESSION['full_name'] ?? 'User';
        
        // Check for profile photo in session first (most up-to-date)
        if (!empty($_SESSION['profile_photo'])) {
            $photoPath = $_SESSION['profile_photo'];
            if (!preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
                $userPhoto = BASE_PATH . $photoPath;
            } else {
                $userPhoto = $photoPath;
            }
        } elseif (isset($pdo)) {
            // Fallback to database if session photo not set
            try {
                if ($userRole === 'learner') {
                    $stmt = $pdo->prepare("SELECT lp.full_name, lp.profile_photo FROM learner_profiles lp WHERE lp.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userName = $profile['full_name'] ?? $userName;
                    if (!empty($profile['profile_photo'])) {
                        $photoPath = $profile['profile_photo'];
                        if (!preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
                            $userPhoto = BASE_PATH . $photoPath;
                        } else {
                            $userPhoto = $photoPath;
                        }
                        // Update session with database photo
                        $_SESSION['profile_photo'] = $profile['profile_photo'];
                    } else {
                        $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
                    }
                } elseif ($userRole === 'expert') {
                    $stmt = $pdo->prepare("SELECT ep.full_name, ep.profile_photo FROM expert_profiles ep WHERE ep.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userName = $profile['full_name'] ?? $userName;
                    if (!empty($profile['profile_photo'])) {
                        $photoPath = $profile['profile_photo'];
                        if (!preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
                            $userPhoto = BASE_PATH . $photoPath;
                        } else {
                            $userPhoto = $photoPath;
                        }
                        // Update session with database photo
                        $_SESSION['profile_photo'] = $profile['profile_photo'];
                    } else {
                        $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
                    }
                }
            } catch (Exception $e) {
                $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
            }
        } else {
            $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
        }
    } elseif ($isLoggedIn) {
        // If PDO is not available, use session data as fallback
        $userName = $_SESSION['full_name'] ?? 'User';
        $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    } else {
        $userPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    }
?>
    <nav class="bg-[#080B10] border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-[#00D4AA] rounded-xl flex items-center justify-center font-extrabold text-[#080B10] text-2xl transition group-hover:scale-105">N</div>
                    <span class="text-xl sm:text-2xl font-bold text-white tracking-tight">nexpert.ai</span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#categories" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">Explore</a>
                    <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">Find Experts</a>
                    <a href="index.php?page=how-trust-works" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">How Trust Works</a>
                    <a href="index.php?page=methodology" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">Methodology</a>
                    <a href="index.php?page=for-enterprise" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">For Enterprise</a>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- Logged in user options -->
                        <div class="flex items-center space-x-4">
                            <a href="?panel=<?php echo $userRole; ?>&page=dashboard" class="text-gray-300 hover:text-[#00D4AA] transition font-medium">Dashboard</a>
                            <div class="flex items-center space-x-2">
                                <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-gray-700">
                                <span class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($userName); ?></span>
                            </div>
                            <button id="home-logout-btn" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Guest user login buttons -->
                        <a href="?panel=learner&page=auth" class="text-gray-300 hover:text-white transition font-medium">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="border border-gray-700 text-white px-6 py-2 rounded-xl hover:bg-white hover:text-black hover:border-white transition font-semibold text-sm">Apply as Expert</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="home-mobile-menu-btn" class="md:hidden p-2 text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="home-mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <?php if ($isLoggedIn): ?>
                        <!-- Logged in mobile menu -->
                        <div class="flex items-center space-x-3 px-2 py-3 border-b border-gray-800">
                            <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-700">
                            <div>
                                <p class="font-semibold text-white"><?php echo htmlspecialchars($userName); ?></p>
                                <p class="text-xs text-gray-400"><?php echo ucfirst($userRole); ?></p>
                            </div>
                        </div>
                        <a href="?panel=<?php echo $userRole; ?>&page=dashboard" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Dashboard</a>
                        <a href="#categories" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Categories</a>
                        <a href="#how-it-works" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">How It Works</a>
                        <a href="#experts" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Top Experts</a>
                        <button id="home-logout-btn-mobile" class="text-left text-gray-300 hover:text-red-500 transition px-2 py-2">Logout</button>
                    <?php else: ?>
                        <!-- Guest mobile menu -->
                        <a href="#categories" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Categories</a>
                        <a href="#how-it-works" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">How It Works</a>
                        <a href="#experts" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Top Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-gray-800 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition text-center">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="border border-gray-700 text-white px-6 py-3 rounded-lg hover:bg-white hover:text-black transition text-center">Apply as Expert</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'learner'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner';
    $currentPage = $_GET['page'] ?? '';
    
    // Get learner profile data if logged in - prioritize session data
    $learnerName = '';
    $learnerPhoto = BASE_PATH . '/attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    if ($isLoggedIn) {
        $learnerName = $_SESSION['full_name'] ?? 'Learner';
        
        // Check session photo first (most up-to-date after save)
        if (!empty($_SESSION['profile_photo'])) {
            $photoPath = $_SESSION['profile_photo'];
            if (!preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
                $learnerPhoto = BASE_PATH . $photoPath;
            } else {
                $learnerPhoto = $photoPath;
            }
        } elseif (isset($pdo)) {
            // Fallback to database if session doesn't have photo
            try {
                $stmt = $pdo->prepare("SELECT lp.full_name, lp.profile_photo FROM learner_profiles lp WHERE lp.user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $learnerProfile = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($learnerProfile) {
                    $learnerName = $learnerProfile['full_name'] ?? $learnerName;
                    if (!empty($learnerProfile['profile_photo'])) {
                        $photoPath = $learnerProfile['profile_photo'];
                        if (!preg_match('/^(https?:\/\/|data:)/', $photoPath)) {
                            $learnerPhoto = BASE_PATH . $photoPath;
                        } else {
                            $learnerPhoto = $photoPath;
                        }
                        // Update session with database photo for next page load
                        $_SESSION['profile_photo'] = $learnerProfile['profile_photo'];
                    }
                }
            } catch (Exception $e) {
                // Keep default values
            }
        }
    }
?>
    <nav class="bg-[#080B10] border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="text-xl sm:text-2xl font-bold text-white hover:text-[#00D4AA] transition">Nexpert.ai</a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=learner&page=dashboard" class="text-gray-300 hover:text-[#00D4AA] transition">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] transition">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="text-gray-300 hover:text-[#00D4AA] transition">My Programs</a>
                        <a href="?panel=learner&page=profile" class="text-gray-300 hover:text-[#00D4AA] transition">Profile</a>
                        <a href="?panel=learner&page=messages" class="relative text-gray-300 hover:text-[#00D4AA] transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="learner-unread-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </a>
                        <div class="flex items-center space-x-2">
                            <img src="<?php echo htmlspecialchars($learnerPhoto); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-gray-700">
                            <span class="text-sm font-medium text-gray-300"><?php echo htmlspecialchars($learnerName); ?></span>
                        </div>
                        <button id="learner-logout-btn" class="text-gray-400 hover:text-red-500 transition flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-sm font-medium">Logout</span>
                        </button>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] transition">Browse Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-[#00D4AA] text-[#080B10] px-6 py-2 rounded-xl hover:bg-[#00bda0] font-bold transition">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="border border-gray-800 text-white px-6 py-2 rounded-xl hover:bg-[#131b2e] hover:border-gray-700 transition font-semibold">Apply as Expert</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="learner-mobile-menu-btn" class="md:hidden p-2 text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="learner-mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-3 px-2 py-3 border-b border-gray-800">
                            <img src="<?php echo htmlspecialchars($learnerPhoto); ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-700">
                            <div>
                                <p class="font-semibold text-white"><?php echo htmlspecialchars($learnerName); ?></p>
                                <p class="text-xs text-gray-400">Learner</p>
                            </div>
                        </div>
                        <a href="?panel=learner&page=dashboard" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">My Programs</a>
                        <a href="?panel=learner&page=profile" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Profile</a>
                        <div class="flex items-center justify-between px-2 py-2">
                            <span class="text-gray-300">Notifications</span>
                            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </div>
                        <button id="learner-logout-btn-mobile" class="text-left text-gray-400 hover:text-red-500 transition px-2 py-2">Logout</button>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-300 hover:text-[#00D4AA] transition px-2 py-2">Browse Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-[#00D4AA] text-[#080B10] px-6 py-3 rounded-lg hover:bg-[#00bda0] transition text-center font-bold">Learner Login</a>
                        <a href="?panel=expert&page=apply" class="border border-gray-800 text-white px-6 py-3 rounded-lg hover:bg-[#131b2e] hover:border-gray-700 transition text-center font-semibold">Apply as Expert</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'expert'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
    
    // Get expert profile data if logged in
    $expertName = '';
    $expertPhoto = 'attached_assets/stock_images/diverse_professional_1d96e39f.jpg';
    if ($isLoggedIn && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT ep.full_name, ep.profile_photo FROM expert_profiles ep WHERE ep.user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $expertProfile = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($expertProfile) {
                $expertName = $expertProfile['full_name'] ?? $_SESSION['full_name'] ?? 'Expert';
                if (!empty($expertProfile['profile_photo'])) {
                    $expertPhoto = $expertProfile['profile_photo'];
                }
            } else {
                $expertName = $_SESSION['full_name'] ?? 'Expert';
            }
        } catch (Exception $e) {
            $expertName = $_SESSION['full_name'] ?? 'Expert';
        }
    } elseif ($isLoggedIn) {
        $expertName = $_SESSION['full_name'] ?? 'Expert';
    }
?>
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="<?php echo BASE_PATH; ?>/index.php" class="text-xl sm:text-2xl font-bold text-accent hover:text-yellow-600 transition">Nexpert.ai</a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=expert&page=dashboard" class="text-gray-600 hover:text-accent">Dashboard</a>
                        <a href="?panel=expert&page=my-programs" class="text-gray-600 hover:text-accent">My Programs</a>
                        <a href="?panel=expert&page=my-webinars" class="text-gray-600 hover:text-accent">My Webinars</a>
                        <a href="?panel=expert&page=earnings" class="text-gray-600 hover:text-accent">Earnings</a>
                        <a href="?panel=expert&page=booking-management" class="text-gray-600 hover:text-accent">Bookings</a>
                        <a href="?panel=expert&page=learner-management" class="text-gray-600 hover:text-accent">Learners</a>
                        <a href="?panel=expert&page=messages" class="relative text-gray-600 hover:text-accent">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="expert-unread-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </a>
                        <div class="flex items-center space-x-2">
                            <a href="?panel=expert&page=settings" class="block">
                                <img id="expert-nav-photo" src="<?php echo htmlspecialchars($expertPhoto); ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-accent transition border-2 border-gray-200">
                            </a>
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($expertName); ?></span>
                        </div>
                        <button id="expert-logout-btn" class="text-gray-600 hover:text-red-600 transition flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-sm font-medium">Logout</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="expert-mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-accent">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="expert-mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <?php if ($isLoggedIn): ?>
                        <div class="flex items-center space-x-3 px-2 py-3 border-b border-gray-200">
                            <img src="<?php echo htmlspecialchars($expertPhoto); ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200">
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($expertName); ?></p>
                                <p class="text-xs text-gray-500">Expert</p>
                            </div>
                        </div>
                        <a href="?panel=expert&page=dashboard" class="text-gray-600 hover:text-accent px-2 py-2">Dashboard</a>
                        <a href="?panel=expert&page=my-programs" class="text-gray-600 hover:text-accent px-2 py-2">My Programs</a>
                        <a href="?panel=expert&page=my-webinars" class="text-gray-600 hover:text-accent px-2 py-2">My Webinars</a>
                        <a href="?panel=expert&page=earnings" class="text-gray-600 hover:text-accent px-2 py-2">Earnings</a>
                        <a href="?panel=expert&page=booking-management" class="text-gray-600 hover:text-accent px-2 py-2">Bookings</a>
                        <a href="?panel=expert&page=learner-management" class="text-gray-600 hover:text-accent px-2 py-2">Learners</a>
                        <a href="?panel=expert&page=settings" class="text-gray-600 hover:text-accent px-2 py-2">Settings</a>
                        <button id="expert-logout-btn-mobile" class="text-left text-gray-600 hover:text-red-600 transition px-2 py-2">Logout</button>
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
            if (result.success && result.data.profile_photo) {
                expertNavPhoto.src = result.data.profile_photo;
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
