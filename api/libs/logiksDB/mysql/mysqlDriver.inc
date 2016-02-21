<?php
/*
 * Driver For MySQL
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */

class mysqlDriver extends AbstractDBDriver {
	
	private $delete_hack=false;
	
	public function __construct($params) {
		parent::__construct($params);
	}
	public function open($keyName) {
		if(!parent::open($keyName)) {
			trigger_error("Database ERROR, Database Is Already Open {$keyName}");
			return false;
		}
		
		$this->link = mysql_connect($this->dbParams['host'],$this->dbParams['user'],$this->dbParams['pwd'],$this->dbParams['database']) or die("MySQL DB Connection Error [{$keyName}] Connection");
		
		if (!$this->link) {
			trigger_error("Database ERROR, Connection Error For {$keyName}");
		}
		
		$db = mysql_select_db($this->dbParams['database'],$this->link) or die("MySQL Database Selection Error For [{$keyName}] Connection");
		
		if(!$db) {
			trigger_error("Database ERROR, Database Selection Error For {$keyName}");
		}
		
		return $this;
	}
	public function close() {
		if($this->link) {
			mysql_close($this->link);
		}
		$this->link=null;
	}
	
	public function runQuery($sql) {
		if(!parent::runQuery($sql)) {
			trigger_error("Database ERROR, Security Threat Query Execution Hash Mismatch.");
			return false;
		}
		
		if(is_a($sql,"AbstractQueryBuilder")) {
			$q=$sql->_SQL();
		} else {
			$q=$sql;
		}
		$qArr=explode(" ",trim($q));
		$qArr=strtoupper($qArr[0]);
		
		if(!$this->checkQuery($qArr)) {
			trigger_error("Database ERROR, Restricted Query [{$qArr}] for ".$this->instanceName,E_USER_WARNING);
		}
		
		$r=mysql_query($q, $this->link);
		return $r;
	}
	public function runCommandQuery($q) {
		$r=mysql_query($q, $this->link);
		return $r;
	}
	public function freeResult($result) {
		if($result) {
			return @mysql_free_result($result);
		}
		return false;
	}
	
	//Result based functions
	public function fetchData($result,$format="assoc") {
		$result=parent::fetchData($result);
		$format=strtolower($format);
		if($format=="array") {
			return mysql_fetch_array($result,MYSQL_BOTH);
		} else if($format=="assoc") {
			return mysql_fetch_assoc($result);
		} else if($format=="field") {
			return mysql_fetch_field($result);
		} else if($format=="lengths") {
			return mysql_fetch_lengths($result);
		} else if($format=="object") {
			return mysql_fetch_object($result);
		} else if($format=="row") {
			return mysql_fetch_row($result);
		} else {
			trigger_error("Database ERROR, Unknown Data Format Requested : $format");
		}
		return null;
	}
	
	public function fetchHeaders($result) {
		$cols=array();
		$nx=mysql_num_fields($result);
		for ( $i = 0; $i < $nx; $i++ ) {
            $cols[] = mysql_field_name($result, $i);
        }
		
        return $cols;
	}
	public function fetchFields($result) {
		$cols=array();
		$nx=mysql_num_fields($result);
		for ( $i = 0; $i < $nx; $i++ ) {
            $cols[mysql_field_name($result, $i)] = array(
					"name"=>mysql_field_name($result, $i),
					"type"=>mysql_field_type($result, $i),
					"length"=>mysql_field_len($result, $i),
					"flags"=>mysql_field_flags($result, $i),
				);
        }
        return $cols;
	}
	public function get_fieldType($result, $colIndex) {
		return mysql_field_type($result, $colIndex);
	}
	public function get_recordCount($result) {
		if($result) {
			return mysql_num_rows($result);
		} else {
			return 0;
		}
	}
	public function get_columnCount($result) {
		if($result) {
			return mysql_num_fields($result);
		} else {
			return 0;
		}
	}
	
	//Link/Resource# Based Function
	public function get_errorNo() {return mysql_errno($this->link);}
	public function get_error() {return mysql_error($this->link);}
	public function get_affectedRows() {return mysql_affected_rows($this->link);}
	public function get_insertID() {return mysql_insert_id($this->link);}
	
	//All mainline functions and special queries
	public function get_tableList() {
		$sql="SHOW TABLES";
		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$arr=array();
		while($record=mysql_fetch_array($result)) {
			$p=$record[0];
			array_push($arr,$p);
		}
		$this->freeResult($result);
		return $arr;
	}
	public function get_columnList($table,$nameOnly=true) {
		$sql="SHOW COLUMNS FROM $table";
		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$arr=array();
		if($result) {
			if($nameOnly) {
				while($row = mysql_fetch_array($result,MYSQL_NUM)) {
					$arr[]=$row[0];
				}
			} else {
				while($row = mysql_fetch_array($result,MYSQL_NUM)) {
					$arr[$row[0]]=$row;
				}
			}
		}
		$this->freeResult($result);
		return $arr;
	}
	public function get_primaryKey($table) {
		$sql="SHOW KEYS FROM $table WHERE Key_name='PRIMARY'";
		$result=$this->runQuery($sql);
		if (!$result) {
			return null;
		}
		$record=mysql_fetch_assoc($result);
		$this->freeResult($result);
		
		$pKey=$record['Column_name'];
		if(strlen($pKey)<=0) {
			$query="SELECT * FROM $table limit 0,1";
			$result=mysql_query($query,$this->link);

			$meta = mysql_fetch_field($result, 0);
			$pKey=$meta->name;
		}

		return $pKey;
	}
	public function get_allkeys($table) {
		$sql="SHOW KEYS FROM $table";
		$result=$this->runQuery($sql);
		if (!$result) {
			return null;
		}
		$arr=array();
		$cols=$this->fetchHeaders($result);
		while($row = mysql_fetch_array($result,MYSQL_NUM)) {
			$fRow=array();
			foreach($row as $a=>$b) {
				$fRow[$cols[$a]]=$b;
			}
			$arr[]=$fRow;
		}
		$this->freeResult($result);
		return $arr;
	}


	//Schema related functions
	public function get_schema($tables=null,$dropIfExists=true) {
		$tblList=$this->get_tableList();
		$s="";
		if($tables==null) {
			foreach($tblList as $a) {
				$r=$this->runQuery('SHOW CREATE TABLE '.$a);
				if($r) {
					$da=$this->fetchAllData($r);
					$this->freeResult($r);
					if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
					$sqlCreate=$da[0]['Create Table'];
					//$sqlCreate = str_replace("\n","",$sqlCreate);
					$s.="{$sqlCreate};\n\n";
				}
			}
		} else {
			if(!is_array($tables)) $tables=explode(",",$tables);
			foreach($tblList as $a) {
				if(in_array($a,$tables)) {
					$r=$this->runQuery('SHOW CREATE TABLE '.$a);
					if($r) {
						$da=$this->fetchAllData($r);
						$this->freeResult($r);
						if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
						$sqlCreate=$da[0]['Create Table'];
						//$sqlCreate = str_replace("\n","",$sqlCreate);
						$s.="{$sqlCreate};\n\n";
					}
				}
			}
		}
		return $s;
	}
	
	public function prep_query($sql) {
		// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
		// the query so that it returns the number of affected rows
		if ($this->delete_hack == TRUE) {
			if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
				$sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
			}
		}
		//$sql=QueryGenerator::_clean($sql);
		return $sql;
	}
}
?>
