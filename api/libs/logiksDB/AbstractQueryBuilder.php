<?php
/*
 * This class is used for as the base Driver Class from which all query builders are created.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
 
 class AbstractQueryBuilder {
 	protected $sql="";
	protected $obj=array(
				"table"=>"",
				"cols"=>"",
				"where"=>"",
				"groupby"=>"",
				"orderby"=>"",
				"limits"=>array(),
			);
	protected $fromSQL=false;
	
	protected $dbInstance=null;
	
	public static function create($dbInstance) {
		return new AbstractQueryBuilder($dbInstance);
	}
	
	protected function __construct($dbInstance) {
		$this->dbInstance=$dbInstance;
	}
	
	public function getInstance() {
		return $this->dbInstance;
	}
	
	public function getInstanceName() {
		return $this->dbInstance->getInstanceName();
	}

	public function _get() {
		$res=$this->dbInstance->runQuery($this);
		return $this->dbInstance->fetchAllData($res);
	}
	
	//CRUD stands for Create, Update, Retrive and Delete.
	public function _envelop($sql) {
		$this->sql=$sql;
		return $this;
	}

	public function _selectQ($table, $cols="", $where=null, $groupby = null, $orderby = null, $limit = FALSE) {return $this;}
	public function _insertQ($table, $keys, $values) {return $this;}
	public function _insertQ1($table, $arr) {return $this;}
	public function _replaceQ($table, $keys, $values) {return $this;}
	public function _replaceQ1($table, $arr) {return $this;}
	public function _insert_batchQ($table, $arr) {return $this;}
	public function _updateQ($table, $values, $where, $orderby = array(), $limit = false) {return $this;}
	public function _deleteQ($table, $where = array(), $limit = false) {return $this;}

	public function _raw($sql) {
		if($this->sql==null) $this->sql="";
		$this->sql.=$sql; 
		return $this;
	}
	
	
	//WHERE Functions
	public function _where($where=null,$type="AND") {
		if(is_array($this->obj['where'])) $this->obj['where']=implode(" AND ",$this->obj['where']);
		
		$whr="";
		if(is_array($where)) {
			$w=array();
			foreach($where as $a=>$b) {
				if(is_array($b)) {
					$w[]=$this->parseRelation($a,$b);
				} else {
					$w[]="$a=".$this->sqlData($b)."";
				}
			}
			$whr.=implode(" AND ",$w);
		} elseif(strlen($where)>0) {
			$whr.=$where;
		}
		if(strlen($whr)>0) {
			if(strpos(strtoupper($this->sql)," WHERE")<=0) {
				$this->sql.=" WHERE {$whr}";
				$this->obj['where'].=$whr;
			} else {
				$this->sql.=" {$type} ({$whr})";
				$this->obj['where'].=" {$type} ({$whr})";
			}
		}
		return $this;
	}
	//GROUP BY Function
	public function _groupby($groupby,$having=null) {
		if(!$groupby) return $this;
		if(is_array($groupby)) {
			if(isset($groupby['having'])) {
				$having=$groupby["having"];
			}
			$groupby=$groupby["group"];
		}
		
		$obj=array();
		if(is_array($groupby)) {
			$groupby=$this->cleanArr($groupby);
			$this->sql.=(count($groupby) >= 1)?' GROUP BY '.implode(", ", $groupby):'';
		} elseif(strlen($groupby)>0) {
			$this->sql.=" GROUP BY $groupby";
		}
		if($having!=null) {
			if(is_array($having)) {
				if(is_array($having[1])) {
					$having=$this->parseRelation($having[0],$having[1]);
				} else {
					$having="{$having[0]}=".$this->sqlData($having[1])."";
				}
				$this->sql.=" HAVING {$having}";
			} elseif(strlen($having)>0) {
				$this->sql.=" HAVING {$having}";
			}
		}
		$this->obj['groupby']['group']=$groupby;
		$this->obj['groupby']['having']=$having;
		return $this;
	} 
	//ORDER BY Functions
	public function _orderby($orderby) {
		if(!$orderby) return $this;
		if(is_array($orderby)) {
			$orderby=$this->cleanArr($orderby);
			$this->sql.=(count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';
		} elseif(strlen($orderby)>0) {
			$this->sql.=" ORDER BY $orderby";
		}
		$this->obj['orderby']=$orderby;
		return $this;
	}
	//Limit BY Function
	public function _limit($limit=null,$offset=null) {
		if(is_array($limit)) {
			if(isset($limit['offset'])) {
				$offset=$limit["offset"];
				$limit=$limit["limit"];
			} else {
				$offset=$limit[0];
				$limit=$limit[1];
			}
		}
		if(is_numeric($limit) && $limit>0) {
			if($offset==null || !is_numeric($offset)) $offset=0;
			else $offset=$this->clean($offset);
			
			$limit=$this->clean($limit);
			$this->sql.=" LIMIT $offset,{$limit}";	
		} elseif($offset!=null && is_numeric($offset)) {
			$this->sql.=" LIMIT $offset";	
		}
		
		$this->obj['limits']['limit']=$limit;
		$this->obj['limits']['offset']=$offset;
		
		return $this;
	}
	//SubQuery
	public function _query($col,$query,$relation="IN",$glueType="AND") {
		if(is_a($query,"QueryBuilder")) {
			$this->obj['query']=array(
				"col"=>$col,
				"query"=>$query->_array(),
				"relation"=>$relation,
				"glueType"=>$glueType,
			);
			$query=$query->_SQL();
			$this->sql=trim($this->sql)." {$glueType} {$col} {$relation} ({$query})";
		} elseif(is_array($query)) {
			
		} else {
			$this->obj['query']=array(
				"col"=>$col,
				"query"=>$query,
				"relation"=>$relation,
				"glueType"=>$glueType,
			);
			$this->sql=trim($this->sql)." {$glueType} {$col} {$relation} ({$query})";
		}
		
		return $this;
	}
	//Join Statements
	public function _join($query,$condition,$as=null,$type="LEFT") {
		if(is_a($query,"QueryBuilder")) {
			$this->obj['join'][]=array(
				"query"=>$query->_array(),
				"condition"=>$condition,
				"as"=>$as,
				"type"=>$type,
			);
			$query=$query->_SQL();
		} else {
			$this->obj['join'][]=array(
				"query"=>$query,
				"condition"=>$condition,
				"as"=>$as,
				"type"=>$type,
			);
		}
		if($as==null) $as='';
		switch($type) {
			case "LEFT":
				$this->sql=trim($this->sql)." LEFT JOIN ({$query}) $as ON $condition";
			break;
			case "RIGHT":
				$this->sql=trim($this->sql)." RIGHT JOIN ({$query}) $as ON $condition";
			break;
			case "INNER":
				$this->sql=trim($this->sql)." INNER JOIN ({$query}) $as ON $condition";
			break;
		}
		return $this;
	}
	//UNION Statements
	public function _union($query) {
		if(is_a($query,"QueryBuilder")) {
			$this->obj['union']=$query->_array();
			$query=$query->_SQL();
		} else {
			$this->obj['union']=$query;
		}
		$this->sql=trim($this->sql)." UNION ({$query})";
		return $this;
	}
	
	//Special Queries Generator
	public function _truncateQ($table) {
		return "TRUNCATE ".$table;
	}
	
	public function _dbVersionQ() {
		return "SELECT version() AS ver";
	}
	
	//Check if QueryBuilder is created directly From SQL
	public function isFromSQL() {
		return $this->fromSQL;
	}
	
	//GETS/SETS the sql data into a queryBuilder object
	//@$sql	params	SQL Query to be set into QueryBuilder
	public function _SQL($sql=null) {
		return $this->sql;
	}
	//GETS/SETS the sql data into a queryBuilder object
	//@$sql	params	SQL Query to be set into QueryBuilder
	public function _array() {
		return $this->obj;
	}
	//GETS/SETS the json based sql data source into a queryBuilder object
	//@$json params	SQL JSON Query to be set into QueryBuilder
	public function _JSON() {
		return json_encode($this->obj);
	}
	
	//Creates QueryBuilder Object From SQL String
	public static function fromSQL($sql,$dbInstance) {
		$obj=QueryBuilder::create($dbInstance);
		$obj->sql=$sql;
		$obj->fromSQL=true;
		return $obj;
	}
	//Creates QueryBuilder Object From JSON Object using array converion.
	public static function fromJSON($json,$dbInstance) {
		$arr=json_decode($json,true);
		return QueryBuilder::fromArray($arr,$dbInstance);
	}
	//Creates QueryBuilder Object From Array
	//Currently it can not handle very complex queries
	public static function fromArray($arr,$dbInstance) {
		$table=null;
		$cols="";
		$where=null;
		$groupby = null;
		$orderby = null;
		$limit = FALSE;
		
		$table=$arr['table'];
		$cols=$arr['cols'];
		if(isset($arr['where'])) $where=$arr['where'];
		if(isset($arr['groupby'])) $groupby=$arr['groupby'];
		if(isset($arr['orderby'])) $orderby=$arr['orderby'];
		if(isset($arr['limits'])) $limit=$arr['limits'];
		
		if(is_array($table)) {
			$obj=QueryBuilder::fromArray($table,$dbInstance);
			$table=$obj->_SQL();
		}
		
		$objx=QueryBuilder::create($dbInstance)->_selectQ($table,$cols);
		$objx=$objx->_where($where);
		
		if(isset($arr['join'])) {
			foreach($arr['join'] as $jn) {
				$query=$jn['query'];
				$condition=$jn['condition'];
				$as=$jn['as'];
				$type=$jn['type'];
				
				$objx=$objx->_join($query,$condition,$as,$type);
			}
		}
		
		$objx=$objx->_groupby($groupby);
		$objx=$objx->_orderby($orderby);
		$objx=$objx->_limit($limit);
		
		return $objx;
	}
	
	//Just appends a new string to sql
	public function _append($str) {
		//$this->obj['append']=$str;
		$this->sql.=" {$str}";
		return $this;
	} 
	
	//Function to sanitize values received from the form. Prevents SQL injection
	//clean for array
	protected function cleanArr($arr) {
		foreach($arr as $a=>$b) {
			$arr[$a]=$this->clean($b);
		}
		return $arr;
	}
	//For cleaning of data internally
	protected function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {$str=stripslashes($str);}
		//$str=@mysql_real_escape_string($str);
		return $str;
	}
	
	//sqlData for arrays
	protected function sqlDataArr($arr,$sqlType="*") {
		foreach($arr as $a=>$b) {
			$arr[$a]=$this->sqlData($b,$sqlType);
		}
		return $arr;
	}
	//SQL Data Operations for insert and where clauses
	protected function sqlData($s,$sqlType="*") {
		if(is_array($s)) {
			$s=implode(",",$s);
		}
		$s=$this->clean($s);
		if(strlen($s)<=0) return "''";
		if($sqlType=="*" || $sqlType=="auto") {
			//$s1=strtolower($s);
			if($s=="TRUE" || $s=="FALSE") return strtoupper($s);
			elseif($s===true || $s===false) return ($s===true)?"TRUE":"FALSE";
			elseif(is_numeric($s)) return $s;
			elseif(is_float($s)) return $s;
			elseif(is_null($s)) return $s;
			elseif(is_bool($s)) return $s;
			elseif(preg_match("/\d{2}\-\d{2}-\d{4}/",str_replace("/","-",$s))) return "'"._date($s)."'"; 
			else return "'$s'";
		} elseif($sqlType=="int" || $sqlType=="float" || $sqlType=="bool") {
			if(strlen($s)<=0) return "0";
			else return "$s";
		} elseif($sqlType=="date") {
			$s=_date($s);
			return "'$s'";
		} else {
			return "'$s'";
		}
	}
	//WHERE Condition Parser
	protected function parseRelation($col,$arr) {
		if(!isset($arr[1])) $arr[1]="=";
		
		$s="";
		switch($arr[1]) {
			case "eq":case ":eq:":
			case "=":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}={$arr[0]}";
			break;
			
			case "ne":case ":ne:":
			case "neq":case ":neq:":
			case "<>":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}<>{$arr[0]}";
			break;
			
			case "lt":case ":lt:":
			case "<":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}<{$arr[0]}";
			break;
			case "le":case ":le:":
			case "<=":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}<={$arr[0]}";
			break;
			case "gt":case ":gt:":
			case ">":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}>{$arr[0]}";
			break;
			case "ge":case ":ge:":
			case ">=":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}>={$arr[0]}";
			break;
			
			case "nn":case ":nn:":
				$s="{$col} IS NOT NULL";
			break;
			case "nu":case ":nu:":
				$s="{$col} IS NULL";
			break;
			
			case "bw":case ":bw:":
			case "starts":
				$s="{$col} LIKE '{$arr[0]}%'";
			break;
			case "bn":case ":bn:":
				$s="{$col} NOT LIKE '{$arr[0]}%'";
			break;
			
			case "lw":case ":lw:":
			case "ends":
				$s="{$col} LIKE '%{$arr[0]}'";
			break;
			case "ln":case ":ln:":
				$s="{$col} NOT LIKE '%{$arr[0]}'";
			break;
			
			case "cw":case ":cw:":
			case "in":case ":in:":
				$s="{$col} LIKE '%{$arr[0]}%'";
			break;
			case "cn":case ":cn:":
			case "ni":case ":ni:":
				$s="{$col} NOT LIKE '%{$arr[0]}%'";
			break;
			
			default:
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col} {$arr[0]}";
		}
		return $s;
	}

	public function __debugInfo() {
        return [];
    }
}
?>
