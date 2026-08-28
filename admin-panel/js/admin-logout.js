// Admin logout handler - used across all admin pages
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('adminLogout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const basePath = typeof BASE_PATH !== 'undefined' ? BASE_PATH : '';
            
            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    title: 'Admin Sign Out',
                    text: 'Are you sure you want to exit the admin console?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Sign Out',
                    cancelButtonText: 'Stay in Console',
                    background: '#0D131F',
                    color: '#FFFFFF',
                    iconColor: '#F59E0B',
                    customClass: {
                        popup: 'border border-white/10 rounded-2xl shadow-2xl backdrop-blur-xl',
                        title: 'text-white font-extrabold text-lg',
                        htmlContainer: 'text-gray-400 text-xs',
                        confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-bold px-5 py-2.5 rounded-xl transition text-xs mr-3 cursor-pointer',
                        cancelButton: 'bg-white/[0.05] hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 font-bold px-5 py-2.5 rounded-xl transition text-xs cursor-pointer'
                    },
                    buttonsStyling: false
                });
                if (!res.isConfirmed) return;
            } else {
                if (!confirm('Are you sure you want to exit the admin console?')) return;
            }
            
            try {
                await fetch(basePath + '/admin-panel/apis/admin/auth.php', { method: 'DELETE' });
                window.location.href = basePath + '/index.php?panel=admin&page=auth';
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = basePath + '/index.php?panel=admin&page=auth';
            }
        });
    }
});
