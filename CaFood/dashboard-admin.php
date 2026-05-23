`<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
            color: white;
            transition: all 0.3s;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .sidebar-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .sidebar-subtitle {
            font-size: 0.75rem;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.3s;
            color: rgba(255,255,255,0.8);
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .menu-item.active {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            border-left: 3px solid white;
        }
        
        .menu-icon {
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        /* Header */
        .admin-header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background: #f56565;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-info h3 {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 8px;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea20, #764ba220);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            color: #666;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-active { background: #c6f6d5; color: #22543d; }
        .status-inactive { background: #fed7d7; color: #742a2a; }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .btn-edit { background: #4299e1; color: white; }
        .btn-delete { background: #f56565; color: white; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .close-modal {
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        
        .btn-save {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 101;
                background: #667eea;
                color: white;
                padding: 10px;
                border-radius: 8px;
                cursor: pointer;
            }
        }
        
        .menu-toggle {
            display: none;
        }
        
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        canvas {
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="menu-toggle" id="menuToggle">☰</div>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">👑</div>
            <div class="sidebar-title">CaFood Admin</div>
            <div class="sidebar-subtitle">Management Panel</div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-item active" data-tab="dashboard">
                <div class="menu-icon">📊</div>
                <span>Dashboard</span>
            </div>
            <div class="menu-item" data-tab="stands">
                <div class="menu-icon">🏪</div>
                <span>Manage Stands</span>
            </div>
            <div class="menu-item" data-tab="users">
                <div class="menu-icon">👥</div>
                <span>Manage Users</span>
            </div>
            <div class="menu-item" data-tab="orders">
                <div class="menu-icon">📦</div>
                <span>All Orders</span>
            </div>
            <div class="menu-item" data-tab="promos">
                <div class="menu-icon">🎁</div>
                <span>Promotions</span>
            </div>
            <div class="menu-item" data-tab="reports">
                <div class="menu-icon">📈</div>
                <span>Reports</span>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="admin-header">
            <div class="header-title">Admin Dashboard</div>
            <div class="admin-info">
                <div class="admin-avatar">👑</div>
                <span>Admin</span>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
        
        <!-- Dashboard Tab -->
        <div id="dashboardTab">
            <div class="stats-grid" id="statsGrid">
                <!-- Stats loaded dynamically -->
            </div>
            
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">Recent Orders</div>
                </div>
                <div id="recentOrdersTable">
                    <!-- Recent orders loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Stands Tab -->
        <div id="standsTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">All Food Stands</div>
                    <button class="btn-add" onclick="openStandModal()">+ Add Stand</button>
                </div>
                <div id="standsTable">
                    <!-- Stands list loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Users Tab -->
        <div id="usersTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">All Users</div>
                    <button class="btn-add" onclick="openUserModal()">+ Add User</button>
                </div>
                <div id="usersTable">
                    <!-- Users list loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Orders Tab -->
        <div id="ordersTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">All Orders</div>
                </div>
                <div id="allOrdersTable">
                    <!-- All orders loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Promos Tab -->
        <div id="promosTab" style="display: none;">
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">Promotions</div>
                    <button class="btn-add" onclick="openPromoModal()">+ Add Promo</button>
                </div>
                <div id="promosTable">
                    <!-- Promos list loaded here -->
                </div>
            </div>
        </div>
        
        <!-- Reports Tab -->
        <div id="reportsTab" style="display: none;">
            <div class="stats-grid" id="reportStats">
                <!-- Report stats loaded here -->
            </div>
            <div class="table-container">
                <div class="section-header">
                    <div class="section-title">Daily Sales Report</div>
                    <input type="date" id="reportDate" onchange="loadReport()">
                </div>
                <div id="reportTable">
                    <!-- Report table loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for Stand -->
    <div id="standModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add/Edit Stand</div>
                <div class="close-modal" onclick="closeModal('standModal')">&times;</div>
            </div>
            <form id="standForm">
                <input type="hidden" id="standId">
                <div class="form-group">
                    <label>Stand Name</label>
                    <input type="text" class="form-control" id="standName" required>
                </div>
                <div class="form-group">
                    <label>Logo</label>
                    <input type="text" class="form-control" id="standLogo" placeholder="Emoji or URL">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" id="standDesc" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" id="standStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Save Stand</button>
            </form>
        </div>
    </div>
    
    <!-- Modal for User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add/Edit User</div>
                <div class="close-modal" onclick="closeModal('userModal')">&times;</div>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" id="userName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" id="userEmail" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select class="form-control" id="userRole">
                        <option value="user">User</option>
                        <option value="stand">Stand Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" id="userPassword" placeholder="Leave blank to keep current">
                </div>
                <button type="submit" class="btn-save">Save User</button>
            </form>
        </div>
    </div>
    
    <!-- Modal for Promo -->
    <div id="promoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Add/Edit Promo</div>
                <div class="close-modal" onclick="closeModal('promoModal')">&times;</div>
            </div>
            <form id="promoForm">
                <input type="hidden" id="promoId">
                <div class="form-group">
                    <label>Promo Name</label>
                    <input type="text" class="form-control" id="promoName" required>
                </div>
                <div class="form-group">
                    <label>Discount Type</label>
                    <select class="form-control" id="promoType">
                        <option value="percentage">Percentage (%)</option>
                        <option value="nominal">Nominal (Rp)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Discount Value</label>
                    <input type="number" class="form-control" id="promoValue" required>
                </div>
                <div class="form-group">
                    <label>Valid Until</label>
                    <input type="date" class="form-control" id="promoEndDate" required>
                </div>
                <button type="submit" class="btn-save">Save Promo</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sample Data
        let stands = [
            { id: 1, name: "Ramen House", logo: "🍜", description: "Authentic Japanese Ramen", status: "active", totalSales: 12500000, orders: 342 },
            { id: 2, name: "Nasi Campur Bali", logo: "🍚", description: "Traditional Balinese Cuisine", status: "active", totalSales: 8900000, orders: 245 },
            { id: 3, name: "Coffee & Co", logo: "☕", description: "Premium Coffee & Pastries", status: "active", totalSales: 5600000, orders: 178 }
        ];
        
        let users = [
            { id: 1, name: "John Doe", email: "john@example.com", role: "user", status: "active", orders: 12 },
            { id: 2, name: "Ramen House", email: "ramen@cafood.com", role: "stand", status: "active", orders: 342 },
            { id: 3, name: "Jane Smith", email: "jane@example.com", role: "user", status: "active", orders: 8 }
        ];
        
        let orders = [
            { id: 1, orderNumber: "ORD-001", customer: "John Doe", stand: "Ramen House", total: 115000, status: "completed", date: "2024-01-15" },
            { id: 2, orderNumber: "ORD-002", customer: "Jane Smith", stand: "Nasi Campur Bali", total: 82000, status: "processing", date: "2024-01-15" },
            { id: 3, orderNumber: "ORD-003", customer: "Mike Johnson", stand: "Coffee & Co", total: 45000, status: "pending", date: "2024-01-15" }
        ];
        
        let promos = [
            { id: 1, name: "New Year Promo", type: "percentage", value: 20, endDate: "2024-02-01" },
            { id: 2, name: "Weekend Special", type: "nominal", value: 20000, endDate: "2024-01-31" }
        ];
        
        let salesChart = null;
        
        // Tab switching
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                const tab = this.dataset.tab;
                document.getElementById('dashboardTab').style.display = tab === 'dashboard' ? 'block' : 'none';
                document.getElementById('standsTab').style.display = tab === 'stands' ? 'block' : 'none';
                document.getElementById('usersTab').style.display = tab === 'users' ? 'block' : 'none';
                document.getElementById('ordersTab').style.display = tab === 'orders' ? 'block' : 'none';
                document.getElementById('promosTab').style.display = tab === 'promos' ? 'block' : 'none';
                document.getElementById('reportsTab').style.display = tab === 'reports' ? 'block' : 'none';
                
                // Load data for tab
                if (tab === 'dashboard') loadDashboard();
                if (tab === 'stands') renderStands();
                if (tab === 'users') renderUsers();
                if (tab === 'orders') renderAllOrders();
                if (tab === 'promos') renderPromos();
                if (tab === 'reports') loadReport();
            });
        });
        
        function loadDashboard() {
            // Stats
            const totalRevenue = orders.reduce((sum, o) => sum + o.total, 0);
            const totalOrders = orders.length;
            const activeStands = stands.filter(s => s.status === 'active').length;
            const totalUsers = users.length;
            
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Revenue</h3>
                        <div class="stat-number">Rp ${totalRevenue.toLocaleString()}</div>
                    </div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <div class="stat-number">${totalOrders}</div>
                    </div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Active Stands</h3>
                        <div class="stat-number">${activeStands}</div>
                    </div>
                    <div class="stat-icon">🏪</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <div class="stat-number">${totalUsers}</div>
                    </div>
                    <div class="stat-icon">👥</div>
                </div>
            `;
            
            // Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            if (salesChart) salesChart.destroy();
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Sales (Rp)',
                        data: [2500000, 3200000, 2800000, 4100000, 3800000, 5200000, 4800000],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            
            // Recent orders
            renderRecentOrders();
        }
        
        function renderRecentOrders() {
            const recentOrders = orders.slice(0, 5);
            document.getElementById('recentOrdersTable').innerHTML = `
                <table>
                    <thead>
                        <tr><th>Order #</th><th>Customer</th><th>Stand</th><th>Total</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        ${recentOrders.map(order => `
                            <tr>
                                <td>${order.orderNumber}</td>
                                <td>${order.customer}</td>
                                <td>${order.stand}</td>
                                <td>Rp ${order.total.toLocaleString()}</td>
                                <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                                <td>${order.date}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function renderStands() {
            document.getElementById('standsTable').innerHTML = `
                <table>
                    <thead><tr><th>Logo</th><th>Name</th><th>Description</th><th>Total Sales</th><th>Orders</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        ${stands.map(stand => `
                            <tr>
                                <td style="font-size:1.5rem">${stand.logo}</td>
                                <td><strong>${stand.name}</strong></td>
                                <td>${stand.description}</td>
                                <td>Rp ${stand.totalSales.toLocaleString()}</td>
                                <td>${stand.orders}</td>
                                <td><span class="status-badge status-${stand.status}">${stand.status}</span></td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="editStand(${stand.id})">Edit</button>
                                    <button class="action-btn btn-delete" onclick="deleteStand(${stand.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function renderUsers() {
            document.getElementById('usersTable').innerHTML = `
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Orders</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td><strong>${user.name}</strong></td>
                                <td>${user.email}</td>
                                <td><span class="status-badge">${user.role}</span></td>
                                <td>${user.orders}</td>
                                <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="editUser(${user.id})">Edit</button>
                                    <button class="action-btn btn-delete" onclick="deleteUser(${user.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function renderAllOrders() {
            document.getElementById('allOrdersTable').innerHTML = `
                <table>
                    <thead><tr><th>Order #</th><th>Customer</th><th>Stand</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td>${order.orderNumber}</td>
                                <td>${order.customer}</td>
                                <td>${order.stand}</td>
                                <td>Rp ${order.total.toLocaleString()}</td>
                                <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                                <td>${order.date}</td>
                                <td>
                                    <select onchange="updateOrderStatus(${order.id}, this.value)" class="form-control" style="width:120px">
                                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                        <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>Ready</option>
                                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    </select>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function renderPromos() {
            document.getElementById('promosTable').innerHTML = `
                <table>
                    <thead><tr><th>Name</th><th>Discount Type</th><th>Value</th><th>Valid Until</th><th>Actions</th></tr></thead>
                    <tbody>
                        ${promos.map(promo => `
                            <tr>
                                <td><strong>${promo.name}</strong></td>
                                <td>${promo.type === 'percentage' ? 'Percentage' : 'Nominal'}</td>
                                <td>${promo.type === 'percentage' ? promo.value + '%' : 'Rp ' + promo.value.toLocaleString()}</td>
                                <td>${promo.endDate}</td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="editPromo(${promo.id})">Edit</button>
                                    <button class="action-btn btn-delete" onclick="deletePromo(${promo.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function loadReport() {
            const date = document.getElementById('reportDate').value || new Date().toISOString().split('T')[0];
            const reportData = orders.filter(o => o.date === date);
            
            const totalSales = reportData.reduce((sum, o) => sum + o.total, 0);
            document.getElementById('reportStats').innerHTML = `
                <div class="stat-card"><div class="stat-info"><h3>Daily Sales</h3><div class="stat-number">Rp ${totalSales.toLocaleString()}</div></div><div class="stat-icon">💰</div></div>
                <div class="stat-card"><div class="stat-info"><h3>Total Orders</h3><div class="stat-number">${reportData.length}</div></div><div class="stat-icon">📦</div></div>
            `;
            
            document.getElementById('reportTable').innerHTML = `
                <table>
                    <thead><tr><th>Order #</th><th>Stand</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        ${reportData.map(order => `
                            <tr><td>${order.orderNumber}</td><td>${order.stand}</td><td>Rp ${order.total.toLocaleString()}</td><td>${order.status}</td></tr>
                        `).join('')}
                        ${reportData.length === 0 ? '<tr><td colspan="4" style="text-align:center">No orders for this date</td></tr>' : ''}
                    </tbody>
                </table>
            `;
        }
        
        // CRUD Operations
        function openStandModal(stand = null) {
            if (stand) {
                document.getElementById('standId').value = stand.id;
                document.getElementById('standName').value = stand.name;
                document.getElementById('standLogo').value = stand.logo;
                document.getElementById('standDesc').value = stand.description;
                document.getElementById('standStatus').value = stand.status;
            } else {
                document.getElementById('standForm').reset();
                document.getElementById('standId').value = '';
            }
            document.getElementById('standModal').style.display = 'flex';
        }
        
        function editStand(id) {
            const stand = stands.find(s => s.id === id);
            if (stand) openStandModal(stand);
        }
        
        function deleteStand(id) {
            if (confirm('Delete this stand?')) {
                stands = stands.filter(s => s.id !== id);
                renderStands();
                loadDashboard();
            }
        }
        
        function openUserModal(user = null) {
            if (user) {
                document.getElementById('userId').value = user.id;
                document.getElementById('userName').value = user.name;
                document.getElementById('userEmail').value = user.email;
                document.getElementById('userRole').value = user.role;
            } else {
                document.getElementById('userForm').reset();
                document.getElementById('userId').value = '';
            }
            document.getElementById('userModal').style.display = 'flex';
        }
        
        function editUser(id) {
            const user = users.find(u => u.id === id);
            if (user) openUserModal(user);
        }
        
        function deleteUser(id) {
            if (confirm('Delete this user?')) {
                users = users.filter(u => u.id !== id);
                renderUsers();
                loadDashboard();
            }
        }
        
        function openPromoModal(promo = null) {
            if (promo) {
                document.getElementById('promoId').value = promo.id;
                document.getElementById('promoName').value = promo.name;
                document.getElementById('promoType').value = promo.type;
                document.getElementById('promoValue').value = promo.value;
                document.getElementById('promoEndDate').value = promo.endDate;
            } else {
                document.getElementById('promoForm').reset();
                document.getElementById('promoId').value = '';
            }
            document.getElementById('promoModal').style.display = 'flex';
        }
        
        function editPromo(id) {
            const promo = promos.find(p => p.id === id);
            if (promo) openPromoModal(promo);
        }
        
        function deletePromo(id) {
            if (confirm('Delete this promo?')) {
                promos = promos.filter(p => p.id !== id);
                renderPromos();
            }
        }
        
        function updateOrderStatus(orderId, status) {
            const order = orders.find(o => o.id === orderId);
            if (order) {
                order.status = status;
                renderAllOrders();
                loadDashboard();
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function logout() {
            localStorage.clear();
            window.location.href = 'login.html';
        }
        
        // Form submissions
        document.getElementById('standForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('standId').value;
            const stand = {
                id: id ? parseInt(id) : stands.length + 1,
                name: document.getElementById('standName').value,
                logo: document.getElementById('standLogo').value,
                description: document.getElementById('standDesc').value,
                status: document.getElementById('standStatus').value,
                totalSales: 0,
                orders: 0
            };
            if (id) {
                const index = stands.findIndex(s => s.id === parseInt(id));
                stands[index] = stand;
            } else {
                stands.push(stand);
            }
            closeModal('standModal');
            renderStands();
            loadDashboard();
        });
        
        document.getElementById('userForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('userId').value;
            const user = {
                id: id ? parseInt(id) : users.length + 1,
                name: document.getElementById('userName').value,
                email: document.getElementById('userEmail').value,
                role: document.getElementById('userRole').value,
                status: 'active',
                orders: 0
            };
            if (id) {
                const index = users.findIndex(u => u.id === parseInt(id));
                users[index] = user;
            } else {
                users.push(user);
            }
            closeModal('userModal');
            renderUsers();
            loadDashboard();
        });
        
        document.getElementById('promoForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('promoId').value;
            const promo = {
                id: id ? parseInt(id) : promos.length + 1,
                name: document.getElementById('promoName').value,
                type: document.getElementById('promoType').value,
                value: parseInt(document.getElementById('promoValue').value),
                endDate: document.getElementById('promoEndDate').value
            };
            if (id) {
                const index = promos.findIndex(p => p.id === parseInt(id));
                promos[index] = promo;
            } else {
                promos.push(promo);
            }
            closeModal('promoModal');
            renderPromos();
        });
        
        // Mobile menu toggle
        document.getElementById('menuToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        // Initialize
        loadDashboard();
    </script>
</body>
</html>