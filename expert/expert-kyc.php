<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$page_title = "KYC Verification - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>

<div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 pb-6 border-b border-gray-800">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                Sovereign Trust Telemetry
            </div>
            <h1 class="text-3xl font-extrabold text-white">Expert Identity & KYC Verification</h1>
            <p class="text-sm text-gray-400 mt-1">Complete your cryptographic identity verification to enable direct 1:1 bookings, trust badges, and automated payout routing.</p>
        </div>

        <!-- Verification Status -->
        <div class="bg-[#0D131F] border-l-4 border-l-amber-500 border border-gray-800 rounded-2xl p-5 mb-8 shadow-xl">
            <div class="flex items-start">
                <div class="shrink-0 p-2 bg-amber-500/10 border border-amber-500/25 rounded-xl mr-3.5">
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-300 leading-relaxed">
                        <strong class="text-amber-400 font-bold">Verification Review Active:</strong> Your KYC documents and settlement telemetry are processed with end-to-end encryption. Once verified, instant payouts and sovereign trust scoring are unlocked.
                    </p>
                </div>
            </div>
        </div>

        <!-- KYC Form -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-6 md:p-8">
            <form id="kycForm">
                <!-- Personal Information -->
                <div class="mb-8 pb-8 border-b border-gray-800">
                    <h2 class="text-base font-extrabold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Personal Information
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Full Legal Name *</label>
                            <input type="text" id="fullLegalName" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="As per government ID">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Date of Birth *</label>
                            <input type="date" id="dob" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nationality *</label>
                            <select id="nationality" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                                <option value="">Select nationality</option>
                                <option value="IN">India</option>
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                                <option value="SG">Singapore</option>
                                <option value="AE">United Arab Emirates</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Gender *</label>
                            <select id="gender" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-8 pb-8 border-b border-gray-800">
                    <h2 class="text-base font-extrabold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Address Telemetry
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Street Address *</label>
                            <input type="text" id="addressLine1" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="House/Flat number, Street name">
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">City *</label>
                                <input type="text" id="city" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="City">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">State/Province *</label>
                                <input type="text" id="state" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="State/Province">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Postal/ZIP Code *</label>
                                <input type="text" id="postalCode" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs font-mono" placeholder="Postal code">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Country *</label>
                                <select id="country" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                                    <option value="">Select country</option>
                                    <option value="IN">India</option>
                                    <option value="US">United States</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identity Documents -->
                <div class="mb-8 pb-8 border-b border-gray-800">
                    <h2 class="text-base font-extrabold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        Identity Verification Documents
                    </h2>
                    
                    <div class="space-y-6">
                        <!-- ID Type -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">ID Document Type *</label>
                            <select id="idType" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                                <option value="">Select document type</option>
                                <option value="passport">Passport</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="aadhaar">Aadhaar Card</option>
                                <option value="pan">PAN Card</option>
                                <option value="national_id">National ID Card</option>
                            </select>
                        </div>

                        <!-- ID Number -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">ID Number *</label>
                            <input type="text" id="idNumber" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs font-mono" placeholder="Enter your ID number">
                        </div>

                        <!-- ID Document Upload -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Upload ID Document (Front) *</label>
                            <div class="border-2 border-dashed border-gray-700 bg-[#080B10]/50 rounded-2xl p-6 text-center hover:border-[#00D4AA] transition">
                                <input type="file" id="idDocumentFront" accept="image/*,.pdf" class="hidden">
                                <label for="idDocumentFront" class="cursor-pointer">
                                    <svg class="mx-auto h-10 w-10 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-xs font-bold text-gray-300">Click to upload or drag & drop</p>
                                    <p class="text-[11px] text-gray-500 mt-1">PNG, JPG, PDF up to 10MB</p>
                                </label>
                            </div>
                            <!-- Preview Area -->
                            <div id="idFrontPreviewArea" class="mt-4 hidden">
                                <div class="relative border border-gray-800 bg-[#080B10] rounded-2xl p-4">
                                    <img id="idFrontPreviewImage" class="max-w-full h-auto rounded-xl mx-auto" style="max-height: 300px;" />
                                    <div id="idFrontPdfPreview" class="hidden p-4 bg-[#0D131F] rounded-xl text-center">
                                        <svg class="mx-auto h-12 w-12 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                        </svg>
                                        <p class="mt-2 text-xs font-bold text-white" id="idFrontFileName"></p>
                                        <p class="text-[10px] text-gray-400">PDF Document</p>
                                    </div>
                                    <button type="button" onclick="clearPreview('idFront')" class="absolute top-2 right-2 bg-red-500/80 text-white rounded-full p-1 hover:bg-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ID Document Back -->
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Upload ID Document (Back)</label>
                            <div class="border-2 border-dashed border-gray-700 bg-[#080B10]/50 rounded-2xl p-6 text-center hover:border-[#00D4AA] transition">
                                <input type="file" id="idDocumentBack" accept="image/*,.pdf" class="hidden">
                                <label for="idDocumentBack" class="cursor-pointer">
                                    <svg class="mx-auto h-10 w-10 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-xs font-bold text-gray-300">Click to upload or drag & drop</p>
                                    <p class="text-[11px] text-gray-500 mt-1">PNG, JPG, PDF up to 10MB</p>
                                </label>
                            </div>
                            <!-- Preview Area -->
                            <div id="idBackPreviewArea" class="mt-4 hidden">
                                <div class="relative border border-gray-800 bg-[#080B10] rounded-2xl p-4">
                                    <img id="idBackPreviewImage" class="max-w-full h-auto rounded-xl mx-auto" style="max-height: 300px;" />
                                    <div id="idBackPdfPreview" class="hidden p-4 bg-[#0D131F] rounded-xl text-center">
                                        <svg class="mx-auto h-12 w-12 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                        </svg>
                                        <p class="mt-2 text-xs font-bold text-white" id="idBackFileName"></p>
                                        <p class="text-[10px] text-gray-400">PDF Document</p>
                                    </div>
                                    <button type="button" onclick="clearPreview('idBack')" class="absolute top-2 right-2 bg-red-500/80 text-white rounded-full p-1 hover:bg-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="mb-8 pb-8 border-b border-gray-800">
                    <h2 class="text-base font-extrabold text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Bank Account Telemetry (Payout Routing)
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Account Holder Name *</label>
                            <input type="text" id="accountHolderName" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="Name as per bank account">
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bank Name *</label>
                                <input type="text" id="bankName" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs" placeholder="Bank name">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Account Number *</label>
                                <input type="text" id="accountNumber" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs font-mono" placeholder="Account number">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">IFSC Code / Routing Number *</label>
                                <input type="text" id="ifscCode" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs font-mono" placeholder="IFSC/Swift/Routing code">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Account Type *</label>
                                <select id="accountType" required class="w-full px-4 py-2.5 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:ring-1 focus:ring-[#00D4AA] focus:border-[#00D4AA] text-xs">
                                    <option value="">Select type</option>
                                    <option value="savings">Savings</option>
                                    <option value="current">Current / Checking</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="mb-8">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" id="termsAccepted" required class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-[#080B10] border-gray-700 rounded mt-0.5 mr-3">
                        <span class="text-xs text-gray-300 leading-relaxed">
                            I attest that all submitted credentials and account telemetry are valid and authentic. I agree to Nexpert.ai's <a href="<?php echo BASE_PATH; ?>/?panel=page&page=terms" class="text-[#00D4AA] hover:underline font-bold">Terms of Service</a> and <a href="<?php echo BASE_PATH; ?>/?panel=page&page=privacy-policy" class="text-[#00D4AA] hover:underline font-bold">Privacy Policy</a>.
                        </span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-800">
                    <button type="button" id="saveDraftBtn" class="flex-1 bg-[#080B10] border border-gray-700 text-gray-300 py-3 px-6 rounded-xl hover:text-white hover:border-gray-500 transition text-center font-bold text-xs">
                        Save as Draft
                    </button>
                    <button type="submit" class="flex-1 bg-[#00D4AA] text-[#080B10] py-3 px-6 rounded-xl hover:bg-[#00bfa0] transition font-extrabold text-xs shadow-md">
                        Submit for Verification
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-[#0D131F] border border-gray-800 rounded-3xl p-6">
            <h3 class="text-sm font-extrabold text-white mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Verification Telemetry FAQ
            </h3>
            <ul class="space-y-2 text-xs text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] shrink-0 mt-1.5"></span>
                    Verification typically clears within 24-48 hours.
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] shrink-0 mt-1.5"></span>
                    All identity documents must be sharp, legible, and uncropped.
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] shrink-0 mt-1.5"></span>
                    Bank account details must match the legal name submitted.
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    // File preview functionality
    function setupFilePreview(inputId, previewAreaId, previewImageId, pdfPreviewId, fileNameId) {
        const input = document.getElementById(inputId);
        const previewArea = document.getElementById(previewAreaId);
        const previewImage = document.getElementById(previewImageId);
        const pdfPreview = document.getElementById(pdfPreviewId);
        const fileName = document.getElementById(fileNameId);

        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                input.value = '';
                return;
            }

            // Show preview area
            previewArea.classList.remove('hidden');

            // Check if it's PDF or image
            if (file.type === 'application/pdf') {
                // Show PDF preview
                previewImage.classList.add('hidden');
                pdfPreview.classList.remove('hidden');
                fileName.textContent = file.name;
            } else if (file.type.startsWith('image/')) {
                // Show image preview
                pdfPreview.classList.add('hidden');
                previewImage.classList.remove('hidden');
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Clear preview function
    function clearPreview(type) {
        if (type === 'idFront') {
            document.getElementById('idDocumentFront').value = '';
            document.getElementById('idFrontPreviewArea').classList.add('hidden');
            document.getElementById('idFrontPreviewImage').src = '';
        } else if (type === 'idBack') {
            document.getElementById('idDocumentBack').value = '';
            document.getElementById('idBackPreviewArea').classList.add('hidden');
            document.getElementById('idBackPreviewImage').src = '';
        }
    }

    // Setup previews for both upload fields
    setupFilePreview('idDocumentFront', 'idFrontPreviewArea', 'idFrontPreviewImage', 'idFrontPdfPreview', 'idFrontFileName');
    setupFilePreview('idDocumentBack', 'idBackPreviewArea', 'idBackPreviewImage', 'idBackPdfPreview', 'idBackFileName');

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

    let existingFrontDocUrl = '';
    let existingBackDocUrl = '';

    // Load existing KYC data on page load
    async function loadExistingKYC() {
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/kyc.php`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const kyc = result.data;
                existingFrontDocUrl = kyc.id_document_front_url || kyc.id_document_path || '';
                existingBackDocUrl = kyc.id_document_back_url || '';
                
                // Populate form fields with existing data
                if (kyc.full_legal_name) document.getElementById('fullLegalName').value = kyc.full_legal_name;
                if (kyc.date_of_birth) document.getElementById('dob').value = kyc.date_of_birth;
                if (kyc.nationality) document.getElementById('nationality').value = kyc.nationality;
                if (kyc.gender) document.getElementById('gender').value = kyc.gender;
                if (kyc.address_line1) document.getElementById('addressLine1').value = kyc.address_line1;
                if (kyc.city) document.getElementById('city').value = kyc.city;
                if (kyc.state) document.getElementById('state').value = kyc.state;
                if (kyc.postal_code) document.getElementById('postalCode').value = kyc.postal_code;
                if (kyc.country) document.getElementById('country').value = kyc.country;
                if (kyc.id_document_type) document.getElementById('idType').value = kyc.id_document_type;
                if (kyc.id_number) document.getElementById('idNumber').value = kyc.id_number;
                if (kyc.account_holder_name) document.getElementById('accountHolderName').value = kyc.account_holder_name;
                if (kyc.bank_name) document.getElementById('bankName').value = kyc.bank_name;
                if (kyc.account_number) document.getElementById('accountNumber').value = kyc.account_number;
                if (kyc.ifsc_code) document.getElementById('ifscCode').value = kyc.ifsc_code;
                if (kyc.account_type) document.getElementById('accountType').value = kyc.account_type;

                // Show preview if document already exists
                if (existingFrontDocUrl) {
                    const previewArea = document.getElementById('idFrontPreview');
                    const fileName = document.getElementById('idFrontFileName');
                    if (previewArea && fileName) {
                        previewArea.classList.remove('hidden');
                        fileName.textContent = 'Uploaded Document (' + (kyc.id_document_type || 'ID') + ')';
                    }
                }
                
                // Show status if already submitted
                if (kyc.verification_status === 'pending') {
                    console.log('KYC is pending verification review');
                } else if (kyc.verification_status === 'approved') {
                    console.log('KYC is approved');
                } else if (kyc.verification_status === 'rejected') {
                    alert('Your KYC was rejected. Reason: ' + (kyc.rejection_reason || 'Please review and resubmit.'));
                }
            }
        } catch (error) {
            console.error('Error loading KYC data:', error);
        }
    }

    // Load existing data when page loads
    loadExistingKYC();

    // Helper function to build FormData
    function buildKYCFormData(isSubmit) {
        const formData = new FormData();
        formData.append('full_legal_name', document.getElementById('fullLegalName').value);
        formData.append('date_of_birth', document.getElementById('dob').value);
        formData.append('nationality', document.getElementById('nationality').value);
        formData.append('gender', document.getElementById('gender').value);
        formData.append('address_line1', document.getElementById('addressLine1').value);
        formData.append('city', document.getElementById('city').value);
        formData.append('state', document.getElementById('state').value);
        formData.append('postal_code', document.getElementById('postalCode').value);
        formData.append('country', document.getElementById('country').value);
        formData.append('id_document_type', document.getElementById('idType').value);
        formData.append('id_number', document.getElementById('idNumber').value);
        formData.append('account_holder_name', document.getElementById('accountHolderName').value);
        formData.append('bank_name', document.getElementById('bankName').value);
        formData.append('account_number', document.getElementById('accountNumber').value);
        formData.append('ifsc_code', document.getElementById('ifscCode').value);
        formData.append('account_type', document.getElementById('accountType').value);
        formData.append('submit', isSubmit ? '1' : '0');

        const frontFiles = document.getElementById('idDocumentFront').files;
        if (frontFiles.length > 0) {
            formData.append('id_document_front', frontFiles[0]);
        } else if (existingFrontDocUrl) {
            formData.append('id_document_front_url', existingFrontDocUrl);
        }

        const backFiles = document.getElementById('idDocumentBack').files;
        if (backFiles.length > 0) {
            formData.append('id_document_back', backFiles[0]);
        } else if (existingBackDocUrl) {
            formData.append('id_document_back_url', existingBackDocUrl);
        }

        return formData;
    }

    // Save as draft handler
    document.getElementById('saveDraftBtn').addEventListener('click', async function() {
        const formData = buildKYCFormData(false);
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/kyc.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = `${window.BASE_PATH}/index.php?panel=expert&page=dashboard`;
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error saving KYC data. Please try again.');
            console.error(error);
        }
    });

    // Form submission handler
    document.getElementById('kycForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const termsAccepted = document.getElementById('termsAccepted').checked;
        if (!termsAccepted) {
            alert('Please accept the terms and conditions to continue');
            return;
        }

        const idFront = document.getElementById('idDocumentFront').files.length;
        if (!idFront && !existingFrontDocUrl) {
            alert('Please upload your ID document (front side)');
            return;
        }

        const formData = buildKYCFormData(true);

        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/expert/kyc.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = `${window.BASE_PATH}/index.php?panel=expert&page=dashboard`;
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error submitting KYC data. Please try again.');
            console.error(error);
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
