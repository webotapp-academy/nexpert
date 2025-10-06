<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (isset($panel_type) && $panel_type === 'admin' && isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <?php endif; ?>
    <title><?php echo isset($page_title) ? $page_title : 'Nexpert.ai - Global Expert Learning Platform'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                        accent: '#F59E0B',
                        saffron: '#FF6B35',
                        emerald: '#10B981'
                    }
                }
            }
        }
    </script>
    <!-- Expert Panel JavaScript -->
    <script src="/admin-panel/js/expert-panel.js"></script>
    <?php if (isset($panel_type) && $panel_type === 'admin'): ?>
    <!-- Admin API JavaScript -->
    <script src="/admin-panel/js/admin-api.js"></script>
    <script src="/admin-panel/js/admin-logout.js"></script>
    <?php endif; ?>
</head>
<body class="bg-gray-50 min-h-screen">
