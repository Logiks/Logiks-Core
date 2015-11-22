<?php

if(!isset($initialized)) {
	include_once ROOT. "config/classpath.php";
	require_once ('bootlogiks.php');

	//logiksRequestPreboot();

	include_once ('commons.php');
	require_once ('configurator.php');

	loadConfigs([
			ROOT . "config/basic.cfg",
			ROOT . "config/php.cfg",
			ROOT . "config/system.cfg",
			ROOT . "config/developer.cfg",
			ROOT . "config/errorlog.cfg",
			ROOT . "config/security.cfg",
			ROOT . "config/folders.cfg",
			ROOT . "config/others.cfg",
			ROOT . "config/xtras.cfg",
			ROOT . "config/framework.cfg",
			ROOT . "config/appPage.cfg",
		]);

	define ('SiteLocation', 'http://localhost/'.InstallFolder);

	require_once ROOT. "api/libs/errorLogs/tests.php";

	LogiksConfig::fixPHPINIConfigs();

	//logiksRequestBoot();

	include_once ROOT. "api/libs/logiksCache/boot.php";
	include_once ROOT. "api/libs/loaders/boot.php";
	include_once ROOT. "api/system.php";
	include_once ROOT. "api/security.php";
	include_once ROOT. "api/app.php";

	include_once ROOT. "api/libs/logiksUser/boot.php";
	include_once ROOT. "api/libs/logiksTemplate/boot.php";

	include_once ROOT. "api/libs/logiksPages/boot.php";

	loadHelpers(array("urltools","hooks","mobility","outputbuffer","shortfuncs"));

	$_SESSION['SESS_ACTIVE_SITE']=SITENAME;
	
	if(!defined("APPS_NAME")) define("APPS_NAME",getConfig("APPS_NAME"));
	if(!defined("APPS_VERS")) define("APPS_VERS",getConfig("APPS_VERS"));
}

?>