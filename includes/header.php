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
    
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        
        window.getInitials = function(name) {
            if (!name) return 'EX';
            var words = name.trim().split(/\s+/);
            if (words.length >= 2) {
                return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        };
        
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        primary: '#00D4AA',
                        secondary: '#131B2E',
                        accent: '#00D4AA',
                        brand: {
                            dark: '#080B10',
                            surface: '#0D131F',
                            elevated: '#131B2E',
                            border: '#1E293B',
                            emerald: '#00D4AA',
                            emeraldDark: '#00BFA0',
                            sovereign: '#10B981',
                            established: '#00D4AA',
                            verified: '#38BDF8',
                            emerging: '#818CF8',
                            unverified: '#64748B'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #080B10;
            color: #F3F4F6;
        }
        
        /* Custom Modern Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #080B10;
        }
        ::-webkit-scrollbar-thumb {
            background: #1E293B;
            border-radius: 9999px;
            border: 2px solid #080B10;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Ambient Glass Card Styles */
        .glass-card {
            background: rgba(13, 19, 31, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
        .glass-card-hover {
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-card-hover:hover {
            border-color: rgba(0, 212, 170, 0.3);
            box-shadow: 0 10px 30px -10px rgba(0, 212, 170, 0.1);
            transform: translateY(-2px);
        }

        /* Subtle Shimmer Animation for Skeletons */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0.03) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.03) 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-16px); }
        }
        @keyframes float-delay {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
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
<body class="bg-[#080B10] text-gray-100 min-h-screen font-sans antialiased selection:bg-[#00D4AA]/20 selection:text-[#00D4AA]">

    <!-- Global Page Preloader -->
    <div id="nexpert-page-loader" class="fixed inset-0 z-[99999] bg-[#080B10] flex flex-col items-center justify-center transition-opacity duration-300 pointer-events-none">
        <div class="relative flex items-center justify-center">
            <!-- Glowing Orbit Spinner -->
            <div class="w-14 h-14 rounded-full border-2 border-[#00D4AA]/15 border-t-[#00D4AA] animate-spin shadow-[0_0_20px_rgba(0,212,170,0.2)]"></div>
            <!-- Central Brand Badge -->
            <div class="absolute w-8 h-8 bg-[#0D131F] border border-[#00D4AA]/30 rounded-xl flex items-center justify-center font-black text-[#00D4AA] text-sm shadow-[0_0_12px_rgba(0,212,170,0.3)]">
                N
            </div>
        </div>
        <div class="mt-4 flex items-center gap-1.5">
            <span class="text-[11px] font-bold text-gray-400 font-mono tracking-widest uppercase">NEXPERT</span>
            <span class="inline-flex space-x-1">
                <span class="w-1 h-1 bg-[#00D4AA] rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                <span class="w-1 h-1 bg-[#00D4AA] rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                <span class="w-1 h-1 bg-[#00D4AA] rounded-full animate-bounce"></span>
            </span>
        </div>
    </div>

    <script>
        (function() {
            function hidePageLoader() {
                var loader = document.getElementById('nexpert-page-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    setTimeout(function() {
                        if (loader && loader.parentNode) {
                            loader.style.display = 'none';
                        }
                    }, 300);
                }
            }

            if (document.readyState === 'complete') {
                hidePageLoader();
            } else {
                window.addEventListener('load', hidePageLoader);
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(hidePageLoader, 100);
                });
                window.addEventListener('pageshow', hidePageLoader);
                setTimeout(hidePageLoader, 1500); // Safety fallback
            }
        })();
    </script>
