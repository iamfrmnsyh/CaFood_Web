<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database configuration
require_once __DIR__ . '/config/database.php';

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
$totalStands = $pdo->query("SELECT COUNT(*) as total FROM stands")->fetch(PDO::FETCH_ASSOC)['total'];
$totalOrders = $pdo->query("SELECT COUNT(*) as total FROM orders")->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue
try {
    $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status = 'completed'");
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'");
        $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e2) {
        $revenue = 0;
    }
}

// Get all orders with join to stands and users
try {
    $stmt = $pdo->query("
        SELECT o.*, s.stand_name, s.id as stand_id, u.name as user_name 
        FROM orders o 
        LEFT JOIN stands s ON o.stand_id = s.id 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC
    ");
    $allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $allOrders = [];
}

// Order statistics
$pendingOrders = count(array_filter($allOrders, function($order) {
    return ($order['status'] ?? '') == 'pending';
}));
$processingOrders = count(array_filter($allOrders, function($order) {
    return ($order['status'] ?? '') == 'processing';
}));
$completedOrders = count(array_filter($allOrders, function($order) {
    return ($order['status'] ?? '') == 'completed';
}));
$cancelledOrders = count(array_filter($allOrders, function($order) {
    return ($order['status'] ?? '') == 'cancelled';
}));

// Get all stands with menu counts
$stands = $pdo->query("
    SELECT s.*, u.name as owner_name, 
           (SELECT COUNT(*) FROM menus WHERE stand_id = s.id) as menu_count 
    FROM stands s 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get selected stand from URL (baik dari tab stands maupun dari klik nama stand di orders)
$selectedStandId = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;

// Jika ada parameter from_order, set active tab ke stands
$fromOrder = isset($_GET['from_order']) ? true : false;
if ($fromOrder && $selectedStandId > 0) {
    $activeTab = 'stands';
} else {
    // Get current tab
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
}

$selectedStand = null;
$standMenus = [];

if ($selectedStandId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM stands WHERE id = ?");
    $stmt->execute([$selectedStandId]);
    $selectedStand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selectedStand) {
        $stmt = $pdo->prepare("SELECT * FROM menus WHERE stand_id = ? ORDER BY created_at DESC");
        $stmt->execute([$selectedStandId]);
        $standMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get all users with search and filter
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($roleFilter) && $roleFilter != 'all') {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        $message = "Status pesanan berhasil diupdate";
        header("Location: dashboard-admin.php?tab=orders&message=" . urlencode($message));
        exit;
    } catch(PDOException $e) {
        $error = "Gagal mengupdate status: " . $e->getMessage();
    }
}

// Delete order
if (isset($_GET['delete_order'])) {
    $orderId = (int)$_GET['delete_order'];
    try {
        // Delete order items first
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $message = "Pesanan berhasil dihapus";
        header("Location: dashboard-admin.php?tab=orders&message=" . urlencode($message));
        exit;
    } catch(PDOException $e) {
        $error = "Gagal menghapus pesanan: " . $e->getMessage();
    }
}

// Delete user
if (isset($_GET['delete_user'])) {
    $userId = (int)$_GET['delete_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "User berhasil dihapus";
        header("Location: dashboard-admin.php?tab=users&message=" . urlencode($message));
        exit;
    } catch(PDOException $e) {
        $error = "Gagal menghapus user: " . $e->getMessage();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get message from URL
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';

function formatRupiah($price) {
    if (!$price) return 'Rp 0';
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function getOrderTotal($order) {
    if (isset($order['total'])) return $order['total'];
    if (isset($order['total_amount'])) return $order['total_amount'];
    if (isset($order['total_price'])) return $order['total_price'];
    if (isset($order['amount'])) return $order['amount'];
    return 0;
}

function getStatusLabel($status) {
    $labels = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'ready' => 'Siap Diambil',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
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

// Get role label in Indonesian
function getRoleLabel($role) {
    $labels = [
        'admin' => 'Admin',
        'stand' => 'Pemilik Stand',
        'customer' => 'Pelanggan'
    ];
    return $labels[$role] ?? $role;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CaFood</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; min-height: 100vh; }
        
        .navbar { background: linear-gradient(135deg, #667eea, #764ba2); padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; position: sticky; top: 0; z-index: 100; }
        .logo { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .logo-icon { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: white; }
        .logo-text { font-size: 1.3rem; font-weight: 800; color: white; }
        .nav-menu { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .nav-item { color: white; text-decoration: none; font-weight: 500; padding: 8px 16px; border-radius: 30px; transition: all 0.3s; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.2); }
        .user-info { display: flex; align-items: center; gap: 15px; color: white; }
        .logout-btn { background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 30px; color: white; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .logout-btn:hover { background: #ef4444; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 20px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-icon { width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .stat-info h3 { font-size: 1.5rem; font-weight: 800; color: #1e293b; }
        .stat-info p { color: #64748b; font-size: 0.75rem; }
        
        .section { background: white; border-radius: 24px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .section-title { font-size: 1.2rem; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        /* Filter & Search */
        .filter-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
        .search-box { display: flex; gap: 10px; flex: 1; max-width: 400px; }
        .search-box input { flex: 1; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 30px; font-size: 0.85rem; }
        .search-box button { padding: 10px 20px; background: #6C4CF1; color: white; border: none; border-radius: 30px; cursor: pointer; }
        .filter-select { padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 30px; font-size: 0.85rem; background: white; cursor: pointer; }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 700; color: #1e293b; }
        td { color: #475569; }
        
        /* Stand name clickable style */
        .stand-name-link { 
            color: #6C4CF1; 
            cursor: pointer; 
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .stand-name-link:hover { 
            color: #4f46e5; 
            text-decoration: underline;
        }
        
        .stand-card { cursor: pointer; transition: all 0.3s; }
        .stand-card:hover { background: #f8fafc; transform: translateX(4px); }
        .stand-card.active { background: #e0e7ff; border-left: 4px solid #6C4CF1; }
        
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed { background: #dbeafe; color: #2563eb; }
        .status-processing { background: #e0e7ff; color: #4f46e5; }
        .status-ready { background: #d1fae5; color: #059669; }
        .status-completed { background: #a7f3d0; color: #047857; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        .badge-open { background: #d1fae5; color: #059669; }
        .badge-closed { background: #fee2e2; color: #dc2626; }
        
        .btn-action { padding: 6px 12px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.75rem; font-weight: 500; transition: all 0.3s; margin: 0 4px; }
        .btn-edit { background: #e0e7ff; color: #4f46e5; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-status { padding: 4px 10px; border-radius: 20px; border: none; cursor: pointer; font-size: 0.7rem; font-weight: 500; margin: 0 2px; background: #e2e8f0; color: #1e293b; }
        .btn-status:hover { transform: translateY(-1px); background: #cbd5e1; }
        
        .alert-success { background: #d1fae5; color: #059669; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; }
        
        /* Menu Grid */
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .menu-item { background: #f8fafc; border-radius: 16px; padding: 16px; display: flex; gap: 16px; transition: all 0.3s; border: 1px solid #e2e8f0; }
        .menu-item:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .menu-image { width: 80px; height: 80px; border-radius: 12px; overflow: hidden; background: linear-gradient(135deg, #667eea, #764ba2); flex-shrink: 0; }
        .menu-image img { width: 100%; height: 100%; object-fit: cover; }
        .menu-image-placeholder { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 2rem; color: rgba(255,255,255,0.7); }
        .menu-info { flex: 1; }
        .menu-name { font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .menu-price { font-weight: 600; color: #6C4CF1; font-size: 0.9rem; }
        .menu-desc { font-size: 0.7rem; color: #64748b; margin-top: 4px; }
        
        .stand-info-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 16px 20px; border-radius: 16px; margin-bottom: 20px; }
        .stand-info-header h3 { margin-bottom: 8px; }
        .stand-info-header p { opacity: 0.9; font-size: 0.85rem; }
        
        .order-detail { background: #f8fafc; border-radius: 16px; padding: 16px; margin-top: 16px; }
        .order-items { margin-top: 12px; }
        .order-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        
        .empty-state { text-align: center; padding: 60px; color: #94a3b8; }
        .empty-state i { font-size: 4rem; margin-bottom: 16px; display: block; opacity: 0.5; }
        
        @media (max-width: 768px) { 
            .navbar { flex-direction: column; text-align: center; } 
            .stats-grid { grid-template-columns: repeat(2, 1fr); } 
            .section { padding: 16px; } 
            th, td { padding: 8px 12px; font-size: 0.75rem; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .search-box { max-width: 100%; }
        }
        @media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo" onclick="window.location.href='dashboard-admin.php'">
            <div class="logo-icon"><i class="fas fa-utensils"></i></div>
            <div class="logo-text">CaFood Admin</div>
        </div>
        <div class="nav-menu">
            <a href="?tab=orders" class="nav-item <?php echo $activeTab == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="?tab=stands" class="nav-item <?php echo $activeTab == 'stands' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i> Stands
            </a>
            <a href="?tab=users" class="nav-item <?php echo $activeTab == 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
        </div>
        <div class="user-info">
            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if($message): ?>
            <div class="alert-success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- STATS CARDS (menampilkan ringkasan) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info"><h3><?php echo $totalOrders; ?></h3><p>Total Orders</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info"><h3><?php echo formatRupiah($revenue); ?></h3><p>Total Revenue</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><h3><?php echo $pendingOrders; ?></h3><p>Pending Orders</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><h3><?php echo $completedOrders; ?></h3><p>Completed Orders</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-store"></i></div>
                <div class="stat-info"><h3><?php echo $totalStands; ?></h3><p>Total Stands</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info"><h3><?php echo $totalUsers; ?></h3><p>Total Users</p></div>
            </div>
        </div>

        <!-- ORDERS TAB (Dashboard & Order digabung jadi Orders) -->
        <?php if($activeTab == 'orders'): ?>
        <div class="section">
            <div class="section-title"><i class="fas fa-shopping-cart"></i> All Orders</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Stand</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($allOrders)): ?>
                            <tr><td colspan="8" style="text-align: center;">Belum ada pesanan</td>79
                        <?php else: ?>
                            <?php foreach($allOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_number'] ?? $order['id']; ?></td>
                                <!-- Nama Stand bisa diklik -->
                                <td>
                                    <?php if(!empty($order['stand_id'])): ?>
                                    <a href="?tab=stands&stand_id=<?php echo $order['stand_id']; ?>&from_order=1" class="stand-name-link">
                                        <i class="fas fa-store"></i> <?php echo htmlspecialchars($order['stand_name'] ?? '-'); ?>
                                    </a>
                                    <?php else: ?>
                                    <?php echo htmlspecialchars($order['stand_name'] ?? '-'); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($order['user_name'] ?? 'Guest'); ?></td>
                                <td><?php echo formatRupiah(getOrderTotal($order)); ?></td>
                                <td><span class="status-badge <?php echo getStatusClass($order['status']); ?>"><?php echo getStatusLabel($order['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($order['payment_method'] ?? 'cash'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" class="btn-status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo ($order['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                            <option value="confirmed" <?php echo ($order['status'] ?? '') == 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                            <option value="processing" <?php echo ($order['status'] ?? '') == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                            <option value="ready" <?php echo ($order['status'] ?? '') == 'ready' ? 'selected' : ''; ?>>Siap Diambil</option>
                                            <option value="completed" <?php echo ($order['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="cancelled" <?php echo ($order['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                        <input type="hidden" name="update_order_status" value="1">
                                    </form>
                                    <button class="btn-action btn-edit" onclick="viewOrderDetail(<?php echo $order['id']; ?>)">Detail</button>
                                    <a href="?tab=orders&delete_order=<?php echo $order['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin hapus pesanan ini?')">Hapus</a>
                                 </td>
                             </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- STANDS TAB -->
        <?php if($activeTab == 'stands'): ?>
        <div class="section">
            <div class="section-title"><i class="fas fa-store"></i> All Stands</div>
            <div style="display: grid; grid-template-columns: 350px 1fr; gap: 24px;">
                <!-- Left Side: Stand List -->
                <div style="background: #f8fafc; border-radius: 20px; padding: 16px; max-height: 600px; overflow-y: auto;">
                    <h4 style="margin-bottom: 16px; color: #1e293b;">Daftar Stand</h4>
                    <?php foreach($stands as $stand): ?>
                    <div class="stand-card <?php echo $selectedStandId == $stand['id'] ? 'active' : ''; ?>" 
                         onclick="window.location.href='?tab=stands&stand_id=<?php echo $stand['id']; ?>'">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px;">
                            <div>
                                <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($stand['stand_name']); ?></div>
                                <div style="font-size: 0.7rem; color: #64748b;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($stand['owner_name'] ?? 'N/A'); ?>
                                </div>
                                <div style="font-size: 0.65rem; color: #6C4CF1; margin-top: 4px;">
                                    <i class="fas fa-utensils"></i> <?php echo $stand['menu_count']; ?> menu
                                </div>
                            </div>
                            <div>
                                <span class="status-badge <?php echo $stand['status'] == 'Open' ? 'badge-open' : 'badge-closed'; ?>">
                                    <?php echo $stand['status'] ?? 'Open'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Right Side: Menus from Selected Stand -->
                <div>
                    <?php if($selectedStand): ?>
                        <div class="stand-info-header">
                            <h3><i class="fas fa-store"></i> <?php echo htmlspecialchars($selectedStand['stand_name']); ?></h3>
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($selectedStand['description'] ?? 'Tidak ada deskripsi'); ?></p>
                            <p><i class="fas fa-clock"></i> Estimasi: <?php echo htmlspecialchars($selectedStand['estimasiWaktu'] ?? '15-30'); ?> menit</p>
                            <p><i class="fas fa-star" style="color: #fbbf24;"></i> Rating: <?php echo number_format($selectedStand['rating'] ?? 4.5, 1); ?></p>
                        </div>
                        
                        <h4 style="margin-bottom: 16px;"><i class="fas fa-utensils"></i> Daftar Menu</h4>
                        
                        <?php if(empty($standMenus)): ?>
                            <div class="empty-state">
                                <i class="fas fa-utensils"></i>
                                <p>Belum ada menu untuk stand ini</p>
                            </div>
                        <?php else: ?>
                            <div class="menu-grid">
                                <?php foreach($standMenus as $menu): ?>
                                <div class="menu-item">
                                    <div class="menu-image">
                                        <?php 
                                        $imageExists = !empty($menu['image']) && file_exists(__DIR__ . '/' . $menu['image']);
                                        if ($imageExists): ?>
                                            <img src="<?php echo htmlspecialchars($menu['image']); ?>" alt="<?php echo htmlspecialchars($menu['name']); ?>">
                                        <?php else: ?>
                                            <div class="menu-image-placeholder">
                                                <i class="fas fa-utensils"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="menu-info">
                                        <div class="menu-name"><?php echo htmlspecialchars($menu['name']); ?></div>
                                        <div class="menu-price"><?php echo formatRupiah($menu['price']); ?></div>
                                        <div class="menu-desc"><?php echo htmlspecialchars(substr($menu['description'] ?? 'Tidak ada deskripsi', 0, 60)); ?></div>
                                        <div style="margin-top: 8px;">
                                            <span class="status-badge <?php echo ($menu['available'] ?? 'Tersedia') == 'Tersedia' ? 'badge-open' : 'badge-closed'; ?>">
                                                <?php echo $menu['available'] ?? 'Tersedia'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Tombol Kembali ke Orders -->
                        <div style="margin-top: 24px; text-align: center;">
                            <a href="?tab=orders" class="btn-action btn-edit" style="padding: 10px 24px; text-decoration: none;">
                                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-store"></i>
                            <p>Pilih stand dari daftar di sebelah kiri untuk melihat menu</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- USERS TAB (dengan filter dan search) -->
        <?php if($activeTab == 'users'): ?>
        <div class="section">
            <div class="section-title"><i class="fas fa-users"></i> All Users</div>
            
            <!-- Filter & Search Bar -->
            <div class="filter-bar">
                <form method="GET" class="search-box" style="display: flex; gap: 10px;">
                    <input type="hidden" name="tab" value="users">
                    <input type="text" name="search" placeholder="Cari nama, email, atau telepon..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Cari</button>
                </form>
                
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="hidden" name="tab" value="users">
                    <select name="role" class="filter-select" onchange="this.form.submit()">
                        <option value="all" <?php echo $roleFilter == 'all' ? 'selected' : ''; ?>>Semua Role</option>
                        <option value="customer" <?php echo $roleFilter == 'customer' ? 'selected' : ''; ?>>Pelanggan</option>
                        <option value="stand" <?php echo $roleFilter == 'stand' ? 'selected' : ''; ?>>Pemilik Stand</option>
                        <option value="admin" <?php echo $roleFilter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    
                    <?php if(!empty($searchQuery) || !empty($roleFilter) && $roleFilter != 'all'): ?>
                    <a href="?tab=users" class="btn-action btn-edit" style="padding: 10px 20px; text-decoration: none;">Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($users)): ?>
                            <tr><td colspan="7" style="text-align: center;">Tidak ada user ditemukan</td>79
                        <?php else: ?>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td><span class="status-badge"><?php echo getRoleLabel($user['role']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['role'] !== 'admin'): ?>
                                    <a href="?tab=users&delete_user=<?php echo $user['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin hapus user ini?')">Delete</a>
                                    <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.7rem;">(Admin)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: white; border-radius: 28px; padding: 24px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;">
                <h3 id="modalTitle">Detail Pesanan</h3>
                <span onclick="closeOrderModal()" style="font-size: 1.5rem; cursor: pointer; color: #9ca3af;">&times;</span>
            </div>
            <div id="orderDetailContent"></div>
        </div>
    </div>

    <script>
        function viewOrderDetail(orderId) {
            fetch('get_order_detail.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <p><strong>Order Number:</strong> ${data.order.order_number || data.order.id}</p>
                            <p><strong>Stand:</strong> ${data.order.stand_name || '-'}</p>
                            <p><strong>Customer:</strong> ${data.order.customer_name || data.order.user_name || 'Guest'}</p>
                            <p><strong>Status:</strong> <span class="status-badge ${getStatusClass(data.order.status)}">${getStatusLabel(data.order.status)}</span></p>
                            <p><strong>Payment:</strong> ${data.order.payment_method || 'Cash'}</p>
                            <p><strong>Notes:</strong> ${data.order.notes || '-'}</p>
                            <p><strong>Date:</strong> ${new Date(data.order.created_at).toLocaleString('id-ID')}</p>
                            <div class="order-items">
                                <h4>Items:</h4>
                                ${data.items.map(item => `
                                    <div class="order-item">
                                        <span>${item.quantity}x ${item.menu_name}</span>
                                        <span>${formatRupiah(item.price)}</span>
                                    </div>
                                `).join('')}
                                <div class="order-item" style="font-weight: 700; border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 8px;">
                                    <span>Total</span>
                                    <span>${formatRupiah(data.order.total)}</span>
                                </div>
                            </div>
                        `;
                        document.getElementById('orderDetailContent').innerHTML = html;
                        document.getElementById('orderModal').style.display = 'flex';
                    } else {
                        alert('Gagal mengambil detail pesanan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil detail pesanan');
                });
        }
        
        function formatRupiah(price) {
            return 'Rp ' + Number(price).toLocaleString('id-ID');
        }
        
        function getStatusLabel(status) {
            const labels = {
                'pending': 'Menunggu',
                'confirmed': 'Dikonfirmasi',
                'processing': 'Diproses',
                'ready': 'Siap Diambil',
                'completed': 'Selesai',
                'cancelled': 'Dibatalkan'
            };
            return labels[status] || status;
        }
        
        function getStatusClass(status) {
            const classes = {
                'pending': 'status-pending',
                'confirmed': 'status-confirmed',
                'processing': 'status-processing',
                'ready': 'status-ready',
                'completed': 'status-completed',
                'cancelled': 'status-cancelled'
            };
            return classes[status] || 'status-pending';
        }
        
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>