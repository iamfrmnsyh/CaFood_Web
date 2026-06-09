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
$userName = $_SESSION['name'] ?? '';
$userPhone = $_SESSION['phone'] ?? '';

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $customerName = trim($_POST['customer_name']);
    $customerPhone = trim($_POST['customer_phone']);
    $tableNumber = trim($_POST['table_number']) ?: null;
    $notes = trim($_POST['order_notes']) ?: null;
    $paymentMethod = $_POST['payment_method'];
    
    if (empty($customerName) || empty($customerPhone)) {
        $error = "Mohon isi nama dan nomor telepon Anda!";
    } else {
        try {
            // Get cart items
            $stmt = $pdo->prepare("
                SELECT 
                    c.menu_id,
                    m.name,
                    m.price,
                    c.quantity,
                    m.stand_id,
                    s.stand_name
                FROM cart c
                JOIN menus m ON c.menu_id = m.id
                JOIN stands s ON m.stand_id = s.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cartItems)) {
                $error = "Keranjang belanja kosong!";
            } else {
                $total = array_sum(array_map(function($item) {
                    return $item['price'] * $item['quantity'];
                }, $cartItems));
                
                $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(100, 999);
                $queueNumber = rand(1, 50);
                $estimatedTime = 15;
                $paymentNames = [
                    'cash' => 'Tunai',
                    'qris' => 'QRIS'
                ];
                
                $pdo->beginTransaction();
                
                // Insert order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (
                        order_number, queue_number, customer_name, customer_phone, 
                        table_number, notes, stand_id, stand_name, total, 
                        payment_method, estimated_time, user_id, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $standId = $cartItems[0]['stand_id'] ?? null;
                $standName = $cartItems[0]['stand_name'] ?? null;
                
                $stmt->execute([
                    $orderNumber,
                    $queueNumber,
                    $customerName,
                    $customerPhone,
                    $tableNumber,
                    $notes,
                    $standId,
                    $standName,
                    $total,
                    $paymentNames[$paymentMethod],
                    $estimatedTime,
                    $userId,
                    'pending'
                ]);
                
                $orderId = $pdo->lastInsertId();
                
                // Insert order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, menu_id, menu_name, quantity, price, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($cartItems as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $stmt->execute([
                        $orderId,
                        $item['menu_id'],
                        $item['name'],
                        $item['quantity'],
                        $item['price'],
                        $subtotal
                    ]);
                }
                
                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$userId]);
                
                $pdo->commit();
                
                // Store order in session for status page
                $_SESSION['last_order'] = [
                    'order_number' => $orderNumber,
                    'queue_number' => $queueNumber,
                    'total' => $total,
                    'estimated_time' => $estimatedTime
                ];
                
                $message = "Pesanan berhasil dibuat!";
                header("refresh:2;url=status-pesanan.php");
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Gagal membuat pesanan: " . $e->getMessage();
        }
    }
}

