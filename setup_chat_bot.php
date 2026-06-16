<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Setup bot untuk semua stand atau stand tertentu
$standId = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : 0;

if ($standId > 0) {
    // Setup untuk stand tertentu
    $stmt = $pdo->prepare("
        INSERT INTO chat_bot_settings (stand_id, enabled, auto_reply_delay, greeting_message, auto_reply_template, business_hours_start, business_hours_end, out_of_hours_message, created_at) 
        VALUES (?, 1, 5, 
            'Halo! Terima kasih sudah menghubungi kami. Tim kami akan segera merespon pesan Anda.',
            'Terima kasih atas pesannya! Kami akan segera menghubungi Anda kembali.',
            '08:00', '22:00',
            'Maaf, kami sedang di luar jam operasional (08:00-22:00). Kami akan merespon saat jam operasional.',
            NOW()
        )
        ON DUPLICATE KEY UPDATE updated_at = NOW()
    ");
    $stmt->execute([$standId]);
    echo "✅ Chat bot activated for stand ID: $standId<br>";
} else {
    // Setup untuk semua stand
    $stmt = $pdo->query("SELECT id FROM stands");
    $stands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($stands as $stand) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO chat_bot_settings (stand_id, enabled, auto_reply_delay, greeting_message, auto_reply_template, business_hours_start, business_hours_end, out_of_hours_message, created_at) 
            VALUES (?, 1, 5, 
                'Halo! Terima kasih sudah menghubungi kami. Tim kami akan segera merespon pesan Anda.',
                'Terima kasih atas pesannya! Kami akan segera menghubungi Anda kembali.',
                '08:00', '22:00',
                'Maaf, kami sedang di luar jam operasional (08:00-22:00). Kami akan merespon saat jam operasional.',
                NOW()
            )
        ");
        $stmt->execute([$stand['id']]);
    }
    echo "✅ Chat bot activated for all stands (" . count($stands) . " stands)<br>";
}

echo "<br><a href='dashboard-stand.php'>Kembali ke Dashboard</a>";
?>