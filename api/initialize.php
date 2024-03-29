<?php
/*
 * This file initializes the system into a running system by configuring
 * all the parameters and including the basic required files.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.3
 */

if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($initialized)) {
	ob_start();
	//clearstatcache();
	@session_start();

	$startTime=microtime(true);
	$_SESSION['REQUEST_PAGE_START']=$startTime;

	// Do System Check Once per Installation
	if(!isset($_SESSION['SYSCHECK']) || !is_numeric($_SESSION['SYSCHECK']) || time()-$_SESSION['SYSCHECK']>36000) {
		
		include_once 'syscheck.php';
		
		//Set the current check time
		$_SESSION['SYSCHECK']=time();
	}

	// define("FORCE_HTTPS",true);
	
	include_once 'commons.php';

	$currentCookieParams = session_get_cookie_params();
	$sidvalue = session_id();  
	setcookie(
	    'PHPSESSID',//name
	    $sidvalue,//value
	    0,//expires at end of session
	    $currentCookieParams['path'],//path
	    $currentCookieParams['domain'],//domain
	    isHTTPS(), //secure
	    isHTTPS()?false:true //httponly
	);
	_envData("SESSION",'REQUEST_PAGE_START',$startTime);

	$dirPath=str_replace(_server("DOCUMENT_ROOT"),'',dirname(_server("SCRIPT_FILENAME"))."/");
	$dirPath=substr($dirPath, 1);
	if(!defined("InstallFolder")) define('InstallFolder',$dirPath);

	if(!defined("WEBROOT")) define ('WEBROOT', 'http' . (isHTTPS() ? 's' : '') . '://' . 
			str_replace("//", "/", _server('HTTP_HOST').dirname(_server('SCRIPT_NAME'))."/"));
	
	if(!defined("SiteLocation")) define ('SiteLocation', 'http' . (isHTTPS() ? 's' : '') . '://' . 
			str_replace("//", "/", _server('HTTP_HOST')."/".InstallFolder."/"));
	
	include_once ROOT. "config/classpath.php";

	require_once ('bootlogiks.php');

	logiksRequestPreboot();

	require_once ('configurator.php');

	loadConfigs([
			ROOT . "config/basic.cfg",
			ROOT . "config/php.cfg",
			ROOT . "config/system.cfg",
			ROOT . "config/developer.cfg",
			ROOT . "config/debug.cfg",
			ROOT . "config/errorlog.cfg",
			ROOT . "config/security.cfg",
			ROOT . "config/folders.cfg",
			ROOT . "config/others.cfg",
			ROOT . "config/xtras.cfg",
			ROOT . "config/framework.cfg",
			ROOT . "config/masters/appPage.cfg",
		]);

	if(PRINT_PHP_HEADERS) header("X-Powered-By: ".Framework_Title." [".Framework_Site."]",false);

	require_once ROOT. "api/libs/errorLogs/boot.php";

	LogiksConfig::fixPHPINIConfigs();

	logiksSystemBoot();

	include_once ROOT. "api/libs/logiksCache/boot.php";
	include_once ROOT. "api/libs/loaders/boot.php";
	include_once ROOT. "api/system.php";
	include_once ROOT. "api/security.php";
	include_once ROOT. "api/app.php";

	include_once ROOT. "api/libs/logiksUser/boot.php";
	include_once ROOT. "api/libs/logiksTemplate/boot.php";

	include_once ROOT. "api/libs/logiksPages/boot.php";

	loadHelpers(array("urltools","hooks","mobility","outputbuffer","shortfuncs"));

	loadComposerAutoloaders();

	$initialized=true;
	runHooks("postinit");

	_envData("SESSION",'SESS_ACTIVE_SITE',SITENAME);
	
	if(!defined("APPS_NAME")) define("APPS_NAME",getConfig("APPS_NAME"));
	if(!defined("APPS_VERS")) define("APPS_VERS",getConfig("APPS_VERS"));
}
?>
