<?php
/*
 * This class is the base object that manipulates the database system using drivers and query system.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.1
 */
if(!defined('ROOT')) exit('No direct script access allowed');
	
class Database {
	private static $connections=array();
	
	private $instanceName=null;
	private $connectionParams=null;
	
	private $objDriver=null;
	private $objQBuilder=null;
	
	private $dbLink=null;
	
	//Static Utility Functions for connecting, closing, status checking and gettingConnection
	public static function connect($key,$params=array()) {
		$cfg=loadJSONConfig("db");
		if(!isset($cfg[$key])) {
			trigger_error("Database ERROR, Connection Configuration Could Not Be Found For {$key}");	
		} else {
			$params=$cfg[$key];
		}
		
		$db=new Database($key,$params);
		
		//Setup Cache

		//Setup Logging
		
		return $db;
	}
	//Get the instance name of the Query
	public static function getKeyForQuery($query) {
		if(is_a($query,"AbstractQueryBuilder")) {
			return $query->getInstanceName();
		} elseif(is_a($query,"QueryResult")) {
			return $query->getInstanceName();
		} else {
			return "app";
		}
	}
	//Checks if the particular named db connection is alive
	public static function isOpen($key) {
		if(isset(Database::$connections[$key])) {
			return Database::$connections[$key]->isAlive();
		}
		return false;
	}
	//Closes all the db connections
	public static function closeAll() {
		$a=true;
		foreach(Database::$connections as $key=>$dbcon) {
			$dbcon->close();
		}
		return $a;
	}
	//Gets the database object for the named db connection
	public static function dbConnection($key) {
		if(isset(Database::$connections[$key])) return Database::$connections[$key];
		else {
			trigger_error("Database ERROR, Connection Could Not Be Found For {$key}");
		}
	}
	
	//Primary Constructor
	protected function __construct($name,$params) {
		$driver=$params['driver'];
		$driverPath=dirname(__FILE__)."/$driver/";
		if(!is_dir($driverPath)) {
			trigger_error("Database ERROR, Driver Not Found For {$name} :: {$driver}");
		}
		
		$driverClass="{$driver}Driver";
		$qBuilderClass="{$driver}QueryBuilder";
		
		include_once $driverPath."{$driverClass}.inc";
		include_once $driverPath."{$qBuilderClass}.inc";
		
		$this->objDriver=new $driverClass($params);
		$this->objDriver->open($name);
		$this->dbLink=$this->objDriver->get_link();
		
		$this->objQBuilder=$qBuilderClass;
		
		$this->instanceName=$name;
		$this->connectionParams=$params;
		Database::$connections[$name]=$this;
		
		//printObj($this->connectionParams);
	}
	
	//Closes the current connection
	public function close() {
		if($this->objDriver->isOpen()) return $this->objDriver->close();
		return true;
	}
	//Checks if the current connection is open
	public function isAlive() {
		return $this->objDriver->isOpen();
	}
	
	//Gets the php native object for the current connection
	public function dbLink() {return $this->dbLink;}
	//Gets the driver,database name for the current connection
	public function dbParams($key) {
		if(isset($this->connectionParams[$key]))
			return $this->connectionParams[$key];
		else
			return "";
	}
	//Gets the querybuilder object for the database.
	public function queryBuilder() {
		$qBuilder = $this->objQBuilder;
		return $qBuilder::create($this);
	}
	//Gets the name for this DB Instance
	public function getInstanceName() {
		return $this->instanceName;
	}
	
	//Creates and returns a PHP PDO Object for the current connection
	public function toPDO($dbuser=null,$dbpwd=null) {
		//$dsn = 'mysql:dbname=testdb;host=127.0.0.1;port=3333';
		//$dsn=$this->driver.":dbname=".$this->dbName.";host=".$this->dbhost;
		$dsn=$this->objDriver->getDSN();
		
		if($dbuser==null) $dbuser=$this->connectionParams['user'];
		if($dbpwd==null) $dbpwd=$this->connectionParams['pwd'];

		try {
			$dbh = new PDO($dsn, $dbuser, $dbpwd);
			return $dbh;
		} catch (PDOException $e) {
			trigger_error("Database ERROR, PDO Creation Failed For Driver Not ".$this->instanceName." :: ". $e->getMessage());
		}
	}
	
	//All Query Functions
	public function executeQuery($sql,$keyName=null) {
		if(!$this->objDriver->isAllowedSQL()) {
			if(!is_a($sql,"AbstractQueryBuilder")) {
				trigger_error("Database ERROR, Direct SQL Queries are not allowed for this database.");
			}
		}
		if($keyName==null && is_a($sql,"AbstractQueryBuilder")) {
			$keyName=$sql->getInstanceName();
		}
		$result=$this->objDriver->runQuery($sql);
		if($result) return new QueryResult($keyName,$result);
		else return false;
	}
	public function executeCommandQuery($sql) {
		return $this->objDriver->runCommandQuery($sql);
	}
	public function free($result) {
		if(is_a($result,"QueryResult")) {
			$rs=$result->getResult();
			if($this->objDriver->freeResult($rs)) {
				$result->purge();
				return true;
			} else {
				return false;
			}
		} else {
			return $this->objDriver->freeResult($result);
		}
	}
	
	public function isTablesAvailable($table) {
		$tablesArr=$this->objDriver->get_tableList();
		if(is_array($table)) {
			$table=array_flip($table);
			foreach($table as $a=>$b) {
				$table[$a]=in_array($a,$tablesArr);
			}
			return $table;
		} else {
			return in_array($table,$tablesArr);
		}
	}
	
	public function fetchColumn($table,$column,$where=null,$orderby=null,$single=false) {
		$sql=$this->_selectQ($table,$column,$where,$orderby);
		$res=$this->executeQuery($sql);
		//$data=$this->fetchAllData($res);
		$ans=array();
		if($res) {
			if($single) {
				$record=$this->fetchData($res);
				$ans=$record[$column];
			} else {
				while($record=$this->fetchData($res)){
					array_push($ans,$record[$column]);
				}
			}
			$this->free($res);
		}
		return $ans;
	}
	
	//Special Functions Encapsulating some queries directly
	public function cloneRow($table,$where,$autoIncrementColumn='id') {
		$cols=$this->get_columnList($table);
		if(is_array($autoIncrementColumn)) {
			foreach($cols as $a=>$b) {
				if(in_array($b,$autoIncrementColumn)) {
					unset($cols[$a]);
				}
			}
		} else {
			if(($key = array_search($autoIncrementColumn, $cols)) !== false) {
			    unset($cols[$key]);
			}
		}
		$cols=implode(", ",$cols);
		$sqlSelect=$this->_selectQ($table,$cols,$where);
		$sql=$this->_insertQ($table,$cols,$sqlSelect);
		
		$sql=$sql->_sql();
		$sql=str_replace(") VALUES (",") (",$sql);
		
		return $this->executeQuery($sql);
	}

	public function __call($method, $arguments) {
		if(strpos($method,"_")===0) {
			$qbuilder=$this->objQBuilder;
			return call_user_func_array(array($qbuilder::create($this), $method), $arguments);
		} elseif(strpos($method,"get_")===0) {
			return call_user_func_array(array($this->objDriver, $method), $arguments);
		} elseif(strpos($method,"fetch")===0) {
			return call_user_func_array(array($this->objDriver, $method), $arguments);
		} else {
			trigger_error("Database ERROR, [$method] Method Not Found [".$this->dbParams('driver')."]");
		}
	}
}
?>
