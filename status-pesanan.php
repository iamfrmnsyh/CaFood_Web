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
            color: #6C4CF1;
            font-size: 1.2rem;
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .status-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Queue Card */
        .queue-card {
            background: linear-gradient(135deg, #6C4CF1, #8B5CF6);
            border-radius: 24px;
            padding: 28px;
            text-align: center;
            color: white;
            margin-bottom: 24px;
        }
        
        .queue-label {
            font-size: 0.75rem;
            opacity: 0.8;
            letter-spacing: 1px;
        }
        
        .queue-number {
            font-size: 3rem;
            font-weight: 800;
            margin: 12px 0;
            letter-spacing: 2px;
        }
        
        .order-number {
            font-size: 0.7rem;
            opacity: 0.7;
        }
        
        /* Timeline */
        .timeline-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
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
            color: #1a1a2e;
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
        
        /* Estimated Card */
        .estimated-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
        }
        
        .estimated-label {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        
        .estimated-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #6C4CF1;
        }
        
        /* Details Card */
        .details-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .details-title {
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
            color: #1a1a2e;
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
            color: #1a1a2e;
        }
        
        .items-list {
            margin-top: 12px;
        }
        
        .items-list .detail-row {
            padding: 6px 0;
            border-bottom: 1px dashed #e9ecef;
        }
        
        /* Action Buttons */
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
        }
        
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
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 24px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #6C4CF1;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
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
            .step-label { font-size: 0.6rem; }
            .queue-number { font-size: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="back-btn" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="header-title">📦 Status Pesanan</div>
        </div>
    </div>
    
    <div class="status-container" id="statusContainer">
        <div class="empty-state">
            <div class="spinner"></div>
            <div>Memuat status pesanan...</div>
        </div>
    </div>
    
    <script type="module">
        import { db, auth, collection, doc, getDoc, getDocs, onAuthStateChanged } from './js/firebase-config.js';
        
        let currentOrder = null;
        let currentUser = null;
        
        const statusSteps = [
            { id: 'pending', label: 'Menunggu Konfirmasi', icon: '🕐' },
            { id: 'confirmed', label: 'Dikonfirmasi', icon: '✅' },
            { id: 'processing', label: 'Diproses', icon: '🍳' },
            { id: 'ready', label: 'Siap Diambil', icon: '🛎️' },
            { id: 'completed', label: 'Selesai', icon: '🎉' }
        ];
        
        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }
        
        function formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
        
        async function loadStatus() {
            // Try to get current order from localStorage
            currentOrder = JSON.parse(localStorage.getItem('currentOrder') || 'null');
            
            // If no current order, try to get latest from Firestore for this user
            if (!currentOrder && currentUser) {
                try {
                    const ordersRef = collection(db, "orders");
                    const snapshot = await getDocs(ordersRef);
                    const userOrders = snapshot.docs
                        .map(doc => ({ id: doc.id, ...doc.data() }))
                        .filter(o => o.userId === currentUser.uid)
                        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
                    
                    if (userOrders.length > 0) {
                        currentOrder = userOrders[0];
                        localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                    }
                } catch (error) {
                    console.error("Error loading orders:", error);
                }
            }
            
            if (!currentOrder) {
                document.getElementById('statusContainer').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-receipt" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <h3>Tidak Ada Pesanan Aktif</h3>
                        <p style="color: #64748b; margin-top: 8px;">Anda belum memiliki pesanan</p>
                        <button class="btn-primary" onclick="goToHome()" style="margin-top: 24px; display: inline-block; padding: 12px 32px;">
                            <i class="fas fa-store"></i> Mulai Belanja
                        </button>
                    </div>
                `;
                return;
            }
            
            renderStatus();
        }
        
        function getCurrentStepIndex() {
            const status = currentOrder.status || 'pending';
            const index = statusSteps.findIndex(step => step.id === status);
            return index !== -1 ? index : 0;
        }
        
        function getEstimatedTimeDisplay() {
            // Ambil estimasi dari order atau default
            const estimatedTime = currentOrder.estimatedTime || 15;
            const status = currentOrder.status;
            
            if (status === 'pending') {
                return `${estimatedTime} menit`;
            } else if (status === 'confirmed') {
                return `${Math.max(5, estimatedTime - 5)} menit`;
            } else if (status === 'processing') {
                return `${Math.max(2, Math.floor(estimatedTime / 2))} menit`;
            } else if (status === 'ready') {
                return 'Siap diambil!';
            } else if (status === 'completed') {
                return 'Selesai';
            }
            return `${estimatedTime} menit`;
        }
        
        function renderStatus() {
            const container = document.getElementById('statusContainer');
            const currentStepIndex = getCurrentStepIndex();
            const estimatedDisplay = getEstimatedTimeDisplay();
            
            // Hitung total harga dari items
            const total = currentOrder.total || currentOrder.items?.reduce((sum, item) => sum + (item.price * item.quantity), 0) || 0;
            
            container.innerHTML = `
                <div class="queue-card">
                    <div class="queue-label">NOMOR ANTREAN ANDA</div>
                    <div class="queue-number">#${currentOrder.queueNumber || '--'}</div>
                    <div class="order-number">${currentOrder.orderNumber || 'ORD-XXXX'}</div>
                </div>
                
                <div class="timeline-card">
                    <div class="timeline">
                        ${statusSteps.map((step, index) => `
                            <div class="timeline-step ${index <= currentStepIndex ? 'completed' : ''} ${index === currentStepIndex ? 'active' : ''}">
                                <div class="step-icon">${step.icon}</div>
                                <div class="step-label">${step.label}</div>
                                ${index === currentStepIndex && currentOrder.updatedAt ? `<div class="step-time">${formatTime(currentOrder.updatedAt)}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="estimated-card">
                    <div class="estimated-label">
                        <i class="fas fa-hourglass-half"></i> Estimasi Waktu
                    </div>
                    <div class="estimated-value">
                        ${currentOrder.status === 'ready' ? '🛎️ Siap Diambil!' : currentOrder.status === 'completed' ? '✅ Selesai' : '⏱️ ' + estimatedDisplay}
                    </div>
                </div>
                
                <div class="details-card">
                    <div class="details-title">Detail Pesanan</div>
                    <div class="detail-row">
                        <span class="detail-label">Stand</span>
                        <span class="detail-value">${currentOrder.standName || '-'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nama</span>
                        <span class="detail-value">${currentOrder.customerName || '-'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">No. Meja</span>
                        <span class="detail-value">${currentOrder.tableNumber || '-'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Metode Pembayaran</span>
                        <span class="detail-value">${currentOrder.paymentMethod || 'Tunai'}</span>
                    </div>
                    
                    <div class="details-title" style="margin-top: 16px;">Item Pesanan</div>
                    <div class="items-list">
                        ${(currentOrder.items || []).map(item => `
                            <div class="detail-row">
                                <span class="detail-label">${item.quantity}x ${item.name}</span>
                                <span class="detail-value">${formatRupiah(item.price * item.quantity)}</span>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="detail-row" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e9ecef; font-weight: 700;">
                        <span class="detail-label">Total</span>
                        <span class="detail-value">${formatRupiah(total)}</span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn-outline" onclick="orderAgain()">
                        <i class="fas fa-store"></i> Pesan Lagi
                    </button>
                    <button class="btn-primary" onclick="refreshStatus()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            `;
        }
        
        async function refreshStatus() {
            if (!currentOrder?.id) {
                await loadStatus();
                return;
            }
            
            try {
                const orderRef = doc(db, "orders", currentOrder.id);
                const snapshot = await getDoc(orderRef);
                
                if (snapshot.exists()) {
                    const updatedOrder = { id: snapshot.id, ...snapshot.data() };
                    if (updatedOrder.status !== currentOrder.status) {
                        currentOrder = updatedOrder;
                        localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                        renderStatus();
                        showToast(`Status berubah: ${getStatusText(currentOrder.status)}`);
                    } else {
                        showToast('Status masih sama');
                    }
                } else {
                    showToast('Pesanan tidak ditemukan', true);
                }
            } catch (error) {
                console.error("Error refreshing:", error);
                showToast('Gagal refresh status', true);
            }
        }
        
        function getStatusText(statusId) {
            const step = statusSteps.find(s => s.id === statusId);
            return step ? step.label : statusId;
        }
        
        function orderAgain() {
            window.location.href = 'home.php';
        }
        
        function goToHome() {
            window.location.href = 'home.php';
        }

        
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
        
        // Auto refresh every 10 seconds
        let refreshInterval;
        
        function startAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            refreshInterval = setInterval(async () => {
                if (currentOrder?.id) {
                    try {
                        const orderRef = doc(db, "orders", currentOrder.id);
                        const snapshot = await getDoc(orderRef);
                        if (snapshot.exists()) {
                            const updatedOrder = { id: snapshot.id, ...snapshot.data() };
                            if (updatedOrder.status !== currentOrder.status) {
                                currentOrder = updatedOrder;
                                localStorage.setItem('currentOrder', JSON.stringify(currentOrder));
                                renderStatus();
                            }
                        }
                    } catch (error) {
                        console.error("Auto refresh error:", error);
                    }
                }
            }, 10000);
        }
        
        // Initialize auth and load
        onAuthStateChanged(auth, async (user) => {
            if (user) {
                currentUser = user;
                await loadStatus();
                startAutoRefresh();
            } else {
                // Not logged in, redirect to login
window.location.href = 'login.php';
            }
        });
        
        // Make functions available globally
        window.refreshStatus = refreshStatus;
        window.orderAgain = orderAgain;
        window.goToHome = goToHome;
    </script>
</body>
</html>