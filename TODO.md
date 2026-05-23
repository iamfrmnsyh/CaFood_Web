# TODO - CaFood migrasi Firebase -> backend-only

## Tahap 1: Hapus ketergantungan Firebase di halaman flow customer
- [ ] Migrasi `register.php` agar pakai `./js/api-client.js` + `window.API.register` (tanpa `firebase-config.js`).
- [ ] Migrasi `home.php` agar ambil stands via `window.API.getStands` (menyesuaikan endpoint yang tersedia) dan hitung cartCount dari localStorage `cart`.
- [ ] Migrasi `menu-stand.php` agar ambil stand + menus via `window.API` dan cart via localStorage.
- [ ] Migrasi `checkout.php` agar baca cart dari localStorage dan create order via `window.API.createOrder`, lalu clear cart localStorage.
- [ ] Migrasi `status-pesanan.php` agar ambil order via `window.API.getOrders/getOrder` dan refresh status via backend-only.

## Tahap 2: Dashboard backend-only (stand/admin)
- [ ] Migrasi `dashboard-admin.php` agar CRUD stands/menus/orders/users via `window.API` dan update status order via backend-only.
- [ ] Migrasi `dashboard-stand.php` agar load menus/orders via `window.API`, dan update status order via backend-only.

## Tahap 3: Konsistensi routing & file target
- [ ] Samakan redirect: semua halaman harus pakai `.php` yang benar (hindari `.html` yang tidak ada).
- [ ] Perbaiki bug `home.php` double redirect `dashboardLink`.

## Tahap 4: Verifikasi end-to-end
- [ ] Jalankan server php built-in dan lakukan smoke test: register/login → home → menu-stand → keranjang → checkout → status-pesanan → dashboard.
- [ ] Periksa console/browser network errors.

