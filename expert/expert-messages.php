<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as expert
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'expert') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=expert&page=auth');
    exit;
}

$userId = $_SESSION['user_id'];

$page_title = "Messages - Nexpert.ai";
$panel_type = "expert";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

// Get all learners who have messaged this expert or whom this expert has messaged
$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.id as learner_id,
        lp.full_name as learner_name,
        lp.profile_photo,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND recipient_id = ? AND is_read = 0) as unread_count,
        (SELECT message FROM messages WHERE (sender_id = u.id AND recipient_id = ?) OR (sender_id = ? AND recipient_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE (sender_id = u.id AND recipient_id = ?) OR (sender_id = ? AND recipient_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM messages m
    JOIN users u ON (m.sender_id = u.id OR m.recipient_id = u.id)
    JOIN learner_profiles lp ON u.id = lp.user_id
    WHERE (m.sender_id = ? OR m.recipient_id = ?) AND u.id != ? AND u.role = 'learner'
    GROUP BY u.id, lp.full_name, lp.profile_photo
    ORDER BY last_message_time DESC
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-[#080B10] text-gray-100 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 pb-6 border-b border-gray-800">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-[#00D4AA]/10 text-[#00D4AA] border border-[#00D4AA]/25 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00D4AA] animate-pulse"></span>
                Encrypted Communications
            </div>
            <h1 class="text-3xl font-extrabold text-white">Direct Advisory Messages</h1>
            <p class="text-sm text-gray-400 mt-1">Communicate with your active learners and answer asynchronous advisory inquiries</p>
        </div>

        <?php if (count($conversations) > 0): ?>
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Conversations List -->
            <div class="lg:col-span-1 bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl overflow-hidden">
                <div class="p-4 border-b border-gray-800 bg-[#080B10]">
                    <h2 class="font-extrabold text-white text-xs uppercase tracking-wider">Conversations</h2>
                </div>
                <div class="divide-y divide-gray-800/60 max-h-[600px] overflow-y-auto">
                    <?php foreach ($conversations as $index => $conv): ?>
                    <button class="conversation-item w-full text-left p-4 hover:bg-[#131B2E] transition <?php echo $index === 0 ? 'bg-[#131B2E] border-l-2 border-[#00D4AA]' : ''; ?>"
                            data-learner-id="<?php echo $conv['learner_id']; ?>"
                            data-learner-name="<?php echo htmlspecialchars($conv['learner_name']); ?>">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-full bg-[#080B10] border border-gray-700 shrink-0 overflow-hidden">
                                <?php if (!empty($conv['profile_photo'])): ?>
                                    <img src="<?php echo BASE_PATH . '/' . ltrim($conv['profile_photo'], '/'); ?>" 
                                         alt="<?php echo htmlspecialchars($conv['learner_name']); ?>"
                                         class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-[#00D4AA]/20 text-[#00D4AA] text-sm font-black">
                                        <?php echo getInitials($conv['learner_name']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-white text-xs truncate"><?php echo htmlspecialchars($conv['learner_name']); ?></h3>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span class="bg-[#00D4AA] text-[#080B10] text-[10px] font-extrabold rounded-full px-2 py-0.5"><?php echo $conv['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-400 truncate mt-1"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : ''); ?></p>
                                <p class="text-[10px] text-gray-500 font-mono mt-1"><?php echo date('M j, g:i A', strtotime($conv['last_message_time'])); ?></p>
                            </div>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Message Thread -->
            <div class="lg:col-span-2 bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl flex flex-col overflow-hidden" style="height: 700px;">
                <div class="p-4 border-b border-gray-800 bg-[#080B10] flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-800 overflow-hidden border border-gray-700" id="current-learner-photo"></div>
                    <h2 class="font-bold text-white text-xs" id="current-learner-name">Select a conversation</h2>
                </div>
                
                <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-[#080B10]/50" id="messages-container">
                    <div class="text-center text-gray-500 py-12">
                        <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-xs text-gray-400">Select a conversation to view advisory messages</p>
                    </div>
                </div>
                
                <div class="p-4 border-t border-gray-800 bg-[#080B10] hidden" id="message-input-area">
                    <div class="flex gap-3">
                        <textarea id="message-input" 
                                  rows="2" 
                                  class="flex-1 px-4 py-2.5 bg-[#0D131F] border border-gray-700 text-white rounded-xl focus:outline-none focus:border-[#00D4AA] text-xs resize-none placeholder-gray-500" 
                                  placeholder="Type your response..."></textarea>
                        <button id="send-message-btn" class="bg-[#00D4AA] text-[#080B10] px-5 py-2.5 rounded-xl hover:bg-[#00bfa0] transition font-extrabold self-end shadow-md">
                            <svg id="send-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <svg id="send-loader" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 font-mono mt-1.5"><span id="char-count">0</span>/1000 characters</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-[#0D131F] border border-gray-800 rounded-3xl shadow-xl p-12 text-center">
            <div class="inline-block p-4 bg-[#080B10] border border-gray-800 rounded-2xl mb-4">
                <svg class="w-12 h-12 mx-auto text-[#00D4AA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-white mb-1">No Messages Yet</h3>
            <p class="text-xs text-gray-400 max-w-sm mx-auto">You don't have any direct messages yet. Learners who book your programs or advisory slots can message you here.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.BASE_PATH = '<?php echo BASE_PATH; ?>';
let currentLearnerId = null;
const messagesContainer = document.getElementById('messages-container');
const messageInput = document.getElementById('message-input');
const charCount = document.getElementById('char-count');
const sendBtn = document.getElementById('send-message-btn');
const inputArea = document.getElementById('message-input-area');

// Load messages for selected conversation
document.querySelectorAll('.conversation-item').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.conversation-item').forEach(b => b.classList.remove('bg-[#131B2E]', 'border-l-2', 'border-[#00D4AA]'));
        this.classList.add('bg-[#131B2E]', 'border-l-2', 'border-[#00D4AA]');
        
        currentLearnerId = this.dataset.learnerId;
        const learnerName = this.dataset.learnerName;
        
        document.getElementById('current-learner-name').textContent = learnerName;
        inputArea.classList.remove('hidden');
        
        loadMessages(currentLearnerId);
    });
});

// Load first conversation by default if exists
if (document.querySelector('.conversation-item')) {
    document.querySelector('.conversation-item').click();
}

function loadMessages(learnerId) {
    fetch(`${window.BASE_PATH}/admin-panel/apis/expert/messages.php?learner_id=${learnerId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        })
        .catch(err => console.error('Error loading messages:', err));
}

function displayMessages(messages) {
    if (messages.length === 0) {
        messagesContainer.innerHTML = '<div class="text-center text-gray-500 py-12"><p class="text-xs text-gray-400">No messages yet. Send your first advisory update!</p></div>';
        return;
    }
    
    messagesContainer.innerHTML = messages.map(msg => {
        const isMyMessage = msg.sender_role === 'expert';
        return `
            <div class="flex ${isMyMessage ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[75%] ${isMyMessage ? 'bg-[#00D4AA] text-[#080B10]' : 'bg-[#080B10] text-gray-200 border border-gray-800'} rounded-2xl px-4 py-2.5 shadow-md">
                    <p class="text-xs font-medium leading-relaxed">${escapeHtml(msg.message)}</p>
                    <p class="text-[10px] ${isMyMessage ? 'text-[#080B10]/75' : 'text-gray-500'} font-mono mt-1 text-right">${formatTime(msg.created_at)}</p>
                </div>
            </div>
        `;
    }).join('');
    
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Character count
messageInput.addEventListener('input', function() {
    const count = this.value.length;
    charCount.textContent = count;
    if (count > 1000) {
        this.value = this.value.substring(0, 1000);
        charCount.textContent = '1000';
    }
});

// Send message
sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function sendMessage() {
    const message = messageInput.value.trim();
    if (!message || !currentLearnerId) return;
    
    const sendIcon = document.getElementById('send-icon');
    const sendLoader = document.getElementById('send-loader');
    
    sendBtn.disabled = true;
    sendIcon.classList.add('hidden');
    sendLoader.classList.remove('hidden');
    
    fetch(`${window.BASE_PATH}/admin-panel/apis/expert/messages.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            learner_id: currentLearnerId,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            charCount.textContent = '0';
            loadMessages(currentLearnerId);
        } else {
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Failed to send message');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendIcon.classList.remove('hidden');
        sendLoader.classList.add('hidden');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/footer.php'; ?>
