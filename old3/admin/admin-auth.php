<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Nexpert.ai</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <a href="/" class="text-sm text-primary hover:text-secondary">← Back to Homepage</a>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>🔒 Secure admin authentication</p>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('adminEmail').value;
        const password = document.getElementById('adminPassword').value;
        
        try {
            const response = await fetch('/admin-panel/apis/admin/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '?panel=admin&page=dashboard';
            } else {
                alert(result.message || 'Invalid credentials');
            }
        } catch (error) {
            console.error('Login error:', error);
            alert('Login failed. Please try again.');
        }
    });
    </script>
</body>
</html>
