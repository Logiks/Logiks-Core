<?php
/**
 * PHP 8.x Compatibility Layer for Logiks Framework
 * 
 * This file provides backward compatibility for features removed in PHP 8.x
 * while maintaining support for PHP 7.4+
 * 
 * @package    Logiks
 * @subpackage Compatibility
 * @author     Logiks Development Team
 * @version    4.2
 * @since      PHP 7.4
 */

if(!defined('ROOT')) exit('No direct script access allowed');

// ============================================================================
// Magic Quotes Compatibility (Removed in PHP 7.4)
// ============================================================================

if (!function_exists('get_magic_quotes_gpc')) {
    /**
     * Polyfill for get_magic_quotes_gpc()
     * Always returns false as magic quotes are permanently disabled
     * 
     * @return bool Always returns false
     */
    function get_magic_quotes_gpc() {
        return false;
    }
}

if (!function_exists('get_magic_quotes_runtime')) {
    /**
     * Polyfill for get_magic_quotes_runtime()
     * Always returns false as magic quotes are permanently disabled
     * 
     * @return bool Always returns false
     */
    function get_magic_quotes_runtime() {
        return false;
    }
}

// ============================================================================
// MYSQL_* Constants Compatibility (Removed in PHP 7.0)
// ============================================================================

// Fetch type constants
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);  // 1
}

if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', MYSQLI_NUM);      // 2
}

if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);    // 3
}

// MySQL field type constants
if (!defined('MYSQL_TYPE_DECIMAL')) {
    define('MYSQL_TYPE_DECIMAL', MYSQLI_TYPE_DECIMAL);
    define('MYSQL_TYPE_TINY', MYSQLI_TYPE_TINY);
    define('MYSQL_TYPE_SHORT', MYSQLI_TYPE_SHORT);
    define('MYSQL_TYPE_LONG', MYSQLI_TYPE_LONG);
    define('MYSQL_TYPE_FLOAT', MYSQLI_TYPE_FLOAT);
    define('MYSQL_TYPE_DOUBLE', MYSQLI_TYPE_DOUBLE);
    define('MYSQL_TYPE_TIMESTAMP', MYSQLI_TYPE_TIMESTAMP);
    define('MYSQL_TYPE_LONGLONG', MYSQLI_TYPE_LONGLONG);
    define('MYSQL_TYPE_INT24', MYSQLI_TYPE_INT24);
    define('MYSQL_TYPE_DATE', MYSQLI_TYPE_DATE);
    define('MYSQL_TYPE_TIME', MYSQLI_TYPE_TIME);
    define('MYSQL_TYPE_DATETIME', MYSQLI_TYPE_DATETIME);
    define('MYSQL_TYPE_YEAR', MYSQLI_TYPE_YEAR);
    define('MYSQL_TYPE_NEWDATE', MYSQLI_TYPE_NEWDATE);
    define('MYSQL_TYPE_VARCHAR', MYSQLI_TYPE_VARCHAR);
    define('MYSQL_TYPE_BIT', MYSQLI_TYPE_BIT);
    define('MYSQL_TYPE_JSON', MYSQLI_TYPE_JSON);
    define('MYSQL_TYPE_NEWDECIMAL', MYSQLI_TYPE_NEWDECIMAL);
    define('MYSQL_TYPE_ENUM', MYSQLI_TYPE_ENUM);
    define('MYSQL_TYPE_SET', MYSQLI_TYPE_SET);
    define('MYSQL_TYPE_TINY_BLOB', MYSQLI_TYPE_TINY_BLOB);
    define('MYSQL_TYPE_MEDIUM_BLOB', MYSQLI_TYPE_MEDIUM_BLOB);
    define('MYSQL_TYPE_LONG_BLOB', MYSQLI_TYPE_LONG_BLOB);
    define('MYSQL_TYPE_BLOB', MYSQLI_TYPE_BLOB);
    define('MYSQL_TYPE_VAR_STRING', MYSQLI_TYPE_VAR_STRING);
    define('MYSQL_TYPE_STRING', MYSQLI_TYPE_STRING);
    define('MYSQL_TYPE_GEOMETRY', MYSQLI_TYPE_GEOMETRY);
}

// ============================================================================
// Enhanced Error Handling for PHP 8.x
// ============================================================================

/**
 * Safe array access with default value
 * Prevents undefined array key warnings in PHP 8.x
 * 
 * @param array $array The array to access
 * @param mixed $key The key to retrieve
 * @param mixed $default Default value if key doesn't exist
 * @return mixed The value or default
 */
if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null) {
        if (!is_array($array)) {
            return $default;
        }
        return $array[$key] ?? $default;
    }
}

/**
 * Safe nested array access with dot notation
 * Example: array_get_nested($arr, 'user.profile.name', 'Guest')
 * 
 * @param array $array The array to access
 * @param string $path Dot-separated path
 * @param mixed $default Default value
 * @return mixed The value or default
 */
if (!function_exists('array_get_nested')) {
    function array_get_nested($array, $path, $default = null) {
        if (!is_array($array)) {
            return $default;
        }
        
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
}

// ============================================================================
// PHP 8.x Compatibility Helpers
// ============================================================================

/**
 * Check if running on PHP 8.0 or higher
 * 
 * @return bool
 */
if (!function_exists('is_php8')) {
    function is_php8() {
        return version_compare(PHP_VERSION, '8.0.0', '>=');
    }
}

/**
 * Check if running on PHP 8.1 or higher
 * 
 * @return bool
 */
if (!function_exists('is_php81')) {
    function is_php81() {
        return version_compare(PHP_VERSION, '8.1.0', '>=');
    }
}

/**
 * Check if running on PHP 8.2 or higher
 * 
 * @return bool
 */
if (!function_exists('is_php82')) {
    function is_php82() {
        return version_compare(PHP_VERSION, '8.2.0', '>=');
    }
}

/**
 * Check if running on PHP 8.3 or higher
 * 
 * @return bool
 */
if (!function_exists('is_php83')) {
    function is_php83() {
        return version_compare(PHP_VERSION, '8.3.0', '>=');
    }
}

/**
 * Check if running on PHP 8.4 or higher
 * 
 * @return bool
 */
if (!function_exists('is_php84')) {
    function is_php84() {
        return version_compare(PHP_VERSION, '8.4.0', '>=');
    }
}

// ============================================================================
// String Function Enhancements
// ============================================================================

/**
 * Safe string conversion (handles null in PHP 8.1+)
 * 
 * @param mixed $value Value to convert to string
 * @return string
 */
if (!function_exists('safe_strval')) {
    function safe_strval($value) {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value) || is_object($value)) {
            return serialize($value);
        }
        return (string)$value;
    }
}

/**
 * Safe strlen that handles null (PHP 8.1+ deprecation)
 * 
 * @param mixed $string String to measure
 * @return int Length of string
 */
if (!function_exists('safe_strlen')) {
    function safe_strlen($string) {
        if ($string === null || $string === false) {
            return 0;
        }
        return strlen((string)$string);
    }
}

// ============================================================================
// Backward Compatibility Definitions
// ============================================================================

// Define PHP version constants for conditional code
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

// Mark compatibility layer as loaded
if (!defined('LOGIKS_PHP8_COMPAT_LOADED')) {
    define('LOGIKS_PHP8_COMPAT_LOADED', true);
}

// Log compatibility layer loading in debug mode
if (defined('MASTER_DEBUG_MODE') && MASTER_DEBUG_MODE) {
    error_log("Logiks PHP 8.x Compatibility Layer loaded for PHP " . PHP_VERSION);
}
