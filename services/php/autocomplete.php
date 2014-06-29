<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//checkServiceSession();

$src=$_REQUEST["src"];
if($src=="sqltbl") {
	$formCols="";

	$tbl=$_REQUEST["tbl"];
	$cols=$_REQUEST["cols"];
	if(isset($_REQUEST["term"])) $term=$_REQUEST["term"]; else $term="";
	if(isset($_REQUEST["where"])) $where=$_REQUEST["where"]; else $where="";
	if(isset($_REQUEST["groupby"])) $group=$_REQUEST["groupby"]; else $group="";
	if(isset($_REQUEST["orderby"])) $order=$_REQUEST["orderby"]; else $orderby="";
	if(isset($_REQUEST["i"])) $i=$_REQUEST["i"]; else $i=0;
	if(isset($_REQUEST["l"])) $l=$_REQUEST["l"]; else $l=1000;
	if(isset($_REQUEST["form"])) $formCols=$_REQUEST["form"];

	if(strlen($where)>0) {
		loadHelpers("sqlsrc");
		$where=parseSQLWhere($where);
	}
	
	$colArr=explode(",",$cols);
	$col=$colArr[0];
	$sys=false;

	if(strpos("#".$tbl,"lgks")==1) {
		$sys=true;
		if(strlen($where)>0) {
			$where.="site='".SITENAME."' AND $where";
		} else {
			$where.="site='".SITENAME."'";
		}
	}

	if(strlen($formCols)>0) $cols=$cols.",".$formCols;

	$sql="SELECT $cols FROM $tbl";// WHERE $col LIKE '$term%'

	$whrArr=array();
	if(!isset($_REQUEST['match']) || $_REQUEST['match']=="starts") {
		foreach($colArr as $q=>$e) {
			$whrArr[$q]=current(explode(" ",$e))." LIKE '$term%'";
		}
	} else {
		foreach($colArr as $q=>$e) {
			$whrArr[$q]=current(explode(" ",$e))." LIKE '%$term%'";
		}
	}
	
	$sql.=" WHERE (".implode(" OR ",$whrArr).")";

	if(strlen($where)>0) {
		$sql="$sql AND $where";
	}

	if(strlen($group)>0) {
		$sql="$sql GROUP BY $group";
	}
	if(strlen($order)>0) {
		$sql="$sql ORDER BY $order";
	}
	$sql="$sql LIMIT $i,$l";
	//exit("<option>$sql</option>");
	//exit($sql);
	if(count($colArr)>1) {
		$col1=$colArr[1];
	} else {
		$col1=$colArr[0];
	}
	dispatchData($sql,array('label'=>$col,'value'=>$col1),$sys);
} elseif($src=="db") {
	$where=explode(",",$_REQUEST["where"]);
	$qW="";
	$i=0;
	foreach($where as $a=>$b) {
		$e=explode(":",$b);
		$qW.=getRelation($e[1],$e[0],$e[2]);
		if($i<sizeOf($where)-1) $qW.=" AND ";
		$i++;
	}
	$_REQUEST["where"]=$qW;
	$q=generateSelectFromArray($_REQUEST,array("table"=>"tbl"));
	$result=_dbQuery($q);
	dispatchData(_db()->fetchAllData($result,"array"));
	_db()->freeResult($result);
}
exit();
function dispatchData($sql,$cols,$sys=false) {
	$format="json";
	if(isset($_REQUEST['format'])) {
		$format=$_REQUEST['format'];
	}
	if($format=="json") {
		dispatchJSONData($sql,$cols,$sys);
	} elseif($format=="selector") {
		dispatchSelectorData($sql,$cols,$sys);
	} else {
		dispatchJSONData($sql,$cols,$sys);
	}
}
function dispatchSelectorData($sql,$cols,$sys=false) {
	$res=_db($sys)->executeQuery($sql);
	if($res) {
		$arr=array();
		foreach($cols as $a=>$b) {
				$cols[$a]=trim(end(explode(" as ",$b)));
				//$cols[$a]=$cols[$a][0];//if(is_array($cols[$a]) && count($cols[$a])<=1)
		}
		while($rec=_db($sys)->fetchData($res)) {
			$t=$rec[$cols["label"]];
			$n=$rec[$cols["value"]];

			if($n!=$t) $s="$n [$t]";
			else $s=$t;

			$x["data"]=array();
			foreach($rec as $q=>$w) {
				$x["data"][$q]=$w;
			}

			$arr[]=array(
						"name"=>$s,
						"value"=>$t,
						"data"=>$x,
					);
		}
		//printArray($cols);
		foreach($arr as $a=>$b) {
			$n=$b["name"];
			$a=$b["value"];
			$d=json_encode($b["data"]);
			$a=stripslashes($a);
			$n=stripslashes($n);
			//$d=stripslashes($d);
			echo "<option value=\"$a\" data='$d'>$n</option>";
		}
	}
}
function dispatchJSONData($sql,$cols,$sys=false) {
	$res=_db($sys)->executeQuery($sql,$sys);
	if($res) {
		$arr=array();
		while($rec=_db()->fetchData($res)) {
			$t=$rec[$cols["label"]];
			$n=$rec[$cols["value"]];

			if($n!=$t) $s="$n [$t]";
			else $s=$t;

			$x["label"]="$s";
			$x["value"]="$t";
			$x["name"]="$n";

			$x["data"]=array();
			foreach($rec as $q=>$w) {
				$x["data"][$q]=$w;
			}

			array_push($arr,$x);
		}
		echo json_encode($arr);
	}
}
function generateSelectFromArray($arr,$paramsIn=array()) {
	$params=array(
				"table"=>"table","columns"=>"cols","where"=>"where","orderby"=>"orderby","index"=>"index","limit"=>"limit",
			);
	if(count($paramsIn)>0) {
		foreach($paramsIn as $a=>$b) {
			$params[$a]=$b;
		}
	}

	$sql="";
	$sql=_db()->_selectQ($arr[$params["table"]],$arr[$params["columns"]],$arr[$params["where"]],$arr[$params["orderby"]],$arr[$params["limit"]]);
	return $sql;
}
/*
function dispatchData($result,$outType="json") {
	$out=array();
	foreach($result as $a=>$b) {
		$out[sizeOf($out)]=$b["0"];
	}
	if($outType=="json") {
		echo json_encode($out);
	}
}*/
//["c++", "java", "php", "coldfusion", "javascript", "asp", "ruby"]
//["DSC Falcon","Tunaann","Sword Fishann","Dubai Dolphin","Kriti"]
?>
