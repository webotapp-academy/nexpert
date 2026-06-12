// Expert Settings JavaScript
(function() {
    'use strict';

    // Use BASE_PATH from global scope (defined in header.php)
    // In strict mode IIFE, we need to access it explicitly
    const basePath = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : '';
    console.log('Expert Settings JS: basePath =', basePath);

    // Default profile photo
    const DEFAULT_PROFILE_PHOTO = `attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;

    // Utility function to show toast notifications
    function showToast(message, type = 'success') {
        try {
            console.log(`Toast (${type}):`, message);
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: type,
                title: message,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        } catch (error) {
            console.error('Toast notification error:', error);
            alert(message);
        }
    }

    // Enhanced image loading function
    function loadImage(src, altSrc = DEFAULT_PROFILE_PHOTO) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            
            // Track image loading states
            img.addEventListener('load', () => {
                console.log('Image loaded successfully:', src);
                resolve(img.src);
            });
            
            img.addEventListener('error', () => {
                console.error('Failed to load image:', src);
                
                // Try alternative source if provided
                if (altSrc && altSrc !== src) {
                    console.log('Attempting to load alternative image:', altSrc);
                    img.src = altSrc;
                } else {
                    console.error('All image loading attempts failed');
                    reject(new Error('Image loading failed'));
                }
            });
            
            // Start loading the image
            img.src = src;
        });
    }

    // Utility function to resolve image path
    function resolveImagePath(imagePath) {
        console.log('Resolving image path:', imagePath);
        
        // If it's a full URL or data URI, return as-is
        if (/^(https?:\/\/|data:)/.test(imagePath)) {
            console.log('Full URL or data URI detected');
            return imagePath;
        }
        
        // If no image path, use default
        if (!imagePath) {
            console.log('No image path, using default');
            return `${basePath}/${DEFAULT_PROFILE_PHOTO}`;
        }
        
        // If path already starts with basePath, return as-is
        if (imagePath.startsWith(basePath + '/')) {
            console.log('Path already has basePath:', imagePath);
            return imagePath;
        }
        
        // If path starts with /, it's an absolute path from document root
        if (imagePath.startsWith('/')) {
            console.log('Absolute path detected:', imagePath);
            return imagePath;
        }
        
        // Otherwise, prepend basePath
        const fullPath = `${basePath}/${imagePath}`;
        console.log('Constructed full path:', fullPath);
        return fullPath;
    }

    // Load profile data on page load
    async function loadProfileData() {
        try {
            console.log('Starting loadProfileData function');
            const profilePhotoPreview = document.getElementById('profilePhotoPreview');
            const profilePhotoImg = profilePhotoPreview ? profilePhotoPreview.querySelector('img') : null;

            console.log('Fetching profile data...');
            const response = await fetch(`${basePath}/admin-panel/apis/expert/profile-data.php`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Profile data response:', result);
            
            if (!result.success) {
                throw new Error(result.message || 'Error loading profile data');
            }

            const profileData = result.data || {};
            console.log('Profile data:', profileData);

            // Detailed logging for profile photo
            console.log('Profile photo before resolution:', profileData.profile_photo);
            const photoSrc = resolveImagePath(profileData.profile_photo);
            console.log('Resolved photo src:', photoSrc);

            // Update profile photo with enhanced loading
            if (profilePhotoImg) {
                try {
                    const loadedSrc = await loadImage(photoSrc);
                    console.log('Setting photo src on img element:', loadedSrc);
                    profilePhotoImg.src = loadedSrc;
                } catch (error) {
                    console.error('Failed to load profile photo:', error);
                    profilePhotoImg.src = DEFAULT_PROFILE_PHOTO;
                }
            } else {
                console.error('Profile photo img element not found');
            }

            // Populate other form fields
            const fullNameInput = document.querySelector('input[name="full_name"]');
            if (fullNameInput) {
                fullNameInput.value = profileData.full_name || '';
                console.log('Full name set:', fullNameInput.value);
            }

            const taglineInput = document.querySelector('input[name="tagline"]');
            if (taglineInput) {
                taglineInput.value = profileData.tagline || '';
                console.log('Tagline set:', taglineInput.value);
            }

            const categorySelect = document.querySelector('select[name="category"]');
            if (categorySelect) {
                categorySelect.value = profileData.category || '';
                console.log('Category set:', categorySelect.value);
            }

            const bioFullTextarea = document.querySelector('textarea[name="bio_full"]');
            if (bioFullTextarea) {
                bioFullTextarea.value = profileData.bio_full || '';
                console.log('Bio set:', bioFullTextarea.value);
            }

            const tagsInput = document.querySelector('input[name="tags"]');
            if (tagsInput) {
                tagsInput.value = profileData.tags || '';
                console.log('Tags set:', tagsInput.value);
            }
            
            const experienceYearsSelect = document.querySelector('select[name="experience_years"]');
            if (experienceYearsSelect) {
                const options = experienceYearsSelect.options;
                const experienceYears = profileData.experience_years;
                console.log('Experience years:', experienceYears);
                
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === experienceYears) {
                        options[i].selected = true;
                        console.log('Selected experience years option:', options[i].value);
                        break;
                    }
                }
            }

        } catch (error) {
            console.error('Error loading profile data:', error);
            showToast('Failed to load profile data', 'error');

            // Set default profile photo
            const profilePhotoPreview = document.getElementById('profilePhotoPreview');
            const profilePhotoImg = profilePhotoPreview ? profilePhotoPreview.querySelector('img') : null;
            
            if (profilePhotoImg) {
                profilePhotoImg.src = DEFAULT_PROFILE_PHOTO;
            }
        }
    }

    // Upload profile photo to server
    function uploadProfilePhoto(file) {
        const formData = new FormData();
        formData.append('profile_photo', file);

        fetch(`${basePath}/admin-panel/apis/expert/upload-photo.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Upload response status:', response.status);
            return response.json();
        })
        .then(async (result) => {
            console.log('Upload result:', result);
            
            if (result.success) {
                showToast('Profile photo updated successfully');
                
                // Update the preview image in the profile tab
                const previewImg = document.querySelector('#content-profile img');
                if (previewImg) {
                    const photoSrc = resolveImagePath(result.photo_url);
                    console.log('Setting uploaded photo src:', photoSrc);
                    
                    try {
                        const loadedSrc = await loadImage(photoSrc);
                        previewImg.src = loadedSrc;
                    } catch (error) {
                        console.error('Failed to load uploaded photo:', error);
                        previewImg.src = DEFAULT_PROFILE_PHOTO;
                    }
                }

                // Reload profile data to ensure consistency
                loadProfileData();
            } else {
                showToast(result.message || 'Failed to upload photo', 'error');
            }
        })
        .catch(error => {
            console.error('Photo upload error:', error);
            showToast('Error uploading photo', 'error');
        });
    }

    // Profile photo preview function
    function previewProfilePhoto(event) {
        try {
            const file = event.target.files[0];
            const preview = document.getElementById('profilePhotoPreview');
            const img = preview.querySelector('img') || document.createElement('img');

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                showToast('File size exceeds 5MB limit', 'error');
                event.target.value = ''; // Clear the file input
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                showToast('Only JPG and PNG files are allowed', 'error');
                event.target.value = ''; // Clear the file input
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.add('w-full', 'h-full', 'object-cover');
                preview.innerHTML = '';
                preview.appendChild(img);

                // Upload photo to server
                uploadProfilePhoto(file);
            };
            reader.readAsDataURL(file);
        } catch (error) {
            console.error('Profile photo preview error:', error);
            showToast('Error processing photo', 'error');
        }
    }

    // Attach preview function to file input
    function attachPhotoHandler() {
        const profilePhotoInput = document.getElementById('profilePhoto');
        if (profilePhotoInput) {
            profilePhotoInput.addEventListener('change', previewProfilePhoto);
            console.log('Photo handler attached');
        } else {
            console.error('Profile photo input not found');
        }
    }

    // Call attachPhotoHandler and loadProfileData when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        attachPhotoHandler();
        loadProfileData();
    });

    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        // Prevent default form submission
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });

        // Add click event to submit button
        const submitButton = profileForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Detailed logging of form elements
                console.group('Profile Form Submission');
                console.log('Form Elements:', {
                    full_name: profileForm.querySelector('input[name="full_name"]'),
                    tagline: profileForm.querySelector('input[name="tagline"]'),
                    bio_full: profileForm.querySelector('textarea[name="bio_full"]'),
                    timezone: profileForm.querySelector('input[name="timezone"]'),
                    experience_years: profileForm.querySelector('select[name="experience_years"]'),
                    email: profileForm.querySelector('input[name="email"]'),
                    phone: profileForm.querySelector('input[name="phone"]')
                });
                
                // Prevent default form submission
                const formData = {
                    section: 'profile',
                    full_name: profileForm.querySelector('input[name="full_name"]')?.value || '',
                    tagline: profileForm.querySelector('input[name="tagline"]')?.value || '',
                    category: profileForm.querySelector('select[name="category"]')?.value || null,
                    bio_full: profileForm.querySelector('textarea[name="bio_full"]')?.value || '',
                    tags: profileForm.querySelector('input[name="tags"]')?.value || '',
                    experience_years: profileForm.querySelector('select[name="experience_years"]')?.value || null,
                    email: profileForm.querySelector('input[name="email"]')?.value || '',
                    phone: profileForm.querySelector('input[name="phone"]')?.value || ''
                };

                // Add strengths and expected_outcomes
                const strengthsTextarea = profileForm.querySelector('textarea[name="strengths"]');
                const outcomesTextarea = profileForm.querySelector('textarea[name="expected_outcomes"]');
                
                if (strengthsTextarea) {
                    const strengthsText = strengthsTextarea.value.trim();
                    if (strengthsText) {
                        // Convert line-separated text to JSON array
                        const strengthsArray = strengthsText.split('\n')
                            .map(s => s.trim())
                            .filter(s => s.length > 0)
                            .slice(0, 3); // Max 3 items
                        formData.strengths = JSON.stringify(strengthsArray);
                    } else {
                        formData.strengths = '[]';
                    }
                }
                
                if (outcomesTextarea) {
                    const outcomesText = outcomesTextarea.value.trim();
                    if (outcomesText) {
                        // Convert line-separated text to JSON array
                        const outcomesArray = outcomesText.split('\n')
                            .map(s => s.trim())
                            .filter(s => s.length > 0)
                            .slice(0, 3); // Max 3 items
                        formData.expected_outcomes = JSON.stringify(outcomesArray);
                    } else {
                        formData.expected_outcomes = '[]';
                    }
                }

                console.log('Prepared Form Data:', formData);

                try {
                    console.log('Sending request to:', `${basePath}/admin-panel/apis/expert/settings.php`);
                    const response = await fetch(`${basePath}/admin-panel/apis/expert/settings.php`, {
                        method: 'PUT',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(formData)
                    });

                    console.log('Response status:', response.status);
                    console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                    // Log raw response text before parsing
                    const responseText = await response.text();
                    console.log('Raw Response Text:', responseText);

                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON Parsing Error:', parseError);
                        throw new Error('Invalid server response: ' + responseText);
                    }

                    console.log('Parsed Response result:', result);
                    
                    if (result.success) {
                        showToast(result.message);
                        // Redirect to index page after successful submission
                        window.location.href = `${basePath}/index.php?panel=expert`;
                    } else {
                        showToast(result.message || 'Profile update failed', 'error');
                    }
                } catch (error) {
                    console.error('Profile update error:', error);
                    showToast('Failed to update profile: ' + error.message, 'error');
                } finally {
                    console.groupEnd();
                }
            });
        }
    }

    // Bank Form Submission
    const bankForm = document.getElementById('bankForm');
    if (bankForm) {
        bankForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const formData = {
                    section: 'bank',
                    account_holder_name: this.querySelector('input[name="account_holder_name"]')?.value || '',
                    bank_name: this.querySelector('input[name="bank_name"]')?.value || '',
                    branch_name: this.querySelector('input[name="branch_name"]')?.value || '',
                    account_number: this.querySelector('input[name="account_number"]')?.value || '',
                    ifsc_code: this.querySelector('input[name="ifsc_code"]')?.value || '',
                    account_type: this.querySelector('select[name="account_type"]')?.value || ''
                };

                console.log('Bank form data:', formData);

                const response = await fetch(`${basePath}/admin-panel/apis/expert/settings.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                console.log('Bank details response:', result);
                
                if (result.success) {
                    showToast('Bank details updated successfully');
                    // Redirect to index page after successful submission
                    window.location.href = `${basePath}/index.php?panel=expert`;
                } else {
                    showToast(result.message || 'Bank details update failed', 'error');
                }
            } catch (error) {
                console.error('Bank details update error:', error);
                showToast('Error updating bank details', 'error');
            }
        });
    }

    // Availability Form Submission
    const availabilityForm = document.getElementById('availabilityForm');
    if (availabilityForm) {
        availabilityForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : 'Add Time Slot';
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Optional: Add a loading spinner or text
                submitButton.innerHTML = `
                    <span class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Adding Time Slot...
                    </span>
                `;
            }

            try {
                // Check if we're in edit mode
                const timeSlotsContainer = document.getElementById('timeSlotsContainer');
                const editingSlots = timeSlotsContainer?.dataset.editingSlots;
                
                if (editingSlots) {
                    // Delete old slots first
                    const slotIds = JSON.parse(editingSlots);
                    console.log('Deleting old slots:', slotIds);
                    
                    for (const slotId of slotIds) {
                        try {
                            const deleteResponse = await fetch(`${basePath}/admin-panel/apis/expert/delete-availability.php?id=${slotId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            
                            if (!deleteResponse.ok) {
                                console.warn('Failed to delete slot:', slotId);
                            }
                        } catch (deleteError) {
                            console.error('Error deleting slot:', slotId, deleteError);
                        }
                    }
                    
                    // Clear the editing flag
                    delete timeSlotsContainer.dataset.editingSlots;
                }
                
                // Gather form data with detailed logging
                const formData = new FormData(this);
                
                console.group('Availability Form Submission');
                console.log('Form Data:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
                const response = await fetch(`${basePath}/admin-panel/apis/expert/update-availability.php`, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response Status:', response.status);
                
                const result = await response.json();
                console.log('Response Result:', result);
                
                // Restore button state
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.textContent = originalText;
                }

                if (result.success) {
                    showToast('Availability updated successfully');
                    // Redirect to index page after successful submission
                    window.location.href = `${basePath}/index.php?panel=expert`;
                    this.reset(); // Clear form
                    
                    // Optional: Reload availability data or update UI
                    try {
                        const availabilityResponse = await fetch(`${basePath}/admin-panel/apis/expert/get-availability.php`);
                        const availabilityData = await availabilityResponse.json();
                        
                        if (availabilityData.success) {
                            updateAvailabilityDisplay(availabilityData.data);
                        }
                    } catch (reloadError) {
                        console.error('Error reloading availability:', reloadError);
                    }
                } else {
                    showToast(result.message || 'Availability update failed', 'error');
                }
                
                console.groupEnd();
            } catch (error) {
                console.error('Availability update error:', error);
                
                // Restore button state
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.textContent = originalText;
                }
                
                showToast('Error updating availability', 'error');
                console.groupEnd();
            }
        });
    }

    // Function to update availability display
    function updateAvailabilityDisplay(availabilitySlots) {
        const availabilityContainer = document.querySelector('#current-availability-container');
        if (!availabilityContainer) return;

        // Clear existing slots
        availabilityContainer.innerHTML = '';

        // Days of the week
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Group slots by day
        const groupedSlots = {};
        availabilitySlots.forEach(slot => {
            if (!groupedSlots[slot.day_of_week]) {
                groupedSlots[slot.day_of_week] = [];
            }
            groupedSlots[slot.day_of_week].push(slot);
        });

        // Create display for each day
        days.forEach((day, index) => {
            const daySlots = groupedSlots[index] || [];
            
            if (daySlots.length > 0) {
                const dayElement = document.createElement('div');
                dayElement.classList.add('flex', 'items-center', 'justify-between', 'p-4', 'bg-gray-50', 'rounded-lg', 'mb-2');
                
                dayElement.innerHTML = `
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">${day}</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            ${daySlots.map(slot => `
                                <span class="px-3 py-1 bg-accent text-white rounded-full text-sm">
                                    ${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    <button data-delete-day="${day}" class="delete-availability-btn ml-4 text-red-600 hover:text-red-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                `;
                
                availabilityContainer.appendChild(dayElement);
            }
        });
    }

    // Event delegation for delete availability buttons
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-availability-btn');
        if (deleteBtn) {
            const dayName = deleteBtn.dataset.deleteDay;
            if (dayName) {
                deleteDayAvailability(dayName);
            }
        }
    });

    // Edit day availability function
    async function editDayAvailability(dayName) {
        try {
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            const dayIndex = days.indexOf(dayName);
            
            if (dayIndex === -1) {
                throw new Error('Invalid day name');
            }

            // Fetch current availability slots
            const response = await fetch(`${basePath}/admin-panel/apis/expert/get-availability.php`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to fetch availability');
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to load availability');
            }

            // Filter slots for this day
            const daySlots = result.data.filter(slot => parseInt(slot.day_of_week) === dayIndex);

            if (daySlots.length === 0) {
                showToast(`No availability slots found for ${dayName}`, 'info');
                return;
            }

            // Navigate to availability tab
            const availabilityTab = document.querySelector('button[data-tab="availability"]');
            if (availabilityTab) {
                availabilityTab.click();
                
                // Wait for tab to load
                setTimeout(() => {
                    // Set the day dropdown
                    const daySelect = document.getElementById('availabilityDayOfWeek');
                    if (daySelect) {
                        daySelect.value = dayIndex;
                        
                        // Clear existing time slot inputs
                        const timeSlotsContainer = document.getElementById('time-slots-container');
                        if (timeSlotsContainer) {
                            timeSlotsContainer.innerHTML = '';
                            
                            // Add inputs for each existing slot
                            daySlots.forEach((slot, index) => {
                                const slotDiv = document.createElement('div');
                                slotDiv.className = 'flex items-center gap-4 mb-3';
                                slotDiv.innerHTML = `
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                                        <input type="time" name="start_time[]" value="${slot.start_time.substring(0, 5)}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time *</label>
                                        <input type="time" name="end_time[]" value="${slot.end_time.substring(0, 5)}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    </div>
                                    ${index > 0 ? `
                                    <button type="button" onclick="this.parentElement.remove()" class="mt-6 text-red-500 hover:text-red-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                    ` : ''}
                                `;
                                timeSlotsContainer.appendChild(slotDiv);
                            });
                            
                            // Store slot IDs for deletion before update
                            const slotIds = daySlots.map(slot => slot.id);
                            timeSlotsContainer.dataset.editingSlots = JSON.stringify(slotIds);
                            
                            showToast(`Editing ${dayName} availability`, 'info');
                        }
                    }
                }, 300);
            }
        } catch (error) {
            console.error('Edit availability error:', error);
            showToast('Error loading availability for editing', 'error');
        }
    }

    // Delete day availability function
    async function deleteDayAvailability(dayName) {
        try {
            const result = await Swal.fire({
                title: `Delete ${dayName} Availability?`,
                text: "All availability slots for this day will be permanently deleted. This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) {
                return;
            }

            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            const dayIndex = days.indexOf(dayName);
            
            if (dayIndex === -1) {
                throw new Error('Invalid day name');
            }

            // Send delete request
            const response = await fetch(`${basePath}/admin-panel/apis/expert/delete-availability.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ day_of_week: dayIndex })
            });

            if (!response.ok) {
                throw new Error('Failed to delete availability');
            }

            const deleteResult = await response.json();
            
            if (deleteResult.success) {
                showToast(`${dayName} availability deleted successfully`, 'success');
                // Reload availability display
                loadAvailability();
            } else {
                throw new Error(deleteResult.message || 'Failed to delete availability');
            }
        } catch (error) {
            console.error('Delete availability error:', error);
            showToast('Error deleting availability', 'error');
        }
    }

    // Utility function to format time
    function formatTime(timeString) {
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(parseInt(hours), parseInt(minutes));
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }

    // Notification Preferences
    const notificationPrefsButton = document.querySelector('#content-notifications button');
    if (notificationPrefsButton) {
        notificationPrefsButton.addEventListener('click', async function() {
            try {
                const formData = new FormData();
                const checkboxes = document.querySelectorAll('#content-notifications input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    formData.append(checkbox.id, checkbox.checked ? '1' : '0');
                });

                const response = await fetch(`admin-panel/apis/expert/update-notifications.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showToast('Notification preferences updated');
                    // Redirect to index page after successful submission
                    window.location.href = `${basePath}/index.php?panel=expert`;
                } else {
                    showToast(result.message || 'Failed to update preferences', 'error');
                }
            } catch (error) {
                console.error('Notification preferences error:', error);
                showToast('Error updating notification preferences', 'error');
            }
        });
    }

    // Privacy Settings
    const privacySettingsButton = document.querySelector('#content-privacy button');
    if (privacySettingsButton) {
        privacySettingsButton.addEventListener('click', async function() {
            try {
                const formData = new FormData();
                const checkboxes = document.querySelectorAll('#content-privacy input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    formData.append(checkbox.id, checkbox.checked ? '1' : '0');
                });

                const response = await fetch(`admin-panel/apis/expert/update-privacy.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showToast('Privacy settings updated');
                    // Redirect to index page after successful submission
                    window.location.href = `${basePath}/index.php?panel=expert`;
                } else {
                    showToast(result.message || 'Failed to update privacy settings', 'error');
                }
            } catch (error) {
                console.error('Privacy settings error:', error);
                showToast('Error updating privacy settings', 'error');
            }
        });
    }

    // Security Settings - Password Change
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const formData = new FormData(this);
                const response = await fetch(`admin-panel/apis/expert/change-password.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showToast('Password updated successfully');
                    this.reset(); // Clear form
                    // Redirect to index page after successful submission
                    window.location.href = `${basePath}/index.php?panel=expert`;
                } else {
                    showToast(result.message || 'Password update failed', 'error');
                }
            } catch (error) {
                console.error('Password change error:', error);
                showToast('Error changing password', 'error');
            }
        });
    }

    // Deactivate Account
    const deactivateButton = document.querySelector('#content-privacy .bg-red-600');
    if (deactivateButton) {
        deactivateButton.addEventListener('click', async function() {
            try {
                const result = await Swal.fire({
                    title: 'Deactivate Account',
                    text: 'Are you sure you want to temporarily disable your account?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, deactivate'
                });

                if (result.isConfirmed) {
                    const response = await fetch(`admin-panel/apis/expert/deactivate-account.php`, {
                        method: 'POST'
                    });

                    const deactivationResult = await response.json();
                    if (deactivationResult.success) {
                        showToast('Account deactivated successfully');
                        window.location.href = `${basePath}/index.php?panel=expert&page=auth`;
                    } else {
                        showToast(deactivationResult.message || 'Account deactivation failed', 'error');
                    }
                }
            } catch (error) {
                console.error('Account deactivation error:', error);
                showToast('Error deactivating account', 'error');
            }
        });
    }

    // Global error handling
    window.addEventListener('error', function(event) {
        console.error('Unhandled error:', event.error);
        console.error('Error message:', event.message);
        console.error('Error filename:', event.filename);
        console.error('Error line:', event.lineno);
        // Temporarily show detailed error for debugging
        showToast(event.message || 'An unexpected error occurred', 'error');
    });
})();
