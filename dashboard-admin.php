<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F3F0FF; overflow-x: hidden; }
        
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: linear-gradient(180deg, #4C1D95 0%, #5B21B6 100%); color: white; z-index: 100; transition: all 0.3s; box-shadow: 4px 0 20px rgba(0,0,0,0.1); }
        .sidebar-header { padding: 30px 24px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.15); }
        .sidebar-logo { width: 70px; height: 70px; margin: 0 auto 15px; background: linear-gradient(135deg, #6C4CF1, #8B5CF6); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .sidebar-title { font-size: 1.3rem; font-weight: 700; }
        .sidebar-subtitle { font-size: 0.75rem; opacity: 0.7; margin-top: 5px; }
        .sidebar-menu { padding: 20px 0; }
        .menu-item { padding: 14px 28px; display: flex; align-items: center; gap: 14px; cursor: pointer; transition: all 0.3s; color: rgba(255,255,255,0.75); margin: 4px 12px; border-radius: 12px; }
        .menu-item:hover { background: rgba(255,255,255,0.12); color: white; }
        .menu-item.active { background: #8B5CF6; color: white; box-shadow: 0 4px 12px rgba(139,92,246,0.3); }
        .menu-item i { width: 24px; font-size: 1.2rem; }
        
        .main-content { margin-left: 280px; padding: 24px 32px; min-height: 100vh; }
        .admin-header { background: white; border-radius: 20px; padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); }
        .header-title { font-size: 1.4rem; font-weight: 700; color: #1E1B4B; }
        .logout-btn { background: linear-gradient(135deg, #EF4444, #DC2626); color: white; border: none; padding: 8px 24px; border-radius: 30px; cursor: pointer; font-weight: 600; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: white; border-radius: 24px; padding: 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: 800; color: #6C63FF; }
        .stat-label { font-size: 0.85rem; color: #6B7280; margin-top: 6px; }
        .stat-icon { font-size: 2.5rem; opacity: 0.7; color: #8B5CF6; }
        
        .table-container { background: white; border-radius: 24px; padding: 24px; overflow-x: auto; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
        .btn-add { background: linear-gradient(135deg, #6C63FF, #8B5CF6); color: white; border: none; padding: 10px 24px; border-radius: 30px; cursor: pointer; font-weight: 600; }
        .btn-back { background: #6B7280; color: white; border: none; padding: 8px 20px; border-radius: 30px; cursor: pointer; font-weight: 600; margin-right: 12px; }
        
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px; }
        .stand-card { background: white; border-radius: 20px; padding: 20px; border: 1px solid #F3F4F6; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s; cursor: pointer; }
        .stand-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .stand-card h3 { margin-bottom: 8px; color: #1E1B4B; }
        .stand-image { width: 100%; height: 160px; border-radius: 16px; overflow: hidden; margin-bottom: 12px; background: #F3F4F6; display: flex; align-items: center; justify-content: center; }
        .stand-image img { width: 100%; height: 100%; object-fit: cover; }
        
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .status-Open { background: #D1FAE5; color: #059669; }
        .status-Closed { background: #FEE2E2; color: #DC2626; }
        .status-Tersedia { background: #D1FAE5; color: #059669; }
        .status-Tidak { background: #FEE2E2; color: #DC2626; }
        .status-pending { background: #FEF3C7; color: #D97706; }
        .status-confirmed { background: #DBEAFE; color: #2563EB; }
        .status-processing { background: #E0E7FF; color: #4F46E5; }
        .status-ready { background: #D1FAE5; color: #059669; }
        .status-completed { background: #A7F3D0; color: #047857; }
        .status-customer { background: #DBEAFE; color: #2563EB; }
        .status-stand { background: #FEF3C7; color: #D97706; }
        .status-operator { background: #FEF3C7; color: #D97706; }
        .status-admin { background: #FEE2E2; color: #DC2626; }
        
        .action-btn { padding: 6px 14px; margin: 0 4px; border: none; border-radius: 20px; cursor: pointer; font-size: 0.7rem; font-weight: 600; }
        .btn-edit { background: #6C63FF; color: white; }
        .btn-edit:hover { background: #5B21B6; }
        .btn-delete { background: #EF4444; color: white; }
        .btn-delete:hover { background: #DC2626; }
        
        .selected-stand-info { background: #F3F4F6; padding: 16px; border-radius: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .selected-stand-name { font-weight: 700; color: #4C1D95; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 28px; padding: 28px; width: 90%; max-width: 520px; max-height: 85vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #F3F4F6; }
        .close-modal { font-size: 1.8rem; cursor: pointer; color: #9CA3AF; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600; color: #374151; }
        .form-control, .form-select { width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 16px; font-size: 0.9rem; outline: none; }
        .form-control:focus, .form-select:focus { border-color: #6C63FF; box-shadow: 0 0 0 3px rgba(108,99,255,0.1); }
        .btn-save { width: 100%; padding: 14px; background: linear-gradient(135deg, #6C63FF, #8B5CF6); color: white; border: none; border-radius: 30px; cursor: pointer; font-weight: 700; margin-top: 12px; }
        
        .image-preview { width: 120px; height: 120px; border-radius: 16px; overflow: hidden; background: #F3F4F6; display: flex; align-items: center; justify-content: center; margin-top: 10px; border: 1px solid #E5E7EB; }
        .image-preview img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview .no-image { font-size: 2.5rem; color: #9CA3AF; }
        
        .stand-selector-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .stand-selector-card { background: white; border-radius: 16px; padding: 16px; border: 2px solid #E5E7EB; cursor: pointer; transition: all 0.2s; text-align: center; }
        .stand-selector-card:hover { border-color: #6C63FF; transform: translateY(-2px); }
        .stand-selector-card i { font-size: 3rem; color: #6C63FF; margin-bottom: 10px; }
        
        .menu-thumbnail { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .menu-thumbnail-placeholder { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #F3F4F6; border-radius: 8px; }
        
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th, .orders-table td { padding: 12px; text-align: left; border-bottom: 1px solid #F3F4F6; }
        .orders-table th { color: #6B7280; font-weight: 600; font-size: 0.8rem; background: #F9FAFB; }
        .orders-table td { font-size: 0.85rem; color: #374151; }
        .orders-table tr:hover { background: #F9FAFB; }
        
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #F3F4F6; }
        .users-table th { color: #6B7280; font-weight: 600; font-size: 0.8rem; background: #F9FAFB; }
        .users-table td { font-size: 0.85rem; color: #374151; }
        .users-table tr:hover { background: #F9FAFB; }
        
        .toast-notification { position: fixed; bottom: 30px; right: 30px; background: #1F2937; color: white; padding: 12px 24px; border-radius: 40px; font-size: 0.85rem; z-index: 1100; animation: slideInRight 0.3s ease; }
        @keyframes slideInRight { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; padding: 16px; } .orders-table { font-size: 0.7rem; } .orders-table th, .orders-table td { padding: 8px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><i class="fas fa-utensils"></i></div>
            <div class="sidebar-title">CaFood Admin</div>
            <div class="sidebar-subtitle">Food Stand Manager</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-tab="dashboard"><i class="fas fa-chart-line"></i> <span>Dashboard</span></div>
            <div class="menu-item" data-tab="stands"><i class="fas fa-store"></i> <span>Manage Stands</span></div>
            <div class="menu-item" data-tab="menus"><i class="fas fa-utensils"></i> <span>Manage Menus</span></div>
            <div class="menu-item" data-tab="orders"><i class="fas fa-truck"></i> <span>All Orders</span></div>
            <div class="menu-item" data-tab="users"><i class="fas fa-users"></i> <span>Manage Users</span></div>
        </div>
    </div>

    <div class="main-content">
        <div class="admin-header">
            <div class="header-title"><i class="fas fa-crown" style="color: #8B5CF6; margin-right: 10px;"></i> Admin Dashboard</div>
            <button class="logout-btn" onclick="handleLogout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>

        <div id="dashboardTab">
            <div class="stats-grid" id="statsGrid"></div>
            <div class="table-container">
                <div class="section-header"><h2><i class="fas fa-clock"></i> Recent Orders</h2></div>
                <div id="recentOrders"></div>
            </div>
        </div>

        <div id="standsTab" style="display: none;">
            <div class="table-container">
                <div class="section-header"><h2><i class="fas fa-store"></i> All Food Stands</h2><button class="btn-add" onclick="openStandModal()"><i class="fas fa-plus"></i> Add Stand</button></div>
                <div id="standsList" class="card-grid"></div>
            </div>
        </div>

        <div id="menusTab" style="display: none;">
            <div class="table-container">
                <div class="section-header"><h2><i class="fas fa-utensils"></i> Manage Menus</h2><button class="btn-add" onclick="showStandSelector()"><i class="fas fa-plus"></i> Add Menu</button></div>
                <div id="standSelectorPanel"><h3 style="margin-bottom: 16px;">Pilih Stand untuk Mengelola Menu</h3><div id="standSelectorList" class="stand-selector-grid"></div></div>
                <div id="menuPanel" style="display: none;">
                    <div class="selected-stand-info"><div><i class="fas fa-store"></i> <span class="selected-stand-name" id="selectedStandName"></span></div><div><button class="btn-back" onclick="backToStandSelector()"><i class="fas fa-arrow-left"></i> Ganti Stand</button><button class="btn-add" onclick="openMenuModal()"><i class="fas fa-plus"></i> Tambah Menu</button></div></div>
                    <div id="menusList"></div>
                </div>
            </div>
        </div>

        <div id="ordersTab" style="display: none;">
            <div class="table-container">
                <div class="section-header"><h2><i class="fas fa-clipboard-list"></i> All Orders</h2></div>
                <div id="allOrdersTable"></div>
            </div>
        </div>

        <div id="usersTab" style="display: none;">
            <div class="table-container">
                <div class="section-header"><h2><i class="fas fa-users"></i> Manage Users</h2><button class="btn-add" onclick="openUserModal()"><i class="fas fa-plus"></i> Add User</button></div>
                <div class="filter-group" style="margin-bottom: 20px; display: flex; gap: 12px;">
                    <select id="roleFilter" class="form-select" style="width: 150px;" onchange="filterUsers()">
                        <option value="all">All Roles</option>
                        <option value="customer">Customer</option>
                        <option value="stand">Stand Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                    <input type="text" id="userSearchInput" class="form-control" placeholder="Search by name or email" style="width: 250px;" onkeyup="filterUsers()">
                </div>
                <div id="usersList"></div>
            </div>
        </div>
    </div>

    <!-- Stand Modal -->
    <div id="standModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3 id="standModalTitle">Tambah Stand Baru</h3><span class="close-modal" onclick="closeModal('standModal')">&times;</span></div>
            <form id="standForm">
                <input type="hidden" id="standId">
                <div class="form-group"><label>Gambar Stand</label><input type="file" id="standImage" accept="image/*" onchange="previewStandImage(event)"><div id="standImagePreview" class="image-preview"><div class="no-image"><i class="fas fa-store"></i></div></div><input type="hidden" id="standImageBase64"><small>Format: JPG, PNG (Max 2MB)</small></div>
                <div class="form-group"><label>Nama Stand *</label><input type="text" id="standName" class="form-control" required></div>
                <div class="form-group"><label>Deskripsi</label><textarea id="standDesc" class="form-control" rows="3"></textarea></div>
                <div class="form-group"><label>Estimasi Waktu (menit)</label><input type="text" id="standEstTime" class="form-control" placeholder="e.g., 15-30 min"></div>
                <div class="form-group"><label>Status</label><select id="standStatus" class="form-select"><option value="Open">Open</option><option value="Closed">Closed</option></select></div>
                <button type="submit" class="btn-save">Simpan Stand</button>
            </form>
        </div>
    </div>

    <!-- Menu Modal -->
    <div id="menuModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3 id="menuModalTitle">Tambah Menu Baru</h3><span class="close-modal" onclick="closeModal('menuModal')">&times;</span></div>
            <form id="menuForm">
                <input type="hidden" id="menuId">
                <div class="form-group"><label>Gambar Menu</label><input type="file" id="menuImage" accept="image/*" onchange="previewMenuImage(event)"><div id="menuImagePreview" class="image-preview"><div class="no-image"><i class="fas fa-utensils"></i></div></div><input type="hidden" id="menuImageBase64"><small>Format: JPG, PNG (Max 2MB)</small></div>
                <div class="form-group"><label>Nama Menu *</label><input type="text" id="menuName" class="form-control" required></div>
                <div class="form-group"><label>Harga (Rp) *</label><input type="number" id="menuPrice" class="form-control" required></div>
                <div class="form-group"><label>Status</label><select id="menuAvailable" class="form-select"><option value="Tersedia">Tersedia</option><option value="Tidak">Tidak Tersedia</option></select></div>
                <button type="submit" class="btn-save">Simpan Menu</button>
            </form>
        </div>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h3 id="userModalTitle">Tambah User</h3><span class="close-modal" onclick="closeModal('userModal')">&times;</span></div>
            <form id="userForm">
                <input type="hidden" id="userId">
                <div class="form-group"><label>Nama Lengkap *</label><input type="text" id="userName" class="form-control" required></div>
                <div class="form-group"><label>Email *</label><input type="email" id="userEmail" class="form-control" required></div>
                <div class="form-group"><label>Password</label><input type="password" id="userPassword" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah"></div>
                <div class="form-group"><label>Role *</label><select id="userRole" class="form-select" required>
                    <option value="customer">Customer</option>
                    <option value="stand">Stand Owner</option>
                    <option value="admin">Admin</option>
                </select></div>
                <div class="form-group" id="standSelectGroup" style="display: none;"><label>Assign Stand (untuk Stand Owner)</label><select id="userStandId" class="form-select"><option value="">-- Pilih Stand --</option></select></div>
                <button type="submit" class="btn-save">Simpan User</button>
            </form>
        </div>
    </div>

    <script type="module">
        import { db, auth, collection, doc, getDocs, addDoc, updateDoc, deleteDoc, signOut } from './js/firebase-config.js';

        const currentUser = JSON.parse(localStorage.getItem('currentUser') || 'null');
        if (!currentUser || currentUser.role !== 'admin') window.location.href = 'login.html';

        let allStands = [], allMenus = [], allOrders = [], allUsers = [], selectedStandId = null;

        function showToast(msg, isError = false) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.style.background = isError ? '#EF4444' : '#10B981';
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${msg}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        window.previewStandImage = function(e) {
            const file = e.target.files[0];
            if (file && file.size <= 2*1024*1024) {
                const reader = new FileReader();
                reader.onload = ev => { document.getElementById('standImagePreview').innerHTML = `<img src="${ev.target.result}">`; document.getElementById('standImageBase64').value = ev.target.result; };
                reader.readAsDataURL(file);
            } else if (file) showToast('Ukuran gambar maksimal 2MB!', true);
        };

        window.previewMenuImage = function(e) {
            const file = e.target.files[0];
            if (file && file.size <= 2*1024*1024) {
                const reader = new FileReader();
                reader.onload = ev => { document.getElementById('menuImagePreview').innerHTML = `<img src="${ev.target.result}">`; document.getElementById('menuImageBase64').value = ev.target.result; };
                reader.readAsDataURL(file);
            } else if (file) showToast('Ukuran gambar maksimal 2MB!', true);
        };

        async function loadStands() {
            try {
                const snapshot = await getDocs(collection(db, "stands"));
                allStands = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data(), name: doc.data().nama || doc.data().name || 'No Name', description: doc.data().deskripsi || doc.data().description || '', estimatedTime: doc.data().estimasiWaktu || doc.data().estimatedTime || '15-20 min', image: doc.data().gambarUrl || doc.data().image || null, status: doc.data().status || 'Open' }));
                renderStands();
                renderStandSelector();
                updateStandSelect();
            } catch(e) { showToast("Gagal load stands", true); }
        }

        async function loadMenus() {
            try {
                const snapshot = await getDocs(collection(db, "menus"));
                allMenus = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data(), name: doc.data().name || doc.data().nama || doc.data().menu_name || 'No Name', price: doc.data().price || doc.data().harga || 0, available: doc.data().available || doc.data().status || doc.data().ketersediaan || 'Tersedia', image: doc.data().image || doc.data().gambarUrl || doc.data().gambar || null }));
                if (selectedStandId) renderMenusByStand(selectedStandId);
            } catch(e) { showToast("Gagal load menus", true); }
        }

        async function loadOrders() {
            try {
                const snapshot = await getDocs(collection(db, "orders"));
                allOrders = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
                renderAllOrders();
                renderDashboard();
            } catch(e) { console.error(e); }
        }

        async function loadUsers() {
            try {
                const snapshot = await getDocs(collection(db, "users"));
                allUsers = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
                renderUsers();
                renderDashboard();
            } catch(e) { showToast("Gagal load users", true); }
        }

        function updateStandSelect() {
            const select = document.getElementById('userStandId');
            if (select) {
                select.innerHTML = '<option value="">-- Pilih Stand --</option>' + allStands.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
            }
        }

        // ============ FILTER USERS (Mencakup role 'stand' dan 'operator') ============
        function filterUsers() {
            const roleFilter = document.getElementById('roleFilter').value;
            const searchTerm = document.getElementById('userSearchInput').value.toLowerCase();
            
            let filtered = [...allUsers];
            
            if (roleFilter !== 'all') {
                if (roleFilter === 'stand') {
                    // PERBAIKAN: Filter untuk role 'stand' ATAU 'operator'
                    filtered = filtered.filter(u => u.role === 'stand' || u.role === 'operator');
                } else {
                    filtered = filtered.filter(u => u.role === roleFilter);
                }
            }
            if (searchTerm) {
                filtered = filtered.filter(u => 
                    (u.name || u.full_name || '').toLowerCase().includes(searchTerm) ||
                    (u.email || '').toLowerCase().includes(searchTerm)
                );
            }
            
            renderUsers(filtered);
        }

        // ============ RENDER USERS (Menampilkan operator sebagai Stand Owner) ============
        function renderUsers(users = allUsers) {
    const container = document.getElementById('usersList');
    if (!container) return;
    
    if (!users.length) {
        container.innerHTML = '<div style="text-align:center; padding:40px;">Belum ada user</div>';
        return;
    }
    
    container.innerHTML = `
        <table class="users-table">
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>Stand</th><th>Dibuat</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                ${users.map(user => {
                    // PERBAIKAN: Ambil nama dari berbagai kemungkinan field
                    const userName = user.name || user.full_name || user.nama || user.username || '-';
                    const userEmail = user.email || '-';
                    const userStand = user.standName || user.assignedStandName || '-';
                    const userCreated = user.createdAt ? new Date(user.createdAt).toLocaleDateString('id-ID') : '-';
                    
                    // Tampilan role
                    let roleDisplay = '';
                    if (user.role === 'customer') roleDisplay = 'Customer';
                    else if (user.role === 'stand' || user.role === 'operator') roleDisplay = 'Stand Owner';
                    else if (user.role === 'admin') roleDisplay = 'Admin';
                    else roleDisplay = user.role || 'Customer';
                    
                    return `
                        <tr>
                            <td>${userName}</td>
                            <td>${userEmail}</td>
                            <td><span class="status-badge status-${user.role}">${roleDisplay}</span></td>
                            <td>${userStand}</td>
                            <td>${userCreated}</td>
                            <td>
                                <button class="action-btn btn-edit" onclick="editUser('${user.id}')">Edit</button>
                                <button class="action-btn btn-delete" onclick="deleteUser('${user.id}')">Hapus</button>
                            </td>
                        </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
}

        window.filterUsers = filterUsers;

        function renderDashboard() {
            const totalRevenue = allOrders.reduce((s,o) => s+(o.total||0),0);
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card"><div><div class="stat-number">Rp ${totalRevenue.toLocaleString('id-ID')}</div><div class="stat-label">Total Revenue</div></div><div class="stat-icon">💰</div></div>
                <div class="stat-card"><div><div class="stat-number">${allOrders.length}</div><div class="stat-label">Total Orders</div></div><div class="stat-icon">📦</div></div>
                <div class="stat-card"><div><div class="stat-number">${allStands.filter(s=>s.status==='Open').length}</div><div class="stat-label">Active Stands</div></div><div class="stat-icon">🏪</div></div>
                <div class="stat-card"><div><div class="stat-number">${allUsers.length}</div><div class="stat-label">Total Users</div></div><div class="stat-icon">👥</div></div>
            `;
            const recent = [...allOrders].slice(0,5);
            document.getElementById('recentOrders').innerHTML = `
                <table class="orders-table">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>${recent.map(o=>`<tr><td>${o.orderNumber||'-'}</td><td>${o.customerName||'-'}</td><td>Rp ${(o.total||0).toLocaleString('id-ID')}</td><td><span class="status-badge status-${o.status||'pending'}">${o.status||'pending'}</span></td><td>${new Date(o.createdAt).toLocaleDateString()}</td></tr>`).join('')}${recent.length===0?'<tr><td colspan="5" style="text-align:center">Belum ada pesanan</td></tr>':''}</tbody>
                </table>
            `;
        }

        function renderStands() {
            const container = document.getElementById('standsList');
            if (!container) return;
            if (!allStands.length) { container.innerHTML = '<div style="text-align:center;padding:40px;">Belum ada stand. Klik "Add Stand" untuk menambahkan.</div>'; return; }
            container.innerHTML = allStands.map(s => `<div class="stand-card"><div class="stand-image">${s.image?`<img src="${s.image}" onerror="this.src='https://via.placeholder.com/300x160?text=No+Image'">`:'<i class="fas fa-store" style="font-size: 3rem; color: #9CA3AF;"></i>'}</div><h3>${s.name}</h3><p style="color:#6B7280;font-size:0.75rem;margin:8px 0;">${s.description||'-'}</p><div style="display:flex;justify-content:space-between;margin:12px 0;"><span>⭐ ${s.rating||0}</span><span>⏱️ ${s.estimatedTime||'-'}</span></div><div style="display:flex;justify-content:space-between;align-items:center;"><span class="status-badge status-${s.status}">${s.status||'Open'}</span><div><button class="action-btn btn-edit" onclick="editStand('${s.id}')">Edit</button><button class="action-btn btn-delete" onclick="deleteStand('${s.id}')">Hapus</button></div></div></div>`).join('');
        }

        function renderStandSelector() {
            const container = document.getElementById('standSelectorList');
            if (!container) return;
            if (!allStands.length) { container.innerHTML = '<div style="text-align:center;padding:40px;">Belum ada stand. Buat stand terlebih dahulu.</div>'; return; }
            container.innerHTML = allStands.map(s => `<div class="stand-selector-card" onclick="selectStand('${s.id}')"><i class="fas fa-store"></i><h3>${s.name}</h3><p style="color:#6B7280;font-size:0.7rem;">${(s.description||'').substring(0,50)||'-'}</p><div style="margin-top:10px;"><span class="status-badge status-${s.status}">${s.status||'Open'}</span></div></div>`).join('');
        }

        function renderMenusByStand(standId) {
            const container = document.getElementById('menusList');
            if (!container) return;
            const filtered = allMenus.filter(m => m.standId === standId);
            if (!filtered.length) { container.innerHTML = '<div style="text-align:center;padding:40px;">Belum ada menu. Klik "Tambah Menu" untuk menambahkan.</div>'; return; }
            container.innerHTML = `
                <table class="orders-table">
                    <thead><tr><th>Gambar</th><th>Menu</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>${filtered.map(m => `<td><td style="width:70px;">${m.image?`<img src="${m.image}" class="menu-thumbnail" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">`:'<div class="menu-thumbnail-placeholder"><i class="fas fa-utensils" style="font-size:1.5rem; color:#9CA3AF;"></i></div>'}</td><td><strong>${m.name}</strong></td><td>Rp ${(m.price||0).toLocaleString('id-ID')}</td><td><span class="status-badge status-${m.available==='Tersedia'?'Open':'Closed'}">${m.available||'Tersedia'}</span></td><td><button class="action-btn btn-edit" onclick="editMenu('${m.id}')">Edit</button><button class="action-btn btn-delete" onclick="deleteMenu('${m.id}')">Hapus</button></td></tr>`).join('')}</tbody>
                </table>
            `;
        }

        function renderAllOrders() {
            const container = document.getElementById('allOrdersTable');
            if (!container) return;
            if (!allOrders.length) { container.innerHTML = '<div style="text-align:center;padding:40px;">Belum ada pesanan</div>'; return; }
            container.innerHTML = `
                <table class="orders-table">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>${allOrders.map(o => `<tr><td>${o.orderNumber||'-'}</td><td>${o.customerName||'-'}</td><td>${o.items?.map(i=>i.name).join(', ')||'-'}</td><td>Rp ${(o.total||0).toLocaleString('id-ID')}</td><td><span class="status-badge status-${o.status||'pending'}">${o.status||'pending'}</span></td><td>${new Date(o.createdAt).toLocaleDateString()}</td></table>`).join('')}</tbody>
                </table>
            `;
        }

        window.selectStand = function(id) {
            selectedStandId = id;
            const s = allStands.find(x=>x.id===id);
            document.getElementById('selectedStandName').innerText = s?.name||'';
            document.getElementById('standSelectorPanel').style.display = 'none';
            document.getElementById('menuPanel').style.display = 'block';
            renderMenusByStand(id);
        };
        window.backToStandSelector = function() { selectedStandId = null; document.getElementById('standSelectorPanel').style.display = 'block'; document.getElementById('menuPanel').style.display = 'none'; };
        window.showStandSelector = function() { backToStandSelector(); };

        window.editStand = function(id) {
            const s = allStands.find(x=>x.id===id);
            if(s){
                document.getElementById('standId').value = s.id;
                document.getElementById('standName').value = s.name;
                document.getElementById('standDesc').value = s.description||'';
                document.getElementById('standEstTime').value = s.estimatedTime||'';
                document.getElementById('standStatus').value = s.status||'Open';
                document.getElementById('standImagePreview').innerHTML = s.image ? `<img src="${s.image}">` : '<div class="no-image"><i class="fas fa-store"></i></div>';
                document.getElementById('standImageBase64').value = s.image||'';
                document.getElementById('standModalTitle').innerText = 'Edit Stand';
                openStandModal();
            }
        };

        window.deleteStand = async function(id) {
            if(confirm('Yakin hapus stand ini? Semua menu terkait juga akan dihapus!')){
                try {
                    const toDelete = allMenus.filter(m=>m.standId===id);
                    for(const menu of toDelete) await deleteDoc(doc(db,"menus",menu.id));
                    await deleteDoc(doc(db,"stands",id));
                    showToast('Stand dan menu berhasil dihapus');
                    await loadStands();
                    await loadMenus();
                    if(selectedStandId===id) backToStandSelector();
                } catch(e){ showToast('Gagal hapus stand',true); }
            }
        };

        window.editMenu = function(id) {
            const m = allMenus.find(x=>x.id===id);
            if(m){
                document.getElementById('menuId').value = m.id;
                document.getElementById('menuName').value = m.name;
                document.getElementById('menuPrice').value = m.price;
                document.getElementById('menuAvailable').value = m.available||'Tersedia';
                document.getElementById('menuImagePreview').innerHTML = m.image ? `<img src="${m.image}">` : '<div class="no-image"><i class="fas fa-utensils"></i></div>';
                document.getElementById('menuImageBase64').value = m.image||'';
                document.getElementById('menuModalTitle').innerText = 'Edit Menu';
                openMenuModal();
            }
        };

        window.deleteMenu = async function(id) {
            if(confirm('Yakin hapus menu ini?')){
                try { await deleteDoc(doc(db,"menus",id)); showToast('Menu berhasil dihapus'); await loadMenus(); }
                catch(e){ showToast('Gagal hapus menu',true); }
            }
        };

        // ============ USER CRUD ============
        window.editUser = function(id) {
            const user = allUsers.find(u => u.id === id);
            if(user){
                document.getElementById('userId').value = user.id;
                document.getElementById('userName').value = user.name || user.full_name || '';
                document.getElementById('userEmail').value = user.email || '';
                document.getElementById('userRole').value = user.role === 'operator' ? 'stand' : (user.role || 'customer');
                document.getElementById('userPassword').value = '';
                
                const standSelectGroup = document.getElementById('standSelectGroup');
                if (user.role === 'stand' || user.role === 'operator') {
                    standSelectGroup.style.display = 'block';
                    document.getElementById('userStandId').value = user.standId || '';
                } else {
                    standSelectGroup.style.display = 'none';
                }
                
                document.getElementById('userModalTitle').innerText = 'Edit User';
                openUserModal();
            }
        };

        window.deleteUser = async function(id) {
            if(confirm('Yakin hapus user ini?')){
                try {
                    await deleteDoc(doc(db, "users", id));
                    showToast('User berhasil dihapus');
                    await loadUsers();
                } catch(e){ showToast('Gagal hapus user', true); }
            }
        };

        window.openUserModal = function() {
            document.getElementById('userModal').style.display = 'flex';
            if (!document.getElementById('userId').value) {
                document.getElementById('userModalTitle').innerText = 'Tambah User';
                document.getElementById('userForm').reset();
                document.getElementById('userId').value = '';
                document.getElementById('standSelectGroup').style.display = 'none';
            }
        };

        document.getElementById('userRole').addEventListener('change', function() {
            const standSelectGroup = document.getElementById('standSelectGroup');
            if (this.value === 'stand') {
                standSelectGroup.style.display = 'block';
            } else {
                standSelectGroup.style.display = 'none';
            }
        });

        document.getElementById('userForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = document.getElementById('userId').value;
            const userData = {
                name: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value,
                role: document.getElementById('userRole').value,
                updatedAt: new Date().toISOString()
            };
            
            if (userData.role === 'stand') {
                const standId = document.getElementById('userStandId').value;
                if (standId) {
                    userData.standId = standId;
                    const stand = allStands.find(s => s.id === standId);
                    userData.standName = stand?.name || '';
                }
            }
            
            const password = document.getElementById('userPassword').value;
            
            try {
                if (id) {
                    await updateDoc(doc(db, "users", id), userData);
                    showToast('User berhasil diupdate');
                } else {
                    if (!password) {
                        showToast('Password wajib diisi untuk user baru!', true);
                        return;
                    }
                    userData.createdAt = new Date().toISOString();
                    userData.password = btoa(password);
                    await addDoc(collection(db, "users"), userData);
                    showToast('User berhasil ditambahkan');
                }
                closeModal('userModal');
                await loadUsers();
            } catch(e){ showToast('Gagal menyimpan user', true); }
        });

        window.openStandModal = function() {
            document.getElementById('standModal').style.display = 'flex';
            if(!document.getElementById('standId').value){
                document.getElementById('standModalTitle').innerText = 'Tambah Stand Baru';
                document.getElementById('standForm').reset();
                document.getElementById('standId').value = '';
                document.getElementById('standImagePreview').innerHTML = '<div class="no-image"><i class="fas fa-store"></i></div>';
            }
        };

        window.openMenuModal = function() {
            if(!selectedStandId) { showToast('Pilih stand terlebih dahulu!',true); return; }
            document.getElementById('menuModal').style.display = 'flex';
            document.getElementById('menuForm').reset();
            document.getElementById('menuId').value = '';
            document.getElementById('menuImagePreview').innerHTML = '<div class="no-image"><i class="fas fa-utensils"></i></div>';
            document.getElementById('menuModalTitle').innerText = 'Tambah Menu Baru';
        };

        window.closeModal = function(id) { document.getElementById(id).style.display = 'none'; };

        document.getElementById('standForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('standId').value;
            const data = { nama: document.getElementById('standName').value, deskripsi: document.getElementById('standDesc').value, estimasiWaktu: document.getElementById('standEstTime').value, status: document.getElementById('standStatus').value, gambarUrl: document.getElementById('standImageBase64').value || null, updatedAt: new Date().toISOString() };
            try {
                if(id) await updateDoc(doc(db,"stands",id), data);
                else { data.createdAt = new Date().toISOString(); await addDoc(collection(db,"stands"), data); }
                showToast(id?'Stand berhasil diupdate':'Stand berhasil ditambahkan');
                closeModal('standModal');
                await loadStands();
            } catch(e){ showToast('Gagal menyimpan stand',true); }
        });

        document.getElementById('menuForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('menuId').value;
            const data = { standId: selectedStandId, name: document.getElementById('menuName').value, price: parseInt(document.getElementById('menuPrice').value), available: document.getElementById('menuAvailable').value, image: document.getElementById('menuImageBase64').value || null, updatedAt: new Date().toISOString() };
            try {
                if(id) await updateDoc(doc(db,"menus",id), data);
                else { data.createdAt = new Date().toISOString(); await addDoc(collection(db,"menus"), data); }
                showToast(id?'Menu berhasil diupdate':'Menu berhasil ditambahkan');
                closeModal('menuModal');
                await loadMenus();
            } catch(e){ showToast('Gagal menyimpan menu',true); }
        });

        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', async () => {
                document.querySelectorAll('.menu-item').forEach(i=>i.classList.remove('active'));
                item.classList.add('active');
                const tab = item.dataset.tab;
                document.getElementById('dashboardTab').style.display = 'none';
                document.getElementById('standsTab').style.display = 'none';
                document.getElementById('menusTab').style.display = 'none';
                document.getElementById('ordersTab').style.display = 'none';
                document.getElementById('usersTab').style.display = 'none';
                if(tab==='dashboard') { document.getElementById('dashboardTab').style.display = 'block'; await loadOrders(); renderDashboard(); }
                else if(tab==='stands') { document.getElementById('standsTab').style.display = 'block'; await loadStands(); }
                else if(tab==='menus') { document.getElementById('menusTab').style.display = 'block'; await loadStands(); await loadMenus(); backToStandSelector(); }
                else if(tab==='orders') { document.getElementById('ordersTab').style.display = 'block'; await loadOrders(); }
                else if(tab==='users') { document.getElementById('usersTab').style.display = 'block'; await loadUsers(); }
            });
        });

        window.handleLogout = async () => { if(confirm('Logout?')){ try{ await signOut(auth); localStorage.removeItem('currentUser'); window.location.href='login.html'; } catch(e){ window.location.href='login.html'; } } };

        loadStands(); loadMenus(); loadOrders(); loadUsers();
    </script>
</body>
</html>