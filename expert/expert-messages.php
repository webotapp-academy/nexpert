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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
        <p class="text-gray-600 mt-2">Communicate with your experts</p>
    </div>

    <?php if (count($conversations) > 0): ?>
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Conversations List -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Conversations</h2>
            </div>
            <div class="divide-y divide-gray-200 max-h-[600px] overflow-y-auto">
                <?php foreach ($conversations as $index => $conv): ?>
                <button class="conversation-item w-full text-left p-4 hover:bg-gray-50 transition <?php echo $index === 0 ? 'bg-blue-50' : ''; ?>"
                        data-learner-id="<?php echo $conv['learner_id']; ?>"
                        data-learner-name="<?php echo htmlspecialchars($conv['learner_name']); ?>">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex-shrink-0 overflow-hidden">
                            <?php if (!empty($conv['profile_photo'])): ?>
                                <img src="<?php echo BASE_PATH . '/' . ltrim($conv['profile_photo'], '/'); ?>" 
                                     alt="<?php echo htmlspecialchars($conv['learner_name']); ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-primary text-white text-lg font-bold">
                                    <?php echo strtoupper(substr($conv['learner_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($conv['learner_name']); ?></h3>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="bg-primary text-white text-xs font-bold rounded-full px-2 py-0.5"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-600 truncate mt-1"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)) . (strlen($conv['last_message']) > 50 ? '...' : ''); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo date('M j, g:i A', strtotime($conv['last_message_time'])); ?></p>
                        </div>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Message Thread -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg flex flex-col" style="height: 700px;">
            <div class="p-4 border-b border-gray-200 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-200 overflow-hidden" id="current-learner-photo"></div>
                <h2 class="font-semibold text-gray-900" id="current-learner-name">Select a conversation</h2>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                <div class="text-center text-gray-500 py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p>Select a conversation to view messages</p>
                </div>
            </div>
            
            <div class="p-4 border-t border-gray-200 hidden" id="message-input-area">
                <div class="flex gap-3">
                    <textarea id="message-input" 
                              rows="2" 
                              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none" 
                              placeholder="Type your message..."></textarea>
                    <button id="send-message-btn" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition self-end">
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
    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Messages Yet</h3>
        <p class="text-gray-600">You don't have any messages yet. Your learners can message you from their panel.</p>
    </div>
    <?php endif; ?>
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
        document.querySelectorAll('.conversation-item').forEach(b => b.classList.remove('bg-blue-50'));
        this.classList.add('bg-blue-50');
        
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
        messagesContainer.innerHTML = '<div class="text-center text-gray-500 py-12"><p>No messages yet. Start the conversation!</p></div>';
        return;
    }
    
    messagesContainer.innerHTML = messages.map(msg => {
        const isMyMessage = msg.sender_role === 'expert';
        return `
            <div class="flex ${isMyMessage ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[70%] ${isMyMessage ? 'bg-primary text-white' : 'bg-gray-100 text-gray-900'} rounded-lg px-4 py-2">
                    <p class="text-sm">${escapeHtml(msg.message)}</p>
                    <p class="text-xs ${isMyMessage ? 'text-blue-100' : 'text-gray-500'} mt-1">${formatTime(msg.created_at)}</p>
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
