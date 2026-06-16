<?php
session_start();

// Redirect to splash screen if not seen before
if (!isset($_SESSION['splash_seen'])) {
    $_SESSION['splash_seen'] = true;
    header('Location: splash.php');
    exit;
}

// If splash already seen, continue to main home page content
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$currentUser = null;
$userRole = null;
$userName = null;

if ($isLoggedIn) {
    $currentUser = $_SESSION['user_id'];
    $userRole = $_SESSION['role'] ?? 'customer';
    $userName = $_SESSION['name'] ?? 'User';
} else {
    $userName = 'Pengunjung';
}

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$originalSearchQuery = $searchQuery;

if (!empty($searchQuery)) {
    $searchQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');
}

// Get all active stands with search filter
$sql = "SELECT * FROM stands WHERE status = 'Open'";
$params = [];

if (!empty($originalSearchQuery)) {
    $sql .= " AND (stand_name LIKE :search1 OR description LIKE :search2 OR LOWER(stand_name) LIKE LOWER(:search3) OR LOWER(description) LIKE LOWER(:search4))";
    $searchTerm = "%{$originalSearchQuery}%";
    $params[':search1'] = $searchTerm;
    $params[':search2'] = $searchTerm;
    $params[':search3'] = $searchTerm;
    $params[':search4'] = $searchTerm;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stands = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activeStandsCount = count($stands);

// Get cart count from database
$cartCount = 0;
if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$currentUser]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $result['total'] ?? 0;
}

