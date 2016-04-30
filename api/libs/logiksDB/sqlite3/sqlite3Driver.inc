<?php
/*
 * Driver For SQLite
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

class sqlite3Driver extends AbstractDBDriver {
	
	private $dataFile=null;

	private $fieldtypes=array(
			1=>"SQLITE3_INTEGER",
			2=>"SQLITE3_FLOAT",
			3=>"SQLITE3_TEXT",
			4=>"SQLITE3_BLOB",
			5=>"SQLITE3_NULL",
		);

	public function __construct($params) {
		parent::__construct($params);

		if(!class_exists("sqlite3")) {
			trigger_logikserror("Database ERROR, SQLite Not Installed.");
			return false;
		}

		$dataFile=ROOT.DATA_FOLDER."sqlite/{$params['database']}.db";
		if(!is_dir(dirname($dataFile))) {
			mkdir(dirname($dataFile));
		}
		if(!is_writable(dirname($dataFile))) {
			trigger_logikserror("Database ERROR, SQLite folder is not writable Open {$keyName}");
			return false;
		}
		$this->dataFile=$dataFile;

		//printArray($params);
	}
	public function open($keyName) {
		if(!parent::open($keyName)) {
			trigger_logikserror("Database ERROR, Database Is Already Open {$keyName}");
			return false;
		}
		$sqliteerror=null;

		$this->link = new sqlite3($this->dataFile, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE, $this->dbParams['pwd']);

		if(!$this->link) {
			trigger_logikserror("Database ERROR, Connection Error For {$keyName}");
		}
	}

	public function close() {
		if($this->link) {
			$this->link->close();
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
			trigger_logikserror("Database ERROR, Restricted Query [{$qArr}] for ".$this->keyName,E_USER_WARNING);
		}
		
		$r=$this->link->query($q);

		return $r;
	}
	public function runCommandQuery($q) {
		return $this->link->exec($q);
	}

	public function freeResult($result) {
		if($result) {
			return $result->finalize();
		}
		return false;
	}

	//Result based functions
	public function fetchData($result,$format="assoc") {
		if($this->link->lastErrorCode() ) return false;
		
		$result=parent::fetchData($result);
		$format=strtolower($format);
		if($format=="array") {
			return $result->fetchArray(SQLITE3_NUM);
		} else if($format=="assoc") {
			return $result->fetchArray(SQLITE3_ASSOC);
		} else {
			trigger_logikserror("Database ERROR, Unknown Data Format Requested : $format");
		}
		return false;
	}

	public function fetchAllData($result,$format="assoc") {
		if($this->link->lastErrorCode() ) return false;

		$format=strtolower($format);
		$out=array();
		if($format=="array") {
			while($row = $result->fetchArray(SQLITE3_NUM)) {
	            $out[]=$row;
	        }
		} else if($format=="assoc") {
			while($row = $result->fetchArray(SQLITE3_ASSOC)) {
	            $out[]=$row;
	        }
		} else {
			trigger_logikserror("Database ERROR, Unknown Data Format Requested : $format");
		}
		return $out;
	}

	public function fetchHeaders($result) {
		if($this->link->lastErrorCode() ) return false;

		$cols=array();
		$nx = $result->numColumns(); 
		for ($i = 0; $i < $nx; $i++) { 
            $cols[] = $result->columnName($i);
        } 
		return $cols;
	}

	public function fetchFields($result) {
		if($this->link->lastErrorCode() ) return false;

		$cols=array();

		$result->fetchArray(SQLITE3_ASSOC);

		$nx = $result->numColumns(); 
		for ($i = 0; $i < $nx; $i++) { 
			$dx="";
			if(isset($this->fieldtypes[$result->columnType($i)])) $dx=$this->fieldtypes[$result->columnType($i)];
            $cols[] = array(
            		"name"=>$result->columnName($i),
            		"type"=>$dx,
            		"length"=>0,
            		"flags"=>"",
            	);
        } 

		return $cols;	
	}

	public function get_fieldType($result, $colIndex) {
		if($this->link->lastErrorCode() ) return false;

		return $this->fieldtypes[$result->columnType($colIndex)];
	}

	public function get_columnCount($result) {
		if($this->link->lastErrorCode() ) return false;

		return $result->numColumns();
	}

	//Link/Resource# Based Function
	public function get_errorNo() {return $this->link->lastErrorCode();}
	public function get_error() {return $this->link->lastErrorMsg();}

	public function get_affectedRows() {return $this->link->changes();}
	public function get_insertID() {return $this->link->lastInsertRowID();}

	//All mainline functions and special queries
	public function get_tableList() {
		$sql="SELECT name FROM sqlite_master WHERE type='table' AND  name NOT LIKE 'sqlite_%';";

		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$records=$this->fetchAllData($result);
		$this->freeResult($result);

		$arr=array();
		foreach ($records as $key => $value) {
			$arr[]=$value['name'];
		}
		
		return $arr;
	}

	public function get_columnList($table,$nameOnly=true) {
		$sql="SELECT * FROM api_toc WHERE id<0";
		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$arr=array();
		if($result) {
			if($nameOnly) {
				$arr=$this->fetchHeaders($result);
			} else {
				$arr=$this->fetchFields($result);
			}
		}
		$this->freeResult($result);
		return $arr;
	}

	public function get_primaryKey($table) {
		$sql="SELECT * FROM sqlite_master WHERE name='$table'";
		$result=$this->runQuery($sql);
		if (!$result) {
			return array();
		}
		$records=$this->fetchData($result);
		$this->freeResult($result);
		$arr=array();
		if(count($records)>0) {
			$tok=trim(substr(stristr($records['sql'],"("), 1,-1));
			$tok=explode(",", $tok);
			foreach ($tok as $field) {
				if(strpos($field, "PRIMARY KEY")){
					$field=explode(" ", str_replace('"', "", $field));
					$arr[]=$field[0];
				}
			}
		}
		
		return $arr;
	}

	public function get_allkeys($table) {
		return $this->get_primaryKey($table);
	}


	//Schema related functions
	public function get_schema($tables=null,$dropIfExists=true) {
		$tblList=$this->get_tableList();
		$s="";
		if($tables==null) {
			foreach($tblList as $a) {
				$r=$this->runQuery("SELECT * FROM sqlite_master WHERE name='$a'");
				if($r) {
					$da=$this->fetchData($r);
					$this->freeResult($r);
					if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
					$sqlCreate=$da['sql'];
					//$sqlCreate = str_replace("\n","",$sqlCreate);
					$s.="{$sqlCreate};\n\n";
				}
			}
		} else {
			if(!is_array($tables)) $tables=explode(",",$tables);
			foreach($tblList as $a) {
				if(in_array($a,$tables)) {
					$r=$this->runQuery("SELECT * FROM sqlite_master WHERE name='$a'");
					if($r) {
						$da=$this->fetchData($r);
						$this->freeResult($r);
						if($dropIfExists) $s.="DROP TABLE IF EXISTS `$a`;\n";
						$sqlCreate=$da['sql'];
						//$sqlCreate = str_replace("\n","",$sqlCreate);
						$s.="{$sqlCreate};\n\n";
					}
				}
			}
		}
		return $s;
	}

	public function get_tableInserts($table) {
		$result =  $this->runQuery('SELECT * FROM '.$table);
		$num_fields = $this->get_columnCount($result);

		$s="";
		while($row = $result->fetchArray(SQLITE3_NUM)) {
			$s.= 'INSERT INTO '.$table.' VALUES (';
			for($j=0; $j<$num_fields; $j++) {
				$row[$j] = addslashes($row[$j]);
				$row[$j] = str_replace("\n","\\"."n",$row[$j]);
				if(isset($row[$j])) { $s.= '"'.$row[$j].'"' ; } else { $s.= '""'; }
				if($j<($num_fields-1)) { $s.= ','; }
			}
			$s.= ");\n";
		}
		return $s;
	}
}
?>
