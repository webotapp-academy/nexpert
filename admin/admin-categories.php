<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-auth-check.php';

$page_title = "Category Management - Admin Console - Nexpert.ai";
$panel_type = "admin";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8 space-y-6">
    
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-[#0D131F] border border-gray-800 rounded-2xl p-6 shadow-xl">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-[#00D4AA] bg-[#00D4AA]/10 border border-[#00D4AA]/25 px-2.5 py-0.5 rounded-full">
                    Platform Taxonomy
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Category Management</h1>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Manage expert domain categories displayed on the homepage, browse screen, and expert registration.</p>
        </div>
        <button onclick="openCategoryModal()" class="bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] px-5 py-2.5 rounded-xl text-xs font-extrabold transition flex items-center gap-2 shadow-lg shadow-[#00D4AA]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Add New Category
        </button>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-5 shadow-lg">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Categories</p>
            <h3 class="text-2xl font-black text-white mt-1" id="stat-total">0</h3>
        </div>
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-5 shadow-lg">
            <p class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Active Categories</p>
            <h3 class="text-2xl font-black text-emerald-400 mt-1" id="stat-active">0</h3>
        </div>
        <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-5 shadow-lg">
            <p class="text-xs font-bold text-cyan-400 uppercase tracking-wider">Associated Experts</p>
            <h3 class="text-2xl font-black text-cyan-400 mt-1" id="stat-experts">0</h3>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-[#0D131F] border border-gray-800 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <input type="text" id="search-input" onkeyup="filterCategories()" placeholder="Search categories..." class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl pl-10 pr-4 py-2 text-xs focus:outline-none focus:border-[#00D4AA]">
            <svg class="w-4 h-4 text-gray-500 absolute left-3.5 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select id="status-filter" onchange="filterCategories()" class="bg-[#080B10] border border-gray-800 text-gray-300 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#00D4AA] w-full sm:w-auto">
                <option value="all">All Status</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>
    </div>

    <!-- Categories Table Card -->
    <div class="bg-[#0D131F] border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-800 bg-[#080B10]/60 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                        <th class="py-3.5 px-6">Category Name</th>
                        <th class="py-3.5 px-6">Slug</th>
                        <th class="py-3.5 px-6">Description</th>
                        <th class="py-3.5 px-6 text-center">Experts</th>
                        <th class="py-3.5 px-6 text-center">Order</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="divide-y divide-gray-800/60 text-xs">
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-8 h-8 border-4 border-gray-800 border-t-[#00D4AA] rounded-full animate-spin mb-2"></div>
                                <span>Loading categories...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ADD/EDIT CATEGORY MODAL -->
<div id="categoryModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-[#0D131F] border border-gray-800 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl animate-fade-in">
        <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-[#080B10]/40">
            <h3 class="text-base font-extrabold text-white" id="modal-title">Add New Category</h3>
            <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="categoryForm" onsubmit="saveCategory(event)" class="p-6 space-y-4">
            <input type="hidden" id="category_id" name="id" value="">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Category Name *</label>
                <input type="text" id="category_name" name="name" required oninput="autoSlug()" placeholder="e.g. AI & Technology" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">URL Slug *</label>
                <input type="text" id="category_slug" name="slug" required placeholder="e.g. ai-technology" class="w-full bg-[#080B10] border border-gray-800 text-gray-300 font-mono rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Description</label>
                <textarea id="category_description" name="description" rows="2" placeholder="Brief summary of this expert specialization area..." class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA] resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Display Order</label>
                    <input type="number" id="category_order" name="display_order" value="0" min="0" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Status</label>
                    <select id="category_active" name="is_active" class="w-full bg-[#080B10] border border-gray-800 text-white rounded-xl p-3 text-xs focus:outline-none focus:border-[#00D4AA]">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-800 flex justify-end gap-3">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2.5 bg-[#080B10] border border-gray-800 hover:bg-gray-800 text-gray-300 text-xs font-bold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" id="save-btn" class="px-6 py-2.5 bg-[#00D4AA] hover:bg-[#00bfa0] text-[#080B10] text-xs font-extrabold rounded-xl transition shadow-md shadow-[#00D4AA]/20">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.BASE_PATH = '<?php echo BASE_PATH; ?>';
let categoriesData = [];

// Load categories on page load
document.addEventListener('DOMContentLoaded', loadCategories);

async function loadCategories() {
    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/categories.php`);
        const result = await res.json();
        
        if (result.success) {
            categoriesData = result.categories || [];
            renderCategories(categoriesData);
            updateStats(categoriesData);
        } else {
            document.getElementById('categories-table-body').innerHTML = `<tr><td colspan="7" class="py-8 text-center text-red-400 font-bold">${result.error || 'Failed to load categories.'}</td></tr>`;
        }
    } catch (e) {
        document.getElementById('categories-table-body').innerHTML = `<tr><td colspan="7" class="py-8 text-center text-red-400 font-bold">Network or server error while loading categories.</td></tr>`;
    }
}

function updateStats(data) {
    const total = data.length;
    const active = data.filter(c => parseInt(c.is_active) === 1).length;
    const experts = data.reduce((sum, c) => sum + parseInt(c.expert_count || 0), 0);

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-active').textContent = active;
    document.getElementById('stat-experts').textContent = experts;
}

function renderCategories(data) {
    const tbody = document.getElementById('categories-table-body');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-gray-500 italic">No categories found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = data.map(c => {
        const isActive = parseInt(c.is_active) === 1;
        const statusBadge = isActive 
            ? `<span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-full text-[11px] font-bold">Active</span>`
            : `<span class="px-2.5 py-1 bg-gray-800 border border-gray-700 text-gray-400 rounded-full text-[11px] font-bold">Inactive</span>`;

        return `
            <tr class="hover:bg-[#131b2e]/40 transition" id="cat-row-${c.id}">
                <td class="py-4 px-6 font-bold text-white">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full ${isActive ? 'bg-[#00D4AA]' : 'bg-gray-600'}"></span>
                        <span>${escapeHtml(c.name)}</span>
                    </div>
                </td>
                <td class="py-4 px-6 font-mono text-gray-400 text-[11px]">${escapeHtml(c.slug)}</td>
                <td class="py-4 px-6 text-gray-400 text-xs max-w-xs truncate">${escapeHtml(c.description || '—')}</td>
                <td class="py-4 px-6 text-center">
                    <span class="px-2.5 py-1 bg-cyan-950/40 border border-cyan-500/30 text-cyan-300 rounded-full text-xs font-mono font-bold">
                        ${c.expert_count || 0}
                    </span>
                </td>
                <td class="py-4 px-6 text-center font-mono text-gray-400">${c.display_order || 0}</td>
                <td class="py-4 px-6 text-center">
                    <button onclick="toggleCategoryStatus(${c.id})" title="Click to toggle status" class="hover:opacity-80 transition cursor-pointer">
                        ${statusBadge}
                    </button>
                </td>
                <td class="py-4 px-6 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick="editCategory(${c.id})" class="px-3 py-1.5 bg-[#080B10] hover:bg-gray-800 border border-gray-800 text-gray-200 hover:text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit
                        </button>
                        <button onclick="deleteCategory(${c.id}, '${escapeHtml(c.name)}', ${c.expert_count || 0})" class="px-3 py-1.5 bg-red-950/20 hover:bg-red-900/40 border border-red-800/40 text-red-300 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function filterCategories() {
    const q = document.getElementById('search-input').value.toLowerCase().trim();
    const status = document.getElementById('status-filter').value;

    const filtered = categoriesData.filter(c => {
        const matchQ = c.name.toLowerCase().includes(q) || c.slug.toLowerCase().includes(q) || (c.description && c.description.toLowerCase().includes(q));
        const matchStatus = (status === 'all') || (status === 'active' && parseInt(c.is_active) === 1) || (status === 'inactive' && parseInt(c.is_active) === 0);
        return matchQ && matchStatus;
    });

    renderCategories(filtered);
}

function autoSlug() {
    const id = document.getElementById('category_id').value;
    if (!id) { // Only auto-generate on new category
        const name = document.getElementById('category_name').value;
        const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('category_slug').value = slug;
    }
}

function openCategoryModal() {
    document.getElementById('modal-title').textContent = 'Add New Category';
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('category_slug').value = '';
    document.getElementById('category_description').value = '';
    document.getElementById('category_order').value = categoriesData.length + 1;
    document.getElementById('category_active').value = '1';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function editCategory(id) {
    const cat = categoriesData.find(c => parseInt(c.id) === parseInt(id));
    if (!cat) return;

    document.getElementById('modal-title').textContent = 'Edit Category: ' + cat.name;
    document.getElementById('category_id').value = cat.id;
    document.getElementById('category_name').value = cat.name;
    document.getElementById('category_slug').value = cat.slug;
    document.getElementById('category_description').value = cat.description || '';
    document.getElementById('category_order').value = cat.display_order || 0;
    document.getElementById('category_active').value = cat.is_active ? '1' : '0';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

async function saveCategory(e) {
    e.preventDefault();
    const id = document.getElementById('category_id').value;
    const name = document.getElementById('category_name').value.trim();
    const slug = document.getElementById('category_slug').value.trim();
    const description = document.getElementById('category_description').value.trim();
    const display_order = parseInt(document.getElementById('category_order').value) || 0;
    const is_active = parseInt(document.getElementById('category_active').value) || 1;

    const action = id ? 'update' : 'create';

    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/categories.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: action,
                id: id,
                name: name,
                slug: slug,
                description: description,
                display_order: display_order,
                is_active: is_active
            })
        });

        const result = await res.json();
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Category';

        if (result.success) {
            closeCategoryModal();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message,
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
            loadCategories();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.error || 'Failed to save category.',
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff',
                customClass: { popup: 'border border-gray-800 rounded-2xl' }
            });
        }
    } catch (e) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Category';
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Network error.',
            background: '#0D131F',
            color: '#fff'
        });
    }
}

async function toggleCategoryStatus(id) {
    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/categories.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'toggle_status',
                id: id
            })
        });
        const result = await res.json();
        if (result.success) {
            loadCategories();
        }
    } catch(e){}
}

async function deleteCategory(id, name, expertCount) {
    const warning = expertCount > 0 
        ? `<p class="text-amber-400 text-xs mt-2">⚠️ Note: ${expertCount} expert(s) are currently associated with this category.</p>`
        : '';

    const confirm = await Swal.fire({
        title: 'Delete Category?',
        html: `<p class="text-gray-300 text-sm">Are you sure you want to delete <strong>${escapeHtml(name)}</strong>?</p>${warning}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Delete Category',
        background: '#0D131F',
        color: '#fff',
        customClass: { popup: 'border border-gray-800 rounded-2xl' }
    });

    if (!confirm.isConfirmed) return;

    try {
        const res = await fetch(`${window.BASE_PATH}/admin-panel/apis/admin/categories.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        });

        const result = await res.json();
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: result.message,
                confirmButtonColor: '#00D4AA',
                background: '#0D131F',
                color: '#fff'
            });
            loadCategories();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: result.error || 'Failed to delete category.',
                background: '#0D131F',
                color: '#fff'
            });
        }
    } catch(e){}
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
