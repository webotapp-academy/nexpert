<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Use centralized session + config (defines BASE_PATH & BASE_URL constants)
require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Prevent redirect loop by checking current page
    $currentPage = $_SERVER['REQUEST_URI'];
    $authPage = BASE_PATH . '/index.php?panel=expert&page=auth';
    
    if ($currentPage !== $authPage) {
        // Save the current URL to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $authPage);
        exit;
    }
}

$page_title = "Expert Authentication - Nexpert.ai";
$panel_type = "home";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<script>
    // Set BASE_PATH (application root) globally for JS
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        // If it's a full URL or a data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        
        // If no image path, use a default
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        
        // Remove leading slashes
        const normalizedPath = imagePath.replace(/^\/+/, '');
        
        // Construct full path
        return `${window.BASE_PATH}/${normalizedPath}`;
    }
</script>

    <div class="min-h-screen flex bg-white">
        <!-- Left Side - Image/Branding -->
        <div class="hidden lg:block lg:w-1/2 bg-indigo-50 border-r border-indigo-100 relative overflow-hidden">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-emerald-400/20 rounded-full blur-3xl"></div>
            </div>
            <div class="flex flex-col justify-center h-full p-16 relative z-10">
                <div class="max-w-md mx-auto">
                    <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-8 border border-gray-100">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-4xl font-black mb-4 text-gray-900 tracking-tight">Share Your Expertise</h3>
                    <p class="text-lg text-gray-600 mb-10 leading-relaxed">Join thousands of experts helping learners worldwide achieve their goals.</p>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mr-4 border border-gray-100 shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold mb-1">Flexible Earnings</h4>
                                <p class="text-sm text-gray-500 leading-relaxed">Set your own rates and earn on your own schedule.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mr-4 border border-gray-100 shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold mb-1">Global Reach</h4>
                                <p class="text-sm text-gray-500 leading-relaxed">Connect with highly motivated learners from all over the world.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center mr-4 border border-gray-100 shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold mb-1">Analytics & Insights</h4>
                                <p class="text-sm text-gray-500 leading-relaxed">Track your performance and grow your reputation automatically.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-20 xl:px-24 bg-white relative">
            <div class="absolute inset-0 bg-white z-0 pointer-events-none"></div>
            <div class="w-full max-w-md relative z-10 bg-white rounded-2xl p-8 sm:p-10 border border-transparent shadow-none sm:border-gray-100 sm:shadow-xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Expert Portal</h2>
                    <p class="mt-2 text-sm text-gray-500 font-medium">Sign in or create your account</p>
                </div>

                <!-- Toggle Tabs -->
                <div class="flex p-1 bg-gray-100 rounded-lg mb-8">
                    <button id="signInTab" class="flex-1 py-2.5 text-sm text-center font-bold bg-white text-gray-900 rounded-md shadow-sm transition" onclick="switchTab('signin')">
                        Sign In
                    </button>
                    <button id="signUpTab" class="flex-1 py-2.5 text-sm text-center font-medium text-gray-500 hover:text-gray-900 rounded-md transition" onclick="switchTab('signup')">
                        Sign Up
                    </button>
                </div>

                <!-- Sign In Form -->
                <form id="signInForm" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email Address</label>
                        <input id="signInEmail" type="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="your@email.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Password</label>
                        <input id="signInPassword" type="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 bg-gray-50 border-gray-300 text-indigo-600 focus:ring-indigo-500 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-700 transition">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-3.5 px-4 rounded-xl hover:bg-indigo-700 transition duration-200 font-bold shadow-lg shadow-indigo-600/20">
                        Sign In
                    </button>
                </form>

                <!-- Sign Up Form -->
                <form id="signUpForm" class="space-y-5 hidden">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Full Name</label>
                        <input id="expertName" type="text" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="Enter your full name">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Mobile Number</label>
                        <div class="flex gap-2">
                            <select id="expertCountryCode" class="w-32 px-3 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium">
                                <option value="+91">🇮🇳 +91</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+61">🇦🇺 +61</option>
                                <option value="+86">🇨🇳 +86</option>
                                <option value="+81">🇯🇵 +81</option>
                                <option value="+49">🇩🇪 +49</option>
                                <option value="+33">🇫🇷 +33</option>
                                <option value="+971">🇦🇪 +971</option>
                                <option value="+65">🇸🇬 +65</option>
                                <option value="+60">🇲🇾 +60</option>
                                <option value="+966">🇸🇦 +966</option>
                            </select>
                            <input id="expertMobile" type="tel" required class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="9876543210">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Email Address</label>
                        <input id="expertEmail" type="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="your@email.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Password</label>
                        <input id="expertPassword" type="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="Create a password">
                        <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-1.5">
                                <span id="expertPasswordStrengthText" class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Password strength</span>
                            </div>
                            <div class="h-1.5 bg-gray-200 rounded-full mb-2.5">
                                <div id="expertPasswordStrengthBar" class="h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <ul class="text-[11px] text-gray-500 space-y-1 font-medium grid grid-cols-2 gap-x-2">
                                <li id="expertPwLength" class="flex items-center"><span class="mr-1.5 font-bold">○</span> 8+ chars</li>
                                <li id="expertPwUppercase" class="flex items-center"><span class="mr-1.5 font-bold">○</span> Uppercase</li>
                                <li id="expertPwLowercase" class="flex items-center"><span class="mr-1.5 font-bold">○</span> Lowercase</li>
                                <li id="expertPwNumber" class="flex items-center"><span class="mr-1.5 font-bold">○</span> Number</li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Repeat Password</label>
                        <input id="expertPasswordRepeat" type="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition placeholder-gray-400 font-medium" placeholder="Confirm your password">
                    </div>

                    <button type="submit" id="expertRegisterBtn" class="w-full bg-indigo-600 text-white py-3.5 px-4 rounded-xl hover:bg-indigo-700 transition duration-200 font-bold flex items-center justify-center shadow-lg shadow-indigo-600/20">
                        <span id="expertRegisterText">Create Account & Continue</span>
                        <svg id="expertRegisterSpinner" class="hidden animate-spin ml-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <div class="mt-8">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-white text-gray-400 font-bold uppercase tracking-wider">Or continue with</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" id="googleSignInBtn" class="w-full bg-white border border-gray-200 text-gray-700 py-3 px-4 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition duration-200 flex items-center justify-center font-bold shadow-sm">
                            <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Sign in with Google
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    // Tab switching
    function switchTab(tab) {
        const signInTab = document.getElementById('signInTab');
        const signUpTab = document.getElementById('signUpTab');
        const signInForm = document.getElementById('signInForm');
        const signUpForm = document.getElementById('signUpForm');

        if (tab === 'signin') {
            signInTab.classList.add('border-[#00D4AA]', 'text-indigo-600');
            signInTab.classList.remove('border-transparent', 'text-gray-400');
            signUpTab.classList.remove('border-[#00D4AA]', 'text-indigo-600');
            signUpTab.classList.add('border-transparent', 'text-gray-400');
            signInForm.classList.remove('hidden');
            signUpForm.classList.add('hidden');
        } else {
            signUpTab.classList.add('border-[#00D4AA]', 'text-indigo-600');
            signUpTab.classList.remove('border-transparent', 'text-gray-400');
            signInTab.classList.remove('border-[#00D4AA]', 'text-indigo-600');
            signInTab.classList.add('border-transparent', 'text-gray-400');
            signUpForm.classList.remove('hidden');
            signInForm.classList.add('hidden');
        }
    }

    // Sign In Form Handler
    document.getElementById('signInForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('signInEmail').value.trim();
        const password = document.getElementById('signInPassword').value;
        
        if (!email || !password) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in all fields',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        
        try {
            const response = await fetch(window.BASE_PATH + '/admin-panel/apis/expert/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json().catch(() => null);
            
            console.log('[Expert Login] Raw response:', result);
            if (result && result.success) {
                // Successful login
                await Swal.fire({
                    icon: 'success',
                    title: 'Welcome Back!',
                    text: 'Login successful',
                    confirmButtonColor: '#4f46e5',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Check if expert needs to complete KYC
                let target;
                if (result.user.verification_status === 'pending' || !result.user.verification_status) {
                    target = window.BASE_PATH + '/index.php?panel=expert&page=kyc';
                } else {
                    target = window.BASE_PATH + '/index.php?panel=expert&page=dashboard';
                }
                console.log('[Expert Login] Redirecting to:', target, 'BASE_PATH:', window.BASE_PATH, 'verification_status:', result.user.verification_status);
                window.location.href = target;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: (result && result.message) ? result.message : 'Invalid email or password. Please try again.',
                    confirmButtonColor: '#4f46e5'
                });
                console.warn('[Expert Login] Failure message:', result ? result.message : 'No response');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Unable to reach the server. Please check your connection and try again.',
                confirmButtonColor: '#4f46e5'
            });
        }
    });

    // Sign Up Form Handler
    document.getElementById('signUpForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const name = document.getElementById('expertName').value.trim();
        const countryCode = document.getElementById('expertCountryCode').value;
        const mobile = document.getElementById('expertMobile').value.trim();
        const email = document.getElementById('expertEmail').value.trim();
        const password = document.getElementById('expertPassword').value;
        const passwordRepeat = document.getElementById('expertPasswordRepeat').value;
        
        if (!name || !mobile || !email || !password || !passwordRepeat) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in all required fields',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        
        if (password.length < 8) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        
        if (password !== passwordRepeat) {
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Passwords do not match. Please try again.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        
        // Combine country code with mobile number
        const fullMobile = countryCode + mobile;
        
        // Show loading spinner
        const btn = document.getElementById('expertRegisterBtn');
        const btnText = document.getElementById('expertRegisterText');
        const spinner = document.getElementById('expertRegisterSpinner');
        btn.disabled = true;
        btnText.textContent = 'Creating Account...';
        spinner.classList.remove('hidden');
        
        try {
            const response = await fetch(window.BASE_PATH + '/admin-panel/apis/expert/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    name: name,
                    mobile: fullMobile,
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json().catch(() => null);
            
            if (result && result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Welcome to Nexpert.ai!',
                    text: 'Registration successful! Please complete your profile setup.',
                    confirmButtonColor: '#4f46e5',
                    timer: 2000
                });
                window.location.href = window.BASE_PATH + '/index.php?panel=expert&page=kyc';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: (result && result.message) ? result.message : 'Registration failed. Please try again.',
                    confirmButtonColor: '#4f46e5'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during registration. Please try again.',
                confirmButtonColor: '#4f46e5'
            });
        } finally {
            // Hide loading spinner
            btn.disabled = false;
            btnText.textContent = 'Create Account';
            spinner.classList.add('hidden');
        }
    });

    // Password strength checker for expert registration
    const expertPasswordInput = document.getElementById('expertPassword');
    if (expertPasswordInput) {
        expertPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('expertPasswordStrengthBar');
            const strengthText = document.getElementById('expertPasswordStrengthText');
            
            const lengthCheck = document.getElementById('expertPwLength');
            const uppercaseCheck = document.getElementById('expertPwUppercase');
            const lowercaseCheck = document.getElementById('expertPwLowercase');
            const numberCheck = document.getElementById('expertPwNumber');
            
            let strength = 0;
            
            // Check length
            if (password.length >= 8) {
                lengthCheck.classList.add('text-indigo-600');
                lengthCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                lengthCheck.classList.remove('text-indigo-600');
                lengthCheck.querySelector('span').textContent = '○';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                uppercaseCheck.classList.add('text-indigo-600');
                uppercaseCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                uppercaseCheck.classList.remove('text-indigo-600');
                uppercaseCheck.querySelector('span').textContent = '○';
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                lowercaseCheck.classList.add('text-indigo-600');
                lowercaseCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                lowercaseCheck.classList.remove('text-indigo-600');
                lowercaseCheck.querySelector('span').textContent = '○';
            }
            
            // Check number
            if (/[0-9]/.test(password)) {
                numberCheck.classList.add('text-indigo-600');
                numberCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                numberCheck.classList.remove('text-indigo-600');
                numberCheck.querySelector('span').textContent = '○';
            }
            
            // Update strength bar and text
            const percentage = (strength / 4) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (strength === 0) {
                strengthBar.className = 'h-full rounded-full transition-all duration-300';
                strengthText.textContent = 'Password strength';
                strengthText.className = 'text-xs font-medium text-gray-500';
            } else if (strength <= 2) {
                strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-red-500';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs font-medium text-red-600';
            } else if (strength === 3) {
                strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-yellow-500';
                strengthText.textContent = 'Medium';
                strengthText.className = 'text-xs font-medium text-yellow-600';
            } else {
                strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-[#00D4AA]';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs font-medium text-indigo-600';
            }
        });
    }

    // Google Sign In Button Handler
    document.getElementById('googleSignInBtn').addEventListener('click', async function() {
        try {
            // Generate state parameter for CSRF protection
            const state = Math.random().toString(36).substring(2, 18);
            
            // Store state and role in session via API
            const response = await fetch(window.BASE_PATH + '/admin-panel/apis/oauth/init-google-oauth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    state: state,
                    role: 'expert'
                })
            });
            
            const result = await response.json();
            
            if (result.success && result.auth_url) {
                // Redirect to Google OAuth
                window.location.href = result.auth_url;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Configuration Error',
                    text: result.message || 'Google Sign In is not configured. Please contact support.',
                    confirmButtonColor: '#4f46e5'
                });
            }
        } catch (error) {
            console.error('Google Sign In error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to initiate Google Sign In. Please try again.',
                confirmButtonColor: '#4f46e5'
            });
        }
    });
    </script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
