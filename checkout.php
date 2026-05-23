<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            padding-bottom: 40px;
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
            align-items: center;
            gap: 16px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .back-btn {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6C4CF1;
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .checkout-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-section {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            color: #1a1a2e;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.9rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6C4CF1;
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .payment-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .payment-option:hover {
            border-color: #6C4CF1;
            background: #f8f9fa;
        }
        
        .payment-option.selected {
            border-color: #6C4CF1;
            background: #f0f4ff;
        }
        
        .payment-icon {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .payment-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .payment-desc {
            font-size: 0.65rem;
            color: #64748b;
        }
        
        .qris-modal {
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
        
        .qris-modal-content {
            background: white;
            border-radius: 28px;
            padding: 28px;
            width: 90%;
            max-width: 380px;
            text-align: center;
        }
        
        .qris-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1a1a2e;
        }
        
        .qris-qr-code {
            width: 220px;
            height: 220px;
            margin: 0 auto 20px;
            background: #f1f5f9;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .qris-qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .qris-amount {
            font-size: 1.3rem;
            font-weight: 800;
            color: #6C4CF1;
            margin-bottom: 8px;
        }
        
        .qris-instruction {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .qris-close-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 20px;
        }
        
        .order-items {
            margin-bottom: 16px;
            max-height: 250px;
            overflow-y: auto;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row.total {
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .confirm-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a2e;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.8rem;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="back-btn" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="header-title">💳 Checkout</div>
        </div>
    </div>
    
    <div class="checkout-container" id="checkoutContainer">
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-user" style="color:#6C4CF1;"></i> Informasi Pemesan
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" id="customerName" placeholder="Masukkan nama Anda">
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" class="form-control" id="customerPhone" placeholder="Masukkan nomor telepon">
            </div>
            <div class="form-group">
                <label>Nomor Meja (Opsional)</label>
                <input type="text" class="form-control" id="tableNumber" placeholder="Nomor meja">
            </div>
            <div class="form-group">
                <label>Catatan (Opsional)</label>
                <textarea class="form-control" id="orderNotes" rows="2" placeholder="Catatan khusus untuk pesanan"></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-credit-card" style="color:#6C4CF1;"></i> Metode Pembayaran
            </div>
            <div class="payment-methods" id="paymentMethods">
                <div class="payment-option selected" data-method="cash">
                    <div class="payment-icon"><i class="fas fa-money-bill"></i></div>
                    <div>
                        <div class="payment-name">Tunai</div>
                        <div class="payment-desc">Bayar di kasir</div>
                    </div>
                </div>
                <div class="payment-option" data-method="qris">
                    <div class="payment-icon"><i class="fas fa-qrcode"></i></div>
                    <div>
                        <div class="payment-name">QRIS</div>
                        <div class="payment-desc">Scan QR code dengan e-wallet</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="order-summary" id="orderSummary">
            <div class="section-title">Ringkasan Pesanan</div>
            <div class="order-items" id="orderItems"></div>
            <div class="summary-row total">
                <span>Total</span>
                <span id="totalValue">Rp 0</span>
            </div>
            <button class="confirm-btn" id="confirmBtn">Konfirmasi Pesanan</button>
        </div>
    </div>
    
    <!-- QRIS Modal -->
    <div id="qrisModal" class="qris-modal">
        <div class="qris-modal-content">
            <div class="qris-title">Scan QRIS untuk Membayar</div>
            <div class="qris-qr-code">
                <img id="qrisImage" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=QRIS_CaFood" alt="QR Code">
            </div>
            <div class="qris-amount" id="qrisAmount">Rp 0</div>
            <div class="qris-instruction">
                <i class="fas fa-mobile-alt"></i> Buka aplikasi e-wallet atau mobile banking<br>
                Scan QR code di atas untuk melakukan pembayaran
            </div>
            <button class="qris-close-btn" id="qrisConfirmBtn">Saya Sudah Bayar</button>
            <button class="qris-close-btn" id="qrisCancelBtn" style="background:#6B7280; margin-top:8px;">Batal</button>
        </div>
    </div>
    
    <script type="module">
        import { db, auth, collection, getDocs, query, where, addDoc, deleteDoc, onAuthStateChanged } from './js/firebase-config.js';
        
        let currentUser = null;
        let cartItems = [];
        let selectedPayment = 'cash';
        let isProcessing = false;
        
        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }
        
        async function loadCart() {
            if (!currentUser) {
                window.location.href = 'login.html';
                return;
            }
            
            try {
                const cartsRef = collection(db, "carts");
                const q = query(cartsRef, where("userId", "==", currentUser.uid));
                const snapshot = await getDocs(q);
                
                if (snapshot.empty) {
                    window.location.href = 'keranjang.html';
                    return;
                }
                
                const itemsMap = new Map();
                snapshot.docs.forEach(doc => {
                    const data = doc.data();
                    const menuId = data.menuId;
                    if (itemsMap.has(menuId)) {
                        itemsMap.get(menuId).quantity += data.quantity;
                    } else {
                        itemsMap.set(menuId, {
                            id: menuId,
                            name: data.menuName,
                            price: data.menuPrice,
                            image: data.menuImage,
                            quantity: data.quantity,
                            standId: data.standId,
                            standName: data.standName
                        });
                    }
                });
                
                cartItems = Array.from(itemsMap.values());
                renderCheckout();
            } catch (error) {
                console.error("Error loading cart:", error);
            }
        }
        
        function renderCheckout() {
            const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            const orderItemsDiv = document.getElementById('orderItems');
            orderItemsDiv.innerHTML = cartItems.map(item => `
                <div class="order-item">
                    <span>${item.quantity}x ${item.name}</span>
                    <span>${formatRupiah(item.price * item.quantity)}</span>
                </div>
            `).join('');
            
            document.getElementById('totalValue').textContent = formatRupiah(total);
            document.getElementById('qrisAmount').textContent = formatRupiah(total);
            
            const savedUser = localStorage.getItem('currentUser');
            if (savedUser) {
                const user = JSON.parse(savedUser);
                document.getElementById('customerName').value = user.name || '';
            }
        }
        
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                selectedPayment = this.dataset.method;
            });
        });
        
        function generateOrderNumber() {
            return 'ORD-' + Date.now().toString().slice(-8);
        }
        
        function generateQueueNumber() {
            return Math.floor(Math.random() * 50) + 1;
        }
        
        async function clearCart() {
            const cartsRef = collection(db, "carts");
            const q = query(cartsRef, where("userId", "==", currentUser.uid));
            const snapshot = await getDocs(q);
            const deletePromises = snapshot.docs.map(doc => deleteDoc(doc.ref));
            await Promise.all(deletePromises);
        }
        
        function openQrisModal() {
            document.getElementById('qrisModal').style.display = 'flex';
        }
        
        function closeQrisModal() {
            document.getElementById('qrisModal').style.display = 'none';
        }
        
        async function saveOrder() {
            if (isProcessing) return;
            isProcessing = true;
            
            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.innerHTML = '<span class="spinner"></span> Memproses...';
            confirmBtn.disabled = true;
            
            const customerName = document.getElementById('customerName').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            
            if (!customerName || !customerPhone) {
                alert('Mohon isi nama dan nomor telepon Anda!');
                confirmBtn.innerHTML = 'Konfirmasi Pesanan';
                confirmBtn.disabled = false;
                isProcessing = false;
                return;
            }
            
            const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const estimatedTime = 15;
            const queueNumber = generateQueueNumber();
            const orderNumber = generateOrderNumber();
            
            const paymentNames = {
                'cash': 'Tunai',
                'qris': 'QRIS'
            };
            
            const orderData = {
                orderNumber: orderNumber,
                queueNumber: queueNumber,
                customerName: customerName,
                customerPhone: customerPhone,
                tableNumber: document.getElementById('tableNumber').value || '-',
                notes: document.getElementById('orderNotes').value,
                standId: cartItems[0]?.standId,
                standName: cartItems[0]?.standName,
                items: cartItems.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity,
                    subtotal: item.price * item.quantity
                })),
                total: total,
                paymentMethod: paymentNames[selectedPayment] || 'Tunai',
                estimatedTime: estimatedTime,
                userId: currentUser?.uid,
                status: 'pending',
                createdAt: new Date().toISOString()
            };
            
            try {
                const ordersRef = collection(db, "orders");
                await addDoc(ordersRef, orderData);
                await clearCart();
                localStorage.setItem('currentOrder', JSON.stringify(orderData));
                
                showToast('✅ Pesanan berhasil dibuat!');
                
                setTimeout(() => {
                    window.location.href = 'status-pesanan.html';
                }, 1500);
            } catch (error) {
                console.error("Error saving order:", error);
                alert('Gagal membuat pesanan. Silakan coba lagi.');
                confirmBtn.innerHTML = 'Konfirmasi Pesanan';
                confirmBtn.disabled = false;
                isProcessing = false;
            }
        }
        
        async function confirmOrder() {
            const customerName = document.getElementById('customerName').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            
            if (!customerName || !customerPhone) {
                alert('Mohon isi nama dan nomor telepon Anda!');
                return;
            }
            
            if (selectedPayment === 'qris') {
                openQrisModal();
                return;
            }
            
            await saveOrder();
        }
        
        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.background = '#10B981';
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }
        
        // Event listeners
        document.getElementById('confirmBtn').addEventListener('click', confirmOrder);
        document.getElementById('qrisConfirmBtn').addEventListener('click', async () => {
            closeQrisModal();
            await saveOrder();
        });
        document.getElementById('qrisCancelBtn').addEventListener('click', closeQrisModal);
        
        onAuthStateChanged(auth, async (user) => {
            if (user) {
                currentUser = user;
                await loadCart();
            } else {
                window.location.href = 'login.html';
            }
        });
    </script>
</body>
</html>