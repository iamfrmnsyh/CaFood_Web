<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaFood • Detail Stand & Menu</title>
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
            background: #f5f0ff;
            padding-bottom: 100px;
        }

        .stand-header {
            position: relative;
            height: 280px;
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            overflow: hidden;
        }

        .stand-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
        }

        .cart-icon-header {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s;
        }

        .cart-icon-header:hover, .back-btn:hover {
            background: rgba(0,0,0,0.7);
            transform: scale(1.05);
        }

        .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .stand-info-card {
            background: white;
            border-radius: 36px 36px 0 0;
            margin-top: -30px;
            position: relative;
            z-index: 5;
            padding: 24px 24px 16px 24px;
        }

        .stand-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e1a3a;
            margin-bottom: 8px;
        }

        .stand-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0eaff;
        }

        .stand-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f5f3ff;
            padding: 6px 12px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6d28d9;
        }

        .menu-section {
            background: white;
            padding: 8px 24px 24px 24px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e1a3a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .menu-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 32px;
        }

        .menu-item-card {
            background: #faf8ff;
            border-radius: 20px;
            padding: 16px;
            border: 1px solid #f0eaff;
            transition: all 0.2s;
            display: flex;
            gap: 16px;
        }

        .menu-item-card:hover {
            border-color: #c4b5fd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .menu-image-container {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 16px;
            overflow: hidden;
            background: linear-gradient(135deg, #e2d9ff, #f0eaff);
        }

        .menu-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menu-info {
            flex: 1;
        }

        .menu-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .menu-item-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e1a3a;
        }

        .menu-item-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: #6d28d9;
        }

        .menu-item-desc {
            font-size: 0.8rem;
            color: #7b6cb0;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .menu-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 6px 12px;
            border-radius: 40px;
            border: 1px solid #e2d9ff;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f0eaff;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #6d28d9;
            color: white;
        }

        .qty-value {
            font-weight: 600;
            min-width: 28px;
            text-align: center;
        }

        .add-item-btn {
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .add-item-btn:hover {
            transform: scale(1.02);
        }

        .cart-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #f0eaff;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
        }

        .cart-total {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .cart-total-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #6d28d9;
        }

        .cart-items-count {
            background: #f0eaff;
            padding: 6px 12px;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6d28d9;
        }

        .checkout-btn {
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 60px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .view-cart-btn {
            background: white;
            color: #6d28d9;
            border: 1px solid #6d28d9;
            padding: 12px 24px;
            border-radius: 60px;
            font-weight: 600;
            cursor: pointer;
        }

        .toast {
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e1a3a;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
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
            .stand-header { height: 200px; }
            .menu-item-card { flex-direction: column; }
            .menu-image-container { width: 100%; height: 150px; }
            .cart-summary { flex-direction: column; }
            .checkout-btn, .view-cart-btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="stand-header">
        <div class="back-btn" onclick="history.back()">←</div>
        <div class="cart-icon-header" onclick="window.location.href='keranjang.php'">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count-badge" id="cartCountHeader">0</span>
        </div>
        <img class="stand-cover" id="standCover" src="" alt="Stand Cover">
    </div>

    <div class="stand-info-card" id="standInfo"></div>

    <div class="menu-section">
        <div class="section-title">
            <i class="fas fa-utensils" style="color:#6d28d9;"></i> Menu List
        </div>
        <div class="menu-list" id="menuList"></div>
    </div>

    <div class="cart-summary" id="cartSummary"></div>

    <script>
        const standsData = [
            {
                id: 1,
                name: "Warung Makan PW",
                rating: 4.7,
                deliveryTime: "15-20 min",
                imageUrl: "https://images.unsplash.com/photo-1633945274405-b6c8069047b0?w=800&auto=format",
                description: "Menu nasi rames, ayam bakar, ayam goreng, aneka lauk & sayur, aneka sate, dan gorengan.",
                menus: [
                    { id: 101, name: "Nasi Rames Komplit", price: 15000, desc: "Nasi + ayam + tempe + tahu + sayur", image: "https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=400&auto=format" },
                    { id: 102, name: "Ayam Bakar", price: 12000, desc: "Ayam bakar bumbu kecap", image: "https://images.unsplash.com/photo-1630315500315-43112e2bfd88?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8YXlhbSUyMGJha2FyfGVufDB8fDB8fHww" },
                    { id: 103, name: "Ayam Goreng", price: 12000, desc: "Ayam goreng gurih dan lezat", image: "https://images.unsplash.com/photo-1732185269471-b62b52ca46f9?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8bmFzaSUyMGF5YW0lMjBnb3Jlbmd8ZW58MHx8MHx8fDA%3D" },
                    { id: 104, name: "Sate Ayam (5 tusuk)", price: 10000, desc: "Sate ayam dengan bumbu kacang", image: "https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=400&auto=format" },
                    { id: 105, name: "Gorengan (5 pcs)", price: 5000, desc: "Tahu, tempe, bakwan, pisang, ubi", image: "https://images.unsplash.com/photo-1613764816537-a43baeb559c1?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW5kb25lc2lhbiUyMGZyaXR0ZXJzfGVufDB8fDB8fHwwhttps://images.unsplash.com/photo-1629386199824-700da7af2097?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHBvdGF0byUyMGZyaXR0ZXJzfGVufDB8fDB8fHwwhttps://media.istockphoto.com/id/2150260758/photo/perkedel-kentang-is-an-indonesian-fried-patties-made-of-mashed-potatoes-minced-meat-garlic.webp?a=1&b=1&s=612x612&w=0&k=20&c=fwrMjoy8vKdP57HFSSE0_Mu3Q5dJ0iinlqxAU1vvcm8=https://media.istockphoto.com/id/2255993915/photo/various-gorengan-for-takjil-breaking-the-fast.webp?a=1&b=1&s=612x612&w=0&k=20&c=tUc3_NjT3nbl8lz6GKu85LiuldtlCV6LLZjKb5Fh7lM=https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&auto=format" },
                    { id: 106, name: "Telur Dadar", price: 5000, desc: "Telur dadar tipis", image: "https://images.unsplash.com/photo-1677137261161-0095c10418ef?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8YXNpYW4lMjBvbWVsZXR0fGVufDB8fDB8fHwwhttps://media.istockphoto.com/id/2235290327/photo/egg-roll-omelete-or-telur-dadar-gulung-or-tamagoyaki-or-japaneses-egg-roll-gyeran-mari-or.webp?a=1&b=1&s=612x612&w=0&k=20&c=CDp1H7johwRnPDRsrQUUGR0FmHMkdz-aoNFIsFMgLKE=https://images.unsplash.com/photo-1604908554167-5d2b8c9e0b93?w=400&auto=format" },
                    { id: 107, name: "Perkedel", price: 1000, desc: "Perkedel kentang", image: "https://images.unsplash.com/photo-1629386199824-700da7af2097?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHBvdGF0byUyMGZyaXR0ZXJzfGVufDB8fDB8fHwwhttps://media.istockphoto.com/id/2150260758/photo/perkedel-kentang-is-an-indonesian-fried-patties-made-of-mashed-potatoes-minced-meat-garlic.webp?a=1&b=1&s=612x612&w=0&k=20&c=fwrMjoy8vKdP57HFSSE0_Mu3Q5dJ0iinlqxAU1vvcm8=https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&auto=format" }
                ]
            },
            {
                id: 2,
                name: "Kantin Dinasty Kitchen",
                rating: 4.8,
                deliveryTime: "10-20 min",
                imageUrl: "https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=800&auto=format",
                description: "Bakso, ayam geprek, nasi goreng, takoyaki, kupat, gorengan, aneka jajanan.",
                menus: [
                    { id: 201, name: "Bakso Urat", price: 15000, desc: "Bakso sapi + mie + pangsit", image: "https://images.unsplash.com/photo-1747317368514-590dad462536?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8YmFrc298ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1605475121042-fc21b7c2a4e5?w=400&auto=format" },
                    { id: 202, name: "Ayam Geprek", price: 12000, desc: "Ayam geprek sambal bawang + nasi", image: "https://images.unsplash.com/photo-1569058242252-623df46b5025?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8c3BpY3klMjBmcmllZCUyMGNoaWNrZW4lMjByaWNlfGVufDB8fDB8fHwwhttps://images.unsplash.com/photo-1625944230945-1b7dd3b949ab?w=400&auto=format" },
                    { id: 203, name: "Nasi Goreng Spesial", price: 12000, desc: "Nasi goreng + telur + ayam", image: "https://images.unsplash.com/photo-1680674814945-7945d913319c?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTJ8fGluZG9uZXNpYW4lMjBmcmllZCUyMHJpY2V8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1604908176997-431e7b6e4e52?w=400&auto=format" },
                    { id: 204, name: "Takoyaki (5 pcs)", price: 10000, desc: "Takoyaki saus khas", image: "https://images.unsplash.com/photo-1742633882704-41ec3a57dbb7?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8dGFrb3lha2l8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1617196034796-73dfa7b1fd56?w=400&auto=format" },
                    { id: 205, name: "Kupat Tahu", price: 12000, desc: "Kupat + tahu + taoge + bumbu kacang", image: "https://plus.unsplash.com/premium_photo-1671547329181-8b1925cab127?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8a2V0dXBhdCUyMHRhaHV8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1589307004396-0a4d9a6a1c06?w=400&auto=format" },
                    { id: 206, name: "Gorengan", price: 1000, desc: "Campuran tahu, tempe, bakwan, risol", image: "https://images.unsplash.com/photo-1613764816537-a43baeb559c1?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW5kb25lc2lhbiUyMGZyaXR0ZXJzfGVufDB8fDB8fHwwhttps://images.unsplash.com/photo-1629386199824-700da7af2097?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fHBvdGF0byUyMGZyaXR0ZXJzfGVufDB8fDB8fHwwhttps://media.istockphoto.com/id/2150260758/photo/perkedel-kentang-is-an-indonesian-fried-patties-made-of-mashed-potatoes-minced-meat-garlic.webp?a=1&b=1&s=612x612&w=0&k=20&c=fwrMjoy8vKdP57HFSSE0_Mu3Q5dJ0iinlqxAU1vvcm8=https://media.istockphoto.com/id/2255993915/photo/various-gorengan-for-takjil-breaking-the-fast.webp?a=1&b=1&s=612x612&w=0&k=20&c=tUc3_NjT3nbl8lz6GKu85LiuldtlCV6LLZjKb5Fh7lM=https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&auto=format" },
                    { id: 207, name: "Jajanan Pasar", price: 2000, desc: "Lupis, klepon, mendut", image: "https://images.unsplash.com/photo-1680345576151-bbc497ba969e?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW5kb25lc2lhbiUyMHRyYWRpdGlvbmFsJTIwc25hY2tzfGVufDB8fDB8fHwwhttps://images.unsplash.com/photo-1604909053196-2c8d0d8c3c06?w=400&auto=format" }
                ]
            },
            {
                id: 3,
                name: "Warmindo Syailendra 168",
                rating: 4.6,
                deliveryTime: "10-15 min",
                imageUrl: "https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&auto=format",
                description: "Indomie goreng, rebus, telur, pecel, gado-gado, ketoprak, pentol kuah, aneka jajanan.",
                menus: [
                    { id: 301, name: "Indomie Goreng", price: 7000, desc: "Indomie goreng + bawang goreng", image: "https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8ZnJpZWQlMjBpbnN0YW50JTIwbm9vZGxlc3xlbnwwfHwwfHx8MA%3D%3Dhttps://images.unsplash.com/photo-1626808642875-0aa545482dfb?w=400&auto=format" },
                    { id: 302, name: "Indomie Rebus", price: 7000, desc: "Indomie kuah + sayur", image: "https://images.unsplash.com/photo-1761125065373-05a8e2f85cd5?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTZ8fGZyaWVkJTIwaW5zdGFudCUyMG5vb2RsZXN8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400&auto=format" },
                    { id: 303, name: "Indomie Telur", price: 10000, desc: "Indomie goreng/rebus + telur", image: "https://images.unsplash.com/photo-1752924349515-761435ec22b3?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTl8fGZyaWVkJTIwaW5zdGFudCUyMG5vb2RsZXMlMjBlZ2d8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1626808642875-0aa545482dfb?w=400&auto=format" },
                    { id: 304, name: "Pecel", price: 10000, desc: "Sayur pecel + bumbu kacang", image: "https://images.unsplash.com/photo-1750190624031-8a1eacae11bb?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8dmVnZXRhYmxlJTIwcGVhbnV0JTIwc2FsYWR8ZW58MHx8MHx8fDA%3Dhttps://images.unsplash.com/photo-1589302168068-964664d93dc0?w=400&auto=format" },
                    { id: 305, name: "Gado-Gado", price: 10000, desc: "Sayur + lontong + bumbu kacang", image: "https://images.unsplash.com/photo-1707269561481-a4a0370a980a?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8Z2FkbyUyMGdhZG8lMjBzYWxhZHxlbnwwfHwwfHx8MA%3D%3Dhttps://images.unsplash.com/photo-1589302168068-964664d93dc0?w=400&auto=format" },
                    { id: 306, name: "Ketoprak", price: 12000, desc: "Ketoprak Jakarta", image: "https://images.unsplash.com/photo-1707269561481-a4a0370a980a?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8Z2FkbyUyMGdhZG8lMjBzYWxhZHxlbnwwfHwwfHx8MA%3D%3Dhttps://images.unsplash.com/photo-1589302168068-964664d93dc0?w=400&auto=format" },
                    { id: 307, name: "Pentol Kuah", price: 5000, desc: "Pentol bakso kuah hangat", image: "https://images.unsplash.com/photo-1768703321913-db220381bb84?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8bWVhdGJhbGwlMjBzb3VwJTIwc3RyZWV0JTIwZm9vZHxlbnwwfHwwfHx8MA%3D%3D" },
                    { id: 308, name: "Aneka Jajanan (3 pcs)", price: 5000, desc: "Cireng, cilok, cilor", image: "https://images.unsplash.com/photo-1680345576151-bbc497ba969e?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aW5kb25lc2lhbiUyMHRyYWRpdGlvbmFsJTIwc25hY2tzfGVufDB8fDB8fHwwhttps://images.unsplash.com/photo-1604909053196-2c8d0d8c3c06?w=400&auto=format" }
                ]
            }
        ];

        const urlParams = new URLSearchParams(window.location.search);
        const standId = parseInt(urlParams.get('standId')) || 1;
        const currentStand = standsData.find(s => s.id === standId) || standsData[0];

        let cart = [];

        function formatRupiah(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
        }

        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
            localStorage.setItem('currentStand', JSON.stringify({
                id: currentStand.id,
                name: currentStand.name
            }));
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            const headerBadge = document.getElementById('cartCountHeader');
            if (headerBadge) headerBadge.textContent = totalItems;

            const summaryContainer = document.getElementById('cartSummary');
            if (summaryContainer) {
                if (cart.length === 0) {
                    summaryContainer.innerHTML = `
                        <div class="cart-total">
                            <span class="cart-total-value">Rp 0</span>
                            <span class="cart-items-count">0 item</span>
                        </div>
                        <button class="view-cart-btn" onclick="window.location.href='keranjang.php'">View Cart</button>
                    `;
                } else {
                    summaryContainer.innerHTML = `
                        <div class="cart-total">
                            <span class="cart-total-value">${formatRupiah(totalPrice)}</span>
                            <span class="cart-items-count">${totalItems} item${totalItems > 1 ? 's' : ''}</span>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <button class="view-cart-btn" onclick="window.location.href='keranjang.php'">View Cart</button>
                            <button class="checkout-btn" onclick="window.location.href='checkout.php'">Checkout</button>
                        </div>
                    `;
                }
            }
        }

        function addToCart(menu) {
            updateQuantity(menu.id, 1);
            showToast(`${menu.name} ditambahkan!`);
        }

        function updateQuantity(menuId, change) {
            let item = cart.find(i => i.id === menuId);

            if (!item && change > 0) {
                const menu = currentStand.menus.find(m => m.id === menuId);
                item = {
                    id: menu.id,
                    name: menu.name,
                    price: menu.price,
                    quantity: 1,
                    image: menu.image
                };
                cart.push(item);
            } else if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== menuId);
                }
            }

            saveCart();
            renderMenus();
        }

        function showToast(message) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) existingToast.remove();

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }

        function renderStandInfo() {
            const container = document.getElementById('standInfo');
            const coverImg = document.getElementById('standCover');

            if (coverImg) coverImg.src = currentStand.imageUrl;

            if (container) {
                container.innerHTML = `
                    <h1 class="stand-title">${currentStand.name}</h1>
                    <div class="stand-meta">
                        <span class="stand-rating"><i class="fas fa-star"></i> ${currentStand.rating}</span>
                        <span><i class="far fa-clock"></i> ${currentStand.deliveryTime}</span>
                        <span><i class="fas fa-motorcycle"></i> Free Delivery</span>
                    </div>
                    <p>${currentStand.description}</p>
                `;
            }
        }

        function loadCart() {
            const savedCart = localStorage.getItem('cart');
            const savedStand = localStorage.getItem('currentStand');

            if (savedCart && savedStand) {
                const savedStandData = JSON.parse(savedStand);
                if (savedStandData.id === currentStand.id) {
                    cart = JSON.parse(savedCart);
                } else {
                    cart = [];
                }
            }

            updateCartDisplay();
        }

        function renderMenus() {
            const container = document.getElementById('menuList');
            if (!container) return;

            container.innerHTML = currentStand.menus.map(menu => {
                const cartItem = cart.find(i => i.id === menu.id);
                const qty = cartItem ? cartItem.quantity : 0;

                return `
                    <div class="menu-item-card">
                        <div class="menu-image-container">
                            <img class="menu-image" src="${menu.image}" alt="${menu.name}" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:2rem;\'>🍽️</div>'">
                        </div>
                        <div class="menu-info">
                            <div class="menu-item-header">
                                <span class="menu-item-name">${menu.name}</span>
                                <span class="menu-item-price">${formatRupiah(menu.price)}</span>
                            </div>
                            <div class="menu-item-desc">${menu.desc}</div>
                            <div class="menu-item-actions">
                                <div class="quantity-selector">
                                    <button class="qty-btn" onclick="updateQuantity(${menu.id}, -1)">-</button>
                                    <span class="qty-value" id="qty-${menu.id}">${qty}</span>
                                    <button class="qty-btn" onclick="updateQuantity(${menu.id}, 1)">+</button>
                                </div>
                                <button class="add-item-btn" onclick="addToCart({id: ${menu.id}, name: '${menu.name}', price: ${menu.price}, image: '${menu.image}'})">
                                    <i class="fas fa-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        renderStandInfo();
        renderMenus();
        loadCart();
    </script>
</body>
</html>

