<?php
// backend/db.php - PDO connection helper
header('Content-Type: application/json; charset=utf-8');
$host = '127.0.0.1';
$db   = 'cafood';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // MySQL is required. If it fails, stop with an error (no SQLite fallback).
    http_response_code(500);
    echo json_encode([
        'error' => 'MySQL DB connection failed',
        'message' => $e->getMessage(),
        'dsn' => $dsn
    ]);
    exit;
}


// Export $pdo to API files via include
