<?php
/*
 * For doing some basic system check once per session. This is done so that there is no issue while running 
 * logiks apps.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

if(!isset($_SESSION['SYSCHECK']) || !is_numeric($_SESSION['SYSCHECK']) || time()-$_SESSION['SYSCHECK']>3600) {

	//Check Supported PHP Version
	if (!defined('PHP_VERSION_ID')) {
	    $version = explode('.', PHP_VERSION);

	    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}

	if(PHP_VERSION_ID < 50600) {
	    echo "<h1 align=center style='color:#BF2E11'>PHP version 5.6 or above is required to run Logiks 4.0.</h1>";
	   	echo "<h3 align=center style='color:#444;'>Please upgrade to continue</h3>";
	    exit();
	}

	//Check Installation
	$bpath=dirname(dirname(__FILE__));
	if(!file_exists("$bpath/config/basic.cfg")) {
		if(file_exists("$bpath/install/")) {
			echo "Initiating Installation Sequence ...";
			header("Location:install/");
		} else {
			echo "<h1 align=center style='color:#BF2E11'>Error In Logiks Installation Or Corrupt Installation.</h1>";
			echo "<h3 align=center style='color:#444;'>Please Contact Admin.</h3>";
		}
		exit();
	}

	//Check tmp directories permissions
	if(!is_writable("$bpath/tmp")) {
		echo "<h1 align=center style='color:#BF2E11'>Error In Logiks TMP Directory, its not writable.</h1>";
		exit();
	}

	//Check some important files.
	$checkFiles=array(
			
		);
	foreach ($checkFiles as $fx) {
		if(!file_exists($fx)) {
			echo "<h1 align=center style='color:#BF2E11'>Important file missing, please check the installation.</h1>";
			echo "<h3 align=center style='color:#444;'>Missing : ".basename($fx)."</h3>";
			exit();
		}
	}



	//Set the current check time
	$_SESSION['SYSCHECK']=time();
	//echo "DONE SYS CHECK<br><br>";
}
?>