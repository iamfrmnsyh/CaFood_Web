<?php
// create_admin.php
// Script untuk membuat akun admin

require_once __DIR__ . '/config/database.php';

$email = 'admincafood@gmail.com';  // <- Ganti email jadi lengkap
$plainPassword = 'admin123';
$role = 'admin';
$name = 'Administrator CaFood';

// Cek apakah admin sudah ada
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    // Jika sudah ada, update password
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ?, name = ? WHERE email = ?');
    $stmt->execute([$hashedPassword, $name, $email]);
    echo "✅ Admin user already exists. Password has been reset!<br>";
} else {
    // Jika belum ada, buat baru
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $hashedPassword, '', $role]);
    echo "✅ Admin user created successfully!<br>";
}

echo "<br>";
echo "🔐 Informasi Login:<br>";
echo "Email: <strong>admincafood@gmail.com</strong><br>";
echo "Password: <strong>admin123</strong><br>";
echo "<br>";
echo "<a href='login.php' style='background:#6C4CF1; color:white; padding:10px 20px; text-decoration:none; border-radius:10px;'>→ Klik untuk Login</a>";
?>