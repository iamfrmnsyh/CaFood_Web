# TODO - Migrasi CaFood dari Firebase ke Backend API

## Tujuan
Membuat web berjalan end-to-end tanpa Firebase, dengan semua halaman menggunakan backend PHP (`backend/api.php`) dan client (`js/api-client.js`).

## Langkah
1. Audit seluruh halaman yang masih mengimpor `./js/firebase-config.js` (login/register/home/checkout/dashboard).
2. Implement session auth berbasis `localStorage` (currentUser) untuk semua halaman.
3. Migrasi `login.php`:
   - ganti signIn Firebase menjadi `API.login`.
   - simpan `currentUser` dari respon API (uid/id, role, name/email).
   - redirect ke halaman yang benar sesuai role (admin/stand/customer).
4. Migrasi `register.php`:
   - ganti createUser Firebase menjadi `API.register`.
   - kirim `name/email/password/role` ke backend.
5. Migrasi `home.php`:
   - ganti load stands & cart count dari Firestore ke API stands/menus/orders/carts yang relevan.
   - perbaiki bug double-redirect dashboard.
6. Migrasi `checkout.php`:
   - ganti load cart dari Firestore ke localStorage cart (atau endpoint cart bila ada).
   - ganti create order menjadi `API.createOrder`.
   - setelah order dibuat redirect ke `status-pesanan.php` (atau yang tersedia).
7. Migrasi `dashboard-admin.php` dan `dashboard-stand.php`:
   - ganti semua operasi CRUD Firestore menjadi CRUD via endpoint backend.
   - pastikan update status order memakai `API.updateOrder` (atau mekanisme PUT via api-client).
8. Hapus semua import `firebase-config.js` dari file-file yang sudah dimigrasi.
9. Periksa referensi file `.html` vs `.php` untuk navigasi (gunakan yang benar).
10. Jalankan smoke test:
   - register -> login -> home -> cart -> checkout -> status pesanan.
   - akses dashboard sesuai role.

## Done/Progress
- [ ] Langkah 1
- [ ] Langkah 2
- [ ] Langkah 3
- [ ] Langkah 4
- [ ] Langkah 5
- [ ] Langkah 6
- [ ] Langkah 7
- [ ] Langkah 8
- [ ] Langkah 9
- [ ] Langkah 10

