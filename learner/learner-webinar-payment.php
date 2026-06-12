<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Get webinar ID
$webinar_id = isset($_GET['webinar_id']) ? intval($_GET['webinar_id']) : 0;

if (!$webinar_id) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$page_title = "Payment - Nexpert.ai";
$panel_type = "learner";
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>
<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>

<div class="bg-[#080B10] min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#00D4AA]"></div>
            <p class="mt-4 text-gray-400">Loading payment details...</p>
        </div>

        <!-- Payment Content -->
        <div id="payment-content" class="hidden animate-fadeIn">
            
            <!-- Back Button -->
            <div class="mb-6">
                <button onclick="history.back()" class="inline-flex items-center text-[#00D4AA] hover:text-white font-medium transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Webinar
                </button>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Left Column - Payment Form -->
                <div class="lg:col-span-2">
                    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Complete Payment</h2>
                        
                        <!-- Payment Options -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-300 mb-4">Select Payment Method</label>
                            <div class="space-y-3">
                                <!-- Razorpay -->
                                <label class="flex items-center p-4 border border-gray-850 rounded-lg cursor-pointer hover:border-[#00D4AA] transition bg-[#0e1322]">
                                    <input type="radio" name="payment_method" value="razorpay" checked class="w-5 h-5 text-[#00D4AA] focus:ring-[#00D4AA] border-gray-800 bg-[#080B10]">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-white">Razorpay</span>
                                            <div class="flex gap-2 bg-white px-1.5 py-0.5 rounded">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" alt="Razorpay" class="h-4">
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">Credit/Debit Card, UPI, Net Banking, Wallet</p>
                                    </div>
                                </label>

                                <!-- UPI Direct -->
                                <label class="flex items-center p-4 border border-gray-850 rounded-lg cursor-pointer hover:border-[#00D4AA] transition bg-[#0e1322]">
                                    <input type="radio" name="payment_method" value="upi" class="w-5 h-5 text-[#00D4AA] focus:ring-[#00D4AA] border-gray-800 bg-[#080B10]">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-white">UPI</span>
                                            <div class="flex gap-2">
                                                <span class="text-lg">📱</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">Google Pay, PhonePe, Paytm, BHIM</p>
                                    </div>
                                </label>

                                <!-- Wallet -->
                                <label class="flex items-center p-4 border border-gray-850 rounded-lg cursor-pointer hover:border-[#00D4AA] transition bg-[#0e1322]">
                                    <input type="radio" name="payment_method" value="wallet" class="w-5 h-5 text-[#00D4AA] focus:ring-[#00D4AA] border-gray-800 bg-[#080B10]">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-white">Wallet Balance</span>
                                            <span class="text-sm font-semibold text-[#00D4AA]">₹0.00</span>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">Pay using your wallet balance</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" id="terms-checkbox" class="w-5 h-5 text-[#00D4AA] focus:ring-[#00D4AA] border-gray-800 rounded bg-[#080B10] mt-1">
                                <span class="ml-3 text-sm text-gray-400">
                                    I agree to the <a href="#" class="text-[#00D4AA] hover:underline">Terms & Conditions</a> and 
                                    <a href="#" class="text-[#00D4AA] hover:underline">Refund Policy</a>
                                </span>
                            </label>
                        </div>

                        <!-- Pay Button -->
                        <button id="pay-now-btn" disabled class="w-full bg-[#00D4AA] text-[#080B10] px-6 py-4 rounded-lg hover:bg-[#00bda0] transition-all duration-300 font-bold shadow-lg text-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Pay Now</span>
                            <span id="pay-amount">₹0</span>
                        </button>

                        <!-- Security Note -->
                        <div class="mt-6 flex items-center justify-center gap-2 text-sm text-gray-400">
                            <svg class="w-5 h-5 text-emerald-450" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>Secure payment powered by Razorpay</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-xl p-6 sticky top-4">
                        <h3 class="text-xl font-bold text-white mb-6">Order Summary</h3>
                        
                        <!-- Webinar Details -->
                        <div id="webinar-summary" class="mb-6">
                            <!-- Will be loaded dynamically -->
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-850 pt-4 space-y-3">
                            <div class="flex justify-between text-gray-400">
                                <span>Webinar Price</span>
                                <span id="price-original">₹0</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Platform Fee</span>
                                <span id="price-fee">₹0</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>GST (18%)</span>
                                <span id="price-gst">₹0</span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-gray-800 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-white">Total Amount</span>
                                <span id="price-total" class="text-2xl font-bold text-[#00D4AA]">₹0</span>
                            </div>
                        </div>

                        <!-- Refund Policy -->
                        <div class="mt-6 p-4 bg-blue-950/20 border border-blue-900/30 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-xs text-blue-300">
                                    <p class="font-semibold mb-1">100% Refund Policy</p>
                                    <p>Full refund if webinar is cancelled by expert. No refund for learner cancellation within 24 hours of webinar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const WEBINAR_ID = <?php echo $webinar_id; ?>;
