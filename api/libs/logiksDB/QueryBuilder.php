<?php
/*
 * Base SQL Query Generator For Logiks
 * other dedicated Geenrators will inherit this class.
 * 
 * Author: Bismay Kumar Mohapatra (bismay@openlogiks.org)
 * Author: Kshyana Prava (mita@openlogiks.org)
 * Version: 2.0
 */

include_once dirname(__FILE__) . "/AbstractQueryBuilder.php";

class QueryBuilder extends AbstractQueryBuilder {
	
	public static function create($instance) {
		return new QueryBuilder($instance);
	}
	
	protected function __construct($instance) {
		parent::__construct($instance);
	}
	
	//CUD stands for Create, Update and Delete.
	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	public function _insertQ($table, $keys, $values) {
		$this->obj['table']=$table;
		$this->obj['cols']=$keys;
		
		if(is_array($keys)) {$keys=implode(', ', $keys);}
		
		if(is_array($values)) {
			$values=$this->sqlDataArr($values);
			$this->sql="INSERT INTO ".$table." ({$keys}) VALUES (".implode(", ", $values).")";
		} elseif(is_a($values,"QueryBuilder")) {
			$values=$values->_sql();
			$this->sql="INSERT INTO ".$table." ({$keys}) VALUES ({$values})";
		} else {
			$this->sql="INSERT INTO ".$table." ({$keys}) VALUES ({$values})";
		}
		return $this;
	}
	
	/**
	 * Insert statement with a single array
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys=values
	 * @return	string
	 */
	public function _insertQ1($table, $arr) {
		$keys=array_keys($arr);
		
		$this->obj['table']=$table;
		$this->obj['cols']=$keys;
		
		$values=array_values($arr);
		$values=$this->cleanArr($values);
		$this->_insertQ($table, $keys, $values);
		return $this;
	}
	
	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	public function _replaceQ($table, $keys, $values) {
		$this->obj['table']=$table;
		$this->obj['cols']=$keys;
		
		$values=$this->cleanArr($values);
		$this->sql="REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')";
		return $this;
	}
	
	/**
	 * Replace statement with input params as array
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys=values
	 * @return	string
	 */
	public function _replaceQ1($table, $arr) {
		$keys=array_keys($arr);
		
		$this->obj['table']=$table;
		$this->obj['cols']=$keys;
		
		$values=array_values($arr);
		$values=$this->cleanArr($values);
		$this->sql="REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')";
		return $this;
	}
	
	/**
	 * Insert_batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	public function _insert_batchQ($table, $arr) {
		if(!isset($arr[0])) {
			trigger_error("Database ERROR, _insert_batchQ needs array of data");
			return false;
		}
		$keys=array_keys($arr[0]);
		
		$this->obj['table']=$table;
		$this->obj['cols']=$keys;
		
		$sql="INSERT INTO $table ";
		$cols="";
		$vals=array();
		foreach($arr as $a=>$b) {
			$v=array_values($b);
			$v=$this->cleanArr($v);
			array_push($vals,"(".implode(",",$this->_sqlDataArr($v)).")");
		}
		
		$sql.="($keys) VALUES ".implode(",",$vals);
		$this->sql=$sql;
		
		return $this;
	}
	
	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @param	array	the orderby clause
	 * @param	array	the limit clause
	 * @return	string
	 */
	public function _updateQ($table, $values, $where, $orderby = array(), $limit = false) {
		$this->obj['table']=$table;
		$this->obj['cols']=array_keys($values);
		//$this->obj['where']=$where;
		
		foreach($values as $key => $val) {
			$valstr[] = $key." = '".parent::clean($val)."'";
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);
		
		$this->sql=trim($sql);
		
		$this->_where($where);
		$this->_orderby($orderby);
		if($limit!==false) $this->_limit($limit);
		
		return $this;
	}
	
	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @param	string	the limit clause
	 * @return	string
	 */
	public function _deleteQ($table, $where = array(), $limit = false) {
		$this->obj['table']=$table;
		//$this->obj['where']=$where;
		
		$sql="DELETE FROM $table";

		$this->sql=trim($sql);
		
		$this->_where($where,"AND");
		if($limit!==false) $this->_limit($limit);
		
		return $this;
	}
	
	/**
	 * Select statement
	 *
	 * Generates a platform-specific Select string from the supplied data
	 *
	 * @access	public
	 * @param	string			the table name
	 * @param	array/string	the Columns
	 * @param	array/string	the Where
	 * @param	array/string	the GroupBy
	 * @param	string			the OrderBy
	 * @param	array/string	the Limit
	 * @return	string
	 */
	public function _selectQ($table, $cols="", $where=null, $groupby = null, $orderby = null, $limit = FALSE) {
		if(is_a($table,"QueryBuilder")) {
			$this->obj=array(
					"table"=>$table->_array(),
					"cols"=>$cols,
					"where"=>[],
					"groupby"=>$groupby,
					"orderby"=>$orderby,
					"limits"=>$limit,
				);
				
			$table="(".$table->_SQL().")";
		} else {
			$this->obj=array(
				"table"=>$table,
				"cols"=>$cols,
				"where"=>[],
				"groupby"=>$groupby,
				"orderby"=>$orderby,
				"limits"=>$limit,
			);
		}
		
		$sql="SELECT ";

		if(is_array($cols)) {
			$sql.=implode(", ", $this->cleanArr($cols))." ";
		} elseif(strlen($cols)==0) {
			$sql.="* ";
		} else {
			$sql.="$cols ";
		}
		$sql.=" FROM ".$table;
		
		$this->sql=trim($sql);
		
		$this->_where($where,"AND");
		$this->_groupby($groupby);
		$this->_orderby($orderby);
		if($limit!==false) $this->_limit($limit);
		
		return $this;
	}
}
?>
