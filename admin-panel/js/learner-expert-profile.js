document.addEventListener('DOMContentLoaded', async function() {
    const urlParams = new URLSearchParams(window.location.search);
    const expertId = urlParams.get('expert_id');

    // Use window.BASE_PATH instead of BASE_PATH
    const BASE_PATH = window.BASE_PATH || '';

    if (!expertId) {
        window.location.href = BASE_PATH + '/index.php?panel=learner&page=browse-experts';
        return;
    }

    try {
        console.log('Loading expert profile from:', BASE_PATH + `/admin-panel/apis/learner/expert-profile.php?expert_id=${expertId}`);
        const response = await fetch(BASE_PATH + `/admin-panel/apis/learner/expert-profile.php?expert_id=${expertId}`);
        
        console.log('Expert Profile response status:', response.status);
        console.log('Expert Profile response ok:', response.ok);
        
        if (!response.ok) {
            throw new Error('Failed to fetch expert profile');
        }
        
        const data = await response.json();
        console.log('API Response:', data);

        if (!data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Expert Not Found',
                text: data.message || 'The expert profile could not be found.',
                confirmButtonColor: '#3B82F6'
            }).then(() => {
                window.location.href = BASE_PATH + '/index.php?panel=learner&page=browse-experts';
            });
            return;
        }

        const expert = data.data;
        renderExpertProfile(expert);
    } catch (error) {
        console.error('Error loading expert profile:', error);
        Swal.fire({
            icon: 'error',
            title: 'Loading Failed',
            text: 'Failed to load expert profile. Please try again.',
            confirmButtonColor: '#3B82F6'
        }).then(() => {
            window.location.href = BASE_PATH + '/index.php?panel=learner&page=browse-experts';
        });
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function resolveImagePath(imagePath) {
    // If it's a full URL or a data URI, return as-is
    if (/^(https?:\/\/|data:)/.test(imagePath)) {
        return imagePath;
    }
    
    // If no image path, use a default
    if (!imagePath) {
        return `${window.BASE_PATH || ''}/attached_assets/stock_images/diverse_professional_1d96e39f.jpg`;
    }
    
    // Remove leading slashes
    const normalizedPath = imagePath.replace(/^\/+/, '');
    
    // Construct full path
    return `${window.BASE_PATH || ''}/${normalizedPath}`;
}

function renderExpertProfile(expert) {
    document.getElementById('expert-name').textContent = expert.name || 'Expert';
    document.getElementById('expert-title').textContent = expert.professional_title || 'Expert';
    document.getElementById('expert-location').textContent = expert.location || 'Chicago, IL';
    
    const rating = Math.max(0, Math.min(5, Math.floor(Number(expert.avg_rating) || 0)));
    const ratingStars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    document.getElementById('expert-rating-stars').textContent = ratingStars;
    document.getElementById('expert-rating-value').textContent = `(${(Number(expert.avg_rating) || 0).toFixed(1)})`;
    document.getElementById('expert-review-count').textContent = `${Number(expert.review_count) || 0} reviews`;
    
    const skills = Array.isArray(expert.skills) ? expert.skills : (typeof expert.skills === 'string' ? expert.skills.split(',').map(s => s.trim()) : []);
    const skillsContainer = document.getElementById('expert-skills');
    // Light theme soft pills
    skillsContainer.innerHTML = skills.length > 0 
        ? skills.map(skill => `<span class="px-4 py-2 bg-gray-100 text-gray-800 text-sm rounded-full ring-1 ring-gray-200">${escapeHtml(skill)}</span>`).join('')
        : '<span class="text-gray-500 text-sm">No skills listed</span>';
    
    document.getElementById('expert-hourly-rate').textContent = `₹${Number(expert.hourly_rate) || 0}`;
    
    const bioElement = document.getElementById('expert-bio');
    bioElement.textContent = expert.bio || 'No bio available.';
    
    document.getElementById('expert-total-sessions').textContent = Number(expert.total_sessions) || 0;
    const years = Number(expert.experience_years) || 0;
    document.getElementById('expert-experience-years').textContent = years;
    const expHeaderEl = document.getElementById('expert-experience-header');
    if (expHeaderEl) expHeaderEl.textContent = `${years} years`;
    document.getElementById('expert-total-reviews').textContent = Number(expert.review_count) || 0;
    
    const photoContainer = document.getElementById('expert-photo');
    const img = document.createElement('img');
    img.src = resolveImagePath(expert.profile_photo);
    img.alt = expert.name || 'Expert';
    img.className = 'w-full h-full object-cover';
    photoContainer.innerHTML = '';
    photoContainer.appendChild(img);
    
    const BASE_PATH = window.BASE_PATH || '';
    if (expert.id) {
        document.getElementById('book-session-btn').href = `${BASE_PATH}/index.php?panel=learner&page=booking&expert_id=${expert.id}`;
        const sidebarBookBtn = document.getElementById('sidebar-book-btn');
        if (sidebarBookBtn) {
            sidebarBookBtn.href = `${BASE_PATH}/index.php?panel=learner&page=booking&expert_id=${expert.id}`;
        }
    }
}
