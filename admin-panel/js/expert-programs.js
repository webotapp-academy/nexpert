// Load programs and stats on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadProgramsAndStats();
});

// Load programs and stats
async function loadProgramsAndStats() {
    try {
        const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
        const response = await fetch(basePath + '/admin-panel/apis/expert/programs.php');
        const result = await response.json();
        
        if (result.success) {
            updateStats(result.stats);
            displayPrograms(result.programs);
        }
    } catch (error) {
        console.error('Error loading programs:', error);
    }
}

// Update stats cards
function updateStats(stats) {
    document.querySelector('#stats-total-programs').textContent = stats.total_programs || 0;
    document.querySelector('#stats-active-learners').textContent = stats.active_learners || 0;
    document.querySelector('#stats-total-assignments').textContent = stats.total_assignments || 0;
    document.querySelector('#stats-completion-rate').textContent = Math.round(stats.avg_completion || 0) + '%';
}

// Display programs
function displayPrograms(programs) {
    const emptyState = document.getElementById('empty-state');
    const programsGrid = document.getElementById('programs-grid');
    
    if (programs.length === 0) {
        emptyState.classList.remove('hidden');
        programsGrid.classList.add('hidden');
    } else {
        emptyState.classList.add('hidden');
        programsGrid.classList.remove('hidden');
        
        programsGrid.innerHTML = programs.map(program => `
            <div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">${escapeHtml(program.title)}</h3>
                        <p class="text-sm text-gray-600 line-clamp-2">${escapeHtml(program.description || '')}</p>
                    </div>
                    <div class="flex gap-2">
                        <div class="relative share-menu-container">
                            <button onclick="toggleShareMenu(event, ${program.id})" class="text-gray-600 hover:text-gray-800 p-2" title="Share">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                            </button>
                            <div id="share-menu-${program.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button onclick="copyProgramUrl('${escapeHtml(program.title)}', ${program.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Copy URL
                                    </button>
                                    <button onclick="shareToLinkedIn('${escapeHtml(program.title)}', '${escapeHtml(program.description || '')}', ${program.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                        </svg>
                                        Share on LinkedIn
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button data-delete-program="${program.id}" class="delete-program-btn text-red-500 hover:text-red-700 p-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="text-center p-3 bg-amber-50 rounded-lg">
                        <p class="text-2xl font-bold text-accent">${program.milestone_count}</p>
                        <p class="text-xs text-gray-600">Milestones</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-600">${program.assignment_count}</p>
                        <p class="text-xs text-gray-600">Assignments</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600">${program.learner_count}</p>
                        <p class="text-xs text-gray-600">Learners</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">${program.duration_weeks} weeks</span>
                    <a href="?panel=expert&page=program-details&id=${program.id}" class="view-program-link text-accent hover:text-yellow-600 font-medium">View Details →</a>
                </div>
            </div>
        `).join('');
    }
}

// Save program
async function saveProgram() {
    const title = document.getElementById('program-title').value.trim();
    const description = document.getElementById('program-description').value.trim();
    const duration = parseInt(document.getElementById('program-duration').value) || 0;
    const price = parseInt(document.getElementById('program-price').value) || 0;
    
    if (!title) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please enter a program title',
            confirmButtonColor: '#F59E0B'
        });
        return;
    }
    
    // Collect milestones
    const milestones = [];
    document.querySelectorAll('#milestones-container > div').forEach(div => {
        if (div.classList.contains('flex')) {
            const inputs = div.querySelectorAll('input');
            if (inputs.length >= 3) {
                milestones.push({
                    title: inputs[0].value,
                    week: parseInt(inputs[1].value) || 1,
                    deliverable: inputs[2].value
                });
            }
        }
    });
    
    // Collect assignments
    const assignments = [];
    document.querySelectorAll('#assignments-container > div').forEach(div => {
        if (div.querySelector('input')) {
            const titleInput = div.querySelector('input[placeholder="Assignment title"]');
            const typeSelect = div.querySelector('select');
            const descTextarea = div.querySelector('textarea');
            
            assignments.push({
                title: titleInput?.value || '',
                type: typeSelect?.value || 'project',
                description: descTextarea?.value || ''
            });
        }
    });
    
    // Collect resources
    const resources = [];
    document.querySelectorAll('#resources-container > div').forEach(div => {
        if (div.classList.contains('flex')) {
            const inputs = div.querySelectorAll('input');
            const select = div.querySelector('select');
            
            if (inputs.length >= 2) {
                resources.push({
                    title: inputs[0].value,
                    type: select?.value || 'document',
                    url: inputs[1].value
                });
            }
        }
    });
    
    const programData = {
        title,
        description,
        duration_weeks: duration,
        price,
        milestones,
        assignments,
        resources
    };
    
    try {
        const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
        const response = await fetch(basePath + '/admin-panel/apis/expert/programs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(programData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Program Created!',
                text: 'Your program has been created successfully.',
                confirmButtonColor: '#F59E0B'
            }).then(() => {
                closeModal();
                loadProgramsAndStats();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to create program',
                confirmButtonColor: '#F59E0B'
            });
        }
    } catch (error) {
        console.error('Save error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving the program',
            confirmButtonColor: '#F59E0B'
        });
    }
}

