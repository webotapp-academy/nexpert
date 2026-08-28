<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';
require_once dirname(__DIR__) . '/admin-panel/apis/connection/pdo.php';

$program_id = $_GET['id'] ?? null;

if (!$program_id) {
    header('Location: ?panel=learner&page=browse-experts');
    exit;
}

// Fetch program details
try {
    $stmt = $pdo->prepare("
        SELECT w.*, ep.full_name, ep.profile_photo, ep.tagline, ep.rating_average
        FROM workflows w
        JOIN expert_profiles ep ON w.expert_id = ep.user_id
        WHERE w.id = ? AND w.is_active = 1
    ");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$program) {
        header('Location: ?panel=learner&page=browse-experts');
        exit;
    }
    
    // Get milestones (step_type = 'milestone' or empty for legacy/AI-generated programs)
    $stmt = $pdo->prepare("
        SELECT * FROM workflow_steps 
        WHERE workflow_id = ? AND (step_type = 'milestone' OR step_type = '' OR step_type IS NULL)
        ORDER BY step_order ASC
    ");
    $stmt->execute([$program_id]);
    $milestones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get assignments (step_type = 'assignment_template', 'assignment', 'task', or specific resource types)
    $stmt = $pdo->prepare("
        SELECT * FROM workflow_steps 
        WHERE workflow_id = ? AND step_type IN ('assignment_template', 'assignment', 'task', 'session', 'survey', 'followup')
        ORDER BY step_order ASC
    ");
    $stmt->execute([$program_id]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Program Details Error: " . $e->getMessage());
    header('Location: ?panel=learner&page=browse-experts');
    exit;
}

// Now include header after all redirects are done
$page_title = "Program Details - Nexpert.ai";
$panel_type = "learner";

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/navigation.php';
?>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
<div class="bg-[#080B10] min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button -->
        <a href="?panel=learner&page=expert-profile&expert_id=<?php echo $program['expert_id']; ?>" 
           class="inline-flex items-center gap-2 text-[#00D4AA] hover:text-[#00bda0] font-semibold mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Expert Profile
        </a>

        <!-- Program Header -->
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl overflow-hidden mb-8">
            <div class="bg-gradient-to-br from-[#1b253d] to-[#131b2e] border-b border-gray-800 p-8 text-white">
                <div class="flex items-start gap-6">
                    <img src="<?php echo htmlspecialchars($program['profile_photo'] ?? ''); ?>" 
                         alt="Expert" 
                         class="w-20 h-20 rounded-full border-4 border-white shadow-lg">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-black mb-2"><?php echo htmlspecialchars($program['title']); ?></h1>
                        <p class="text-blue-100 text-lg mb-4">by <?php echo htmlspecialchars($program['full_name']); ?></p>
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="inline-flex items-center gap-2 bg-[#0e1322] border border-[#00D4AA]/30 text-[#00D4AA] px-4 py-2 rounded-full text-sm font-bold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                                <?php echo $program['duration_weeks'] ?? 0; ?> weeks
                            </span>
                            <span class="inline-flex items-center gap-2 bg-[#0e1322] border border-[#00D4AA]/30 text-[#00D4AA] px-4 py-2 rounded-full text-sm font-bold">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"></path>
                                </svg>
                                <?php echo count($milestones); ?> Milestones
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8">
                <h2 class="text-2xl font-bold text-white mb-4">Program Overview</h2>
                <p class="text-gray-300 leading-relaxed mb-6"><?php echo nl2br(htmlspecialchars($program['description'] ?? '')); ?></p>
                
                <?php if (!empty($program['goal_outcome'])): ?>
                <div class="bg-[#0e1322] border-l-4 border-[#00D4AA] p-6 rounded-lg mb-6">
                    <h3 class="font-bold text-[#00D4AA] mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Learning Goal
                    </h3>
                    <p class="text-gray-300"><?php echo htmlspecialchars($program['goal_outcome']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="flex flex-wrap gap-4">
                    <a href="?panel=learner&page=program-payment&program_id=<?php echo $program_id; ?>" 
                       class="inline-flex items-center gap-2 bg-[#00D4AA] text-[#080B10] px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Enroll in Program - ₹<?php echo number_format($program['price_inr'] ?? 0, 2); ?>
                    </a>
                    <button onclick="openMessageModal()" class="inline-flex items-center gap-2 bg-transparent border border-gray-800 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Ask a Question
                    </button>
                </div>
            </div>
        </div>

        <!-- Milestones -->
        <?php if (count($milestones) > 0): ?>
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-7 h-7 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
                Program Milestones
            </h2>
            <div class="space-y-4">
                <?php foreach ($milestones as $index => $milestone): ?>
                <div class="flex gap-4 p-6 bg-[#0e1322] rounded-xl border border-gray-800 hover:border-gray-700 transition-all">
                    <div class="flex-shrink-0 w-12 h-12 bg-[#131b2e] border border-gray-800 text-[#00D4AA] rounded-full flex items-center justify-center font-bold text-lg">
                        <?php echo $index + 1; ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-white text-lg mb-2"><?php echo htmlspecialchars($milestone['title'] ?? ''); ?></h3>
                        <?php if (!empty($milestone['description'])): ?>
                        <p class="text-gray-400"><?php echo nl2br(htmlspecialchars($milestone['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Assignments -->
        <?php if (count($assignments) > 0): ?>
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-7 h-7 text-[#00D4AA]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Program Assignments
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($assignments as $assignment): ?>
                <div class="p-6 bg-[#0e1322] rounded-xl border border-gray-800 hover:border-gray-700 transition-all">
                    <h3 class="font-bold text-white mb-2"><?php echo htmlspecialchars($assignment['title'] ?? ''); ?></h3>
                    <?php if (!empty($assignment['description'])): ?>
                    <p class="text-gray-400 text-sm"><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Enrollment CTA After Assignments -->
        <div class="bg-[#131b2e] border border-gray-800 rounded-3xl shadow-2xl p-12 text-center mt-8">
            <h3 class="text-3xl font-bold text-white mb-4">Ready to Transform Your Skills?</h3>
            <p class="text-gray-400 text-lg mb-8 max-w-2xl mx-auto">
                Enroll now and join <?php echo htmlspecialchars($program['full_name']); ?> in this comprehensive learning journey
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="?panel=learner&page=program-payment&program_id=<?php echo $program_id; ?>" 
                   class="inline-flex items-center gap-2 bg-[#00D4AA] text-[#080B10] px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Enroll Now - ₹<?php echo number_format($program['price_inr'] ?? 0, 2); ?>
                </a>
                <button onclick="openMessageModal()" class="inline-flex items-center gap-2 bg-transparent border border-gray-800 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Have Questions?
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-[#131b2e] border border-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 text-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-white">Send a Message</h3>
            <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="mb-4">
            <p class="text-gray-400">To: <?php echo htmlspecialchars($program['full_name']); ?></p>
        </div>
        <form id="messageForm" onsubmit="sendMessage(event)">
            <textarea id="messageText" 
                      placeholder="Type your message here..." 
                      class="w-full px-4 py-3 bg-[#0e1322] border border-gray-800 rounded-lg focus:ring-2 focus:ring-[#00D4AA] focus:border-transparent resize-none text-white placeholder-gray-500"
                      rows="5"
                      required></textarea>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeMessageModal()" class="flex-1 px-4 py-2 border border-gray-800 text-gray-400 rounded-lg hover:bg-[#0e1322] transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-[#00D4AA] text-[#080B10] rounded-lg transition font-bold">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>



<script>
const expertId = <?php echo $program['expert_id']; ?>;

function openMessageModal() {
    document.getElementById('messageModal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageForm').reset();
}

async function sendMessage(event) {
    event.preventDefault();
    
    const messageText = document.getElementById('messageText').value;
    
    try {
        const response = await fetch(`${BASE_PATH}/admin-panel/apis/learner/messages.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                expert_id: expertId,
                message: messageText
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Message Sent!',
                text: 'Your message has been sent to the expert.',
                confirmButtonColor: '#3B82F6'
            });
            closeMessageModal();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed to Send',
                text: data.message || 'Could not send message. Please try again.',
                confirmButtonColor: '#3B82F6'
            });
        }
    } catch (error) {
        console.error('Error sending message:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while sending your message.',
            confirmButtonColor: '#3B82F6'
        });
    }
}

// Close modal when clicking outside
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
