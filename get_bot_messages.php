<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$roomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($roomId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

// Ambil pesan bot baru
$stmt = $pdo->prepare("
    SELECT cm.*, u.name as sender_name, 
           CASE WHEN cbc.id IS NOT NULL THEN 1 ELSE 0 END as is_bot
    FROM chat_messages cm
    LEFT JOIN users u ON cm.sender_id = u.id
    LEFT JOIN chat_bot_conversations cbc ON cm.id = cbc.bot_message_id
    WHERE cm.room_id = ? AND cm.id > ? AND cm.sender_type = 'stand'
    ORDER BY cm.created_at ASC
");
$stmt->execute([$roomId, $lastId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'messages' => $messages
]);
?>