<?php
/**
 * Load application config from local file (gitignored) or example template.
 */
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    $configFile = __DIR__ . '/config.example.php';
}
require_once $configFile;
