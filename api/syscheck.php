<?php
/*
 * For doing some basic system check once per session. This is done so that there is no issue while running 
 * logiks apps.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
	
//Check Supported PHP Version
if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);

		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if(PHP_VERSION_ID < 50600) {
	sysCheckPrint("PHP version 5.6 or above is required to run Logiks 4.0.","Please upgrade to continue.");
}

//Check some important extensions.
$checkExtensions=array(
		"cURL PHP Extension is required"=>"func:curl_init",
		"MCrypt PHP Extension is required"=>"library:mcrypt",
		"Mbstring PHP Extension is required"=>"library:mbstring",
		"OpenSSL PHP Extension is required"=>"library:openssl",
		"ZipArchive PHP Library is required"=>"class:ZipArchive",
// 		"GD PHP Library is required"=>"library:gd",
	);
$errorMsg=[];
$noProceed=false;

foreach ($checkExtensions as $msg=>$extension) {
	$extension=explode(":",$extension);
	switch($extension[0]) {
		case "library":
			if(!extension_loaded($extension[1])) {
				$errorMsg[]=$msg;
				$noProceed=true;
			}
			break;
		case "class":
			if(!class_exists($extension[1])) {
				$errorMsg[]=$msg;
				$noProceed=true;
			}
			break;
		case "func":
			if(!function_exists($extension[1])) {
				$errorMsg[]=$msg;
				$noProceed=true;
			}
			break;
	}
}
if($noProceed) {
	sysCheckPrint("Important extension missing in PHP, please install them before continuing.",$errorMsg);
}


//Check Installation for basic config files
$bpath=dirname(dirname(__FILE__));
if(!file_exists("$bpath/config/basic.cfg")) {
	if(file_exists("$bpath/install/")) {
		if(php_sapi_name() != 'cli' ) {
			echo "Initiating Installation Sequence ...";
			header("Location:install/");
		} else {
			sysCheckPrint("Complete Installation First","");
		}
	} else {
		sysCheckPrint("Error In Logiks Installation Or Corrupt Installation.","Please Contact Admin.");
	}
}


//Check tmp directories permissions
if(!is_writable("$bpath/tmp")) {
	sysCheckPrint("Error In Logiks TMP Directory, its not writable.","tmp/ directory");
}


//Check some important files.
$checkFiles=array(
		
	);
foreach ($checkFiles as $fx) {
	if(!file_exists($fx)) {
		sysCheckPrint("Important file missing, please check the installation.",basename($fx));
	}
}
//sysCheckPrint("","");
function sysCheckPrint($msg1,$msg2) {
	$isCLI = (php_sapi_name() == 'cli' );
	if($isCLI) {
		echo "\n";
		echo $msg1."\n";
		if(is_array($msg2)) {
			foreach($msg2 as $m) {
				echo "\t+ ".$m."\n";
			}
		} else {
			echo "\t".$msg2."\n";
		}
		echo "\n";
	} else {
		echo "<h1 align=center style='color:#BF2E11'>{$msg1}</h1>";
		if(is_array($msg2)) {
			echo "<hr>";
			foreach($msg2 as $m) {
				echo "<p align=center style='color:#444;'>{$m}</p>";
			}
		} else {
			echo "<h3 align=center style='color:#444;'>Error : {$msg2}</h3>";
		}
	}
	exit();
}
if(php_sapi_name() == 'cli' ) {
	echo "\nInstallation is all done. Visit the site on browser to continue.\n\n";
	exit();
}
?>