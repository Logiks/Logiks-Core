<?php
/**
 * Database Layer Boot File
 * 
 * @package    Logiks
 * @subpackage Database
 */

if(!defined('ROOT')) exit('No direct script access allowed');

// Load compatibility layer first
if (file_exists(ROOT . 'api/compat/php8-compat.php')) {
    require_once ROOT . 'api/compat/php8-compat.php';
}

// Load database core files
require_once dirname(__FILE__) . '/database.php';
require_once dirname(__FILE__) . '/AbstractDBDriver.php';
require_once dirname(__FILE__) . '/AbstractQueryBuilder.php';
require_once dirname(__FILE__) . '/QueryBuilder.php';
require_once dirname(__FILE__) . '/QueryResult.php';

// Load MySQLi driver
if (file_exists(dirname(__FILE__) . '/MySQLiDriver.php')) {
    require_once dirname(__FILE__) . '/MySQLiDriver.php';
}

/**
 * Get database instance
 * 
 * @param string $dbKey Database connection key
 * @return object Database instance
 */
function _db($dbKey="app") {
	return Database::connect($dbKey);
}

/**
 * Execute database query
 * 
 * @param string $query SQL query
 * @param string $dbKey Database connection key
 * @return mixed Query result
 */
function _dbQuery($query, $dbKey="app") {
	$db = Database::connect($dbKey);
	if ($db) {
		return $db->query($query);
	}
	return false;
}

/**
 * Fetch single row from result
 * 
 * @param mixed $result Query result
 * @param string $dbKey Database connection key
 * @param string $format Fetch format: 'assoc', 'array', 'object'
 * @return mixed Row data or false
 */
function _dbFetch($result, $dbKey="app", $format="assoc") {
	$db = Database::connect($dbKey);
	if ($db && method_exists($db, 'fetchData')) {
		return $db->fetchData($result, $format);
	}
	return false;
}

/**
 * Fetch all rows from result
 * 
 * @param mixed $result Query result
 * @param string $dbKey Database connection key
 * @param string $format Fetch format: 'assoc', 'array', 'object'
 * @return array All rows
 */
function _dbData($result, $dbKey="app", $format="assoc") {
	$db = Database::connect($dbKey);
	if ($db && method_exists($db, 'fetchAll')) {
		return $db->fetchAll($result, $format);
	}
	return [];
}

/**
 * Free result memory
 * 
 * @param mixed $result Query result
 * @param string $dbKey Database connection key
 * @return bool Success status
 */
function _dbFree($result, $dbKey="app") {
	$db = Database::connect($dbKey);
	if ($db && method_exists($db, 'freeResult')) {
		return $db->freeResult($result);
	}
	return false;
}

/**
 * Escape string for database
 * 
 * @param string $string String to escape
 * @param string $dbKey Database connection key
 * @return string Escaped string
 */
function _dbEscape($string, $dbKey="app") {
	$db = Database::connect($dbKey);
	if ($db && method_exists($db, 'escapeString')) {
		return $db->escapeString($string);
	}
	// Fallback
	return addslashes($string);
}
