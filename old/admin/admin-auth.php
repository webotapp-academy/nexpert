<?php
// Define BASE_PATH
$BASE_PATH = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$BASE_PATH = $BASE_PATH ? $BASE_PATH : '/';

// Include session configuration
require_once $_SERVER['DOCUMENT_ROOT'] . '/nexpert/includes/session-config.php';

// Check if already logged in as admin
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: ' . $BASE_PATH . '/index.php?panel=admin&page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Nexpert.ai</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                        secondary: '#1E40AF',
                        accent: '#F59E0B'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-primary mb-2">Nexpert.ai</h1>
                <h2 class="text-2xl font-semibold text-gray-800">Admin Portal</h2>
                <p class="mt-2 text-gray-600">Secure admin access only</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <!-- Demo Credentials -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="text-sm text-blue-800">
                        <strong>Demo Admin Credentials:</strong><br>
                        Email: admin@nexpert.ai<br>
                        Password: admin123
                    </div>
                </div>

                <form id="adminLoginForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input id="adminEmail" type="email" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                            placeholder="Enter admin email" 
                            value="admin@nexpert.ai">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input id="adminPassword" type="password" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" 
                            placeholder="Enter password" 
                            value="admin123">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" 
                        class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary transition font-semibold">
                        Sign In to Admin Portal
                    </button>
                </form>

                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <a href="<?php echo $BASE_PATH; ?>/index.php" class="text-sm text-primary hover:text-secondary">← Back to Homepage</a>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>🔒 Secure admin authentication</p>
            </div>
        </div>
    </div>

    <script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo $BASE_PATH; ?>';

    document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('adminEmail').value;
        const password = document.getElementById('adminPassword').value;
        
        // Disable submit button and show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <span class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing In...
            </span>
        `;
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email, password })
            });
            
            const result = await response.json();
            
            // Restore button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign In to Admin Portal';
            
            if (result.success) {
                // Use SweetAlert for success notification
                await Swal.fire({
                    icon: 'success',
                    title: 'Login Successful',
                    text: 'Redirecting to Admin Dashboard...',
                    showConfirmButton: false,
                    timer: 1500
                });
                
                window.location.href = `${window.BASE_PATH}/index.php?panel=admin&page=dashboard`;
            } else {
                // Use SweetAlert for error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: result.message || 'Invalid credentials',
                    confirmButtonColor: '#3085d6'
                });
            }
        } catch (error) {
            console.error('Login error:', error);
            
            // Restore button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Sign In to Admin Portal';
            
            // Use SweetAlert for network/unexpected errors
            Swal.fire({
                icon: 'error',
                title: 'Login Error',
                text: 'Login failed. Please check your connection and try again.',
                confirmButtonColor: '#3085d6'
            });
        }
    });
    </script>
</body>
</html>
