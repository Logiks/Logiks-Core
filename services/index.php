<?php
if(defined('ROOT')) exit('Only Direct Access Is Allowed');

//Service Handler For Logiks 3.0+
//Commands : scmd,stype,enc,format
//Formats : html(table,list,select), json, xml, raw

//ob_start();
//ob_start('ob_gzhandler');

ini_set ("display_errors", "strerr");
ini_set("error_reporting", E_ALL); 

session_start();

$defSite='default';
$predefinedSite=true;

if(isset($_REQUEST['site'])) {
	$GLOBALS["CURRENT_SITE"]=$_REQUEST['site'];
} elseif(isset($_SESSION['LGKS_SESS_SITE'])) {
	$GLOBALS["CURRENT_SITE"]=$_SESSION['LGKS_SESS_SITE'];
} elseif(isset($_SERVER["HTTP_REFERER"])) {
	$pos1=strpos($_SERVER["HTTP_REFERER"],"site=");
	if($pos1>0) {
		$d1=substr($_SERVER["HTTP_REFERER"],$pos1);
		$pos2=strpos($d1,"&");
		if($pos2>0) {
			$d1=substr($d1,0,$pos2);
		}
		$pos3=strpos($d1,"=")+1;
		$d1=substr($d1,$pos3);
		$GLOBALS["CURRENT_SITE"]=$d1;
	} else {
		$predefinedSite=false;
		$GLOBALS["CURRENT_SITE"]=$defSite;
	}
} else {
	$predefinedSite=false;
	$GLOBALS["CURRENT_SITE"]=$defSite;
}
//Untill Now $GLOBALS["CURRENT_SITE"] is available for all

if(!defined('ROOT')) {
	define('ROOT',dirname(dirname(__FILE__)) . '/');
}
if(!defined('ROOT_RELATIVE')) {
	define('ROOT_RELATIVE',"../");
}
if(!defined('SERVICE_FOLDER')) {
	define('SERVICE_FOLDER',dirname(__FILE__) . "/");
}
if(!defined('SERVICE_HOST')) {
	$sa=dirname(__FILE__);
	$sa=str_replace($_SERVER['DOCUMENT_ROOT'],"",$sa);
	define('SERVICE_HOST', "http://".$_SERVER['HTTP_HOST'] . "$sa/?");
	unset($sa);
}

include_once "config.php";
include_once "api.php";
include_once "ServiceSecurity.inc";
include_once "ServiceController.inc";

$sysdbLink=new Database();
$sysdbLink->connect();
$appdbLink=null;

if(!$predefinedSite) {
	if(!checkServiceSession(false)) {
		$dm=new DomainMap(_db(true));		
		$GLOBALS["CURRENT_SITE"]=$dm->checkServiceHost();
	}
}

if(!isset($_SERVER["HTTP_REFERER"])) $_SERVER["HTTP_REFERER"]="";

$_REQUEST['site']=$GLOBALS["CURRENT_SITE"];
$_SESSION["LGKS_SESS_SITE"]=$GLOBALS["CURRENT_SITE"];

define("SITENAME",$GLOBALS["CURRENT_SITE"]);
define("APPROOT",ROOT . APPS_FOLDER . SITENAME . "/");
define("WEBAPPROOT",SiteLocation . "apps/" . SITENAME . "/");
define("BASEPATH",APPS_FOLDER . SITENAME . "/");
define("REVERTLINK",$_SERVER["HTTP_REFERER"]);

runSysHooks("servicePreProcess");

$ctrl=new ServiceController();
$secure=new ServiceSecurity();

$secure->isBlacklisted(true);

if(!in_array($_REQUEST["scmd"],$arrSpecialCmds)) {
	if(!isset($_SESSION["LGKS_SESS_SITE"])) {
		$secure->isRemoteKeyValid(true);
		$secure->checkHTTPReferenceLocks(true);
	}
}
//loadHelpers("urlkit");

$ctrl->checkQuery();
$request=$ctrl->preProcessQuery();
$request=$secure->checkSecurity($request);
$request=$ctrl->cleanRequest($request);

$secure->cleanSecurityConfigs();

$apps_cfg=APPROOT."apps.cfg";
if(file_exists($apps_cfg)) {
	LoadConfigFile($apps_cfg);
	loadConfigDir(APPROOT."config/",true);
}

DataBus::singleton();
function __cleanup() {
		DataBus::singleton()->dumpToSession();
		if(_db(true)->isOpen()) _db(true)->close();
		if(_db()->isOpen()) _db()->close();
		//echo PHP_EOL;
}
register_shutdown_function("__cleanup");

runHooks("serviceOnRequest");
$ctrl->executeRequest($request);
runHooks("serviceAfterRequest");
exit();
?>
