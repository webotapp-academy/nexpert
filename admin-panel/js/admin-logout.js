// Admin logout handler - used across all admin pages
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('adminLogout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('/admin-panel/apis/admin/auth.php', {
                    method: 'DELETE'
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = '?panel=admin&page=auth';
                }
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = '?panel=admin&page=auth';
            }
        });
    }
});
