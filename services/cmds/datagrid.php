<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(!isset($_REQUEST['datatype'])) $_REQUEST['datatype']="json";
loadHelpers(array("sqlprint","sqlsrc"));
loadModuleLib("reports","jqgrid");

if($_REQUEST['action']=="load") {
	$src="";
	$id="";
	$tbl="";
	if(isset($_REQUEST['sqlsrc'])) $src=$_REQUEST['sqlsrc'];
	if(isset($_REQUEST['sqlid'])) $id=$_REQUEST['sqlid'];
	if(isset($_REQUEST['sqltbl'])) $tbl=$_REQUEST['sqltbl'];

	if(!isset($_REQUEST['page'])) $_REQUEST['page']="1";
	if(!isset($_REQUEST['rows'])) $_REQUEST['rows']="30";
	if(!isset($_REQUEST['sidx'])) $_REQUEST['sidx']="";
	if(!isset($_REQUEST['sord'])) $_REQUEST['sord']="asc";
	if(!isset($_REQUEST['grp'])) $_REQUEST['grp']="";

	$qArr=getSQLSrc($src,$id,$tbl);

	if($qArr==null || sizeOf($qArr)<=0) {
		exit();
	}
	$table=$qArr["table"];
	$cols=$qArr["cols"];
	$where=$qArr["where"];

	$sys=false;
	if(strpos("#".$table,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
		$sys=true;
	}

	if(strlen($where)>0) $where=processWhere($where);

	$q="SELECT $cols FROM $table";
	$q1="SELECT COUNT(*) AS count FROM $table";

	$page = $_REQUEST['page']; // get the requested page
	$limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
	$sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
	$sord = $_REQUEST['sord']; // get the direction
	$grp = $_REQUEST['grp']; // get the Group By

	if(!$sidx) $sidx =1;
	if($limit==0) $limit=10;
	if($page==null) $page=0;

	if(isset($_REQUEST['_search']) && strlen($_REQUEST['_search'])>0) {
		$r=getWhere($_REQUEST['_search']);
		if(strlen($r)>0) {
			if(strlen($where)>0) {
				$where=$where." AND $r";
			} else {
				$where=" $r ";
			}
		}
	}
	$where=trim($where);
	$n1=strlen($where);
	if(substr($where,0,1)=="(" && substr($where,$n1-1,$n1)==")") {
		//$where=substr($where,1,$n1-2);
	}
	$where1=trim($where);
	$nn=strpos(strtolower($where1)," group by ");
	if(is_numeric($nn) && $nn>1) {
		$where1=substr($where1,0,$nn);
	}
	if(strlen($where1)>0) {
		if(strpos($where1, "WHERE")>0) {
			$q1.=" $where1";
		} else if(strtolower(substr(trim($where1),0,5))!="group") {
			$q1.=" WHERE $where1";
		} else {
			$q1.=" $where1";
		}
	}

	$result = _dbQuery($q1,$sys);
	if(!$result) {
		$arr=array();
		if(MASTER_DEBUG_MODE=="true")
			$arr["MSG"]="Error $q1<br/>"._db()->getError();
		else
			$arr["MSG"]="Error Generating Report Query (1).";
		header("Content-type: application/json");
		exit(json_encode($arr));
	}

	$row = _db()->fetchData($result);
	$count = $row['count'];
	if($limit==-1) {
		$limit=$count+1;
	}
	if( $count >0 ) {
		$total_pages = ceil($count/$limit);
	} else {
		$total_pages = 0;
	}
	if ($page > $total_pages) $page=$total_pages;
	$start = $limit*$page - $limit; // do not put $limit*($page - 1)
	if($start<0) $start=0;

	$SQL =$q;
	if(strlen($where)>0) {
		if(strpos($where1, "WHERE")>0) {
			$SQL.=" $where1";
		} else if(strtolower(substr(trim($where),0,5))!="group") {
			$SQL.=" WHERE $where";
		} else {
			$SQL.=" $where";
		}
	}
	if(strtolower(substr(trim($where),0,5))!="group") {
		if(strlen($grp)>0) $SQL.=" GROUP BY $grp";
		if(!strpos(strtolower($SQL)," order by "))
			$SQL.=" ORDER BY $sidx $sord";
		if(!strpos(strtolower($SQL)," limit "))
			$SQL.=" LIMIT $start , $limit";
	}
	//echo json_encode(array("msg"=>$SQL));

	$result = _dbQuery($SQL,$sys);
	if(!$result) {
		$arr=array();
		if(MASTER_DEBUG_MODE=="true")
			$arr["MSG"]="Error $q1<br/>"._db()->getError();
		else
			$arr["MSG"]="Error Generating Report Query (2).";
		header("Content-type: application/json");
		exit(json_encode($arr));
	}

	$params=$_REQUEST;
	$params["page"]=$page;
	$params["total"]=$total_pages;
	$params["records"]=$count;
	
	if(isset($_REQUEST['src']) && $_REQUEST['src']=="reports") {
		printSQLResult($result, $_REQUEST['datatype'],$params,"",true);//$SQL,$_SERVER['QUERY_STRING']
	} else {
		printSQLResult($result, $_REQUEST['datatype'],$params,"",false);//$SQL,$_SERVER['QUERY_STRING']
	}
	_db()->freeResult($result);
} elseif($_REQUEST['action']=="edit") {
	echo json_encode(array("msg"=>"No Edit Allowed"));
} else {
	echo json_encode(array("msg"=>"No Action Command Found"));
}
?>