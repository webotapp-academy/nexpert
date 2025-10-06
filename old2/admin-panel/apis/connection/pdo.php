<?php

// Set default timezone to IST
date_default_timezone_set('Asia/Kolkata');

$host = 'srv1368.hstgr.io:3306';
$dbname = 'u621169360_replit';
$username = 'u621169360_replit';
$password = 'JAIhanuman89@@@';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Set MySQL timezone to IST
    $pdo->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
