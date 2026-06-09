<?php
session_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Helper function to get stand image URL
function getStandImageUrl($imagePath) {
    if (empty($imagePath)) {
        return 'assets/images/stand-placeholder.jpg';
    }
    
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }
    
    $cleanPath = ltrim($imagePath, '/');
    $fullPath = 'uploads/stands/' . $cleanPath;
    
    if (file_exists(__DIR__ . '/' . $fullPath)) {
        return $fullPath;
    }
    
    return $fullPath;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Get stand ID from URL
$standId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($standId <= 0) {
    header('Location: index.php');
    exit;
}

// Get stand data
$stmt = $pdo->prepare("SELECT * FROM stands WHERE id = ? AND status = 'Open'");
$stmt->execute([$standId]);
$stand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stand) {
    header('Location: index.php');
    exit;
}

// Get stand image URL
$standImageUrl = getStandImageUrl($stand['gambarUrl'] ?? '');

// Get menus for this stand
$stmt = $pdo->prepare("SELECT * FROM menus WHERE stand_id = ? AND available = 'Tersedia' ORDER BY created_at DESC");
$stmt->execute([$standId]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get fresh cart data for display
$cart = [];
if ($isLoggedIn) {
    $stmt = $pdo->prepare(
        "SELECT c.*, m.name, m.price, m.image FROM cart c JOIN menus m ON c.menu_id = m.id WHERE c.user_id = ?"
    );
    $stmt->execute([$userId]);
    $dbCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dbCartItems as $item) {
        $cart[$item['menu_id']] = [
            'id' => $item['menu_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'],
            'standId' => $standId,
            'standName' => $stand['stand_name']
        ];
    }
}

$totalItems = $isLoggedIn ? array_sum(array_column($cart, 'quantity')) : 0;
$totalPrice = $isLoggedIn ? array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart)) : 0;

