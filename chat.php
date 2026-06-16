<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'customer';
$userName = $_SESSION['name'] ?? 'User';

// Get stand ID from URL
$standId = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;

if ($standId <= 0) {
    header('Location: index.php');
    exit;
}

// Get stand data
$stmt = $pdo->prepare("SELECT * FROM stands WHERE id = ? AND status = 'Open'");
$stmt->execute([$standId]);
$stand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stand) {
    header('Location: index.php');
    exit;
}

// Get or create chat room
$stmt = $pdo->prepare("SELECT id FROM chat_rooms WHERE stand_id = ? AND user_id = ?");
$stmt->execute([$standId, $userId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    $stmt = $pdo->prepare("INSERT INTO chat_rooms (stand_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$standId, $userId]);
    $roomId = $pdo->lastInsertId();
} else {
    $roomId = $room['id'];
}

// Handle sending message (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $message = trim($_POST['message'] ?? '');
    $requestId = $_POST['request_id'] ?? '';
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong']);
        exit;
    }
    
    try {
        // CEK DUPLIKAT: cek pesan yang sama dalam 3 detik terakhir
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total, id 
            FROM chat_messages 
            WHERE room_id = ? AND sender_id = ? AND message = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 3 SECOND)
        ");
        $stmt->execute([$roomId, $userId, $message]);
        $duplicateCheck = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Jika ada pesan yang sama dalam 3 detik terakhir, anggap duplikat
        if ($duplicateCheck && $duplicateCheck['total'] > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Pesan sudah terkirim',
                'duplicate' => true,
                'message_id' => $duplicateCheck['id'] ?? 0
            ]);
            exit;
        }
        
        // Insert message
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (room_id, sender_id, sender_type, message, created_at) 
            VALUES (?, ?, 'customer', ?, NOW())
        ");
        $stmt->execute([$roomId, $userId, $message]);
        $messageId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("
            UPDATE chat_rooms SET last_message = ?, last_message_time = NOW(), updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$message, $roomId]);
        
        $stmt = $pdo->prepare("SELECT user_id FROM stands WHERE id = ?");
        $stmt->execute([$standId]);
        $standOwner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($standOwner) {
            $stmt = $pdo->prepare("
                INSERT INTO chat_notifications (user_id, room_id, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$standOwner['user_id'], $roomId]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Pesan terkirim',
            'message_id' => $messageId,
            'sender_type' => 'customer',
            'created_at' => date('Y-m-d H:i:s'),
            'duplicate' => false
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
    }
    exit;
}

