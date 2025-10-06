<?php
require_once 'includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ?panel=learner&page=auth');
    exit;
}

$page_title = "Payment - Nexpert.ai";
$panel_type = "learner";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-green-600 font-medium">Session Details</span>
                </div>
                <div class="w-16 h-0.5 bg-green-500 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        2
                    </div>
                    <span class="ml-2 text-sm text-primary font-medium">Payment</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-2 text-sm text-gray-500">Confirmation</span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Payment Details</h1>
                
                <form id="payment-form" class="space-y-6">
                    <!-- Payment Method Selection -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h3>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg hover:border-primary cursor-pointer transition duration-200">
                                <input type="radio" name="payment_method" value="cash_test" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <div class="ml-3 flex items-center">
                                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <div>
                                        <span class="font-medium">Cash (Test Mode)</span>
                                        <p class="text-xs text-gray-500 mt-1">Use this option for testing. No real payment required.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg hover:border-primary cursor-pointer transition duration-200 opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="card" disabled class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <div class="ml-3 flex items-center">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <div>
                                        <span class="font-medium">Credit/Debit Card</span>
                                        <p class="text-xs text-gray-500 mt-1">Coming soon via Razorpay</p>
                                    </div>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg hover:border-primary cursor-pointer transition duration-200 opacity-50 cursor-not-allowed">
                                <input type="radio" name="payment_method" value="upi" disabled class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <div class="ml-3 flex items-center">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <span class="font-medium">UPI</span>
                                        <p class="text-xs text-gray-500 mt-1">Coming soon</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Test Mode Notice -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-semibold text-green-800 mb-1">Test Mode Active</h4>
                                <p class="text-sm text-green-700">
                                    Cash payment option is for testing purposes only. Click "Complete Payment" to simulate a successful payment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input type="checkbox" id="terms-checkbox" required class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-0.5">
                        <div class="ml-3 text-sm">
                            <p class="text-gray-700">
                                I agree to the <a href="#" class="text-primary hover:text-secondary">Terms of Service</a> and 
                                <a href="#" class="text-primary hover:text-secondary">Privacy Policy</a>
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-payment-btn" class="w-full bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition duration-200 font-semibold text-lg shadow-md hover:shadow-lg">
                        Complete Payment
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-lg p-6 h-fit">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                
                <!-- Expert Info -->
                <div class="mb-6 pb-6 border-b">
                    <div class="flex items-center mb-3">
                        <div id="summary-expert-photo" class="w-12 h-12 rounded-full bg-gray-200 mr-3 overflow-hidden flex items-center justify-center">
                            <span class="text-xl text-gray-400">👤</span>
                        </div>
                        <div>
                            <h3 id="summary-expert-name" class="font-semibold text-gray-900">Loading...</h3>
                            <p id="summary-expert-title" class="text-sm text-gray-600">Loading...</p>
                        </div>
                    </div>
                </div>

                <!-- Session Details -->
                <div class="space-y-3 mb-6 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Session Date:</span>
                        <span id="summary-date" class="text-gray-900 font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Session Time:</span>
                        <span id="summary-time" class="text-gray-900 font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duration:</span>
                        <span class="text-gray-900 font-medium">60 minutes</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Type:</span>
                        <span class="text-gray-900 font-medium">1-on-1 Video Call</span>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="border-t pt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Session Fee:</span>
                        <span id="summary-amount" class="text-gray-900">₹0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Platform Fee:</span>
                        <span class="text-gray-900">₹0</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold pt-2 border-t">
                        <span class="text-gray-900">Total:</span>
                        <span id="summary-total" class="text-primary">₹0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
(function() {
    'use strict';

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const expertId = urlParams.get('expert_id');
    const sessionDatetime = urlParams.get('datetime');
    const amount = urlParams.get('amount');

    if (!expertId || !sessionDatetime || !amount) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Payment details are incomplete. Redirecting to browse experts...',
            confirmButtonColor: '#3B82F6',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = '/?panel=learner&page=browse-experts';
        });
        return;
    }

    // Load expert data and populate summary
    async function loadExpertData() {
        try {
            const response = await fetch(`/admin-panel/apis/learner/booking.php?expert_id=${expertId}`);
            const result = await response.json();

            if (result.success) {
                const expert = result.data;
                document.getElementById('summary-expert-name').textContent = expert.name || 'Expert';
                document.getElementById('summary-expert-title').textContent = expert.professional_title || 'Professional';
                
                if (expert.profile_photo) {
                    const photoContainer = document.getElementById('summary-expert-photo');
                    photoContainer.innerHTML = `<img src="${expert.profile_photo}" alt="${expert.name}" class="w-full h-full object-cover">`;
                }
            }
        } catch (error) {
            console.error('Error loading expert data:', error);
        }
    }

    // Populate session details
    const datetime = new Date(sessionDatetime);
    const formattedDate = datetime.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
    const formattedTime = datetime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    
    document.getElementById('summary-date').textContent = formattedDate;
    document.getElementById('summary-time').textContent = formattedTime;
    document.getElementById('summary-amount').textContent = `₹${amount}`;
    document.getElementById('summary-total').textContent = `₹${amount}`;

    // Handle form submission
    document.getElementById('payment-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const submitBtn = document.getElementById('submit-payment-btn');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        try {
            const response = await fetch('/admin-panel/apis/learner/payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    expert_id: expertId,
                    session_datetime: sessionDatetime,
                    amount: amount,
                    duration: 60,
                    payment_method: paymentMethod
                })
            });

            const result = await response.json();

            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful!',
                    text: 'Your session has been booked.',
                    confirmButtonColor: '#3B82F6',
                    timer: 2000,
                    showConfirmButton: false
                });
                window.location.href = '/?panel=learner&page=dashboard';
            } else {
                if (response.status === 401) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Login Required',
                        text: 'Please login to complete payment',
                        confirmButtonColor: '#3B82F6'
                    });
                    window.location.href = '/?panel=learner&page=auth';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: result.message || 'Payment could not be processed',
                        confirmButtonColor: '#3B82F6'
                    });
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Payment';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payment processing failed. Please try again.',
                confirmButtonColor: '#3B82F6'
            });
            submitBtn.disabled = false;
            submitBtn.textContent = 'Complete Payment';
        }
    });

    // Initialize
    loadExpertData();
})();
</script>
<?php require_once 'includes/footer.php'; ?>
