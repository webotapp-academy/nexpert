<?php

// Set default timezone to IST
date_default_timezone_set('Asia/Kolkata');

$host = 'srv1983.hstgr.io';
$dbname = 'u181502964_MakeNew';
$username = 'u181502964_MakeNew';
$password = '!YcXSZlt@cI5';

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
