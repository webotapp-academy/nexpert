// Load programs and stats on page load
document.addEventListener('DOMContentLoaded', async function() {
    await loadProgramsAndStats();
});

// Load programs and stats
async function loadProgramsAndStats() {
    try {
        const response = await fetch('/admin-panel/apis/expert/programs.php');
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
                    <button onclick="deleteProgram(${program.id})" class="text-red-500 hover:text-red-700 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
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
                    <a href="?panel=expert&page=program-details&id=${program.id}" class="text-accent hover:text-yellow-600 font-medium">View Details →</a>
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
        const response = await fetch('/admin-panel/apis/expert/programs.php', {
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
            const response = await fetch(`/admin-panel/apis/expert/programs.php?id=${programId}`, {
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
