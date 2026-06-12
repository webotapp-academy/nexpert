<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-auth-check.php';

$page_title = "Credibility Console - Admin";
$panel_type = "admin";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/admin-sidebar.php';
?>

<!-- Page Header -->
<div class="p-6 bg-white border-b flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Agentic Credibility Console</h1>
        <p class="text-gray-600 mt-1">Monitor and manage trust signals and tiers</p>
    </div>
    <div class="flex space-x-3">
        <button onclick="processEvents()"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z">
                </path>
            </svg>
            Process Events
        </button>
        <button onclick="updateScores()"
            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            Recompute Scores
        </button>
    </div>
</div>

<div class="p-6">
    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-500 uppercase font-semibold">Tier A Experts</p>
            <h3 class="text-2xl font-bold" id="stat-tier-a">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-500 uppercase font-semibold">Tier B Experts</p>
            <h3 class="text-2xl font-bold" id="stat-tier-b">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-gray-500">
            <p class="text-sm text-gray-500 uppercase font-semibold">Tier C Experts</p>
            <h3 class="text-2xl font-bold" id="stat-tier-c">0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
            <p class="text-sm text-gray-500 uppercase font-semibold">Pending Events</p>
            <h3 class="text-2xl font-bold" id="stat-pending">0</h3>
        </div>
    </div>

    <!-- Experts Trust Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expert</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trust Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stability</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">S / O / B / C</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Updated</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="trust-table" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading trust data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Timeline Modal -->
<div id="timeline-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Expert Trust Timeline</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div id="timeline-content" class="space-y-4">
            <!-- Timeline items will be injected here -->
        </div>
    </div>
</div>

