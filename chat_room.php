<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in and is stand owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'stand') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get stand owned by this user
$stmt = $pdo->prepare("SELECT id, stand_name FROM stands WHERE user_id = ?");
$stmt->execute([$userId]);
$stand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stand) {
    die("Anda belum memiliki stand. Hubungi admin!");
}

$standId = $stand['id'];
$standName = $stand['stand_name'];

// Get room ID
$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

if ($roomId <= 0) {
    header('Location: chat_stand.php');
    exit;
}

// Verify room belongs to this stand
$stmt = $pdo->prepare("SELECT cr.*, u.name as customer_name FROM chat_rooms cr JOIN users u ON cr.user_id = u.id WHERE cr.id = ? AND cr.stand_id = ?");
$stmt->execute([$roomId, $standId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    header('Location: chat_stand.php');
    exit;
}

$customerName = $room['customer_name'];

// Handle sending message (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (room_id, sender_id, sender_type, message, created_at) 
            VALUES (?, ?, 'stand', ?, NOW())
        ");
        $stmt->execute([$roomId, $userId, $message]);
        $messageId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("
            UPDATE chat_rooms SET last_message = ?, last_message_time = NOW(), updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$message, $roomId]);
        
        $stmt = $pdo->prepare("
            INSERT INTO chat_notifications (user_id, room_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$room['user_id'], $roomId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Pesan terkirim',
            'message_id' => $messageId,
            'sender_type' => 'stand',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
    }
    exit;
}

