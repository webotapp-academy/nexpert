<?php
// Load domain path configuration
$base_path = require_once dirname(__DIR__) . '/admin-panel/apis/connection/domain-path.php';

// Central session + config
require_once dirname(__DIR__) . '/includes/session-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/admin-panel/apis/connection/pdo.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'learner') {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_PATH . '/index.php?panel=learner&page=auth');
    exit;
}

$userId = $_SESSION['user_id'];

$page_title = "Messages - Nexpert.ai";
$panel_type = "learner";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/includes/navigation.php';

// Get all experts who have messaged this learner or whom this learner has messaged
$stmt = $pdo->prepare("
    SELECT DISTINCT
        u.id as expert_id,
        ep.full_name as expert_name,
        ep.profile_photo,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND recipient_id = ? AND is_read = 0) as unread_count,
        (SELECT message FROM messages WHERE (sender_id = u.id AND recipient_id = ?) OR (sender_id = ? AND recipient_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE (sender_id = u.id AND recipient_id = ?) OR (sender_id = ? AND recipient_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM messages m
    JOIN users u ON (m.sender_id = u.id OR m.recipient_id = u.id)
    JOIN expert_profiles ep ON u.id = ep.user_id
    WHERE (m.sender_id = ? OR m.recipient_id = ?) AND u.id != ? AND u.role = 'expert'
    GROUP BY u.id, ep.full_name, ep.profile_photo
    ORDER BY last_message_time DESC
");
$stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script>
    document.body.className = "bg-[#080B10] min-h-screen text-white";
</script>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Messages</h1>
        <p class="text-gray-400 mt-2">Communicate with your experts</p>
    </div>

    <?php if (count($conversations) > 0): ?>
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1 bg-[#131b2e] border border-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-800 bg-[#0e1322]/50">
                <h2 class="font-semibold text-white">Conversations</h2>
            </div>
            <div class="divide-y divide-gray-800 max-h-[600px] overflow-y-auto bg-[#131b2e]">
                <?php foreach ($conversations as $index => $conv): ?>
                <button class="conversation-item w-full text-left p-4 hover:bg-[#0e1322]/40 transition <?php echo $index === 0 ? 'bg-[#0e1322]' : ''; ?>"
                        data-expert-id="<?php echo $conv['expert_id']; ?>"
                        data-expert-name="<?php echo htmlspecialchars($conv['expert_name']); ?>">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                            <?php if (!empty($conv['profile_photo'])): ?>
                                <img src="<?php echo BASE_PATH . '/' . ltrim($conv['profile_photo'], '/'); ?>" 
                                     alt="<?php echo htmlspecialchars($conv['expert_name']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-primary text-white text-lg font-bold">
                                    <?php echo strtoupper(substr($conv['expert_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-white truncate"><?php echo htmlspecialchars($conv['expert_name']); ?></h3>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="bg-[#00D4AA] text-[#080B10] text-xs font-bold rounded-full px-2 py-0.5"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-400 truncate mt-1"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : ''); ?></p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, g:i A', strtotime($conv['last_message_time'])); ?></p>
                        </div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Message Thread -->
        <div class="lg:col-span-2 bg-[#131b2e] border border-gray-800 rounded-lg shadow-lg flex flex-col" style="height: 700px;">
            <div class="p-4 border-b border-gray-800 bg-[#0e1322]/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-800 overflow-hidden" id="current-expert-photo"></div>
                <h2 class="font-semibold text-white" id="current-expert-name">Select a conversation</h2>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                <div class="text-center text-gray-500 py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p>Select a conversation to view messages</p>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-800 bg-[#0e1322]/30 hidden" id="message-input-area">
                <div class="flex gap-3">
                    <textarea id="message-input" 
                               rows="2" 
                               class="flex-1 px-4 py-2 bg-[#0e1322] border border-gray-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#00D4AA] text-white resize-none" 
                               placeholder="Type your message..."></textarea>
                    <button id="send-message-btn" class="bg-[#00D4AA] text-[#080B10] font-bold px-6 py-2 rounded-lg hover:bg-[#00bda0] transition self-end">
                        <svg id="send-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        <svg id="send-loader" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1"><span id="char-count">0</span>/1000 characters</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-[#131b2e] border border-gray-800 rounded-lg shadow-lg p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <h3 class="text-xl font-semibold text-white mb-2">No Messages Yet</h3>
        <p class="text-gray-400">You don't have any messages. Book a session with an expert to start chatting!</p>
    </div>
    <?php endif; ?>
</div>

<script>
window.BASE_PATH = '<?php echo BASE_PATH; ?>';
let currentExpertId = null;
const messagesContainer = document.getElementById('messages-container');
const messageInput = document.getElementById('message-input');
const charCount = document.getElementById('char-count');
const sendBtn = document.getElementById('send-message-btn');
const inputArea = document.getElementById('message-input-area');

// Load messages for selected conversation
document.querySelectorAll('.conversation-item').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.conversation-item').forEach(b => b.classList.remove('bg-[#0e1322]'));
        this.classList.add('bg-[#0e1322]');
        
        currentExpertId = this.dataset.expertId;
        const expertName = this.dataset.expertName;
        
        document.getElementById('current-expert-name').textContent = expertName;
        inputArea.classList.remove('hidden');
        
        loadMessages(currentExpertId);
    });
});

// Check for expert_id in URL parameter (deep linking from other pages)
const urlParams = new URLSearchParams(window.location.search);
const targetExpertId = urlParams.get('expert_id');

if (targetExpertId) {
    // Try to find and click the conversation with this expert
    const targetConversation = document.querySelector(`.conversation-item[data-expert-id="${targetExpertId}"]`);
    if (targetConversation) {
        targetConversation.click();
    } else {
        // No existing conversation with this expert - we need to initiate one
        // Get expert info and create a new conversation view
        fetch(`${window.BASE_PATH}/admin-panel/apis/learner/expert-profile.php?expert_id=${targetExpertId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const expert = data.data;
                    currentExpertId = targetExpertId;
                    document.getElementById('current-expert-name').textContent = expert.name;
                    inputArea.classList.remove('hidden');
                    messagesContainer.innerHTML = '<div class="text-center text-gray-500 py-12"><p>No messages yet. Start the conversation!</p></div>';
                } else {
                    // Expert not found - show error and fall back
                    console.error('Expert not found:', data.message);
                    alert('Expert not found. Showing your existing conversations instead.');
                    // Fall back to first conversation
                    if (document.querySelector('.conversation-item')) {
                        document.querySelector('.conversation-item').click();
                    }
                }
            })
            .catch(err => {
                console.error('Error loading expert:', err);
                alert('Failed to load expert information. Showing your existing conversations instead.');
                // Fall back to first conversation
                if (document.querySelector('.conversation-item')) {
                    document.querySelector('.conversation-item').click();
                }
            });
    }
} else {
    // Load first conversation by default if exists
    if (document.querySelector('.conversation-item')) {
        document.querySelector('.conversation-item').click();
    }
}

function loadMessages(expertId) {
    fetch(`${window.BASE_PATH}/admin-panel/apis/learner/messages.php?expert_id=${expertId}`)
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
        messagesContainer.innerHTML = '<div class="text-center text-gray-500 py-12"><p>No messages yet. Start the conversation!</p></div>';
        return;
    }
    
    messagesContainer.innerHTML = messages.map(msg => {
        const isMyMessage = msg.sender_role === 'learner';
        return `
            <div class="flex ${isMyMessage ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[70%] ${isMyMessage ? 'bg-[#00D4AA] text-[#080B10] font-medium' : 'bg-[#0e1322] border border-gray-800 text-white'} rounded-lg px-4 py-2">
                    <p class="text-sm">${escapeHtml(msg.message)}</p>
                    <p class="text-xs ${isMyMessage ? 'text-[#080B10]/70' : 'text-gray-500'} mt-1">${formatTime(msg.created_at)}</p>
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
    if (!message || !currentExpertId) return;
    
    const sendIcon = document.getElementById('send-icon');
    const sendLoader = document.getElementById('send-loader');
    
    sendBtn.disabled = true;
    sendIcon.classList.add('hidden');
    sendLoader.classList.remove('hidden');
    
    fetch(`${window.BASE_PATH}/admin-panel/apis/learner/messages.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            expert_id: currentExpertId,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            charCount.textContent = '0';
            loadMessages(currentExpertId);
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
