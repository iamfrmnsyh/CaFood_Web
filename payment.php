<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'User';

// Get order ID from parameter
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$paymentMethod = isset($_GET['method']) ? $_GET['method'] : 'qris';

if ($orderId <= 0) {
    header('Location: keranjang.php');
    exit;
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as user_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: keranjang.php');
    exit;
}

// Generate payment code (unique for this transaction)
$paymentCode = 'CAF' . str_pad($orderId, 8, '0', STR_PAD_LEFT) . date('ymd');

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, m.name as menu_name, m.price 
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$subtotal = $order['total_amount'];
$deliveryFee = 5000;
$tax = $subtotal * 0.1; // 10% tax
$grandTotal = $subtotal + $deliveryFee + $tax;

// Generate barcode data based on payment method
$barcodeData = '';
if ($paymentMethod == 'qris') {
    // Format QRIS data (simplified - in production use proper QRIS standard)
    $barcodeData = json_encode([
        'merchant' => 'CaFood',
        'amount' => $grandTotal,
        'order_id' => $orderId,
        'payment_code' => $paymentCode,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} elseif ($paymentMethod == 'bca') {
    $barcodeData = 'BCA-' . $paymentCode;
} elseif ($paymentMethod == 'mandiri') {
    $barcodeData = 'MANDIRI-' . $paymentCode;
} elseif ($paymentMethod == 'bni') {
    $barcodeData = 'BNI-' . $paymentCode;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pembayaran - CaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .payment-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 24px;
            text-align: center;
        }
        
        .payment-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        
        .payment-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .payment-content {
            padding: 24px;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .summary-title {
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 16px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-total {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px solid #dee2e6;
            font-weight: 700;
            font-size: 1.1rem;
            color: #6C4CF1;
        }
        
        .barcode-section {
            text-align: center;
            padding: 24px;
            background: white;
            border-radius: 20px;
            border: 2px dashed #e9ecef;
            margin-bottom: 24px;
        }
        
        .barcode-title {
            font-weight: 600;
            margin-bottom: 16px;
            color: #495057;
        }
        
        #qrcode {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        #qrcode img {
            width: 200px;
            height: 200px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .barcode-number {
            margin-top: 16px;
            font-family: monospace;
            font-size: 1.2rem;
            letter-spacing: 2px;
            background: #f8f9fa;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .payment-instruction {
            background: #e7f3ff;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .payment-instruction i {
            color: #6C4CF1;
            margin-right: 8px;
        }
        
        .timer-section {
            text-align: center;
            padding: 16px;
            background: #fff3cd;
            border-radius: 16px;
            margin-bottom: 24px;
        }
        
        .timer {
            font-size: 2rem;
            font-weight: 700;
            color: #856404;
            font-family: monospace;
        }
        
        .timer-label {
            font-size: 0.8rem;
            color: #856404;
            margin-top: 4px;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108,76,241,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-outline {
            background: white;
            border: 2px solid #6C4CF1;
            color: #6C4CF1;
        }
        
        .btn-outline:hover {
            background: #6C4CF1;
            color: white;
        }
        
        .payment-methods {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        
        .payment-method-btn {
            flex: 1;
            padding: 12px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .payment-method-btn.active {
            border-color: #6C4CF1;
            background: rgba(108,76,241,0.1);
            color: #6C4CF1;
        }
        
        .payment-method-btn i {
            font-size: 1.5rem;
            margin-bottom: 4px;
            display: block;
        }
        
        .payment-method-btn span {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        @media (max-width: 768px) {
            body { padding: 12px; }
            .payment-container { border-radius: 24px; }
            .timer { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1><i class="fas fa-qrcode"></i> Pembayaran</h1>
            <p>Scan QR Code untuk menyelesaikan pembayaran</p>
        </div>
        
        <div class="payment-content">
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-title">
                    <i class="fas fa-receipt"></i>
                    Ringkasan Pesanan
                </div>
                <div class="summary-item">
                    <span>Order #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></span>
                    <span><?php echo date('H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <?php foreach($orderItems as $item): ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['menu_name']); ?> x<?php echo $item['quantity']; ?></span>
                    <span><?php echo formatRupiah($item['price'] * $item['quantity']); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-item">
                    <span>Subtotal</span>
                    <span><?php echo formatRupiah($subtotal); ?></span>
                </div>
                <div class="summary-item">
                    <span>Biaya Delivery</span>
                    <span><?php echo formatRupiah($deliveryFee); ?></span>
                </div>
                <div class="summary-item">
                    <span>Pajak (10%)</span>
                    <span><?php echo formatRupiah($tax); ?></span>
                </div>
                <div class="summary-total">
                    <span>Total Pembayaran</span>
                    <span><?php echo formatRupiah($grandTotal); ?></span>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="payment-methods">
                <div class="payment-method-btn <?php echo $paymentMethod == 'qris' ? 'active' : ''; ?>" onclick="changePaymentMethod('qris')">
                    <i class="fas fa-qrcode"></i>
                    <span>QRIS</span>
                </div>
                <div class="payment-method-btn <?php echo $paymentMethod == 'bca' ? 'active' : ''; ?>" onclick="changePaymentMethod('bca')">
                    <i class="fas fa-university"></i>
                    <span>BCA</span>
                </div>
                <div class="payment-method-btn <?php echo $paymentMethod == 'mandiri' ? 'active' : ''; ?>" onclick="changePaymentMethod('mandiri')">
                    <i class="fas fa-university"></i>
                    <span>Mandiri</span>
                </div>
                <div class="payment-method-btn <?php echo $paymentMethod == 'bni' ? 'active' : ''; ?>" onclick="changePaymentMethod('bni')">
                    <i class="fas fa-university"></i>
                    <span>BNI</span>
                </div>
            </div>
            
            <!-- Barcode Section -->
            <div class="barcode-section">
                <div class="barcode-title">
                    <i class="fas fa-qrcode"></i> Scan Barcode Berikut
                </div>
                <div id="qrcode"></div>
                <div class="barcode-number" id="barcodeNumber">
                    <?php echo $paymentCode; ?>
                </div>
                <p style="font-size: 0.7rem; color: #6c757d; margin-top: 12px;">
                    Kode Pembayaran: <?php echo $paymentCode; ?>
                </p>
            </div>
            
            <!-- Payment Instruction -->
            <div class="payment-instruction">
                <i class="fas fa-info-circle"></i>
                <strong>Cara Pembayaran:</strong><br>
                <span id="paymentInstruction">
                    1. Buka aplikasi mobile banking atau e-wallet<br>
                    2. Pilih menu scan QR Code<br>
                    3. Scan QR Code di atas<br>
                    4. Konfirmasi pembayaran
                </span>
            </div>
            
            <!-- Timer -->
            <div class="timer-section">
                <i class="fas fa-hourglass-half"></i>
                <div class="timer" id="timer">10:00</div>
                <div class="timer-label">Waktu tersisa untuk menyelesaikan pembayaran</div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="status-pesanan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-primary" onclick="confirmPayment()">
                    <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let countdownTimer;
        let timeLeft = 600; // 10 minutes in seconds
        
        // Generate QR Code based on payment method
        function generateQRCode() {
            const paymentMethod = getPaymentMethod();
            let qrData = '';
            
            <?php if($paymentMethod == 'qris'): ?>
            if (paymentMethod === 'qris') {
                qrData = JSON.stringify({
                    merchant: 'CaFood',
                    amount: <?php echo $grandTotal; ?>,
                    order_id: <?php echo $orderId; ?>,
                    payment_code: '<?php echo $paymentCode; ?>',
                    timestamp: new Date().toISOString()
                });
            } else {
                qrData = paymentMethod.toUpperCase() + '-' + '<?php echo $paymentCode; ?>';
            }
            <?php else: ?>
            qrData = paymentMethod.toUpperCase() + '-' + '<?php echo $paymentCode; ?>';
            <?php endif; ?>
            
            // Clear previous QR code
            document.getElementById('qrcode').innerHTML = '';
            
            // Generate new QR code
            new QRCode(document.getElementById('qrcode'), {
                text: qrData,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            // Update barcode number display
            document.getElementById('barcodeNumber').textContent = qrData;
        }
        
        // Get current payment method from URL or default
        function getPaymentMethod() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('method') || 'qris';
        }
        
        // Change payment method
        function changePaymentMethod(method) {
            // Update URL without reload
            const url = new URL(window.location.href);
            url.searchParams.set('method', method);
            window.history.pushState({}, '', url);
            
            // Update active state on buttons
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase().includes(method)) {
                    btn.classList.add('active');
                }
            });
            
            // Update instruction text
            updateInstruction(method);
            
            // Regenerate QR code
            generateQRCode();
        }
        
        // Update payment instruction based on method
        function updateInstruction(method) {
            const instructionDiv = document.querySelector('.payment-instruction span');
            let instruction = '';
            
            switch(method) {
                case 'qris':
                    instruction = `1. Buka aplikasi mobile banking atau e-wallet (OVO, GoPay, Dana, ShopeePay, dll)<br>
                                   2. Pilih menu scan QR Code<br>
                                   3. Scan QR Code di atas<br>
                                   4. Masukkan nominal Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?><br>
                                   5. Konfirmasi pembayaran dan screenshot bukti`;
                    break;
                case 'bca':
                    instruction = `1. Buka aplikasi BCA Mobile atau myBCA<br>
                                   2. Pilih menu transfer antar rekening<br>
                                   3. Masukkan nomor rekening: 1234567890 a.n PT CaFood<br>
                                   4. Masukkan nominal: Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?><br>
                                   5. Masukkan kode pembayaran: ${document.getElementById('barcodeNumber').textContent}<br>
                                   6. Konfirmasi pembayaran`;
                    break;
                case 'mandiri':
                    instruction = `1. Buka aplikasi Livin' by Mandiri<br>
                                   2. Pilih menu transfer ke rekening Mandiri<br>
                                   3. Masukkan nomor rekening: 0987654321 a.n PT CaFood<br>
                                   4. Masukkan nominal: Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?><br>
                                   5. Masukkan kode pembayaran: ${document.getElementById('barcodeNumber').textContent}<br>
                                   6. Konfirmasi pembayaran`;
                    break;
                case 'bni':
                    instruction = `1. Buka aplikasi BNI Mobile Banking<br>
                                   2. Pilih menu transfer antar rekening BNI<br>
                                   3. Masukkan nomor rekening: 5678901234 a.n PT CaFood<br>
                                   4. Masukkan nominal: Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?><br>
                                   5. Masukkan kode pembayaran: ${document.getElementById('barcodeNumber').textContent}<br>
                                   6. Konfirmasi pembayaran`;
                    break;
            }
            
            instructionDiv.innerHTML = instruction;
        }
        
        // Timer function
        function startTimer() {
            const timerElement = document.getElementById('timer');
            
            countdownTimer = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    timerElement.textContent = '00:00';
                    alert('Waktu pembayaran telah habis. Silakan lakukan pemesanan ulang.');
                    window.location.href = 'keranjang.php';
                } else {
                    timeLeft--;
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }
        
        // Confirm payment function
        function confirmPayment() {
            if (confirm('Apakah Anda sudah melakukan pembayaran? Konfirmasi akan mengirimkan bukti pembayaran ke admin.')) {
                // Show loading
                const confirmBtn = document.querySelector('.btn-primary');
                const originalText = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Memproses...';
                confirmBtn.disabled = true;
                
                // Simulate API call to confirm payment
                setTimeout(() => {
                    // Redirect to order status page
                    alert('Pembayaran berhasil dikonfirmasi! Pesanan Anda akan segera diproses.');
                    window.location.href = 'status-pesanan.php';
                }, 2000);
            }
        }
        
        // Check payment status periodically (simulated)
        function checkPaymentStatus() {
            setInterval(() => {
                // In production, this would be an AJAX call to check payment status
                console.log('Checking payment status for order <?php echo $orderId; ?>...');
            }, 5000);
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            generateQRCode();
            startTimer();
            checkPaymentStatus();
            
            // Update instruction on load
            updateInstruction(getPaymentMethod());
        });
        
        // Cleanup timer on page unload
        window.addEventListener('beforeunload', () => {
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
        });
    </script>
</body>
</html>

<?php
function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
?>