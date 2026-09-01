<?php
/**
 * Database Configuration
 * Vaccination Management System
 */

// ---- Your requested mysqli connection ----
$connection = mysqli_connect('localhost', 'root', '', 'vaccination_system');

if (!$connection) {
    die('<div style="font-family:Arial, sans-serif; padding:40px; color:#b91c1c;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars(mysqli_connect_error()) . '</p>
        <p>Make sure XAMPP\'s MySQL service is running and that the
        <strong>vaccination_system</strong> database has been imported.</p>
        </div>');
}

mysqli_set_charset($connection, 'utf8mb4');

// ---- PDO connection (same DB, same credentials) ----
// The rest of this project (every admin/parent/hospital page) is built on
// PDO — $pdo->prepare(), ->execute(), ->fetch(), etc. Rather than rewrite
// 30+ files to mysqli, this keeps $pdo working exactly as before while
// still giving you $connection to use however you'd like.
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=vaccination_system;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:Arial, sans-serif; padding:40px; color:#b91c1c;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        </div>');
}