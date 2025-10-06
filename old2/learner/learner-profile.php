<?php
require_once 'includes/session-config.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    // Save the current URL to redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ?panel=learner&page=auth');
    exit;
}

$page_title = "Profile - Nexpert.ai";
$panel_type = "learner";
require_once 'includes/header.php';
require_once 'includes/navigation.php';
?>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Profile Settings</h1>
            <p class="text-gray-600">Manage your personal information and preferences</p>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form id="profile-form">
                <!-- Profile Photo -->
                <div class="flex items-center mb-8">
                    <img id="profile-photo" src="attached_assets/stock_images/professional_busines_a5bb892c.jpg" alt="Profile" class="w-20 h-20 rounded-full object-cover mr-6">
                    <div>
                        <input type="file" id="photo-upload" accept="image/jpeg,image/png,image/jpg" class="hidden">
                        <button type="button" id="change-photo-btn" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition">
                            Change Photo
                        </button>
                        <p class="text-gray-600 text-sm mt-2">JPG, PNG up to 10MB</p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select id="timezone" name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="Asia/Kolkata">India Standard Time (IST)</option>
                            <option value="America/Los_Angeles">Pacific Standard Time (PST)</option>
                            <option value="America/New_York">Eastern Standard Time (EST)</option>
                            <option value="America/Chicago">Central Standard Time (CST)</option>
                            <option value="Europe/London">Greenwich Mean Time (GMT)</option>
                        </select>
                    </div>
                </div>

                <!-- Goals & Preferences -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Learning Goals</label>
                    <textarea id="learning_goals" name="learning_goals" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Describe your learning goals and what you want to achieve..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <button type="button" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

<script>
let profileData = {};

// Load profile data
async function loadProfile() {
    try {
        const response = await fetch('/admin-panel/apis/learner/profile.php');
        const result = await response.json();
        
        if (result.success) {
            profileData = result.profile;
            
            // Populate form
            document.getElementById('full_name').value = profileData.full_name || '';
            document.getElementById('email').value = profileData.email || '';
            document.getElementById('phone').value = profileData.phone || '';
            document.getElementById('timezone').value = profileData.timezone || 'Asia/Kolkata';
            document.getElementById('learning_goals').value = profileData.learning_goals || '';
            
            // Update profile photo
            if (profileData.profile_photo) {
                document.getElementById('profile-photo').src = profileData.profile_photo;
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
    
    const formData = new FormData();
    formData.append('profile_photo', file);
    
    try {
        const response = await fetch('/admin-panel/apis/learner/profile.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('profile-photo').src = result.photo_url;
            alert('Profile photo updated successfully!');
        } else {
            alert(result.message || 'Failed to upload photo');
        }
    } catch (error) {
        console.error('Error uploading photo:', error);
        alert('Failed to upload photo');
    }
});

// Handle form submission
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        full_name: document.getElementById('full_name').value,
        phone: document.getElementById('phone').value,
        timezone: document.getElementById('timezone').value,
        learning_goals: document.getElementById('learning_goals').value
    };
    
    try {
        const response = await fetch('/admin-panel/apis/learner/profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Profile updated successfully!');
            loadProfile(); // Reload data
        } else {
            alert(result.message || 'Failed to update profile');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('Failed to update profile');
    }
});

// Load profile on page load
loadProfile();
</script>

<?php require_once 'includes/footer.php'; ?>
