<?php
/**
 * PHPUnit test bootstrap
 *
 * Initializes the testing environment for the Dummy Data Generator plugin
 *
 * @package APP\plugins\generic\dummyDataGenerator\tests
 */

declare(strict_types=1);

// Load Composer autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    // Fallback to OJS autoloader if vendor not present
    // This path assumes plugin is installed in OJS directory structure
    $ojsAutoloadPath = __DIR__ . '/../../../../../../lib/pkp/lib/vendor/autoload.php';
    if (file_exists($ojsAutoloadPath)) {
        $autoloadPath = $ojsAutoloadPath;
    } else {
        // Try alternative OJS structure
        $ojsAutoloadPath = __DIR__ . '/../../../../lib/pkp/vendor/autoload.php';
        if (file_exists($ojsAutoloadPath)) {
            $autoloadPath = $ojsAutoloadPath;
        } else {
            fwrite(STDERR, "Composer autoloader not found. Run 'composer install' first.\n");
            exit(1);
        }
    }
}

require_once $autoloadPath;

// Define test environment constants
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

// Set timezone for consistent date handling
date_default_timezone_set('UTC');

// Load test base classes
require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/IntegrationTestCase.php';
require_once __DIR__ . '/E2ETestCase.php';

// Load mock helpers
require_once __DIR__ . '/Mocks/MockRepo.php';
require_once __DIR__ . '/Mocks/MockServices.php';
require_once __DIR__ . '/Mocks/MockClasses.php';
