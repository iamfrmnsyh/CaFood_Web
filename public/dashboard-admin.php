<?php
$pageTitle = 'CaFood Admin Dashboard';
require_once __DIR__ . '/../app/includes/head.php';

$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? 'customer') !== 'admin') {
    header('Location: /public/login.php');
    exit;
}

require_once __DIR__ . '/../app/includes/sidebar-admin.php';
?>

<?php /* dashboard-admin.html converted to PHP with includes */ ?>

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
                <div class="selected-stand-info">
                    <div><i class="fas fa-store"></i> <span class="selected-stand-name" id="selectedStandName"></span></div>
                    <div>
                        <button class="btn-back" onclick="backToStandSelector()"><i class="fas fa-arrow-left"></i> Ganti Stand</button>
                        <button class="btn-add" onclick="openMenuModal()"><i class="fas fa-plus"></i> Tambah Menu</button>
                    </div>
                </div>
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

<!-- modals + JS tetap akan kamu migrasi full 1:1 berikutnya; untuk tahap output contoh inti DOM tetap ada -->

<script>
// Placeholder logout
window.handleLogout = function() {
    if (confirm('Logout?')) {
        window.location.href = '/public/api/auth/logout.php';
    }
};
</script>

<?php require_once __DIR__ . '/../app/includes/footer.php'; ?>

