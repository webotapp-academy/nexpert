<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Use centralized session + config (defines BASE_PATH & BASE_URL constants)
require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as expert
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'expert') {
    $redirectUrl = $_SESSION['redirect_after_login'] ?? (BASE_PATH . '/index.php?panel=expert&page=dashboard');
    unset($_SESSION['redirect_after_login']);
    if (strpos($redirectUrl, 'page=auth') !== false || strpos($redirectUrl, 'page=apply') !== false) {
        $redirectUrl = BASE_PATH . '/index.php?panel=expert&page=dashboard';
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$page_title = "Expert Portal — Nexpert.ai";
$panel_type = "home";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>

<script>
    // Set BASE_PATH (application root) globally for JS
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    // Utility function to resolve image paths
    function resolveImagePath(imagePath) {
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            return imagePath;
        }
        if (!imagePath) {
            return `${window.BASE_PATH}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
        }
        const normalizedPath = imagePath.replace(/^\/+/, '');
        return `${window.BASE_PATH}/${normalizedPath}`;
    }
</script>

<div class="min-h-screen flex bg-[#080B10] text-gray-100 relative overflow-hidden">
    <!-- Left Side - Value Proposition & Highlights -->
    <div class="hidden lg:block lg:w-1/2 bg-[#0D131F] border-r border-gray-800 relative overflow-hidden">
        <!-- Ambient Glow -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-[#00D4AA]/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-indigo-500/5 rounded-full blur-3xl"></div>
        </div>
        
        <div class="flex flex-col justify-center h-full p-16 relative z-10">
            <div class="max-w-md mx-auto">
                <div class="inline-flex items-center gap-2 bg-[#00D4AA]/10 border border-[#00D4AA]/25 text-[#00D4AA] px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-8">
                    <span class="w-1.5 h-1.5 bg-[#00D4AA] rounded-full animate-pulse"></span>
                    Verified Expert Cohort
                </div>
                
                <h3 class="text-4xl font-extrabold mb-4 text-white tracking-tight leading-tight">
                    Monetize Your <span class="text-[#00D4AA]">Mastery</span>
                </h3>
                <p class="text-base text-gray-400 mb-10 leading-relaxed">
                    Build a verified reputation backed by real telemetry and learner outcomes rather than subjective reviews.
                </p>
                
                <div class="space-y-5">
                    <div class="flex items-start p-4 rounded-2xl bg-[#080B10] border border-gray-800/80">
                        <div class="w-10 h-10 rounded-xl bg-[#00D4AA]/10 border border-[#00D4AA]/20 text-[#00D4AA] flex items-center justify-center mr-4 shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-sm mb-0.5">Flexible Direct Earnings</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">Set your hourly rates and receive automated escrow payouts upon session completion.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 rounded-2xl bg-[#080B10] border border-gray-800/80">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mr-4 shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-sm mb-0.5">High-Intent Global Learners</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">Connect with professionals and organizations seeking verified domain leadership.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start p-4 rounded-2xl bg-[#080B10] border border-gray-800/80">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center mr-4 shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-sm mb-0.5">Autonomous Credibility Scoring</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">Grow your Trust Score through 4 dimensions (Outcome, Structure, Boundary, Consistency).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Auth Form -->
    <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-20 xl:px-24 bg-[#080B10] py-12 relative">
        <div class="w-full max-w-md relative z-10 bg-[#0D131F] rounded-2xl p-8 sm:p-10 border border-gray-800 shadow-2xl">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Expert Portal</h2>
                <p class="mt-2 text-xs text-gray-400">Sign in to your dashboard or apply to become an expert</p>
            </div>

            <!-- Toggle Tabs -->
            <div class="flex p-1 bg-[#080B10] border border-gray-800 rounded-xl mb-8">
                <button id="signInTab" class="flex-1 py-2.5 text-xs text-center font-extrabold bg-[#00D4AA] text-[#080B10] rounded-lg shadow-sm transition" onclick="switchTab('signin')">
                    Sign In
                </button>
                <button id="signUpTab" class="flex-1 py-2.5 text-xs text-center font-semibold text-gray-400 hover:text-white rounded-lg transition" onclick="switchTab('signup')">
                    Apply as Expert
                </button>
            </div>

            <!-- Sign In Form -->
            <form id="signInForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Email Address</label>
                    <input id="signInEmail" type="email" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="your@email.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Password</label>
                    <input id="signInPassword" type="password" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-gray-400">
                        <input type="checkbox" class="h-4 w-4 bg-[#080B10] border-gray-700 text-[#00D4AA] focus:ring-[#00D4AA] rounded">
                        <span class="ml-2 font-medium">Remember me</span>
                    </label>
                    <a href="#" class="font-bold text-[#00D4AA] hover:underline">Forgot password?</a>
                </div>

                <button type="submit" id="expertSignInBtn" class="w-full bg-[#00D4AA] text-[#080B10] py-3.5 px-4 rounded-xl hover:bg-[#00bfa0] transition duration-200 font-extrabold flex items-center justify-center shadow-[0_0_20px_rgba(0,212,170,0.25)] mt-2">
                    <span id="expertSignInBtnText">Sign In &rarr;</span>
                    <svg id="expertSignInSpinner" class="hidden animate-spin ml-2 h-5 w-5 text-[#080B10]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <!-- Sign Up Form -->
            <form id="signUpForm" class="space-y-4 hidden">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Full Name</label>
                    <input id="expertName" type="text" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="Enter your full name">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Mobile Number</label>
                    <div class="flex gap-2">
                        <select id="expertCountryCode" class="w-28 px-3 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs">
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
                        </select>
                        <input id="expertMobile" type="tel" required class="flex-1 px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="9876543210">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Email Address</label>
                    <input id="expertEmail" type="email" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="your@email.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Password</label>
                    <input id="expertPassword" type="password" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="Create a password">
                    <div class="mt-2.5 bg-[#080B10] p-3 rounded-xl border border-gray-800">
                        <div class="flex items-center justify-between mb-1.5">
                            <span id="expertPasswordStrengthText" class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Password strength</span>
                        </div>
                        <div class="h-1.5 bg-gray-800 rounded-full mb-2">
                            <div id="expertPasswordStrengthBar" class="h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <ul class="text-[10px] text-gray-400 space-y-1 font-medium grid grid-cols-2 gap-x-2">
                            <li id="expertPwLength" class="flex items-center"><span class="mr-1 font-bold">○</span> 8+ chars</li>
                            <li id="expertPwUppercase" class="flex items-center"><span class="mr-1 font-bold">○</span> Uppercase</li>
                            <li id="expertPwLowercase" class="flex items-center"><span class="mr-1 font-bold">○</span> Lowercase</li>
                            <li id="expertPwNumber" class="flex items-center"><span class="mr-1 font-bold">○</span> Number</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase tracking-wide">Repeat Password</label>
                    <input id="expertPasswordRepeat" type="password" required class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] transition placeholder-gray-500 text-sm" placeholder="Confirm your password">
                </div>

                <button type="submit" id="expertRegisterBtn" class="w-full bg-[#00D4AA] text-[#080B10] py-3.5 px-4 rounded-xl hover:bg-[#00bfa0] transition duration-200 font-extrabold flex items-center justify-center shadow-[0_0_20px_rgba(0,212,170,0.25)] mt-2">
                    <span id="expertRegisterText">Create Account &amp; Continue</span>
                    <svg id="expertRegisterSpinner" class="hidden animate-spin ml-3 h-5 w-5 text-[#080B10]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-800"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-[#0D131F] text-gray-500 font-bold uppercase tracking-wider text-[10px]">Or continue with</span>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="button" id="googleSignInBtn" class="w-full bg-[#080B10] border border-gray-700 text-gray-200 py-3 px-4 rounded-xl hover:border-gray-500 hover:text-white transition duration-200 flex items-center justify-center font-bold text-xs shadow-sm">
                        <svg class="w-4 h-4 mr-2.5" viewBox="0 0 24 24">
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
        signInTab.className = 'flex-1 py-2.5 text-xs text-center font-extrabold bg-[#00D4AA] text-[#080B10] rounded-lg shadow-sm transition';
        signUpTab.className = 'flex-1 py-2.5 text-xs text-center font-semibold text-gray-400 hover:text-white rounded-lg transition';
        signInForm.classList.remove('hidden');
        signUpForm.classList.add('hidden');
    } else {
        signUpTab.className = 'flex-1 py-2.5 text-xs text-center font-extrabold bg-[#00D4AA] text-[#080B10] rounded-lg shadow-sm transition';
        signInTab.className = 'flex-1 py-2.5 text-xs text-center font-semibold text-gray-400 hover:text-white rounded-lg transition';
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
            confirmButtonColor: '#00D4AA'
        });
        return;
    }

    const signInBtn = document.getElementById('expertSignInBtn');
    const btnText = document.getElementById('expertSignInBtnText');
    const spinner = document.getElementById('expertSignInSpinner');

    signInBtn.disabled = true;
    btnText.textContent = 'Signing in...';
    spinner.classList.remove('hidden');

    function resetSignInBtn() {
        signInBtn.disabled = false;
        btnText.innerHTML = 'Sign In &rarr;';
        spinner.classList.add('hidden');
    }
    
    let redirectAfterLogin = sessionStorage.getItem('redirect_after_login');
    
    try {
        const apiUrl = `${window.BASE_PATH}/admin-panel/apis/expert/auth.php`;
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                email: email,
                password: password,
                redirect_after_login: redirectAfterLogin
            })
        });
        
        const result = await response.json().catch(() => null);
        
        if (result && result.success) {
            sessionStorage.removeItem('redirect_after_login');
            
            await Swal.fire({
                icon: 'success',
                title: 'Welcome Back!',
                text: 'Login successful',
                confirmButtonColor: '#00D4AA',
                timer: 1500,
                showConfirmButton: false
            });
            let redirectUrl = result.redirect_url || redirectAfterLogin || `${window.BASE_PATH}/index.php?panel=expert&page=dashboard`;
            if (redirectUrl.includes('page=apply') || redirectUrl.includes('page=auth')) {
                redirectUrl = `${window.BASE_PATH}/index.php?panel=expert&page=dashboard`;
            }
            window.location.href = redirectUrl;
        } else {
            resetSignInBtn();
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: (result && result.message) ? result.message : 'Invalid email or password. Please check your credentials.',
                confirmButtonColor: '#00D4AA'
            });
        }
    } catch (error) {
        resetSignInBtn();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to reach the server. Please check your internet connection and try again.',
            confirmButtonColor: '#00D4AA'
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
            confirmButtonColor: '#00D4AA'
        });
        return;
    }
    
    if (password.length < 8) {
        Swal.fire({
            icon: 'warning',
            title: 'Weak Password',
            text: 'Password must be at least 8 characters long',
            confirmButtonColor: '#00D4AA'
        });
        return;
    }
    
    if (password !== passwordRepeat) {
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'Passwords do not match. Please try again.',
            confirmButtonColor: '#00D4AA'
        });
        return;
    }
    
    const fullMobile = countryCode + mobile;
    
    const btn = document.getElementById('expertRegisterBtn');
    const btnText = document.getElementById('expertRegisterText');
    const spinner = document.getElementById('expertRegisterSpinner');
    btn.disabled = true;
    btnText.textContent = 'Creating Account...';
    spinner.classList.remove('hidden');
    
    try {
        const apiUrl = `${window.BASE_PATH}/admin-panel/apis/expert/register.php`;
        const response = await fetch(apiUrl, {
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
                text: 'Registration successful! Proceed to set up your expert profile.',
                confirmButtonColor: '#00D4AA',
                timer: 2000
            });
            const redirectUrl = result.redirect_url || `${window.BASE_PATH}/index.php?panel=expert&page=profile-setup`;
            window.location.href = redirectUrl;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: (result && result.message) ? result.message : 'Registration failed. Please check your details and try again.',
                confirmButtonColor: '#00D4AA'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred during registration. Please try again.',
            confirmButtonColor: '#00D4AA'
        });
    } finally {
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
        
        if (password.length >= 8) {
            lengthCheck.classList.add('text-[#00D4AA]');
            lengthCheck.querySelector('span').textContent = '✓';
            strength++;
        } else {
            lengthCheck.classList.remove('text-[#00D4AA]');
            lengthCheck.querySelector('span').textContent = '○';
        }
        
        if (/[A-Z]/.test(password)) {
            uppercaseCheck.classList.add('text-[#00D4AA]');
            uppercaseCheck.querySelector('span').textContent = '✓';
            strength++;
        } else {
            uppercaseCheck.classList.remove('text-[#00D4AA]');
            uppercaseCheck.querySelector('span').textContent = '○';
        }
        
        if (/[a-z]/.test(password)) {
            lowercaseCheck.classList.add('text-[#00D4AA]');
            lowercaseCheck.querySelector('span').textContent = '✓';
            strength++;
        } else {
            lowercaseCheck.classList.remove('text-[#00D4AA]');
            lowercaseCheck.querySelector('span').textContent = '○';
        }
        
        if (/[0-9]/.test(password)) {
            numberCheck.classList.add('text-[#00D4AA]');
            numberCheck.querySelector('span').textContent = '✓';
            strength++;
        } else {
            numberCheck.classList.remove('text-[#00D4AA]');
            numberCheck.querySelector('span').textContent = '○';
        }
        
        const percentage = (strength / 4) * 100;
        strengthBar.style.width = percentage + '%';
        
        if (strength === 0) {
            strengthBar.className = 'h-full rounded-full transition-all duration-300';
            strengthText.textContent = 'Password strength';
            strengthText.className = 'text-[10px] font-bold text-gray-400 uppercase tracking-wide';
        } else if (strength <= 2) {
            strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-red-500';
            strengthText.textContent = 'Weak';
            strengthText.className = 'text-[10px] font-bold text-red-400 uppercase tracking-wide';
        } else if (strength === 3) {
            strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-yellow-500';
            strengthText.textContent = 'Medium';
            strengthText.className = 'text-[10px] font-bold text-yellow-400 uppercase tracking-wide';
        } else {
            strengthBar.className = 'h-full rounded-full transition-all duration-300 bg-[#00D4AA]';
            strengthText.textContent = 'Strong';
            strengthText.className = 'text-[10px] font-bold text-[#00D4AA] uppercase tracking-wide';
        }
    });
}

// Google Sign In Button Handler
document.getElementById('googleSignInBtn').addEventListener('click', async function() {
    try {
        const state = Math.random().toString(36).substring(2, 18);
        const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/oauth/init-google-oauth.php`, {
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
            window.location.href = result.auth_url;
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Google Sign-In Configuration',
                html: `
                    <div class="text-left text-xs text-gray-300 space-y-3">
                        <p class="text-sm text-gray-200 font-medium">To enable Google Sign-In, add your Google OAuth client credentials to <code>.env</code>:</p>
                        <div class="bg-[#080B10] p-3 rounded-xl border border-gray-800 text-[#00D4AA] font-mono text-[11px]">
                            <div>GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com</div>
                            <div>GOOGLE_CLIENT_SECRET=your-client-secret</div>
                        </div>
                        <p class="text-[11px] text-gray-400">
                            <strong>Authorized Redirect URI in Google Cloud:</strong><br>
                            <span class="text-emerald-400 font-mono break-all">${window.location.origin}${window.BASE_PATH}/admin-panel/apis/oauth/google-callback.php</span>
                        </p>
                    </div>
                `,
                background: '#0D131F',
                color: '#fff',
                confirmButtonColor: '#00D4AA',
                confirmButtonText: 'Got it'
            });
        }
    } catch (error) {
        console.error('Google Sign In error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to communicate with authentication service. Please check your network or try again.',
            background: '#0D131F',
            color: '#fff',
            confirmButtonColor: '#00D4AA'
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
