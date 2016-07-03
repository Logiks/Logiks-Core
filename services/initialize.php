<?php
/*
 * This file initializes the system into a running system by configuring
 * all the parameters and including the basic required files.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.2
 */

if(!defined('ROOT')) exit('No direct script access allowed');


session_start();
ob_start();

_envData("SESSION",'REQUEST_SERVICE_START',microtime(true));

define('ROOT_RELATIVE',"../");
define('SERVICE_ROOT',dirname(__FILE__) . "/");
define('SERVICE_PATH',dirname(_server('SCRIPT_NAME'))."/");

define ('SERVICE_HOST', 'http' . (_server('HTTPS') ? 's' : '') . '://' . _server('HTTP_HOST').dirname(_server('SCRIPT_NAME'))."/");
define ('WEBROOT', 'http' . (_server('HTTPS') ? 's' : '') . '://' . _server('HTTP_HOST').dirname(_server('SCRIPT_NAME'))."/");

if(!_server("HTTP_REFERER")) _envData("SERVER","HTTP_REFERER","");

/*
 * Enable Debug mode
 */
$isDebug = array_key_exists('debug', $_REQUEST);
if($isDebug) {
    ini_set('display_errors', 1);
    error_reporting(1);
    define("MASTER_DEBUG_MODE",true);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

include_once ROOT. "config/classpath.php";

include_once ROOT. "api/bootlogiks.php";

logiksRequestPreboot();

include_once SERVICE_ROOT. "ServiceAuthEngine.inc";
include_once SERVICE_ROOT. "api.php";
include_once ROOT. "api/configurator.php";

loadConfigs([
			ROOT . "config/basic.cfg",
			ROOT . "config/php.cfg",
			ROOT . "config/system.cfg",
			ROOT . "config/developer.cfg",
			ROOT . "config/debug.cfg",
			ROOT . "config/services.cfg",
			ROOT . "config/errorlog.cfg",
			ROOT . "config/security.cfg",
			ROOT . "config/others.cfg",
			ROOT . "config/xtras.cfg",
			ROOT . "config/folders.cfg",
			//ROOT . "config/framework.cfg",
		]);
LogiksConfig::fixPHPINIConfigs();

$dirPath=str_replace(_server("DOCUMENT_ROOT"),'',dirname(dirname(_server("SCRIPT_FILENAME")))."/");
$dirPath=substr($dirPath, 1);
if(!defined("InstallFolder")) define('InstallFolder',$dirPath);

if(!defined("WEBROOT")) define ('WEBROOT', 'http' . (_server('HTTPS') ? 's' : '') . '://' . 
			str_replace("//", "/", _server('HTTP_HOST').dirname(_server('SCRIPT_NAME'))."/"));
if(!defined("SiteLocation")) define ('SiteLocation', 'http' . (_server('HTTPS') ? 's' : '') . '://' . 
		str_replace("//", "/", _server('HTTP_HOST')."/".InstallFolder."/"));

require_once ROOT. "api/libs/errorLogs/boot.php";

logiksSystemBoot();

header("X-Powered-By: Logiks [http://openlogiks.org]",false);
header("SESSION-KEY:".session_id(),false);
header("Access-Control-Allow-Origin:*");

//Origin
//Access-Control-Allow-Methods:OPTIONS,GET,POST,PUT,DELETE
//Access-Control-Allow-Headers:Content-Type, Authorization, X-Requested-With
//header("Access-Control-Allow-Headers", "access-control-allow-origin, accept, access-control-allow-methods, access-control-allow-headers, x-random-shit");
//header("X-Powered-By: ".Framework_Title." [".Framework_Site."]",false);
//print_r($GLOBALS['LOGIKS']["_SERVER"]);exit();

include_once ROOT. "api/libs/logiksCache/boot.php";

include_once ROOT. "api/libs/loaders/boot.php";
include_once ROOT. "api/system.php";
include_once ROOT. "api/security.php";
include_once ROOT. "api/app.php";

logiksServiceBoot();

include_once ROOT. "api/libs/logiksUser/boot.php";
include_once ROOT. "api/libs/logiksTemplate/boot.php";

include_once SERVICE_ROOT. "ServiceController.inc";

_envData("SESSION",'SERVICE',true);
_envData("SESSION",'SESS_ACTIVE_SITE',SITENAME);

ini_set("error_reporting",getConfig("SERVICE_ERROR_REPORTING"));

loadHelpers(array("urltools","hooks","mobility","formatprint","shortfuncs"));

runHooks("serviceInit");
?>
