<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>CaFood Marketplace | Purple • Food Stalls</title>
    <!-- Font Awesome 6 (ikon pelengkap) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter + fallback -->
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
            color: #1e1a3a;
            overflow-x: auto;
        }

        /* LANDSCAPE MAKSIMAL: lebar penuh tanpa batasan container sempit */
        .marketplace-full {
            width: 100%;
            min-height: 100vh;
            padding: 28px 40px 60px 40px;
            background: #f5f0ff;
        }

        /* HERO SECTION - full width gradien ungu */
        .hero-full {
            background: linear-gradient(125deg, #6d28d9 0%, #a855f7 100%);
            border-radius: 36px;
            padding: 48px 56px;
            margin-bottom: 48px;
            color: white;
            box-shadow: 0 20px 35px -10px rgba(109, 40, 217, 0.3);
            width: 100%;
        }

        .brand-chip {
            font-size: 14px;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            border-radius: 60px;
            margin-bottom: 24px;
            letter-spacing: 0.3px;
        }

        .hero-full h1 {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .hero-full p {
            font-size: 18px;
            opacity: 0.92;
            max-width: 580px;
            margin-bottom: 32px;
        }

        /* search bar besar & modern */
        .search-row {
            max-width: 620px;
            display: flex;
            background: white;
            border-radius: 100px;
            align-items: center;
            justify-content: space-between;
            padding: 6px 6px 6px 28px;
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
        }

        .search-row i {
            color: #a855f7;
            font-size: 20px;
        }

        .search-row input {
            flex: 1;
            border: none;
            padding: 16px 14px;
            font-size: 16px;
            font-weight: 500;
            background: transparent;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        .search-row input::placeholder {
            color: #a29bbf;
            font-weight: 400;
        }

        .search-purple-btn {
            background: #7c3aed;
            border: none;
            padding: 10px 32px;
            border-radius: 60px;
            color: white;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
            font-family: inherit;
        }

        .search-purple-btn:hover {
            background: #5b21b6;
            transform: scale(0.97);
        }

        /* kategori scroll fleksibel (wrap jika perlu) */
        .category-section {
            margin-bottom: 48px;
        }

        .section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .section-head h2 {
            font-size: 26px;
            font-weight: 800;
            color: #2d1b69;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .cat-item {
            background: white;
            border-radius: 60px;
            padding: 10px 30px;
            font-weight: 700;
            font-size: 15px;
            color: #4c3b7c;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #e2d9ff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .cat-item.active {
            background: #7c3aed;
            color: white;
            border-color: #7c3aed;
            box-shadow: 0 8px 18px rgba(124, 58, 237, 0.2);
        }

        /* GRID LANDSKAP MAKSIMAL: memanfaatkan lebar penuh dengan auto-fit */
        .stands-landscape-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 32px;
            margin-top: 12px;
            width: 100%;
        }

        /* kartu stand premium */
        .stand-card {
            background: #ffffff;
            border-radius: 32px;
            overflow: hidden;
            transition: all 0.25s ease;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.05);
            border: 1px solid #ede9fe;
            display: flex;
            flex-direction: column;
        }

        .stand-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 35px -14px rgba(109, 40, 217, 0.25);
            border-color: #c4b5fd;
        }

        .stand-img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            background: #ddd6fe;
            display: block;
        }

        .stand-content {
            padding: 20px 22px 24px 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .stand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .stand-name {
            font-size: 22px;
            font-weight: 800;
            color: #211752;
            letter-spacing: -0.2px;
        }

        .rating {
            background: #f5f3ff;
            padding: 5px 12px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 13px;
            color: #6d28d9;
        }

        .desc {
            color: #52525b;
            font-size: 14px;
            line-height: 1.45;
            margin: 10px 0 12px 0;
        }

        .info-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            font-size: 13px;
            color: #5b4b8c;
            margin-bottom: 16px;
        }

        .info-strip span i {
            margin-right: 6px;
            width: 16px;
            color: #a855f7;
        }

        .free-tag {
            background: #e9e3ff;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 12px;
            color: #5b21b6;
        }

        /* Menu populer di dalam stand */
        .menu-preview {
            margin: 18px 0 20px 0;
            border-top: 1px solid #f0eaff;
            padding-top: 16px;
        }

        .menu-label {
            font-weight: 800;
            font-size: 14px;
            color: #3b2b6b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .menu-list-compact {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-name {
            color: #1f1a3a;
        }

        .menu-price {
            font-weight: 800;
            color: #7c3aed;
        }

        .btn-order {
            background: #f3efff;
            border: none;
            padding: 12px 0;
            border-radius: 60px;
            font-weight: 800;
            font-size: 15px;
            color: #6d28d9;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: inherit;
            width: 100%;
        }

        .btn-order:hover {
            background: #7c3aed;
            color: white;
        }

        /* empty state */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 70px 20px;
            background: #faf8ff;
            border-radius: 48px;
            color: #6b5b9e;
        }

        .footer {
            margin-top: 70px;
            text-align: center;
            color: #7b6cb0;
            border-top: 1px solid #e2d9ff;
            padding-top: 32px;
            font-weight: 500;
        }

        /* Responsif untuk layar ekstra lebar (landscape maksimal) */
        @media (min-width: 1800px) {
            .stands-landscape-grid {
                grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
                gap: 36px;
            }
            .hero-full h1 {
                font-size: 64px;
            }
            .marketplace-full {
                padding: 36px 60px 70px 60px;
            }
        }

        @media (max-width: 1100px) {
            .marketplace-full {
                padding: 20px 24px 50px 24px;
            }
            .hero-full {
                padding: 36px 32px;
            }
            .hero-full h1 {
                font-size: 42px;
            }
        }

        @media (max-width: 780px) {
            .stands-landscape-grid {
                grid-template-columns: 1fr;
            }
            .hero-full h1 {
                font-size: 34px;
            }
            .search-row {
                flex-wrap: wrap;
                background: transparent;
                padding: 0;
                gap: 12px;
            }
            .search-row input {
                background: white;
                border-radius: 80px;
                padding: 14px 20px;
            }
            .search-purple-btn {
                width: auto;
                padding: 12px 24px;
            }
        }
    </style>
