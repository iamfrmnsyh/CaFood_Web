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
$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['name'] ?? '';

// Check if user is stand owner
if ($userRole !== 'stand') {
    header('Location: index.php');
    exit;
}

// Get stand data by user_id
$stmt = $pdo->prepare("SELECT * FROM stands WHERE user_id = ?");
$stmt->execute([$userId]);
$stand = $stmt->fetch(PDO::FETCH_ASSOC);

// If not found, try to get by name
if (!$stand) {
    $stmt = $pdo->prepare("SELECT * FROM stands WHERE stand_name LIKE ?");
    $stmt->execute(['%' . $userName . '%']);
    $stand = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$stand) {
    die("Anda belum memiliki stand. Hubungi admin!");
}

$standId = $stand['id'];
$standName = $stand['stand_name'];

// ========== BUAT FOLDER UPLOADS JIKA BELUM ADA ==========
$uploadDir = __DIR__ . '/uploads/menus/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Menu Operations
$successMessage = '';
$errorMessage = '';

// ========== SAVE MENU DENGAN UPLOAD GAMBAR (TANPA BATASAN UKURAN) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menu'])) {
    $menuId = $_POST['menu_id'] ?? null;
    $name = trim($_POST['name']);
    $price = (int)$_POST['price'];
    $description = trim($_POST['description']);
    $available = $_POST['available'];
    
    // Proses upload gambar
    $imagePath = '';
    $hasNewImage = false;
    
    if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['menu_image'];
        $fileTmp = $file['tmp_name'];
        $fileType = $file['type'];
        $fileError = $file['error'];
        
        // Validasi tidak ada error upload
        if ($fileError === 0) {
            // Validasi tipe file (HAPUS BATASAN UKURAN)
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif', 'image/bmp'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
            
            if (in_array($fileType, $allowedTypes) || in_array($fileExtension, $allowedExtensions)) {
                // Buat nama file unik
                $filename = time() . '_' . uniqid() . '.' . $fileExtension;
                $targetFile = $uploadDir . $filename;
                
                // Upload file (tanpa batasan ukuran)
                if (move_uploaded_file($fileTmp, $targetFile)) {
                    $imagePath = 'uploads/menus/' . $filename;
                    $hasNewImage = true;
                } else {
                    $errorMessage = "Gagal mengupload gambar!";
                }
            } else {
                $errorMessage = "Format file harus JPG, PNG, WEBP, GIF, atau BMP!";
            }
        } else {
            $errorMessage = "Error upload file!";
        }
    }
    
    if (empty($name) || $price <= 0) {
        $errorMessage = "Nama menu dan harga harus diisi!";
    } else {
        try {
            if ($menuId) {
                // UPDATE: cek apakah ada gambar baru
                if ($hasNewImage) {
                    // Hapus gambar lama
                    $stmt = $pdo->prepare("SELECT image FROM menus WHERE id = ?");
                    $stmt->execute([$menuId]);
                    $oldMenu = $stmt->fetch();
                    if ($oldMenu && !empty($oldMenu['image'])) {
                        $oldImagePath = __DIR__ . '/' . $oldMenu['image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE menus SET name = ?, price = ?, description = ?, available = ?, image = ?, updated_at = NOW() WHERE id = ? AND stand_id = ?");
                    $stmt->execute([$name, $price, $description, $available, $imagePath, $menuId, $standId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE menus SET name = ?, price = ?, description = ?, available = ?, updated_at = NOW() WHERE id = ? AND stand_id = ?");
                    $stmt->execute([$name, $price, $description, $available, $menuId, $standId]);
                }
                $successMessage = "Menu berhasil diupdate";
            } else {
                // INSERT baru
                $stmt = $pdo->prepare("INSERT INTO menus (stand_id, name, price, description, available, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$standId, $name, $price, $description, $available, $imagePath]);
                $successMessage = "Menu berhasil ditambahkan";
            }
            
            header("Location: dashboard-stand.php?tab=menus&success=" . urlencode($successMessage));
            exit;
        } catch(PDOException $e) {
            $errorMessage = "Gagal menyimpan menu: " . $e->getMessage();
        }
    }
}

// Update Order Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        $successMessage = "Status pesanan berhasil diupdate";
        header("Location: dashboard-stand.php?tab=orders&success=" . urlencode($successMessage));
        exit;
    } catch(PDOException $e) {
        $errorMessage = "Gagal mengupdate status: " . $e->getMessage();
    }
}

// Update Stand Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stand'])) {
    $standNameInput = trim($_POST['stand_name']);
    $description = trim($_POST['description']);
    $estimasiWaktu = trim($_POST['estimasiWaktu']);
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE stands SET stand_name = ?, description = ?, estimasiWaktu = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$standNameInput, $description, $estimasiWaktu, $status, $standId]);
        $successMessage = "Profil stand berhasil diupdate";
        // Refresh stand data
        $stmt = $pdo->prepare("SELECT * FROM stands WHERE id = ?");
        $stmt->execute([$standId]);
        $stand = $stmt->fetch(PDO::FETCH_ASSOC);
        $standName = $stand['stand_name'];
    } catch(PDOException $e) {
        $errorMessage = "Gagal mengupdate profil: " . $e->getMessage();
    }
}

