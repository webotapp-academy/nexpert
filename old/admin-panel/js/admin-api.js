// Admin API utility with CSRF token handling
window.AdminAPI = {
    csrfToken: null,
    
    // Initialize CSRF token from meta tag
    init: function() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            this.csrfToken = metaTag.content;
        }
    },
    
    // Make API request with CSRF token
    fetch: async function(url, options = {}) {
        if (!this.csrfToken) {
            this.init();
        }
        
        // Add CSRF token to headers for state-changing requests
        const method = (options.method || 'GET').toUpperCase();
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            options.headers = options.headers || {};
            options.headers['X-CSRF-Token'] = this.csrfToken;
        }
        
        // Ensure credentials (cookies) are included
        options.credentials = 'same-origin';
        
        return fetch(url, options);
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.AdminAPI.init();
});
