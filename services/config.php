<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

require_once (ROOT . 'api/configurator.php');

LoadConfigFile(ROOT . "config/basic.cfg");
LoadConfigFile(ROOT . "config/system.cfg");
LoadConfigFile(ROOT . "config/xtras.cfg");
LoadConfigFile(ROOT . "config/headers.cfg");
LoadConfigFile(ROOT . "config/framework.cfg");	
LoadConfigFile(ROOT . "config/db.cfg");
LoadConfigFile(ROOT . "config/folders.cfg");
LoadConfigFile(ROOT . "config/services.cfg");
LoadConfigFile(ROOT . "config/security.cfg");

fixPHPINIConfigs();
fixLogiksVariables();

include_once ROOT. "api/commons.php";
include_once ROOT. "config/classpath.php";
include_once ROOT. "api/libs/URLTools.php";

include_once ROOT. "api/loaders.php";
include_once ROOT. "api/security.php";
include_once ROOT. "api/usersettings.php";
//include_once ROOT. "api/rolemodel.php";
include_once ROOT. "api/system.php";
include_once ROOT. "api/database.inc";

include ROOT."config/errors.php";

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

$errorImg="<img src=" . SiteLocation . "services/images/error.png  width=48 height=48>";
$loadImg="<img src=" . SiteLocation . "services/images/loading.gif width=200 height=20>";
$bugImg="<img src=" . SiteLocation . "services/images/bug.png width=48 height=48>";

$cmdFormat=explode(",",SUPPORTED_COMMAND_FORMATS);

//Loading Special Access Commands
$arrSpecialCmds=array();
$accessData=file_get_contents(ROOT."config/lists/services_access.lst");
$accessData=explode("\n",$accessData);
foreach($accessData as $s) {
	if(strlen($s)>0 && substr($s,0,1)!="#") {
		array_push($arrSpecialCmds,$s);
	}
}
?>
