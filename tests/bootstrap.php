<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Initializes the testing environment for Logiks Framework.
 * 
 * @package    Logiks
 * @subpackage Tests
 * @author     Logiks Development Team
 * @version    4.2
 */

// Define ROOT constant
if (!defined('ROOT')) {
	define('ROOT', dirname(__DIR__) . '/');
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set test environment
$_ENV['TESTING'] = true;
$_ENV['APP_ENV'] = 'testing';

// Start output buffering (framework uses it)
if (ob_get_level() === 0) {
	ob_start();
}

// Load test configuration if exists
if (file_exists(ROOT . 'config/test.cfg')) {
	// Test-specific configuration
}

// Autoload composer dependencies if available
if (file_exists(ROOT . 'plugins/vendors/autoload.php')) {
	require_once ROOT . 'plugins/vendors/autoload.php';
}

// Load framework test case base class if it exists
if (file_exists(ROOT . 'api/libs/logikstestcase.inc')) {
	require_once ROOT . 'api/libs/logikstestcase.inc';
}

// Helper function to require framework files safely
function requireFrameworkFile($file) {
	$path = ROOT . $file;
	if (file_exists($path)) {
		require_once $path;
		return true;
	}
	return false;
}

// Load essential framework files for testing
requireFrameworkFile('api/compat/php8-compat.php');
requireFrameworkFile('api/commons.php');

echo "Logiks Framework Test Bootstrap Loaded\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "ROOT: " . ROOT . "\n\n";