// Delete program
async function deleteProgram(programId) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Delete Program?',
        text: 'Are you sure you want to delete this program? This action cannot be undone.',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it'
    });
    
    if (result.isConfirmed) {
        try {
            const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
            const response = await fetch(`${basePath}/admin-panel/apis/expert/programs.php?id=${programId}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Program has been deleted.',
                    confirmButtonColor: '#F59E0B'
                });
                loadProgramsAndStats();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to delete program',
                    confirmButtonColor: '#F59E0B'
                });
            }
        } catch (error) {
            console.error('Delete error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the program',
                confirmButtonColor: '#F59E0B'
            });
        }
    }
}

// Escape HTML helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Update the save button click handler
document.getElementById('save-program-btn')?.addEventListener('click', saveProgram);

// ========== AI PROGRAM CREATION FUNCTIONALITY ==========

// Open AI modal
document.getElementById('create-program-ai-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.remove('hidden');
    document.getElementById('ai-program-idea').value = '';
});

// Close AI modal
document.getElementById('close-ai-modal-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.add('hidden');
});

document.getElementById('cancel-ai-modal-btn')?.addEventListener('click', function() {
    document.getElementById('ai-idea-modal').classList.add('hidden');
});

// Generate program with AI
document.getElementById('generate-ai-program-btn')?.addEventListener('click', async function() {
    const ideaTextarea = document.getElementById('ai-program-idea');
    const idea = ideaTextarea.value.trim();
    
    if (!idea) {
        Swal.fire({
            icon: 'warning',
            title: 'Idea Required',
            text: 'Please describe your program idea',
            confirmButtonColor: '#F59E0B'
        });
        return;
    }
    
    const button = this;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generating with AI...
    `;
    
    try {
        const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
        const response = await fetch(basePath + '/admin-panel/apis/expert/generate-program-ai.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idea })
        });
        
        const result = await response.json();
        
        if (result.success && result.data) {
            // Close AI modal
            document.getElementById('ai-idea-modal').classList.add('hidden');
            
            // Open create program modal
            document.getElementById('create-program-modal').classList.remove('hidden');
            
            // Fill in basic program details
            document.getElementById('program-title').value = result.data.title || '';
            document.getElementById('program-description').value = result.data.description || '';
            document.getElementById('program-duration').value = result.data.duration_weeks || '';
            document.getElementById('program-price').value = result.data.price_inr || '';
            
            // Fill in milestones
            if (result.data.milestones && Array.isArray(result.data.milestones)) {
                const milestonesContainer = document.getElementById('milestones-container');
                milestonesContainer.innerHTML = ''; // Clear existing
                
                result.data.milestones.forEach((milestone, index) => {
                    const milestoneHtml = `
                        <div class="flex items-center gap-4 mb-3">
                            <div class="flex-1">
                                <input type="text" value="${escapeHtml(milestone.title)}" placeholder="Milestone title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="w-24">
                                <input type="number" value="${milestone.week || index + 1}" min="1" placeholder="Week" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="flex-1">
                                <input type="text" value="${escapeHtml(milestone.deliverable || '')}" placeholder="Deliverable" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            ${index > 0 ? `
                            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            ` : ''}
                        </div>
                    `;
                    milestonesContainer.insertAdjacentHTML('beforeend', milestoneHtml);
                });
            }
            
            // Fill in assignments
            if (result.data.assignments && Array.isArray(result.data.assignments)) {
                const assignmentsContainer = document.getElementById('assignments-container');
                assignmentsContainer.innerHTML = ''; // Clear existing
                
                result.data.assignments.forEach((assignment, index) => {
                    const assignmentHtml = `
                        <div class="border border-gray-200 rounded-lg p-4 mb-3">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex-1">
                                    <input type="text" value="${escapeHtml(assignment.title)}" placeholder="Assignment title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div class="w-32">
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="project" ${assignment.type === 'project' ? 'selected' : ''}>Project</option>
                                        <option value="quiz" ${assignment.type === 'quiz' ? 'selected' : ''}>Quiz</option>
                                        <option value="reading" ${assignment.type === 'reading' ? 'selected' : ''}>Reading</option>
                                        <option value="practical" ${assignment.type === 'practical' ? 'selected' : ''}>Practical</option>
                                    </select>
                                </div>
                                ${index > 0 ? `
                                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                ` : ''}
                            </div>
                            <textarea rows="2" placeholder="Assignment description" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">${escapeHtml(assignment.description || '')}</textarea>
                        </div>
                    `;
                    assignmentsContainer.insertAdjacentHTML('beforeend', assignmentHtml);
                });
            }
            
            Swal.fire({
                icon: 'success',
                title: 'AI Program Generated!',
                text: 'Your program has been created with AI. Review and customize it before saving.',
                confirmButtonColor: '#F59E0B'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                text: result.message || 'Failed to generate program with AI',
                confirmButtonColor: '#F59E0B'
            });
        }
    } catch (error) {
        console.error('AI generation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while generating the program',
            confirmButtonColor: '#F59E0B'
        });
    } finally {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    }
});

