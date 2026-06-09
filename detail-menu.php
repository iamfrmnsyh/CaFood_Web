<?php
session_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Get stand ID from URL
$standId = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;

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

// Get menus for this stand
$stmt = $pdo->prepare("SELECT * FROM menus WHERE stand_id = ? AND available = 'Tersedia' ORDER BY created_at DESC");
$stmt->execute([$standId]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart items from cookie/session
$cart = [];
if (isset($_COOKIE['cart_' . $standId])) {
    $cart = json_decode($_COOKIE['cart_' . $standId], true);
    if (!is_array($cart)) $cart = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $menuId = (int)$_POST['menu_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($menuId > 0 && $quantity > 0) {
        // Find menu item
        $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ? AND stand_id = ? AND available = 'Tersedia'");
        $stmt->execute([$menuId, $standId]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($menu) {
            if (isset($cart[$menuId])) {
                $cart[$menuId]['quantity'] += $quantity;
            } else {
                $cart[$menuId] = [
                    'id' => $menu['id'],
                    'name' => $menu['name'],
                    'price' => $menu['price'],
                    'image' => $menu['image'],
                    'quantity' => $quantity
                ];
            }
            
            // Save cart to cookie (expires in 30 days)
            setcookie('cart_' . $standId, json_encode($cart), time() + (86400 * 30), "/");
            setcookie('current_stand', json_encode(['id' => $stand['id'], 'name' => $stand['stand_name']]), time() + (86400 * 30), "/");
            
            $success = "Menu berhasil ditambahkan ke keranjang!";
        }
    }
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $menuId = (int)$_POST['menu_id'];
    $change = (int)$_POST['change'];
    
    if (isset($cart[$menuId])) {
        $newQuantity = $cart[$menuId]['quantity'] + $change;
        if ($newQuantity > 0) {
            $cart[$menuId]['quantity'] = $newQuantity;
        } else {
            unset($cart[$menuId]);
        }
        
        setcookie('cart_' . $standId, json_encode($cart), time() + (86400 * 30), "/");
    }
    
    header("Location: stand-detail.php?stand_id=" . $standId);
    exit;
}

// Calculate cart totals
$totalItems = array_sum(array_column($cart, 'quantity'));
$totalPrice = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart));

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Get rating (default 4.5 if not set)
$rating = $stand['rating'] ?? 4.5;
$deliveryTime = $stand['estimasiWaktu'] ?? '15-30 min';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • <?php echo htmlspecialchars($stand['stand_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f0ff;
            padding-bottom: 100px;
        }

        .stand-header {
            position: relative;
            height: 280px;
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            overflow: hidden;
        }

        .stand-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
            text-decoration: none;
        }

        .cart-icon-header {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
            text-decoration: none;
            position: relative;
        }

        .cart-icon-header:hover, .back-btn:hover {
            background: rgba(0,0,0,0.7);
            transform: scale(1.05);
        }

        .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .stand-info-card {
            background: white;
            border-radius: 36px 36px 0 0;
            margin-top: -30px;
            position: relative;
            z-index: 5;
            padding: 24px 24px 16px 24px;
        }

        .stand-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e1a3a;
            margin-bottom: 8px;
        }

        .stand-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0eaff;
        }

        .stand-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f5f3ff;
            padding: 6px 12px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6d28d9;
        }

        .menu-section {
            background: white;
            padding: 8px 24px 24px 24px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e1a3a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .menu-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 32px;
        }

        .menu-item-card {
            background: #faf8ff;
            border-radius: 20px;
            padding: 16px;
            border: 1px solid #f0eaff;
            transition: all 0.2s;
            display: flex;
            gap: 16px;
        }

        .menu-item-card:hover {
            border-color: #c4b5fd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .menu-image-container {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, #e2d9ff, #f0eaff);
        }

        .menu-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-info {
            flex: 1;
        }

        .menu-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .menu-item-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e1a3a;
        }

        .menu-item-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: #6d28d9;
        }

        .menu-item-desc {
            font-size: 0.8rem;
            color: #7b6cb0;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .menu-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 6px 12px;
            border-radius: 40px;
            border: 1px solid #e2d9ff;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f0eaff;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #6d28d9;
            color: white;
        }

        .qty-value {
            font-weight: 600;
            min-width: 28px;
            text-align: center;
        }

        .add-item-btn {
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .add-item-btn:hover {
            transform: scale(1.02);
        }

        .cart-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #f0eaff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
        }

        .cart-total {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .cart-total-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #6d28d9;
        }

        .cart-items-count {
            background: #f0eaff;
            padding: 6px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6d28d9;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .view-cart-btn {
            background: white;
            color: #6d28d9;
            border: 1px solid #6d28d9;
            padding: 12px 24px;
            border-radius: 60px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e1a3a;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.8rem;
            z-index: 1000;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        .alert-success {
            background: #D1FAE5;
            color: #059669;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .stand-header { height: 200px; }
            .menu-item-card { flex-direction: column; }
            .menu-image-container { width: 100%; height: 150px; }
            .cart-summary { flex-direction: column; }
            .checkout-btn, .view-cart-btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="stand-header">
        <a href="javascript:history.back()" class="back-btn">←</a>
        <a href="keranjang.php" class="cart-icon-header">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count-badge"><?php echo $totalItems; ?></span>
        </a>
        <?php if($stand['gambarUrl']): ?>
            <img class="stand-cover" src="<?php echo htmlspecialchars($stand['gambarUrl']); ?>" alt="Stand Cover">
        <?php else: ?>
            <div class="stand-cover" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6d28d9, #a855f7);">
                <i class="fas fa-store" style="font-size: 5rem; color: white; opacity: 0.5;"></i>
            </div>
        <?php endif; ?>
    </div>

    <div class="stand-info-card">
        <h1 class="stand-title"><?php echo htmlspecialchars($stand['stand_name']); ?></h1>
        <div class="stand-meta">
            <span class="stand-rating"><i class="fas fa-star"></i> <?php echo $rating; ?></span>
            <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($deliveryTime); ?></span>
            <span><i class="fas fa-motorcycle"></i> Free Delivery</span>
        </div>
        <p><?php echo htmlspecialchars($stand['description'] ?: 'Tidak ada deskripsi'); ?></p>
    </div>

    <div class="menu-section">
        <div class="section-title">
            <i class="fas fa-utensils" style="color:#6d28d9;"></i> Menu List
        </div>
        
        <?php if(isset($success)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="menu-list">
            <?php if(empty($menus)): ?>
                <div style="text-align: center; padding: 40px;">Belum ada menu tersedia</div>
            <?php else: ?>
                <?php foreach($menus as $menu): ?>
                <?php $qty = isset($cart[$menu['id']]) ? $cart[$menu['id']]['quantity'] : 0; ?>
                <div class="menu-item-card">
                    <div class="menu-image-container">
                        <?php if($menu['image']): ?>
                            <img class="menu-image" src="<?php echo htmlspecialchars($menu['image']); ?>" alt="<?php echo htmlspecialchars($menu['name']); ?>" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:2rem;\'>🍽️</div>'">
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 2rem;">🍽️</div>
                        <?php endif; ?>
                    </div>
                    <div class="menu-info">
                        <div class="menu-item-header">
                            <span class="menu-item-name"><?php echo htmlspecialchars($menu['name']); ?></span>
                            <span class="menu-item-price"><?php echo formatRupiah($menu['price']); ?></span>
                        </div>
                        <div class="menu-item-desc"><?php echo htmlspecialchars($menu['description'] ?: 'Tidak ada deskripsi'); ?></div>
                        <div class="menu-item-actions">
                            <div class="quantity-selector">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                    <input type="hidden" name="change" value="-1">
                                    <button type="submit" name="update_quantity" class="qty-btn">-</button>
                                </form>
                                <span class="qty-value"><?php echo $qty; ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                    <input type="hidden" name="change" value="1">
                                    <button type="submit" name="update_quantity" class="qty-btn">+</button>
                                </form>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="add-item-btn">
                                    <i class="fas fa-plus"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="cart-summary">
        <div class="cart-total">
            <span class="cart-total-value"><?php echo formatRupiah($totalPrice); ?></span>
            <span class="cart-items-count"><?php echo $totalItems; ?> item<?php echo $totalItems > 1 ? 's' : ''; ?></span>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="keranjang.php" class="view-cart-btn">View Cart</a>
            <a href="checkout.php" class="checkout-btn">Checkout</a>
        </div>
    </div>
</body>
</html>