// Handle AJAX POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    // Check login
    if (!$isLoggedIn) {
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu', 'redirect' => 'login.php']);
        exit;
    }
    
    // Add to cart
    if (isset($_POST['add_to_cart'])) {
        $menuId = (int)$_POST['menu_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($menuId > 0 && $quantity > 0) {
            // Check if menu exists
            $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ? AND stand_id = ? AND available = 'Tersedia'");
            $stmt->execute([$menuId, $standId]);
            $menu = $stmt->fetch();
            
            if ($menu) {
                // Check if already in cart
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$userId, $menuId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $newQuantity = $existing['quantity'] + $quantity;
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND menu_id = ?");
                    $stmt->execute([$newQuantity, $userId, $menuId]);
                    $responseMessage = "Jumlah diperbarui";
                    $updatedQty = $newQuantity;
                } else {
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, menu_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$userId, $menuId, $quantity]);
                    $responseMessage = "Menu ditambahkan ke keranjang";
                    $updatedQty = $quantity;
                }
                
                // Get updated cart totals
                $stmt = $pdo->prepare("
                    SELECT c.*, m.price 
                    FROM cart c 
                    JOIN menus m ON c.menu_id = m.id 
                    WHERE c.user_id = ?
                ");
                $stmt->execute([$userId]);
                $dbCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $totalQty = 0;
                $totalPriceSum = 0;
                foreach ($dbCartItems as $item) {
                    $totalQty += $item['quantity'];
                    $totalPriceSum += $item['price'] * $item['quantity'];
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $responseMessage,
                    'totalItems' => $totalQty,
                    'totalPrice' => $totalPriceSum,
                    'updatedQuantity' => $updatedQty,
                    'menuId' => $menuId
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
    }
    
    // Update quantity (from +/- buttons)
    if (isset($_POST['update_quantity'])) {
        $menuId = (int)$_POST['menu_id'];
        $change = (int)$_POST['change'];
        
        // Check if item exists in cart
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND menu_id = ?");
        $stmt->execute([$userId, $menuId]);
        $cartItem = $stmt->fetch();
        
        if (!$cartItem && $change < 0) {
            // Trying to decrease non-existent item - ignore
            echo json_encode(['success' => true, 'skipUpdate' => true]);
            exit;
        }
        
        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $change;
            if ($newQuantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$newQuantity, $userId, $menuId]);
                $updatedQty = $newQuantity;
                $responseMessage = $change > 0 ? "Jumlah ditambah" : "Jumlah dikurangi";
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$userId, $menuId]);
                $updatedQty = 0;
                $responseMessage = "Item dihapus dari keranjang";
            }
            
            // Get updated cart totals
            $stmt = $pdo->prepare("
                SELECT c.*, m.price 
                FROM cart c 
                JOIN menus m ON c.menu_id = m.id 
                WHERE c.user_id = ?
            ");
            $stmt->execute([$userId]);
            $dbCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalQty = 0;
            $totalPriceSum = 0;
            foreach ($dbCartItems as $item) {
                $totalQty += $item['quantity'];
                $totalPriceSum += $item['price'] * $item['quantity'];
            }
            
            echo json_encode([
                'success' => true,
                'message' => $responseMessage,
                'totalItems' => $totalQty,
                'totalPrice' => $totalPriceSum,
                'updatedQuantity' => $updatedQty,
                'menuId' => $menuId
            ]);
            exit;
        } else {
            echo json_encode(['success' => true, 'skipUpdate' => true]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
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
    <title>CaFood • <?php echo htmlspecialchars($stand['stand_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; padding-bottom: 100px; }
        
        .stand-header { 
            position: relative; 
            height: 250px; 
            background: linear-gradient(135deg, rgba(108,76,241,0.7), rgba(139,92,246,0.7)), url('<?php echo htmlspecialchars($standImageUrl); ?>');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }
        
        .back-btn { 
            position: absolute; 
            top: 20px; 
            left: 20px; 
            width: 40px; 
            height: 40px; 
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-size: 1.2rem; 
            cursor: pointer; 
            z-index: 10; 
            text-decoration: none; 
            border: 1px solid rgba(255,255,255,0.3); 
            transition: all 0.3s; 
        }
        .back-btn:hover { background: rgba(0,0,0,0.6); transform: scale(1.05); }
        
        .cart-icon-header { 
            position: absolute; 
            top: 20px; 
            right: 20px; 
            width: 40px; 
            height: 40px; 
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-size: 1.2rem; 
            cursor: pointer; 
            z-index: 10; 
            text-decoration: none; 
            border: 1px solid rgba(255,255,255,0.3); 
            transition: all 0.3s; 
        }
        .cart-icon-header:hover { background: rgba(0,0,0,0.6); transform: scale(1.05); }
        
        .cart-count-badge { 
            position: absolute; 
            top: -8px; 
            right: -8px; 
            background: #ef4444; 
            color: white; 
            border-radius: 50%; 
            width: 20px; 
            height: 20px; 
            font-size: 0.7rem; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border: 2px solid white; 
            transition: all 0.2s;
        }
        
        .stand-info-card { 
            background: white; 
            border-radius: 25px 25px 0 0; 
            margin-top: -30px; 
            position: relative; 
            z-index: 5; 
            padding: 25px 20px 20px; 
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05); 
        }
        
        .stand-name { 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: #1a1a2e; 
            margin-bottom: 6px; 
        }
        
        .stand-meta { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            flex-wrap: wrap; 
            margin-bottom: 10px; 
        }
        
        .stand-rating { 
            display: flex; 
            align-items: center; 
            gap: 4px; 
            background: #f1f5f9; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            color: #f59e0b; 
        }
        
        .stand-desc { 
            color: #64748b; 
            font-size: 0.85rem; 
            line-height: 1.5; 
        }
        
        .menu-section { 
            background: white; 
            padding: 0 15px 20px; 
        }
        
        .section-title { 
            font-size: 1.1rem; 
            font-weight: 700; 
            color: #1a1a2e; 
            margin-bottom: 16px; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        .section-title i { 
            font-size: 1.2rem; 
        }
        
        .menu-list { 
            display: flex; 
            flex-direction: column; 
            gap: 16px; 
        }
        
        .menu-item { 
            display: flex; 
            gap: 16px; 
            padding: 16px; 
            background: white; 
            border-radius: 20px; 
            transition: all 0.3s; 
            border: 1px solid #e9ecef; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.03); 
        }
        
        .menu-item:hover { 
            border-color: #6C4CF1; 
            box-shadow: 0 8px 20px rgba(108,76,241,0.1); 
            transform: translateY(-2px); 
        }
        
        .menu-image { 
            width: 85px; 
            height: 85px; 
            border-radius: 16px; 
            overflow: hidden; 
            flex-shrink: 0; 
            background: #e2e8f0; 
        }
        
        .menu-image img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .menu-info { 
            flex: 1; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .menu-details {
            flex: 1;
        }
        
        .menu-name { 
            font-size: 1.05rem; 
            font-weight: 700; 
            color: #1a1a2e; 
            margin-bottom: 5px; 
        }
        
        .menu-desc { 
            font-size: 0.75rem; 
            color: #64748b; 
            margin-bottom: 6px; 
            line-height: 1.4; 
        }
        
        .menu-price { 
            font-size: 0.95rem; 
            font-weight: 700; 
            color: #6C4CF1; 
            margin-bottom: 10px; 
        }
        
        .menu-actions { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            flex-shrink: 0;
        }
        
        .qty-selector { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            background: #f8f9fa; 
            padding: 4px 8px; 
            border-radius: 30px; 
            border: 1px solid #e2e8f0; 
        }
        
        .qty-btn { 
            width: 28px; 
            height: 28px; 
            border-radius: 50%; 
            border: none; 
            background: white; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 1rem; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
            transition: all 0.2s; 
        }
        
        .qty-btn:hover { 
            background: #6C4CF1; 
            color: white; 
            transform: scale(1.05); 
        }
        
        .qty-btn:disabled { 
            opacity: 0.5; 
            cursor: not-allowed; 
            transform: none; 
        }
        
        .qty-value { 
            font-size: 0.9rem; 
            font-weight: 600; 
            min-width: 28px; 
            text-align: center; 
        }
        
        .add-btn { 
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6); 
            color: white; 
            border: none; 
            padding: 8px 18px; 
            border-radius: 30px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s; 
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .add-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(108,76,241,0.4); 
        }
        
        .cart-summary { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            background: rgba(255,255,255,0.95); 
            backdrop-filter: blur(10px);
            border-top: 1px solid #e9ecef; 
            padding: 14px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            z-index: 100; 
        }
        
        .cart-total { 
            display: flex; 
            align-items: baseline; 
            gap: 8px; 
            flex-wrap: wrap; 
        }
        
        .cart-total-label { 
            font-size: 0.75rem; 
            color: #64748b; 
        }
        
        .cart-total-value { 
            font-size: 1.2rem; 
            font-weight: 800; 
            color: #6C4CF1; 
        }
        
        .cart-items-count { 
            background: #f1f5f9; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.7rem; 
            font-weight: 600; 
        }
        
        .view-cart-btn { 
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6); 
            color: white; 
            border: none; 
            padding: 10px 24px; 
            border-radius: 30px; 
            font-weight: 600; 
            font-size: 0.8rem; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            transition: all 0.3s; 
        }
        
        .view-cart-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(108,76,241,0.3); 
        }
        
        .toast { 
            position: fixed; 
            bottom: 100px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: #1a1a2e; 
            color: white; 
            padding: 10px 20px; 
            border-radius: 30px; 
            font-size: 0.85rem; 
            z-index: 1000; 
            animation: slideUp 0.3s ease; 
        }
        
        .toast.success { background: #10B981; }
        .toast.error { background: #EF4444; }
        
        @keyframes slideUp { 
            from { opacity: 0; transform: translateX(-50%) translateY(20px); } 
            to { opacity: 1; transform: translateX(-50%) translateY(0); } 
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 3rem;
            opacity: 0.5;
            margin-bottom: 12px;
            display: block;
        }
        
        @media (max-width: 768px) { 
            .stand-header { height: 180px; } 
            .stand-name { font-size: 1.2rem; } 
            .back-btn, .cart-icon-header { top: 12px; width: 36px; height: 36px; font-size: 1rem; } 
            .back-btn { left: 12px; } 
            .cart-icon-header { right: 12px; }
            .menu-image { width: 70px; height: 70px; }
            .menu-info { flex-direction: column; align-items: flex-start; gap: 12px; }
            .menu-actions { margin-top: 8px; }
        }
        
        @media (max-width: 480px) {
            .menu-actions { flex-wrap: wrap; }
            .cart-summary { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="stand-header">
        <a href="javascript:history.back()" class="back-btn">←</a>
        <a href="keranjang.php" class="cart-icon-header">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count-badge" id="cartCountBadge"><?php echo $totalItems; ?></span>
        </a>
    </div>
    
    <div class="stand-info-card">
        <h1 class="stand-name"><?php echo htmlspecialchars($stand['stand_name']); ?></h1>
        <div class="stand-meta">
            <span class="stand-rating"><i class="fas fa-star"></i> <?php echo number_format($stand['rating'] ?? 4.5, 1); ?></span>
            <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($stand['estimasiWaktu'] ?? '15-30 min'); ?></span>
            <span><i class="fas fa-motorcycle"></i> Free Delivery</span>
            <span style="color: <?php echo ($stand['status'] ?? 'Open') == 'Open' ? '#10b981' : '#ef4444'; ?>">
                <i class="fas fa-circle"></i> <?php echo $stand['status'] ?? 'Open'; ?>
            </span>
        </div>
        <p class="stand-desc"><?php echo htmlspecialchars($stand['description'] ?? 'Tidak ada deskripsi'); ?></p>
    </div>
    
    <div class="menu-section">
        <div class="section-title">
            <i class="fas fa-utensils" style="color:#6C4CF1;"></i> Daftar Menu
        </div>
        
        <div class="menu-list">
            <?php if(empty($menus)): ?>
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    Belum ada menu tersedia
                </div>
            <?php else: ?>
                <?php foreach($menus as $menu): ?>
                <?php $qty = isset($cart[$menu['id']]) ? $cart[$menu['id']]['quantity'] : 0; ?>
                <div class="menu-item" data-menu-id="<?php echo $menu['id']; ?>">
                    <div class="menu-image">
                        <?php
                            $imgUrl = $menu['image'] ?? 'https://placehold.co/85x85/6C4CF1/FFFFFF?text=' . rawurlencode($menu['name']);
                        ?>
                        <img src="<?php echo htmlspecialchars($imgUrl); ?>"
                             alt="<?php echo htmlspecialchars($menu['name']); ?>"
                             onerror="this.src='https://placehold.co/85x85/6C4CF1/FFFFFF?text=' + encodeURIComponent('<?php echo $menu['name']; ?>')">
                    </div>
                    <div class="menu-info">
                        <div class="menu-details">
                            <div class="menu-name"><?php echo htmlspecialchars($menu['name']); ?></div>
                            <div class="menu-desc"><?php echo htmlspecialchars($menu['description'] ?? 'Tidak ada deskripsi'); ?></div>
                            <div class="menu-price"><?php echo formatRupiah($menu['price']); ?></div>
                        </div>
                        <div class="menu-actions">
                            <div class="qty-selector">
                                <button type="button" class="qty-btn minus-btn" data-menu-id="<?php echo $menu['id']; ?>" <?php echo $qty == 0 ? 'disabled' : ''; ?>>
                                    −
                                </button>
                                <span class="qty-value" id="qty-<?php echo $menu['id']; ?>"><?php echo $qty; ?></span>
                                <button type="button" class="qty-btn plus-btn" data-menu-id="<?php echo $menu['id']; ?>">
                                    +
                                </button>
                            </div>
                            <button type="button" class="add-btn" data-menu-id="<?php echo $menu['id']; ?>">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="cart-summary">
        <div class="cart-total">
            <span class="cart-total-label">Total:</span>
            <span class="cart-total-value" id="cartTotalPrice"><?php echo formatRupiah($totalPrice); ?></span>
            <?php if($totalItems > 0): ?>
            <span class="cart-items-count" id="cartItemsCount"><?php echo $totalItems; ?> item<?php echo $totalItems > 1 ? 's' : ''; ?></span>
            <?php else: ?>
            <span class="cart-items-count" id="cartItemsCount" style="display: none;">0 item</span>
            <?php endif; ?>
        </div>
        <a href="keranjang.php" class="view-cart-btn">
            <?php echo $totalItems > 0 ? 'Checkout' : 'Lihat Keranjang'; ?> <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <script>
        // Show toast notification
        function showToast(message, isError = false) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = `toast ${isError ? 'error' : 'success'}`;
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        // Format Rupiah
        function formatRupiah(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        }
        
        // Update UI after cart change
        function updateCartUI(menuId, newQuantity, totalItems, totalPrice) {
            // Update quantity display for specific menu
            const qtyElement = document.getElementById(`qty-${menuId}`);
            if (qtyElement) {
                qtyElement.textContent = newQuantity;
            }
            
            // Update minus button state
            const menuItem = document.querySelector(`.menu-item[data-menu-id="${menuId}"]`);
            if (menuItem) {
                const minusBtn = menuItem.querySelector('.minus-btn');
                if (minusBtn) {
                    if (newQuantity <= 0) {
                        minusBtn.disabled = true;
                    } else {
                        minusBtn.disabled = false;
                    }
                }
            }
            
            // Update cart badge
            const cartBadge = document.getElementById('cartCountBadge');
            if (cartBadge) {
                cartBadge.textContent = totalItems;
                if (totalItems === 0) {
                    cartBadge.style.display = 'none';
                } else {
                    cartBadge.style.display = 'flex';
                }
            }
            
            // Update total price
            const totalPriceElement = document.getElementById('cartTotalPrice');
            if (totalPriceElement) {
                totalPriceElement.textContent = formatRupiah(totalPrice);
            }
            
            // Update items count
            const itemsCountElement = document.getElementById('cartItemsCount');
            if (itemsCountElement) {
                if (totalItems > 0) {
                    itemsCountElement.textContent = totalItems + ' item' + (totalItems > 1 ? 's' : '');
                    itemsCountElement.style.display = 'inline-block';
                } else {
                    itemsCountElement.style.display = 'none';
                }
            }
            
            // Update checkout button text
            const checkoutBtn = document.querySelector('.view-cart-btn');
            if (checkoutBtn) {
                checkoutBtn.innerHTML = (totalItems > 0 ? 'Checkout' : 'Lihat Keranjang') + ' <i class="fas fa-arrow-right"></i>';
            }
        }
        
        // Send AJAX request
        async function sendRequest(formData) {
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error:', error);
                return { success: false, message: 'Network error: ' + error.message };
            }
        }
        
        // Handle add to cart
        async function addToCart(menuId) {
            const formData = new FormData();
            formData.append('menu_id', menuId);
            formData.append('quantity', '1');
            formData.append('add_to_cart', '1');
            
            const result = await sendRequest(formData);
            
            if (result.success) {
                updateCartUI(result.menuId, result.updatedQuantity, result.totalItems, result.totalPrice);
                showToast(result.message);
            } else if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                showToast(result.message || 'Gagal menambahkan ke keranjang', true);
            }
        }
        
        // Handle quantity update (plus/minus)
        async function updateQuantity(menuId, change) {
            const formData = new FormData();
            formData.append('menu_id', menuId);
            formData.append('change', change);
            formData.append('update_quantity', '1');
            
            const result = await sendRequest(formData);
            
            if (result.success) {
                if (!result.skipUpdate) {
                    updateCartUI(menuId, result.updatedQuantity, result.totalItems, result.totalPrice);
                    if (result.message) {
                        showToast(result.message);
                    }
                }
            } else if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                showToast(result.message || 'Gagal mengupdate keranjang', true);
            }
        }
        
        // Event listeners for plus buttons
        document.querySelectorAll('.plus-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const menuId = this.getAttribute('data-menu-id');
                updateQuantity(menuId, 1);
            });
        });
        
        // Event listeners for minus buttons
        document.querySelectorAll('.minus-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const menuId = this.getAttribute('data-menu-id');
                updateQuantity(menuId, -1);
            });
        });
        
        // Event listeners for add buttons
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const menuId = this.getAttribute('data-menu-id');
                addToCart(menuId);
            });
        });
    </script>
</body>
</html>