<?php
require_once 'includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ?panel=learner&page=auth');
    exit;
}

$page_title = "My Programs - Nexpert.ai";
$panel_type = "learner";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Programs</h1>
        <p class="text-gray-600">Track your enrolled programs, view assignments, and monitor your progress</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Active Programs</p>
                </div>
                <div class="p-3 bg-primary rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-gray-900">0</p>
                    <p class="text-gray-600 text-sm mt-1">Pending Assignments</p>
                </div>
                <div class="p-3 bg-amber-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-gray-900">0%</p>
                    <p class="text-gray-600 text-sm mt-1">Overall Progress</p>
                </div>
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Programs List -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Enrolled Programs</h2>
            <a href="?panel=learner&page=browse-experts" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition">
                Browse Experts
            </a>
        </div>

        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="inline-block p-4 bg-gray-100 rounded-full mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Programs Yet</h3>
            <p class="text-gray-600 mb-6">Start your learning journey by enrolling in programs with our expert instructors</p>
            <a href="?panel=learner&page=browse-experts" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition">
                Find an Expert
            </a>
        </div>
    </div>

    <!-- Upcoming Assignments Section -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Upcoming Assignments</h2>
        
        <!-- Empty State -->
        <div class="text-center py-8">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <p class="text-gray-500">No assignments due</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
