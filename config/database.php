<?php
/**
 * Database Configuration
 * Online Classifieds Marketplace
 *
 * This file centralizes PDO database connection settings.
 * Default settings are tuned for local development with XAMPP / MySQL.
 */

// Database credentials
// You can adjust DB_PORT if your MySQL runs on 3306, 3307, or 3308.
defined('DB_HOST') or define('DB_HOST', '127.0.0.1');
defined('DB_PORT') or define('DB_PORT', 3308);
defined('DB_NAME') or define('DB_NAME', 'online_classifieds');
defined('DB_USER') or define('DB_USER', 'root');
defined('DB_PASS') or define('DB_PASS', '');
defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO database connection instance.
 *
 * @return PDO|null Returns PDO connection on success, or null on failure.
 */
function get_db_connection(): ?PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 3,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Record error in error_log without exposing sensitive credentials
        error_log('Database Connection Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Checks if the database is reachable and the database exists.
 *
 * @return bool
 */
function is_db_connected(): bool {
    return get_db_connection() !== null;
}
