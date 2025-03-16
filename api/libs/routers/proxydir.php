<?php
/*
 * A simple router file for routing requests into the app
 * Mostly used by developers for ease of deployment and controls.
 * Good for designers
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 07/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

$page=PAGE;

$page=explode("/",$page);
$page=current($page);

// APP_TYPE - logiks-cordova, logiks-nodejs, logiks-react

//printArray([APPROOT,APPS_PAGES_FOLDER,APP_TYPE]);
$dirBase=array(
		APPROOT."public/",
		APPROOT."www/",
	);

define("TEST_ROOT", "TEST");

ob_clean();

foreach ($dirBase as $dir) {
	if(is_dir($dir)) {//file_exists($dir."index.html")
		if(strlen($page)==0) $page ="index.html";

		// echo $dir . PAGE;exit();
		if(file_exists($dir . $page) && !is_dir($dir . $page)) include_once $dir . $page;
		elseif(file_exists($dir . PAGE)) {
			$ext = explode(".", PAGE);
			$ext = end($ext);
			
			loadHelpers("mimes");
			
			printMimeHeader($ext);
			readfile($dir.PAGE);
			exit();
		} else {
			// println($dir . PAGE);
			$ext = explode(".", PAGE);
			$ext = end($ext);
			
			loadHelpers("mimes");

			printMimeHeader($ext);
			echo "";
			// echo "404";
			exit();
		}
		break;
	}
}