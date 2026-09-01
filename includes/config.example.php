<?php
/**
 * SkySoft Weather - Configuration Example
 *
 * Copy this file to config.php and update your database credentials:
 *   cp includes/config.example.php includes/config.php
 */

define('APP_NAME', 'SkySoft Weather');
define('APP_VERSION', '1.0.0');

// Database settings (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_NAME', 'weather_forecast');
define('DB_USER', 'root');
define('DB_PASS', '');          // Set your MySQL password here
define('DB_CHARSET', 'utf8mb4');

// Cache settings
define('CACHE_DIR', dirname(__DIR__) . '/cache/');
define('WEATHER_CACHE_TTL', 300);   // 5 minutes
define('SEARCH_CACHE_TTL', 1800);   // 30 minutes

// Open-Meteo API endpoints
define('GEOCODING_API', 'https://geocoding-api.open-meteo.com/v1/search');
define('WEATHER_API', 'https://api.open-meteo.com/v1/forecast');

// Error handling - never display errors to users
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Timezone default (will be overridden per city)
date_default_timezone_set('UTC');
