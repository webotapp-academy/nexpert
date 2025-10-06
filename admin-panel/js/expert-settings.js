// Expert Settings JavaScript
(function() {
    'use strict';

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Settings are already loaded from PHP on page load, no need for additional AJAX loading

    // Helper function to set toggle state
    function setToggleState(id, isChecked) {
        const toggle = document.getElementById(id);
        if (toggle) {
            toggle.checked = isChecked;
        }
    }

    // Profile photo upload
    const profilePhotoInput = document.getElementById('profilePhoto');
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);

            try {
                const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
                const response = await fetch(basePath + '/admin-panel/apis/expert/upload-photo.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showToast('Profile photo updated successfully', 'success');
                    // Update the preview image
                    const previewImg = document.querySelector('#content-profile img');
                    if (previewImg) {
                        previewImg.src = result.photo_url;
                    }
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Failed to upload photo', 'error');
            }
        });
    }

    // Profile form submission
    document.getElementById('profileForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            section: 'profile',
            full_name: this.querySelector('input[name="full_name"]')?.value || '',
            tagline: this.querySelector('input[name="tagline"]')?.value || '',
            bio_short: this.querySelector('textarea[name="bio_short"]')?.value || '',
            bio_full: this.querySelector('textarea[name="bio_full"]')?.value || '',
            expertise_verticals: this.querySelector('input[name="expertise_verticals"]')?.value || '',
            credentials: this.querySelector('textarea[name="credentials"]')?.value || '',
            experience_years: this.querySelector('select[name="experience_years"]')?.value || '',
            timezone: this.querySelector('input[name="timezone"]')?.value || 'UTC'
        };

        try {
            const response = await fetch('/admin-panel/apis/expert/settings.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            showToast('Failed to update profile', 'error');
        }
    });

    // Bank form submission
    document.getElementById('bankForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const accountNum = this.querySelector('input[name="account_number"]')?.value || '';
        const confirmAccountNum = this.querySelector('input[name="account_number_confirm"]')?.value || '';

        // Check if account number is masked (starts with ****)
        if (accountNum.startsWith('****')) {
            showToast('Please enter your full account number to update', 'error');
            return;
        }

        if (confirmAccountNum && accountNum !== confirmAccountNum) {
            showToast('Account numbers do not match', 'error');
            return;
        }

        const formData = {
            section: 'bank',
            account_holder_name: this.querySelector('input[name="account_holder_name"]')?.value || '',
            bank_name: this.querySelector('input[name="bank_name"]')?.value || '',
            branch_name: this.querySelector('input[name="branch_name"]')?.value || '',
            account_number: accountNum,
            ifsc_code: this.querySelector('input[name="ifsc_code"]')?.value || '',
            account_type: this.querySelector('select[name="account_type"]')?.value || ''
        };

        try {
            const response = await fetch('/admin-panel/apis/expert/settings.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            showToast('Failed to update bank details', 'error');
        }
    });

    // Password form submission
    document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const currentPassword = this.querySelectorAll('input[type="password"]')[0].value;
        const newPassword = this.querySelectorAll('input[type="password"]')[1].value;
        const confirmPassword = this.querySelectorAll('input[type="password"]')[2].value;

        if (newPassword !== confirmPassword) {
            showToast('New passwords do not match', 'error');
            return;
        }

        if (newPassword.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }

        const formData = {
            section: 'password',
            current_password: currentPassword,
            new_password: newPassword
        };

        try {
            const response = await fetch('/admin-panel/apis/expert/settings.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                this.reset();
            }
        } catch (error) {
            showToast('Failed to update password', 'error');
        }
    });

    // Toggle change handler for privacy settings
    async function handleToggleChange(section, setting, value) {
        const data = { section };
        data[setting] = value;

        try {
            const response = await fetch('/admin-panel/apis/expert/settings.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                showToast('Setting updated', 'success');
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Failed to update setting', 'error');
        }
    }

    // Privacy toggles
    const privacyToggles = [
        { id: 'privacy-show-search', setting: 'show_in_search' },
        { id: 'privacy-show-email', setting: 'show_email' },
        { id: 'privacy-accept-bookings', setting: 'accept_bookings' }
    ];

    privacyToggles.forEach(({ id, setting }) => {
        const toggle = document.getElementById(id);
        if (toggle) {
            toggle.addEventListener('change', function() {
                handleToggleChange('privacy', setting, this.checked);
            });
        }
    });

    // Two-factor authentication toggle
    const twoFactorToggle = document.getElementById('two-factor');
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', async function() {
            const data = {
                section: 'two_factor',
                enabled: this.checked
            };

            try {
                const response = await fetch('/admin-panel/apis/expert/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
            } catch (error) {
                showToast('Failed to update two-factor authentication', 'error');
            }
        });
    }

    // Notification preferences save button
    const notifSaveBtn = document.querySelector('#content-notifications button');
    if (notifSaveBtn) {
        notifSaveBtn.addEventListener('click', async function() {
            const data = {
                section: 'notifications',
                notify_booking_email: document.getElementById('notify-booking')?.checked || false,
                notify_payment_email: document.getElementById('notify-payment')?.checked || false,
                notify_reminder_email: document.getElementById('notify-reminder')?.checked || false,
                notify_marketing_email: document.getElementById('notify-marketing')?.checked || false,
                notify_urgent_sms: document.getElementById('notify-sms')?.checked || false
            };

            try {
                const response = await fetch('/admin-panel/apis/expert/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
            } catch (error) {
                showToast('Failed to update notification preferences', 'error');
            }
        });
    }

    // Privacy settings save button
    const privacySaveBtn = document.querySelector('#content-privacy > div > button:last-child');
    if (privacySaveBtn) {
        privacySaveBtn.addEventListener('click', async function() {
            const data = {
                section: 'privacy',
                show_in_search: document.getElementById('privacy-show-search')?.checked || false,
                show_email: document.getElementById('privacy-show-email')?.checked || false,
                accept_bookings: document.getElementById('privacy-accept-bookings')?.checked || false
            };

            try {
                const response = await fetch('/admin-panel/apis/expert/settings.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
            } catch (error) {
                showToast('Failed to update privacy settings', 'error');
            }
        });
    }

    // Availability form submission
    document.getElementById('availabilityForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            section: 'availability',
            day_of_week: this.querySelector('select[name="day_of_week"]')?.value || '',
            start_time: this.querySelector('input[name="start_time"]')?.value || '',
            end_time: this.querySelector('input[name="end_time"]')?.value || ''
        };

        if (!formData.day_of_week || !formData.start_time || !formData.end_time) {
            showToast('Please fill in all fields', 'error');
            return;
        }

        try {
            const response = await fetch('/admin-panel/apis/expert/settings.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');
            
            if (result.success) {
                this.reset();
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            showToast('Failed to add availability', 'error');
        }
    });

    // Settings are already loaded from PHP, no DOMContentLoaded listener needed
})();
