<?php
/**
 * Database Configuration (EXAMPLE)
 * Copy this file to database.php and update with your credentials
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'votabudapest');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection
 * @throws PDOException If connection fails
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this error instead of displaying it
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