// ===== HITUNG NOTIFIKASI CHAT UNTUK SIDEBAR =====
$chatNotifCount = 0;
if ($isLoggedIn) {
    try {
        if ($userRole === 'stand') {
            $stmt = $pdo->prepare("
                SELECT COUNT(cn.id) as total 
                FROM chat_notifications cn 
                JOIN chat_rooms cr ON cn.room_id = cr.id 
                JOIN stands s ON cr.stand_id = s.id 
                WHERE s.user_id = ? AND cn.user_id = ? AND cn.is_read = 0
            ");
            $stmt->execute([$currentUser, $currentUser]);
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(cn.id) as total 
                FROM chat_notifications cn 
                WHERE cn.user_id = ? AND cn.is_read = 0
            ");
            $stmt->execute([$currentUser]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $chatNotifCount = $result['total'] ?? 0;
    } catch (PDOException $e) {
        $chatNotifCount = 0;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Helper function to get stand image URL
function getStandImageUrl($stand) {
    $imagePath = '';
    if (isset($stand['gambar']) && !empty($stand['gambar'])) {
        $imagePath = $stand['gambar'];
    } elseif (isset($stand['gambarUrl']) && !empty($stand['gambarUrl'])) {
        $imagePath = $stand['gambarUrl'];
    } elseif (isset($stand['image']) && !empty($stand['image'])) {
        $imagePath = $stand['image'];
    } elseif (isset($stand['foto']) && !empty($stand['foto'])) {
        $imagePath = $stand['foto'];
    }
    
    if (empty($imagePath)) {
        return 'https://placehold.co/400x160/6C4CF1/FFFFFF?text=' . urlencode($stand['stand_name']);
    }
    
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }
    
    return 'uploads/stands/' . ltrim($imagePath, '/');
}

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FA;
            overflow-x: hidden;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            width: 100%;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            box-shadow: 0 4px 10px rgba(108,76,241,0.2);
        }
        
        .logo-text {
            font-size: 1.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            width: 44px;
            height: 44px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: #6C4CF1;
            text-decoration: none;
        }
        
        .cart-icon:hover {
            background: #e2e8f0;
            transform: scale(1.05);
        }
        
        .cart-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }
        
        .menu-icon {
            width: 44px;
            height: 44px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #6C4CF1;
            font-size: 1.2rem;
        }
        
        .menu-icon:hover {
            background: #e2e8f0;
            transform: scale(1.05);
        }
        
        .side-menu {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            height: 100vh;
            background: white;
            z-index: 200;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px 0 30px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        
        .side-menu.open { 
            right: 0; 
        }
        
        .side-menu-header {
            padding: 32px 24px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
        }
        
        .side-menu-header h3 { margin-bottom: 8px; font-size: 1.3rem; }
        .side-menu-header p { font-size: 0.8rem; opacity: 0.9; }
        
        .side-menu-items { flex: 1; padding: 20px 0; }
        
        .side-menu-item {
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.3s;
            color: #374151;
            border-left: 3px solid transparent;
            text-decoration: none;
            font-weight: 500;
            position: relative;
        }
        
        .side-menu-item:hover {
            background: #F3F4F6;
            color: #6C4CF1;
            padding-left: 30px;
        }
        
        .side-menu-item i { width: 24px; font-size: 1.1rem; }
        
        /* ===== BADGE NOTIFIKASI DI SIDEBAR ===== */
        .badge-notif {
            background: #ef4444;
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: auto;
            animation: pulse-badge 2s infinite;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .side-menu-footer {
            padding: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .logout-side-btn {
            width: 100%;
            padding: 12px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .logout-side-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .login-side-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .login-side-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108,76,241,0.3);
        }
        
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 199;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .menu-overlay.active { 
            opacity: 1;
            visibility: visible;
        }
        
        .hero {
            padding: 60px 20px;
            background: linear-gradient(135deg, #1A1A2E 0%, #2A2A4A 100%);
            position: relative;
            overflow: hidden;
            color: white;
            text-align: center;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle at center, rgba(108,76,241,0.15) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
        
        .hero-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .hero-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .hero-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 28px;
        }
        
        .search-bar {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }
        
        .search-bar input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: none;
            background: white;
            border-radius: 50px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #1a1a2e;
            transition: all 0.3s;
        }
        
        .search-bar input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(108,76,241,0.3);
            transform: scale(1.02);
        }
        
        .search-bar input::placeholder { color: #94a3b8; }
        
        .search-bar i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6C4CF1;
            pointer-events: none;
        }
        
        .search-clear-btn {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
            display: <?php echo !empty($originalSearchQuery) ? 'block' : 'none'; ?>;
            transition: color 0.2s;
            z-index: 10;
        }
        
        .search-clear-btn:hover {
            color: #ef4444;
        }
        
        .stands-section {
            padding: 20px;
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .section-link {
            color: #6C4CF1;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(108,76,241,0.1);
            transition: all 0.2s;
        }
        
        .section-link:hover {
            background: rgba(108,76,241,0.2);
        }
        
        .search-info {
            background: rgba(108,76,241,0.1);
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: <?php echo !empty($originalSearchQuery) ? 'flex' : 'none'; ?>;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-info-text {
            font-size: 0.85rem;
            color: #6C4CF1;
        }
        
        .search-info-text i {
            margin-right: 8px;
        }
        
        .search-info-clear {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(239,68,68,0.1);
            transition: all 0.2s;
        }
        
        .search-info-clear:hover {
            background: rgba(239,68,68,0.2);
        }
        
        .stand-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .stand-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(108,76,241,0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            backdrop-filter: blur(10px);
        }
        
        .stand-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(108,76,241,0.2);
            border-color: rgba(108,76,241,0.3);
        }
        
        .stand-image {
            height: 160px;
            overflow: hidden;
            background: #f1f5f9;
        }
        
        .stand-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .stand-card:hover .stand-image img { transform: scale(1.05); }
        
        .stand-info { padding: 16px; }
        
        .stand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        
        .stand-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .stand-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            color: #64748b;
        }
        
        .rating { color: #f59e0b; }
        .status-open { color: #10b981; }
        .status-closed { color: #ef4444; }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
            grid-column: 1 / -1;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .fab {
            position: fixed;
            bottom: 30px;
            right: 16px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(108,76,241,0.4);
            transition: all 0.2s;
            z-index: 99;
        }
        
        .fab:hover { transform: scale(1.05); }
        
        /* Highlight untuk kata kunci */
        mark {
            background: rgba(108,76,241,0.2);
            color: #6C4CF1;
            padding: 0 2px;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .stand-grid { grid-template-columns: 1fr; gap: 16px; }
            .hero-title { font-size: 1.4rem; }
            .side-menu { width: 260px; right: -260px; }
            .header-content { padding: 0 16px; }
            .logo-text { font-size: 1.2rem; }
            .logo-icon { width: 36px; height: 36px; font-size: 1rem; }
            .cart-icon, .menu-icon { width: 38px; height: 38px; }
        }
    </style>
</head>
<body>
    <div class="menu-overlay" id="menuOverlay"></div>
    
    <div class="side-menu" id="sideMenu">
        <div class="side-menu-header">
            <h3><i class="fas fa-user-circle"></i> Menu</h3>
            <p><?php echo $isLoggedIn ? "Halo, " . htmlspecialchars($userName) . "!" : "Selamat Datang!"; ?></p>
        </div>
        <div class="side-menu-items">
            <a href="index.php" class="side-menu-item">
                <i class="fas fa-home"></i> <span>Beranda</span>
            </a>
            
            <?php if($isLoggedIn): ?>
            <!-- ===== MENU PESANAN SAYA ===== -->
            <a href="status-pesanan.php" class="side-menu-item">
                <i class="fas fa-clipboard-list"></i> <span>Pesanan Saya</span>
            </a>
            
            <!-- ===== MENU KERANJANG ===== -->
            <a href="keranjang.php" class="side-menu-item">
                <i class="fas fa-shopping-cart"></i> <span>Keranjang</span>
            </a>
            
            <!-- ===== MENU PROFIL ===== -->
            <a href="profile.php" class="side-menu-item">
                <i class="fas fa-user"></i> <span>Profil Saya</span>
            </a>
            
            <!-- ===== MENU CHAT DENGAN BADGE NOTIFIKASI ===== -->
            <?php 
            // Tentukan link chat berdasarkan role
            if ($userRole == 'stand') {
                $chatLink = 'chat_stand.php';
            } elseif ($userRole == 'admin') {
                $chatLink = 'chat_admin.php';
            } else {
                $chatLink = 'chat_list.php';
            }
            ?>
            <a href="<?php echo $chatLink; ?>" class="side-menu-item">
                <i class="fas fa-comment-dots"></i> 
                <span>Pesan</span>
                <?php if($chatNotifCount > 0): ?>
                    <span class="badge-notif"><?php echo $chatNotifCount > 9 ? '9+' : $chatNotifCount; ?></span>
                <?php endif; ?>
            </a>
            
            <!-- ===== MENU DASHBOARD ===== -->
            <?php if($userRole == 'stand'): ?>
            <a href="dashboard-stand.php" class="side-menu-item">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard Stand</span>
            </a>
            <?php elseif($userRole == 'admin'): ?>
            <a href="dashboard-admin.php" class="side-menu-item">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard Admin</span>
            </a>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- ===== MENU UNTUK TAMU ===== -->
            <a href="login.php" class="side-menu-item">
                <i class="fas fa-sign-in-alt"></i> <span>Login</span>
            </a>
            <a href="register.php" class="side-menu-item">
                <i class="fas fa-user-plus"></i> <span>Daftar</span>
            </a>
            <?php endif; ?>
        </div>
        <div class="side-menu-footer">
            <?php if($isLoggedIn): ?>
                <a href="?logout=1" class="logout-side-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="login-side-btn">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="header">
        <div class="header-content">
            <div class="logo" onclick="window.location.href='index.php'">
                <div class="logo-icon"><i class="fas fa-utensils"></i></div>
                <div class="logo-text">CaFood</div>
            </div>
            <div class="header-right">
                <a href="keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a>
                <div class="menu-icon" id="menuToggleBtn">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="hero">
        <div class="hero-badge">
            <i class="fas fa-fire"></i> <span><?php echo $activeStandsCount; ?></span> Stand Aktif
        </div>
        <h1 class="hero-title" id="realtimeGreeting">Selamat Datang!</h1>
        <p class="hero-subtitle">Jelajahi berbagai makanan lezat dari stand terbaik</p>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari stand atau makanan favorit..." 
                   value="<?php echo htmlspecialchars($originalSearchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                   autofocus>
            <button class="search-clear-btn" id="searchClearBtn" onclick="clearSearch()">
                <i class="fas fa-times-circle"></i>
            </button>
        </div>
    </div>
    
    <div class="stands-section">
        <div class="section-header">
            <h2 class="section-title">🍽️ Stand Makanan</h2>
        </div>
        
        <?php if(!empty($originalSearchQuery)): ?>
        <div class="search-info" id="searchInfo">
            <div class="search-info-text">
                <i class="fas fa-search"></i> 
                Menampilkan hasil pencarian untuk: "<strong><?php echo htmlspecialchars($originalSearchQuery); ?></strong>"
            </div>
            <a href="index.php" class="search-info-clear">
                <i class="fas fa-times"></i> Hapus Filter
            </a>
        </div>
        <?php endif; ?>
        
        <div class="stand-grid" id="standGrid">
            <?php if(empty($stands)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <?php echo !empty($originalSearchQuery) ? '🔍' : '🍽️'; ?>
                    </div>
                    <div>
                        <?php if(!empty($originalSearchQuery)): ?>
                            Tidak ada stand ditemukan untuk "<strong><?php echo htmlspecialchars($originalSearchQuery); ?></strong>"
                        <?php else: ?>
                            Belum ada stand yang tersedia
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($originalSearchQuery)): ?>
                    <div style="font-size:0.75rem; margin-top:12px;">
                        Coba kata kunci lain atau 
                        <a href="index.php" style="color:#6C4CF1; text-decoration:none;">lihat semua stand</a>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach($stands as $stand): ?>
                <div class="stand-card" onclick="goToStand(<?php echo $stand['id']; ?>)">
                    <div class="stand-image">
                        <img src="<?php echo getStandImageUrl($stand); ?>" 
                             alt="<?php echo htmlspecialchars($stand['stand_name']); ?>"
                             loading="lazy"
                             onerror="this.src='https://placehold.co/400x160/6C4CF1/FFFFFF?text=<?php echo urlencode($stand['stand_name']); ?>'">
                    </div>
                    <div class="stand-info">
                        <div class="stand-name"><?php echo htmlspecialchars($stand['stand_name']); ?></div>
                        <div class="stand-desc"><?php echo htmlspecialchars(substr($stand['description'] ?? 'Delicious food available', 0, 100)); ?></div>
                        <div class="stand-meta">
                            <span class="meta-item rating"><i class="fas fa-star"></i> <?php echo number_format($stand['rating'] ?? 4.5, 1); ?></span>
                            <span class="meta-item"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($stand['estimasiWaktu'] ?? '15-30 min'); ?></span>
                            <span class="meta-item"><i class="fas fa-truck"></i> Free Delivery</span>
                            <span class="meta-item <?php echo ($stand['status'] ?? 'Open') == 'Open' ? 'status-open' : 'status-closed'; ?>">
                                <i class="fas fa-circle"></i> <?php echo $stand['status'] ?? 'Open'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="fab" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script>
        // DOM Elements
        const sideMenu = document.getElementById('sideMenu');
        const overlay = document.getElementById('menuOverlay');
        const menuToggleBtn = document.getElementById('menuToggleBtn');
        const searchInput = document.getElementById('searchInput');
        const searchClearBtn = document.getElementById('searchClearBtn');
        
        // ===== SIDEBAR FUNCTIONS =====
        function openSidebar() {
            sideMenu.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeSidebar() {
            sideMenu.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        function toggleSidebar() {
            if (sideMenu.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }
        
        if (menuToggleBtn) {
            menuToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                closeSidebar();
            });
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sideMenu.classList.contains('open')) {
                closeSidebar();
            }
            // Shortcut Ctrl+K atau Cmd+K untuk fokus ke search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
        
        // ===== SEARCH FUNCTIONS =====
        let searchTimeout;
        let isNavigating = false;
        
        function performSearch(query, shouldRefocus = true) {
            if (isNavigating) return;
            
            const currentQuery = query.trim();
            const currentUrl = window.location.href;
            const hasSearchParam = currentUrl.includes('?search=');
            
            let needRedirect = false;
            let newUrl = '';
            
            if (currentQuery.length > 0) {
                if (!hasSearchParam || !currentUrl.includes(encodeURIComponent(currentQuery))) {
                    needRedirect = true;
                    newUrl = `index.php?search=${encodeURIComponent(currentQuery)}`;
                }
            } else if (currentQuery.length === 0 && hasSearchParam) {
                needRedirect = true;
                newUrl = 'index.php';
            }
            
            if (needRedirect) {
                isNavigating = true;
                window.location.href = newUrl;
                setTimeout(() => {
                    isNavigating = false;
                }, 100);
            }
        }
        
        function clearSearch() {
            if (searchInput) {
                searchInput.value = '';
                if (searchClearBtn) searchClearBtn.style.display = 'none';
                
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    urlParams.delete('search');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.location.href = newUrl;
                }
            }
        }
        
        // ===== SEARCH INPUT HANDLING =====
        if (searchInput) {
            function toggleClearButton() {
                if (searchClearBtn) {
                    searchClearBtn.style.display = searchInput.value.length > 0 ? 'block' : 'none';
                }
            }
            
            toggleClearButton();
            
            searchInput.addEventListener('input', function(e) {
                toggleClearButton();
                clearTimeout(searchTimeout);
                const query = this.value;
                searchTimeout = setTimeout(() => {
                    performSearch(query, true);
                }, 800);
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    performSearch(this.value, true);
                }
            });
            
            // Pastikan input tetap fokus
            window.addEventListener('load', function() {
                if (searchInput) {
                    searchInput.focus();
                    if (searchInput.value.length > 0) {
                        const len = searchInput.value.length;
                        searchInput.setSelectionRange(len, len);
                    }
                }
            });
        }
        
        // ===== NAVIGATION =====
        function goToStand(standId) {
            window.location.href = `menu-stand.php?id=${standId}`;
        }
        
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // ===== GREETING =====
        function updateGreeting() {
            const hour = new Date().getHours();
            let greeting = "";
            let greetingSub = "";
            
            if (hour >= 5 && hour < 11) {
                greeting = "Selamat Pagi! ☀️";
                greetingSub = "Apa Sarapanmu Hari Ini?";
            } else if (hour >= 11 && hour < 15) {
                greeting = "Selamat Siang! 🌤️";
                greetingSub = "Waktunya Makan Siang?";
            } else if (hour >= 15 && hour < 19) {
                greeting = "Selamat Sore! 🌅";
                greetingSub = "Camilan Sore?";
            } else {
                greeting = "Selamat Malam! 🌙";
                greetingSub = "Ngidam Larut Malam?";
            }
            
            const greetingEl = document.getElementById('realtimeGreeting');
            if (greetingEl) {
                greetingEl.innerHTML = greeting + "<br><span style='font-size:0.9rem; opacity:0.9;'>" + greetingSub + "</span>";
            }
        }
        
        updateGreeting();
        setInterval(updateGreeting, 60000);
        
        // ===== HIGHLIGHT SEARCH TERM =====
        <?php if(!empty($originalSearchQuery)): ?>
        function highlightSearchTerm() {
            const searchTerm = "<?php echo addslashes($originalSearchQuery); ?>".toLowerCase();
            const standNames = document.querySelectorAll('.stand-name');
            const standDescs = document.querySelectorAll('.stand-desc');
            
            function highlightText(element, term) {
                const text = element.textContent;
                if (text.toLowerCase().includes(term)) {
                    const regex = new RegExp(`(${term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    element.innerHTML = text.replace(regex, '<mark>$1</mark>');
                }
            }
            
            standNames.forEach(name => highlightText(name, searchTerm));
            standDescs.forEach(desc => highlightText(desc, searchTerm));
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', highlightSearchTerm);
        } else {
            highlightSearchTerm();
        }
        <?php endif; ?>
        
        // ===== PREVENT FORM SUBMISSION =====
        const searchBar = document.querySelector('.search-bar');
        if (searchBar) {
            searchBar.addEventListener('submit', function(e) {
                e.preventDefault();
                return false;
            });
        }
    </script>
</body>
</html>