</head>
<body>
<div class="marketplace-full">
    <!-- Hero dengan gradien ungu dan landscape penuh -->
    <div class="hero-full">
        <div class="brand-chip">
            <i class="fas fa-crown"></i> CaFood Marketplace
        </div>
        <h1>Good Afternoon! 🌤️<br>Where do you want to eat?</h1>
        <p>Explore authentic food stalls, local favorites, and trending stands — free delivery across all vendors.</p>
        <div class="search-row">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search stand or menu (e.g. Ramen, Nasi, Kopi, Dessert) ...">
            <button class="search-purple-btn" id="searchBtn">Discover</button>
        </div>
    </div>

    <!-- Categories section tanpa emoji, full width -->
    <div class="category-section">
        <div class="section-head">
            <h2><i class="fas fa-tag" style="color:#a855f7;"></i> Categories</h2>
        </div>
        <div class="category-pills" id="categoryList">
            <!-- dynamic categories -->
        </div>
    </div>

    <!-- Stands grid (menampilkan stand-stand, bukan hanya menu) dengan landscape penuh -->
    <div class="section-head">
        <h2><i class="fas fa-store"></i> Popular Stands · Recommended for you</h2>
    </div>
    <div class="stands-landscape-grid" id="standsContainer">
        <!-- JS render daftar stand beserta menu populer di dalamnya -->
    </div>

    <div class="footer">
        <i class="fas fa-motorcycle"></i> Free delivery on all stands · Real-time order · Purple marketplace
    </div>
</div>