// Event delegation for delete program buttons
document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.delete-program-btn');
    if (deleteBtn) {
        const programId = deleteBtn.dataset.deleteProgram;
        if (programId) {
            deleteProgram(programId);
        }
    }
});

// Function to share program on LinkedIn
function shareToLinkedIn(title, description, programId) {
    // Get the current page URL
    const currentUrl = window.location.origin + window.location.pathname;
    
    // Create the program URL for learners to view
    const programUrl = `${currentUrl}?panel=learner&page=program-details&id=${programId}`;
    
    // Prepare the share text
    const shareText = `🎓 ${title}\n\n${description}`;
    
    // Detect if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if (isMobile) {
        // For mobile: Try to open LinkedIn app first, fallback to browser
        const linkedInAppUrl = `linkedin://shareArticle?url=${encodeURIComponent(programUrl)}&title=${encodeURIComponent(title)}&summary=${encodeURIComponent(description)}`;
        const linkedInWebUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(programUrl)}`;
        
        // Try to open the app
        window.location.href = linkedInAppUrl;
        
        // Fallback to browser after a short delay if app doesn't open
        setTimeout(() => {
            window.open(linkedInWebUrl, '_blank');
        }, 500);
    } else {
        // For desktop: Open in new window/tab
        const linkedInUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(programUrl)}`;
        
        const width = 600;
        const height = 600;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        
        window.open(
            linkedInUrl,
            'LinkedIn Share',
            `width=${width},height=${height},left=${left},top=${top},toolbar=0,status=0,resizable=1`
        );
    }
    
    // Close the share menu
    closeAllShareMenus();
}

// Function to toggle share menu
function toggleShareMenu(event, programId) {
    event.stopPropagation();
    const menu = document.getElementById(`share-menu-${programId}`);
    const allMenus = document.querySelectorAll('[id^="share-menu-"]');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `share-menu-${programId}`) {
            m.classList.add('hidden');
        }
    });
    
    // Toggle current menu
    menu.classList.toggle('hidden');
}

// Function to close all share menus
function closeAllShareMenus() {
    const allMenus = document.querySelectorAll('[id^="share-menu-"]');
    allMenus.forEach(m => m.classList.add('hidden'));
}

// Function to copy program URL
function copyProgramUrl(title, programId) {
    const currentUrl = window.location.origin + window.location.pathname;
    // Create learner-facing URL
    const programUrl = `${currentUrl}?panel=learner&page=program-details&id=${programId}`;
    
    // Copy to clipboard
    navigator.clipboard.writeText(programUrl).then(() => {
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'URL Copied!',
            text: 'Program URL has been copied to clipboard',
            timer: 2000,
            showConfirmButton: false
        });
    }).catch(err => {
        console.error('Failed to copy URL:', err);
        Swal.fire({
            icon: 'error',
            title: 'Copy Failed',
            text: 'Failed to copy URL to clipboard',
            confirmButtonColor: '#F59E0B'
        });
    });
    
    // Close the share menu
    closeAllShareMenus();
}

// Close share menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.share-menu-container')) {
        closeAllShareMenus();
    }
});
