<?php
session_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Customer';

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quantity
    if (isset($_POST['update_quantity'])) {
        $menuId = (int)$_POST['menu_id'];
        $change = (int)$_POST['change'];
        
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND menu_id = ?");
        $stmt->execute([$userId, $menuId]);
        $cartItem = $stmt->fetch();
        
        if ($cartItem) {
            $newQuantity = $cartItem['quantity'] + $change;
            if ($newQuantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$newQuantity, $userId, $menuId]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND menu_id = ?");
                $stmt->execute([$userId, $menuId]);
            }
        }
        
        header("Location: keranjang.php");
        exit;
    }
    
    // Remove item
    if (isset($_POST['remove_item'])) {
        $menuId = (int)$_POST['menu_id'];
        
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND menu_id = ?");
        $stmt->execute([$userId, $menuId]);
        
        header("Location: keranjang.php");
        exit;
    }
    
    // Clear cart
    if (isset($_POST['clear_cart'])) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        header("Location: keranjang.php");
        exit;
    }
}

// Get cart items from database
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.menu_id,
        c.quantity,
        m.name as menu_name,
        m.price,
        m.image,
        m.description,
        s.stand_name,
        s.id as stand_id
    FROM cart c
    JOIN menus m ON c.menu_id = m.id
    JOIN stands s ON m.stand_id = s.id
    WHERE c.user_id = ?
    ORDER BY s.stand_name, c.created_at DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryFee = 0; // Free delivery
$tax = 0; // No tax
$total = $subtotal + $deliveryFee + $tax;

