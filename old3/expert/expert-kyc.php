<?php
$page_title = "KYC Verification - Nexpert.ai";
$panel_type = "expert";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">KYC Verification</h1>
        <p class="text-gray-600">Complete your identity verification to start earning and receiving payouts</p>
    </div>

    <!-- Verification Status -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Verification Pending:</strong> Your KYC documents are under review. You cannot receive payouts until verification is complete.
                </p>
            </div>
        </div>
    </div>

    <!-- KYC Form -->
    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8">
        <form id="kycForm">
            <!-- Personal Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Legal Name *</label>
                        <input type="text" id="fullLegalName" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="As per government ID">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" id="dob" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nationality *</label>
                        <select id="nationality" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                        <select id="gender" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
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
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Address Information
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                        <input type="text" id="addressLine1" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="House/Flat number, Street name">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                            <input type="text" id="city" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province *</label>
                            <input type="text" id="state" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="State/Province">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal/ZIP Code *</label>
                            <input type="text" id="postalCode" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Postal code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <select id="country" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
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
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                    </svg>
                    Identity Verification Documents
                </h2>
                
                <div class="space-y-6">
                    <!-- ID Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Document Type *</label>
                        <select id="idType" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                        <input type="text" id="idNumber" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Enter your ID number">
                    </div>

                    <!-- ID Document Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload ID Document (Front) *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-accent transition">
                            <input type="file" id="idDocumentFront" accept="image/*,.pdf" class="hidden">
                            <label for="idDocumentFront" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
                            </label>
                            <div id="idFrontPreview" class="mt-3 text-sm text-green-600 hidden"></div>
                        </div>
                    </div>

                    <!-- ID Document Back -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload ID Document (Back)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-accent transition">
                            <input type="file" id="idDocumentBack" accept="image/*,.pdf" class="hidden">
                            <label for="idDocumentBack" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
                            </label>
                            <div id="idBackPreview" class="mt-3 text-sm text-green-600 hidden"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Bank Account Details (For Payouts)
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name *</label>
                        <input type="text" id="accountHolderName" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Name as per bank account">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name *</label>
                            <input type="text" id="bankName" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Bank name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Number *</label>
                            <input type="text" id="accountNumber" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="Account number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">IFSC Code / Routing Number *</label>
                            <input type="text" id="ifscCode" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent" placeholder="IFSC/Swift/Routing code">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Type *</label>
                            <select id="accountType" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent">
                                <option value="">Select type</option>
                                <option value="savings">Savings</option>
                                <option value="current">Current/Checking</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="mb-8">
                <label class="flex items-start">
                    <input type="checkbox" id="termsAccepted" required class="h-4 w-4 text-accent focus:ring-accent border-gray-300 rounded mt-1">
                    <span class="ml-3 text-sm text-gray-700">
                        I confirm that all information provided is accurate and complete. I understand that providing false information may result in account suspension. I agree to Nexpert.ai's <a href="#" class="text-accent hover:underline">Terms of Service</a> and <a href="#" class="text-accent hover:underline">Privacy Policy</a>.
                    </span>
                </label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4">
                <button type="button" id="saveDraftBtn" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-300 transition text-center font-semibold">
                    Save as Draft
                </button>
                <button type="submit" class="flex-1 bg-accent text-white py-3 px-6 rounded-lg hover:bg-yellow-600 transition font-semibold">
                    Submit for Verification
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-8 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
        <ul class="space-y-2 text-sm text-gray-700">
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Verification typically takes 24-48 hours
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                All documents must be clear and readable
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Your information is encrypted and secure
            </li>
            <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Contact support@nexpert.ai for assistance
            </li>
        </ul>
    </div>
</div>

<script>
    // File upload preview handlers
    document.getElementById('idDocumentFront').addEventListener('change', function(e) {
        const preview = document.getElementById('idFrontPreview');
        if (e.target.files.length > 0) {
            preview.textContent = '✓ ' + e.target.files[0].name;
            preview.classList.remove('hidden');
        }
    });

    document.getElementById('idDocumentBack').addEventListener('change', function(e) {
        const preview = document.getElementById('idBackPreview');
        if (e.target.files.length > 0) {
            preview.textContent = '✓ ' + e.target.files[0].name;
            preview.classList.remove('hidden');
        }
    });

    // Load existing KYC data on page load
    async function loadExistingKYC() {
        try {
            const response = await fetch('/admin-panel/apis/expert/kyc.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                const kyc = result.data;
                
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
                
                // Show status if already submitted
                if (kyc.verification_status === 'pending') {
                    alert('Your KYC has been submitted and is under review. You can update and resubmit if needed.');
                } else if (kyc.verification_status === 'approved') {
                    alert('Your KYC has been approved!');
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

    // Helper function to collect form data
    function collectFormData() {
        return {
            full_legal_name: document.getElementById('fullLegalName').value,
            date_of_birth: document.getElementById('dob').value,
            nationality: document.getElementById('nationality').value,
            gender: document.getElementById('gender').value,
            address_line1: document.getElementById('addressLine1').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value,
            postal_code: document.getElementById('postalCode').value,
            country: document.getElementById('country').value,
            id_document_type: document.getElementById('idType').value,
            id_number: document.getElementById('idNumber').value,
            id_document_front_url: 'uploads/kyc/front_placeholder.jpg',
            id_document_back_url: 'uploads/kyc/back_placeholder.jpg',
            account_holder_name: document.getElementById('accountHolderName').value,
            bank_name: document.getElementById('bankName').value,
            account_number: document.getElementById('accountNumber').value,
            ifsc_code: document.getElementById('ifscCode').value,
            account_type: document.getElementById('accountType').value
        };
    }

    // Save as draft handler
    document.getElementById('saveDraftBtn').addEventListener('click', async function() {
        const data = collectFormData();
        data.submit = false;
        
        try {
            const response = await fetch('/admin-panel/apis/expert/kyc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = '?panel=expert&page=dashboard';
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
        if (!idFront) {
            alert('Please upload your ID document (front side)');
            return;
        }

        const data = collectFormData();
        data.submit = true;

        try {
            const response = await fetch('/admin-panel/apis/expert/kyc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message + ' Our team will review your documents within 24-48 hours.');
                window.location.href = '?panel=expert&page=dashboard';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error submitting KYC data. Please try again.');
            console.error(error);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
