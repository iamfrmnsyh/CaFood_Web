<?php
require_once __DIR__ . '/config/database.php';

$email = 'admincafood@gmail.com';
$newPassword = 'admin123';

// Buat hash baru
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$hashedPassword, $email]);

echo "<h2>🔐 Update Password Admin</h2>";

if ($result) {
    echo "<p style='color:green;'>✅ Password berhasil diupdate!</p>";
    echo "<p>📧 Email: <strong>$email</strong></p>";
    echo "<p>🔑 Password: <strong>$newPassword</strong></p>";
    echo "<p>🔒 Hash baru: <code>" . $hashedPassword . "</code></p>";
    
    // Verifikasi
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (password_verify($newPassword, $user['password'])) {
        echo "<p style='color:green;'>✅ Verifikasi: Password cocok!</p>";
    } else {
        echo "<p style='color:red;'>❌ Verifikasi gagal!</p>";
    }
    
    echo "<br><a href='login.php' style='background:#6C4CF1; color:white; padding:10px 20px; text-decoration:none; border-radius:8px;'>→ Klik untuk Login</a>";
} else {
    echo "<p style='color:red;'>❌ Gagal mengupdate password. User tidak ditemukan.</p>";
}
?>