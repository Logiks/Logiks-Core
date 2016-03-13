<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$GLOBALS['classPath']=array(
		"api/",
		"api/libs/",
		"plugins/modules/",
	);
$GLOBALS['mediaPaths']=array(
		"#APPROOT#userdata/",
		"#APPROOT#media/",
		"#APPROOT#css/",
		"#THEME#/",
		"media/",
		"media/#SITENAME#/",
	);
$GLOBALS['vendorPath']=array(
		"#APPROOT#plugins/",
		"#ROOT#api/",
		"#ROOT#plugins/",
		"#ROOT#pluginsDev/",
	);
$GLOBALS['pluginPaths']=array(
		"#APPROOT#plugins/",
		"#ROOT#pluginsDev/",
		"#ROOT#plugins/",
	);
?>
