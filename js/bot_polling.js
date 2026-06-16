// bot_polling.js - Tambahkan ke halaman chat

// Cek pesan bot baru setiap 10 detik
let lastBotMessageId = 0;

async function checkNewBotMessages() {
    try {
        const response = await fetch(`get_bot_messages.php?room_id=${roomId}&last_id=${lastBotMessageId}`);
        const result = await response.json();
        
        if (result.success && result.messages.length > 0) {
            result.messages.forEach(msg => {
                // Tampilkan pesan bot dengan label
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message message-received';
                msgDiv.innerHTML = `
                    <div class="message-sender">
                        ${escapeHtml(msg.sender_name)}
                        <span class="bot-label">🤖 Bot</span>
                    </div>
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                `;
                chatMessages.appendChild(msgDiv);
                lastBotMessageId = msg.id;
            });
            scrollToBottom();
        }
    } catch (error) {
        console.error('Error checking bot messages:', error);
    }
}

// Jalankan setiap 10 detik
setInterval(checkNewBotMessages, 10000);