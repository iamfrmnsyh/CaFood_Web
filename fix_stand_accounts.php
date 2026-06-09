<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>🔧 Perbaikan Akun Stand Owner</h2>";

// Data akun stand owner
$accounts = [
    [
        'name' => 'Warung Makan PW',
        'email' => 'warung@wpw.com',
        'password' => 'operator123',
        'stand_name' => 'Warung Makan PW',
        'description' => 'Menu nasi rames, ayam bakar, ayam goreng, aneka lauk & sayur',
        'estimasiWaktu' => '15-20 min',
        'rating' => 4.7
    ],
    [
        'name' => 'Kantin Dinasty Kitchen',
        'email' => 'dynastikitchen@kdc.com',
        'password' => 'operator1011',
        'stand_name' => 'Kantin Dinasty Kitchen',
        'description' => 'Bakso, ayam geprek, nasi goreng, takoyaki, kupat',
        'estimasiWaktu' => '10-20 min',
        'rating' => 4.8
    ],
    [
        'name' => 'Warmindo Syailendra 168',
        'email' => 'syailendra@ws.com',
        'password' => 'operator1213',
        'stand_name' => 'Warmindo Syailendra 168',
        'description' => 'Indomie goreng, rebus, telur, pecel, gado-gado',
        'estimasiWaktu' => '10-15 min',
        'rating' => 4.6
    ],
    [
        'name' => 'Bakso Barokah',
        'email' => 'baksobarokah@bb.com',
        'password' => 'operator456',
        'stand_name' => 'Bakso Barokah',
        'description' => 'Bakso urat, bakso telur, mie ayam',
        'estimasiWaktu' => '10-15 min',
        'rating' => 4.5
    ],
    [
        'name' => 'Kopken',
        'email' => 'kopken@kk.com',
        'password' => 'operator789',
        'stand_name' => 'Kopken',
        'description' => 'Kopi, teh, snack, gorengan',
        'estimasiWaktu' => '5-10 min',
        'rating' => 4.4
    ]
];

// Current timestamp
$now = date('Y-m-d H:i:s');

// 1. Cek koneksi database
echo "<h3>1. Koneksi Database:</h3>";
try {
    $pdo->query("SELECT 1");
    echo "✅ Koneksi database BERHASIL<br>";
} catch (Exception $e) {
    echo "❌ Koneksi database GAGAL: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Proses setiap akun
echo "<h3>2. Proses Akun Stand Owner:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width:100%;'>";
echo "<tr style='background:#f59e0b; color:white;'>
        <th>Stand Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Status User</th>
        <th>Status Stand</th>
     </tr>";

foreach ($accounts as $account) {
    // Hash password
    $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
    
    // Proses User
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$account['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $userStatus = '';
    $userId = null;
    
    if ($existingUser) {
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = 'stand', updated_at = ? WHERE email = ?");
        $stmt->execute([$account['name'], $hashedPassword, $now, $account['email']]);
        $userId = $existingUser['id'];
        $userStatus = "✅ User diupdate";
    } else {
        // Insert user baru
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'stand', ?)");
        $stmt->execute([$account['name'], $account['email'], $hashedPassword, $now]);
        $userId = $pdo->lastInsertId();
        $userStatus = "✅ User baru dibuat";
    }
    
    // Proses Stand
    $stmt = $pdo->prepare("SELECT id FROM stands WHERE stand_name = ?");
    $stmt->execute([$account['stand_name']]);
    $existingStand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $standStatus = '';
    
    if ($existingStand) {
        // Update stand
        $stmt = $pdo->prepare("UPDATE stands SET 
            stand_name = ?, 
            description = ?, 
            estimasiWaktu = ?, 
            rating = ?, 
            status = 'Open', 
            user_id = ?, 
            updated_at = ? 
            WHERE id = ?");
        $stmt->execute([
            $account['stand_name'],
            $account['description'],
            $account['estimasiWaktu'],
            $account['rating'],
            $userId,
            $now,
            $existingStand['id']
        ]);
        $standStatus = "✅ Stand diupdate";
    } else {
        // Insert stand baru
        $stmt = $pdo->prepare("INSERT INTO stands (stand_name, description, estimasiWaktu, rating, status, user_id, created_at) 
            VALUES (?, ?, ?, ?, 'Open', ?, ?)");
        $stmt->execute([
            $account['stand_name'],
            $account['description'],
            $account['estimasiWaktu'],
            $account['rating'],
            $userId,
            $now
        ]);
        $standStatus = "✅ Stand baru dibuat";
    }
    
    echo "<tr>";
    echo "<td>{$account['stand_name']}</td>";
    echo "<td>{$account['email']}</td>";
    echo "<td>{$account['password']}</td>";
    echo "<td style='color:green;'>{$userStatus}</td>";
    echo "<td style='color:green;'>{$standStatus}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Tampilkan semua akun untuk login
echo "<h3>3. Akun untuk Login:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width:100%;'>";
echo "<tr style='background:#6C4CF1; color:white;'>
        <th>Role</th>
        <th>Email</th>
        <th>Password</th>
        <th>Nama</th>
     </tr>";

// Tampilkan admin
echo "<tr>";
echo "<td><span style='color:#ef4444; font-weight:bold;'>Admin</span></td>";
echo "<td>admincafood@gmail.com</td>";
echo "<td>admin123</td>";
echo "<td>Administrator</td>";
echo "</tr>";

// Tampilkan semua stand owner
$stmt = $pdo->query("SELECT name, email, role FROM users WHERE role = 'stand' ORDER BY id");
$standUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($standUsers as $user) {
    // Cari password dari array accounts
    $originalPassword = '';
    foreach ($accounts as $acc) {
        if ($acc['email'] == $user['email']) {
            $originalPassword = $acc['password'];
            break;
        }
    }
    echo "<tr>";
    echo "<td><span style='color:#f59e0b; font-weight:bold;'>Stand Owner</span></td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$originalPassword}</td>";
    echo "<td>{$user['name']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br>";
echo "<div style='background:#D1FAE5; padding:15px; border-radius:10px; margin-top:20px;'>";
echo "<strong>📝 Cara Login:</strong><br>";
echo "1. Buka <a href='login.php'>login.php</a><br>";
echo "2. Masukkan email dan password dari tabel di atas<br>";
echo "3. Pilih role yang sesuai<br>";
echo "</div>";

echo "<br>";
echo "<a href='login.php' style='background:#6C4CF1; color:white; padding:12px 24px; text-decoration:none; border-radius:10px; display:inline-block; margin-right:10px;'>🔐 Klik untuk Login</a>";
echo "<a href='dashboard-admin.php' style='background:#10b981; color:white; padding:12px 24px; text-decoration:none; border-radius:10px; display:inline-block;'>👑 Dashboard Admin</a>";
?>