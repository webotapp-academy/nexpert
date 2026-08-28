<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (isset($panel_type) && $panel_type === 'admin' && isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <?php endif; ?>
    <title><?php echo isset($page_title) ? $page_title : 'Nexpert.ai - Global Expert Learning Platform'; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Connect with expert coaches, mentors, and consultants. Accelerate your learning journey with personalized guidance from industry professionals.'; ?>">
    <meta name="keywords" content="expert learning, online mentorship, coaching, professional development, skill development">
    <meta name="author" content="Nexpert.ai">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : 'Nexpert.ai - Global Expert Learning Platform'; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'Connect with expert coaches, mentors, and consultants. Accelerate your learning journey with personalized guidance.'; ?>">
    <meta property="og:image" content="<?php echo BASE_URL; ?>/attached_assets/og-image.jpg?v=<?php echo time(); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Nexpert.ai - Expert Learning Platform">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo BASE_URL . $_SERVER['REQUEST_URI']; ?>">
    <meta property="twitter:title" content="<?php echo isset($page_title) ? $page_title : 'Nexpert.ai - Global Expert Learning Platform'; ?>">
    <meta property="twitter:description" content="<?php echo isset($page_description) ? $page_description : 'Connect with expert coaches, mentors, and consultants. Accelerate your learning journey.'; ?>">
    <meta property="twitter:image" content="<?php echo BASE_URL; ?>/attached_assets/og-image.jpg?v=<?php echo time(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_PATH; ?>/attached_assets/favicon.ico">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                        accent: '#F59E0B',
                        saffron: '#FF6B35'
                    }
                }
            }
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes float-delay {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-float-delay {
            animation: float-delay 8s ease-in-out infinite;
        }
        .animate-float-slow {
            animation: float-slow 10s ease-in-out infinite;
        }
        .animate-pulse-slow {
            animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
    <!-- Expert Panel JavaScript -->
    <script src="<?php echo BASE_PATH; ?>/admin-panel/js/expert-panel.js"></script>
    <!-- Save Redirect URL Before Login -->
    <script src="<?php echo BASE_PATH; ?>/admin-panel/js/save-redirect-url.js"></script>
    <?php if (isset($panel_type) && $panel_type === 'admin'): ?>
    <!-- Admin API JavaScript -->
    <script src="<?php echo BASE_PATH; ?>/admin-panel/js/admin-api.js"></script>
    <script src="<?php echo BASE_PATH; ?>/admin-panel/js/admin-logout.js"></script>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen">
