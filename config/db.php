<?php
/**
 * Database Configuration
 * Vaccination Management System
 */

// Database Credentials
$db_host = 'localhost';
$db_name = 'vaccination_system';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

// ---- PDO Connection ----
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die('<div style="font-family:Arial, sans-serif; padding:40px; color:#b91c1c;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>Make sure XAMPP\'s MySQL service is running and that the
        <strong>vaccination_system</strong> database has been imported.</p>
        </div>');
}