<?php
/**
 * SkySoft Weather - Cache Helper
 */

require_once __DIR__ . '/config.php';

/**
 * Get cached data by key.
 */
function cacheGet(string $key): ?array
{
    $file = CACHE_DIR . md5($key) . '.json';

    if (!file_exists($file)) {
        return null;
    }

    $content = @file_get_contents($file);
    if ($content === false) {
        return null;
    }

    $data = json_decode($content, true);
    if (!is_array($data) || !isset($data['expires'], $data['payload'])) {
        @unlink($file);
        return null;
    }

    if (time() > $data['expires']) {
        @unlink($file);
        return null;
    }

    return $data['payload'];
}

/**
 * Store data in cache.
 */
function cacheSet(string $key, array $payload, int $ttl): bool
{
    if (!is_dir(CACHE_DIR)) {
        @mkdir(CACHE_DIR, 0755, true);
    }

    $file = CACHE_DIR . md5($key) . '.json';
    $data = [
        'expires' => time() + $ttl,
        'payload' => $payload,
    ];

    return @file_put_contents($file, json_encode($data), LOCK_EX) !== false;
}

/**
 * Send JSON response and exit.
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Sanitize string input.
 */
function sanitizeString(string $value, int $maxLength = 255): string
{
    $value = trim($value);
    $value = strip_tags($value);
    if (mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }
    return $value;
}

/**
 * Validate latitude.
 */
function isValidLatitude($lat): bool
{
    return is_numeric($lat) && $lat >= -90 && $lat <= 90;
}

/**
 * Validate longitude.
 */
function isValidLongitude($lon): bool
{
    return is_numeric($lon) && $lon >= -180 && $lon <= 180;
}

/**
 * Fetch URL with cURL or file_get_contents.
 */
function httpGet(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'SkySoft-Weather/1.0',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log('HTTP request failed: ' . $url . ' (code: ' . $httpCode . ')');
            return null;
        }
        return $response;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 15,
            'header'  => "User-Agent: SkySoft-Weather/1.0\r\n",
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    return $response !== false ? $response : null;
}

/**
 * Map WMO weather code to condition key for i18n.
 */
function weatherCodeToKey(int $code): string
{
    if ($code === 0) return 'clear';
    if ($code >= 1 && $code <= 3) return 'partly_cloudy';
    if ($code >= 45 && $code <= 48) return 'fog';
    if ($code >= 51 && $code <= 57) return 'drizzle';
    if ($code >= 61 && $code <= 67) return 'rain';
    if ($code >= 71 && $code <= 77) return 'snow';
    if ($code >= 80 && $code <= 82) return 'rain_showers';
    if ($code >= 85 && $code <= 86) return 'snow_showers';
    if ($code >= 95 && $code <= 99) return 'thunderstorm';
    return 'unknown';
}

/**
 * Get weather icon class from WMO code.
 */
function weatherCodeToIcon(int $code, bool $isDay = true): string
{
    if ($code === 0) return $isDay ? 'sun' : 'moon';
    if ($code >= 1 && $code <= 3) return $isDay ? 'partly-cloudy' : 'partly-cloudy-night';
    if ($code >= 45 && $code <= 48) return 'fog';
    if ($code >= 51 && $code <= 57) return 'drizzle';
    if ($code >= 61 && $code <= 67) return 'rain';
    if ($code >= 71 && $code <= 77) return 'snow';
    if ($code >= 80 && $code <= 82) return 'rain-showers';
    if ($code >= 85 && $code <= 86) return 'snow';
    if ($code >= 95 && $code <= 99) return 'thunderstorm';
    return 'cloud';
}

/**
 * Convert wind direction degrees to compass.
 */
function degreesToCompass(float $degrees): string
{
    $directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    $index = (int) round($degrees / 45) % 8;
    return $directions[$index];
}

/**
 * Safe date formatting with timezone.
 * Use $sourceIsLocal=true when the datetime string is already in the target timezone (Open-Meteo).
 */
function formatDateTime(string $datetime, string $timezone, string $format, bool $sourceIsLocal = false): string
{
    try {
        if ($sourceIsLocal) {
            $dt = new DateTime($datetime, new DateTimeZone($timezone));
        } else {
            $dt = new DateTime($datetime, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone($timezone));
        }
        return $dt->format($format);
    } catch (Exception $e) {
        error_log('Date format error: ' . $e->getMessage());
        return '';
    }
}

/**
 * Check if a datetime is daytime at given location.
 */
function isDaytime(string $datetime, string $timezone, ?string $sunrise, ?string $sunset): bool
{
    try {
        $dt = new DateTime($datetime, new DateTimeZone($timezone));

        if ($sunrise && $sunset) {
            $sr = new DateTime($sunrise, new DateTimeZone($timezone));
            $ss = new DateTime($sunset, new DateTimeZone($timezone));
            return $dt >= $sr && $dt < $ss;
        }

        $hour = (int) $dt->format('G');
        return $hour >= 6 && $hour < 18;
    } catch (Exception $e) {
        return true;
    }
}
