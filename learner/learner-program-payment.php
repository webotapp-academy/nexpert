<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once dirname(__DIR__) . '/includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$page_title = "Program Enrollment Payment - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Get parameters
$program_id = $_GET['program_id'] ?? null;

if (!$program_id) {
    header('Location: ?panel=learner&page=browse-experts');
    exit;
}

// Fetch program details and price from database (NEVER trust client-supplied price)
try {
    $stmt = $pdo->prepare("
        SELECT w.*, ep.full_name, ep.profile_photo 
        FROM workflows w
        JOIN expert_profiles ep ON w.expert_id = ep.user_id
        WHERE w.id = ? AND w.is_active = 1
    ");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program || !isset($program['price_inr']) || $program['price_inr'] <= 0) {
        header('Location: ?panel=learner&page=browse-experts');
        exit;
    }
    
    // Use server-side price - NEVER trust client input for payment amounts
    $amount = $program['price_inr'];
} catch (PDOException $e) {
    error_log("Program Payment Error: " . $e->getMessage());
    header('Location: ?panel=learner&page=browse-experts');
    exit;
}
?>
<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        ✓
                    </div>
                    <span class="ml-2 text-sm text-emerald-400 font-medium">Program Details</span>
                </div>
                <div class="w-16 h-0.5 bg-emerald-500 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-[#00D4AA] text-[#080B10] rounded-full flex items-center justify-center text-sm font-bold">
                        2
                    </div>
                    <span class="ml-2 text-sm text-[#00D4AA] font-bold">Payment</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-800 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-800 text-gray-500 rounded-full flex items-center justify-center text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-2 text-sm text-gray-500">Confirmation</span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Payment Form -->
            <div class="lg:col-span-2 bg-[#131b2e] border border-gray-800 rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-white mb-6">Payment Details</h1>
                
                <form id="payment-form" class="space-y-6">
                    <!-- Payment Method Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Select Payment Method</label>
                        <div class="space-y-3">
                            <!-- Card Payment (Razorpay) -->
                            <label class="flex items-center p-4 border border-[#00D4AA] rounded-lg cursor-pointer bg-[#0e1322]">
                                <input type="radio" name="payment_method" value="razorpay" checked class="w-5 h-5 text-[#00D4AA] focus:ring-[#00D4AA]">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center">
                                        <span class="font-medium text-white">Credit / Debit Card</span>
                                        <div class="ml-auto flex space-x-2">
                                            <img src="https://cdn-icons-png.flaticon.com/512/349/349221.png" alt="Visa" class="h-6">
                                            <img src="https://cdn-icons-png.flaticon.com/512/349/349228.png" alt="Mastercard" class="h-6">
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Secure payment via Razorpay</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="pt-6 border-t border-gray-800">
                        <button type="submit" id="submit-payment" class="w-full bg-[#00D4AA] text-[#080B10] py-4 px-6 rounded-lg font-bold text-lg hover:bg-[#00bda0] transition-all transform hover:scale-105 shadow-lg flex items-center justify-center space-x-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>Proceed to Secure Payment</span>
                        </button>
                        <p class="text-center text-sm text-gray-500 mt-3">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Secure SSL encrypted payment
                        </p>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow-lg p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-white mb-6">Enrollment Summary</h2>
                    
                    <!-- Program Info -->
                    <div class="mb-6 pb-6 border-b border-gray-800">
                        <div class="flex items-start gap-4 mb-4">
                            <img src="<?php echo htmlspecialchars($program['profile_photo'] ?? ''); ?>" 
                                 alt="Expert" 
                                 class="w-16 h-16 rounded-full border border-gray-800">
                            <div class="flex-1">
                                <p class="font-semibold text-white"><?php echo htmlspecialchars($program['title']); ?></p>
                                <p class="text-sm text-gray-600">by <?php echo htmlspecialchars($program['full_name']); ?></p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="summary-duration"><?php echo $program['duration_weeks'] ?? 0; ?> weeks duration</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="space-y-3 mb-6 pb-6 border-b border-gray-800">
                        <div class="flex justify-between text-gray-300">
                            <span>Program Fee</span>
                            <span id="summary-amount">₹<?php echo number_format($amount, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-300">
                            <span>Platform Fee</span>
                            <span class="text-emerald-400 font-semibold">₹0</span>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="flex justify-between items-center text-xl font-bold text-white mb-6">
                        <span>Total Amount</span>
                        <span id="summary-total" class="text-[#00D4AA]">₹<?php echo number_format($amount, 2); ?></span>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="bg-[#0e1322] border border-gray-800 rounded-lg p-4">
                        <div class="flex items-center justify-center space-x-4 text-sm text-gray-400">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Secure</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span>Encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    
    // Get parameters from URL
    const programId = <?php echo json_encode($program_id); ?>;
    const amount = <?php echo json_encode((float)$amount); ?>;

    // Inject Razorpay script
    const rzScript = document.createElement('script');
    rzScript.src = 'https://checkout.razorpay.com/v1/checkout.js';
    document.head.appendChild(rzScript);

    document.getElementById('payment-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-payment');
        const originalBtnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        try {
            const orderResult = await createProgramOrder(paymentMethod);
            
            if (paymentMethod === 'cash_test') {
                // Instant success for test mode
                Swal.fire({
                    icon: 'success',
                    title: 'Enrollment Successful!',
                    text: 'You have successfully enrolled in the program (Test Mode)',
                    confirmButtonText: 'View My Programs'
                }).then(() => {
                    window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=dashboard`;
                });
            } else if (orderResult.mode === 'razorpay' && orderResult.data.razorpay_order_id) {
                // Launch Razorpay checkout
                const options = {
                    key: orderResult.data.razorpay_key_id,
                    amount: parseInt(amount * 100),
                    currency: 'INR',
                    name: 'Nexpert.ai',
                    description: '<?php echo addslashes($program['title']); ?>',
                    order_id: orderResult.data.razorpay_order_id,
                    handler: async function (response) {
                        try {
                            const verifyResult = await verifyProgramPayment({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature,
                                enrollment_id: orderResult.data.enrollment_id
                            });
                            
                            if (verifyResult.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Enrollment Successful!',
                                    text: 'Payment verified. You are now enrolled in the program!',
                                    confirmButtonText: 'View My Programs'
                                }).then(() => {
                                    window.location.href = `${window.BASE_PATH}/index.php?panel=learner&page=my-programs`;
                                });
                            } else {
                                throw new Error(verifyResult.message || 'Payment verification failed');
                            }
                        } catch (error) {
                            console.error('Verification error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Verification Failed',
                                text: error.message || 'Payment verification failed. Please contact support.',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    prefill: {
                        email: '<?php echo $_SESSION['email'] ?? ''; ?>',
                    },
                    theme: {
                        color: '#3B82F6'
                    }
                };
                
                const rzp = new Razorpay(options);
                rzp.open();
            }
        } catch (error) {
            console.error('Payment error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Payment Failed',
                text: error.message || 'An error occurred during payment processing',
                confirmButtonText: 'Try Again'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });

    async function createProgramOrder(paymentMethod) {
        const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/program-payment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                action: 'create_order',
                program_id: programId,
                payment_method: paymentMethod
                // NOTE: We do NOT send amount - server fetches canonical price from database
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to create payment order');
        }
        
        return result;
    }

    async function verifyProgramPayment(payload) {
        const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/program-payment.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                action: 'verify_payment',
                ...payload
            })
        });
        
        const result = await response.json();
        return result;
    }
    </script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
