<?php
/**
 * SkySoft Weather - Weather API
 * GET ?lat=..&lon=..&timezone=..&city=..&country=..&country_code=..
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

$lat  = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon  = isset($_GET['lon']) ? floatval($_GET['lon']) : null;
$tz   = isset($_GET['timezone']) ? sanitizeString($_GET['timezone'], 100) : 'UTC';
$city = isset($_GET['city']) ? sanitizeString($_GET['city'], 255) : '';
$country = isset($_GET['country']) ? sanitizeString($_GET['country'], 255) : '';
$countryCode = isset($_GET['country_code']) ? sanitizeString($_GET['country_code'], 10) : '';

if (!isValidLatitude($lat) || !isValidLongitude($lon)) {
    jsonResponse([
        'success' => false,
        'error'   => 'invalid_coordinates',
        'message' => 'Invalid location coordinates.',
    ], 400);
}

// Validate timezone (allow "auto" for Open-Meteo auto-detection)
if ($tz !== 'auto') {
    try {
        new DateTimeZone($tz);
    } catch (Exception $e) {
        $tz = 'auto';
    }
}

$cacheKey = 'weather_' . round($lat, 4) . '_' . round($lon, 4);
$cached = cacheGet($cacheKey);

if ($cached !== null) {
    $cached['city'] = $city ?: ($cached['city'] ?? '');
    $cached['country'] = $country ?: ($cached['country'] ?? '');
    $cached['country_code'] = $countryCode ?: ($cached['country_code'] ?? '');
    jsonResponse(['success' => true, 'data' => $cached, 'cached' => true]);
}

$params = [
    'latitude'                   => $lat,
    'longitude'                  => $lon,
    'current'                    => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m,surface_pressure,is_day',
    'hourly'                     => 'temperature_2m,weather_code',
    'daily'                      => 'weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset',
    'timezone'                   => $tz,
    'forecast_days'              => 7,
    'forecast_hours'             => 24,
    'wind_speed_unit'            => 'kmh',
];

$url = WEATHER_API . '?' . http_build_query($params);
$response = httpGet($url);

if ($response === null) {
    jsonResponse([
        'success' => false,
        'error'   => 'weather_failed',
        'message' => 'Unable to load weather information. Please try again.',
    ], 503);
}

$data = json_decode($response, true);

if (!is_array($data) || !isset($data['current'])) {
    error_log('Invalid weather API response for lat=' . $lat . ' lon=' . $lon);
    jsonResponse([
        'success' => false,
        'error'   => 'weather_failed',
        'message' => 'Unable to load weather information. Please try again.',
    ], 503);
}

// Use resolved timezone from API when "auto" was requested
if ($tz === 'auto' && !empty($data['timezone'])) {
    $tz = $data['timezone'];
}

$current = $data['current'];
$currentCode = (int) ($current['weather_code'] ?? 0);
$isDay = (bool) ($current['is_day'] ?? 1);

// Get today's sunrise/sunset
$todaySunrise = $data['daily']['sunrise'][0] ?? null;
$todaySunset  = $data['daily']['sunset'][0] ?? null;

$localTime = formatDateTime($current['time'] ?? 'now', $tz, 'Y-m-d H:i:s', true);
$localDate = formatDateTime($current['time'] ?? 'now', $tz, 'l, d F Y', true);

// Build hourly forecast (next 24 hours from now)
$hourly = [];
if (isset($data['hourly']['time'], $data['hourly']['temperature_2m'], $data['hourly']['weather_code'])) {
    $now = new DateTime('now', new DateTimeZone($tz));
    $count = 0;

    foreach ($data['hourly']['time'] as $i => $time) {
        try {
            $ht = new DateTime($time, new DateTimeZone($tz));
        } catch (Exception $e) {
            continue;
        }

        if ($ht < $now) {
            continue;
        }

        $hCode = (int) ($data['hourly']['weather_code'][$i] ?? 0);
        $hIsDay = isDaytime($time, $tz, $todaySunrise, $todaySunset);

        $hourly[] = [
            'time'        => formatDateTime($time, $tz, 'H:i', true),
            'temperature' => round($data['hourly']['temperature_2m'][$i] ?? 0, 1),
            'weather_code'=> $hCode,
            'condition'   => weatherCodeToKey($hCode),
            'icon'        => weatherCodeToIcon($hCode, $hIsDay),
        ];

        $count++;
        if ($count >= 24) {
            break;
        }
    }
}

// Build daily forecast (7 days)
$daily = [];
if (isset($data['daily']['time'])) {
    foreach ($data['daily']['time'] as $i => $date) {
        try {
            $dt = new DateTime($date, new DateTimeZone($tz));
            $formattedDate = $dt->format('Y-m-d');
            $displayDate = $dt->format('D, d M');
        } catch (Exception $e) {
            continue;
        }

        $dCode = (int) ($data['daily']['weather_code'][$i] ?? 0);

        $daily[] = [
            'date'         => $formattedDate,
            'display_date' => $displayDate,
            'temp_max'     => round($data['daily']['temperature_2m_max'][$i] ?? 0, 1),
            'temp_min'     => round($data['daily']['temperature_2m_min'][$i] ?? 0, 1),
            'weather_code' => $dCode,
            'condition'    => weatherCodeToKey($dCode),
            'icon'         => weatherCodeToIcon($dCode, true),
        ];
    }
}

$weatherData = [
    'city'          => $city,
    'country'       => $country,
    'country_code'  => $countryCode,
    'latitude'      => $lat,
    'longitude'     => $lon,
    'timezone'      => $tz,
    'local_time'    => $localTime,
    'local_date'    => $localDate,
    'current'       => [
        'temperature'    => round($current['temperature_2m'] ?? 0, 1),
        'feels_like'     => round($current['apparent_temperature'] ?? 0, 1),
        'humidity'       => (int) ($current['relative_humidity_2m'] ?? 0),
        'wind_speed'     => round($current['wind_speed_10m'] ?? 0, 1),
        'wind_direction' => degreesToCompass((float) ($current['wind_direction_10m'] ?? 0)),
        'wind_degrees'   => (int) ($current['wind_direction_10m'] ?? 0),
        'pressure'       => round($current['surface_pressure'] ?? 0, 0),
        'weather_code'   => $currentCode,
        'condition'      => weatherCodeToKey($currentCode),
        'icon'           => weatherCodeToIcon($currentCode, $isDay),
        'sunrise'        => $todaySunrise ? formatDateTime($todaySunrise, $tz, 'H:i', true) : '',
        'sunset'         => $todaySunset ? formatDateTime($todaySunset, $tz, 'H:i', true) : '',
    ],
    'hourly' => $hourly,
    'daily'  => $daily,
];

cacheSet($cacheKey, $weatherData, WEATHER_CACHE_TTL);

jsonResponse(['success' => true, 'data' => $weatherData]);
