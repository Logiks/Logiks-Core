<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

loadSysConfigs();

fixLogiksVariables();

include_once ROOT. "api/commons.php";
include_once ROOT. "config/classpath.php";
include_once ROOT. "api/libs/URLTools.php";

include_once ROOT. "api/loaders.php";
include_once ROOT. "api/security.php";
include_once ROOT. "api/usersettings.php";
//include_once ROOT. "api/rolemodel.php";
include_once ROOT. "api/system.php";
include_once ROOT. "api/user.php";
include_once ROOT. "api/database.inc";

include_once ROOT."config/errors.php";

include_once ROOT. "api/logdb.php";//For Apps Events
include_once "serviceErrors.php";//Service Error Handling System

function __autoload($class) {
	global $classpath;
	if(strpos(strtolower(" " . $class),"smarty_")>0) {
		if(function_exists("smartyAutoload")) {
			smartyAutoload($class);
			return;
		}
	}
	$name1=$class;
	$name2=strtolower($class);
	
	$found=false;	
	foreach($classpath as $p) {
		$s1=ROOT.$p.$name1.".inc";
		$s2=ROOT.$p.$name2.".inc";
		if(file_exists($s1)) {
			include_once $s1;
			$found=true;
			break;
		} elseif(file_exists($s2)) {
			include_once $s2;
			$found=true;
			break;
		}
	}
	//echo "$class $found <br/>";
	if(!$found && SERVICE_DEBUG=="true") trigger_error("$class Class Not Found within given paths");
}

function getServiceCtrlConfig() {
	$jsonDb=array();
	
	if(defined("APPS_CONFIG_FOLDER"))
		$f1=APPROOT.APPS_CONFIG_FOLDER."services.json";
	else
		$f1="";
	$f2=ROOT.CFG_FOLDER."services.json";
	$db1=array();$db2=array();
	if(strlen($f1)>0 && file_exists($f1)) {
		$d1=file_get_contents($f1);
		$db1=json_decode($d1,true);
	}
	if(strlen($f2)>0 && file_exists($f2)) {
		$d1=file_get_contents($f2);
		$db2=json_decode($d1,true);
	}
	if($db1==null) $db1=array();
	if($db2==null) $db2=array();
	
	$jsonDb=array_merge($db2,$db1);
	return $jsonDb;
}

function loadSysConfigs() {
	LoadConfigFile(ROOT . "config/system.cfg");
	LoadConfigFile(ROOT . "config/xtras.cfg");
	LoadConfigFile(ROOT . "config/headers.cfg");
	LoadConfigFile(ROOT . "config/framework.cfg");	
	LoadConfigFile(ROOT . "config/db.cfg");
	LoadConfigFile(ROOT . "config/folders.cfg");
	
	fixPHPINIConfigs();
}
function loadAppConfigs() {
	$apps_cfg=APPROOT."apps.cfg";
	if(file_exists($apps_cfg)) {
		LoadConfigFile($apps_cfg);
		loadConfigDir(APPROOT."config/",true);
	}
}

//Handling Encoded/Encrypted QUERY_STRINGS
if(isset($_REQUEST['encoded'])) {
	$query=$_REQUEST['encoded'];
	$queryo=decryptURL($query);
	$query=explode("&",$queryo);
	foreach($query as $q) {
		$q=explode("=",$q);
		if(count($q)==0) {
		} elseif(count($q)==1) {
			$_REQUEST[$q[0]]="";
		} else {
			$qs=$q[0];
			unset($q[0]);
			$qv=implode("=",$q);
			$_REQUEST[$qs]=$qv;
		}
	}
	$_SERVER['QUERY_STRING'].="&{$queryo}";
}

loadHelpers("shortfuncs");
loadHelpers("formatPrint");

if(ENABLE_AUTO_HOOKS=="true") {
	loadHelpers("hooks");
}

$errorImg="<img src='" . SiteLocation . "services/images/error.png'  width=48 height=48>";
$loadImg="<img src='" . SiteLocation . "services/images/loading.gif' width=200 height=20>";
$bugImg="<img src='" . SiteLocation . "services/images/bug.png' width=48 height=48>";

$cmdFormat=explode(",",SUPPORTED_COMMAND_FORMATS);

if(!isset($_REQUEST['format'])) {
	$_REQUEST['format']="html";
} else {
	$_REQUEST['format']=strtolower($_REQUEST['format']);
}
?>
