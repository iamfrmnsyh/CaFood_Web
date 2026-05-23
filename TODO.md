# TODO - Migrasi HTML ke PHP (kecuali splash)

- [ ] Identifikasi semua file `.html` di project (kecuali `splash.html`)
- [ ] Untuk setiap file: buat versi `.php` dengan isi yang sama
- [ ] Setelah berhasil dibuat: hapus file `.html` aslinya
- [ ] Cari & update semua referensi link `*.html` → `*.php` yang sesuai (href/script/fetch)
- [ ] Verifikasi endpoint utama: `public/index.php`, `public/login.php`, dashboard admin/stand
- [ ] Jalankan smoke test (jika tersedia) untuk memastikan tidak ada 404 karena referensi lama

