<?php
require_once 'includes/admin-auth-check.php';

$page_title = "Settings - Admin";
$panel_type = "admin";
require_once 'includes/header.php';
require_once 'includes/admin-sidebar.php';
?>

    <!-- Page Header -->
    <div class="p-6 bg-white border-b">
        <h1 class="text-2xl font-bold text-gray-900">Platform Settings</h1>
        <p class="text-gray-600 mt-1">Configure platform settings and preferences</p>
    </div>

    <div class="p-6">
        <!-- Settings Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- General Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Platform Name</label>
                        <input type="text" value="Nexpert.ai" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
                        <input type="email" value="support@nexpert.ai" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                        <input type="text" value="+1 (555) 123-4567" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commission Rate (%)</label>
                        <input type="number" value="15" min="0" max="100" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Payout (₹)</label>
                        <input type="number" value="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="INR" selected>INR (₹)</option>
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" checked class="mr-2">
                            <span class="text-sm text-gray-700">Two-Factor Authentication</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" checked class="mr-2">
                            <span class="text-sm text-gray-700">Email Verification Required</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2">
                            <span class="text-sm text-gray-700">Maintenance Mode</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                        <input type="number" value="30" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                    <input type="text" placeholder="smtp.example.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                    <input type="number" value="587" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                    <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                    <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <button onclick="saveSettings()" class="bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-600 font-medium">
                Save Settings
            </button>
        </div>
    </div>

<script>
function saveSettings() {
    alert('Settings saved successfully! (Demo - settings functionality to be implemented)');
}
</script>

</div> <!-- Close admin-sidebar main content div -->

<?php require_once 'includes/footer.php'; ?>
