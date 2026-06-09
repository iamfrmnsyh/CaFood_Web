<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>🔐 Setup Akun Stand Owner</h2>";

// Data akun stand owner
$accounts = [
    [
        'name' => 'Warung Makan PW',
        'email' => 'warung@wpw.com',
        'password' => 'operator123',
        'stand_name' => 'Warung Makan PW'
    ],
    [
        'name' => 'Bakso Barokah',
        'email' => 'baksobarokah@bb.com',
        'password' => 'operator456',
        'stand_name' => 'Bakso Barokah'
    ],
    [
        'name' => 'Kopken',
        'email' => 'kopken@kk.com',
        'password' => 'operator789',
        'stand_name' => 'Kopken'
    ],
    [
        'name' => 'Dynasty Kitchen',
        'email' => 'dynastikitchen@kdc.com',
        'password' => 'operator1011',
        'stand_name' => 'Kantin Dinasty Kitchen'
    ],
    [
        'name' => 'Syailendra',
        'email' => 'syailendra@ws.com',
        'password' => 'operator1213',
        'stand_name' => 'Warmindo Syailendra 168'
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

// 2. Cek tabel users
echo "<h3>2. Cek Tabel Users:</h3>";
$stmt = $pdo->query("SHOW TABLES LIKE 'users'");
if ($stmt->rowCount() > 0) {
    echo "✅ Tabel users ditemukan<br>";
} else {
    echo "❌ Tabel users TIDAK ditemukan!<br>";
    exit;
}

// 3. Cek tabel stands
echo "<h3>3. Cek Tabel Stands:</h3>";
$stmt = $pdo->query("SHOW TABLES LIKE 'stands'");
if ($stmt->rowCount() > 0) {
    echo "✅ Tabel stands ditemukan<br>";
} else {
    echo "❌ Tabel stands TIDAK ditemukan!<br>";
    exit;
}

// 4. Buat atau update akun stand owner
echo "<h3>4. Setup Akun Stand Owner:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background:#6C4CF1; color:white;'><th>Stand Name</th><th>Email</th><th>Password</th><th>Status</th></tr>";

foreach ($accounts as $account) {
    // Cek apakah stand sudah ada
    $stmt = $pdo->prepare("SELECT id FROM stands WHERE stand_name = ?");
    $stmt->execute([$account['stand_name']]);
    $stand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stand) {
        // Buat stand baru
        $stmt = $pdo->prepare("INSERT INTO stands (stand_name, description, status, created_at) VALUES (?, ?, 'Open', ?)");
        $stmt->execute([$account['stand_name'], 'Restaurant and food stall', $now]);
        $standId = $pdo->lastInsertId();
        echo "<tr>
            <td>{$account['stand_name']}</td>
            <td>{$account['email']}</td>
            <td>{$account['password']}</td>
            <td style='color:green;'>✅ Stand baru dibuat</td>
        </tr>";
    } else {
        $standId = $stand['id'];
        echo "<tr>
            <td>{$account['stand_name']}</td>
            <td>{$account['email']}</td>
            <td>{$account['password']}</td>
            <td style='color:blue;'>✅ Stand sudah ada</td>
        </table>";
    }
    
    // Hash password
    $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
    
    // Cek apakah user sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$account['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = 'stand', updated_at = ? WHERE email = ?");
        $stmt->execute([$account['name'], $hashedPassword, $now, $account['email']]);
        echo "<tr><td colspan='3'></td><td style='color:green;'>✅ User diupdate</td></tr>";
    } else {
        // Buat user baru
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'stand', ?)");
        $stmt->execute([$account['name'], $account['email'], $hashedPassword, $now]);
        echo "<tr><td colspan='3'></td><td style='color:green;'>✅ User baru dibuat</td></tr>";
    }
}
echo "</table>";

// 5. Tampilkan semua akun
echo "<h3>5. Daftar Semua Akun:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background:#6C4CF1; color:white;'><th>Name</th><th>Email</th><th>Role</th><th>Password</th></td>";

// Ambil admin
$stmt = $pdo->query("SELECT name, email, role FROM users WHERE email = 'admincafood@gmail.com'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    echo "<tr>
        <td>{$admin['name']}</td>
        <td>{$admin['email']}</td>
        <td><span style='color:#ef4444;'>Admin</span></td>
        <td><code>admin123</code></td>
    </tr>";
}

// Ambil semua stand owner
$stmt = $pdo->query("SELECT name, email, role FROM users WHERE role = 'stand' ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    // Cari password asli dari array accounts
    $originalPassword = '';
    foreach ($accounts as $acc) {
        if ($acc['email'] == $user['email']) {
            $originalPassword = $acc['password'];
            break;
        }
    }
    
    echo "<tr>";
    echo "<td>{$user['name']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td><span style='color:#f59e0b;'>Stand Owner</span></td>";
    echo "<td><code>{$originalPassword}</code></td>";
    echo "</tr>";
}

// Ambil customer
$stmt = $pdo->query("SELECT name, email, role FROM users WHERE role = 'customer' ORDER BY id LIMIT 5");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($customers as $customer) {
    echo "<tr>";
    echo "<td>{$customer['name']}</td>";
    echo "<td>{$customer['email']}</td>";
    echo "<td><span style='color:#10b981;'>Customer</span></td>";
    echo "<td><code>********</code></td>";
    echo "</tr>";
}
echo "</table>";

echo "<br>";
echo "<a href='login.php' style='background:#6C4CF1; color:white; padding:12px 24px; text-decoration:none; border-radius:10px; display:inline-block; margin-right:10px;'>🔐 Klik untuk Login</a>";
echo "<a href='dashboard-admin.php' style='background:#10b981; color:white; padding:12px 24px; text-decoration:none; border-radius:10px; display:inline-block; margin-right:10px;'>👑 Dashboard Admin</a>";
echo "<a href='index.php' style='background:#f59e0b; color:white; padding:12px 24px; text-decoration:none; border-radius:10px; display:inline-block;'>🏠 Home</a>";
?>