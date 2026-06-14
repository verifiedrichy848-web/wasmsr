<?php
// db.php - single DB config used by all files
$host = 'localhost';
$db   = 'scu_journal';    // create this DB first or use phpMyAdmin to import schema
$user = 'root';
$pass = '';              // set your DB password if any
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // don't leak details in production
    die("Database connection failed: " . $e->getMessage());
}
