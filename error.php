<?php
//Error File For Displaying all Error Informations
require_once ('api/initialize.php');

if(!isset($_REQUEST["code"])) {
	$_REQUEST["code"]=501;
}

$site="";
if(defined("SITENAME")) $site=SITENAME;
elseif(isset($_SESSION["LGKS_SESS_SITE"])) $site=$_SESSION["LGKS_SESS_SITE"];
elseif(isset($_REQUEST['site'])) $site=$_REQUEST['site'];
else $site=DEFAULT_SITE;

if(!defined("BASEPATH"))
	define("BASEPATH", dirname(__FILE__)."/".APPS_FOLDER."$site/");

if(!defined("APPS_COMPANY")) {
	define("APPS_COMPANY",getConfig("APPS_COMPANY"));
}
if(!defined("SITENAME")) {
	define("SITENAME",$site);
}
trigger_ErrorCode($_REQUEST["code"]);
?>
