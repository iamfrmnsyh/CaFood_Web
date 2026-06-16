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

// Get chat rooms for this stand
$stmt = $pdo->prepare("
    SELECT cr.*, u.name as customer_name, u.email as customer_email,
           COUNT(cn.id) as unread_count
    FROM chat_rooms cr
    JOIN users u ON cr.user_id = u.id
    LEFT JOIN chat_notifications cn ON cr.id = cn.room_id AND cn.user_id = ? AND cn.is_read = 0
    WHERE cr.stand_id = ?
    GROUP BY cr.id
    ORDER BY cr.last_message_time DESC
");
$stmt->execute([$userId, $standId]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatTime($datetime) {
    if (!$datetime) return '-';
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    return date('d/m/Y H:i', $time);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat • <?php echo htmlspecialchars($standName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; }
        
        .header {
            background: white;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .back-btn {
            background: #f1f5f9;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #e2e8f0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .room-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .room-item {
            background: white;
            border-radius: 20px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .room-item:hover {
            border-color: #6C4CF1;
            box-shadow: 0 4px 12px rgba(108,76,241,0.1);
            transform: translateY(-2px);
        }
        
        .room-item .customer-name {
            font-weight: 700;
            color: #1e293b;
        }
        
        .room-item .last-message {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        
        .room-item .last-time {
            color: #94a3b8;
            font-size: 0.7rem;
            float: right;
        }
        
        .room-item .unread-badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .header { padding: 12px 16px; }
            .header-title { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">
            <i class="fas fa-comments" style="color: #6C4CF1;"></i> Pesan Masuk
        </div>
        <a href="dashboard-stand.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    
    <div class="container">
        <?php if(empty($rooms)): ?>
            <div class="empty-state">
                <i class="fas fa-comment-dots"></i>
                <h3>Belum Ada Percakapan</h3>
                <p>Belum ada customer yang menghubungi Anda</p>
            </div>
        <?php else: ?>
            <div class="room-list">
                <?php foreach($rooms as $room): ?>
                <a href="chat_room.php?room_id=<?php echo $room['id']; ?>" class="room-item">
                    <div>
                        <span class="customer-name">
                            <i class="fas fa-user-circle" style="color: #6C4CF1;"></i>
                            <?php echo htmlspecialchars($room['customer_name']); ?>
                        </span>
                        <?php if($room['unread_count'] > 0): ?>
                            <span class="unread-badge"><?php echo $room['unread_count']; ?> baru</span>
                        <?php endif; ?>
                        <span class="last-time"><?php echo formatTime($room['last_message_time']); ?></span>
                    </div>
                    <div class="last-message">
                        <?php 
                        $lastMsg = $room['last_message'] ?? 'Belum ada pesan';
                        echo htmlspecialchars(substr($lastMsg, 0, 60)) . (strlen($lastMsg) > 60 ? '...' : '');
                        ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>