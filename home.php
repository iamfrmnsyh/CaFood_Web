<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #F8F9FA;
            overflow-x: hidden;
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
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
        }
        
        .logo-text {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
            width: 42px;
            height: 42px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            color: #6C4CF1;
        }
        
        .cart-icon:hover {
            background: #e2e8f0;
        }
        
        .cart-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .menu-icon {
            width: 42px;
            height: 42px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #6C4CF1;
            font-size: 1.2rem;
        }
        
        .menu-icon:hover {
            background: #e2e8f0;
        }
        
        .side-menu {
            position: fixed;
            top: 0;
            right: -300px;
            width: 280px;
            height: 100vh;
            background: white;
            z-index: 200;
            transition: right 0.3s ease;
            box-shadow: -4px 0 20px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        
        .side-menu.open { right: 0; }
        
        .side-menu-header {
            padding: 24px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
        }
        
        .side-menu-header h3 { margin-bottom: 8px; }
        .side-menu-header p { font-size: 0.8rem; opacity: 0.8; }
        
        .side-menu-items { flex: 1; padding: 16px 0; }
        
        .side-menu-item {
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: all 0.2s;
            color: #374151;
            border-left: 3px solid transparent;
        }
        
        .side-menu-item:hover {
            background: #F3F4F6;
            color: #6C4CF1;
        }
        
        .side-menu-item.active {
            background: #F3F4F6;
            color: #6C4CF1;
            border-left-color: #6C4CF1;
        }
        
        .side-menu-item i { width: 24px; font-size: 1.1rem; }
        
        .side-menu-footer {
            padding: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .logout-side-btn {
            width: 100%;
            padding: 12px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 199;
            display: none;
        }
        
        .menu-overlay.active { display: block; }
        
        .hero {
            padding: 40px 20px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            text-align: center;
        }
        
        .hero-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .hero-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .hero-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 28px;
        }
        
        .search-bar {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }
        
        .search-bar input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: none;
            background: white;
            border-radius: 50px;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #1a1a2e;
        }
        
        .search-bar input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(108,76,241,0.3);
        }
        
        .search-bar input::placeholder { color: #94a3b8; }
        
        .search-bar i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #6C4CF1;
        }
        
        .stands-section {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .section-link {
            color: #6C4CF1;
            font-size: 0.75rem;
            cursor: pointer;
        }
        
        .stand-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .stand-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .stand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(108,76,241,0.15);
            border-color: #6C4CF1;
        }
        
        .stand-image {
            height: 160px;
            overflow: hidden;
        }
        
        .stand-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .stand-card:hover .stand-image img { transform: scale(1.05); }
        
        .stand-info { padding: 16px; }
        
        .stand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        
        .stand-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .stand-meta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            color: #64748b;
        }
        
        .rating { color: #f59e0b; }
        .status-open { color: #10b981; }
        .status-closed { color: #ef4444; }
        
        .loading {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f1f5f9;
            border-top-color: #6C4CF1;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 12px;
        }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #94a3b8;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .fab {
            position: fixed;
            bottom: 30px;
            right: 16px;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(108,76,241,0.4);
            transition: all 0.2s;
            z-index: 99;
        }
        
        .fab:hover { transform: scale(1.05); }
        
        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a2e;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.8rem;
            z-index: 1000;
            animation: fadeInUp 0.3s ease;
        }
        
        @keyframes fadeInUp {
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
            .stand-grid { grid-template-columns: 1fr; gap: 16px; }
            .hero-title { font-size: 1.4rem; }
            .side-menu { width: 260px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-utensils"></i></div>
                <div class="logo-text">CaFood</div>
            </div>
            <div class="header-right">
onclick="window.location.href='keranjang.php'
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
                <div class="menu-icon" onclick="toggleMenu()">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="menu-overlay" id="menuOverlay" onclick="toggleMenu()"></div>
    <div class="side-menu" id="sideMenu">
        <div class="side-menu-header">
            <h3><i class="fas fa-user-circle"></i> Menu</h3>
            <p id="userNameDisplay">Selamat Datang!</p>
        </div>
        <div class="side-menu-items">
navigateTo('home.php')
                <i class="fas fa-home"></i> <span>Beranda</span>
            </div>
navigateTo('status-pesanan.php')
                <i class="fas fa-clipboard-list"></i> <span>Pesanan Saya</span>
            </div>
navigateTo('keranjang.php')
                <i class="fas fa-shopping-cart"></i> <span>Keranjang</span>
            </div>
            <div class="side-menu-item" onclick="navigateTo('profile.php')">

                <i class="fas fa-user"></i> <span>Profil Saya</span>
            </div>
            <div class="side-menu-item" id="dashboardLink" style="display: none;" onclick="navigateToDashboard()">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </div>
        </div>
        <div class="side-menu-footer">
            <button class="logout-side-btn" onclick="handleLogout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <div class="hero">
        <div class="hero-badge">
            <i class="fas fa-fire"></i> <span id="standCount">0</span> Stand Aktif
        </div>
        <h1 class="hero-title" id="heroTitle">Selamat Sore! 🌅<br>Camilan Sore?</h1>
        <p class="hero-subtitle">Jelajahi berbagai makanan lezat dari stand terbaik</p>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari Stand CaFood">
        </div>
    </div>
    
    <div class="stands-section">
        <div class="section-header">
            <h2 class="section-title">Stand</h2>
            <span class="section-link" onclick="showAllStands()">Lihat Semua</span>
        </div>
        <div id="standsGrid" class="stand-grid">
            <div class="loading"><div class="spinner"></div><div>Memuat stand...</div></div>
        </div>
    </div>
    
    <div class="fab" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script type="module">
        import './js/api-client.js';
        const { getStands } = window.API;

        
        // ============ GLOBAL VARIABLES ============
        let allStands = [];
        let searchQuery = "";
        let currentUser = null;
        
        const standsGrid = document.getElementById('standsGrid');
        const searchInput = document.getElementById('searchInput');
        const standCountSpan = document.getElementById('standCount');
        
        function setGreeting() {
            const hour = new Date().getHours();
            const heroTitle = document.getElementById('heroTitle');
            if (hour >= 5 && hour < 11) {
                heroTitle.innerHTML = "Selamat Pagi! ☀️<br>Apa Sarapanmu Hari Ini?";
            } else if (hour >= 11 && hour < 15) {
                heroTitle.innerHTML = "Selamat Siang! 🌤️<br>Waktunya Makan Siang?";
            } else if (hour >= 15 && hour < 19) {
                heroTitle.innerHTML = "Selamat Sore! 🌅<br>Camilan Sore?";
            } else {
                heroTitle.innerHTML = "Selamat Malam! 🌙<br>Ngidam Larut Malam?";
            }
        }
        
        // ============ LOAD CARTS COUNT FROM FIRESTORE ============
        async function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = Array.isArray(cart) ? cart.reduce((sum, it) => sum + (it.quantity || 0), 0) : 0;
            const cartCount = document.getElementById('cartCount');
            if (cartCount) cartCount.textContent = String(totalItems);
        }

        
        // ============ LOAD STANDS ============
        async function loadStands() {
            try {
                showsLoading(true);
            const result = await getStands();
                const stands = Array.isArray(result) ? result : (result?.data || []);


                allStands = stands.map(s => ({
                    id: s.id,
                    ...s,
                    name: s.nama || s.name || 'No Name',
                    description: s.deskripsi || s.description || 'Delicious food available',
                    estimatedTime: s.estimasiWaktu || s.estimatedTime || s.estTime || '15-20 min',
                    deliveryFee: s.biayaPengiriman || s.deliveryFee || 'Free',
                    imageUrl: s.gambarUrl || s.imageUrl || s.image || null,
                    rating: s.rating || 4.5,
                    status: s.status || 'Open'
                }));

                const activeStands = allStands.filter(s => s.status === 'Open').length;
                standCountSpan.textContent = activeStands;

                renderStands();
                showsLoading(false);
            } catch (error) {
                console.error("Error loading stands:", error);
                standsGrid.innerHTML = `<div class="empty-state"><div class="empty-icon">⚠️</div><div>Gagal memuat data</div><div style="font-size:0.75rem; margin-top:8px;">${error.message}</div></div>`;
            }
        }

        
        function getFilteredStands() {
            let filtered = [...allStands];
            if (searchQuery.trim() !== "") {
                const queryLower = searchQuery.toLowerCase();
                filtered = filtered.filter(stand => 
                    (stand.name && stand.name.toLowerCase().includes(queryLower)) ||
                    (stand.description && stand.description.toLowerCase().includes(queryLower))
                );
            }
            return filtered;
        }
        
        function renderStands() {
            const filtered = getFilteredStands();
            if (filtered.length === 0) {
                standsGrid.innerHTML = `<div class="empty-state"><div class="empty-icon">🔍</div><div>Stand tidak ditemukan</div><div style="font-size:0.75rem; margin-top:8px;">Coba kata kunci lain</div></div>`;
                return;
            }
            
            standsGrid.innerHTML = filtered.map(stand => `
                <div class="stand-card" onclick="goToStand('${stand.id}')">
                    <div class="stand-image">
                        <img src="${stand.imageUrl || 'https://via.placeholder.com/400x160?text=Food'}" 
                             alt="${stand.name}"
                             onerror="this.src='https://via.placeholder.com/400x160?text=No+Image'">
                    </div>
                    <div class="stand-info">
                        <div class="stand-name">${stand.name}</div>
                        <div class="stand-desc">${stand.description || 'Delicious food available'}</div>
                        <div class="stand-meta">
                            <span class="meta-item rating"><i class="fas fa-star"></i> ${stand.rating || 4.5}</span>
                            <span class="meta-item"><i class="fas fa-clock"></i> ${stand.estimatedTime || '15-20 min'}</span>
                            <span class="meta-item"><i class="fas fa-truck"></i> ${stand.deliveryFee || 'Free'}</span>
                            <span class="meta-item ${stand.status === 'Open' ? 'status-open' : 'status-closed'}">
                        <i class="fas fa-circle"></i> ${stand.status || 'Open'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        
        function showsLoading(show) {
            if (show) {
                standsGrid.innerHTML = `<div class="loading"><div class="spinner"></div><div>Memuat stand...</div></div>`;
            }
        }
        
            window.goToStand = function(standId) {
            window.location.href = `menu-stand.php?id=${standId}`;
        };

        
        window.navigateTo = function(url) {
            toggleMenu();
            setTimeout(() => { window.location.href = url; }, 200);
        };
        
        window.navigateToDashboard = function() {
            if (currentUser) {
window.location.href = 'dashboard-stand.php';
window.location.href = 'dashboard-admin.php';
            }
            toggleMenu();
        };
        
        window.showAllStands = function() {
            window.scrollTo({ top: 400, behavior: 'smooth' });
        };
        
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        window.toggleMenu = function() {
            const sideMenu = document.getElementById('sideMenu');
            const overlay = document.getElementById('menuOverlay');
            sideMenu.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sideMenu.classList.contains('open') ? 'hidden' : '';
        };
        
        async function checkLogin() {
            return new Promise((resolve) => {
                onAuthStateChanged(auth, async (user) => {
                    if (user) {
                        currentUser = { uid: user.uid, email: user.email, role: 'customer' };
                        try {
                            const usersRef = collection(db, "users");
                            const q = query(usersRef, where("email", "==", user.email));
                            const snapshot = await getDocs(q);
                            if (!snapshot.empty) {
                                const userData = snapshot.docs[0].data();
                                currentUser.role = userData.role || 'customer';
                                currentUser.name = userData.name || userData.full_name || user.email.split('@')[0];
                            }
                        } catch (err) { console.log("Error getting user role:", err); }
                        
                        const userNameDisplay = document.getElementById('userNameDisplay');
                        if (userNameDisplay) userNameDisplay.textContent = `Halo, ${currentUser.name || currentUser.email.split('@')[0]}!`;
                        
                        const dashboardLink = document.getElementById('dashboardLink');
                        if (dashboardLink && (currentUser.role === 'stand' || currentUser.role === 'admin')) {
                            dashboardLink.style.display = 'flex';
                        }
                        
                        await updateCartCount();
                    } else {
                        currentUser = null;
                        const userNameDisplay = document.getElementById('userNameDisplay');
                        if (userNameDisplay) userNameDisplay.textContent = 'Selamat Datang!';
                        const cartCount = document.getElementById('cartCount');
                        if (cartCount) cartCount.textContent = "0";
                    }
                    resolve(currentUser);
                });
            });
        }
        
        window.handleLogout = async function() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                try {
                    await signOut(auth);
                    localStorage.removeItem('currentUser');
                    showToast('Logout berhasil!');
            window.location.href = 'login.php';
                } catch (error) {
                    showToast('Gagal logout!', true);
                }
            }
        };
        
        function showToast(message, isError = false) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = isError ? '#EF4444' : '#10B981';
            toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            renderStands();
        });
        
        document.addEventListener('click', function(event) {
            const sideMenu = document.getElementById('sideMenu');
            const menuIcon = document.querySelector('.menu-icon');
            const overlay = document.getElementById('menuOverlay');
            if (sideMenu && sideMenu.classList.contains('open')) {
                if (!sideMenu.contains(event.target) && !menuIcon.contains(event.target)) {
                    toggleMenu();
                }
            }
        });
        
        async function init() {
            setGreeting();
            await checkLogin();
            await loadStands();
        }
        
        init();
    </script>
</body>
</html>