let webinarData = null;

document.addEventListener('DOMContentLoaded', function() {
    loadWebinarDetails();
    
    // Enable pay button when terms accepted
    document.getElementById('terms-checkbox').addEventListener('change', function(e) {
        document.getElementById('pay-now-btn').disabled = !e.target.checked;
    });
    
    // Pay button click
    document.getElementById('pay-now-btn').addEventListener('click', processPayment);
});

async function loadWebinarDetails() {
    try {
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?id=${WEBINAR_ID}`);
        const result = await response.json();
        
        if (result.success && result.webinar) {
            webinarData = result.webinar;
            displayWebinarSummary(result.webinar);
            calculatePricing(result.webinar.price_inr);
            
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('payment-content').classList.remove('hidden');
        } else {
            throw new Error('Webinar not found');
        }
    } catch (error) {
        console.error('Error loading webinar:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to load webinar details',
            icon: 'error',
            confirmButtonColor: '#00D4AA'
        }).then(() => {
            history.back();
        });
    }
}

function displayWebinarSummary(webinar) {
    const date = new Date(webinar.webinar_date);
    const html = `
        <div class="border border-gray-850 bg-[#0e1322] rounded-lg p-4 mb-4">
            <div class="mb-2">
                <span class="inline-block px-2.5 py-0.5 bg-[#00D4AA]/10 text-[#00D4AA] text-xs font-bold rounded uppercase">
                    🎥 Webinar
                </span>
            </div>
            <h4 class="font-bold text-white mb-2">${webinar.title}</h4>
            <div class="space-y-1 text-xs text-gray-400">
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                </div>
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>${webinar.webinar_time} · ${webinar.duration_hours}h</span>
                </div>
            </div>
        </div>
    `;
    document.getElementById('webinar-summary').innerHTML = html;
}

function calculatePricing(basePrice) {
    const price = parseFloat(basePrice);
    const platformFee = price * 0.05; // 5% platform fee
    const subtotal = price + platformFee;
    const gst = subtotal * 0.18; // 18% GST
    const total = subtotal + gst;
    
    document.getElementById('price-original').textContent = `₹${price.toFixed(2)}`;
    document.getElementById('price-fee').textContent = `₹${platformFee.toFixed(2)}`;
    document.getElementById('price-gst').textContent = `₹${gst.toFixed(2)}`;
    document.getElementById('price-total').textContent = `₹${total.toFixed(2)}`;
    document.getElementById('pay-amount').textContent = `₹${total.toFixed(2)}`;
}

async function processPayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'razorpay') {
        initiateRazorpayPayment();
    } else if (paymentMethod === 'upi') {
        Swal.fire({
            title: 'Coming Soon',
            text: 'Direct UPI payment will be available soon. Please use Razorpay for now.',
            icon: 'info',
            confirmButtonColor: '#00D4AA'
        });
    } else if (paymentMethod === 'wallet') {
        Swal.fire({
            title: 'Insufficient Balance',
            text: 'Your wallet balance is insufficient. Please use another payment method.',
            icon: 'error',
            confirmButtonColor: '#00D4AA'
        });
    }
}

function initiateRazorpayPayment() {
    const price = parseFloat(webinarData.price_inr);
    const platformFee = price * 0.05;
    const subtotal = price + platformFee;
    const gst = subtotal * 0.18;
    const total = (subtotal + gst) * 100; // Convert to paise
    
    const options = {
        key: 'rzp_test_YOUR_KEY_HERE', // TODO: Use actual Razorpay key
        amount: Math.round(total),
        currency: 'INR',
        name: 'Nexpert.ai',
        description: webinarData.title,
        image: `${BASE_PATH}/attached_assets/stock_images/logo.png`,
        handler: function(response) {
            handlePaymentSuccess(response);
        },
        prefill: {
            email: '<?php echo $_SESSION['email'] ?? ''; ?>',
            contact: ''
        },
        theme: {
            color: '#00D4AA'
        },
        modal: {
            ondismiss: function() {
                console.log('Payment cancelled by user');
            }
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
}

async function handlePaymentSuccess(paymentResponse) {
    try {
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we confirm your payment',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // TODO: Verify payment on server
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        Swal.fire({
            title: 'Payment Successful!',
            html: `
                <div class="text-center text-white">
                    <p class="mb-4">Your registration is confirmed!</p>
                    <p class="text-sm text-gray-400">You will receive an email with webinar joining details.</p>
                </div>
            `,
            icon: 'success',
            confirmButtonColor: '#00D4AA',
            confirmButtonText: 'View My Webinars'
        }).then(() => {
            window.location.href = `${BASE_PATH}/index.php?panel=learner&page=my-programs`;
        });
        
    } catch (error) {
        console.error('Payment verification error:', error);
        Swal.fire({
            title: 'Payment Verification Failed',
            text: 'Please contact support if amount was deducted.',
            icon: 'error',
            confirmButtonColor: '#00D4AA'
        });
    }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