<script>
    window.BASE_PATH = '<?php echo BASE_PATH; ?>';

    window.loadTrustData = async function () {
        console.log('Loading trust data...');
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/credibility.php?action=get_experts_trust`);
            const data = await response.json();

            console.log('Trust data received:', data);

            if (data.success && data.experts) {
                const tableHtml = data.experts.map(expert => {
                    const tierClass = expert.trust_tier === 'A' ? 'bg-green-100 text-green-800' :
                        expert.trust_tier === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800';

                    const frozenStatus = expert.is_frozen == 1 ? '<span class="ml-2 text-red-500" title="Frozen">❄️</span>' : '';
                    const freezeBtnText = expert.is_frozen == 1 ? 'Unfreeze' : 'Freeze';
                    const freezeBtnClass = expert.is_frozen == 1 ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900';

                    let photoUrl = window.BASE_PATH + '/assets/images/default-avatar.png';
                    if (expert.profile_photo) {
                        photoUrl = expert.profile_photo.startsWith('http') ? expert.profile_photo : window.BASE_PATH + '/' + expert.profile_photo;
                    }

                    return `
                    <tr>
                        <td class="px-6 py-4 flex items-center">
                            <img src="${photoUrl}" class="w-10 h-10 rounded-full mr-3 border shadow-sm object-cover">
                            <div>
                                <p class="font-bold text-gray-900">${expert.full_name || 'Unknown'}${frozenStatus}</p>
                                <p class="text-xs text-gray-500">${expert.email || ''}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full ${tierClass}">Tier ${expert.trust_tier || 'C'}</span>
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-lg">${parseFloat(expert.overall_score || 0).toFixed(1)}</td>
                        <td class="px-6 py-4 text-sm">${parseFloat(expert.stability_score || 0).toFixed(0)}%</td>
                        <td class="px-6 py-4 text-xs font-mono text-gray-600">
                            ${parseFloat(expert.structure_score || 0).toFixed(0)} / ${parseFloat(expert.outcome_score || 0).toFixed(0)} / ${parseFloat(expert.boundary_score || 0).toFixed(0)} / ${parseFloat(expert.consistency_score || 0).toFixed(0)}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">${expert.last_updated || 'Never'}</td>
                        <td class="px-6 py-4 space-x-2">
                            <button onclick="viewTimeline(${expert.expert_id})" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Timeline</button>
                            <button onclick="viewSignals(${expert.expert_id})" class="text-blue-600 hover:text-blue-900 font-medium text-xs">Signals</button>
                            <button onclick="toggleFreeze(${expert.expert_id})" class="${freezeBtnClass} font-medium text-xs">${freezeBtnText}</button>
                        </td>
                    </tr>
                `;
                }).join('');

                document.getElementById('trust-table').innerHTML = tableHtml || '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No experts found</td></tr>';

                const experts = data.experts || [];
                document.getElementById('stat-tier-a').innerText = experts.filter(e => e.trust_tier === 'A').length;
                document.getElementById('stat-tier-b').innerText = experts.filter(e => e.trust_tier === 'B').length;
                document.getElementById('stat-tier-c').innerText = experts.filter(e => e.trust_tier === 'C').length;

            } else {
                document.getElementById('trust-table').innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load data: ${data.message || 'Unknown error'}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading trust data:', error);
            document.getElementById('trust-table').innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Network Error: ${error.message}</td></tr>`;
        }
    }

    window.processEvents = async function () {
        Swal.fire({ title: 'Processing Events...', text: 'Agents are analyzing trust signals...', didOpen: () => { Swal.showLoading() } });
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/events/process.php`);
            const data = await response.json();
            if (data.success) {
                Swal.fire('Processed!', data.message, 'success');
                window.loadTrustData();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to trigger processor', 'error');
        }
    }

    window.updateScores = async function () {
        Swal.fire({ title: 'Updating Scores...', text: 'Running EMA aggregation engine...', didOpen: () => { Swal.showLoading() } });
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/credibility-actions.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'recompute_all' })
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Updated!', data.message, 'success');
                window.loadTrustData();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update scores', 'error');
        }
    }

    window.toggleFreeze = async function (expertId) {
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/credibility-actions.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_freeze', expert_id: expertId })
            });
            const data = await response.json();
            if (data.success) {
                window.loadTrustData();
            }
        } catch (error) {
            console.error('Error toggling freeze:', error);
        }
    }

    window.viewSignals = async function (expertId) {
        document.getElementById('timeline-modal').classList.remove('hidden');
        document.getElementById('timeline-content').innerHTML = '<p class="text-center text-gray-500 py-8">Loading signals...</p>';
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/credibility.php?action=get_expert_signals&expert_id=${expertId}`);
            const data = await response.json();
            if (data.success) {
                const signalsHtml = data.signals.map(s => `
                <div class="flex items-start p-4 border rounded-lg bg-gray-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                        ${s.agent_type.charAt(0).toUpperCase()}
                    </div>
                    <div class="ml-4 flex-grow">
                        <div class="flex justify-between">
                            <p class="text-sm font-bold text-gray-900">${s.agent_type.toUpperCase()} SIGNAL</p>
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-200 text-blue-800">${s.signal_value} / 100</span>
                        </div>
                        <p class="text-xs text-gray-700 mt-1 italic">"${s.metadata ? (s.metadata.justification || 'No justification provided') : 'No metadata'}"</p>
                        <p class="text-[10px] text-gray-400 mt-2">${s.created_at}</p>
                    </div>
                </div>
            `).join('');
                document.getElementById('timeline-content').innerHTML = signalsHtml || '<p class="text-center text-gray-500 py-8">No signals found</p>';
            }
        } catch (error) {
            console.error('Error loading signals:', error);
        }
    }

    window.viewTimeline = async function (expertId) {
        document.getElementById('timeline-modal').classList.remove('hidden');
        document.getElementById('timeline-content').innerHTML = '<p class="text-center text-gray-500 py-8">Loading history...</p>';
        try {
            const response = await window.AdminAPI.fetch(`${window.BASE_PATH}/admin-panel/apis/admin/credibility.php?action=get_expert_timeline&expert_id=${expertId}`);
            const data = await response.json();
            if (data.success) {
                const historyHtml = data.history.map(h => `
                <div class="flex items-center p-4 border rounded-lg bg-gray-50">
                    <div class="flex-shrink-0 w-12 text-center">
                        <span class="block text-lg font-bold">${parseFloat(h.overall_score).toFixed(1)}</span>
                        <span class="text-[10px] text-gray-500">Tier ${h.trust_tier}</span>
                    </div>
                    <div class="ml-4 flex-grow border-l pl-4">
                        <p class="text-sm font-medium text-gray-900">Score Snapshot</p>
                        <p class="text-xs text-gray-500">${h.created_at}</p>
                    </div>
                </div>
            `).join('');
                document.getElementById('timeline-content').innerHTML = historyHtml || '<p class="text-center text-gray-500 py-8">No history found</p>';
            }
        } catch (error) {
            console.error('Error loading timeline:', error);
        }
    }

    window.closeModal = function () {
        document.getElementById('timeline-modal').classList.add('hidden');
    }

    window.loadTrustData();
</script>

<?php require_once 'includes/footer.php'; ?>
</div>