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
				"limits"=>array("limit"=>null,"offset"=>null),
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
		if(!$res) return false;
		if(is_bool($res)) return $res;
		$data=$this->dbInstance->fetchAllData($res);
		$this->dbInstance->free($res);
		return $data;
	}

	public function _getRaw() {
		$res=$this->dbInstance->runQuery($this);
		return $res;
	}

	public function _run() {
		return $this->dbInstance->runQuery($this);
	}
	
	//CRUD stands for Create, Update, Retrive and Delete.
	public function _selectQ($table, $cols="", $where=null, $groupby = null, $orderby = null, $limit = FALSE) {return $this;}
	public function _insertQ($table, $keys, $values) {return $this;}
	public function _insertQ1($table, $arr) {return $this;}
	public function _replaceQ($table, $keys, $values) {return $this;}
	public function _replaceQ1($table, $arr) {return $this;}
	public function _insert_batchQ($table, $arr) {return $this;}
	public function _updateQ($table, $values, $where, $orderby = array(), $limit = false) {return $this;}
	public function _deleteQ($table, $where = array(), $limit = false) {return $this;}
	 
	public function _increment($table, $cols, $where) {return $this;}
	public function _decrement($table, $cols, $where) {return $this;}
	

  public function getTableName() {
    return $this->obj['table'];
  }
   
	public function _raw($sql) {
		$this->sql=$sql; 
		return $this;
	}
	
	//WHERE Functions
	public function _where($where=null,$joinType="AND",$implodeType='AND') {
		if(!$where) return $this;
		if(!is_array($this->obj['where'])) $this->obj['where']=[];
		
		if(is_array($where)) {
			$where=$this->cleanArr($where);
			$this->obj['where'][]=array($joinType,$where,$implodeType);
		} else {
			$where=$this->clean($where);
			$this->obj['where'][]=array($joinType,$where,$implodeType);
		}
		
		return $this;
	}
	public function _whereMulti($where=null,$joinType="AND",$implodeType='AND') {
		if(!$where) return $this;
		if(!is_array($this->obj['where'])) $this->obj['where']=[];
		
		$fWhere=[];
		foreach($where as $w) {
			$w=$this->clean($w);
			$n=count($w);
			if(isset($w[1])) {
				if(is_array($w[1])) {
					$fWhere[]=$this->parseRelation($w[0],$w[1]);
				} else {
					$fWhere[]=$this->parseRelation($w[0],[$w[1],"="]);
				}
			} else {
				foreach($w as $k=>$m) {
					if(is_array($m)) {
						$fWhere[]=$this->parseRelation($k,$m);
					} else {
						$fWhere[]=$this->parseRelation($k,[$m,"="]);
					}
				}
			}
		}
		$fWhere="(".implode(" {$implodeType} ",$fWhere).")";
		$this->_whereRAW($fWhere,$joinType);
		
		return $this;
	}
	public function _whereOR($col,$data=null) {
		if(!$col) return $this;
		if(!is_array($this->obj['where'])) $this->obj['where']=[];
		
		if($data!=null) {
			if(is_array($data)) {
				$data=$this->cleanArr($data);
				$str=[];
				foreach ($data as $vx) {
					$str[]=$this->parseRelation($col,$vx);
				}
				$this->obj['where'][]=array("AND","(".implode(" OR ", $str).")","OR");
			}
		} else {
			if(is_array($col)) {
				$col=$this->cleanArr($col);
				$str=[];
				foreach ($col as $key=>$vx) {
					$str[]=$this->parseRelation($key,$vx);
				}
				$this->obj['where'][]=array("AND","(".implode(" OR ", $str).")","OR");
			}
		}
		return $this;
	}
	public function _whereCOL($col1,$col2,$joinType="AND") {
		//$this->_whereRAW("{$col1}={$col2}");
		$this->obj['where'][]=array($joinType,"{$col1}={$col2}");
		return $this;
	}
	public function _whereIN($col,$data,$joinType="AND") {
		if(is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key]=$this->sqlData($value);
			}

			$whereSQL="$col IN (".implode(",", $data).")";
		} else {
			$whereSQL="FIND_IN_SET('$data',$col)";
		}

		$this->obj['where'][]=array($joinType,$whereSQL);

		return $this;
	}
	public function _whereRAW($where=null,$joinType="AND",$implodeType='AND') {
		$this->obj['where'][]=array($joinType,$where,$implodeType);

		return $this;
	}
	//public function _query($where=null,$joinType="AND",$implodeType='AND') {
	public function _query($col,$query,$relation="IN",$glueType="AND") {
		if(is_a($query,"AbstractQueryBuilder")) {
			$sql=$query->_SQL();
			$this->_whereRAW("$col $relation ($sql)");
		//} elseif(is_array($query)) {

		} elseif(is_string($query)) {
			$this->_whereRAW("$col $relation ($sql)");
		} else {
			trigger_logikserror("$query should be an object of AbstractQueryBuilder or String");// or Array
		}

		return $this;
	}
	//GROUP BY Function
	public function _groupby($groupby,$having=null) {
		if(!$groupby) return $this;
		if(is_array($groupby)) {
			if(isset($groupby['having'])) {
				$having=$groupby["having"];
			} else {
				$having=false;
			}
			if(isset($groupby['group'])) {
				$groupby=$groupby["group"];
			} else {
				$groupby=false;
			}
		}
		if($groupby) $groupby=$groupby;//$this->clean($groupby);
		if($having) $having=$having;//$this->clean($having);
		

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
			$orderby=$orderby;//$this->clean($orderby);
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
		return "TRUNCATE ".$this->clean($table);
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
		if($this->obj['limits']) {
			$limit=$this->obj['limits']['limit'];
			$offset=$this->obj['limits']['offset'];
		} else {
			$limit=null;
			$offset=0;
		}

		if($this->obj['orderby']) {
			$orderby = $this->obj['orderby'];
		} else {
			$orderby = false;
		}

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
		$sqlType=strtoupper(trim(current(explode(" ", $sql))));

		$whereFinal=[];
		if($where && is_array($where)) {
			//printArray($where);
			foreach($where as $a=>$b) {
				if(is_array($b)) {
					if(!isset($b[2])) $b[2]="AND";
					if(count($whereFinal)>0) {
						$startW="{$b[0]}";
					} else {
						$startW="";
					}
					if(is_array($b[1])) {
						$sx=[];
						foreach ($b[1] as $m=>$n) {
							if($n=="RAW") {
								$sx[]=$m;
								continue;
							}
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
				} else {
					if($b=="RAW") {
						$whereFinal[]=" AND {$a}";
					} else {
						$b=explode(":", $b);
						if(!isset($b[1])) $b[1]="EQ";
						if(count($whereFinal)>0) {
							$whereFinal[]=" AND ".$this->parseRelation($a,["VALUE"=>$b[0],"OP"=>$b[1]]);
						} else {
							$whereFinal[]=$this->parseRelation($a,["VALUE"=>$b[0],"OP"=>$b[1]]);
						}
					}
				}
			}
		}

		if(function_exists("getAppWhereQuery")) {
			$sqlWX=getAppWhereQuery();
		} else {
			$sqlWX="";
		}
		
		if(count($whereFinal)>0) {
			$sql.=" WHERE ".implode(" ", $whereFinal);
			if(strlen($sqlWX)>0) {
				$sql.=" AND ({$sqlWX})";
			}
		} else {
			if(strlen($sqlWX)>0) {
				$sql.=" WHERE ({$sqlWX})";
			}
		}

		if($sqlType=="SELECT") {
			if($group && strlen($group)>0) {
				$sql.=" GROUP BY $group";
			}
		}

		if($having && strlen($having)>0) {
			$sql.=" HAVING $having";
		}

		if($orderby && strlen($orderby)>0) {
			$sql.=" ORDER BY $orderby";
		}
		
		if($sqlType=="SELECT") {
			if($limit!=null && $limit>0) {
				if($offset==null) {
					$offset=0;
				}
				$sql.=" LIMIT $offset, $limit";
			}
		}

		return $sql;
	}
   
  public function _string() {
    return $this->_SQL();
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
			trigger_logikserror("Database ERROR, DBInstance should be an object of Database");
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
			trigger_logikserror("Database ERROR, DBInstance should be an object of Database");
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
				$jn=array_merge(["as"=>"","type"=>"LEFT"],$jn);
				
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
		if(is_array($str)) return $this->cleanArr($str);

		$str = @trim($str);
		//if(get_magic_quotes_gpc()) {$str=stripslashes($str);}
		//$str=@mysql_real_escape_string($str);

		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a", ";");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z", "%3B");
		$str=str_replace($search, $replace, $str);

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
			elseif(substr($s, 0, 1) == "0") return "'$s'";
			elseif(is_numeric($s)) return $s;
			elseif(is_float($s)) return $s;
			elseif(is_null($s)) return $s;
			elseif(is_bool($s)) return $s;
			elseif(preg_match("/\d{2}\-\d{2}-\d{4}/",str_replace("/","-",$s)) && strlen($s)=="10") return "'"._date($s)."'"; 
			elseif(strpos($s, "()")>1)  return $s;
			else return "'$s'";
		} elseif($sqlType=="int" || $sqlType=="float" || $sqlType=="bool") {
			if(strlen($s)<=0) return "0";
			else return "$s";
		} elseif($sqlType=="date") {
			$s=_date($s);
			return "'$s'";
		} elseif($sqlType=="func") {
			return "$s";
		} else {
			return "'$s'";
		}
	}
	//WHERE Condition Parser
	protected function parseRelation($col,$arr) {
		if(!is_array($arr)) {
			if(in_array($arr[0], ["~","!","@","#"])) {
				switch ($arr[0]) {
					case '^':
							$arr=[
									"OP"=>"SW",
									"VALUE"=>substr($arr, 1)
								];
						break;
					case '!':
							$arr=[
									"OP"=>"NE",
									"VALUE"=>substr($arr, 1)
								];
						break;
					case '@':
							$arr=[
									"OP"=>"FIND",
									"VALUE"=>substr($arr, 1)
								];
						break;
					case '#':
							$arr=[
									"OP"=>"LIKE",
									"VALUE"=>substr($arr, 1)
								];
						break;
					
					default:
						$arr=substr($arr, 1);
						return "{$col}='{$arr}'";
						break;
				}
			} else {
				return "{$col}=".$this->sqlData($arr);
			}
		}
		if(array_key_exists("RAW", $arr)) {
			return "{$arr['RAW']}";
		}
		if(array_key_exists("VALUE",$arr)) $arr[0]=$arr['VALUE'];
		if(array_key_exists("OP",$arr)) $arr[1]=$arr['OP'];

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

			case "le":case ":le:":case "lte":case ":lte:":
			case "<=":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}<={$arr[0]}";
			break;

			case "gt":case ":gt:":
			case ">":
				$arr[0]=$this->sqlData($arr[0]);
				$s="{$col}>{$arr[0]}";
			break;

			case "ge":case ":ge:":case "gte":case ":gte:":
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
			case "sw":case ":sw:":
			case "starts":
				$s="{$col} LIKE '{$arr[0]}%'";
			break;

			case "bn":case ":bn:":
			case "sn":case ":sn:":
				$s="{$col} NOT LIKE '{$arr[0]}%'";
			break;

			case "lw":case ":lw:":
			case "ew":case ":ew:":
			case "ends":
				$s="{$col} LIKE '%{$arr[0]}'";
			break;

			case "ln":case ":ln:":
			case "en":case ":en:":
				$s="{$col} NOT LIKE '%{$arr[0]}'";
			break;
			
			case "cw":case ":cw:":
			case "between":case "like":
				$s="{$col} LIKE '%{$arr[0]}%'";
			break;
			
			case "cn":case ":cn:":
			case "notbetween":case "notlike":
				$s="{$col} NOT LIKE '%{$arr[0]}%'";
			break;
			
			case "s":case ":s:":
			case "find":case ":find:":
				$s="FIND_IN_SET('{$arr[0]}',{$col})";
			break;
				
			case "in":case ":in:":
				if(is_array($arr[0])) {
					foreach($arr[0] as $a=>$b) {
						if(is_string($b)) {
						  $arr[0][$a]="'{$b}'";
						}
					}
					$s="$col IN (".implode(",",$arr[0]).")";
				} else {
					$s="$col IN ({$arr[0]})";
				}
			break;
				
			case "ni":case ":ni:":
				if(is_array($arr[0])) {
					foreach($arr[0] as $a=>$b) {
						if(is_string($b)) {
						  $arr[0][$a]="'{$b}'";
						}
					}
					$s="$col NOT IN (".implode(",",$arr[0]).")";
				} else {
					$s="$col NOT IN ({$arr[0]})";
				}
			break;
				
			case "range":
				if(is_array($arr[0])) {
					if(is_numeric($arr[0][0]) || is_float($arr[0][0])) {
					    $s="$col BETWEEN {$arr[0][0]} AND {$arr[0][1]}";
					  } else {
					    $s="$col BETWEEN '{$arr[0][0]}' AND '{$arr[0][1]}'";
					  }
				} else {
					$s="$col BETWEEN {$arr[0]}";
				}
			break;
			case "rangestr":
				if(is_array($arr[0])) {
					$s="$col BETWEEN '{$arr[0][0]}' AND '{$arr[0][1]}'";
				} else {
					$s="$col BETWEEN {$arr[0]}";
				}
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

