console.log('=== JS DEBUG START ===');
console.log('BASE_PATH from window:', window.BASE_PATH);
console.log('BASE_URL from window:', window.BASE_URL);

// Intercept all fetch calls to log them
const originalFetch = window.fetch;
window.fetch = function(...args) {
    console.log('FETCH CALL:', {
        url: args[0],
        options: args[1],
        timestamp: new Date().toISOString()
    });
    
    return originalFetch.apply(this, args)
        .then(response => {
            console.log('FETCH RESPONSE:', {
                url: args[0],
                status: response.status,
                statusText: response.statusText,
                headers: {
                    'content-type': response.headers.get('content-type')
                }
            });
            return response;
        })
        .catch(error => {
            console.error('FETCH ERROR:', {
                url: args[0],
                error: error.message,
                stack: error.stack
            });
            throw error;
        });
};

// Log expert ID retrieval
const expertId = new URLSearchParams(window.location.search).get('expert_id');
console.log('Expert ID extracted:', expertId);

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
        
        // Load expert programs
        loadExpertPrograms(expertId);
        
        // Load expert webinars
        loadExpertWebinars(expertId);
        
        // Setup message buttons
        setupMessageButtons(expertId);
    } catch (error) {
        console.error('Error loading expert profile:', error);
        Swal.fire({
            icon: 'error',
            title: 'Loading Failed',
            text: 'Failed to load expert profile. Please try again.',
            confirmButtonColor: '#3B82F6'
        }).then(() => {
            //window.location.href = BASE_PATH + '/index.php?panel=learner&page=browse-experts';
        });
    }
});

