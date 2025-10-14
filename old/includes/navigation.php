<?php
$panel_type = isset($panel_type) ? $panel_type : 'home';

if ($panel_type === 'home'): ?>
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Nexpert.ai</h1>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#categories" class="text-gray-600 hover:text-primary transition">Categories</a>
                    <a href="#how-it-works" class="text-gray-600 hover:text-primary transition">How It Works</a>
                    <a href="#experts" class="text-gray-600 hover:text-primary transition">Top Experts</a>
                    <a href="#testimonials" class="text-gray-600 hover:text-primary transition">Success Stories</a>
                    <a href="?panel=learner&page=auth" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition">Learner Login</a>
                    <a href="?panel=expert&page=auth" class="bg-accent text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition">Expert Login</a>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="home-mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="home-mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="#categories" class="text-gray-600 hover:text-primary transition px-2 py-2">Categories</a>
                    <a href="#how-it-works" class="text-gray-600 hover:text-primary transition px-2 py-2">How It Works</a>
                    <a href="#experts" class="text-gray-600 hover:text-primary transition px-2 py-2">Top Experts</a>
                    <a href="#testimonials" class="text-gray-600 hover:text-primary transition px-2 py-2">Success Stories</a>
                    <a href="?panel=learner&page=auth" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition text-center">Learner Login</a>
                    <a href="?panel=expert&page=auth" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition text-center">Expert Login</a>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'learner'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'learner';
    $currentPage = $_GET['page'] ?? '';
?>
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="/" class="text-xl sm:text-2xl font-bold text-primary">Nexpert.ai</a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=learner&page=dashboard" class="text-gray-600 hover:text-primary">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-600 hover:text-primary">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="text-gray-600 hover:text-primary">My Programs</a>
                        <a href="?panel=learner&page=profile" class="text-gray-600 hover:text-primary">Profile</a>
                        <div class="relative">
                            <button class="text-gray-600 hover:text-primary relative">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-3a1 1 0 011-1h3a1 1 0 011 1v3z"></path>
                                </svg>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                            </button>
                        </div>
                        <img src="attached_assets/stock_images/professional_busines_a5bb892c.jpg" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                        <button id="learner-logout-btn" class="text-gray-600 hover:text-red-600 transition flex items-center space-x-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-sm font-medium">Logout</span>
                        </button>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-600 hover:text-primary">Browse Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition">Learner Login</a>
                        <a href="?panel=expert&page=auth" class="bg-accent text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition">Expert Login</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Hamburger Button -->
                <button id="learner-mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="learner-mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=learner&page=dashboard" class="text-gray-600 hover:text-primary px-2 py-2">Dashboard</a>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-600 hover:text-primary px-2 py-2">Browse Experts</a>
                        <a href="?panel=learner&page=my-programs" class="text-gray-600 hover:text-primary px-2 py-2">My Programs</a>
                        <a href="?panel=learner&page=profile" class="text-gray-600 hover:text-primary px-2 py-2">Profile</a>
                        <div class="flex items-center justify-between px-2 py-2">
                            <span class="text-gray-600">Notifications</span>
                            <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                        </div>
                        <button id="learner-logout-btn-mobile" class="text-left text-gray-600 hover:text-red-600 transition px-2 py-2">Logout</button>
                    <?php else: ?>
                        <a href="?panel=learner&page=browse-experts" class="text-gray-600 hover:text-primary px-2 py-2">Browse Experts</a>
                        <a href="?panel=learner&page=auth" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition text-center">Learner Login</a>
                        <a href="?panel=expert&page=auth" class="bg-accent text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition text-center">Expert Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
<?php elseif ($panel_type === 'expert'): 
    $isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert';
?>
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="/" class="text-xl sm:text-2xl font-bold text-accent">Nexpert.ai</a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($isLoggedIn): ?>
                        <a href="?panel=expert&page=dashboard" class="text-gray-600 hover:text-accent">Dashboard</a>
                        <a href="?panel=expert&page=my-programs" class="text-gray-600 hover:text-accent">My Programs</a>
                        <a href="?panel=expert&page=earnings" class="text-gray-600 hover:text-accent">Earnings</a>
                        <a href="?panel=expert&page=booking-management" class="text-gray-600 hover:text-accent">Bookings</a>
                        <a href="?panel=expert&page=learner-management" class="text-gray-600 hover:text-accent">Learners</a>
                        <div class="relative">
                            <button class="text-gray-600 hover:text-accent">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-3a1 1 0 011-1h3a1 1 0 011 1v3z"></path>
                                </svg>
                            </button>
                        </div>
                        <a href="?panel=expert&page=settings" class="block">
                            <img id="expert-nav-photo" src="attached_assets/stock_images/diverse_professional_478267b3.jpg" alt="Profile" class="w-8 h-8 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-accent transition">
                        </a>
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
                        <a href="?panel=expert&page=dashboard" class="text-gray-600 hover:text-accent px-2 py-2">Dashboard</a>
                        <a href="?panel=expert&page=my-programs" class="text-gray-600 hover:text-accent px-2 py-2">My Programs</a>
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
</script>
