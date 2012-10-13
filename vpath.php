<?php
function analyzeQuery() {
	$relRoot=dirname($_SERVER["SCRIPT_NAME"])."/";
	$relRoot=str_replace("//","/",$relRoot);
	
	$uri=$_SERVER["REQUEST_URI"];
	$relUri=substr($uri,strlen($relRoot));
	
	$qr=explode("?",$relUri);
	if(!isset($qr[1])) $qr[1]="";
	$relUri=$qr[0];
	$qUri=$qr[1];
	
	$comps=explode("/",$relUri);
	
	if(isset($comps[0])) {
		$_REQUEST['site']=$comps[0];
		unset($comps[0]);
	}
	$_REQUEST['page']=implode("/",$comps);
	
	$ruri="{$relRoot}index.php?site={$_REQUEST['site']}";
	if(strlen($_REQUEST['page'])>0) {
		$ruri.="&page={$_REQUEST['page']}";
	}
	if(strlen($_SERVER['QUERY_STRING'])>0) {
		$ruri.="&".$_SERVER['QUERY_STRING'];
	}
	$_SERVER['REQUEST_PATH']=$relUri;
	$_SERVER['REQUEST_URI']=$ruri;
}
function debugQuery() {
	print_r($_SERVER);
	print_r($_REQUEST);
	echo "<hr/>";
	echo "{$_REQUEST['site']} :: {$_REQUEST['page']}";
	exit();
}
analyzeQuery();
//debugQuery();
if($_REQUEST['site']=="services") {
	$pg=str_replace("/",".",$_REQUEST['page']);
	$_REQUEST['scmd']=$pg;
	unset($_REQUEST['site']);
	unset($_SESSION['LGKS_SESS_SITE']);
	unset($_COOKIE['LGKS_SESS_SITE']);
	chdir("services");
	include "index.php";
} else {
	include "index.php";
}
?>
