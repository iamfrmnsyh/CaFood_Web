<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Keranjang</title>
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
            padding-bottom: 100px;
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
        }
        
        .header-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .cart-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .cart-items {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            gap: 14px;
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            overflow: hidden;
            background: #e2e8f0;
            flex-shrink: 0;
        }
        
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        
        .cart-item-price {
            font-size: 0.85rem;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .qty-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 30px;
        }
        
        .qty-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: none;
            background: white;
            cursor: pointer;
            font-weight: bold;
        }
        
        .qty-value {
            font-size: 0.85rem;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .remove-btn {
            color: #ef4444;
            font-size: 0.7rem;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .item-subtotal {
            text-align: right;
            min-width: 80px;
        }
        
        .item-subtotal-label {
            font-size: 0.65rem;
            color: #64748b;
        }
        
        .item-subtotal-value {
            font-weight: 700;
            color: #1a1a2e;
        }
        
        .cart-summary {
            background: white;
            border-radius: 20px;
            padding: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }
        
        .summary-row.total {
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
            margin-top: 8px;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .empty-cart i {
            font-size: 4rem;
            opacity: 0.5;
            margin-bottom: 16px;
        }
        
        .shop-now-btn {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .cart-container { padding: 16px; }
            .cart-item { flex-wrap: wrap; }
            .item-subtotal { text-align: left; margin-top: 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="back-btn" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="header-title">🛒 My Cart</div>
        </div>
    </div>
    
    <div class="cart-container" id="cartContainer">
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <div>Your cart is empty</div>
            <a href="index.html" class="shop-now-btn">Browse Restaurants</a>
        </div>
    </div>
    
    <script type="module">
        let cart = [];
        let currentStand = null;
        
        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }
        
        function loadCart() {
            cart = JSON.parse(localStorage.getItem('cart') || '[]');
            currentStand = JSON.parse(localStorage.getItem('currentStand') || 'null');
            renderCart();
        }
        
        function renderCart() {
            const container = document.getElementById('cartContainer');
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <div>Your cart is empty</div>
                        <a href="index.html" class="shop-now-btn">Browse Restaurants</a>
                    </div>
                `;
                return;
            }
            
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const deliveryFee = 0;
            const tax = Math.round(subtotal * 0);
            const total = subtotal + deliveryFee + tax;
            
            container.innerHTML = `
                <div class="cart-items">
                    ${cart.map((item, index) => `
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="${item.image || 'https://via.placeholder.com/70'}" 
                                     alt="${item.name}"
                                     onerror="this.src='https://via.placeholder.com/70'">
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">${formatRupiah(item.price)}</div>
                                <div class="cart-item-actions">
                                    <div class="qty-selector">
                                        <button class="qty-btn" onclick="updateQuantity(${index}, -1)">-</button>
                                        <span class="qty-value">${item.quantity}</span>
                                        <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                                    </div>
                                    <button class="remove-btn" onclick="removeItem(${index})">Remove</button>
                                </div>
                            </div>
                            <div class="item-subtotal">
                                <div class="item-subtotal-label">Subtotal</div>
                                <div class="item-subtotal-value">${formatRupiah(item.price * item.quantity)}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>${formatRupiah(subtotal)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span>${formatRupiah(deliveryFee)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (0%)</span>
                        <span>${formatRupiah(tax)}</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>${formatRupiah(total)}</span>
                    </div>
                    <button class="checkout-btn" onclick="goToCheckout()">
                        Proceed to Checkout <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            `;
        }
        
        window.updateQuantity = function(index, change) {
            const newQty = cart[index].quantity + change;
            if (newQty <= 0) {
                cart.splice(index, 1);
            } else {
                cart[index].quantity = newQty;
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            if (cart.length === 0) {
                localStorage.removeItem('currentStand');
            }
            renderCart();
        };
        
        window.removeItem = function(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            if (cart.length === 0) {
                localStorage.removeItem('currentStand');
            }
            renderCart();
        };
        
        window.goToCheckout = function() {
            if (cart.length > 0) {
                window.location.href = 'checkout.html';
            }
        };
        
        loadCart();
    </script>
</body>
</html>