<?php
// C:\Users\user\CaFood_Web\config\database.php

$host = 'localhost';
$dbname = 'cafood_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Optional: Set timezone
    $pdo->exec("SET time_zone = '+07:00'");
    
    // For debugging - comment in production
    // echo "Database connected successfully";
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>