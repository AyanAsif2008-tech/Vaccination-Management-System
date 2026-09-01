<?php
/**
 * Database Configuration
 * Vaccination Management System - Admin Panel
 *
 * Update the credentials below to match your local MySQL / XAMPP setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'vaccination_system');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
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
        <p>Make sure XAMPP\'s MySQL service is running and that the
        <strong>vaccination_system</strong> database has been imported
        (see <code>vaccination_system.sql</code>).</p>
        </div>');
}
