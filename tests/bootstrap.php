<?php
/**
 * @file tests/bootstrap.php
 *
 * Copyright (c) 2026 Munir Abbasi
 * Distributed under the GNU GPL v3.
 *
 * @brief Test bootstrap for Dummy Data Generator plugin
 *
 * @author Munir Abbasi <munir@syntaxhouse.com>
 * @since 1.1.0
 */

declare(strict_types=1);

// Load Composer autoloader
$autoloaderPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',   // When installed in OJS
    __DIR__ . '/../../../../autoload.php', // When installed deeper in OJS
];

$autoloaded = false;
foreach ($autoloaderPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloaded = true;
        break;
    }
}

if (!$autoloaded) {
    die("Error: Composer autoloader not found. Run 'composer install --dev' first.\n");
}

// Define standalone testing constants if not already defined
// PKP_TEST_ENV is defined by OJS itself when running in its test environment
// We define DUMMY_DATA_STANDALONE_TEST to indicate standalone (non-OJS) test mode
if (!defined('DUMMY_DATA_STANDALONE_TEST') && !defined('PKP_TEST_ENV')) {
    define('DUMMY_DATA_STANDALONE_TEST', true);
}

if (defined('DUMMY_DATA_STANDALONE_TEST') && DUMMY_DATA_STANDALONE_TEST) {
    require_once __DIR__ . '/Stubs/OJSStubs.php';
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', 'http://localhost');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost');
}
