<?php
class QueryGenerator {
		
	//Special Queries Generator
	public static function _list_tablesQ($database) {
		return "SHOW TABLES FROM $database";
	}

	public static function _list_columnsQ($table = '') {
		return "SHOW COLUMNS FROM $table";
	}

	public static function _versionQ() {
		return "SELECT version() AS ver";
	}
	
	public static function _recordCountQ($table) {
		return "SELECT count(*) AS count FROM $table";
	}
	
	public static function _truncateQ($table) {
		return "TRUNCATE ".$table;
	}
	
	/**
	 * Select statement
	 *
	 * Generates a platform-specific Select string from the supplied data
	 * 
	 * @author Mita Prava
	 * 
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the Columns
	 * @return	string
	 */
	public static function _selectQ($table, $cols="", $where=array(), $orderby = array(), $limit = FALSE) {
		$sql="SELECT ";
		
		if(is_array($cols)) {
			$sql.=implode(", ", QueryGenerator::_cleanArr($cols))." ";
		} elseif(strlen($cols)==0) {
			$sql.="* ";
		} else {
			$sql.="$cols ";
		}
		$sql.="  FROM " .$table;
		
		if(is_array($where)) {
			$where=QueryGenerator::_cleanArr($where);
			$w="";
			foreach($where as $a=>$b) {
				if(strlen($w)>1) $w.=" AND ";
				$w.="$a='$b' ";
			}
			$sql.=" WHERE $w";
		} elseif(strlen($where)>0) {
			$sql.=" WHERE $where";
		}
		if(is_array($orderby)) {
			$orderby=QueryGenerator::_cleanArr($orderby);
			$sql.=(sizeOf($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';
		} elseif(strlen($orderby)>0) {
			$sql.=" ORDER BY $orderby";
		}
		$sql.=(!$limit) ? '' : ' LIMIT '.$limit;
		
		return $sql;		
	}
	
	
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
	public static function _insertQ($table, $keys, $values) {
		return "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')";
	}
	
	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys=values
	 * @return	string
	 */
	public static function _insertQ1($table, $arr) {
		$keys=array_keys($arr);
		$values=array_values($arr);
		return QueryGenerator::_insertQ($table, $keys, $values);
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
	public static function _replaceQ($table, $keys, $values) {
		return "REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')";
	}
	
	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys=values
	 * @return	string
	 */
	public static function _replaceQ1($table, $arr) {
		$keys=array_keys($arr);
		$values=array_values($arr);
		return "REPLACE INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')";
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
	public static function _insert_batchQ($table, $arr) {
		$sql="INSERT INTO $table ";
		$cols="";
		$vals=array();
		foreach($arr as $a=>$b) {
			$k=array_keys($b);
			$v=array_values($b);
			$cols=implode(",",$k);
			array_push($vals,"(".implode(",",QueryGenerator::_sqlDataArr($v)).")");
		}		
		$sql.="($cols) VALUES ".implode(",",$vals);
		//return "".$table." (".implode(', ', $keys).") VALUES ".implode(', ', $values);
		return $sql;
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
	public static function _updateQ($table, $values, $where, $orderby = array(), $limit = FALSE) {
		foreach($values as $key => $val) {
			$valstr[] = $key." = '".QueryGenerator::_clean($val)."'";
		}

		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;

		$orderby = (count($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';

		$sql = "UPDATE ".$table." SET ".implode(', ', $valstr);
		
		if(is_array($where)) {
			$where=QueryGenerator::_cleanArr($where);
			$w="";
			foreach($where as $a=>$b) {
				if(strlen($w)>1) $w.=" AND ";
				$w.="$a='$b' ";
			}
			$sql.=" WHERE $w";
		} elseif(strlen($where)>0 && $where != '') {
			$sql.=" WHERE $where";
		}
		if(is_array($orderby)) {
			$orderby=_cleanArr($orderby);
			$sql.=(sizeOf($orderby) >= 1)?' ORDER BY '.implode(", ", $orderby):'';
		} elseif(strlen($orderby)>0) {
			$sql.=" ORDER BY $orderby";
		}
		
		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
		$sql.="$limit";
		
		return $sql;
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
	public static function _deleteQ($table, $where = array(), $like = array(), $limit = FALSE) {
		$sql="DELETE FROM $table";
		
		if(is_array($where)) {
			$where=QueryGenerator::_cleanArr($where);
			$w="";
			foreach($where as $a=>$b) {
				if(strlen($w)>1) $w.=" AND ";
				$w.="$a='$b' ";
			}
			$sql.=" WHERE $w";
		} elseif(strlen($where)>0 && $where != '') {
			$sql.=" WHERE $where";
		}
		$limit = ( ! $limit) ? '' : ' LIMIT '.$limit;
		$sql.="$limit";
		
		return $sql;
	}
	
	//All Supportive Functions
	public static function prep_query($sql,$delete_hack=true) {
		// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
		// the query so that it returns the number of affected rows
		if ($delete_hack == TRUE) {
			if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
				$sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
			}
		}
		//$sql=QueryGenerator::_clean($sql);
		return $sql;
	}
	//Function to sanitize values received from the form. Prevents SQL injection
	public static function _clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {$str=stripslashes($str);}
		$str=mysql_real_escape_string($str);
		return $str;
	}
	public static function _cleanArr($arr) {
		foreach($arr as $a=>$b) {
			$arr[$a]=QueryGenerator::_clean($b);
		}
		return $arr;
	}
	
	public static function _limit($sql, $limit, $offset) {
		if ($offset == 0) {
			$offset = '';
		} else {
			$offset .= ", ";
		}
		return $sql."LIMIT ".$offset.$limit;
	}
	public static function _sqlData($s,$sqlType="*") {
		if(is_array($s)) {
			$s=implode(",",$s);
		}
		if($sqlType=="*") {
			$s1=strtolower($s);
			if($s1=="true" || $s1=="false") return strtoupper($s);
			elseif(is_numeric($s)) return $s;
			elseif(is_float($s)) return $s;
			elseif(is_null($s)) return $s;
			elseif(is_bool($s)) return $s;
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
	public static function _sqlDataArr($arr,$sqlType="*") {
		foreach($arr as $a=>$b) {
			$arr[$a]=QueryGenerator::_sqlData($b,$sqlType);
		}
		return $arr;
	}
}
?>
