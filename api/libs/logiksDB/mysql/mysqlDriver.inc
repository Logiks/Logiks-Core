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
	
	public function __construct($keyName,$params) {
		parent::__construct($keyName,$params);
	}
	public function open($keyName) {
		if(!parent::open($keyName)) {
			trigger_logikserror("Database ERROR, Database Is Already Open {$keyName}");
			return false;
		}
		
		$this->link = @mysql_connect($this->dbParams['host'],$this->dbParams['user'],$this->dbParams['pwd'],$this->dbParams['database']) or die("MySQL DB Connection Error [{$keyName}] Connection");
		
		if (!$this->link) {
			trigger_logikserror("Database ERROR, Connection Error For {$keyName}");
		}
		
		$db = mysql_select_db($this->dbParams['database'],$this->link) or die("MySQL Database Selection Error For [{$keyName}] Connection");
		
		if(!$db) {
			trigger_logikserror("Database ERROR, Database Selection Error For {$keyName}");
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
			trigger_logikserror("Database ERROR, Security Threat Query Execution Hash Mismatch.");
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
			//trigger_logikserror("Database ERROR, Restricted Query [{$qArr}] for ".$this->instanceName,E_USER_WARNING);
			_log("Database is readonly","warn");
			return false;
		}
		
		$r=mysql_query($q, $this->link);
    	$this->postQuery($sql,$r);

		return $r;
	}
	public function runCommandQuery($q) {
		$r=mysql_query($q, $this->link);
    	$this->postQuery($q,$r);
    	
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
			trigger_logikserror("Database ERROR, Unknown Data Format Requested : $format");
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

	public function get_dbinfo() {
		return [
				"db_host"=>$this->dbParams['host'],
				"db_name"=>$this->dbParams['database'],
				"db_driver"=>$this->dbParams['driver'],
				"tables"=>count($this->get_tableList()),
				"collation"=>mysql_client_encoding($this->link),
				"client_info"=>mysql_get_client_info($this->link),
				"host_info"=>mysql_get_host_info($this->link),
				"server_info"=>mysql_get_server_info($this->link),
				"proto_info"=>mysql_get_proto_info($this->link),
			];
	}

	public function get_dbstatus() {
		$sql="SHOW STATUS";
		$result=$this->fetchQuery($sql);
		return $result;
	}

	public function get_tableStatus() {
		$sql="SHOW TABLE STATUS";
		$result=$this->fetchQuery($sql);
		return $result;
	}

	public function get_dbObjects() {
		$tables=[];$views=[];$triggers=[];$routines=[];$events=[];

		$sql="SELECT * FROM information_schema.tables WHERE  information_schema.tables.table_schema='{$this->dbParams['database']}'";
		$data=$this->fetchQuery($sql);
		foreach ($data as $key => $value) {
			$tables[$value['TABLE_NAME']]=$value;
		}

		$sql="SELECT * FROM information_schema.views WHERE  information_schema.views.table_schema='{$this->dbParams['database']}'";
		$data=$this->fetchQuery($sql);
		foreach ($data as $key => $value) {
			$views[$value['TABLE_NAME']]=$value;
		}

		$sql="SELECT * FROM information_schema.triggers WHERE  information_schema.triggers.TRIGGER_SCHEMA='{$this->dbParams['database']}'";
		$data=$this->fetchQuery($sql);
		foreach ($data as $key => $value) {
			$triggers[$value['TRIGGER_NAME']]=$value;
		}

		$sql="SELECT * FROM information_schema.routines WHERE  information_schema.routines.ROUTINE_SCHEMA='{$this->dbParams['database']}'";
		$data=$this->fetchQuery($sql);
		foreach ($data as $key => $value) {
			$routines[$value['ROUTINE_NAME']]=$value;
		}

		$sql="SELECT * FROM information_schema.events WHERE  information_schema.events.EVENT_SCHEMA='{$this->dbParams['database']}'";
		$data=$this->fetchQuery($sql);
		foreach ($data as $key => $value) {
			$events[$value['EVENT_NAME']]=$value;
		}

		return [
				"tables"=>$tables,
				"views"=>$views,
				"triggers"=>$triggers,
				"routines"=>$routines,
				"events"=>$events,
			];
	}
	
	public function get_defination($table) {
		$sql="DESCRIBE $table";
		$ans=$this->fetchQuery($sql);
		$arr=[];
		foreach ($ans as $key => $value) {
			$arr[]=array_values($value);
		}
		return $arr;
	}

	//All mainline functions and special queries
	public function get_tableList() {
		$sql="SHOW TABLES";
		$ans=$this->fetchQuery($sql);
		$arr=[];
		foreach ($ans as $key => $value) {
			$arr[]=array_values($value)[0];
		}
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

	private function fetchQuery($sql) {
		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$arr=array();
		while($record=mysqli_fetch_assoc($result)) {
			$p=$record;
			array_push($arr,$p);
		}
		$this->freeResult($result);
		return $arr;
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
