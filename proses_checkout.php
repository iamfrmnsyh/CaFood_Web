<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Customer';

// Ambil cart items
$stmt = $pdo->prepare("
    SELECT c.*, m.name, m.price, m.stand_id, s.stand_name 
    FROM cart c 
    JOIN menus m ON c.menu_id = m.id 
    JOIN stands s ON m.stand_id = s.id 
    WHERE c.user_id = ?
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    header('Location: keranjang.php');
    exit;
}

// Group by stand (karena bisa pesan dari multiple stand)
$ordersByStand = [];
foreach ($cartItems as $item) {
    $standId = $item['stand_id'];
    if (!isset($ordersByStand[$standId])) {
        $ordersByStand[$standId] = [
            'stand_name' => $item['stand_name'],
            'items' => [],
            'total' => 0
        ];
    }
    $subtotal = $item['price'] * $item['quantity'];
    $ordersByStand[$standId]['items'][] = $item;
    $ordersByStand[$standId]['total'] += $subtotal;
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $notes = $_POST['notes'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        foreach ($ordersByStand as $standId => $orderData) {
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Insert ke orders - HANYA KOLOM YANG ADA DI TABEL
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, stand_id, customer_name, total, status, payment_method, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
            ");
            $stmt->execute([
                $orderNumber,
                $userId,
                $standId,
                $userName,
                $orderData['total'],
                $payment_method,
                $notes
            ]);
            $orderId = $pdo->lastInsertId();
            
            // Insert ke order_items
            foreach ($orderData['items'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (order_id, menu_id, menu_name, price, quantity, subtotal) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['menu_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity'],
                    $subtotal
                ]);
            }
        }
        
        // Hapus cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        
        // Simpan order ID terakhir ke session untuk redirect
        $_SESSION['last_order_id'] = $orderId ?? null;
        $_SESSION['checkout_success'] = true;
        
        header('Location: status-pesanan.php?success=1');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal memproses pesanan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - CaFood</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 24px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        h1 { margin-bottom: 8px; color: #1e293b; }
        .order-summary { margin-bottom: 16px; padding: 16px; background: #f8fafc; border-radius: 16px; }
        .order-items { margin-top: 16px; }
        .order-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .total { font-size: 1.2rem; font-weight: 700; color: #070707; margin-top: 12px; text-align: right; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #374151; }
        select, textarea { width: 100%; padding: 12px; border: 1.5px solid #e2e8f0; border-radius: 16px; font-family: 'Inter', sans-serif; }
        .btn-checkout { background: linear-gradient(135deg, #6C4CF1, #6C4CF1); color: white; border: none; padding: 14px 28px; border-radius: 30px; font-weight: 700; cursor: pointer; width: 100%; font-size: 1rem; transition: all 0.3s; }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245,158,11,0.4); }
        .btn-back { background: #6C4CF1; color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; display: inline-block; margin-bottom: 20px; transition: all 0.3s; }
        .btn-back:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(108,76,241,0.3); }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-error i { font-size: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="keranjang.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Keranjang</a>
        
        <div class="card">
            <h1>Checkout</h1>
            <p style="color: #64748b; margin-bottom: 20px;">Review pesanan Anda</p>
            
            <?php if(isset($error)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php foreach($ordersByStand as $standId => $orderData): ?>
            <div class="order-summary">
                <h3><i class="fas fa-store"></i> <?php echo htmlspecialchars($orderData['stand_name']); ?></h3>
                <div class="order-items">
                    <?php foreach($orderData['items'] as $item): ?>
                    <div class="order-item">
                        <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
                        <span>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="total">Total: Rp <?php echo number_format($orderData['total'], 0, ',', '.'); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" required>
                        <option value="cash">Tunai (Bayar di tempat)</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="notes" rows="3" placeholder="Contoh: Jangan pakai micin, tambah sambal..."></textarea>
                </div>
                <button type="submit" class="btn-checkout">
                    <i class="fas fa-check-circle"></i> Konfirmasi Pesanan
                </button>
            </form>
        </div>
    </div>
</body>
</html>