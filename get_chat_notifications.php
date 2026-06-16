<?php
session_start();
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'unread' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'customer';

try {
    if ($userRole === 'stand') {
        // Untuk stand owner
        $stmt = $pdo->prepare("
            SELECT COUNT(cn.id) as total 
            FROM chat_notifications cn 
            JOIN chat_rooms cr ON cn.room_id = cr.id 
            JOIN stands s ON cr.stand_id = s.id 
            WHERE s.user_id = ? AND cn.user_id = ? AND cn.is_read = 0
        ");
        $stmt->execute([$userId, $userId]);
    } else {
        // Untuk customer
        $stmt = $pdo->prepare("
            SELECT COUNT(cn.id) as total 
            FROM chat_notifications cn 
            WHERE cn.user_id = ? AND cn.is_read = 0
        ");
        $stmt->execute([$userId]);
    }
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread = $result['total'] ?? 0;
    
    echo json_encode(['success' => true, 'unread' => $unread]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'unread' => 0]);
}
?>