// Get cart items for display
$cartItems = [];
$total = 0;

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.menu_id as id,
            m.name,
            m.price,
            m.image,
            c.quantity,
            m.stand_id as standId,
            s.stand_name as standName
        FROM cart c
        JOIN menus m ON c.menu_id = m.id
        JOIN stands s ON m.stand_id = s.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cartItems));
} catch(PDOException $e) {
    $error = "Gagal memuat keranjang: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            padding-bottom: 40px;
        }
        
        .header {
            background: white;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e9ecef;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            gap: 16px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .back-btn {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6C4CF1;
            text-decoration: none;
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-section {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: #1a1a2e;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6C4CF1;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .payment-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .payment-option:hover {
            border-color: #6C4CF1;
            background: #f8f9fa;
        }
        
        .payment-option.selected {
            border-color: #6C4CF1;
            background: #f0f4ff;
        }
        
        .payment-option input {
            display: none;
        }
        
        .payment-icon {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .payment-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .payment-desc {
            font-size: 0.65rem;
            color: #64748b;
        }
        
        .qris-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .qris-modal-content {
            background: white;
            border-radius: 28px;
            padding: 28px;
            width: 90%;
            max-width: 380px;
            text-align: center;
        }
        
        .qris-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1a1a2e;
        }
        
        .qris-qr-code {
            width: 220px;
            height: 220px;
            margin: 0 auto 20px;
            background: #f1f5f9;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .qris-qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .qris-amount {
            font-size: 1.3rem;
            font-weight: 800;
            color: #6C4CF1;
            margin-bottom: 8px;
        }
        
        .qris-instruction {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .qris-close-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 20px;
        }
        
        .order-items {
            margin-bottom: 16px;
            max-height: 250px;
            overflow-y: auto;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row.total {
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .confirm-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .confirm-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #059669;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #6B7280;
        }
        
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="keranjang.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">💳 Checkout</div>
        </div>
    </div>
    
    <div class="checkout-container">
        <?php if($message): ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if(empty($cartItems)): ?>
            <div class="form-section">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 16px; color: #9CA3AF;"></i>
                    <p>Keranjang belanja kosong</p>
                    <a href="index.php" style="display: inline-block; margin-top: 16px; color: #6C4CF1; text-decoration: none;">Lihat Menu →</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="" id="checkoutForm">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user" style="color:#6C4CF1;"></i> Informasi Pemesan
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" class="form-control" name="customer_name" id="customerName" value="<?php echo htmlspecialchars($userName); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon *</label>
                        <input type="tel" class="form-control" name="customer_phone" id="customerPhone" value="<?php echo htmlspecialchars($userPhone); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Meja (Opsional)</label>
                        <input type="text" class="form-control" name="table_number" id="tableNumber" placeholder="Nomor meja">
                    </div>
                    <div class="form-group">
                        <label>Catatan (Opsional)</label>
                        <textarea class="form-control" name="order_notes" id="orderNotes" rows="2" placeholder="Catatan khusus untuk pesanan"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-credit-card" style="color:#6C4CF1;"></i> Metode Pembayaran
                    </div>
                    <div class="payment-methods">
                        <label class="payment-option selected" data-method="cash">
                            <input type="radio" name="payment_method" value="cash" checked hidden>
                            <div class="payment-icon"><i class="fas fa-money-bill"></i></div>
                            <div>
                                <div class="payment-name">Tunai</div>
                                <div class="payment-desc">Bayar di kasir</div>
                            </div>
                        </label>
                        <label class="payment-option" data-method="qris">
                            <input type="radio" name="payment_method" value="qris" hidden>
                            <div class="payment-icon"><i class="fas fa-qrcode"></i></div>
                            <div>
                                <div class="payment-name">QRIS</div>
                                <div class="payment-desc">Scan QR code dengan e-wallet</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="order-summary">
                    <div class="section-title">Ringkasan Pesanan</div>
                    <div class="order-items">
                        <?php foreach($cartItems as $item): ?>
                        <div class="order-item">
                            <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                            <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    <button type="submit" name="confirm_order" class="confirm-btn" id="confirmBtn">Konfirmasi Pesanan</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- QRIS Modal -->
    <div id="qrisModal" class="qris-modal">
        <div class="qris-modal-content">
            <div class="qris-title">Scan QRIS untuk Membayar</div>
            <div class="qris-qr-code">
                <img id="qrisImage" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=QRIS_CaFood_<?php echo $total; ?>" alt="QR Code">
            </div>
            <div class="qris-amount">Rp <?php echo number_format($total, 0, ',', '.'); ?></div>
            <div class="qris-instruction">
                <i class="fas fa-mobile-alt"></i> Buka aplikasi e-wallet atau mobile banking<br>
                Scan QR code di atas untuk melakukan pembayaran
            </div>
            <button type="button" class="qris-close-btn" id="qrisConfirmBtn">Saya Sudah Bayar</button>
            <button type="button" class="qris-close-btn" id="qrisCancelBtn" style="background:#6B7280; margin-top:8px;">Batal</button>
        </div>
    </div>
    
    <script>
        // Payment method selection
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });
        
        // QRIS Modal handling
        const qrisModal = document.getElementById('qrisModal');
        const qrisConfirmBtn = document.getElementById('qrisConfirmBtn');
        const qrisCancelBtn = document.getElementById('qrisCancelBtn');
        const checkoutForm = document.getElementById('checkoutForm');
        const confirmBtn = document.getElementById('confirmBtn');
        
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                if (selectedPayment && selectedPayment.value === 'qris') {
                    e.preventDefault();
                    qrisModal.style.display = 'flex';
                }
            });
        }
        
        qrisConfirmBtn.addEventListener('click', function() {
            qrisModal.style.display = 'none';
            if (checkoutForm) {
                confirmBtn.innerHTML = '<span class="spinner"></span> Memproses...';
                confirmBtn.disabled = true;
                checkoutForm.submit();
            }
        });
        
        qrisCancelBtn.addEventListener('click', function() {
            qrisModal.style.display = 'none';
        });
        
        // Close modal when clicking outside
        qrisModal.addEventListener('click', function(e) {
            if (e.target === qrisModal) {
                qrisModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>