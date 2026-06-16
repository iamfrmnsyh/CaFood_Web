<?php
/**
 * Chat Bot System untuk CaFood
 * File ini akan dijalankan setiap beberapa menit (cron/interval)
 */

require_once __DIR__ . '/config/database.php';

class ChatBot {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Cek dan proses pesan yang perlu dijawab oleh bot
     */
    public function processPendingMessages() {
        // Cari pesan customer yang belum dijawab oleh manusia dan belum dijawab bot
        $stmt = $this->pdo->prepare("
            SELECT 
                cm.*,
                cr.stand_id,
                cr.user_id,
                s.stand_name,
                u.name as customer_name,
                cbs.enabled as bot_enabled,
                cbs.auto_reply_delay,
                cbs.auto_reply_template,
                cbs.greeting_message,
                cbs.business_hours_start,
                cbs.business_hours_end,
                cbs.out_of_hours_message
            FROM chat_messages cm
            JOIN chat_rooms cr ON cm.room_id = cr.id
            JOIN stands s ON cr.stand_id = s.id
            JOIN users u ON cr.user_id = u.id
            LEFT JOIN chat_bot_settings cbs ON cr.stand_id = cbs.stand_id
            LEFT JOIN chat_bot_conversations cbc ON cm.id = cbc.customer_message_id
            WHERE cm.sender_type = 'customer'
                AND cm.created_at > NOW() - INTERVAL 10 MINUTE
                AND cbc.id IS NULL
                AND (cbs.enabled = 1 OR cbs.enabled IS NULL)
            ORDER BY cm.created_at ASC
            LIMIT 10
        ");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $responses = [];
        
        foreach ($messages as $msg) {
            // Cek apakah sudah ada balasan manusia untuk pesan ini
            $hasHumanReply = $this->checkHumanReply($msg['room_id'], $msg['created_at']);
            
            if (!$hasHumanReply) {
                $response = $this->generateReply($msg);
                if ($response) {
                    $responses[] = $this->sendBotReply($msg, $response);
                }
            }
        }
        
        return $responses;
    }
    
    /**
     * Cek apakah sudah ada balasan dari manusia
     */
    private function checkHumanReply($roomId, $customerMessageTime) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total 
            FROM chat_messages 
            WHERE room_id = ? 
                AND sender_type = 'stand' 
                AND created_at > ?
        ");
        $stmt->execute([$roomId, $customerMessageTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }
    
    /**
     * Generate reply berdasarkan konteks
     */
    private function generateReply($msg) {
        $message = strtolower($msg['message']);
        $template = $msg['auto_reply_template'] ?? 'Terima kasih atas pesannya! Kami akan segera menghubungi Anda kembali.';
        $greeting = $msg['greeting_message'] ?? 'Halo! Terima kasih sudah menghubungi kami.';
        
        // Cek jam operasional
        $isBusinessHours = $this->isBusinessHours(
            $msg['business_hours_start'] ?? '08:00',
            $msg['business_hours_end'] ?? '22:00'
        );
        
        if (!$isBusinessHours) {
            return $msg['out_of_hours_message'] ?? 'Maaf, kami sedang di luar jam operasional. Kami akan merespon saat jam operasional.';
        }
        
        // Deteksi kata kunci untuk balasan spesifik
        $keywords = [
            'menu' => 'Untuk melihat menu kami, silakan klik "Menu" di bagian atas halaman. Kami memiliki berbagai pilihan makanan yang lezat!',
            'harga' => 'Untuk info harga, silakan cek menu kami. Setiap harga sudah tertera di setiap item menu.',
            'pesan' => 'Untuk memesan, silakan tambahkan menu ke keranjang dan lakukan checkout. Pesanan Anda akan segera diproses!',
            'alamat' => 'Alamat kami tertera di profil stand. Silakan cek di halaman profil stand.',
            'jam' => 'Kami buka setiap hari dari jam 08:00 - 22:00. Silakan datang atau pesan online!',
            'terima kasih' => 'Sama-sama! Senang bisa membantu. Silakan hubungi kami jika ada pertanyaan lain.',
            'makasih' => 'Sama-sama! Senang bisa membantu. Silakan hubungi kami jika ada pertanyaan lain.',
            'thanks' => 'You\'re welcome! Happy to help.',
            'buka' => 'Kami buka setiap hari dari jam 08:00 - 22:00. Silakan datang!',
            'tutup' => 'Kami tutup pada jam 22:00. Silakan datang besok!',
        ];
        
        // Cek apakah pesan mengandung kata kunci
        foreach ($keywords as $key => $reply) {
            if (strpos($message, $key) !== false) {
                return $reply;
            }
        }
        
        // Jika pesan pendek (sapaan)
        $greetings = ['halo', 'hai', 'hi', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'hello'];
        foreach ($greetings as $greet) {
            if (strpos($message, $greet) !== false && strlen($message) < 30) {
                return $greeting;
            }
        }
        
        // Default reply
        return $template;
    }
    
    /**
     * Cek apakah sekarang jam operasional
     */
    private function isBusinessHours($start, $end) {
        $now = date('H:i');
        return $now >= $start && $now <= $end;
    }
    
    /**
     * Kirim balasan bot
     */
    private function sendBotReply($msg, $replyMessage) {
        try {
            // Simpan pesan bot
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_messages (room_id, sender_id, sender_type, message, created_at) 
                VALUES (?, ?, 'stand', ?, NOW())
            ");
            $stmt->execute([$msg['room_id'], $msg['stand_id'], $replyMessage]);
            $botMessageId = $this->pdo->lastInsertId();
            
            // Update last message di room
            $stmt = $this->pdo->prepare("
                UPDATE chat_rooms SET last_message = ?, last_message_time = NOW(), updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$replyMessage, $msg['room_id']]);
            
            // Catat di tabel bot conversations
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_bot_conversations (room_id, customer_message_id, bot_message_id, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$msg['room_id'], $msg['id'], $botMessageId]);
            
            // Notifikasi ke customer
            $stmt = $this->pdo->prepare("
                INSERT INTO chat_notifications (user_id, room_id, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$msg['user_id'], $msg['room_id']]);
            
            return [
                'success' => true,
                'room_id' => $msg['room_id'],
                'customer_message' => $msg['message'],
                'bot_reply' => $replyMessage,
                'customer_name' => $msg['customer_name'],
                'stand_name' => $msg['stand_name']
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// ===== JALANKAN BOT =====
if (php_sapi_name() === 'cli' || isset($_GET['run'])) {
    $bot = new ChatBot($pdo);
    $result = $bot->processPendingMessages();
    
    if (isset($_GET['run'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'processed' => count($result),
            'results' => $result
        ]);
        exit;
    }
}
?>