<script>
    // Data lengkap stand dengan gambar asli Unsplash (foto asli, tanpa emoji)
    const standsData = [
        {
            id: 1,
            name: "Ramen House",
            description: "Authentic Japanese Ramen with rich Tonkotsu broth, homemade noodles and cozy vibes.",
            rating: 4.8,
            deliveryTime: "15-20 min",
            deliveryFee: "Free",
            category: "Noodles",
            isOpen: true,
            imageUrl: "https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=800&auto=format",
            menu: [
                { name: "Tonkotsu Ramen", price: 45000 },
                { name: "Shoyu Ramen", price: 40000 },
                { name: "Gyozas (5 pcs)", price: 25000 }
            ]
        },
        {
            id: 2,
            name: "Nusantara Rice Bowl",
            description: "Indonesian heritage rice bowls with sambal matah, fried chicken, and lawar.",
            rating: 4.7,
            deliveryTime: "20-25 min",
            deliveryFee: "Free",
            category: "Rice",
            imageUrl: "https://images.unsplash.com/photo-1633945274405-b6c8069047b0?w=800&auto=format",
            menu: [
                { name: "Nasi Campur Bali", price: 38000 },
                { name: "Ayam Betutu Rice", price: 42000 },
                { name: "Sambal Goreng Ati", price: 19000 }
            ]
        },
        {
            id: 3,
            name: "Kopi Kekinian",
            description: "Specialty coffee, cold brews, and artisanal drinks with cozy ambiance.",
            rating: 4.9,
            deliveryTime: "10-15 min",
            deliveryFee: "Free",
            category: "Drinks",
            imageUrl: "https://images.unsplash.com/photo-1507133750040-4a8f57021571?w=800&auto=format",
            menu: [
                { name: "Iced Caramel Latte", price: 32000 },
                { name: "Matcha Espresso", price: 35000 },
                { name: "Vietnamese Drip", price: 30000 }
            ]
        },
        {
            id: 4,
            name: "Sweet Tooth Lab",
            description: "Decadent cakes, traditional desserts, and fusion sweet treats.",
            rating: 4.6,
            deliveryTime: "12-18 min",
            deliveryFee: "Free",
            category: "Dessert",
            imageUrl: "https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=800&auto=format",
            menu: [
                { name: "Lava Cake", price: 34000 },
                { name: "Es Campur Istimewa", price: 24000 },
                { name: "Bubur Sumsum", price: 18000 }
            ]
        },
        {
            id: 5,
            name: "Burger District",
            description: "Juicy smashed burgers, crispy chicken sandwiches, and loaded cheese fries.",
            rating: 4.5,
            deliveryTime: "15-20 min",
            deliveryFee: "Free",
            category: "Fast Food",
            imageUrl: "https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&auto=format",
            menu: [
                { name: "Double Cheeseburger", price: 49000 },
                { name: "Spicy Chicken Sandwich", price: 42000 },
                { name: "Loaded Fries", price: 27000 }
            ]
        },
        {
            id: 6,
            name: "Mie Gacoan",
            description: "Legendary noodles with homemade chili oil, level up your appetite.",
            rating: 4.8,
            deliveryTime: "18-22 min",
            deliveryFee: "Free",
            category: "Noodles",
            imageUrl: "https://images.unsplash.com/photo-1626137979375-5b595d9b8264?w=800&auto=format",
            menu: [
                { name: "Mie Setan Level 7", price: 30000 },
                { name: "Mie Angel Special", price: 28000 },
                { name: "Pangsit Goreng", price: 15000 }
            ]
        },
        {
            id: 7,
            name: "Sate & Gulai Pak Min",
            description: "Authentic sate madura, gulai kambing, and lontong sayur.",
            rating: 4.9,
            deliveryTime: "20-30 min",
            deliveryFee: "Free",
            category: "Rice",
            imageUrl: "https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=800&auto=format",
            menu: [
                { name: "Sate Ayam (10 tusuk)", price: 35000 },
                { name: "Gulai Kambing", price: 55000 },
                { name: "Lontong Sayur", price: 22000 }
            ]
        },
        {
            id: 8,
            name: "Thai Tea & Co",
            description: "Refreshing Thai tea, lemon honey, and fusion milk tea.",
            rating: 4.7,
            deliveryTime: "8-12 min",
            deliveryFee: "Free",
            category: "Drinks",
            imageUrl: "https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800&auto=format",
            menu: [
                { name: "Original Thai Tea", price: 27000 },
                { name: "Pink Milk", price: 25000 },
                { name: "Lemon Honey Tea", price: 22000 }
            ]
        },
        {
            id: 9,
            name: "Waffle Factory",
            description: "Crispy waffles, ice cream toppings, and sweet syrups.",
            rating: 4.6,
            deliveryTime: "12-18 min",
            deliveryFee: "Free",
            category: "Dessert",
            imageUrl: "https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=800&auto=format",
            menu: [
                { name: "Classic Waffle", price: 25000 },
                { name: "Choco Waffle", price: 29000 },
                { name: "Strawberry Dream", price: 32000 }
            ]
        },
        {
            id: 10,
            name: "Spicy Chicken House",
            description: "Korean fried chicken, spicy wings and cheesy sauce.",
            rating: 4.8,
            deliveryTime: "18-24 min",
            deliveryFee: "Free",
            category: "Fast Food",
            imageUrl: "https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=800&auto=format",
            menu: [
                { name: "Original Fried Chicken", price: 38000 },
                { name: "Gochujang Wings", price: 43000 },
                { name: "Cheese Balls", price: 22000 }
            ]
        }
    ];

    // Ekstrak kategori unik + All
    const allCategories = ["All", ...new Set(standsData.map(s => s.category))];
    let activeCategory = "All";
    let searchQuery = "";

    // Helper format Rupiah
    function formatRupiah(price) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
    }

    // Simple escape
    function escapeText(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Filter berdasarkan kategori & search (nama stand atau item menu)
    function getFilteredStands() {
        let filtered = [...standsData];
        if (activeCategory !== "All") {
            filtered = filtered.filter(stand => stand.category === activeCategory);
        }
        if (searchQuery.trim() !== "") {
            const lowerQuery = searchQuery.toLowerCase();
            filtered = filtered.filter(stand => {
                const matchStand = stand.name.toLowerCase().includes(lowerQuery);
                const matchMenu = stand.menu.some(menuItem => menuItem.name.toLowerCase().includes(lowerQuery));
                return matchStand || matchMenu;
            });
        }
        return filtered;
    }

    // Render kategori (pill)
    function renderCategories() {
        const container = document.getElementById("categoryList");
        if (!container) return;
        container.innerHTML = allCategories.map(cat => `
            <div class="cat-item ${activeCategory === cat ? 'active' : ''}" data-category="${cat}">
                ${cat}
            </div>
        `).join("");

        document.querySelectorAll(".cat-item").forEach(el => {
            el.addEventListener("click", (e) => {
                const category = el.getAttribute("data-category");
                if (category) {
                    activeCategory = category;
                    // Reset search saat pindah kategori (opsi UX lebih baik)
                    // Biarkan search terisi? tapi agar konsisten, kita kosongkan search biar sesuai kategori
                    const searchInputDom = document.getElementById("globalSearch");
                    if (searchInputDom) searchInputDom.value = "";
                    searchQuery = "";
                    renderCategories();
                    renderStands();
                }
            });
        });
    }

    // Render semua stand dalam bentuk kartu (gambar asli + menu di dalam)
    function renderStands() {
        const container = document.getElementById("standsContainer");
        if (!container) return;
        const filtered = getFilteredStands();

        if (filtered.length === 0) {
            container.innerHTML = `<div class="empty-state">
                <i class="fas fa-utensils" style="font-size: 52px; opacity: 0.6; margin-bottom: 16px; color:#a78bfa;"></i>
                <h3>No food stands found</h3>
                <p>Try another category or search keyword 🍽️</p>
            </div>`;
            return;
        }

        container.innerHTML = filtered.map(stand => {
            const starIcon = '<i class="fas fa-star" style="color: #fbbf24; font-size: 12px;"></i>';
            const ratingHtml = `${starIcon} ${stand.rating}`;

            // render menu (max 3)
            const menuHtml = stand.menu.map(item => `
                <div class="menu-item-row">
                    <span class="menu-name">${escapeText(item.name)}</span>
                    <span class="menu-price">${formatRupiah(item.price)}</span>
                </div>
            `).join('');

            return `
                <div class="stand-card" data-stand-id="${stand.id}">
                    <img class="stand-img" src="${stand.imageUrl}" alt="${escapeText(stand.name)}" loading="lazy" onerror="this.src='https://placehold.co/800x500?text=Food+Image'">
                    <div class="stand-content">
                        <div class="stand-header">
                            <h3 class="stand-name">${escapeText(stand.name)}</h3>
                            <div class="rating">${ratingHtml}</div>
                        </div>
                        <div class="desc">${escapeText(stand.description)}</div>
                        <div class="info-strip">
                            <span><i class="far fa-clock"></i> ${stand.deliveryTime}</span>
                            <span><i class="fas fa-motorcycle"></i> ${stand.deliveryFee}</span>
                            <span class="free-tag"><i class="fas fa-gem"></i> Free delivery</span>
                            <span><i class="fas fa-check-circle" style="color:#a855f7;"></i> Open</span>
                        </div>
                        <div class="menu-preview">
                            <div class="menu-label">
                                <i class="fas fa-fire" style="color:#f97316;"></i> Popular Menu
                            </div>
                            <div class="menu-list-compact">
                                ${menuHtml}
                            </div>
                        </div>
                        <button class="btn-order" data-id="${stand.id}">
                            <i class="fas fa-shopping-bag"></i> Order from ${escapeText(stand.name.split(' ')[0])}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        // event listener tombol order
        document.querySelectorAll('.btn-order').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.getAttribute('data-id');
                const selected = standsData.find(s => s.id == id);
                if (selected) {
                    alert(`🛍️ Start order at "${selected.name}"\n✨ Menu available: ${selected.menu.length} items\n🚚 Free delivery, proceed to checkout.`);
                }
            });
        });
    }

    // Fungsi pencarian
    function performSearch() {
        const inputSearch = document.getElementById("globalSearch");
        if (inputSearch) {
            searchQuery = inputSearch.value.trim();
            // Reset kategori ke All agar pencarian lebih luas
            activeCategory = "All";
            renderCategories();  // update tampilan pill active = All
            renderStands();
        }
    }

    // Event binding search
    function bindSearchEvents() {
        const searchBtn = document.getElementById("searchBtn");
        const searchField = document.getElementById("globalSearch");
        if (searchBtn) {
            searchBtn.addEventListener("click", performSearch);
        }
        if (searchField) {
            searchField.addEventListener("keypress", (e) => {
                if (e.key === "Enter") performSearch();
            });
        }
    }

    // Inisialisasi penuh
    function init() {
        renderCategories();
        renderStands();
        bindSearchEvents();
    }

    init();
</script>
</body>
</html>