// ===== GET CHAT MESSAGES WITH BOT DETECTION =====
$stmt = $pdo->prepare("
    SELECT 
        cm.*, 
        u.name as sender_name,
        CASE 
            WHEN cbc.id IS NOT NULL THEN 1 
            ELSE 0 
        END as is_bot,
        CASE 
            WHEN cm.sender_type = 'stand' AND cbc.id IS NULL THEN 'human'
            WHEN cm.sender_type = 'stand' AND cbc.id IS NOT NULL THEN 'bot'
            ELSE cm.sender_type
        END as sender_type_display
    FROM chat_messages cm 
    LEFT JOIN users u ON cm.sender_id = u.id 
    LEFT JOIN chat_bot_conversations cbc ON cm.id = cbc.bot_message_id
    WHERE cm.room_id = ? 
    ORDER BY cm.created_at ASC
");
$stmt->execute([$roomId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
$stmt = $pdo->prepare("
    UPDATE chat_notifications SET is_read = 1 WHERE room_id = ? AND user_id = ?
");
$stmt->execute([$roomId, $userId]);

function formatTime($datetime) {
    return date('H:i', strtotime($datetime));
}

function formatDate($datetime) {
    return date('d/m/Y', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat • <?php echo htmlspecialchars($customerName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f0f4f8; 
            height: 100vh; 
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
            box-shadow: 0 4px 20px rgba(108,76,241,0.3);
        }
        
        .back-btn {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .customer-info {
            flex: 1;
        }
        
        .customer-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
        }
        
        .customer-email {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.8);
        }
        
        .chat-status {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.8);
        }
        
        .chat-status i {
            font-size: 0.5rem;
            color: #10b981;
            margin-right: 4px;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: linear-gradient(180deg, #f0f4f8 0%, #e8edf2 100%);
        }
        
        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        .date-divider {
            text-align: center;
            font-size: 0.7rem;
            color: #94a3b8;
            padding: 12px 0;
            position: relative;
        }
        
        .date-divider span {
            background: #e8edf2;
            padding: 4px 16px;
            border-radius: 20px;
            color: #64748b;
            font-weight: 500;
        }
        
        .message {
            max-width: 80%;
            padding: 12px 18px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            animation: fadeIn 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message-sent {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(108,76,241,0.2);
        }
        
        .message-received {
            background: white;
            color: #1e293b;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        /* ===== MESSAGE SENDER ===== */
        .message-sender {
            font-size: 0.65rem;
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .message-sent .message-sender {
            color: rgba(255,255,255,0.8);
        }
        
        .message-received .message-sender {
            color: #6C4CF1;
        }
        
        /* ===== LABEL STYLES ===== */
        .bot-label {
            background: #f59e0b;
            color: white;
            font-size: 0.55rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
            animation: botPulse 3s ease-in-out infinite;
        }
        
        .bot-label i {
            font-size: 0.6rem;
        }
        
        @keyframes botPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .human-label {
            background: #10b981;
            color: white;
            font-size: 0.55rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
        }
        
        .human-label i {
            font-size: 0.6rem;
        }
        
        .customer-label {
            background: #3b82f6;
            color: white;
            font-size: 0.55rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
        }
        
        .customer-label i {
            font-size: 0.6rem;
        }
        
        .message-text {
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .message-time {
            font-size: 0.6rem;
            opacity: 0.6;
            margin-top: 6px;
            text-align: right;
        }
        
        .message-sent .message-time {
            color: rgba(255,255,255,0.7);
        }
        
        .message-received .message-time {
            color: #94a3b8;
        }
        
        .chat-input {
            background: white;
            padding: 12px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-shrink: 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.03);
        }
        
        .chat-input textarea {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            resize: none;
            outline: none;
            transition: all 0.3s;
            max-height: 120px;
            min-height: 44px;
            background: #f8fafc;
        }
        
        .chat-input textarea:focus {
            border-color: #6C4CF1;
            background: white;
            box-shadow: 0 0 0 4px rgba(108,76,241,0.08);
        }
        
        .chat-input textarea::placeholder {
            color: #94a3b8;
        }
        
        .send-btn {
            width: 48px;
            height: 48px;
            min-width: 48px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(108,76,241,0.3);
        }
        
        .send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(108,76,241,0.4);
        }
        
        .send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .typing-indicator {
            display: none;
            align-self: flex-start;
            background: white;
            padding: 12px 18px;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            gap: 6px;
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.2s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.3; }
            30% { transform: translateY(-8px); opacity: 1; }
        }
        
        /* ===== BOT TYPING INDICATOR ===== */
        .bot-typing {
            display: none;
            align-self: flex-start;
            background: white;
            padding: 12px 18px;
            border-radius: 18px;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            gap: 6px;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .bot-typing .bot-label-small {
            font-size: 0.6rem;
            font-weight: 600;
            color: #f59e0b;
            margin-bottom: 2px;
        }
        
        .bot-typing .typing-dots {
            display: flex;
            gap: 6px;
        }
        
        .bot-typing .typing-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #94a3b8;
            border-radius: 50%;
            animation: typing 1.2s infinite;
        }
        
        .bot-typing .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .bot-typing .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @media (max-width: 768px) {
            .message { max-width: 90%; }
            .chat-messages { padding: 16px; }
            .chat-input { padding: 8px 16px; }
            .chat-input textarea { font-size: 0.85rem; padding: 10px 14px; }
            .send-btn { width: 42px; height: 42px; min-width: 42px; font-size: 1rem; }
            .bot-label, .human-label, .customer-label { font-size: 0.5rem; padding: 1px 8px; }
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <a href="chat_stand.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="customer-info">
            <div class="customer-name"><?php echo htmlspecialchars($customerName); ?></div>
            <div class="customer-email"><i class="fas fa-user"></i> Customer</div>
        </div>
        <div class="chat-status">
            <i class="fas fa-circle"></i> Online
        </div>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <?php if(empty($messages)): ?>
            <div style="text-align: center; color: #94a3b8; padding: 40px; align-self: center; margin: auto;">
                <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 2.5rem; color: #6C4CF1; box-shadow: 0 8px 30px rgba(108,76,241,0.1);">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3 style="color: #1e293b; margin-bottom: 4px;">Belum Ada Pesan</h3>
                <p style="font-size: 0.85rem;">Mulai percakapan dengan customer</p>
            </div>
        <?php else: ?>
            <?php 
            $lastDate = '';
            foreach($messages as $msg): 
                $msgDate = date('d/m/Y', strtotime($msg['created_at']));
                $isSent = ($msg['sender_id'] == $userId);
                $senderName = $isSent ? 'Anda' : ($msg['sender_name'] ?? 'Customer');
                $isBot = isset($msg['is_bot']) && $msg['is_bot'] == 1;
                $senderType = $msg['sender_type'] ?? 'customer';
            ?>
                <?php if($lastDate != $msgDate): ?>
                    <div class="date-divider">
                        <span><?php echo $msgDate; ?></span>
                    </div>
                <?php endif; ?>
                <div class="message <?php echo $isSent ? 'message-sent' : 'message-received'; ?>">
                    <?php if(!$isSent): ?>
                        <div class="message-sender">
                            <?php 
                            // Tampilkan nama sender
                            echo htmlspecialchars($senderName); 
                            
                            // ===== TAMPILKAN LABEL SESUAI TIPE SENDER =====
                            if ($senderType === 'customer') {
                                echo ' <span class="customer-label"><i class="fas fa-user"></i> Customer</span>';
                            } elseif ($senderType === 'stand') {
                                if ($isBot) {
                                    echo ' <span class="bot-label"><i class="fas fa-robot"></i> Bot</span>';
                                } else {
                                    echo ' <span class="human-label"><i class="fas fa-store"></i> Stand</span>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                    <div class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                </div>
            <?php 
                $lastDate = $msgDate;
            endforeach; 
            ?>
        <?php endif; ?>
        
        <!-- Bot Typing Indicator -->
        <div class="bot-typing" id="botTypingIndicator">
            <div class="bot-label-small">🤖 Bot sedang mengetik...</div>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
        
        <!-- Human Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <span></span><span></span><span></span>
        </div>
    </div>
    
    <div class="chat-input">
        <textarea id="messageInput" rows="1" placeholder="Balas pesan..." onkeydown="handleKeyPress(event)"></textarea>
        <button class="send-btn" id="sendBtn" onclick="sendMessage()">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
    
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const botTypingIndicator = document.getElementById('botTypingIndicator');
        const roomId = <?php echo $roomId; ?>;
        const userId = <?php echo $userId; ?>;
        let isSending = false;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
        let isPolling = false;
        let typingTimeout = null;
        let botTypingTimeout = null;
        
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function getCurrentTime() {
            return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
        
        // Show bot typing indicator
        function showBotTyping() {
            botTypingIndicator.style.display = 'flex';
            clearTimeout(botTypingTimeout);
            botTypingTimeout = setTimeout(() => {
                botTypingIndicator.style.display = 'none';
            }, 4000);
        }
        
        // Hide bot typing indicator
        function hideBotTyping() {
            botTypingIndicator.style.display = 'none';
            clearTimeout(botTypingTimeout);
        }
        
        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || isSending) return;
            
            isSending = true;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                const formData = new FormData();
                formData.append('message', message);
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const emptyState = chatMessages.querySelector('.empty-state');
                    if (emptyState) emptyState.remove();
                    
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'message message-sent';
                    msgDiv.innerHTML = `
                        <div class="message-text">${escapeHtml(message)}</div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    chatMessages.appendChild(msgDiv);
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    scrollToBottom();
                    
                    // Show bot typing indicator (simulate bot response)
                    showBotTyping();
                    
                    // After bot typing, poll for new messages
                    setTimeout(() => {
                        pollNewMessages();
                    }, 2000);
                } else {
                    alert(result.message || 'Gagal mengirim pesan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            } finally {
                isSending = false;
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        }
        
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }
        
        async function pollNewMessages() {
            if (isPolling) return;
            isPolling = true;
            
            try {
                const response = await fetch(`get_chat_messages.php?room_id=${roomId}&last_id=${lastMessageId}`);
                const result = await response.json();
                
                if (result.success && result.messages.length > 0) {
                    // Hide bot typing when messages arrive
                    hideBotTyping();
                    
                    result.messages.forEach(msg => {
                        if (msg.id <= lastMessageId) return;
                        
                        const isSent = (msg.sender_id == userId);
                        const isBot = msg.is_bot || false;
                        const senderType = msg.sender_type || 'customer';
                        
                        const msgDiv = document.createElement('div');
                        msgDiv.className = `message ${isSent ? 'message-sent' : 'message-received'}`;
                        
                        let senderHtml = '';
                        if (!isSent && msg.sender_name) {
                            senderHtml = `<div class="message-sender">${escapeHtml(msg.sender_name)}`;
                            
                            // ===== TAMPILKAN LABEL SESUAI TIPE SENDER =====
                            if (senderType === 'customer') {
                                senderHtml += ` <span class="customer-label"><i class="fas fa-user"></i> Customer</span>`;
                            } else if (senderType === 'stand') {
                                if (isBot) {
                                    senderHtml += ` <span class="bot-label"><i class="fas fa-robot"></i> Bot</span>`;
                                } else {
                                    senderHtml += ` <span class="human-label"><i class="fas fa-store"></i> Stand</span>`;
                                }
                            }
                            senderHtml += `</div>`;
                        }
                        
                        const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        
                        msgDiv.innerHTML = `
                            ${senderHtml}
                            <div class="message-text">${escapeHtml(msg.message)}</div>
                            <div class="message-time">${time}</div>
                        `;
                        
                        // Remove empty state if exists
                        const emptyState = chatMessages.querySelector('.empty-state');
                        if (emptyState) emptyState.remove();
                        
                        chatMessages.appendChild(msgDiv);
                        lastMessageId = msg.id;
                    });
                    
                    typingIndicator.style.display = 'none';
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Polling error:', error);
            } finally {
                isPolling = false;
            }
        }
        
        function showTyping() {
            typingIndicator.style.display = 'flex';
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                typingIndicator.style.display = 'none';
            }, 3000);
        }
        
        setInterval(pollNewMessages, 3000);
        setTimeout(scrollToBottom, 100);
    </script>
</body>
</html>