<?php
/*
 * This class is used for as the base Driver Class from which all drivers are created.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
 
 class AbstractDBDriver {
 	
	protected $link=null;
	 
	protected $keyName=array();
 	protected $dbParams=array();
	
	protected $blockedStmnts=array();
 	protected $readOnly=false;
 	protected $allowSQL=true;
	 
 	public $qCount=0;

 	protected $ddl=array("CREATE","ALTER","DROP","TRUNCATE","COMMENT","GRANT","REVOKE");
	protected $dml=array("INSERT","UPDATE","DELETE","CALL");
	protected $dql=array("SELECT");

 	public function __construct($keyName,$params) {
		if(is_array($params['block'])) $this->blockedStmnts=$params['block'];
		else $this->blockedStmnts=explode(",", $params['block']); 
 		
 		if(isset($params['readonly'])) $this->readOnly=$params['readonly'];
 		if(isset($params['allowSQL'])) $this->allowSQL=$params['allowSQL'];
		elseif(isset($params['allowsql'])) $this->allowSQL=$params['allowsql'];

 		unset($params['block']); unset($params['readonly']);
 		unset($params['instance']); unset($params['allowSQL']); unset($params['allowsql']);

 		$this->dbParams=$params;
		$this->keyName=$keyName;

		if(strlen($this->dbParams['host'])<=0 || strlen($this->dbParams['database'])<=0) {
			trigger_logikserror("Database ERROR, Wrong Credentials For {$keyName}");
		}
 	}
 	public function __destruct() {
		$this->close();
	}

	//General Functions
	public function open($keyName) {
		$this->keyName=$keyName;
		return (!$this->link);
	}
	public function close() {return true;}
	public function isOpen() {return !($this->link==null);}

	public function isReadonly() {return $this->readOnly;}
	public function isAllowedSQL() {return $this->allowSQL;}

	public function get_dbHost() {
		if(isset($this->dbParams['PORT'])) 
			return $this->dbParams['HOST'].":".$this->dbParams['PORT'];
		else
			return $this->dbParams['HOST'];
	}
	public function get_link() {return $this->link;}
	public function get_QCount() {return $this->qCount;}

	public function get_DSN() {
		if(isset($this->dbParams['port']) && $this->dbParams['port']>0)
			return "{$this->dbParams['driver']}:dbname={$this->dbParams['database']};host={$this->dbParams['host']};port={$this->dbParams['port']}";
		else
			return "{$this->dbParams['driver']}:dbname={$this->dbParams['database']};host={$this->dbParams['host']}";
	}
	
	//All Query & Resultset Functions
	public function freeResult($resultSet) {return false;}


	public function runQuery($sql) {
		if(is_a($sql,"AbstractQueryBuilder")) {
			if(md5($sql->getInstanceName())==md5($this->keyName)) {
				$this->qCount++;
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
	public function runCommandQuery($sql) {$this->qCount++;return true;}
	
	//Resultset based functions
	public function fetchAllData($result,$format="assoc") {
		if($result==null || !$result) return array();
		$out=array();
		while($r=$this->fetchData($result,$format)) {
			$out[]=$r;
		}
		return $out;
	}
	public function fetchData($resultSet,$format="assoc") {
		if(is_a($resultSet,"QueryResult")) {
			return $resultSet->getResult();
		}
		return $resultSet;
	}
	
	public function fetchHeaders($resultSet) {return null;}//Get all column names
	public function fetchFields($resultSet) {return null;}//Get all column names with there info
	
	public function get_fieldType($resultSet, $colIndex) {return null;}
	public function get_recordCount($resultSet) {return 0;}
	public function get_columnCount($resultSet) {return 0;}
	
	//Link/Resource# Based Function
	public function get_errorNo() {return 0;}
	public function get_error() {return false;}

	public function get_affectedRows() {return false;}
	public function get_insertID() {return false;}

	//All Database related functions
	public function get_dbinfo() {return [];}
	public function get_dbstatus() {return [];}
	public function get_tablestatus() {return [];}
	
	public function get_dbObjects() {
		return [
				"tables"=>[],
				"views"=>[],
				"routines"=>[],
				"events"=>[],
				"triggers"=>[],
			];
	}
	
	//All mainline functions and All special queries
	public function get_maxInCol($table,$colname) {
		$sql="SELECT MAX($colname) as mv FROM $table";
		$result=$this->runQuery($sql);
		if (!$result) {
			return null;
		}
		$maxvalue=0;
		while($record=$this->fetchData($result)) {
			$maxvalue=$record["mv"];
		}
		$this->freeResult($result);
		return (int)$maxvalue;
	}
	
	public function get_tableList() {return array();}
	public function get_columnList($table,$nameOnly=true) {return array();}
	public function get_primaryKey($table) {return array();}
	public function get_allkeys($table) {return array();}

	//Schema related functions
	public function get_schema($tables=null,$dropIfExists=true) {return "";}

	public function get_defination($table) {return [];}

	public function get_tableInserts($table) {
		$result =  $this->runQuery('SELECT * FROM '.$table);
		if(!$result) return '';

		$num_fields = $this->get_columnCount($result);

		$s="";
		//for ($i = 0; $i < $num_fields; $i++) {
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
		//}
		return $s;
	}

	//All Supportive Functions
	public function prep_query($sql) {
		return $sql;
	}

	protected function checkQuery($sql) {
		$q=explode(" ",trim($sql));
		$q=strtoupper($q[0]);
		if($this->readOnly && strlen($q)>0 && (in_array($q,$this->ddl) || in_array($q,$this->dml))) {
			return false;
		}
		if(in_array($q,$this->blockedStmnts)) {
			return false;
		}
		return true;
	}

	//All DBTable Name functions
	public function get_table($tblName) {
		$px=$this->dbParams['prefix'];
		$sx=$this->dbParams['suffix'];
		if(strlen($px)>0) $tblName="{$px}_{$tblName}";
		if(strlen($sx)>0) $tblName="{$tblName}_{$sx}";
		return $tblName;
	}

	public function __debugInfo() {
        return [];
    }
}
?>
