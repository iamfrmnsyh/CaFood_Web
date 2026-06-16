<?php
/**
 * File ini dijalankan setiap 1-2 menit via cron atau background process
 * Untuk menjalankan: php chat_bot_scheduler.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/chat_bot.php';

// Cek apakah bot sudah dijalankan dalam 1 menit terakhir
$lockFile = __DIR__ . '/tmp/chat_bot.lock';

if (file_exists($lockFile) && (time() - filemtime($lockFile) < 60)) {
    exit;
}

// Buat lock file
if (!is_dir(__DIR__ . '/tmp')) {
    mkdir(__DIR__ . '/tmp', 0777, true);
}
touch($lockFile);

// Jalankan bot
$bot = new ChatBot($pdo);
$result = $bot->processPendingMessages();

// Log hasil
$logFile = __DIR__ . '/logs/chat_bot.log';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

$log = date('Y-m-d H:i:s') . " - Processed " . count($result) . " messages\n";
foreach ($result as $res) {
    if ($res['success']) {
        $log .= "  ✓ {$res['customer_name']} -> {$res['stand_name']}: {$res['bot_reply']}\n";
    }
}
file_put_contents($logFile, $log, FILE_APPEND);

// Hapus lock file
unlink($lockFile);