<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();
isAdminSite();

//checkUserSiteAccess($_REQUEST['forsite'],true);

if(!isset($_REQUEST['datatype'])) $_REQUEST['datatype']="json";
loadHelpers(array("sqlprint","sqlsrc"));
loadModuleLib("reports","jqgrid");

$dbCon=LogDB::singleton()->getLogDBCon();

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
	
	if(strlen($where)>0) {
		$q1.=" WHERE $where";
	}
	$result = $dbCon->executeQuery($q1);
	$row = $dbCon->fetchData($result);
	$dbCon->freeResult($result);
	
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
		$SQL.=" WHERE $where";
	}
	if(strlen($grp)>0) $SQL.=" GROUP BY $grp";
	$SQL.=" ORDER BY $sidx $sord";
	$SQL.=" LIMIT $start , $limit";
	
	$result = $dbCon->executeQuery($SQL);
	
	//$SQL = "$q WHERE 1=1 $where ORDER BY $sidx $sord LIMIT $start , $limit";//echo $SQL;
	//$_SERVER["QUERY_STRING"],$_REQUEST['filters']//_db($sys)->getdbName(),$SQL
	
	$params=$_REQUEST;
	$params["page"]=$page;
	$params["total"]=$total_pages;
	$params["records"]=$count;	
	//exit($SQL);
	//$_REQUEST['datatype']="json";
	
	printSQLResult($result, $_REQUEST['datatype'],$params,"");//$SQL,$_SERVER['QUERY_STRING']
	$dbCon->freeResult($result);
} else {
	echo json_encode(array("msg"=>"Action Command Not Supported"));
}
exit();
?>
