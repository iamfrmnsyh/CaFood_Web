<?php
/**
 * chat_bot_api.php - API untuk bot chat cepat
 * Dipanggil langsung dari frontend setelah user mengirim pesan
 */

session_start();
require_once __DIR__ . '/config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');
$roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$standId = isset($_POST['stand_id']) ? (int)$_POST['stand_id'] : 0;

if (empty($message) || $roomId <= 0 || $standId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Cek apakah sudah ada balasan dari stand owner dalam 10 detik terakhir
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM chat_messages 
    WHERE room_id = ? AND sender_type = 'stand' 
    AND created_at > DATE_SUB(NOW(), INTERVAL 10 SECOND)
");
$stmt->execute([$roomId]);
$recentReply = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika sudah ada balasan dari stand, jangan kirim bot
if ($recentReply['total'] > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Already replied by stand', 'skip' => true]);
    exit;
}

// Generate bot reply
$botReply = generateBotReply($message);

// Simpan pesan bot ke database
try {
    // Cek apakah bot sudah membalas pesan ini (hindari duplikat)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM chat_messages 
        WHERE room_id = ? AND message = ? AND sender_type = 'stand'
        AND created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
    ");
    $stmt->execute([$roomId, $botReply]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists['total'] > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bot already replied', 'skip' => true]);
        exit;
    }
    
    // Insert pesan bot
    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (room_id, sender_id, sender_type, message, created_at) 
        VALUES (?, ?, 'stand', ?, NOW())
    ");
    $stmt->execute([$roomId, $standId, $botReply]);
    $botMessageId = $pdo->lastInsertId();
    
    // Update last_message di room
    $stmt = $pdo->prepare("
        UPDATE chat_rooms SET last_message = ?, last_message_time = NOW(), updated_at = NOW() WHERE id = ?
    ");
    $stmt->execute([$botReply, $roomId]);
    
    // Catat di chat_bot_conversations
    $stmt = $pdo->prepare("
        INSERT INTO chat_bot_conversations (room_id, customer_message_id, bot_message_id, created_at) 
        VALUES (?, 
            (SELECT id FROM chat_messages WHERE room_id = ? AND sender_type = 'customer' ORDER BY created_at DESC LIMIT 1),
            ?, 
            NOW()
        )
    ");
    $stmt->execute([$roomId, $roomId, $botMessageId]);
    
    // Notifikasi ke customer
    $stmt = $pdo->prepare("
        SELECT user_id FROM chat_rooms WHERE id = ?
    ");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($room) {
        $stmt = $pdo->prepare("
            INSERT INTO chat_notifications (user_id, room_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$room['user_id'], $roomId]);
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Bot replied',
        'reply' => $botReply,
        'message_id' => $botMessageId
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ===== FUNGSI GENERATE BOT REPLY =====
function generateBotReply($message) {
    $msg = strtolower($message);
    
    // Keywords untuk balasan cepat
    $keywords = [
        'menu' => 'Untuk melihat menu kami, silakan klik tombol "Menu" di atas. Kami punya banyak pilihan makanan lezat! 🍽️',
        'harga' => 'Untuk info harga, silakan cek menu kami ya. Setiap harga sudah tertera di setiap item menu. 💰',
        'pesan' => 'Silakan tambahkan menu ke keranjang dan lakukan checkout. Pesanan Anda akan segera diproses! 🛒',
        'alamat' => 'Alamat kami tertera di profil stand. Silakan cek di halaman profil stand. 📍',
        'jam' => 'Kami buka setiap hari dari jam 08:00 - 22:00 WIB. Silakan datang atau pesan online! 🕐',
        'terima kasih' => 'Sama-sama! Senang bisa membantu. Ada yang bisa kami bantu lagi? 😊',
        'makasih' => 'Sama-sama! Senang bisa membantu. Ada yang bisa kami bantu lagi? 😊',
        'thanks' => 'You\'re welcome! Happy to help. 😊',
        'buka' => 'Kami buka setiap hari dari jam 08:00 - 22:00 WIB. Silakan datang! 🏪',
        'tutup' => 'Kami tutup pada jam 22:00 WIB. Silakan datang besok! 🌙',
        'beli' => 'Silakan pilih menu yang Anda inginkan, tambahkan ke keranjang, dan checkout. Mudah kok! 🛒',
        'order' => 'Silakan pilih menu yang Anda inginkan, tambahkan ke keranjang, dan checkout. Mudah kok! 🛒',
        'makan' => 'Kami menyediakan berbagai makanan lezat! Cek menu kami ya. 🍽️',
        'minum' => 'Kami juga menyediakan berbagai minuman segar! Cek menu kami. 🥤',
    ];
    
    // Cek keywords
    foreach ($keywords as $key => $reply) {
        if (strpos($msg, $key) !== false) {
            return $reply;
        }
    }
    
    // Sapaan
    $greetings = ['halo', 'hai', 'hi', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'hello', 'assalamualaikum'];
    foreach ($greetings as $greet) {
        if (strpos($msg, $greet) !== false && strlen($msg) < 30) {
            return 'Halo! Selamat datang di stand kami. Ada yang bisa kami bantu? 😊';
        }
    }
    
    // Default reply (cepat)
    return 'Terima kasih atas pesannya! Tim kami akan segera merespon. Ada yang bisa kami bantu? 😊';
}