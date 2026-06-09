<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$currentUser = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$stmt->execute([$currentUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    // If user record not found, logout for safety
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get cart count (for header display)
$cartCount = 0;
$stmtCart = $pdo->prepare('SELECT SUM(quantity) as total FROM cart WHERE user_id = ?');
$stmtCart->execute([$currentUser]);
$result = $stmtCart->fetch(PDO::FETCH_ASSOC);
$cartCount = $result['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {margin:0;padding:0;box-sizing:border-box;}
        body {font-family:'Inter',sans-serif;background:#F8F9FA;overflow-x:hidden;}
        .header {background:rgba(255,255,255,0.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);padding:16px 20px;position:fixed;top:0;width:100%;z-index:100;border-bottom:1px solid rgba(255,255,255,0.3);box-shadow:0 4px 20px rgba(0,0,0,0.05);}
        .header-content {display:flex;justify-content:space-between;align-items:center;max-width:1200px;margin:0 auto;}
        .logo {display:flex;align-items:center;gap:10px;cursor:pointer;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,#6C4CF1,#8B5CF6);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:white;}
        .logo-text{font-size:1.3rem;font-weight:800;background:linear-gradient(135deg,#6C4CF1,#8B5CF6);-webkit-background-clip:text;background-clip:text;color:transparent;}
        .header-right {display:flex;align-items:center;gap:12px;}
        .cart-icon {position:relative;width:42px;height:42px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6C4CF1;transition:all 0.2s;cursor:pointer;}
        .cart-count {position:absolute;top:-4px;right:-4px;background:#ef4444;color:white;border-radius:50%;width:20px;height:20px;font-size:0.65rem;display:flex;align-items:center;justify-content:center;}
        .menu-icon {width:42px;height:42px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6C4CF1;cursor:pointer;transition:all 0.2s;}
        .profile-card{background:white;padding:32px;border-radius:12px;max-width:500px;margin:120px auto;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
        .profile-card h2{margin-bottom:16px;color:#1a1a2e;}
        .profile-item{margin:8px 0;color:#374151;}
        .profile-item span{font-weight:600;color:#1a1a2e;}
        .logout-btn{display:inline-block;margin-top:20px;padding:10px 20px;background:#ef4444;color:white;border:none;border-radius:6px;text-decoration:none;cursor:pointer;}
        .side-menu {position:fixed;top:0;right:-300px;width:280px;height:100vh;background:white;z-index:200;transition:right 0.3s ease;box-shadow:-4px 0 20px rgba(0,0,0,0.1);display:flex;flex-direction:column;}
        .side-menu.open {right:0;}
        .menu-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:199;display:none;}
        .menu-overlay.active {display:block;}
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo" onclick="window.location.href='index.php'">
                <div class="logo-icon"><i class="fas fa-utensils"></i></div>
                <div class="logo-text">CaFood</div>
            </div>
            <div class="header-right">
                <a href="keranjang.php" class="cart-icon"><i class="fas fa-shopping-bag"></i><span class="cart-count"><?php echo $cartCount; ?></span></a>
                <div class="menu-icon" onclick="toggleMenu(event)"><i class="fas fa-bars"></i></div>
            </div>
        </div>
    </div>
    <div class="menu-overlay" id="menuOverlay" onclick="toggleMenu(event)"></div>
    <div class="side-menu" id="sideMenu">
        <div class="side-menu-header"><h3><i class="fas fa-user-circle"></i> Menu</h3><p>Halo, <?php echo htmlspecialchars($user['name']); ?>!</p></div>
        <div class="side-menu-items">
            <a href="index.php" class="side-menu-item"><i class="fas fa-home"></i><span>Beranda</span></a>
            <?php if($user['role'] == 'stand'): ?>
                <a href="dashboard-stand.php" class="side-menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard Stand</span></a>
            <?php elseif($user['role'] == 'admin'): ?>
                <a href="dashboard-admin.php" class="side-menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard Admin</span></a>
            <?php else: ?>
                <a href="status-pesanan.php" class="side-menu-item"><i class="fas fa-clipboard-list"></i><span>Pesanan Saya</span></a>
                <a href="profile.php" class="side-menu-item"><i class="fas fa-user"></i><span>Profil Saya</span></a>
            <?php endif; ?>
        </div>
        <div class="side-menu-footer">
            <a href="?logout=1" class="logout-side-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="profile-card">
        <h2>Profil Pengguna</h2>
        <div class="profile-item"><span>Nama:</span> <?php echo htmlspecialchars($user['name']); ?></div>
        <div class="profile-item"><span>Email:</span> <?php echo htmlspecialchars($user['email']); ?></div>
        <div class="profile-item"><span>Peran:</span> <?php echo htmlspecialchars($user['role']); ?></div>
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>
    <script>
        function toggleMenu(e){if(e) e.stopPropagation();
            const sideMenu=document.getElementById('sideMenu');
            const overlay=document.getElementById('menuOverlay');
            sideMenu.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sideMenu.classList.contains('open') ? 'hidden' : '';
        }
        document.addEventListener('click',function(event){
            const sideMenu=document.getElementById('sideMenu');
            const menuIcon=document.querySelector('.menu-icon');
            if(sideMenu && sideMenu.classList.contains('open')){
                if(!sideMenu.contains(event.target) && !menuIcon.contains(event.target)){
                    toggleMenu(event);
                }
            }
        });
    </script>
</body>
</html>
