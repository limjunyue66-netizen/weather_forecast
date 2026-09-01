<?php
/**
 * SkySoft Weather - City Search API
 * GET ?q=search_term
 */

require_once dirname(__DIR__) . '/includes/load_config.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

$query = isset($_GET['q']) ? sanitizeString($_GET['q'], 100) : '';

if (mb_strlen($query) < 2) {
    jsonResponse(['success' => true, 'results' => []]);
}

$cacheKey = 'search_' . mb_strtolower($query);
$cached = cacheGet($cacheKey);

if ($cached !== null) {
    jsonResponse(['success' => true, 'results' => $cached, 'cached' => true]);
}

$url = GEOCODING_API . '?' . http_build_query([
    'name'           => $query,
    'count'          => 10,
    'language'       => 'en',
    'format'         => 'json',
]);

$response = httpGet($url);

if ($response === null) {
    jsonResponse([
        'success' => false,
        'error'   => 'search_failed',
        'message' => 'Unable to search cities. Please try again.',
    ], 503);
}

$data = json_decode($response, true);

if (!is_array($data) || !isset($data['results'])) {
    jsonResponse(['success' => true, 'results' => []]);
}

$results = [];
foreach ($data['results'] as $item) {
    $results[] = [
        'name'         => $item['name'] ?? '',
        'country'      => $item['country'] ?? '',
        'country_code' => $item['country_code'] ?? '',
        'latitude'     => $item['latitude'] ?? 0,
        'longitude'    => $item['longitude'] ?? 0,
        'admin1'       => $item['admin1'] ?? '',
        'timezone'     => $item['timezone'] ?? 'UTC',
    ];
}

cacheSet($cacheKey, $results, SEARCH_CACHE_TTL);

jsonResponse(['success' => true, 'results' => $results]);
