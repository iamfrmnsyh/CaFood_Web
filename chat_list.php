<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'User';

// Get all chat rooms for this user
$stmt = $pdo->prepare("
    SELECT 
        cr.*,
        s.stand_name,
        s.id as stand_id,
        s.gambarUrl as stand_image,
        COUNT(CASE WHEN cn.user_id = ? AND cn.is_read = 0 THEN 1 END) as unread_count,
        MAX(cm.created_at) as last_message_time
    FROM chat_rooms cr
    JOIN stands s ON cr.stand_id = s.id
    LEFT JOIN chat_notifications cn ON cr.id = cn.room_id AND cn.user_id = ?
    LEFT JOIN chat_messages cm ON cr.id = cm.room_id
    WHERE cr.user_id = ?
    GROUP BY cr.id
    ORDER BY cr.updated_at DESC
");
$stmt->execute([$userId, $userId, $userId]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get last message for each room
foreach ($rooms as &$room) {
    $stmt = $pdo->prepare("
        SELECT message, created_at, sender_id, sender_type 
        FROM chat_messages 
        WHERE room_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$room['id']]);
    $lastMsg = $stmt->fetch(PDO::FETCH_ASSOC);
    $room['last_message'] = $lastMsg['message'] ?? 'Belum ada pesan';
    $room['last_message_time'] = $lastMsg['created_at'] ?? $room['created_at'];
    $room['sender_type'] = $lastMsg['sender_type'] ?? '';
}

function getLastMessageTime($datetime) {
    if (!$datetime) return '-';
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    return date('d/m/Y', $time);
}

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Count total unread messages
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM chat_notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$totalUnread = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pesan • CaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f0f4f8; 
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .header-title {
            flex: 1;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .header-title i {
            margin-right: 8px;
        }
        
        .header-badge {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        /* Container */
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Chat List */
        .chat-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .chat-item {
            background: white;
            border-radius: 20px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            position: relative;
        }
        
        .chat-item:hover {
            border-color: #6C4CF1;
            box-shadow: 0 4px 20px rgba(108,76,241,0.1);
            transform: translateY(-2px);
        }
        
        .chat-item.unread {
            background: #f8f5ff;
            border-color: rgba(108,76,241,0.2);
        }
        
        /* Avatar */
        .chat-avatar {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea, #764ba2);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .chat-avatar i {
            font-size: 1.5rem;
        }
        
        /* Chat Info */
        .chat-info {
            flex: 1;
            min-width: 0;
        }
        
        .chat-name {
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .chat-name .unread-badge {
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            flex-shrink: 0;
        }
        
        .chat-last-message {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .chat-last-message .sender-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #6C4CF1;
            background: #f1f5f9;
            padding: 1px 8px;
            border-radius: 20px;
        }
        
        .chat-last-message .sender-label.stand {
            color: #f59e0b;
            background: #fef3c7;
        }
        
        .chat-time {
            font-size: 0.7rem;
            color: #94a3b8;
            flex-shrink: 0;
            text-align: right;
            align-self: flex-start;
            margin-top: 4px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
        }
        
        .empty-state .empty-icon {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2.5rem;
            color: #94a3b8;
        }
        
        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .empty-state p {
            color: #64748b;
            font-size: 0.85rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .chat-item { padding: 14px 16px; gap: 12px; }
            .chat-avatar { width: 48px; height: 48px; font-size: 1.2rem; }
            .chat-name { font-size: 0.95rem; }
            .chat-last-message { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">
            <i class="fas fa-comment-dots"></i> Pesan
        </div>
        <?php if($totalUnread > 0): ?>
            <span class="header-badge"><?php echo $totalUnread; ?> baru</span>
        <?php endif; ?>
    </div>
    
    <!-- Container -->
    <div class="container">
        <?php if(empty($rooms)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h3>Belum Ada Percakapan</h3>
                <p>Mulai chat dengan stand favoritmu</p>
                <a href="index.php" style="display: inline-block; margin-top: 16px; background: #6C4CF1; color: white; padding: 10px 24px; border-radius: 30px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-store"></i> Cari Stand
                </a>
            </div>
        <?php else: ?>
            <div class="chat-list">
                <?php foreach($rooms as $room): 
                    $isUnread = $room['unread_count'] > 0;
                    $lastMsg = $room['last_message'] ?? 'Belum ada pesan';
                    $lastTime = $room['last_message_time'] ?? $room['created_at'];
                    
                    // Cek apakah pesan terakhir dari stand atau user
                    $senderLabel = '';
                    if ($room['sender_type'] === 'stand') {
                        $senderLabel = '<span class="sender-label stand">Stand</span>';
                    } elseif ($room['sender_type'] === 'customer') {
                        $senderLabel = '<span class="sender-label">Anda</span>';
                    }
                ?>
                <a href="chat.php?stand_id=<?php echo $room['stand_id']; ?>" class="chat-item <?php echo $isUnread ? 'unread' : ''; ?>">
                    <!-- Avatar -->
                    <div class="chat-avatar">
                        <?php if(!empty($room['stand_image'])): ?>
                            <img src="<?php echo htmlspecialchars($room['stand_image']); ?>" alt="<?php echo htmlspecialchars($room['stand_name']); ?>" onerror="this.parentElement.innerHTML='<i class=\'fas fa-store\'></i>'">
                        <?php else: ?>
                            <i class="fas fa-store"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Info -->
                    <div class="chat-info">
                        <div class="chat-name">
                            <?php echo htmlspecialchars($room['stand_name']); ?>
                            <?php if($isUnread): ?>
                                <span class="unread-badge"><?php echo $room['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-last-message">
                            <?php echo $senderLabel; ?>
                            <?php echo htmlspecialchars(substr($lastMsg, 0, 50)) . (strlen($lastMsg) > 50 ? '...' : ''); ?>
                        </div>
                    </div>
                    
                    <!-- Time -->
                    <div class="chat-time">
                        <?php echo getLastMessageTime($lastTime); ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto refresh every 10 seconds to update unread count
        setInterval(function() {
            fetch('get_chat_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.unread > 0) {
                        const badge = document.querySelector('.header-badge');
                        if (badge) {
                            badge.textContent = data.unread + ' baru';
                        } else {
                            const header = document.querySelector('.header');
                            const badgeNew = document.createElement('span');
                            badgeNew.className = 'header-badge';
                            badgeNew.textContent = data.unread + ' baru';
                            header.appendChild(badgeNew);
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 10000);
    </script>
</body>
</html>