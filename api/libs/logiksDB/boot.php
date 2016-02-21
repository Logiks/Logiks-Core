<?php
/*
 * This file contains the startup sequences for the Database system of Logiks.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.1
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/database.php";
include_once dirname(__FILE__)."/AbstractDBDriver.php";
include_once dirname(__FILE__)."/AbstractQueryBuilder.php";
include_once dirname(__FILE__)."/QueryBuilder.php";
include_once dirname(__FILE__)."/QueryResult.php";

if(!function_exists("_db")) {
	function _db($dbKey="app") {
		if($dbKey===true) $dbKey="core";
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey);
		} else {
			return Database::connect($dbKey);
		}
	}
	function _dbQuery($query,$dbKey=null) {
		if($dbKey===true) $dbKey="core";
		elseif($dbKey==null) $dbKey=Database::getKeyForQuery($query);
		
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey)->executeQuery($query,$dbKey);
		} else {
			return Database::connect($dbKey)->executeQuery($query,$dbKey);
		}
	}
	function _dbFetch($result,$dbKey=null,$format="assoc") {
		if($dbKey===true) $dbKey="core";
		elseif($dbKey==null) $dbKey=Database::getKeyForQuery($result);
		
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey)->fetchData($result,$format);
		} else {
			return false;
		}
	}
	function _dbData($result,$dbKey=null,$format="assoc") {
		if($dbKey===true) $dbKey="core";
		elseif($dbKey==null) $dbKey=Database::getKeyForQuery($result);
		
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey)->fetchAllData($result,$format);
		} else {
			return false;
		}
	}
	function _dbFree($result,$dbKey=null) {
		if($dbKey===true) $dbKey="core";
		elseif($dbKey==null) $dbKey=Database::getKeyForQuery($result);
		
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey)->free($result);
		} else {
			return false;
		}
	}
	function _dbTable($tblName,$dbKey=null) {
		if($dbKey===true) $dbKey="core";
		elseif($dbKey==null) $dbKey=Database::getKeyForQuery($query);
		
		if(Database::isOpen($dbKey)) {
			return Database::dbConnection($dbKey)->get_Table($tblName);
		} else {
			return Database::connect($dbKey)->get_Table($tblName);
		}
	}
}
?>
