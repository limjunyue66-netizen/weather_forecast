<?php
/**
 * SkySoft Weather - Database Connection (PDO)
 */

require_once __DIR__ . '/load_config.php';

/**
 * Get PDO database connection.
 * Returns null if connection fails (weather page still works).
 */
function getDB(): ?PDO
{
    static $pdo = null;
    static $attempted = false;

    if ($attempted) {
        return $pdo;
    }

    $attempted = true;
    $pdo = connectToDatabase();

    return $pdo;
}

/**
 * Attempt connection; auto-create database and tables on first run if missing.
 */
function connectToDatabase(): ?PDO
{
    try {
        return createPDO(DB_NAME);
    } catch (PDOException $e) {
        $message = $e->getMessage();

        // Database does not exist yet — create it and retry once
        if (isMissingDatabaseError($message)) {
            try {
                initializeDatabase();
                return createPDO(DB_NAME);
            } catch (PDOException $setupError) {
                error_log('Database setup failed: ' . $setupError->getMessage());
                return null;
            }
        }

        error_log('Database connection failed: ' . $message);
        return null;
    }
}

/**
 * Create PDO connection to a specific database.
 */
function createPDO(string $database): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . $database . ';charset=' . DB_CHARSET;

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/**
 * Check if PDO error indicates the database is missing.
 */
function isMissingDatabaseError(string $message): bool
{
    return str_contains($message, 'Unknown database')
        || str_contains($message, '1049');
}

/**
 * Create database and required tables.
 */
function initializeDatabase(): void
{
    $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci'
    );

    $pdo->exec('USE `' . DB_NAME . '`');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS search_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            city VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            country_code VARCHAR(10) NOT NULL DEFAULT \'\',
            latitude DECIMAL(10, 7) NOT NULL,
            longitude DECIMAL(10, 7) NOT NULL,
            searched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_searched_at (searched_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            city VARCHAR(255) NOT NULL,
            country VARCHAR(255) NOT NULL,
            country_code VARCHAR(10) NOT NULL DEFAULT \'\',
            latitude DECIMAL(10, 7) NOT NULL,
            longitude DECIMAL(10, 7) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_location (city, country_code, latitude, longitude)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

/**
 * Check if database is available.
 */
function isDBAvailable(): bool
{
    return getDB() !== null;
}
