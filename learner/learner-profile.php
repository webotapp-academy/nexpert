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

$page_title = "Profile - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';
?>
    <script>
        document.body.className = "bg-[#080B10] min-h-screen text-white";
    </script>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Profile Settings</h1>
            <p class="text-gray-400">Manage your personal information and preferences</p>
        </div>

        <!-- Profile Form -->
        <div class="bg-[#131b2e] border border-gray-800 rounded-xl shadow-lg p-8">
            <form id="profile-form">
                <!-- Profile Photo -->
                <div class="flex items-center mb-8">
                    <img id="profile-photo" src="<?php echo BASE_PATH; ?>/attached_assets/stock_images/diverse_professional_1d96e39f.jpg" alt="Profile" class="w-20 h-20 rounded-full object-cover mr-6 border border-gray-800">
                    <div>
                        <input type="file" id="photo-upload" accept="image/jpeg,image/png,image/jpg" class="hidden">
                        <button type="button" id="change-photo-btn" class="bg-[#00D4AA] text-[#080B10] px-4 py-2 rounded-lg hover:bg-[#00bda0] transition font-bold">
                            Change Photo
                        </button>
                        <p class="text-gray-400 text-sm mt-2">JPG, PNG up to 10MB</p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-3 py-2 bg-gray-800 border border-gray-700 text-gray-400 rounded-lg cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                        <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Timezone</label>
                        <select id="timezone" name="timezone" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent">
                            <option class="bg-[#131b2e] text-white" value="Asia/Kolkata">India Standard Time (IST)</option>
                            <option class="bg-[#131b2e] text-white" value="America/Los_Angeles">Pacific Standard Time (PST)</option>
                            <option class="bg-[#131b2e] text-white" value="America/New_York">Eastern Standard Time (EST)</option>
                            <option class="bg-[#131b2e] text-white" value="America/Chicago">Central Standard Time (CST)</option>
                            <option class="bg-[#131b2e] text-white" value="Europe/London">Greenwich Mean Time (GMT)</option>
                        </select>
                    </div>
                </div>

                <!-- Goals & Preferences -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Learning Profile</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Learning Goals</label>
                            <textarea id="learning_goals" name="learning_goals" rows="3" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent" placeholder="What do you want to learn and achieve?"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Challenges</label>
                            <textarea id="challenges" name="challenges" rows="3" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent" placeholder="What challenges are you facing in your learning journey?"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Professional Background</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Education</label>
                            <input type="text" id="education" name="education" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent" placeholder="e.g., B.Tech in Computer Science">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Current Profession</label>
                            <input type="text" id="profession" name="profession" class="w-full px-3 py-2 bg-[#0d131f] border border-gray-800 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent" placeholder="e.g., Software Engineer">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-800">
                    <button type="button" id="cancel-btn" class="px-6 py-2 border border-gray-800 text-gray-300 rounded-lg hover:bg-gray-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg hover:bg-[#00bda0] transition font-bold shadow-md">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

<script>
    // Set BASE_PATH globally
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    let profileData = {};

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

    // Load profile data
    async function loadProfile() {
        try {
            console.log('Loading profile from:', `${window.BASE_PATH}/admin-panel/apis/learner/profile.php`);
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/profile.php`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'include'
            });
            console.log('Profile response status:', response.status);
            console.log('Profile response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Profile result:', result);
            
            if (result.success) {
                profileData = result.profile;
                
                // Populate form
                document.getElementById('full_name').value = profileData.full_name || '';
                document.getElementById('email').value = profileData.email || '';
                document.getElementById('phone').value = profileData.phone || '';
                document.getElementById('timezone').value = profileData.timezone || 'Asia/Kolkata';
                document.getElementById('learning_goals').value = profileData.goals || '';
                document.getElementById('challenges').value = profileData.challenges || '';
                document.getElementById('education').value = profileData.education || '';
                document.getElementById('profession').value = profileData.profession || '';
                
                // Update profile photo
                if (profileData.profile_photo) {
                    document.getElementById('profile-photo').src = resolveImagePath(profileData.profile_photo);
                }
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    // Handle profile photo change
    document.getElementById('change-photo-btn').addEventListener('click', function() {
        document.getElementById('photo-upload').click();
    });

    document.getElementById('photo-upload').addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        console.log('Photo upload started:', file.name, 'Size:', file.size, 'Type:', file.type);
        
        // Validate file size
        if (file.size > 10 * 1024 * 1024) { // 10MB
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'File size must be less than 10MB',
                confirmButtonColor: '#3B82F6'
            });
            return;
        }
        
        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Only JPG and PNG files are allowed',
                confirmButtonColor: '#3B82F6'
            });
            return;
        }
        
        const formData = new FormData();
        formData.append('profile_photo', file);
        
        console.log('Sending photo to:', `${window.BASE_PATH}/admin-panel/apis/learner/profile.php`);
        
        try {
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/profile.php`, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });
            
            console.log('Photo upload response status:', response.status);
            console.log('Photo upload response ok:', response.ok);
            
            const result = await response.json();
            console.log('Photo upload result:', result);
            
            if (result.success) {
                document.getElementById('profile-photo').src = resolveImagePath(result.photo_url);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Profile photo updated successfully!',
                    confirmButtonColor: '#3B82F6'
                });
            } else {
                console.error('Photo upload failed:', result);
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: result.message || 'Failed to upload photo',
                    confirmButtonColor: '#3B82F6'
                });
            }
        } catch (error) {
            console.error('Error uploading photo:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to upload photo. Please try again.',
                confirmButtonColor: '#3B82F6'
            });
        }
    });

    // Handle form submission
    // Initialize profile loading
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded, loading profile...');
        loadProfile();
    });

    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('Form submitted!');
        
        const formData = {
            full_name: document.getElementById('full_name').value,
            phone: document.getElementById('phone').value,
            timezone: document.getElementById('timezone').value,
            learning_goals: document.getElementById('learning_goals').value,
            challenges: document.getElementById('challenges').value,
            education: document.getElementById('education').value,
            profession: document.getElementById('profession').value
        };
        
        console.log('Form data:', formData);
        
        try {
            console.log('Sending request to:', `${window.BASE_PATH}/admin-panel/apis/learner/profile.php`);
            
            const response = await fetch(`${window.BASE_PATH}/admin-panel/apis/learner/profile.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            const result = await response.json();
            console.log('Response result:', result);
            
            if (result.success) {
                // Use SweetAlert2 for better UX with redirect
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Profile updated successfully! Redirecting to home...',
                    confirmButtonColor: '#3B82F6',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    // Redirect to home page
                    window.location.href = `${window.BASE_PATH}/index.php`;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to update profile',
                    confirmButtonColor: '#3B82F6'
                });
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update profile. Please try again.',
                confirmButtonColor: '#3B82F6'
            });
        }
    });

    // Handle cancel button click - redirect to home
    document.getElementById('cancel-btn').addEventListener('click', function() {
        window.location.href = `${window.BASE_PATH}/index.php`;
    });

    // Load profile on page load
    loadProfile();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