// Group items by stand
$groupedItems = [];
foreach ($cartItems as $item) {
    $standId = $item['stand_id'];
    if (!isset($groupedItems[$standId])) {
        $groupedItems[$standId] = [
            'stand_name' => $item['stand_name'],
            'items' => [],
            'subtotal' => 0
        ];
    }
    $groupedItems[$standId]['items'][] = $item;
    $groupedItems[$standId]['subtotal'] += $item['price'] * $item['quantity'];
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
    <title>CaFood • Keranjang Belanja</title>
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
            background: #f8fafc;
            padding-bottom: 120px;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .back-btn {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #1e293b;
            font-size: 1.2rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #e2e8f0;
            transform: scale(1.05);
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .clear-cart-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 0.8rem;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 30px;
            transition: all 0.2s;
        }
        
        .clear-cart-btn:hover {
            background: #fee2e2;
        }
        
        /* Main Container */
        .cart-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Stand Group */
        .stand-group {
            background: white;
            border-radius: 24px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }
        
        .stand-header {
            background: #f8fafc;
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stand-name {
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stand-name i {
            color: #6C4CF1;
        }
        
        /* Cart Items */
        .cart-items {
            padding: 0 16px;
        }
        
        .cart-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        /* Item Image */
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #cbd5e1;
        }
        
        /* Item Details */
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
            font-size: 1rem;
        }
        
        .item-stand {
            font-size: 0.7rem;
            color: #6C4CF1;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .item-price {
            font-weight: 600;
            color: #080808;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        /* Item Actions */
        .item-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 40px;
        }
        
        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: white;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .qty-btn:hover {
            background: #6C4CF1;
            color: white;
            transform: scale(1.05);
        }
        
        .qty-value {
            font-weight: 600;
            min-width: 28px;
            text-align: center;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 0.7rem;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 30px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .remove-btn:hover {
            background: #fee2e2;
        }
        
        .item-subtotal {
            text-align: right;
            min-width: 85px;
        }
        
        .subtotal-label {
            font-size: 0.65rem;
            color: #94a3b8;
        }
        
        .subtotal-value {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.9rem;
        }
        
        /* Stand Subtotal */
        .stand-subtotal {
            background: #f8fafc;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
            font-weight: 600;
            color: #1e293b;
        }
        
        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }
        
        .summary-row.total {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 800;
            font-size: 1.1rem;
            color: #1e293b;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108,76,241,0.4);
        }
        
        /* Empty State */
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        
        .empty-cart-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .empty-cart-text {
            color: #64748b;
            margin-bottom: 24px;
        }
        
        .shop-now-btn {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .shop-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108,76,241,0.3);
        }
        
        /* Warning */
        .warning-message {
            background: #fef3c7;
            color: #6C4CF1;
            padding: 12px 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .loading-spinner {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
        }
        
        .loading-spinner i {
            font-size: 3rem;
            color: #6C4CF1;
            margin-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .cart-container {
                padding: 16px;
            }
            
            .cart-item {
                flex-wrap: wrap;
            }
            
            .item-subtotal {
                text-align: left;
                margin-left: 96px;
            }
            
            .stand-header {
                padding: 12px 16px;
            }
            
            .cart-summary {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-pulse"></i>
            <div>Memproses pesanan...</div>
        </div>
    </div>
    
    <div class="header">
        <div class="header-content">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">
                <i class="fas fa-shopping-cart" style="color: #6C4CF1;"></i> Keranjang Belanja
            </div>
            <?php if(!empty($cartItems)): ?>
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang?')">
                <button type="submit" name="clear_cart" class="clear-cart-btn">
                    <i class="fas fa-trash-alt"></i> Kosongkan
                </button>
            </form>
            <?php else: ?>
            <div style="width: 40px;"></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="cart-container">
        <?php if(empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="empty-cart-title">Keranjang Belanja Kosong</div>
                <div class="empty-cart-text">Yuk, mulai pesan makanan favoritmu!</div>
                <a href="index.php" class="shop-now-btn">
                    <i class="fas fa-store"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <!-- Warning for multiple stands -->
            <?php if(count($groupedItems) > 1): ?>
            <div class="warning-message">
                <i class="fas fa-info-circle"></i>
                <span>Pesanan Anda berasal dari beberapa stand. Pesanan akan diproses secara terpisah sesuai stand masing-masing.</span>
            </div>
            <?php endif; ?>
            
            <!-- Cart Items Grouped by Stand -->
            <?php foreach($groupedItems as $standId => $group): ?>
            <div class="stand-group">
                <div class="stand-header">
                    <div class="stand-name">
                        <i class="fas fa-store"></i> <?php echo htmlspecialchars($group['stand_name']); ?>
                    </div>
                </div>
                <div class="cart-items">
                    <?php foreach($group['items'] as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <?php if(!empty($item['image']) && $item['image'] != ''): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['menu_name']); ?>" onerror="this.parentElement.innerHTML='<div class=\'item-image-placeholder\'>🍽️</div>'">
                            <?php else: ?>
                                <div class="item-image-placeholder">🍽️</div>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['menu_name']); ?></div>
                            <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                            <div class="item-actions">
                                <div class="quantity-control">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">
                                        <input type="hidden" name="change" value="-1">
                                        <button type="submit" name="update_quantity" class="qty-btn">-</button>
                                    </form>
                                    <span class="qty-value"><?php echo $item['quantity']; ?></span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">
                                        <input type="hidden" name="change" value="1">
                                        <button type="submit" name="update_quantity" class="qty-btn">+</button>
                                    </form>
                                </div>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                    <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="item-subtotal">
                            <div class="subtotal-label">Subtotal</div>
                            <div class="subtotal-value"><?php echo formatRupiah($item['price'] * $item['quantity']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="stand-subtotal">
                    <span>Subtotal <?php echo htmlspecialchars($group['stand_name']); ?></span>
                    <span><?php echo formatRupiah($group['subtotal']); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatRupiah($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <span>Gratis</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatRupiah($total); ?></span>
                </div>
                <!-- PERUBAHAN: Tombol checkout sekarang mengarahkan ke proses checkout -->
                <button type="button" class="checkout-btn" id="checkoutBtn">
                    <i class="fas fa-arrow-right"></i> Lanjutkan ke Pembayaran
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        const checkoutBtn = document.getElementById('checkoutBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', async function() {
                // Tampilkan loading
                loadingOverlay.classList.add('active');
                
                try {
                    // Kirim request ke proses_checkout.php
                    const response = await fetch('proses_checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=checkout'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Redirect ke halaman pembayaran
                        window.location.href = `payment.php?order_id=${result.order_id}`;
                    } else {
                        alert(result.message || 'Terjadi kesalahan. Silakan coba lagi.');
                        loadingOverlay.classList.remove('active');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                    loadingOverlay.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>