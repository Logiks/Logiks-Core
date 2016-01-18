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
				"groupby"=>array("group"=>null,"having"=>null),
				"orderby"=>"",
				"limits"=>array("limit"=>100,"offset"=>0),
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
	public function _where($where=null,$joinType="AND",$implodeType='AND') {
		if(!$where) return $this;
		if(!is_array($this->obj['where'])) $this->obj['where']=[];
		
		if(is_array($where)) {
			$this->obj['where'][]=array($joinType,$where,$implodeType);
		} else {
			$this->obj['where'][]=array($joinType,$where,$implodeType);
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
		$groupby=$this->clean($groupby);
		$having=$this->clean($having);

		$this->obj['groupby']['group']=$groupby;
		$this->obj['groupby']['having']=$having;
		return $this;
	} 
	//ORDER BY Functions
	public function _orderby($orderby) {
		if(!$orderby) return $this;
		if(is_array($orderby)) {
			$orderby=$this->cleanArr($orderby);
		} else {
			$orderby=$this->clean($orderby);
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
			$limit=$this->clean($limit);
		}

		if($offset==null || !is_numeric($offset)) {
			$offset=0;
		} else $offset=$this->clean($offset);
		
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
		//var_dump($this->obj);

		$limit=$this->obj['limits']['limit'];
		$offset=$this->obj['limits']['offset'];

		$orderby=$this->obj['orderby'];

		$group=null;
		$having=null;
		if(is_array($this->obj['groupby'])) {
			if(isset($this->obj['groupby']['group'])) {
				$group=$this->obj['groupby']['group'];
			}
			if(isset($this->obj['groupby']['having'])) {
				$having=$this->obj['groupby']['having'];
			}
		}

		$where=$this->obj['where'];

		$sql=$this->sql;

		$whereFinal=[];
		if($where && is_array($where)) {
			foreach($where as $a=>$b) {
				if(!isset($b[2])) $b[2]="AND";
				if(count($whereFinal)>0) {
					$startW="{$b[0]}";
				} else {
					$startW="";
				}
				if(is_array($b[1])) {
					$sx=[];
					foreach ($b[1] as $m=>$n) {
						if(!is_array($n)) {
							$n=["VALUE"=>$n,"OP"=>"EQ"];
						}
						$sx[]=$this->parseRelation($m,$n);
					}
					$startW.=" (".implode(" {$b[2]} ", $sx).") ";
				} else {
					$startW.=" {$b[1]}";
				}

				$whereFinal[]=trim($startW);
			}
		}
		if(count($whereFinal)>0) {
			$sql.=" WHERE ".implode(" ", $whereFinal);
		}

		if($group && strlen($group)>0) {
			$sql.=" GROUP BY $group";
		}

		if($having && strlen($having)>0) {
			$sql.=" HAVING $having";
		}

		if($orderby && strlen($orderby)>0) {
			$sql.=" ORDER BY $orderby";
		}

		$sql.=" LIMIT $offset, $limit";

		return $sql;
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
		if(!is_a($dbInstance, "Database")) {
			trigger_error("Database ERROR, DBInstance should be an object of Database");
		}
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
		if(!is_a($dbInstance, "Database")) {
			trigger_error("Database ERROR, DBInstance should be an object of Database");
		}

		$table=null;
		$cols="";
		$where=null;
		$groupby = null;
		$having = null;
		$orderby = null;
		$index=0;
		$limit = FALSE;
		
		$table=$arr['table'];
		$cols=$arr['cols'];
		if(isset($arr['where'])) $where=$arr['where'];
		if(isset($arr['groupby'])) $groupby=$arr['groupby'];
		if(isset($arr['having'])) $having=$arr['having'];
		if(isset($arr['orderby'])) $orderby=$arr['orderby'];
		if(isset($arr['limit'])) $limit=$arr['limit'];
		if(isset($arr['index'])) $index=$arr['index'];
		
		if(is_array($table)) {
			$obj=QueryBuilder::fromArray($table,$dbInstance);
			$table=$obj->_SQL();
		}
		
		$objx=QueryBuilder::create($dbInstance)->_selectQ($table,$cols,$where);
		//$objx->_where($where);
		
		if(isset($arr['join'])) {
			foreach($arr['join'] as $jn) {
				$query=$jn['query'];
				$condition=$jn['condition'];
				$as=$jn['as'];
				$type=$jn['type'];
				
				$objx->_join($query,$condition,$as,$type);
			}
		}
		
		$objx->_groupby($groupby,$having);
		$objx->_orderby($orderby);
		$objx->_limit($limit,$index);
		
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
		if(array_key_exists("RAW", $arr)) {
			return "{$arr['RAW']}";
		}
		if(isset($arr['VALUE'])) $arr[0]=$arr['VALUE'];
		if(isset($arr['OP'])) $arr[1]=$arr['OP'];

		if(!isset($arr[1])) $arr[1]="=";
		
		$s="";
		switch(strtolower($arr[1])) {
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
			case "between":case "like":
				$s="{$col} LIKE '%{$arr[0]}%'";
			break;
			
			case "cn":case ":cn:":
			case "ni":case ":ni:":
			case "notbetween":
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
