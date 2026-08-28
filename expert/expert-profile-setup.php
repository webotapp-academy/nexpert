<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

// Fetch active categories from database
$categoriesStmt = $pdo->query("SELECT name, slug FROM categories WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
$activeCategories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Profile Setup - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-extrabold text-white mb-2 tracking-tight">Complete Your Expert Profile</h1>
            <p class="text-gray-400 text-sm">Set up your profile, pricing, and availability to start connecting with learners</p>
        </div>

        <!-- Step Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <!-- Step 1 -->
                <div id="step1Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-[#00D4AA] text-[#080B10] font-extrabold rounded-full flex items-center justify-center text-sm shadow-md">
                        1
                    </div>
                    <span class="ml-2 text-sm text-[#00D4AA] font-bold hidden sm:inline">Profile Info</span>
                </div>
                <div id="line1" class="w-16 h-1 bg-gray-800 mx-2"></div>
                <!-- Step 2 -->
                <div id="step2Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-gray-800 text-gray-400 font-bold rounded-full flex items-center justify-center text-sm">
                        2
                    </div>
                    <span class="ml-2 text-sm text-gray-500 font-medium hidden sm:inline">Pricing</span>
                </div>
                <div id="line2" class="w-16 h-1 bg-gray-800 mx-2"></div>
                <!-- Step 3 -->
                <div id="step3Indicator" class="flex items-center">
                    <div class="w-10 h-10 bg-gray-800 text-gray-400 font-bold rounded-full flex items-center justify-center text-sm">
                        3
                    </div>
                    <span class="ml-2 text-sm text-gray-500 font-medium hidden sm:inline">Availability</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl shadow-xl p-6 md:p-10">
            <!-- Step 1: Profile Information -->
            <div id="step1Content" class="step-content">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA]"></span>
                    Profile Information
                </h2>
                
                <!-- Profile Photo -->
                <div class="flex items-center space-x-6 mb-6 p-4 rounded-xl bg-[#080B10] border border-gray-800/80">
                    <div id="profilePhotoPreview" class="w-20 h-20 rounded-2xl bg-[#131B2E] border-2 border-gray-800 flex items-center justify-center text-gray-500 overflow-hidden">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <div>
                        <input type="file" id="profilePhotoInput" accept="image/*" class="hidden">
                        <button type="button" id="uploadPhotoBtn" class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#00bfa0] transition shadow-md">
                            Upload Photo
                        </button>
                        <p class="text-gray-500 text-xs mt-1.5">JPG or PNG, recommended 500x500px</p>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Full Name *</label>
                        <input type="text" id="fullName" placeholder="Enter your full name" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Professional Title *</label>
                        <input type="text" id="tagline" placeholder="e.g., Senior UX Designer, Distributed Systems Architect" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Years of Experience *</label>
                            <select id="experience" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                                <option value="">Select years</option>
                                <option value="2">1-2 years</option>
                                <option value="4">3-5 years</option>
                                <option value="7">5-8 years</option>
                                <option value="9">8-10 years</option>
                                <option value="10">10+ years</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Location *</label>
                            <input type="text" id="location" placeholder="City, Country" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Professional Bio *</label>
                        <textarea rows="4" id="bioText" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition resize-none" placeholder="Tell learners about your background, experience, and what makes your mentorship unique..."></textarea>
                        <p class="text-gray-500 text-xs mt-1">Minimum 100 characters</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Primary Expert Category *</label>
                        <select id="category" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                            <option value="">Select category</option>
                            <?php foreach ($activeCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Expertise Tags *</label>
                        <div id="expertiseTags" class="flex flex-wrap gap-2 mb-3"></div>
                        <input type="text" id="expertiseInput" placeholder="Add expertise tags (press Enter)" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                        <p class="text-gray-500 text-xs mt-1">Add skills, tools, or domain focus areas. Max 10 tags.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Pricing Models -->
            <div id="step2Content" class="step-content hidden">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA]"></span>
                    Pricing & Rates
                </h2>
                
                <div class="space-y-6">
                    <!-- Per Session -->
                    <div class="bg-[#080B10] border border-gray-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-white text-sm">Hourly Session Rate</h3>
                                <p class="text-gray-400 text-xs">Standard one-on-one video call sessions</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enablePerSession" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">60 minutes (₹)</label>
                                <input type="number" id="price60" placeholder="1500" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">30 minutes (₹)</label>
                                <input type="number" id="price30" placeholder="800" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Package Deal -->
                    <div class="bg-[#080B10] border border-gray-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-white text-sm">Multi-Session Package</h3>
                                <p class="text-gray-400 text-xs">Bundle packages for outcome-driven mentorship</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enablePackage" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                            </label>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">4 Sessions (₹)</label>
                                <input type="number" id="package4" placeholder="5000" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">8 Sessions (₹)</label>
                                <input type="number" id="package8" placeholder="9500" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">12 Sessions (₹)</label>
                                <input type="number" id="package12" placeholder="13500" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Subscription -->
                    <div class="bg-[#080B10] border border-gray-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-white text-sm">Monthly Retainer</h3>
                                <p class="text-gray-400 text-xs">Ongoing monthly mentorship</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="enableSubscription" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00D4AA]"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 mb-1">Monthly Retainer Price (₹)</label>
                            <input type="number" id="subscriptionPrice" placeholder="12000" class="w-full px-3 py-2 bg-[#131B2E] border border-gray-700 rounded-lg focus:outline-none focus:border-[#00D4AA] text-white text-sm">
                            <p class="text-gray-500 text-xs mt-1">Suggested: Includes 4 sessions + async chat guidance</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Availability -->
            <div id="step3Content" class="step-content hidden">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA]"></span>
                    Availability Schedule
                </h2>
                
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Timezone</label>
                    <select id="timezone" class="w-full px-4 py-3 bg-[#080B10] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-sm transition">
                        <option>IST (India Standard Time — UTC+05:30)</option>
                        <option>UTC (Coordinated Universal Time)</option>
                        <option>EST (Eastern Standard Time)</option>
                        <option>PST (Pacific Standard Time)</option>
                        <option>GMT (Greenwich Mean Time)</option>
                    </select>
                </div>

                <div class="space-y-3 bg-[#080B10] p-4 rounded-xl border border-gray-800">
                    <?php
                    $days = ['monday'=>'Monday', 'tuesday'=>'Tuesday', 'wednesday'=>'Wednesday', 'thursday'=>'Thursday', 'friday'=>'Friday', 'saturday'=>'Saturday', 'sunday'=>'Sunday'];
                    foreach ($days as $id=>$d):
                    ?>
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-800/80 last:border-0">
                        <div class="flex items-center">
                            <input type="checkbox" id="<?= $id ?>" class="h-4 w-4 text-[#00D4AA] focus:ring-[#00D4AA] bg-gray-900 border-gray-700 rounded mr-3" <?= in_array($id, ['monday','tuesday','wednesday','thursday','friday']) ? 'checked' : '' ?>>
                            <label for="<?= $id ?>" class="font-semibold text-sm text-gray-200"><?= $d ?></label>
                        </div>
                        <div class="flex items-center space-x-2 text-xs">
                            <select id="<?= $id ?>Start" class="px-2.5 py-1.5 bg-[#131B2E] border border-gray-700 rounded-lg text-white">
                                <option>09:00 AM</option><option>10:00 AM</option><option>11:00 AM</option><option>12:00 PM</option><option>02:00 PM</option><option>04:00 PM</option><option>06:00 PM</option>
                            </select>
                            <span class="text-gray-500">to</span>
                            <select id="<?= $id ?>End" class="px-2.5 py-1.5 bg-[#131B2E] border border-gray-700 rounded-lg text-white">
                                <option>05:00 PM</option><option>06:00 PM</option><option>07:00 PM</option><option>08:00 PM</option><option>09:00 PM</option><option>10:00 PM</option>
                            </select>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8 pt-6 border-t border-gray-800">
                <button id="prevBtn" class="px-6 py-2.5 border border-gray-700 bg-[#080B10] text-gray-300 rounded-xl hover:text-white hover:border-gray-500 transition text-sm font-bold disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                    Previous
                </button>
                <button id="nextBtn" class="px-7 py-2.5 bg-[#00D4AA] text-[#080B10] rounded-xl hover:bg-[#00bfa0] transition text-sm font-extrabold shadow-md">
                    Next Step
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        const expertiseTags = [];

        // Load basic info from registration
        const basicInfo = JSON.parse(sessionStorage.getItem('expertBasicInfo') || '{}');
        if (basicInfo.name) {
            document.getElementById('fullName').value = basicInfo.name;
        }

        // Step Navigation
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            
            // Show current step
            document.getElementById(`step${step}Content`).classList.remove('hidden');
            
            // Update indicators
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step${i}Indicator`).querySelector('div');
                const label = document.getElementById(`step${i}Indicator`).querySelector('span');
                const line = document.getElementById(`line${i}`);
                
                if (i < step) {
                    indicator.className = 'w-10 h-10 bg-emerald-500 text-[#080B10] font-extrabold rounded-full flex items-center justify-center text-sm shadow-md';
                    indicator.textContent = '✓';
                    label.className = 'ml-2 text-sm text-emerald-400 font-bold hidden sm:inline';
                    if (line) line.className = 'w-16 h-1 bg-emerald-500 mx-2';
                } else if (i === step) {
                    indicator.className = 'w-10 h-10 bg-[#00D4AA] text-[#080B10] font-extrabold rounded-full flex items-center justify-center text-sm shadow-md';
                    indicator.textContent = i;
                    label.className = 'ml-2 text-sm text-[#00D4AA] font-bold hidden sm:inline';
                    if (line) line.className = 'w-16 h-1 bg-gray-800 mx-2';
                } else {
                    indicator.className = 'w-10 h-10 bg-gray-800 text-gray-400 font-bold rounded-full flex items-center justify-center text-sm';
                    indicator.textContent = i;
                    label.className = 'ml-2 text-sm text-gray-500 font-medium hidden sm:inline';
                    if (line) line.className = 'w-16 h-1 bg-gray-800 mx-2';
                }
            }
            
            // Update buttons
            document.getElementById('prevBtn').disabled = step === 1;
            document.getElementById('nextBtn').textContent = step === totalSteps ? 'Complete Profile' : 'Next Step';
        }

        // Previous Button
        document.getElementById('prevBtn').addEventListener('click', () => {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Next Button
        document.getElementById('nextBtn').addEventListener('click', () => {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            } else {
                // Save complete profile
                saveCompleteProfile();
            }
        });

        // Profile Photo Upload
        document.getElementById('uploadPhotoBtn').addEventListener('click', () => {
            document.getElementById('profilePhotoInput').click();
        });

        document.getElementById('profilePhotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePhotoPreview');
                    preview.innerHTML = `<img src="${e.target.result}" class="w-24 h-24 rounded-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Expertise Tags
        document.getElementById('expertiseInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tag = this.value.trim();
                if (tag && expertiseTags.length < 10 && !expertiseTags.includes(tag)) {
                    expertiseTags.push(tag);
                    updateExpertiseTags();
                    this.value = '';
                }
            }
        });

        function updateExpertiseTags() {
            const container = document.getElementById('expertiseTags');
            container.innerHTML = expertiseTags.map(tag => `
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-accent/10 text-accent">
                    ${tag}
                    <button onclick="removeTag('${tag}')" class="ml-2 focus:outline-none">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>
            `).join('');
        }

        function removeTag(tag) {
            const index = expertiseTags.indexOf(tag);
            if (index > -1) {
                expertiseTags.splice(index, 1);
                updateExpertiseTags();
            }
        }

        // Save Complete Profile Function
        async function saveCompleteProfile() {
            const nextBtn = document.getElementById('nextBtn');
            nextBtn.disabled = true;
            nextBtn.textContent = 'Saving...';

            // Collect Step 1 data
            const fullName = document.getElementById('fullName').value;
            const tagline = document.getElementById('tagline').value;
            const bioText = document.getElementById('bioText').value;
            const category = document.getElementById('category').value;
            const experience = document.getElementById('experience').value;

            // Validate required fields
            if (!fullName || !tagline || !bioText || !category || !experience) {
                alert('Please fill all required fields in Profile Information');
                nextBtn.disabled = false;
                nextBtn.textContent = 'Complete Profile';
                return;
            }

            if (bioText.length < 100) {
                alert('Bio must be at least 100 characters long');
                nextBtn.disabled = false;
                nextBtn.textContent = 'Complete Profile';
                return;
            }

            if (expertiseTags.length === 0) {
                alert('Please add at least one expertise tag');
                nextBtn.disabled = false;
                nextBtn.textContent = 'Complete Profile';
                return;
            }

            // Prepare profile data
            const profileData = {
                user_id: <?php echo $_SESSION['user_id']; ?>,
                full_name: fullName,
                tagline: tagline,
                bio_short: bioText.substring(0, 200),
                bio_full: bioText,
                expertise_verticals: expertiseTags,
                category: category,
                experience_years: parseInt(experience),
                timezone: document.getElementById('timezone').value
            };

            try {
                const response = await fetch(`${BASE_PATH}/admin-panel/apis/expert/profile.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(profileData)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Profile setup complete! Redirecting to dashboard...');
                    window.location.href = `${BASE_PATH}/index.php?panel=expert&page=dashboard`;
                } else {
                    alert('Error: ' + (result.message || 'Failed to save profile'));
                    nextBtn.disabled = false;
                    nextBtn.textContent = 'Complete Profile';
                }
            } catch (error) {
                console.error('Save profile error:', error);
                alert('Error saving profile. Please try again.');
                nextBtn.disabled = false;
                nextBtn.textContent = 'Complete Profile';
            }
        }

        // Initialize
        showStep(1);
    </script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
