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
$userName = $_SESSION['name'] ?? 'User';

// Get all orders for this user
$stmt = $pdo->prepare("
    SELECT o.*, s.stand_name 
    FROM orders o 
    LEFT JOIN stands s ON o.stand_id = s.id 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active orders (pending, confirmed, processing, ready)
$activeOrders = array_filter($allOrders, function($order) {
    return in_array($order['status'], ['pending', 'confirmed', 'processing', 'ready']);
});

// Get completed orders
$completedOrders = array_filter($allOrders, function($order) {
    return $order['status'] == 'completed';
});

// Get current selected order from URL or default to first active order
$selectedOrderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// If no specific order selected and has active orders, show first active order
if ($selectedOrderId == 0 && !empty($activeOrders)) {
    $selectedOrderId = reset($activeOrders)['id'];
} 
// If no active orders but has completed orders, show first completed order
elseif ($selectedOrderId == 0 && !empty($completedOrders)) {
    $selectedOrderId = reset($completedOrders)['id'];
}

// Get selected order details
$currentOrder = null;
$orderItems = [];
if ($selectedOrderId > 0) {
    $stmt = $pdo->prepare("
        SELECT o.*, s.stand_name 
        FROM orders o 
        LEFT JOIN stands s ON o.stand_id = s.id 
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$selectedOrderId, $userId]);
    $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($currentOrder) {
        $stmt = $pdo->prepare("
            SELECT * FROM order_items 
            WHERE order_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$selectedOrderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $currentOrder['items'] = $orderItems;
    }
}

// Handle refresh via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    
    if ($orderId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            echo json_encode(['success' => true, 'order' => $order]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Order not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    }
    exit;
}

function formatRupiah($price) {
    if (!$price) return 'Rp 0';
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function formatDateTime($datetime) {
    if (!$datetime) return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

function getStatusText($status) {
    $labels = [
        'pending' => 'Menunggu Konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'ready' => 'Siap Diambil',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function getStatusIcon($status) {
    $icons = [
        'pending' => '🕐',
        'confirmed' => '✅',
        'processing' => '🍳',
        'ready' => '🛎️',
        'completed' => '🎉',
        'cancelled' => '❌'
    ];
    return $icons[$status] ?? '📦';
}

function getStatusClass($status) {
    $classes = [
        'pending' => 'status-pending',
        'confirmed' => 'status-confirmed',
        'processing' => 'status-processing',
        'ready' => 'status-ready',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled'
    ];
    return $classes[$status] ?? 'status-pending';
}

$statusSteps = [
    ['id' => 'pending', 'label' => 'Menunggu Konfirmasi', 'icon' => '🕐'],
    ['id' => 'confirmed', 'label' => 'Dikonfirmasi', 'icon' => '✅'],
    ['id' => 'processing', 'label' => 'Diproses', 'icon' => '🍳'],
    ['id' => 'ready', 'label' => 'Siap Diambil', 'icon' => '🛎️'],
    ['id' => 'completed', 'label' => 'Selesai', 'icon' => '🎉']
];

$currentStepIndex = -1;
if ($currentOrder && isset($currentOrder['status'])) {
    $currentStepIndex = array_search($currentOrder['status'], array_column($statusSteps, 'id'));
    if ($currentStepIndex === false) $currentStepIndex = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Status Pesanan</title>
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
            background: #f8f9fa;
            padding-bottom: 40px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid #e9ecef;
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
        .back-btn:hover { background: #e2e8f0; transform: scale(1.05); }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .status-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Order Selector */
        .order-selector {
            background: white;
            border-radius: 20px;
            padding: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .order-tab {
            padding: 10px 20px;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
            background: #f8fafc;
            color: #64748b;
        }
        
        .order-tab.active {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            box-shadow: 0 4px 10px rgba(108,76,241,0.2);
        }
        
        .order-tab.completed {
            opacity: 0.7;
        }
        
        /* Queue Card */
        .queue-card {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 24px;
            padding: 28px;
            text-align: center;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(108,76,241,0.2);
            position: relative;
            overflow: hidden;
        }
        
        .queue-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle at center, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
            pointer-events: none;
        }
        
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
        
        .queue-label {
            font-size: 0.75rem;
            opacity: 0.9;
            letter-spacing: 1px;
        }
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 12px 0;
            letter-spacing: 1px;
        }
        
        .order-date {
            font-size: 0.7rem;
            opacity: 0.8;
        }
        
        /* Timeline */
        .timeline-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 28px;
            left: 40px;
            right: 40px;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
        
        .timeline-step {
            text-align: center;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        .step-icon {
            width: 56px;
            height: 56px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.3rem;
            transition: all 0.3s;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .timeline-step.completed .step-icon {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
        }
        
        .timeline-step.active .step-icon {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        
        .step-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .step-time {
            font-size: 0.6rem;
            color: #94a3b8;
            margin-top: 5px;
        }
        
        .timeline-step.completed .step-label,
        .timeline-step.active .step-label {
            color: #6C4CF1;
        }
        
        /* Details Card */
        .details-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .details-title {
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }
        
        .detail-label {
            color: #64748b;
        }
        
        .detail-value {
            font-weight: 500;
            color: #1e293b;
        }
        
        .items-list {
            margin-top: 12px;
        }
        
        .items-list .detail-row {
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed { background: #dbeafe; color: #2563eb; }
        .status-processing { background: #e0e7ff; color: #4f46e5; }
        .status-ready { background: #d1fae5; color: #059669; }
        .status-completed { background: #a7f3d0; color: #047857; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn-primary {
            flex: 1;
            padding: 12px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 0.85rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(108,76,241,0.2);
        }
        
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(108,76,241,0.4); }
        
        .btn-outline {
            flex: 1;
            padding: 12px;
            background: white;
            color: #6C4CF1;
            border: 1px solid #6C4CF1;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: #f8f4ff;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
        }
        
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
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
        
        @media (max-width: 768px) {
            .timeline::before { left: 25px; right: 25px; }
            .step-icon { width: 45px; height: 45px; font-size: 1rem; }
            .step-label { font-size: 0.55rem; }
            .order-number { font-size: 1.2rem; }
            .order-selector { padding: 6px; }
            .order-tab { padding: 8px 16px; font-size: 0.7rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">📦 Status Pesanan</div>
            <div style="width: 40px;"></div>
        </div>
    </div>
    
    <div class="status-container" id="statusContainer">
        <?php if (empty($allOrders)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3>Belum Ada Pesanan</h3>
                <p style="color: #64748b; margin-top: 8px;">Anda belum memiliki riwayat pesanan</p>
                <a href="index.php" class="btn-primary" style="margin-top: 24px; display: inline-block; padding: 12px 32px; text-decoration: none;">
                    <i class="fas fa-store"></i> Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            <!-- Order Selector / Tabs -->
            <div class="order-selector">
                <?php foreach($allOrders as $order): ?>
                    <?php 
                    $isActive = ($selectedOrderId == $order['id']);
                    $isCompleted = ($order['status'] == 'completed');
                    ?>
                    <a href="?order_id=<?php echo $order['id']; ?>" 
                       class="order-tab <?php echo $isActive ? 'active' : ''; ?> <?php echo $isCompleted ? 'completed' : ''; ?>">
                        #<?php echo $order['order_number'] ?? $order['id']; ?>
                        <span class="status-badge <?php echo getStatusClass($order['status']); ?>" style="margin-left: 6px; padding: 2px 8px;">
                            <?php echo getStatusText($order['status']); ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($currentOrder): ?>
                <?php
                $total = $currentOrder['total'] ?? 0;
                $items = $currentOrder['items'] ?? [];
                ?>
                
                <!-- Queue Card -->
                <div class="queue-card">
                    <div class="queue-label">STATUS PESANAN</div>
                    <div class="order-number"><?php echo htmlspecialchars($currentOrder['order_number'] ?? 'ORD-' . $currentOrder['id']); ?></div>
                    <div class="order-date"><?php echo formatDateTime($currentOrder['created_at']); ?></div>
                </div>
                
                <!-- Timeline -->
                <div class="timeline-card">
                    <div class="timeline">
                        <?php foreach($statusSteps as $index => $step): ?>
                            <?php 
                            $isCompleted = $index <= $currentStepIndex;
                            $isActive = $index === $currentStepIndex;
                            ?>
                            <div class="timeline-step <?php echo $isCompleted ? 'completed' : ''; ?> <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="step-icon"><?php echo $step['icon']; ?></div>
                                <div class="step-label"><?php echo $step['label']; ?></div>
                                <?php if($isActive && $currentOrder['updated_at']): ?>
                                <div class="step-time"><?php echo date('H:i', strtotime($currentOrder['updated_at'])); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Details Card -->
                <div class="details-card">
                    <div class="details-title">Detail Pesanan</div>
                    <div class="detail-row">
                        <span class="detail-label">Stand</span>
                        <span class="detail-value"><?php echo htmlspecialchars($currentOrder['stand_name'] ?? '-'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nama</span>
                        <span class="detail-value"><?php echo htmlspecialchars($currentOrder['customer_name'] ?? $userName); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran</span>
                        <span class="detail-value"><?php echo htmlspecialchars($currentOrder['payment_method'] ?? 'Tunai'); ?></span>
                    </div>
                    <?php if(!empty($currentOrder['notes'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan</span>
                        <span class="detail-value"><?php echo htmlspecialchars($currentOrder['notes']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="details-title" style="margin-top: 16px;">Item Pesanan</div>
                    <div class="items-list">
                        <?php if (empty($items)): ?>
                            <div class="detail-row">
                                <span class="detail-label">Tidak ada item</span>
                            </div>
                        <?php else: ?>
                            <?php foreach($items as $item): ?>
                            <div class="detail-row">
                                <span class="detail-label"><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['menu_name'] ?? $item['name']); ?></span>
                                <span class="detail-value"><?php echo formatRupiah(($item['price'] ?? 0) * ($item['quantity'] ?? 1)); ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-weight: 700;">
                        <span class="detail-label">Total</span>
                        <span class="detail-value"><?php echo formatRupiah($total); ?></span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="index.php" class="btn-outline" style="text-decoration: none;">
                        <i class="fas fa-store"></i> Pesan Lagi
                    </a>
                    <button class="btn-primary" onclick="refreshStatus()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
        let currentOrderId = <?php echo $currentOrder ? ($currentOrder['id'] ?? 0) : 0; ?>;
        let refreshInterval;
        
        function showToast(message, isError = false) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isError ? '#EF4444' : '#10B981';
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }
        
        async function refreshStatus() {
            if (!currentOrderId) {
                showToast('Tidak ada pesanan aktif', true);
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('order_id', currentOrderId);
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const oldStatus = document.querySelector('.timeline-step.active .step-label')?.textContent || '';
                    const newStatusText = getStatusText(result.order.status);
                    
                    if (oldStatus !== newStatusText) {
                        showToast(`Status berubah: ${newStatusText}`);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('Status masih sama');
                    }
                } else {
                    showToast(result.error || 'Gagal refresh status', true);
                }
            } catch (error) {
                console.error("Error refreshing:", error);
                showToast('Gagal refresh status', true);
            }
        }
        
        function getStatusText(statusId) {
            const statusMap = {
                'pending': 'Menunggu Konfirmasi',
                'confirmed': 'Dikonfirmasi',
                'processing': 'Diproses',
                'ready': 'Siap Diambil',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan'
            };
            return statusMap[statusId] || statusId;
        }
        
        // Auto refresh only for active orders (not completed)
        function startAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            
            // Only auto-refresh if order is not completed
            const statusBadge = document.querySelector('.order-tab.active .status-badge');
            const isCompleted = statusBadge?.textContent.includes('Selesai');
            
            if (!isCompleted && currentOrderId) {
                refreshInterval = setInterval(() => {
                    refreshStatus();
                }, 15000);
            }
        }
        
        // Start auto refresh if there's an active order
        startAutoRefresh();
        
        // Make functions available globally
        window.refreshStatus = refreshStatus;
    </script>
</body>
</html>