// Delete Menu
if (isset($_GET['delete_menu'])) {
    $menuId = $_GET['delete_menu'];
    try {
        // Hapus gambar terlebih dahulu
        $stmt = $pdo->prepare("SELECT image FROM menus WHERE id = ? AND stand_id = ?");
        $stmt->execute([$menuId, $standId]);
        $menu = $stmt->fetch();
        if ($menu && !empty($menu['image'])) {
            $imagePath = __DIR__ . '/' . $menu['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ? AND stand_id = ?");
        $stmt->execute([$menuId, $standId]);
        $successMessage = "Menu berhasil dihapus";
        header("Location: dashboard-stand.php?tab=menus&success=" . urlencode($successMessage));
        exit;
    } catch(PDOException $e) {
        $errorMessage = "Gagal menghapus menu: " . $e->getMessage();
    }
}

// Get current tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Handle message dengan aman
$successMessage = isset($_GET['success']) ? htmlspecialchars(trim($_GET['success'])) : $successMessage;
$errorMessage = isset($_GET['error']) ? htmlspecialchars(trim($_GET['error'])) : $errorMessage;

// Fetch menus
$menus = [];
try {
    $menusStmt = $pdo->prepare("SELECT * FROM menus WHERE stand_id = ? ORDER BY created_at DESC");
    $menusStmt->execute([$standId]);
    $menus = $menusStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorMessage = "Gagal mengambil data menu: " . $e->getMessage();
}

// Fetch orders
$orders = [];
try {
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM orders");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $totalColumn = 'total';
    if (in_array('total_amount', $columns)) {
        $totalColumn = 'total_amount';
    }
    
    $ordersStmt = $pdo->prepare("
        SELECT DISTINCT 
            o.id,
            o.user_id,
            o.{$totalColumn} as total,
            o.status,
            o.created_at,
            o.updated_at,
            o.order_number,
            o.notes,
            u.name as customer_name
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN menus m ON oi.menu_id = m.id
        WHERE m.stand_id = ? OR o.stand_id = ?
        ORDER BY o.created_at DESC
    ");
    $ordersStmt->execute([$standId, $standId]);
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $orders = [];
}

// Calculate statistics
$today = date('Y-m-d');
$todayOrders = array_filter($orders, function($order) use ($today) {
    if (!isset($order['created_at'])) return false;
    return date('Y-m-d', strtotime($order['created_at'])) === $today;
});

$todayRevenue = 0;
foreach ($todayOrders as $order) {
    $total = isset($order['total']) && is_numeric($order['total']) ? (float)$order['total'] : 0;
    $todayRevenue += $total;
}

$pendingOrders = 0;
foreach ($orders as $order) {
    if (($order['status'] ?? '') === 'pending') $pendingOrders++;
}

$totalRevenue = 0;
foreach ($orders as $order) {
    $total = isset($order['total']) && is_numeric($order['total']) ? (float)$order['total'] : 0;
    $totalRevenue += $total;
}

$completedOrders = 0;
$completedRevenue = 0;
foreach ($orders as $order) {
    $status = $order['status'] ?? '';
    if ($status === 'completed' || $status === 'ready') {
        $completedOrders++;
        $total = isset($order['total']) && is_numeric($order['total']) ? (float)$order['total'] : 0;
        $completedRevenue += $total;
    }
}

$recentOrders = array_slice($orders, 0, 10);

function formatRupiah($price) {
    if (!$price || !is_numeric($price)) return 'Rp 0';
    return 'Rp ' . number_format($price, 0, ',', '.');
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
        'cancelled' => 'status-pending'
    ];
    return $classes[$status] ?? 'status-pending';
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ========== HITUNG PESAN CHAT BELUM DIBACA UNTUK BADGE ==========
$unreadChatCount = 0;
try {
    // Hitung notifikasi chat yang belum dibaca untuk stand ini
    $stmt = $pdo->prepare("
        SELECT COUNT(cn.id) as total 
        FROM chat_notifications cn 
        JOIN chat_rooms cr ON cn.room_id = cr.id 
        WHERE cr.stand_id = ? AND cn.user_id = ? AND cn.is_read = 0
    ");
    $stmt->execute([$standId, $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unreadChatCount = $result['total'] ?? 0;
} catch(PDOException $e) {
    // Jika tabel chat_notifications belum ada, abaikan
    $unreadChatCount = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Dashboard Stand</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #F0F4F8;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #1A1A2E 0%, #2A2A4A 100%);
            color: white;
            z-index: 100;
            transition: all 0.3s;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar-header {
            padding: 30px 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar-logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border: 2px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 15px rgba(108, 76, 241, 0.4);
        }

        .sidebar-title {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .sidebar-subtitle {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.3s;
            margin: 4px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: rgba(255,255,255,0.75);
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.12);
            color: white;
        }

        .menu-item.active {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            box-shadow: 0 4px 12px rgba(108, 76, 241, 0.3);
        }

        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
        }

        /* ===== BADGE UNTUK NOTIFIKASI CHAT ===== */
        .menu-badge {
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: auto;
            min-width: 20px;
            text-align: center;
            line-height: 1.4;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .menu-badge.zero {
            display: none;
        }

        .main-content {
            margin-left: 280px;
            padding: 24px 32px;
            min-height: 100vh;
        }

        .stand-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(108, 76, 241, 0.1);
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1A1A2E;
        }

        .stand-name {
            font-size: 0.9rem;
            color: #6C4CF1;
            background: rgba(108, 76, 241, 0.1);
            padding: 6px 16px;
            border-radius: 30px;
            font-weight: 600;
        }

        .logout-btn {
            background: #EF4444;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.8rem;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #6C4CF1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6B7280;
            margin-top: 4px;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
            color: #6C4CF1;
        }

        .table-container {
            background: white;
            border-radius: 24px;
            padding: 24px;
            overflow-x: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-add {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(108, 76, 241, 0.3);
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 76, 241, 0.4);
        }

        .btn-edit {
            background: #6C4CF1;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
            margin-right: 6px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-delete {
            background: #EF4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-save {
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: all 0.3s;
        }

        .menu-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.1);
            border-color: #6C4CF1;
        }

        .menu-image {
            width: 100%;
            height: 160px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-image-placeholder {
            font-size: 3rem;
            color: rgba(255,255,255,0.7);
        }

        .menu-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1A1A2E;
            margin-bottom: 4px;
        }

        .menu-price {
            font-weight: 700;
            color: #6C4CF1;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .menu-desc {
            font-size: 0.75rem;
            color: #6B7280;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .order-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .order-card:hover {
            background: white;
            border-color: #6C4CF1;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E2E8F0;
        }

        .order-number {
            font-weight: 700;
            color: #6C4CF1;
            font-size: 0.85rem;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-pending { background: #FEF3C7; color: #D97706; }
        .status-confirmed { background: #DBEAFE; color: #2563EB; }
        .status-processing { background: #E0E7FF; color: #4F46E5; }
        .status-ready { background: #D1FAE5; color: #059669; }
        .status-completed { background: #A7F3D0; color: #047857; }
        .status-cancelled { background: #FEE2E2; color: #DC2626; }

        .btn-status {
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.2s;
            margin-right: 6px;
        }
        
        .btn-status:hover {
            transform: translateY(-2px);
        }

        .btn-confirm { background: #059669; color: white; }
        .btn-process { background: #2563EB; color: white; }
        .btn-ready { background: #6C4CF1; color: white; }
        .btn-complete { background: #6B7280; color: white; }

        .profile-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #6C4CF1;
            box-shadow: 0 0 0 3px rgba(108, 76, 241, 0.1);
        }

        .alert-success {
            background: #D1FAE5;
            color: #059669;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .modal {
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

        .modal-content {
            background: white;
            border-radius: 28px;
            padding: 28px;
            width: 90%;
            max-width: 500px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #E2E8F0;
        }

        .close-modal {
            font-size: 1.5rem;
            cursor: pointer;
            color: #9CA3AF;
        }

        /* Style untuk preview gambar */
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 16px;
            overflow: hidden;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            border: 2px dashed #CBD5E1;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview .placeholder {
            text-align: center;
            color: #94A3B8;
        }

        .image-preview .placeholder i {
            font-size: 2rem;
            margin-bottom: 8px;
            display: block;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); position: fixed; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .card-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-store"></i></div>
            <div class="sidebar-title">CaFood Stand</div>
            <div class="sidebar-subtitle"><?php echo htmlspecialchars(substr($standName, 0, 20)); ?></div>
        </div>
        <div class="sidebar-menu">
            <a href="?tab=dashboard" class="menu-item <?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </a>
            <a href="?tab=menus" class="menu-item <?php echo $activeTab == 'menus' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> <span>Manage Menus</span>
            </a>
            <a href="?tab=orders" class="menu-item <?php echo $activeTab == 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> <span>Kelola Pesanan</span>
            </a>
            <a href="?tab=profile" class="menu-item <?php echo $activeTab == 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-store"></i> <span>Profil Stand</span>
            </a>
            <a href="?tab=reports" class="menu-item <?php echo $activeTab == 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> <span>Laporan</span>
            </a>
            
            <!-- ===== MENU CHAT DENGAN BADGE ===== -->
            <a href="chat_stand.php" class="menu-item <?php echo $activeTab == 'chat' ? 'active' : ''; ?>">
                <i class="fas fa-comment-dots"></i> 
                <span>Pesan Masuk</span>
                <span class="menu-badge <?php echo $unreadChatCount <= 0 ? 'zero' : ''; ?>">
                    <?php echo $unreadChatCount > 0 ? $unreadChatCount : '0'; ?>
                </span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="stand-header">
            <div class="header-title">
                <i class="fas fa-store" style="color: #6C4CF1; margin-right: 10px;"></i>
                Stand Dashboard
            </div>
            <div class="stand-name">
                <i class="fas fa-star"></i> <?php echo htmlspecialchars($standName); ?>
            </div>
            <a href="?logout=1" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if(!empty($successMessage)): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($errorMessage)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- DASHBOARD TAB -->
        <?php if($activeTab == 'dashboard'): ?>
        <div id="dashboardTab">
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-number"><?php echo count($todayOrders); ?></div>
                        <div class="stat-label">Order Hari Ini</div>
                    </div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-number"><?php echo formatRupiah($todayRevenue); ?></div>
                        <div class="stat-label">Pendapatan Hari Ini</div>
                    </div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-number"><?php echo $pendingOrders; ?></div>
                        <div class="stat-label">Menunggu Konfirmasi</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-number"><?php echo count($menus); ?></div>
                        <div class="stat-label">Total Menu</div>
                    </div>
                    <div class="stat-icon">🍽️</div>
                </div>
            </div>
            
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Pesanan Terbaru</h2>
                </div>
                <?php if(empty($recentOrders)): ?>
                    <div style="text-align:center; padding:40px; color:#94A3B8;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 12px; display: block;"></i>
                        Belum ada pesanan
                    </div>
                <?php else: ?>
                    <?php foreach($recentOrders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number">#<?php echo htmlspecialchars($order['order_number'] ?? $order['id']); ?></div>
                            <div class="status-badge <?php echo getStatusClass($order['status'] ?? 'pending'); ?>">
                                <?php echo getStatusLabel($order['status'] ?? 'pending'); ?>
                            </div>
                        </div>
                        <div><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></div>
                        <div><strong>Total:</strong> <?php echo formatRupiah($order['total'] ?? 0); ?></div>
                        <div><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- MANAGE MENUS TAB -->
        <?php if($activeTab == 'menus'): ?>
        <div id="menusTab">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-utensils"></i> Daftar Menu</h2>
                    <button class="btn-add" onclick="openMenuModal()">
                        <i class="fas fa-plus"></i> Tambah Menu
                    </button>
                </div>
                <div class="card-grid">
                    <?php if(empty($menus)): ?>
                        <div style="text-align:center; padding:60px; color:#94A3B8; grid-column:1/-1;">
                            <i class="fas fa-utensils" style="font-size: 3rem; margin-bottom: 12px; display: block;"></i>
                            Belum ada menu. Klik "Tambah Menu" untuk menambahkan.
                        </div>
                    <?php else: ?>
                        <?php foreach($menus as $menu): ?>
                        <div class="menu-card">
                            <div class="menu-image">
                                <?php 
                                $imageExists = !empty($menu['image']) && file_exists(__DIR__ . '/' . $menu['image']);
                                if ($imageExists): ?>
                                    <img src="<?php echo htmlspecialchars($menu['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($menu['name']); ?>"
                                         onerror="this.parentElement.innerHTML='<div class=\'menu-image-placeholder\'>🍽️</div>'">
                                <?php else: ?>
                                    <div class="menu-image-placeholder">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="menu-name"><?php echo htmlspecialchars($menu['name']); ?></div>
                            <div class="menu-price"><?php echo formatRupiah($menu['price']); ?></div>
                            <div class="menu-desc"><?php echo htmlspecialchars($menu['description'] ?: 'Tidak ada deskripsi'); ?></div>
                            <div style="margin-bottom: 12px;">
                                <span class="status-badge <?php echo ($menu['available'] ?? 'Tersedia') == 'Tersedia' ? 'status-ready' : 'status-pending'; ?>">
                                    <?php echo $menu['available'] ?? 'Tersedia'; ?>
                                </span>
                            </div>
                            <div>
                                <button class="btn-edit" onclick="editMenu(<?php echo $menu['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="?tab=menus&delete_menu=<?php echo $menu['id']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus menu ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- KELOLA PESANAN TAB -->
        <?php if($activeTab == 'orders'): ?>
        <div id="ordersTab">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-clipboard-list"></i> Daftar Pesanan</h2>
                </div>
                <?php if(empty($orders)): ?>
                    <div style="text-align:center; padding:60px; color:#94A3B8;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 12px; display: block;"></i>
                        Belum ada pesanan
                    </div>
                <?php else: ?>
                    <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number">#<?php echo htmlspecialchars($order['order_number'] ?? $order['id']); ?></div>
                            <div class="status-badge <?php echo getStatusClass($order['status'] ?? 'pending'); ?>">
                                <?php echo getStatusLabel($order['status'] ?? 'pending'); ?>
                            </div>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <div><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></div>
                            <div><strong>Total:</strong> <?php echo formatRupiah($order['total'] ?? 0); ?></div>
                            <div><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')); ?></div>
                        </div>
                        <div class="order-actions">
                            <?php if(($order['status'] ?? '') == 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="new_status" value="confirmed">
                                    <button type="submit" name="update_order_status" class="btn-status btn-confirm">
                                        <i class="fas fa-check"></i> Konfirmasi
                                    </button>
                                </form>
                            <?php elseif(($order['status'] ?? '') == 'confirmed'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="new_status" value="processing">
                                    <button type="submit" name="update_order_status" class="btn-status btn-process">
                                        <i class="fas fa-cog"></i> Proses
                                    </button>
                                </form>
                            <?php elseif(($order['status'] ?? '') == 'processing'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="new_status" value="ready">
                                    <button type="submit" name="update_order_status" class="btn-status btn-ready">
                                        <i class="fas fa-box"></i> Siap Diambil
                                    </button>
                                </form>
                            <?php elseif(($order['status'] ?? '') == 'ready'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="new_status" value="completed">
                                    <button type="submit" name="update_order_status" class="btn-status btn-complete">
                                        <i class="fas fa-check-double"></i> Selesai
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- PROFIL STAND TAB -->
        <?php if($activeTab == 'profile'): ?>
        <div id="profileTab">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-store"></i> Profil Stand</h2>
                </div>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label>Nama Stand</label>
                        <input type="text" name="stand_name" class="form-control" value="<?php echo htmlspecialchars($stand['stand_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($stand['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Estimasi Waktu (menit)</label>
                        <input type="text" name="estimasiWaktu" class="form-control" value="<?php echo htmlspecialchars($stand['estimasiWaktu'] ?? '15-30'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="Open" <?php echo ($stand['status'] ?? 'Open') == 'Open' ? 'selected' : ''; ?>>Open (Buka)</option>
                            <option value="Closed" <?php echo ($stand['status'] ?? 'Open') == 'Closed' ? 'selected' : ''; ?>>Closed (Tutup)</option>
                        </select>
                    </div>
                    <button type="submit" name="update_stand" class="btn-save">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- LAPORAN TAB -->
        <?php if($activeTab == 'reports'): ?>
        <div id="reportsTab">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Laporan Penjualan</h2>
                </div>
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <div>
                            <div class="stat-number"><?php echo count($orders); ?></div>
                            <div class="stat-label">Total Pesanan</div>
                        </div>
                        <div class="stat-icon">📋</div>
                    </div>
                    <div class="stat-card">
                        <div>
                            <div class="stat-number"><?php echo $completedOrders; ?></div>
                            <div class="stat-label">Pesanan Selesai</div>
                        </div>
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-card">
                        <div>
                            <div class="stat-number"><?php echo formatRupiah($totalRevenue); ?></div>
                            <div class="stat-label">Total Pendapatan</div>
                        </div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-card">
                        <div>
                            <div class="stat-number"><?php echo formatRupiah($completedRevenue); ?></div>
                            <div class="stat-label">Pendapatan Selesai</div>
                        </div>
                        <div class="stat-icon">💵</div>
                    </div>
                </div>
                
                <h3 style="margin-bottom: 16px; margin-top: 24px;">Detail Pesanan Selesai</h3>
                <div style="overflow-x: auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:12px; border-bottom:2px solid #E2E8F0;">Order #</th>
                                <th style="text-align:left; padding:12px; border-bottom:2px solid #E2E8F0;">Customer</th>
                                <th style="text-align:right; padding:12px; border-bottom:2px solid #E2E8F0;">Total</th>
                                <th style="text-align:left; padding:12px; border-bottom:2px solid #E2E8F0;">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $completedOrdersList = array_filter($orders, function($order) {
                                return ($order['status'] ?? '') === 'completed';
                            });
                            if(empty($completedOrdersList)): 
                            ?>
                                <tr>
                                    <td colspan="4" style="padding:40px; text-align:center; color:#94A3B8;">
                                        Belum ada pesanan selesai
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($completedOrdersList as $order): ?>
                                <tr>
                                    <td style="padding:12px; border-bottom:1px solid #E2E8F0;">#<?php echo htmlspecialchars($order['order_number'] ?? $order['id']); ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #E2E8F0;"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                                    <td style="text-align:right; padding:12px; border-bottom:1px solid #E2E8F0;"><?php echo formatRupiah($order['total'] ?? 0); ?></td>
                                    <td style="padding:12px; border-bottom:1px solid #E2E8F0;"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? 'now')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========== MENU MODAL - FORM TAMBAH/EDIT MENU ========== -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="menuModalTitle">Tambah Menu</h3>
                <span class="close-modal" onclick="closeModal('menuModal')">&times;</span>
            </div>
            <!-- PENTING: enctype="multipart/form-data" untuk upload file -->
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="menu_id" id="menuId">
                
                <div class="form-group">
                    <label>Gambar Menu</label>
                    <input type="file" name="menu_image" id="menuImage" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif,image/bmp" onchange="previewMenuImage(event)">
                    <div id="menuImagePreview" class="image-preview">
                        <div class="placeholder">
                            <i class="fas fa-image"></i>
                            <span>Belum ada gambar</span>
                        </div>
                    </div>
                    <small>Format: JPG, PNG, WEBP, GIF, BMP (Tanpa batasan ukuran)</small>
                </div>
                
                <div class="form-group">
                    <label>Nama Menu *</label>
                    <input type="text" name="name" id="menuName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" name="price" id="menuPrice" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" id="menuDesc" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="available" id="menuAvailable" class="form-select">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                    </select>
                </div>
                
                <button type="submit" name="save_menu" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Menu
                </button>
            </form>
        </div>
    </div>

    <script>
        // ========== FUNGSI MODAL ==========
        function openMenuModal() {
            document.getElementById('menuModal').style.display = 'flex';
            document.getElementById('menuModalTitle').innerText = 'Tambah Menu';
            document.getElementById('menuId').value = '';
            document.getElementById('menuName').value = '';
            document.getElementById('menuPrice').value = '';
            document.getElementById('menuDesc').value = '';
            document.getElementById('menuAvailable').value = 'Tersedia';
            
            // Reset preview
            const preview = document.getElementById('menuImagePreview');
            preview.innerHTML = '<div class="placeholder"><i class="fas fa-image"></i><span>Belum ada gambar</span></div>';
            document.getElementById('menuImage').value = '';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // ========== EDIT MENU - LOAD DATA KE MODAL ==========
        function editMenu(id) {
            <?php foreach($menus as $menu): ?>
            if (id == <?php echo $menu['id']; ?>) {
                document.getElementById('menuId').value = <?php echo $menu['id']; ?>;
                document.getElementById('menuName').value = '<?php echo addslashes($menu['name']); ?>';
                document.getElementById('menuPrice').value = <?php echo $menu['price']; ?>;
                document.getElementById('menuDesc').value = '<?php echo addslashes($menu['description'] ?? ''); ?>';
                document.getElementById('menuAvailable').value = '<?php echo $menu['available']; ?>';
                
                // Tampilkan gambar yang sudah ada
                const preview = document.getElementById('menuImagePreview');
                <?php if(!empty($menu['image']) && file_exists(__DIR__ . '/' . $menu['image'])): ?>
                preview.innerHTML = '<img src="<?php echo $menu['image']; ?>" alt="Preview">';
                <?php else: ?>
                preview.innerHTML = '<div class="placeholder"><i class="fas fa-image"></i><span>Belum ada gambar</span></div>';
                <?php endif; ?>
                
                document.getElementById('menuModalTitle').innerText = 'Edit Menu';
                document.getElementById('menuModal').style.display = 'flex';
            }
            <?php endforeach; ?>
        }
        
        // ========== PREVIEW GAMBAR SEBELUM UPLOAD (TANPA BATASAN UKURAN) ==========
        function previewMenuImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('menuImagePreview');
            
            if (file) {
                // HAPUS VALIDASI UKURAN - TIDAK ADA BATASAN
                // Hanya validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'image/gif', 'image/bmp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file harus JPG, PNG, WEBP, GIF, atau BMP!');
                    event.target.value = '';
                    preview.innerHTML = '<div class="placeholder"><i class="fas fa-image"></i><span>Belum ada gambar</span></div>';
                    return;
                }
                
                // Preview gambar (tanpa batasan ukuran)
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<div class="placeholder"><i class="fas fa-image"></i><span>Belum ada gambar</span></div>';
            }
        }
        
        // ========== TUTUP MODAL SAAT KLIK DI LUAR ==========
        window.onclick = function(event) {
            const modal = document.getElementById('menuModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>