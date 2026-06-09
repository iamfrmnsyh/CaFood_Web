# TODO - CaFood Auth Fix

## Planned changes
- [ ] Ubah `register.php` agar pendaftaran akun dilakukan via Backend MySQL melalui endpoint:
  - `POST /backend/api.php?resource=users&action=register`
- [ ] Pastikan data yang dikirim sesuai ekspektasi backend:
  - `name`, `email`, `password`, `role`
- [ ] Setelah register sukses, redirect ke `login.php`.
- [ ] Uji:
  - Register -> seharusnya tersimpan di MySQL `users`
  - Login -> seharusnya tidak 401 dan redirect sesuai role.

