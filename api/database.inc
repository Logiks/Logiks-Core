<?php
//This class is used for database functionalities
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "libs/db/QueryGenerator.inc";

class Database {	
	private $link;
	private $driver=null;
	private $dbName=null;
	private $dbHost=null;
	
	private $logConnection=false;
	private $delete_hack=true;
	private $readOnly=false;
	
	private $ddl=array("CREATE","ALTER","DROP","TRUNCATE","COMMENT","GRANT","REVOKE");
	private $dml=array("INSERT","UPDATE","DELETE","CALL");
	private $blockedStmnts=array();
	
	public function __construct($driver=null) {
		if($driver==null) $this->driver=$GLOBALS['DBCONFIG']["DB_DRIVER"];
		else $this->driver=$driver;
	}
	public function __destruct() {
		$this->close();
	}
	public function logConnect($dbuser=null,$dbpwd=null,$dbhost=null,$dbName=null) {
		$this->logConnection=true;
		
		if($dbuser==null) $dbuser=$GLOBALS['DBCONFIG']["DB_USER"];
		if($dbpwd==null) $dbpwd=$GLOBALS['DBCONFIG']["DB_PASSWORD"];
		if($dbhost==null) $this->dbhost=$GLOBALS['DBCONFIG']["DB_HOST"]; else $this->dbhost=$dbhost;
		if($dbName==null) $this->dbName=$GLOBALS['DBCONFIG']["DB_DATABASE"]; else $this->dbName=$dbName;
		
		if($GLOBALS['DBCONFIG']["DB_READ_ONLY"]=="true") $this->readOnly=true;
		
		$this->blockedStmnts=array();
		
		if($this->isOpen()) return true;
		$this->link = mysql_connect($this->dbhost,$dbuser,$dbpwd,$this->dbName);
		if (!$this->link) {
			return false;
		}
		
		$db = mysql_select_db($this->dbName);
		if (!$db) {
			return false;
		}
		
		return true;
	}
	public function connect($dbuser=null,$dbpwd=null,$dbhost=null,$dbName=null) {
		if($dbuser==null) $dbuser=$GLOBALS['DBCONFIG']["DB_USER"];
		if($dbpwd==null) $dbpwd=$GLOBALS['DBCONFIG']["DB_PASSWORD"];
		if($dbhost==null) $this->dbhost=$GLOBALS['DBCONFIG']["DB_HOST"]; else $this->dbhost=$dbhost;
		if($dbName==null) $this->dbName=$GLOBALS['DBCONFIG']["DB_DATABASE"]; else $this->dbName=$dbName;
		
		if($GLOBALS['DBCONFIG']["DB_READ_ONLY"]=="true") $this->readOnly=true;
		
		if(isset($GLOBALS['DBCONFIG']["BLOCK_STATEMENTS"]))
			$this->blockedStmnts=explode(",",strtoupper($GLOBALS['DBCONFIG']["BLOCK_STATEMENTS"]));
		
		if($this->isOpen()) return true;
		$this->link = mysql_connect($this->dbhost,$dbuser,$dbpwd,$this->dbName) or die("MySQL DB Connection Error");//.mysql_error()
		if (!$this->link) {
			return false;
		}
		$db = mysql_select_db($this->dbName) or die("MySQL DB Select Error");
		if (!$db) {
			return false;
		}
		
		return true;
	}
	public function close() {
		if($this->link) {
			mysql_close($this->link);
		}		
		$this->link=null;
	}
	public function isOpen() {
		if($this->link!=null) return true;
		else return false;
	}
	
	public function getLink() {return $this->link;}
	public function getdriver() {return $this->driver;}
	public function getdbName() {return $this->dbName;}
	public function getdbHost() {return $this->dbHost;}
	
