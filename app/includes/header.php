<?php
$user = $_SESSION['user'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'customer';
$userName = $_SESSION['name'] ?? 'User';

// Hitung notifikasi chat yang belum dibaca
$chatNotifCount = 0;
if ($isLoggedIn) {
    try {
        require_once __DIR__ . '/../config/database.php';
        
        if ($userRole === 'stand') {
            // Untuk stand owner: hitung notifikasi dari customer
            $stmt = $pdo->prepare("
                SELECT COUNT(cn.id) as total 
                FROM chat_notifications cn 
                JOIN chat_rooms cr ON cn.room_id = cr.id 
                JOIN stands s ON cr.stand_id = s.id 
                WHERE s.user_id = ? AND cn.user_id = ? AND cn.is_read = 0
            ");
            $stmt->execute([$userId, $userId]);
        } else {
            // Untuk customer: hitung notifikasi dari stand
            $stmt = $pdo->prepare("
                SELECT COUNT(cn.id) as total 
                FROM chat_notifications cn 
                WHERE cn.user_id = ? AND cn.is_read = 0
            ");
            $stmt->execute([$userId]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $chatNotifCount = $result['total'] ?? 0;
    } catch (PDOException $e) {
        $chatNotifCount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
        }
        
        header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: #6C4CF1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .nav-link {
            text-decoration: none;
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 30px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #f1f5f9;
            color: #6C4CF1;
        }
        
        .nav-link i {
            margin-right: 6px;
        }
        
        /* ===== CHAT NOTIFICATION ===== */
        .chat-notif {
            position: relative;
            text-decoration: none;
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 30px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .chat-notif:hover {
            background: #f1f5f9;
            color: #6C4CF1;
        }
        
        .chat-notif i {
            font-size: 1.1rem;
        }
        
        /* Badge notifikasi di icon chat */
        .chat-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 0.6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            border: 2px solid white;
            animation: pulse-badge 2s infinite;
        }
        
        /* Badge di sidebar menu */
        .badge-notif {
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: auto;
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .user-name {
            color: #374151;
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        .logout-btn {
            border: none;
            background: #6C4CF1;
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #5a3dd1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108,76,241,0.3);
        }
        
        .login-btn, .register-btn {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 999px;
            transition: all 0.3s;
        }
        
        .login-btn {
            color: #6C4CF1;
            border: 1px solid #6C4CF1;
        }
        
        .login-btn:hover {
            background: #6C4CF1;
            color: white;
        }
        
        .register-btn {
            background: #6C4CF1;
            color: white;
        }
        
        .register-btn:hover {
            background: #5a3dd1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108,76,241,0.3);
        }
        
        /* Mobile */
        @media (max-width: 768px) {
            .nav-container {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .logo-icon {
                width: 32px;
                height: 32px;
                font-size: 0.85rem;
            }
            
            .nav-right {
                gap: 8px;
                flex-wrap: wrap;
            }
            
            .nav-link, .chat-notif {
                font-size: 0.75rem;
                padding: 6px 10px;
            }
            
            .user-name {
                font-size: 0.75rem;
            }
            
            .logout-btn {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            
            .login-btn, .register-btn {
                font-size: 0.75rem;
                padding: 6px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .nav-container {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            
            .nav-right {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<header>
    <nav class="nav-container">
        <a href="/index.php" class="logo">
            <span class="logo-icon"><i class="fas fa-utensils"></i></span>
            CaFood
        </a>
        <div class="nav-right">
            <?php if (!$isLoggedIn): ?>
                <a href="/public/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="/public/register.php" class="register-btn">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php else: ?>
                <span class="user-name">
                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                </span>
                
                <?php if ($userRole === 'admin'): ?>
                    <a href="/public/dashboard-admin.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Admin
                    </a>
                <?php elseif ($userRole === 'stand'): ?>
                    <a href="/public/dashboard-stand.php" class="nav-link">
                        <i class="fas fa-store"></i> Stand
                    </a>
                <?php endif; ?>
                
                <a href="/public/keranjang.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
                
                <!-- ===== CHAT NOTIFICATION ===== -->
                <?php 
                // Tentukan link chat berdasarkan role
                if ($userRole === 'stand') {
                    $chatLink = '/public/chat_stand.php';
                } elseif ($userRole === 'admin') {
                    $chatLink = '/public/chat_admin.php';
                } else {
                    $chatLink = '/public/chat_list.php';
                }
                ?>
                <a href="<?= $chatLink ?>" class="chat-notif" title="Pesan">
                    <i class="fas fa-comment-dots"></i>
                    Chat
                    <?php if ($chatNotifCount > 0): ?>
                        <span class="chat-badge"><?= $chatNotifCount > 9 ? '9+' : $chatNotifCount ?></span>
                    <?php endif; ?>
                </a>
                
                <form method="POST" action="/public/api/auth/logout.php" style="display:inline;">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- Script untuk auto refresh notifikasi -->
<script>
    // Auto refresh notifikasi chat setiap 10 detik
    function updateChatNotification() {
        fetch('/public/get_chat_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.chat-badge');
                    const chatNotifLink = document.querySelector('.chat-notif');
                    
                    if (data.unread > 0) {
                        if (badge) {
                            badge.textContent = data.unread > 9 ? '9+' : data.unread;
                            badge.style.display = 'flex';
                        } else if (chatNotifLink) {
                            // Buat badge baru jika belum ada
                            const newBadge = document.createElement('span');
                            newBadge.className = 'chat-badge';
                            newBadge.textContent = data.unread > 9 ? '9+' : data.unread;
                            chatNotifLink.appendChild(newBadge);
                        }
                    } else {
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching chat notification:', error));
    }
    
    // Jalankan pertama kali dan setiap 10 detik
    setTimeout(updateChatNotification, 1000);
    setInterval(updateChatNotification, 10000);
</script>