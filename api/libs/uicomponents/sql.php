<?php
if (!defined('ROOT')) exit('No direct script access allowed');
if(!function_exists("createDataSelector")) {
	//Primary Functions To Create Selector Lists
	function createDataSelector($dbLink, $groupID,$allowNone=true) {
		if(isset($_SESSION['SESS_PRIVILEGE_ID'])) {
			$where="groupid='$groupID' and (privilege='*' or privilege>='".$_SESSION['SESS_PRIVILEGE_ID']."')";
		} else {
			$where="groupid='$groupID' and privilege='*'";
		}
		$where.=" ORDER BY id";
		return createDataSelectorFromTable(_db(),_dbtable("lists"),"title","value","class",$where,null,false,$allowNone);
	}	
	function createDataSelectorFromUniques($dbLink, $table, $col1, $col2=null, $where="", $concatName=false,$allowNone=true) {
		if($col2==null) $col2=$col1;
		$query="select $col1,$col2 from $table";
		if($where!=null && strlen($where)>0) {
			$query.=" WHERE " . $where;
		} else {
			$query.=" where length($col1)>0";
		}
		$query.=" group by $col1";
		return createDataSelectorFromSQL($dbLink, $query, $col1, $col2, null, null, $concatName,$allowNone);
	}
	function createDataSelectorFromTable($dbLink, $table, $nameCol, $valCol=null, $classCol=null, $where=null, 
			$groupBy=null, $concatName=false,$allowNone=true) {
		if($valCol==null || strlen($valCol)<=0) {
			$valCol=$nameCol;
		}
		
		$sql="SELECT $nameCol,$valCol";
		
		if($classCol!=null && strlen($classCol)>0) {
			$sql.=",$classCol";
		}
		$sql.=" FROM $table";
		if($where!=null && strlen($where)>0) {
			$sql.=" WHERE $where";
		} else {
			$sql.=" where length($nameCol)>0";
		}		
		if($groupBy!=null && strlen($groupBy)>0) {
			$sql.=" GROUP BY $groupBy";
			$sql.=" ORDER BY $nameCol DESC";
		}				
		return createDataSelectorFromSQL($dbLink, $sql, $nameCol, $valCol, null, $classCol, $concatName,$allowNone);
	}
	
	function createDataSelectorFromSQL($dbLink, $sql, $nameCol, $valCol, $classCol=null, $groupCol=null, 
			$concatName=false,$allowNone=true) {
		if($valCol==null || strlen($valCol)<=0) {
			$valCol=$nameCol;
		}
		if($groupCol==null) {
			$groupSelector=false;
		} else {
			$groupSelector=true;
		}
		$result=$dbLink->executeQuery($sql);
		if($allowNone) $str="<option value=''>"._ling("None")."</option>\n";
		else $str="";
		$lastGroup="";
		if($result) {
			if($dbLink->recordCount($result)>0){
				while($record=$dbLink->fetchData($result)) {
					if($record[$valCol]==null){
						$record[$valCol]=$record[$nameCol];
					}
					$name=$record[$nameCol];
					$name=str_replace('_',' ',$name);
					$name=ucwords($name);
					$name=_ling($name);
					if($concatName) $name=$name . " [".$record[$valCol]."]";
					
					if($classCol!=null && strlen($classCol)>0) {
						$c=$record[$classCol];
					} else {
						$c="";
					}
					if($groupSelector) {
						//Group Selectors
					}
					if(strlen($c)>0) $str.="<option class='$c' value='$record[$valCol]'>$name</option>\n";
					else $str.="<option value='$record[$valCol]'>$name</option>\n";
				}
			}
		}
		$dbLink->freeResult($result);
		return $str;
	}
}
?>