	public function getPDO($dbuser=null,$dbpwd=null) {
		if($dbuser==null) $dbuser=$GLOBALS['DBCONFIG']["DB_USER"];
		if($dbpwd==null) $dbpwd=$GLOBALS['DBCONFIG']["DB_PASSWORD"];
		
		//$dsn = 'mysql:dbname=testdb;host=127.0.0.1;port=3333';
		$dsn=$this->driver.":dbname=".$this->dbName.";host=".$this->dbhost;
		try {
			$dbh = new PDO($dsn, $dbuser, $dbpwd);
			return $dbh;
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
		echo null;
	}
	
	public static function getTable($tblName) {
		if(isset($GLOBALS['DBCONFIG']["DB_PREFIX"]) && strlen($GLOBALS['DBCONFIG']["DB_PREFIX"])>0) {
			$tblName=$GLOBALS['DBCONFIG']["DB_PREFIX"]."_".$tblName;
		}
		if(isset($GLOBALS['DBCONFIG']["DB_SUFFIX"]) && strlen($GLOBALS['DBCONFIG']["DB_SUFFIX"])>0) {
			$tblName=$tblName."_".$GLOBALS['DBCONFIG']["DB_SUFFIX"];
		}
		return $tblName;
	}
	public static function getSysTable($tblName) {
		if(isset($GLOBALS['DBCONFIG']["DB_SYSTEM"]) && strlen($GLOBALS['DBCONFIG']["DB_SYSTEM"])>0) {
			$tblName=$GLOBALS['DBCONFIG']["DB_SYSTEM"]."_".$tblName;
		}
		return $tblName;
	}
	public static function getAppsTable($tblName) {
		if(isset($GLOBALS['DBCONFIG']["DB_APPS"]) && strlen($GLOBALS['DBCONFIG']["DB_APPS"])>0) {
			$tblName=$GLOBALS['DBCONFIG']["DB_APPS"]."_".$tblName;
		}
		return $tblName;
	}
	
	//All Query Functions
	public function executeQuery($sql) {
		if($this->logConnection) {
			$r=mysql_query($sql, $this->link);// or die(mysql_error());
			return $r;
		} else {
			$q=explode(" ",trim($sql));
			$q=strtoupper($q[0]);
			if($this->readOnly && strlen($q)>0 && (in_array($q,$this->ddl) || in_array($q,$this->dml))) {
				return false;
			}
			if(in_array($q,$this->blockedStmnts)) {
				return false;
			}
			$sql = QueryGenerator::prep_query($sql);
			
			if(class_exists("LogDB")) {
				LogDB::singleton()->log_SQLEvent($sql,null,$this->getdbName());
			}
			if(MASTER_DEBUG_MODE=="true") {
				$r=mysql_query($sql, $this->link);// or die(mysql_error($this->link));
				return $r;
			} else {
				$r=mysql_query($sql, $this->link);
				return $r;
			}
		}
	}
	public function executeCommandQuery($sql) {
		$q=explode(" ",trim($sql));
		$q=strtoupper($q[0]);
		if($this->readOnly && strlen($q)>0 && (in_array($q,$this->ddl) || in_array($q,$this->dml))) {
			return false;
		}
		if(in_array($q,$this->blockedStmnts)) {
			return false;
		}
		$sql = QueryGenerator::prep_query($sql);
		
		$r=mysql_query($sql, $this->link) or die(mysql_error($this->link));
		
		if($r) return true;
		else return false;
	}
	public function freeResult($result) {
		if($result) {
			@mysql_free_result($result);
		}
	}
	
	//All Info Functions
	public function recordCount($result) {
		if($result) {
			return mysql_num_rows($result);
		} else {
			return 0;
		}
	}
	public function maxInCol($table,$colname) {
		$sql="SELECT MAX($colname) as maxvalue FROM $table";
		$result=mysql_query($sql,$this->link);
		if (!$result) {
			return null;
		}
		$maxvalue=0;
		while($record=mysql_fetch_array($result)) {
			$maxvalue=$record[0]["maxvalue"];
		}
		return $maxvalue;
	}
	public function columnCount($result) {
		if($result) {
			return mysql_num_fields($result);
		} else {
			return 0;
		}
	}
	public function affected_rows() {
		return mysql_affected_rows($this->link);
	}
	public function insert_id() {
		return mysql_insert_id($this->link);
	}
	public function getTableList() {
		$sql=$this->_list_tablesQ($this->dbName);
		$result=mysql_query($sql,$this->link);
		if (!$result) {
			return null;
		}
		$arr=array();
		while($record=mysql_fetch_array($result)) {
			$p=$record[0];
			array_push($arr,$p);
		}
		return $arr;
	}
	
	public function getColumnList($tbl) {
		$sql="SHOW COLUMNS FROM $tbl";
		$result=mysql_query($sql,$this->link);
		$arr=array();
		if($result) {
			while($row = mysql_fetch_array($result,MYSQL_NUM)) {
				$arr[$row[0]]=$row;
			}
		}
		return $arr;
	}
	
	public function getTableInfo($table) {
		$query="SELECT * from $table limit 0,1";
		$result=mysql_query($query,$this->link);
		if (!$result) {
			//die('Query failed: ' . mysql_error());
			return null;
		}
		$i=0;
		$colname=Array();
		$coltype=Array();
		while($i<mysql_num_fields($result)) {
			$meta=mysql_fetch_field($result, $i);
			$colname[$i]=$meta->name;
			$coltype[$i]=$meta->type;
			$i++;
		}
		mysql_free_result($result);
		
		$data=Array();
		$data[0]=$colname;
		$data[1]=$coltype;
		return $data;
	}
	
	public function getPrimaryKey($table) {
		$query="SHOW KEYS FROM $table WHERE Key_name='PRIMARY'";
		$result=mysql_query($query,$this->link);
		if (!$result) {
			//die('Query failed: ' . mysql_error());
			return null;
		}
		$record=mysql_fetch_assoc($result);
		$pKey=$record['Column_name'];
		
		if(strlen($pKey)<=0) {
			$query="SELECT * FROM $table limit 0,1";
			$result=mysql_query($query,$this->link);
			
			$meta = mysql_fetch_field($result, 0);
			$pKey=$meta->name;
		}
		
		return $pKey;
	}
	
	public function isTablesAvailable($tables) {
		if(is_array($tables)) {
			$sql=$this->_list_tablesQ($this->dbName);
			$result=mysql_query($sql,$this->link);
			if (!$result) {
				return null;
			}
			$out=array();
			$arr=array();
			while($record=mysql_fetch_array($result)) {
				$p=$record[0];
				array_push($arr,$p);
			}
			foreach($tables as $a=>$b) {
				if(in_array($b,$arr,true)) {
					$out[$b]=1;
				} else {
					$out[$b]=0;
				}
			}
			return $out;
		} else {
			$sql=$this->_list_tablesQ($this->dbName);
			$result=mysql_query($sql,$this->link);
			if (!$result) {
				return null;
			}
			while($record=mysql_fetch_array($result)) {
				$p=$record[0];
				if($p==$tables) return true;
			}
		}
		return false;
	}
	
	public function fetchField($resultSet,$offSet=0) {
		return mysql_fetch_field($resultSet,$offSet);
	}
	
	public function fetchData($resultSet,$format="assoc") {
		$format=strtolower($format);
		if($format=="array") {
			return mysql_fetch_array($resultSet);
		} else if($format=="assoc") {
			return mysql_fetch_assoc($resultSet);
		} else if($format=="field") {
			return mysql_fetch_field($resultSet);
		} else if($format=="lengths") {
			return mysql_fetch_lengths($resultSet);
		} else if($format=="object") {
			return mysql_fetch_object($resultSet);
		} else if($format=="row") {
			return mysql_fetch_row($resultSet);
		} else {
			//trigger_error("Unknown Data Format Requested : $format");
		}
		return null;
	}
	public function fetchAllData($result,$format="assoc") {
		if($result==null || !$result) return array();
		$out=array();
		while($r=$this->fetchData($result,$format)) {
			$out[sizeOf($out)]=$r;
		}
		return $out;
	}
	
	public function cloneRow($table,$where) {
		$cols=$this->getColumnList($table);
		$colArr=array_keys($cols);
		unset($colArr[0]);
		$colStr=implode(", ",$colArr);
		$sql="INSERT INTO $table ($colStr) (SELECT $colStr FROM $table WHERE $where)";
		//exit($sql);
		return $this->executeQuery($sql);
	}
	
	public function cloneRowById($table,$idCol,$idColValue) {
		$cols=$this->getColumnList($table);
		unset($cols[$idCol]);
		
		$colArr=array_keys($cols);		
		$colStr=implode(", ",$colArr);
		$sql="INSERT INTO $table ($colStr) (SELECT $colStr FROM $table WHERE $idCol=$idColValue)";
		$a=$this->executeQuery($sql);
		//exit($this->getError());
		return $a;
	}
	
	public function getErrorNo() {
		return mysql_errno($this->link);
	}
	public function getError() {
		return mysql_error($this->link);
	}
	
	public function getSchema($dropIfExists=true, $tables=null) {
		$tblList=$this->getTableList();
		$s="";
		if($tables==null) {
			foreach($tblList as $a) {
				$r=mysql_query('SHOW CREATE TABLE '.$a,$this->link);
				if($r) {
					$da=$this->fetchAllData($r);
					$this->freeResult($r);
					if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
					$sqlCreate=$da[0]['Create Table'];
					$sqlCreate = str_replace("\n","",$sqlCreate);
					$s.="{$sqlCreate};\n\n";
				}
			}
		} else {
			if(!is_array($tables)) $tables=explode(",",$tables);
			foreach($tblList as $a) {
				if(in_array($a,$tables)) {
					$r=mysql_query('SHOW CREATE TABLE '.$a,$this->link);
					if($r) {
						$da=$this->fetchAllData($r);
						$this->freeResult($r);
						if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
						$sqlCreate=$da[0]['Create Table'];
						$sqlCreate = str_replace("\n","",$sqlCreate);
						$s.="{$sqlCreate};\n\n";
					}
				}
			}
		}
		return $s;
	}
	
	public function getTableInserts($table) {
		$result =  $this->executeQuery('SELECT * FROM '.$table);
		$num_fields = $this->columnCount($result);
		
		$s="";
		for ($i = 0; $i < $num_fields; $i++) {
			while($row = $this->fetchData($result,"array")) {
				$s.= 'INSERT INTO '.$table.' VALUES (';        
				for($j=0; $j<$num_fields; $j++) {                
					$row[$j] = addslashes($row[$j]);          
					$row[$j] = str_replace("\n","\\"."n",$row[$j]);
					if(isset($row[$j])) { $s.= '"'.$row[$j].'"' ; } else { $s.= '""'; }
					if($j<($num_fields-1)) { $s.= ','; }
				}
				$s.= ");\n";
			}
		}
		return $s;
	}
	
	public function __call($name, $arguments) {
		if(strpos($name,"_")==0) {
			$n=sizeOf($arguments);
			if($n==0) return call_user_func("QueryGenerator::".$name);
			else if($n==1) return call_user_func("QueryGenerator::".$name,$arguments[0]);
			else if($n==2) return call_user_func("QueryGenerator::".$name,$arguments[0],$arguments[1]);
			else if($n==3) return call_user_func("QueryGenerator::".$name,$arguments[0],$arguments[1],$arguments[2]);
			else if($n==4) return call_user_func("QueryGenerator::".$name,$arguments[0],$arguments[1],$arguments[2],$arguments[3]);
			else if($n==5) return call_user_func("QueryGenerator::".$name,$arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4]);
			else if($n==6) return call_user_func("QueryGenerator::".$name,$arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4],$arguments[5]);
			else return call_user_func("QueryGenerator::".$name);
		} else {
			return "Function Not Found";
		}
	}
}
if(!function_exists("_clean")) {
	function _clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
}
?>
