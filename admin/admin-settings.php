<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Fetch current settings from database
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$enableOnline = ($dbSettings['enable_online_payment'] ?? '1') === '1';
$enablePayLater = ($dbSettings['enable_pay_later'] ?? '1') === '1';
$platformName = $dbSettings['platform_name'] ?? 'Nexpert.ai';
$supportEmail = $dbSettings['support_email'] ?? 'support@nexpert.ai';
$commissionRate = $dbSettings['platform_commission_percentage'] ?? '15';
$minPayout = $dbSettings['min_payout_amount'] ?? '500';
$currency = $dbSettings['currency_default'] ?? 'INR';

$page_title = "Platform Settings - Admin Console - Nexpert.ai";
$panel_type = "admin";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8 space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#0D131F] border border-gray-800 rounded-2xl p-6 shadow-xl">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#00D4AA] bg-[#00D4AA]/10 border border-[#00D4AA]/25 px-2.5 py-0.5 rounded-full">
                    System Configuration
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Platform Settings</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Configure checkout payment method activations, commissions, currency, and general platform parameters.</p>
        </div>
        <button onclick="saveAllSettings()" id="save-top-btn" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-6 py-2.5 rounded-xl text-xs font-extrabold transition flex items-center gap-2 shadow-lg shadow-[#00D4AA]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            Save All Changes
        </button>
    </div>

    <!-- PAYMENT METHODS ACTIVATION SECTION (PRIMARY FOCUS) -->
    <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-gray-800">
            <div>
                <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Checkout Payment Methods Activation
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Control which payment options appear on the learner booking checkout step 2.</p>
            </div>
            <span class="text-xs font-mono text-cyan-400 bg-cyan-950/40 border border-cyan-500/30 px-3 py-1 rounded-full font-bold">
                Live on Learner Checkout
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            
            <!-- OPTION 1: ONLINE PAYMENT (RAZORPAY) -->
            <div class="bg-[#080B10] border <?= $enableOnline ? 'border-[#00D4AA]/50' : 'border-gray-800' ?> rounded-2xl p-5 transition-all shadow-inner space-y-4 relative" id="card-online-payment">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-[#00D4AA]/10 border border-[#00D4AA]/30 flex items-center justify-center text-[#00D4AA] shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Online Payment</h3>
                            <p class="text-xs text-gray-400">Secure payment via Razorpay (UPI, Card, NetBanking)</p>
                        </div>
                    </div>
                    
                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggle_online_payment" <?= $enableOnline ? 'checked' : '' ?> onchange="togglePaymentOption('online')" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Status:</span>
                    <span id="badge-online-payment" class="px-2.5 py-0.5 rounded-full font-bold text-[11px] <?= $enableOnline ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-800 text-gray-500 border border-gray-700' ?>">
                        <?= $enableOnline ? '● Activated (Visible)' : '○ Disabled (Hidden)' ?>
                    </span>
                </div>
            </div>

            <!-- OPTION 2: PAY LATER (POST-SESSION PAYMENT) -->
            <div class="bg-[#080B10] border <?= $enablePayLater ? 'border-emerald-500/50' : 'border-gray-800' ?> rounded-2xl p-5 transition-all shadow-inner space-y-4 relative" id="card-pay-later">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-white">Pay Later</h3>
                            <p class="text-xs text-gray-400">Book now and pay after the session concludes</p>
                        </div>
                    </div>
                    
                    <!-- Toggle Switch -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="toggle_pay_later" <?= $enablePayLater ? 'checked' : '' ?> onchange="togglePaymentOption('pay_later')" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between text-xs">
                    <span class="text-gray-400">Status:</span>
                    <span id="badge-pay-later" class="px-2.5 py-0.5 rounded-full font-bold text-[11px] <?= $enablePayLater ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-800 text-gray-500 border border-gray-700' ?>">
                        <?= $enablePayLater ? '● Activated (Visible)' : '○ Disabled (Hidden)' ?>
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- GENERAL & FINANCIAL SETTINGS SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- General Settings Card -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2 pb-2 border-b border-gray-800">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                General Settings
            </h3>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Platform Brand Name</label>
                    <input type="text" id="setting_platform_name" value="<?= htmlspecialchars($platformName) ?>" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Support Email Address</label>
                    <input type="email" id="setting_support_email" value="<?= htmlspecialchars($supportEmail) ?>" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                </div>
            </div>
        </div>

        <!-- Financial & Payout Settings Card -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-6 shadow-xl space-y-4">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2 pb-2 border-b border-gray-800">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Financial & Payout Parameters
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Commission Rate (%)</label>
                    <input type="number" id="setting_commission" value="<?= htmlspecialchars($commissionRate) ?>" min="0" max="100" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Minimum Payout (₹)</label>
                    <input type="number" id="setting_min_payout" value="<?= htmlspecialchars($minPayout) ?>" min="100" step="50" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Default Currency</label>
                <select id="setting_currency" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                    <option value="INR" <?= $currency === 'INR' ? 'selected' : '' ?>>INR (₹) — Indian Rupee</option>
                    <option value="USD" <?= $currency === 'USD' ? 'selected' : '' ?>>USD ($) — US Dollar</option>
                </select>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.BASE_PATH = '<?php echo BASE_PATH; ?>';

async function togglePaymentOption(method) {
    const isOnline = method === 'online';
    const toggle = document.getElementById(isOnline ? 'toggle_online_payment' : 'toggle_pay_later');
    const badge = document.getElementById(isOnline ? 'badge-online-payment' : 'badge-pay-later');
    const card = document.getElementById(isOnline ? 'card-online-payment' : 'card-pay-later');

    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/settings.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'toggle_payment_method',
                method: method
            })
        });
        
        const result = await res.json();
        if (result.success) {
            const isEnabled = result.is_enabled;
            toggle.checked = isEnabled;

            if (isEnabled) {
                badge.className = 'px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                badge.textContent = '● Activated (Visible)';
                card.classList.remove('border-gray-800');
                card.classList.add(isOnline ? 'border-[#00D4AA]/50' : 'border-emerald-500/50');
            } else {
                badge.className = 'px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-gray-800 text-gray-500 border border-gray-700';
                badge.textContent = '○ Disabled (Hidden)';
                card.classList.remove('border-[#00D4AA]/50', 'border-emerald-500/50');
                card.classList.add('border-gray-800');
            }

            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 2500,
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-xl' }
            });
            Toast.fire({
                icon: 'success',
                title: isEnabled ? `${isOnline ? 'Online Payment' : 'Pay Later'} Activated` : `${isOnline ? 'Online Payment' : 'Pay Later'} Disabled`
            });
        }
    } catch (e) {
        toggle.checked = !toggle.checked;
    }
}

async function saveAllSettings() {
    const saveBtn = document.getElementById('save-top-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const payload = {
        platform_name: document.getElementById('setting_platform_name').value.trim(),
        support_email: document.getElementById('setting_support_email').value.trim(),
        platform_commission_percentage: document.getElementById('setting_commission').value,
        min_payout_amount: document.getElementById('setting_min_payout').value,
        currency_default: document.getElementById('setting_currency').value,
        enable_online_payment: document.getElementById('toggle_online_payment').checked ? '1' : '0',
        enable_pay_later: document.getElementById('toggle_pay_later').checked ? '1' : '0'
    };

    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/settings.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save',
                settings: payload
            })
        });

        const result = await res.json();
        saveBtn.disabled = false;
        saveBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Save All Changes`;

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Settings Saved',
                text: 'Platform configuration updated successfully!',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.error || 'Failed to save settings.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff'
            });
        }
    } catch (e) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = `Save All Changes`;
    }
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