async function loadExpertPrograms(expertId) {
    const BASE_PATH = window.BASE_PATH || '';
    const container = document.getElementById('programs-container');
    
    try {
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/learner/get-expert-programs.php?expert_id=${expertId}`);
        const data = await response.json();
        
        if (!data.success || !data.programs || data.programs.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No programs available yet</p>
                    <p class="text-gray-400 text-sm mt-2">This expert hasn't created any programs yet. Book a session instead!</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = data.programs.map(program => `
            <div class="flex-shrink-0 w-80 md:w-96 snap-center bg-[#131b2e] rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group border border-gray-800 hover:border-[#00D4AA] transform hover:scale-105">
                <div class="bg-[#131b2e] p-1">
                    <div class="bg-[#131b2e] p-6 rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center gap-2 bg-[#0e1322] text-[#00D4AA] border border-[#00D4AA]/30 px-3 py-1 rounded-full text-xs font-bold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                </svg>
                                Program
                            </span>
                            <span class="text-gray-400 font-bold text-sm">${program.duration_weeks || 0} weeks</span>
                        </div>
                        <h3 class="text-xl font-black text-white mb-3 group-hover:text-[#00D4AA] transition-all">${escapeHtml(program.title)}</h3>
                        <p class="text-gray-400 mb-4 line-clamp-3 leading-relaxed">${escapeHtml(program.description || '')}</p>
                        ${program.goal_outcome ? `<div class="bg-[#0e1322] border border-gray-800 p-3 rounded-lg mb-4">
                            <p class="text-sm text-gray-300 font-medium"><span class="font-bold text-[#00D4AA]">Goal:</span> ${escapeHtml(program.goal_outcome)}</p>
                        </div>` : ''}
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-800">
                            ${program.price_inr ? `<div>
                                <p class="text-sm text-gray-400 font-medium">Program Price</p>
                                <p class="text-2xl font-black text-white">₹${Number(program.price_inr).toLocaleString()}</p>
                            </div>` : ''}
                            <div class="${program.price_inr ? 'text-right' : ''}">
                                <p class="text-sm text-gray-400 font-medium">Duration</p>
                                <p class="text-xl font-bold text-[#00D4AA]">${program.duration_weeks || 0} weeks</p>
                            </div>
                        </div>
                        <a href="${BASE_PATH}/index.php?panel=learner&page=program-details&id=${program.id}" 
                           class="block w-full bg-[#00D4AA] text-[#080B10] text-center px-6 py-4 rounded-xl hover:bg-[#00bda0] transition-all font-black text-base shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                            View Program Details →
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading programs:', error);
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">Failed to load programs</p>
            </div>
        `;
    }
}

function setupMessageButtons(expertId) {
    const BASE_PATH = window.BASE_PATH || '';
    const heroMessageBtn = document.getElementById('hero-message-btn');
    const sidebarMessageBtn = document.getElementById('sidebar-message-btn');
    const finalCtaMessageBtn = document.getElementById('final-cta-message');
    
    const messageHandler = () => {
        window.location.href = `${BASE_PATH}/index.php?panel=learner&page=messages&expert_id=${expertId}`;
    };
    
    if (heroMessageBtn) {
        heroMessageBtn.addEventListener('click', messageHandler);
    }
    if (sidebarMessageBtn) {
        sidebarMessageBtn.addEventListener('click', messageHandler);
    }
    if (finalCtaMessageBtn) {
        finalCtaMessageBtn.addEventListener('click', messageHandler);
    }
}

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

async function loadExpertWebinars(expertId) {
    console.log('=== LOADING WEBINARS ===');
    console.log('Expert ID:', expertId);
    
    const BASE_PATH = window.BASE_PATH || '';
    const sidebar = document.getElementById('webinars-sidebar');
    const listContainer = document.getElementById('webinars-list');
    const countBadge = document.getElementById('webinars-count-badge');
    
    console.log('Sidebar element:', sidebar);
    console.log('List container:', listContainer);
    
    try {
        const apiUrl = `${BASE_PATH}/admin-panel/apis/expert/webinar-details.php?expert_id=${expertId}&all=true`;
        console.log('Fetching webinars from:', apiUrl);
        
        const response = await fetch(apiUrl);
        console.log('Webinars API response status:', response.status);
        
        const data = await response.json();
        console.log('Webinars API data:', data);
        
        if (!data.success || !data.webinars || data.webinars.length === 0) {
            sidebar.style.display = 'none';
            return;
        }
        
        // Filter upcoming webinars only
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const upcomingWebinars = data.webinars.filter(w => {
            const webinarDate = new Date(w.webinar_date);
            return w.status === 'upcoming' && w.is_active && webinarDate >= today;
        });
        
        if (upcomingWebinars.length === 0) {
            sidebar.style.display = 'none';
            return;
        }
        
        sidebar.style.display = 'block';
        countBadge.textContent = upcomingWebinars.length;
        
        listContainer.innerHTML = upcomingWebinars.map(webinar => {
            const date = new Date(webinar.webinar_date);
            const time = webinar.webinar_time ? webinar.webinar_time.substring(0, 5) : '';
            const priceDisplay = webinar.price_inr > 0 
                ? `₹${Number(webinar.price_inr).toLocaleString('en-IN')}` 
                : '<span class="text-green-600">FREE</span>';
            
            return `
                <div class="border border-gray-800 rounded-lg p-4 hover:shadow-lg hover:border-gray-700 transition-all bg-[#0e1322] cursor-pointer group"
                     onclick="window.location.href='${BASE_PATH}/index.php?panel=learner&page=webinar-details&id=${webinar.id}'">
                    
                    <!-- Live Badge -->
                    <div class="mb-2">
                        <span class="inline-block px-2 py-1 bg-[#131b2e] border border-[#00D4AA]/30 text-[#00D4AA] text-xs font-bold rounded uppercase">
                            🎥 Live
                        </span>
                    </div>
                    
                    <!-- Title -->
                    <h4 class="font-bold text-white mb-2 group-hover:text-[#00D4AA] transition line-clamp-2">
                        ${webinar.title}
                    </h4>
                    
                    <!-- Date & Time -->
                    <div class="space-y-1 mb-3 text-xs text-gray-400">
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>${time} · ${webinar.duration_hours}h</span>
                        </div>
                    </div>
                    
                    <!-- Price & View Button -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-800">
                        <div class="text-lg font-bold text-[#00D4AA]">
                            ${priceDisplay}
                        </div>
                        <span class="text-[#00D4AA] font-semibold text-xs group-hover:underline">
                            Details →
                        </span>
                    </div>
                </div>
            `;
        }).join('');
        
    } catch (error) {
        console.error('Error loading webinars:', error);
        sidebar.style.display = 'none';
    }
}

function renderExpertProfile(expert) {
    document.getElementById('expert-name').textContent = expert.name || 'Expert';
    document.getElementById('expert-title').textContent = expert.professional_title || 'Expert';
    document.getElementById('expert-location').textContent = expert.location || 'Location not specified';
    
    const years = Number(expert.experience_years) || 0;
    const sessions = Number(expert.total_sessions) || 0;
    const overallScore = Math.round(Number(expert.overall_score) || 0);
    
    // Populate Trust Score stat card
    const trustScoreStat = document.getElementById('expert-trust-score-stat');
    if (trustScoreStat) trustScoreStat.textContent = `${overallScore}%`;

    const taglineElement = document.getElementById('expert-tagline');
    if (taglineElement) {
        const title = expert.professional_title || 'Expert';
        let tagline = `${title} with ${years} years of experience`;
        if (sessions > 0) {
            tagline += `, having successfully mentored ${sessions}+ learners`;
        }
        tagline += '. Verified Trust Tier: ' + (expert.trust_tier || 'C');
        taglineElement.textContent = tagline;
    }
    
    const skills = Array.isArray(expert.skills) ? expert.skills : (typeof expert.skills === 'string' ? expert.skills.split(',').map(s => s.trim()) : []);
    const skillsContainer = document.getElementById('expert-skills');
    skillsContainer.innerHTML = skills.length > 0 
        ? skills.map(skill => `<span class="inline-flex items-center gap-1 px-4 py-2 bg-[#0e1322] text-[#00D4AA] text-sm font-semibold rounded-full ring-1 ring-gray-800 hover:ring-gray-700 transition">
            <svg class="w-3 h-3 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            ${escapeHtml(skill)}
        </span>`).join('')
        : '<span class="text-gray-500 text-sm">No skills listed</span>';
    
    document.getElementById('expert-hourly-rate').textContent = `₹${Number(expert.hourly_rate) || 0}`;
    
    const bioElement = document.getElementById('expert-bio');
    bioElement.textContent = expert.bio || 'No bio available.';
    
    document.getElementById('expert-total-sessions').textContent = sessions;
    const totalSessionsStat = document.getElementById('expert-total-sessions-stat');
    if (totalSessionsStat) totalSessionsStat.textContent = sessions;
    
    document.getElementById('expert-experience-years').textContent = years;
    const expHeaderEl = document.getElementById('expert-experience-header');
    if (expHeaderEl) expHeaderEl.textContent = `${years} years`;
    
    // Stability Score Stat
    const stabilityScore = Math.round(Number(expert.stability_score) || 0);
    const stabilityStat = document.getElementById('expert-stability-stat');
    if (stabilityStat) stabilityStat.textContent = `${stabilityScore}%`;

    // Verified Badge logic
    const verifiedBadge = document.getElementById('verified-badge-container');
    if (verifiedBadge) {
        if (expert.verification_status === 'approved') {
            verifiedBadge.classList.remove('hidden');
        } else {
            verifiedBadge.classList.add('hidden');
        }
    }

    const reviewCount = Number(expert.review_count) || 0;
    const reviewEl = document.getElementById('expert-total-reviews');
    if (reviewEl) reviewEl.textContent = reviewCount;
    
    const photoContainer = document.getElementById('expert-photo');
    const img = document.createElement('img');
    img.src = resolveImagePath(expert.profile_photo);
    img.alt = expert.name || 'Expert';
    img.className = 'w-full h-full object-cover';
    photoContainer.innerHTML = '';
    photoContainer.appendChild(img);
    
    const BASE_PATH = window.BASE_PATH || '';
    if (expert.id) {
        const bookBtns = ['book-session-btn', 'sidebar-book-btn', 'final-cta-book'];
        bookBtns.forEach(btnId => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.href = `${BASE_PATH}/index.php?panel=learner&page=booking&expert_id=${expert.id}`;
            }
        });
    }

    // Render Trust Tier Badge
    if (expert.trust_tier) {
        const trustBadge = document.getElementById('trust-tier-badge');
        const trustLabel = document.getElementById('trust-tier-label');
        if (trustBadge && trustLabel) {
            const tier = expert.trust_tier;
            const score = Math.round(expert.overall_score || 0);
            
            // Set dynamic style based on tier
            let bgColor = 'bg-slate-100';
            let textColor = 'text-slate-600';
            let dotColor = 'bg-slate-400';
            
            if (tier === 'A') {
                bgColor = 'bg-emerald-50';
                textColor = 'text-emerald-700';
                dotColor = 'bg-emerald-500 animate-pulse';
            } else if (tier === 'B') {
                bgColor = 'bg-blue-50';
                textColor = 'text-blue-700';
                dotColor = 'bg-blue-500';
            }
            
            trustBadge.innerHTML = `
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full ${bgColor} border border-white/50 shadow-lg ring-2 ring-white">
                    <span class="flex h-2 w-2 rounded-full ${dotColor}"></span>
                    <span class="text-[11px] font-black tracking-wider ${textColor} uppercase">Tier ${tier}</span>
                    <span class="text-[10px] font-bold text-gray-400 opacity-60">|</span>
                    <span class="text-[10px] font-bold ${textColor}">${score}% Trust</span>
                </div>
            `;
            trustBadge.classList.remove('hidden');
        }
    }
}
