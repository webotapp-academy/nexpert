// Save current URL before redirecting to login
(function() {
    'use strict';

    // Function to save current URL to redirect back after login
    function saveRedirectUrl() {
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname + window.location.search;
        
        // Save to sessionStorage (accessible across tabs)
        sessionStorage.setItem('redirect_after_login', currentPath);
        
        console.log('Saved redirect URL:', currentPath);
    }

    // Add event listeners to all login links/buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Find all learner and expert login links
        const loginLinks = document.querySelectorAll('a[href*="panel=learner&page=auth"], a[href*="panel=expert&page=auth"]');
        
        loginLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Only save redirect if we're not already on the auth page
                const currentPage = new URLSearchParams(window.location.search).get('page');
                if (currentPage !== 'auth') {
                    saveRedirectUrl();
                }
            });
        });

        console.log('Redirect URL saver initialized. Found', loginLinks.length, 'login links');
    });
})();
