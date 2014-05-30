<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("createDataSelector")) {
	//Primary Functions To Create Selector Lists
	function createDataSelector($dbLink, $groupID,$allowNone=true,$format="select",$orderBy=null,$params=array()) {
		if(isset($_SESSION['SESS_PRIVILEGE_ID'])) {
			$where="blocked='false' && groupid='$groupID' and (privilege='*' or privilege>='".$_SESSION['SESS_PRIVILEGE_ID']."')";
		} else {
			$where="blocked='false' && groupid='$groupID' and privilege='*'";
		}
		if($orderBy==null) $where.=" ORDER BY title";
		elseif(strlen($orderBy)>0) $where.=" ORDER BY $orderBy";
		return createDataSelectorFromTable(_db(),_dbtable("lists"),"title","value","class",null,$where,null,false,$allowNone,$format,$params);
	}
	function createDataSelectorFromUniques($dbLink, $table, $col1, $col2=null, $where="",
			$concatName=false,$allowNone=true,$format="select",$params=array()) {
		if($col2==null) $col2=$col1;
		$query="select $col1,$col2 from $table";
		if($where!=null && strlen($where)>0) {
			$query.=" WHERE $where GROUP BY $col1";
		} else {
			$query.=" where length($col1)>0 GROUP BY $col1";
		}
		return createDataSelectorFromSQL($dbLink, $query, $col1, $col2, null, $col1, null, $concatName,$allowNone,$format,$params);
	}
	function createDataSelectorFromTable($dbLink, $table, $nameCol, $valCol=null, $classCol=null, $dataCol=null, $where=null,
			$groupBy=null,$concatName=false,$allowNone=true,$format="select",$params=array()) {
		if($valCol==null || strlen($valCol)<=0) {
			$valCol=$nameCol;
		}

		$sql="SELECT $nameCol,$valCol";

		if($classCol!=null && strlen($classCol)>0) {
			$sql.=",$classCol";
		}
		if($dataCol!=null && strlen($dataCol)>0) {
			$sql.=",$dataCol";
		}
		if($groupBy!=null && strlen($groupBy)>0) {
			$sql.=",$groupBy";
		}
		$sql.=" FROM $table";
		if($where!=null && strlen($where)>0) {
			$sql.=" WHERE $where";
		} else {
			$sql.=" where length($nameCol)>0";
		}
		//if($groupBy!=null && strlen($groupBy)>0) {
			//$sql.=" GROUP BY $groupBy";
			//$sql.=" ORDER BY $groupBy DESC";
		//}
		return createDataSelectorFromSQL($dbLink, $sql, $nameCol, $valCol, $classCol, $dataCol, $groupBy, $concatName,$allowNone,$format,$params);
	}

	function createDataSelectorFromSQL($dbLink, $sql, $nameCol, $valCol, $classCol=null, $dataCol=null, $groupCol=null,
			$concatName=false,$allowNone=true,$format="select",$params=array()) {
		if($valCol==null || strlen($valCol)<=0) {
			$valCol=$nameCol;
		}
		if($groupCol!=null && strlen($groupCol)>0) {
			$groupSelector=true;
		} else {
			$groupSelector=false;
		}
		//echo $sql;

		if(isset($params['name'])) {
			$nameX=$params['name'];
		} else $nameX="";

		$result=$dbLink->executeQuery($sql);
		if($allowNone) {
			if($format=="select")
				$outArr[]="<option value='' rel=''>"._ling("None")."</option>";
			elseif($format=="checkbox")
				$outArr[]="<input type=checkbox name='$nameX' rel='' value='' /> <label for=''>None</label>";
			elseif($format=="radio")
				$outArr[]="<input type=radio name='$nameX' rel='' value='' /> <label for=''>None</label>";
		}
		else $outArr=array();
		$lastGroup="";

		$tmpl="";
		if($format=="select")
			$tmpl="<option class='%s' rel='%s' value='%s' >%s</option>";
		elseif($format=="checkbox")
			$tmpl="<input type=checkbox name='$nameX' class='%s' rel='%s' value='%s' /> <label for=''>%s</label>";
		elseif($format=="radio")
			$tmpl="<input type=radio name='$nameX' class='%s' rel='%s' value='%s' /> <label for=''>%s</label>";

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

					if($classCol!=null && strlen($classCol)>0 && isset($record[$classCol])) {
						$c=$record[$classCol];
					} else {
						$c="";
					}
					if($dataCol!=null && strlen($dataCol)>0 && isset($record[$dataCol])) {
						$d=$record[$dataCol];
					} else {
						$d="";
					}

					if(isset($params['class'])) {
						$c=$params['class'];
					}
					if(isset($params['rel'])) {
						$d=$params['rel'];
					}
					$str=sprintf($tmpl,$c,$d,$record[$valCol],_ling($name));

					if($groupSelector) {
						if(isset($record[$groupCol]) && strlen($record[$groupCol])>0) {
							if(!isset($outArr[$record[$groupCol]])) $outArr[$record[$groupCol]]=array();
							array_push($outArr[$record[$groupCol]],$str);
						} else $outArr[]=$str;
					} else {
						$outArr[]=$str;
					}
				}
			}
		}
		$str="";
		foreach($outArr as $a=>$b) {
			if(is_array($b)) {
				if($format=="select") {
					$str.="<optgroup label='".toTitle($a)."' value='$a'>";
					foreach($b as $x) $str.=$x;
					$str.="</optgroup>";
				} else {
					$str.="<span label='".toTitle($a)."' value='$a'>";
					foreach($b as $x) $str.=$x;
					$str.="</span>";
				}
			} else {
				$str.=$b;
			}
		}
		$dbLink->freeResult($result);
		return $str;
	}
}
?>
