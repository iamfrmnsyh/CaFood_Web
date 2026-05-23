<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood • Menu Stand</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; padding-bottom: 100px; }
        .stand-header { position: relative; height: 200px; background: linear-gradient(135deg, #6C4CF1, #8B5CF6); overflow: hidden; }
        .stand-cover { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; }
        .back-btn { position: absolute; top: 20px; left: 20px; width: 40px; height: 40px; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; cursor: pointer; z-index: 10; }
        .cart-icon-header { position: absolute; top: 20px; right: 20px; width: 40px; height: 40px; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; cursor: pointer; z-index: 10; }
        .cart-count-badge { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.6rem; display: flex; align-items: center; justify-content: center; }
        .stand-info-card { background: white; border-radius: 30px 30px 0 0; margin-top: -30px; position: relative; z-index: 5; padding: 20px; }
        .stand-name { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .stand-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #e9ecef; }
        .stand-rating { display: flex; align-items: center; gap: 4px; background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; color: #f59e0b; }
        .stand-desc { color: #64748b; font-size: 0.85rem; line-height: 1.5; }
        .menu-section { background: white; padding: 0 16px 24px; }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .menu-list { display: flex; flex-direction: column; gap: 12px; }
        .menu-item { display: flex; gap: 14px; padding: 12px; background: #f8f9fa; border-radius: 16px; transition: all 0.2s; border: 1px solid #e9ecef; }
        .menu-item:hover { border-color: #6C4CF1; background: white; box-shadow: 0 2px 8px rgba(108,76,241,0.1); }
        .menu-image { width: 70px; height: 70px; border-radius: 12px; overflow: hidden; flex-shrink: 0; background: #e2e8f0; }
        .menu-image img { width: 100%; height: 100%; object-fit: cover; }
        .menu-info { flex: 1; }
        .menu-name { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .menu-desc { font-size: 0.7rem; color: #64748b; margin-bottom: 8px; }
        .menu-price { font-size: 0.9rem; font-weight: 700; color: #6C4CF1; }
        .menu-actions { display: flex; align-items: center; gap: 12px; }
        .qty-selector { display: flex; align-items: center; gap: 8px; background: white; padding: 4px 8px; border-radius: 30px; border: 1px solid #e2e8f0; }
        .qty-btn { width: 24px; height: 24px; border-radius: 50%; border: none; background: #f1f5f9; cursor: pointer; font-weight: bold; }
        .qty-value { font-size: 0.85rem; font-weight: 600; min-width: 24px; text-align: center; }
        .add-btn { background: linear-gradient(135deg, #6C4CF1, #8B5CF6); color: white; border: none; padding: 6px 16px; border-radius: 30px; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
        .cart-summary { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e9ecef; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; z-index: 100; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
        .cart-total { display: flex; align-items: baseline; gap: 8px; }
        .cart-total-label { font-size: 0.75rem; color: #64748b; }
        .cart-total-value { font-size: 1.2rem; font-weight: 800; color: #6C4CF1; }
        .cart-items-count { background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .view-cart-btn { background: linear-gradient(135deg, #6C4CF1, #8B5CF6); color: white; border: none; padding: 8px 20px; border-radius: 30px; font-weight: 600; font-size: 0.8rem; cursor: pointer; }
        .toast { position: fixed; bottom: 100px; left: 50%; transform: translateX(-50%); background: #1a1a2e; color: white; padding: 8px 16px; border-radius: 30px; font-size: 0.8rem; z-index: 1000; animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateX(-50%) translateY(20px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
        .loading { text-align: center; padding: 40px; color: #64748b; }
        .spinner { width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #6C4CF1; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .stand-header { height: 160px; } .stand-name { font-size: 1.2rem; } .back-btn, .cart-icon-header { top: 12px; width: 36px; height: 36px; font-size: 1rem; } .back-btn { left: 12px; } .cart-icon-header { right: 12px; } }
    </style>
</head>
<body>
    <div class="stand-header">
        <div class="back-btn" onclick="history.back()">←</div>
        <div class="cart-icon-header" onclick="window.location.href='keranjang.html'">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count-badge" id="cartCountHeader">0</span>
        </div>
        <img class="stand-cover" id="standCover" src="" alt="Stand Cover">
    </div>
    
    <div class="stand-info-card" id="standInfo"><div class="loading"><div class="spinner"></div>Memuat stand...</div></div>
    <div class="menu-section">
        <div class="section-title"><i class="fas fa-utensils" style="color:#6C4CF1;"></i> Daftar Menu</div>
        <div class="menu-list" id="menuList"><div class="loading"><div class="spinner"></div>Memuat menu...</div></div>
    </div>
    <div class="cart-summary" id="cartSummary"></div>
    
    <script type="module">
        import { db, collection, doc, getDoc, getDocs, query, where } from './js/firebase-config.js';
        
        const urlParams = new URLSearchParams(window.location.search);
        const standId = urlParams.get('id');
        
        let currentStand = null;
        let menus = [];
        let cart = [];
        
        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }
        
        async function loadStand() {
            try {
                const standRef = doc(db, "stands", standId);
                const standSnap = await getDoc(standRef);
                if (standSnap.exists()) {
                    const data = standSnap.data();
                    currentStand = { 
                        id: standSnap.id, ...data,
                        name: data.nama || data.name || 'Restaurant',
                        description: data.deskripsi || data.description || 'Delicious food',
                        estimatedTime: data.estimasiWaktu || data.estimatedTime || '15-20 min',
                        deliveryFee: data.biayaPengiriman || data.deliveryFee || 'Free',
                        imageUrl: data.gambarUrl || data.imageUrl || null,
                        rating: data.rating || 4.5,
                        status: data.status || 'Open'
                    };
                    renderStandInfo();
                } else {
                    document.getElementById('standInfo').innerHTML = '<div style="padding:20px; text-align:center;">Stand tidak ditemukan</div>';
                }
            } catch (error) { console.error(error); }
        }
        
        async function loadMenus() {
            try {
                const menusRef = collection(db, "menus");
                const q = query(menusRef, where("standId", "==", standId));
                const snapshot = await getDocs(q);
                menus = snapshot.docs.map(doc => ({ 
                    id: doc.id, ...doc.data(),
                    name: doc.data().name || doc.data().nama || 'Menu',
                    price: doc.data().price || doc.data().harga || 0,
                    description: doc.data().description || doc.data().deskripsi || 'Delicious food',
                    imageUrl: doc.data().image || doc.data().imageUrl || doc.data().gambarUrl || null,
                    available: doc.data().available || doc.data().status || 'Tersedia'
                }));
                menus = menus.filter(menu => menu.available === 'Tersedia');
                renderMenus();
            } catch (error) { console.error(error); }
        }
        
        function renderStandInfo() {
            const coverImg = document.getElementById('standCover');
            if (coverImg && currentStand.imageUrl) coverImg.src = currentStand.imageUrl;
            document.getElementById('standInfo').innerHTML = `
                <h1 class="stand-name">${currentStand.name}</h1>
                <div class="stand-meta">
                    <span class="stand-rating"><i class="fas fa-star"></i> ${currentStand.rating}</span>
                    <span><i class="far fa-clock"></i> ${currentStand.estimatedTime}</span>
                    <span><i class="fas fa-motorcycle"></i> ${currentStand.deliveryFee}</span>
                    <span style="color:${currentStand.status === 'Open' ? '#10b981' : '#ef4444'}"><i class="fas fa-circle"></i> ${currentStand.status}</span>
                </div>
                <p class="stand-desc">${currentStand.description}</p>
            `;
        }
        
        function renderMenus() {
            const container = document.getElementById('menuList');
            if (menus.length === 0) { container.innerHTML = '<div style="padding:20px; text-align:center;">Belum ada menu tersedia</div>'; return; }
            container.innerHTML = menus.map(menu => {
                const cartItem = cart.find(c => c.id === menu.id);
                const qty = cartItem ? cartItem.quantity : 0;
                return `
                    <div class="menu-item">
                        <div class="menu-image"><img src="${menu.imageUrl || 'https://via.placeholder.com/70?text=Food'}" onerror="this.src='https://via.placeholder.com/70?text=Food'"></div>
                        <div class="menu-info"><div class="menu-name">${menu.name}</div><div class="menu-desc">${menu.description}</div><div class="menu-price">${formatRupiah(menu.price)}</div></div>
                        <div class="menu-actions">
                            <div class="qty-selector"><button class="qty-btn" onclick="updateQuantity('${menu.id}', -1)">-</button><span class="qty-value" id="qty-${menu.id}">${qty}</span><button class="qty-btn" onclick="updateQuantity('${menu.id}', 1)">+</button></div>
                            <button class="add-btn" onclick="addToCart('${menu.id}')">Tambah</button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function loadCart() {
            const savedCart = localStorage.getItem('cart');
            const savedStand = localStorage.getItem('currentStand');
            if (savedCart && savedStand) {
                const savedStandData = JSON.parse(savedStand);
                if (savedStandData.id === standId) cart = JSON.parse(savedCart);
                else cart = [];
            }
            updateCartDisplay();
            updateQuantityDisplays();
        }
        
        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
            localStorage.setItem('currentStand', JSON.stringify({ id: standId, name: currentStand?.name }));
            updateCartDisplay();
        }
        
        function updateQuantityDisplays() {
            menus.forEach(menu => {
                const cartItem = cart.find(c => c.id === menu.id);
                const qtySpan = document.getElementById(`qty-${menu.id}`);
                if (qtySpan) qtySpan.textContent = cartItem ? cartItem.quantity : 0;
            });
        }
        
        function updateCartDisplay() {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            document.getElementById('cartCountHeader').textContent = totalItems;
            document.getElementById('cartSummary').innerHTML = cart.length === 0 ? 
                `<div class="cart-total"><span class="cart-total-label">Total:</span><span class="cart-total-value">Rp 0</span></div><button class="view-cart-btn" onclick="window.location.href='keranjang.html'">Lihat Keranjang</button>` :
                `<div class="cart-total"><span class="cart-total-label">Total:</span><span class="cart-total-value">${formatRupiah(totalPrice)}</span><span class="cart-items-count">${totalItems} item${totalItems > 1 ? 's' : ''}</span></div><button class="view-cart-btn" onclick="window.location.href='keranjang.html'">Checkout</button>`;
        }
        
        window.updateQuantity = function(menuId, change) {
            const menu = menus.find(m => m.id === menuId);
            if (!menu) return;
            const existingItem = cart.find(item => item.id === menuId);
            if (existingItem) {
                const newQty = existingItem.quantity + change;
                if (newQty <= 0) cart = cart.filter(item => item.id !== menuId);
                else existingItem.quantity = newQty;
            } else if (change > 0) cart.push({ id: menu.id, name: menu.name, price: menu.price, quantity: 1, image: menu.imageUrl, standId: standId, standName: currentStand?.name });
            saveCart();
            updateQuantityDisplays();
        };
        
        window.addToCart = function(menuId) {
            const menu = menus.find(m => m.id === menuId);
            if (!menu) return;
            const existingItem = cart.find(item => item.id === menuId);
            if (existingItem) existingItem.quantity++;
            else cart.push({ id: menu.id, name: menu.name, price: menu.price, quantity: 1, image: menu.imageUrl, standId: standId, standName: currentStand?.name });
            saveCart();
            updateQuantityDisplays();
            showToast(`${menu.name} ditambahkan ke keranjang!`);
        };
        
        function showToast(message) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }
        
        async function init() {
            if (standId) { await loadStand(); await loadMenus(); loadCart(); }
            else { document.getElementById('standInfo').innerHTML = '<div style="padding:20px; text-align:center;">Stand tidak dipilih</div>'; }
        }
        init();
    </script>
</body>
</html>