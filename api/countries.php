<?php
/**
 * SkySoft Weather - Countries API
 * GET ?letter=A  (optional, filter by first letter)
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

$countriesFile = dirname(__DIR__) . '/assets/data/countries.json';

if (!file_exists($countriesFile)) {
    jsonResponse([
        'success' => false,
        'error'   => 'file_not_found',
        'message' => 'Country data is unavailable.',
    ], 500);
}

$content = file_get_contents($countriesFile);
$data = json_decode($content, true);

if (!is_array($data)) {
    error_log('Invalid countries.json format');
    jsonResponse([
        'success' => false,
        'error'   => 'invalid_data',
        'message' => 'Country data is unavailable.',
    ], 500);
}

$letter = isset($_GET['letter']) ? strtoupper(sanitizeString($_GET['letter'], 1)) : '';

if ($letter !== '' && !preg_match('/^[A-Z]$/', $letter)) {
    jsonResponse([
        'success' => false,
        'error'   => 'invalid_letter',
        'message' => 'Invalid letter.',
    ], 400);
}

if ($letter !== '') {
    $filtered = array_values(array_filter($data, function ($country) use ($letter) {
        $name = $country['name'] ?? '';
        return strtoupper(mb_substr($name, 0, 1)) === $letter;
    }));

    jsonResponse([
        'success'   => true,
        'letter'    => $letter,
        'countries' => $filtered,
        'count'     => count($filtered),
    ]);
}

// Return all countries grouped by letter
$grouped = [];
foreach (range('A', 'Z') as $l) {
    $grouped[$l] = [];
}

foreach ($data as $country) {
    $name = $country['name'] ?? '';
    if ($name === '') continue;
    $first = strtoupper(mb_substr($name, 0, 1));
    if (isset($grouped[$first])) {
        $grouped[$first][] = $country;
    }
}

jsonResponse([
    'success'  => true,
    'total'    => count($data),
    'grouped'  => $grouped,
    'letters'  => range('A', 'Z'),
]);
