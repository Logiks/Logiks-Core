<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(isset($_REQUEST["action"])) {
	if($_REQUEST["action"]=="sitelist") {
		if($_SESSION['SESS_PRIVILEGE_ID']<3) {
			$fs=scandir(ROOT.APPS_FOLDER);
			unset($fs[0]);unset($fs[1]);
			foreach($fs as $a=>$b) {
				unset($fs[$a]);
				if(!is_file(ROOT.APPS_FOLDER.$b)) $fs[$b]=$b;
			}
			printFormattedArray($fs);
		} else {
			if(!defined("ADMIN_APPSITES")) {
				$f=ROOT.CFG_FOLDER."lists/adminsites.lst";
				$f=file_get_contents($f);
				$f=explode("\n",$f);
				if(strlen($f[count($f)-1])==0) unset($f[count($f)-1]);
				define("ADMIN_APPSITES",implode(",",$f));
			}
			$acp=explode(",",ADMIN_APPSITES);
			$fs=$_SESSION['SESS_ACCESS_SITES'];
			foreach($fs as $a=>$b) {
				unset($fs[$a]);
				if(!in_array($b, $acp)) $fs[$b]=$b;
			}
			printFormattedArray($fs);
		}
	} elseif($_REQUEST["action"]=="privilegelist") {
		$s="";
		if(isset($_REQUEST["forsite"])) $s=$_REQUEST["forsite"];
		$sql="SELECT id,name FROM "._dbtable("privileges",true)." where blocked='false' and site='*'";
		if(strlen($s)>0) $sql.=" or site='$s'";
		$r=_dbQuery($sql,true);
		if($r) {
			$a=_db(true)->fetchAllData($r);
			$o=array();
			foreach($a as $x=>$c) {
				$o[$c["name"]]=$c["id"];
			}
			printFormattedArray($o);
			_db(true)->freeResult($r);
		}
	} elseif($_REQUEST["action"]=="accesslist") {
		$s="";
		if(isset($_REQUEST["forsite"])) $s=$_REQUEST["forsite"];
		$sql="SELECT id,master FROM "._dbtable("access",true)." where blocked='false' and sites='*'";
		if(strlen($s)>0) $sql.=" or sites LIKE '%$s%'";
		$r=_dbQuery($sql,true);
		if($r) {
			$a=_db(true)->fetchAllData($r);
			$o=array();
			foreach($a as $x=>$c) {
				$o[$c["master"]]=$c["id"];
			}
			printFormattedArray($o);
			_db(true)->freeResult($r);
		}
	}
	exit();
}
if(isset($_REQUEST["mode"])) {
	if(!isset($_REQUEST['s'])) {
		printErr("WrongFormat","Requested Format Ommits Required Fields.");
		exit();
	}
	checkUserSiteAccess($_REQUEST['s'],true);
	
	$mode=$_REQUEST["mode"];
	if(!($mode=="view" || $mode=="create" || $mode=="edit" || $mode=="totedit" || $mode=="infoedit")) $mode="view";
	
	$_SESSION["USER_DETAILS_MODE"]=$mode;
	$_SESSION["USER_DETAILS_USERID"]="";
	$_SESSION["USER_DETAILS_SITE"]=SITENAME;
	$_SESSION["USER_DETAILS_ID"]=0;
	
	if(isset($_REQUEST["uid"])) $_SESSION["USER_DETAILS_USERID"]=$_REQUEST["uid"];
	if(isset($_REQUEST["rid"])) $_SESSION["USER_DETAILS_ID"]=$_REQUEST["rid"];
	if(isset($_REQUEST["s"])) $_SESSION["USER_DETAILS_SITE"]=$_REQUEST["s"];
	
	if($_SESSION["USER_DETAILS_USERID"]!=$_SESSION['SESS_USER_ID']) {
		isAdminSite();
	}
	
	$fp=ROOT.PAGES_FOLDER."userdetails.php";
	if(file_exists($fp)) {
		include $fp;
	} else {
		printErr("NotImplemented","The User Details Page Could Not Be Found.");
	}
	exit();
}
printErr("WrongFormat","The Requested Command Is In Bad Format.");
exit();
?>
