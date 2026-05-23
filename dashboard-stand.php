<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Dashboard Stand</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
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
            background: linear-gradient(180deg, #1E3A5F 0%, #2C5282 100%);
            color: white;
            z-index: 100;
            transition: all 0.3s;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
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
            background: linear-gradient(135deg, #3182CE, #4299E1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
            color: rgba(255,255,255,0.75);
            margin: 4px 12px;
            border-radius: 12px;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.12);
            color: white;
        }

        .menu-item.active {
            background: #3182CE;
            color: white;
            box-shadow: 0 4px 12px rgba(49,130,206,0.3);
        }

        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
        }

        .main-content {
            margin-left: 280px;
            padding: 24px 32px;
            min-height: 100vh;
        }

        .stand-header {
            background: white;
            border-radius: 20px;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1E3A5F;
        }

        .stand-name {
            font-size: 0.9rem;
            color: #3182CE;
            background: #E8F0FE;
            padding: 6px 16px;
            border-radius: 30px;
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
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #3182CE;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6B7280;
            margin-top: 4px;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.7;
            color: #3182CE;
        }

        .table-container {
            background: white;
            border-radius: 24px;
            padding: 24px;
            overflow-x: auto;
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
            background: linear-gradient(135deg, #3182CE, #4299E1);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .menu-image {
            width: 100%;
            height: 160px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 12px;
            background: #F1F5F9;
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
            color: #94A3B8;
        }

        .menu-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1E3A5F;
            margin-bottom: 4px;
        }

        .menu-price {
            font-weight: 700;
            color: #3182CE;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .menu-desc {
            font-size: 0.75rem;
            color: #6B7280;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
            margin-right: 6px;
        }

        .btn-edit { background: #3182CE; color: white; }
        .btn-delete { background: #EF4444; color: white; }

        .order-card {
            background: white;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #E2E8F0;
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
            color: #3182CE;
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

        .btn-status {
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .btn-confirm { background: #059669; color: white; }
        .btn-process { background: #2563EB; color: white; }
        .btn-ready { background: #D97706; color: white; }
        .btn-complete { background: #6B7280; color: white; }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .report-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            border: 1px solid #E2E8F0;
        }

        .report-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #3182CE;
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
            padding: 24px;
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

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            font-size: 0.9rem;
            outline: none;
        }

        .form-control:focus {
            border-color: #3182CE;
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3182CE, #4299E1);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 700;
            margin-top: 12px;
        }

        .image-preview {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 8px;
            border: 1px solid #E2E8F0;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #1F2937;
            color: white;
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.8rem;
            z-index: 1100;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-utensils"></i></div>
            <div class="sidebar-title">CaFood Stand</div>
            <div class="sidebar-subtitle" id="standNameSidebar">Loading...</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-tab="dashboard">
                <i class="fas fa-chart-line"></i> <span>Dashboard</span>
            </div>
            <div class="menu-item" data-tab="menus">
                <i class="fas fa-utensils"></i> <span>Manage Menus</span>
            </div>
            <div class="menu-item" data-tab="orders">
                <i class="fas fa-truck"></i> <span>Kelola Pesanan</span>
            </div>
            <div class="menu-item" data-tab="reports">
                <i class="fas fa-chart-bar"></i> <span>Laporan</span>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="stand-header">
            <div class="header-title">
                <i class="fas fa-store" style="color: #3182CE; margin-right: 10px;"></i>
                Stand Dashboard
            </div>
            <div class="stand-name" id="standNameHeader">Loading...</div>
            <button class="logout-btn" onclick="handleLogout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>

        <div id="dashboardTab">
            <div class="stats-grid" id="statsGrid"></div>
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-clock"></i> Pesanan Terbaru</h2>
                </div>
                <div id="recentOrders"></div>
            </div>
        </div>

        <div id="menusTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-utensils"></i> Daftar Menu</h2>
                    <button class="btn-add" onclick="openMenuModal()">
                        <i class="fas fa-plus"></i> Tambah Menu
                    </button>
                </div>
                <div id="menusList" class="card-grid"></div>
            </div>
        </div>

        <div id="ordersTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-clipboard-list"></i> Daftar Pesanan</h2>
                </div>
                <div id="ordersList"></div>
            </div>
        </div>

        <div id="reportsTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <h2><i class="fas fa-chart-bar"></i> Laporan Penjualan</h2>
                </div>
                <div class="report-grid" id="reportStats"></div>
            </div>
        </div>
    </div>

    <!-- Menu Modal -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="menuModalTitle">Tambah Menu</h3>
                <span class="close-modal" onclick="closeModal('menuModal')">&times;</span>
            </div>
            <form id="menuForm">
                <input type="hidden" id="menuId">
                <div class="form-group">
                    <label>Gambar Menu</label>
                    <input type="file" id="menuImage" accept="image/*" onchange="previewMenuImage(event)">
                    <div id="menuImagePreview" class="image-preview">
                        <div class="menu-image-placeholder">🍽️</div>
                    </div>
                    <input type="hidden" id="menuImageBase64">
                    <small>Format: JPG, PNG (Max 2MB)</small>
                </div>
                <div class="form-group">
                    <label>Nama Menu *</label>
                    <input type="text" id="menuName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp) *</label>
                    <input type="number" id="menuPrice" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="menuDesc" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="menuAvailable" class="form-select">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Tidak">Tidak Tersedia</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Simpan</button>
            </form>
        </div>
    </div>

    <script type="module">
        import { db, auth, collection, doc, getDoc, getDocs, addDoc, updateDoc, deleteDoc, query, where, onAuthStateChanged, signOut } from './js/firebase-config.js';

        let currentUser = null;
        let currentStand = null;
        let allMenus = [];
        let allOrders = [];

        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }

        function showToast(msg, isError = false) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isError ? '#EF4444' : '#10B981';
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${msg}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        window.previewMenuImage = function(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showToast('Ukuran gambar maksimal 2MB!', true);
                    event.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.getElementById('menuImagePreview');
                    previewDiv.innerHTML = `<img src="${e.target.result}">`;
                    document.getElementById('menuImageBase64').value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        async function loadStand() {
            if (!currentUser) return;

            try {
                const standsRef = collection(db, "stands");
                const q = query(standsRef, where("ownerId", "==", currentUser.uid));
                const snapshot = await getDocs(q);

                if (!snapshot.empty) {
                    const standDoc = snapshot.docs[0];
                    currentStand = { id: standDoc.id, ...standDoc.data() };

                    const standName = currentStand.nama || currentStand.name || 'Stand Saya';
                    document.getElementById('standNameSidebar').textContent = standName;
                    document.getElementById('standNameHeader').textContent = standName;

                    await loadMenus();
                    await loadOrders();
                    updateDashboard();
                } else {
                    document.getElementById('standNameSidebar').textContent = 'Tidak ada stand';
                    document.getElementById('standNameHeader').textContent = 'Tidak ada stand';
                    showToast('Anda belum memiliki stand. Hubungi admin!', true);
                }
            } catch (error) {
                console.error("Error loading stand:", error);
            }
        }

        async function loadMenus() {
            if (!currentStand) return;

            try {
                const menusRef = collection(db, "menus");
                const q = query(menusRef, where("standId", "==", currentStand.id));
                const snapshot = await getDocs(q);
                allMenus = snapshot.docs.map(doc => ({
                    id: doc.id,
                    ...doc.data(),
                    name: doc.data().nama || doc.data().name || 'Menu',
                    price: doc.data().harga || doc.data().price || 0,
                    description: doc.data().deskripsi || doc.data().description || '',
                    image: doc.data().gambar || doc.data().image || doc.data().gambarUrl || null,
                    available: doc.data().status || doc.data().available || 'Tersedia'
                }));
                renderMenus();
            } catch (error) {
                console.error("Error loading menus:", error);
            }
        }

        async function loadOrders() {
            if (!currentStand) return;

            try {
                const ordersRef = collection(db, "orders");
                const q = query(ordersRef, where("standId", "==", currentStand.id));
                const snapshot = await getDocs(q);
                allOrders = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
                renderOrders();
                updateDashboard();
            } catch (error) {
                console.error("Error loading orders:", error);
            }
        }

        function renderMenus() {
            const container = document.getElementById('menusList');
            if (!container) return;

            if (allMenus.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:40px;">Belum ada menu. Klik "Tambah Menu" untuk menambahkan.</div>';
                return;
            }

            container.innerHTML = allMenus.map(menu => `
                <div class="menu-card">
                    <div class="menu-image">
                        ${menu.image ?
                            `<img src="${menu.image}" alt="${menu.name}" onerror="this.parentElement.innerHTML='<div class=\\'menu-image-placeholder\\'>🍽️</div>'">` :
                            '<div class="menu-image-placeholder">🍽️</div>'
                        }
                    </div>
                    <div class="menu-name">${menu.name}</div>
                    <div class="menu-price">${formatRupiah(menu.price)}</div>
                    <div class="menu-desc">${menu.description || 'Tidak ada deskripsi'}</div>
                    <div style="margin-bottom: 12px;">
                        <span class="status-badge ${menu.available === 'Tersedia' ? 'status-ready' : 'status-pending'}">${menu.available}</span>
                    </div>
                    <div>
                        <button class="action-btn btn-edit" onclick="editMenu('${menu.id}')">Edit</button>
                        <button class="action-btn btn-delete" onclick="deleteMenu('${menu.id}')">Hapus</button>
                    </div>
                </div>
            `).join('');
        }

        function renderOrders() {
            const container = document.getElementById('ordersList');
            if (!container) return;

            if (allOrders.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:40px;">Belum ada pesanan</div>';
                return;
            }

            const statusLabels = {
                'pending': 'Menunggu',
                'confirmed': 'Dikonfirmasi',
                'processing': 'Diproses',
                'ready': 'Siap Diambil',
                'completed': 'Selesai'
            };

            container.innerHTML = allOrders
                .sort((a,b) => new Date(b.createdAt) - new Date(a.createdAt))
                .map(order => `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">${order.orderNumber || 'ORD-XXXX'}</div>
                        <div class="status-badge status-${order.status}">${statusLabels[order.status] || order.status}</div>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <div><strong>Customer:</strong> ${order.customerName || '-'}</div>
                        <div><strong>Meja:</strong> ${order.tableNumber || '-'}</div>
                        <div><strong>Menu:</strong> ${order.items?.map(i => `${i.quantity}x ${i.name}`).join(', ') || '-'}</div>
                    </div>
                    <div style="font-weight: 700; margin-bottom: 12px;">Total: ${formatRupiah(order.total || 0)}</div>
                    <div class="order-actions">
                        ${getOrderActions(order)}
                    </div>
                </div>
            `).join('');
        }

        function getOrderActions(order) {
            switch(order.status) {
                case 'pending':
                    return `<button class="btn-status btn-confirm" onclick="updateOrderStatus('${order.id}', 'confirmed')">Konfirmasi</button>`;
                case 'confirmed':
                    return `<button class="btn-status btn-process" onclick="updateOrderStatus('${order.id}', 'processing')">Proses</button>`;
                case 'processing':
                    return `<button class="btn-status btn-ready" onclick="updateOrderStatus('${order.id}', 'ready')">Siap Diambil</button>`;
                case 'ready':
                    return `<button class="btn-status btn-complete" onclick="updateOrderStatus('${order.id}', 'completed')">Selesai</button>`;
                default:
                    return '';
            }
        }

        window.updateOrderStatus = async function(orderId, newStatus) {
            try {
                const orderRef = doc(db, "orders", orderId);
                await updateDoc(orderRef, { status: newStatus, updatedAt: new Date().toISOString() });
                showToast(`Status pesanan diubah menjadi ${newStatus}`);
                await loadOrders();
            } catch (error) {
                showToast('Gagal mengupdate status', true);
            }
        };

        function updateDashboard() {
            if (!currentStand) return;

            const today = new Date().toISOString().split('T')[0];
            const todayOrders = allOrders.filter(o => o.createdAt?.split('T')[0] === today);
            const totalRevenue = allOrders.reduce((sum, o) => sum + (o.total || 0), 0);
            const pendingOrders = allOrders.filter(o => o.status === 'pending').length;

            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card">
                    <div><div class="stat-number">${todayOrders.length}</div><div class="stat-label">Order Hari Ini</div></div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-number">${formatRupiah(todayOrders.reduce((sum, o) => sum + (o.total || 0), 0))}</div><div class="stat-label">Pendapatan Hari Ini</div></div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-number">${pendingOrders}</div><div class="stat-label">Menunggu Konfirmasi</div></div>
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-card">
                    <div><div class="stat-number">${allMenus.length}</div><div class="stat-label">Total Menu</div></div>
                    <div class="stat-icon">🍽️</div>
                </div>
            `;

            const recentOrders = [...allOrders]
                .sort((a,b) => new Date(b.createdAt) - new Date(a.createdAt))
                .slice(0,5);

            if (recentOrders.length === 0) {
                document.getElementById('recentOrders').innerHTML = '<div style="text-align:center; padding:40px;">Belum ada pesanan</div>';
            } else {
                const statusLabels = { 'pending': 'Menunggu', 'confirmed': 'Dikonfirmasi', 'processing': 'Diproses', 'ready': 'Siap Diambil', 'completed': 'Selesai' };
                document.getElementById('recentOrders').innerHTML = recentOrders.map(order => `
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-number">${order.orderNumber || 'ORD-XXXX'}</div>
                            <div class="status-badge status-${order.status}">${statusLabels[order.status] || order.status}</div>
                        </div>
                        <div><strong>Customer:</strong> ${order.customerName || '-'}</div>
                        <div style="font-weight: 700; margin-top: 8px;">Total: ${formatRupiah(order.total || 0)}</div>
                    </div>
                `).join('');
            }

            const completedOrders = allOrders.filter(o => o.status === 'completed');
            const totalCompletedRevenue = completedOrders.reduce((sum, o) => sum + (o.total || 0), 0);

            document.getElementById('reportStats').innerHTML = `
                <div class="report-card"><div class="report-value">${allOrders.length}</div><div class="report-label">Total Pesanan</div></div>
                <div class="report-card"><div class="report-value">${completedOrders.length}</div><div class="report-label">Pesanan Selesai</div></div>
                <div class="report-card"><div class="report-value">${formatRupiah(totalRevenue)}</div><div class="report-label">Total Pendapatan</div></div>
                <div class="report-card"><div class="report-value">${formatRupiah(totalCompletedRevenue)}</div><div class="report-label">Pendapatan Selesai</div></div>
            `;
        }

        window.editMenu = function(id) {
            const menu = allMenus.find(m => m.id === id);
            if (menu) {
                document.getElementById('menuId').value = menu.id;
                document.getElementById('menuName').value = menu.name;
                document.getElementById('menuPrice').value = menu.price;
                document.getElementById('menuDesc').value = menu.description || '';
                document.getElementById('menuAvailable').value = menu.available || 'Tersedia';

                if (menu.image) {
                    document.getElementById('menuImagePreview').innerHTML = `<img src="${menu.image}">`;
                    document.getElementById('menuImageBase64').value = menu.image;
                } else {
                    document.getElementById('menuImagePreview').innerHTML = '<div class="menu-image-placeholder">🍽️</div>';
                    document.getElementById('menuImageBase64').value = '';
                }

                document.getElementById('menuModalTitle').innerText = 'Edit Menu';
                openMenuModal();
            }
        };

        window.deleteMenu = async function(id) {
            if (confirm('Yakin hapus menu ini?')) {
                try {
                    await deleteDoc(doc(db, "menus", id));
                    showToast('Menu berhasil dihapus');
                    await loadMenus();
                } catch (error) {
                    showToast('Gagal hapus menu', true);
                }
            }
        };

        window.openMenuModal = function() {
            document.getElementById('menuModal').style.display = 'flex';
            if (!document.getElementById('menuId').value) {
                document.getElementById('menuModalTitle').innerText = 'Tambah Menu';
                document.getElementById('menuForm').reset();
                document.getElementById('menuId').value = '';
                document.getElementById('menuImagePreview').innerHTML = '<div class="menu-image-placeholder">🍽️</div>';
                document.getElementById('menuImageBase64').value = '';
                document.getElementById('menuImage').value = '';
            }
        };

        window.closeModal = function(id) { document.getElementById(id).style.display = 'none'; };

        document.getElementById('menuForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('menuId').value;
            const imageBase64 = document.getElementById('menuImageBase64').value;

            const menuData = {
                standId: currentStand.id,
                nama: document.getElementById('menuName').value,
                harga: parseInt(document.getElementById('menuPrice').value),
                deskripsi: document.getElementById('menuDesc').value,
                status: document.getElementById('menuAvailable').value,
                updatedAt: new Date().toISOString()
            };

            if (imageBase64) {
                menuData.gambar = imageBase64;
            }

            try {
                if (id) {
                    await updateDoc(doc(db, "menus", id), menuData);
                    showToast('Menu berhasil diupdate');
                } else {
                    menuData.createdAt = new Date().toISOString();
                    await addDoc(collection(db, "menus"), menuData);
                    showToast('Menu berhasil ditambahkan');
                }
                closeModal('menuModal');
                await loadMenus();
            } catch (error) {
                showToast('Gagal menyimpan menu', true);
            }
        });

        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', async () => {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                const tab = item.dataset.tab;

                document.getElementById('dashboardTab').style.display = 'none';
                document.getElementById('menusTab').style.display = 'none';
                document.getElementById('ordersTab').style.display = 'none';
                document.getElementById('reportsTab').style.display = 'none';

                if (tab === 'dashboard') {
                    document.getElementById('dashboardTab').style.display = 'block';
                    await loadOrders();
                    updateDashboard();
                } else if (tab === 'menus') {
                    document.getElementById('menusTab').style.display = 'block';
                    await loadMenus();
                } else if (tab === 'orders') {
                    document.getElementById('ordersTab').style.display = 'block';
                    await loadOrders();
                } else if (tab === 'reports') {
                    document.getElementById('reportsTab').style.display = 'block';
                    await loadOrders();
                    updateDashboard();
                }
            });
        });

        window.handleLogout = async function() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                try {
                    await signOut(auth);
                    localStorage.removeItem('currentUser');
                    showToast('Logout berhasil');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1000);
                } catch (error) {
                    window.location.href = 'login.php';
                }
            }
        };

        onAuthStateChanged(auth, async (user) => {
            if (user) {
                currentUser = user;
                await loadStand();
            } else {
                window.location.href = 'login.php';
            }
        });
    </script>
</body>
</html>

