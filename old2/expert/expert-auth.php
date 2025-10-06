<?php
$page_title = "Expert Auth - Nexpert.ai";
$panel_type = "expert";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

    <div class="min-h-screen flex">
        <!-- Left Side - Image/Branding -->
        <div class="hidden lg:block lg:w-1/2 bg-gradient-to-br from-accent to-yellow-600">
            <div class="flex items-center justify-center h-full text-white p-12">
                <div class="text-center">
                    <h3 class="text-4xl font-bold mb-6">Share Your Expertise</h3>
                    <p class="text-xl opacity-90 mb-8">Join thousands of experts helping learners worldwide</p>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            <span>Flexible Earnings</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Global Reach</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span>Analytics & Insights</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-20 xl:px-24">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Expert Portal</h2>
                    <p class="mt-2 text-gray-600">Sign in or create your account</p>
                </div>

                <!-- Toggle Tabs -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button id="signInTab" class="flex-1 py-3 text-center font-medium border-b-2 border-accent text-accent" onclick="switchTab('signin')">
                        Sign In
                    </button>
                    <button id="signUpTab" class="flex-1 py-3 text-center font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700" onclick="switchTab('signup')">
                        Sign Up
                    </button>
                </div>

                <!-- Sign In Form -->
                <form id="signInForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input id="signInEmail" type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="your@email.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input id="signInPassword" type="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm text-accent hover:text-yellow-600">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full bg-accent text-white py-3 px-4 rounded-lg hover:bg-yellow-600 transition duration-200 font-semibold">
                        Sign In
                    </button>
                </form>

                <!-- Sign Up Form -->
                <form id="signUpForm" class="space-y-6 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input id="expertName" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Enter your full name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                        <div class="flex gap-2">
                            <select id="expertCountryCode" class="w-32 px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent">
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
                            <input id="expertMobile" type="tel" required class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="9876543210">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input id="expertEmail" type="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="your@email.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input id="expertPassword" type="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Create a password">
                        <div class="mt-2">
                            <div class="flex items-center justify-between mb-1">
                                <span id="expertPasswordStrengthText" class="text-xs font-medium text-gray-500">Password strength</span>
                            </div>
                            <div class="h-2 bg-gray-200 rounded-full">
                                <div id="expertPasswordStrengthBar" class="h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <ul class="mt-2 text-xs text-gray-600 space-y-1">
                                <li id="expertPwLength" class="flex items-center"><span class="mr-2">○</span> At least 8 characters</li>
                                <li id="expertPwUppercase" class="flex items-center"><span class="mr-2">○</span> One uppercase letter</li>
                                <li id="expertPwLowercase" class="flex items-center"><span class="mr-2">○</span> One lowercase letter</li>
                                <li id="expertPwNumber" class="flex items-center"><span class="mr-2">○</span> One number</li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Repeat Password *</label>
                        <input id="expertPasswordRepeat" type="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent" placeholder="Confirm your password">
                    </div>

                    <button type="submit" id="expertRegisterBtn" class="w-full bg-accent text-white py-3 px-4 rounded-lg hover:bg-yellow-600 transition duration-200 font-semibold flex items-center justify-center">
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
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button type="button" class="w-full bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition duration-200 flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Sign in with Google
                        </button>

                        <button type="button" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition duration-200">
                            Sign in with OTP
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
            signInTab.classList.add('border-accent', 'text-accent');
            signInTab.classList.remove('border-transparent', 'text-gray-500');
            signUpTab.classList.remove('border-accent', 'text-accent');
            signUpTab.classList.add('border-transparent', 'text-gray-500');
            signInForm.classList.remove('hidden');
            signUpForm.classList.add('hidden');
        } else {
            signUpTab.classList.add('border-accent', 'text-accent');
            signUpTab.classList.remove('border-transparent', 'text-gray-500');
            signInTab.classList.remove('border-accent', 'text-accent');
            signInTab.classList.add('border-transparent', 'text-gray-500');
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
                confirmButtonColor: '#F59E0B'
            });
            return;
        }
        
        try {
            const response = await fetch('/admin-panel/apis/expert/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Successful login
                await Swal.fire({
                    icon: 'success',
                    title: 'Welcome Back!',
                    text: 'Login successful',
                    confirmButtonColor: '#F59E0B',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Check if expert needs to complete KYC
                if (result.user.verification_status === 'pending' || !result.user.verification_status) {
                    window.location.href = '?panel=expert&page=kyc';
                } else {
                    window.location.href = '?panel=expert&page=dashboard';
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: result.message,
                    confirmButtonColor: '#F59E0B'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during login. Please try again.',
                confirmButtonColor: '#F59E0B'
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
                confirmButtonColor: '#F59E0B'
            });
            return;
        }
        
        if (password.length < 8) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long',
                confirmButtonColor: '#F59E0B'
            });
            return;
        }
        
        if (password !== passwordRepeat) {
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Passwords do not match. Please try again.',
                confirmButtonColor: '#F59E0B'
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
            const response = await fetch('/admin-panel/apis/expert/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    mobile: fullMobile,
                    email: email,
                    password: password
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Welcome to Nexpert.ai!',
                    text: 'Registration successful! Please complete your profile setup.',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000
                });
                window.location.href = '?panel=expert&page=settings#profile';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: result.message,
                    confirmButtonColor: '#F59E0B'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during registration. Please try again.',
                confirmButtonColor: '#F59E0B'
            });
        } finally {
            // Hide loading spinner
            btn.disabled = false;
            btnText.textContent = 'Create Account & Continue';
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
                lengthCheck.classList.add('text-green-600');
                lengthCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                lengthCheck.classList.remove('text-green-600');
                lengthCheck.querySelector('span').textContent = '○';
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                uppercaseCheck.classList.add('text-green-600');
                uppercaseCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                uppercaseCheck.classList.remove('text-green-600');
                uppercaseCheck.querySelector('span').textContent = '○';
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                lowercaseCheck.classList.add('text-green-600');
                lowercaseCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                lowercaseCheck.classList.remove('text-green-600');
                lowercaseCheck.querySelector('span').textContent = '○';
            }
            
            // Check number
            if (/[0-9]/.test(password)) {
                numberCheck.classList.add('text-green-600');
                numberCheck.querySelector('span').textContent = '✓';
                strength++;
            } else {
                numberCheck.classList.remove('text-green-600');
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
                strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-green-500';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs font-medium text-green-600';
            }
        });
    }
    </script>

<?php require_once 'includes/footer.php'; ?>
