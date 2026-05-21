# Backend setup for CaFood_Web (MySQL + PHP)

Steps to migrate from Firebase to phpMyAdmin (MySQL):

1. Import database schema

   - Open phpMyAdmin and run the SQL in `backend/init.sql` to create the `cafood` database and tables.

2. Configure DB connection

   - Edit `backend/db.php` and set your MySQL credentials (`$host`, `$db`, `$user`, `$pass`).

3. Serve PHP files

   - If using XAMPP, place this project under `htdocs` or configure a virtual host.
   - Alternatively run the built-in PHP server from the workspace root:

```
php -S localhost:8000
```

4. Update frontend

   - Include `js/api-client.js` in pages that previously used Firebase.
   - Replace Firebase calls with the simple helpers exposed on `window.API`, for example:

```
API.getMenus().then(menus => console.log(menus));
API.register({name:'A',email:'a@a.com',password:'123456'});
```

5. Notes

   - Passwords are hashed with `password_hash` on registration. Login returns user data (no token).
   - For production, add authentication tokens (JWT or session) and HTTPS.