// Get chat messages with bot detection
$stmt = $pdo->prepare("
    SELECT 
        cm.*, 
        u.name as sender_name,
        CASE WHEN cbc.id IS NOT NULL THEN 1 ELSE 0 END as is_bot
    FROM chat_messages cm 
    LEFT JOIN users u ON cm.sender_id = u.id 
    LEFT JOIN chat_bot_conversations cbc ON cm.id = cbc.bot_message_id
    WHERE cm.room_id = ? 
    ORDER BY cm.created_at ASC
");
$stmt->execute([$roomId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark messages as read
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Chat • <?php echo htmlspecialchars($stand['stand_name']); ?></title>
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
        
        /* ===== HEADER CHAT ===== */
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
        
        .stand-info {
            flex: 1;
        }
        
        .stand-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
        }
        
        .stand-status {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.8);
        }
        
        .stand-status i {
            font-size: 0.5rem;
            color: #10b981;
            margin-right: 4px;
        }
        
        .header-action {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 14px;
            background: rgba(255,255,255,0.2);
            border-radius: 30px;
            transition: all 0.3s;
        }
        
        .header-action:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        /* ===== MESSAGES ===== */
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
        
        /* Date Divider */
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
        
        /* Message */
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
        
        /* ===== LABELS ===== */
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
        
        /* ===== EMPTY STATE ===== */
        .empty-chat {
            text-align: center;
            color: #94a3b8;
            padding: 40px;
            align-self: center;
            margin: auto;
        }
        
        .empty-chat .empty-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2.5rem;
            color: #6C4CF1;
            box-shadow: 0 8px 30px rgba(108,76,241,0.1);
        }
        
        .empty-chat h3 {
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .empty-chat p {
            font-size: 0.85rem;
        }
        
        /* ===== CHAT INPUT ===== */
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
        
        /* ===== TYPING INDICATORS ===== */
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
        
        /* ===== TOAST ===== */
        .chat-toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.85rem;
            z-index: 9999;
            animation: slideUp 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chat-toast.success { background: #10B981; color: white; }
        .chat-toast.error { background: #EF4444; color: white; }
        .chat-toast.info { background: #1e293b; color: white; }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .message { max-width: 90%; }
            .chat-messages { padding: 16px; }
            .chat-input { padding: 8px 16px; }
            .chat-input textarea { font-size: 0.85rem; padding: 10px 14px; }
            .send-btn { width: 42px; height: 42px; min-width: 42px; font-size: 1rem; }
            .bot-label, .human-label { font-size: 0.5rem; padding: 1px 8px; }
            .chat-toast { font-size: 0.75rem; padding: 8px 16px; bottom: 80px; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="chat-header">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="stand-info">
            <div class="stand-name"><?php echo htmlspecialchars($stand['stand_name']); ?></div>
            <div class="stand-status">
                <i class="fas fa-circle"></i> Online
            </div>
        </div>
        <a href="menu-stand.php?id=<?php echo $standId; ?>" class="header-action">
            <i class="fas fa-utensils"></i> Menu
        </a>
    </div>
    
    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <?php if(empty($messages)): ?>
            <div class="empty-chat">
                <div class="empty-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3>Mulai Percakapan</h3>
                <p>Tanyakan tentang menu atau buat pesanan</p>
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
                            echo htmlspecialchars($senderName); 
                            if ($senderType === 'stand') {
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
    
    <!-- Input -->
    <div class="chat-input">
        <textarea id="messageInput" rows="1" placeholder="Tulis pesan..." onkeydown="handleKeyPress(event)"></textarea>
        <button class="send-btn" id="sendBtn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
    
    <script>
        // =============================================
        // ===== CHAT.JS - OPTIMIZED VERSION =====
        // =============================================
        
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');
        const botTypingIndicator = document.getElementById('botTypingIndicator');
        const roomId = <?php echo $roomId; ?>;
        const standId = <?php echo $standId; ?>;
        const userId = <?php echo $userId; ?>;
        
        // ===== STATE =====
        let isSending = false;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
        let isPolling = false;
        let typingTimeout = null;
        let botTypingTimeout = null;
        let lastSentMessage = '';
        let lastSentTime = 0;
        let pollInterval = null;
        let isPageVisible = true;
        let reconnectAttempts = 0;
        const MAX_RECONNECT_ATTEMPTS = 5;
        let lastNotifTime = 0;
        
        // ===== VISIBILITY API =====
        document.addEventListener('visibilitychange', function() {
            isPageVisible = !document.hidden;
            if (isPageVisible) {
                // Polling segera saat halaman terlihat kembali
                pollNewMessages();
            }
        });
        
        // ===== UTILITY FUNCTIONS =====
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
        
        function showBotTyping() {
            botTypingIndicator.style.display = 'flex';
            clearTimeout(botTypingTimeout);
            botTypingTimeout = setTimeout(() => {
                botTypingIndicator.style.display = 'none';
            }, 3000);
        }
        
        function hideBotTyping() {
            botTypingIndicator.style.display = 'none';
            clearTimeout(botTypingTimeout);
        }
        
        function showTyping() {
            typingIndicator.style.display = 'flex';
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                typingIndicator.style.display = 'none';
            }, 3000);
        }
        
        function hideTyping() {
            typingIndicator.style.display = 'none';
            clearTimeout(typingTimeout);
        }
        
        // ===== NOTIFICATION SOUND =====
        function playNotificationSound() {
            const now = Date.now();
            if (now - lastNotifTime < 3000) return;
            lastNotifTime = now;
            
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } catch (e) {
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }
            }
        }
        
        // ===== TOAST NOTIFICATION =====
        function showToast(message, type = 'info') {
            const existingToast = document.querySelector('.chat-toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `chat-toast ${type}`;
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 3000);
        }
        
        // ===== CEK DUPLIKAT =====
        function isDuplicate(message) {
            // Cek dengan pesan terakhir yang dikirim
            if (message === lastSentMessage && (Date.now() - lastSentTime) < 3000) {
                return true;
            }
            
            // Cek dengan pesan terakhir di chat
            const lastMessages = chatMessages.querySelectorAll('.message-sent');
            if (lastMessages.length > 0) {
                const lastMsg = lastMessages[lastMessages.length - 1];
                const lastText = lastMsg.querySelector('.message-text')?.textContent || '';
                if (lastText === message) {
                    return true;
                }
            }
            
            return false;
        }
        
        // ===== KIRIM PESAN =====
        async function sendMessage(messageText) {
            if (messageText === undefined) {
                messageText = messageInput.value.trim();
            }
            
            if (!messageText || isSending) return;
            
            if (isDuplicate(messageText)) {
                console.log('⚠️ Duplicate message blocked:', messageText);
                return;
            }
            
            lastSentMessage = messageText;
            lastSentTime = Date.now();
            
            isSending = true;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                const formData = new FormData();
                formData.append('message', messageText);
                formData.append('request_id', Date.now());
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.duplicate) {
                    console.log('⚠️ Server detected duplicate');
                    isSending = false;
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    return;
                }
                
                if (result.success) {
                    const emptyState = chatMessages.querySelector('.empty-chat');
                    if (emptyState) emptyState.remove();
                    
                    const msgDiv = document.createElement('div');
                    msgDiv.className = 'message message-sent';
                    msgDiv.innerHTML = `
                        <div class="message-text">${escapeHtml(messageText)}</div>
                        <div class="message-time">${getCurrentTime()}</div>
                    `;
                    chatMessages.appendChild(msgDiv);
                    
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    scrollToBottom();
                    
                    // Panggil bot langsung
                    showBotTyping();
                    await callBotAPI(messageText);
                    
                    setTimeout(() => {
                        pollNewMessages();
                    }, 300);
                    
                } else {
                    showToast(result.message || 'Gagal mengirim pesan', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat mengirim pesan', 'error');
            } finally {
                isSending = false;
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        }
        
        // ===== PANGGIL BOT API =====
        async function callBotAPI(message) {
            try {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('room_id', roomId);
                formData.append('stand_id', standId);
                
                const response = await fetch('chat_bot_api.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && !result.skip) {
                    console.log('✅ Bot replied:', result.reply);
                    setTimeout(() => {
                        pollNewMessages();
                    }, 200);
                } else if (result.skip) {
                    console.log('ℹ️ Bot skipped (already replied by stand)');
                }
            } catch (error) {
                console.error('❌ Bot API error:', error);
                setTimeout(() => {
                    pollNewMessages();
                }, 500);
            }
        }
        
        // ===== POLL NEW MESSAGES =====
        async function pollNewMessages() {
            if (isPolling) return;
            isPolling = true;
            
            try {
                const response = await fetch(`get_chat_messages.php?room_id=${roomId}&last_id=${lastMessageId}`);
                const result = await response.json();
                
                if (!result.success) {
                    console.warn('Polling failed:', result.message);
                    reconnectAttempts++;
                    if (reconnectAttempts > MAX_RECONNECT_ATTEMPTS) {
                        console.error('Max reconnect attempts reached');
                        reconnectAttempts = 0;
                    }
                    isPolling = false;
                    return;
                }
                
                reconnectAttempts = 0;
                
                if (result.messages && result.messages.length > 0) {
                    hideBotTyping();
                    
                    result.messages.forEach(msg => {
                        if (msg.id <= lastMessageId) return;
                        
                        const isSent = (msg.sender_id == userId);
                        const isBot = msg.is_bot || false;
                        const senderType = msg.sender_type || 'customer';
                        
                        const msgDiv = document.createElement('div');
                        msgDiv.className = `message ${isSent ? 'message-sent' : 'message-received'}`;
                        msgDiv.id = `msg-${msg.id}`;
                        
                        let senderHtml = '';
                        if (!isSent && msg.sender_name) {
                            senderHtml = `<div class="message-sender">${escapeHtml(msg.sender_name)}`;
                            if (senderType === 'stand') {
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
                        
                        const emptyState = chatMessages.querySelector('.empty-chat');
                        if (emptyState) emptyState.remove();
                        
                        chatMessages.appendChild(msgDiv);
                        lastMessageId = msg.id;
                        
                        // Notifikasi untuk pesan baru dari stand
                        if (!isSent && senderType === 'stand') {
                            playNotificationSound();
                        }
                    });
                    
                    typingIndicator.style.display = 'none';
                    scrollToBottom();
                }
                
                // Update unread badge
                if (result.total_unread !== undefined) {
                    updateUnreadBadge(result.total_unread);
                }
                
            } catch (error) {
                console.error('Polling error:', error);
            } finally {
                isPolling = false;
            }
        }
        
        // ===== UPDATE UNREAD BADGE =====
        function updateUnreadBadge(count) {
            const badges = document.querySelectorAll('.chat-badge, .badge-notif');
            badges.forEach(badge => {
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            });
        }
        
        // ===== HANDLE KEY PRESS =====
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.stopPropagation();
                sendMessage();
            }
        }
        
        // ===== EVENT LISTENERS =====
        sendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sendMessage();
        });
        
        messageInput.addEventListener('keydown', handleKeyPress);
        
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // ===== START POLLING =====
        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(pollNewMessages, 1500);
        }
        
        startPolling();
        
        // ===== INIT =====
        setTimeout(scrollToBottom, 100);
        setTimeout(pollNewMessages, 500);
        
        console.log('✅ Chat initialized - optimized version');
        console.log('📌 Room ID:', roomId);
        console.log('📌 Stand ID:', standId);
        console.log('📌 User ID:', userId);
        console.log('📌 Polling interval: 1500ms');
    </script>
</body>
</html>