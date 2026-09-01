<?php
/**
 * SkySoft Weather - History & Favorites API
 *
 * Actions:
 *   GET  ?action=history          - Get search history
 *   GET  ?action=favorites        - Get favorites
 *   POST action=add_history       - Add to search history
 *   POST action=add_favorite      - Add favorite
 *   POST action=remove_favorite   - Remove favorite
 */

require_once dirname(__DIR__) . '/includes/load_config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = isset($_GET['action']) ? sanitizeString($_GET['action'], 50) : 'history';
    handleGet($action);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }
    $action = isset($input['action']) ? sanitizeString($input['action'], 50) : '';
    handlePost($action, $input);
} else {
    jsonResponse(['success' => false, 'error' => 'method_not_allowed'], 405);
}

function handleGet(string $action): void
{
    $db = getDB();

    if ($db === null) {
        jsonResponse([
            'success'  => true,
            'available' => false,
            'items'    => [],
        ]);
    }

    if ($action === 'favorites') {
        try {
            $stmt = $db->query('SELECT * FROM favorites ORDER BY created_at DESC LIMIT 50');
            $items = $stmt->fetchAll();
            jsonResponse(['success' => true, 'available' => true, 'items' => $items]);
        } catch (PDOException $e) {
            error_log('Favorites fetch error: ' . $e->getMessage());
            jsonResponse(['success' => true, 'available' => false, 'items' => []]);
        }
    }

    // Default: history
    try {
        $stmt = $db->query('SELECT * FROM search_history ORDER BY searched_at DESC LIMIT 20');
        $items = $stmt->fetchAll();
        jsonResponse(['success' => true, 'available' => true, 'items' => $items]);
    } catch (PDOException $e) {
        error_log('History fetch error: ' . $e->getMessage());
        jsonResponse(['success' => true, 'available' => false, 'items' => []]);
    }
}

function handlePost(string $action, array $input): void
{
    $db = getDB();

    if ($db === null) {
        jsonResponse([
            'success'   => false,
            'available' => false,
            'message'   => 'Database is unavailable.',
        ]);
    }

    $city        = sanitizeString($input['city'] ?? '', 255);
    $country     = sanitizeString($input['country'] ?? '', 255);
    $countryCode = sanitizeString($input['country_code'] ?? '', 10);
    $lat         = isset($input['latitude']) ? floatval($input['latitude']) : null;
    $lon         = isset($input['longitude']) ? floatval($input['longitude']) : null;

    if ($city === '' || !isValidLatitude($lat) || !isValidLongitude($lon)) {
        jsonResponse(['success' => false, 'error' => 'invalid_data'], 400);
    }

    switch ($action) {
        case 'add_history':
            addHistory($db, $city, $country, $countryCode, $lat, $lon);
            break;
        case 'add_favorite':
            addFavorite($db, $city, $country, $countryCode, $lat, $lon);
            break;
        case 'remove_favorite':
            removeFavorite($db, $city, $countryCode, $lat, $lon);
            break;
        default:
            jsonResponse(['success' => false, 'error' => 'invalid_action'], 400);
    }
}

function addHistory(PDO $db, string $city, string $country, string $countryCode, float $lat, float $lon): void
{
    try {
        // Remove duplicate entry for same city
        $del = $db->prepare(
            'DELETE FROM search_history WHERE city = ? AND country_code = ? AND latitude = ? AND longitude = ?'
        );
        $del->execute([$city, $countryCode, $lat, $lon]);

        $stmt = $db->prepare(
            'INSERT INTO search_history (city, country, country_code, latitude, longitude, searched_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$city, $country, $countryCode, $lat, $lon]);

        // Keep only last 50 entries
        $db->exec('DELETE FROM search_history WHERE id NOT IN (
            SELECT id FROM (SELECT id FROM search_history ORDER BY searched_at DESC LIMIT 50) AS tmp
        )');

        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        error_log('Add history error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'db_error']);
    }
}

function addFavorite(PDO $db, string $city, string $country, string $countryCode, float $lat, float $lon): void
{
    try {
        $stmt = $db->prepare(
            'INSERT IGNORE INTO favorites (city, country, country_code, latitude, longitude, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$city, $country, $countryCode, $lat, $lon]);
        jsonResponse(['success' => true, 'added' => $stmt->rowCount() > 0]);
    } catch (PDOException $e) {
        error_log('Add favorite error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'db_error']);
    }
}

function removeFavorite(PDO $db, string $city, string $countryCode, float $lat, float $lon): void
{
    try {
        $stmt = $db->prepare(
            'DELETE FROM favorites WHERE city = ? AND country_code = ? AND latitude = ? AND longitude = ?'
        );
        $stmt->execute([$city, $countryCode, $lat, $lon]);
        jsonResponse(['success' => true, 'removed' => $stmt->rowCount() > 0]);
    } catch (PDOException $e) {
        error_log('Remove favorite error: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'db_error']);
    }
}
