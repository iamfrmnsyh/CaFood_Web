<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($roomId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

// ===== GET NEW MESSAGES WITH BOT DETECTION =====
$stmt = $pdo->prepare("
    SELECT 
        cm.*, 
        u.name as sender_name,
        CASE WHEN cbc.id IS NOT NULL THEN 1 ELSE 0 END as is_bot,
        CASE 
            WHEN cm.sender_type = 'stand' AND cbc.id IS NULL THEN 'human'
            WHEN cm.sender_type = 'stand' AND cbc.id IS NOT NULL THEN 'bot'
            ELSE cm.sender_type
        END as sender_type_display
    FROM chat_messages cm 
    LEFT JOIN users u ON cm.sender_id = u.id 
    LEFT JOIN chat_bot_conversations cbc ON cm.id = cbc.bot_message_id
    WHERE cm.room_id = ? AND cm.id > ? 
    ORDER BY cm.created_at ASC
");
$stmt->execute([$roomId, $lastId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read for the current user
if (!empty($messages)) {
    $stmt = $pdo->prepare("
        UPDATE chat_notifications SET is_read = 1 
        WHERE room_id = ? AND user_id = ?
    ");
    $stmt->execute([$roomId, $userId]);
}

// ===== GET UNREAD COUNT FOR THIS ROOM =====
$stmt = $pdo->prepare("
    SELECT COUNT(*) as unread_count 
    FROM chat_notifications 
    WHERE room_id = ? AND user_id = ? AND is_read = 0
");
$stmt->execute([$roomId, $userId]);
$unreadResult = $stmt->fetch(PDO::FETCH_ASSOC);
$unreadCount = $unreadResult['unread_count'] ?? 0;

// ===== GET TOTAL UNREAD FOR ALL ROOMS (untuk badge) =====
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_unread 
    FROM chat_notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->execute([$userId]);
$totalUnreadResult = $stmt->fetch(PDO::FETCH_ASSOC);
$totalUnread = $totalUnreadResult['total_unread'] ?? 0;

// ===== CUSTOMER TYPING INDICATOR (untuk stand owner) =====
// Cek apakah user sedang mengetik (hanya untuk stand owner yang melihat chat)
// Ini bisa diimplementasikan jika diperlukan

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'unread_count' => $unreadCount,
    'total_unread' => $totalUnread,
    'last_id' => !empty($messages) ? end($messages)['id'] : $lastId,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>