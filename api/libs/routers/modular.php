<?php
/*
 * A module based routing logic where the system directly loads module based on underlaying 
 * routing path and module map based in 
 *
 * config/route.json
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 07/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!defined("APPS_PAGES_FOLDER")) define("APPS_PAGES_FOLDER","pages/");

if(isset($_REQUEST['reload']) && $_REQUEST['reload']=="true") {
	if(isset($_SESSION['APP_ROUTE_PATH'])) unset($_SESSION['APP_ROUTE_PATH']);
}

if(!isset($_SESSION['APP_ROUTE_PATH'])) {
	$routeFile = APPROOT."config/route.json";
	$jsonRouteData = json_decode(file_get_contents($routeFile), true);

	$_SESSION['APP_ROUTE_PATH'] = $jsonRouteData;
}

$page=PAGE;

$slug=_slug("path1/path2/path3");

if($jsonRouteData && isset($jsonRouteData['paths'])) {
	if(isset($jsonRouteData['paths'][$slug["path1"]])) {
		$moduleName = explode("@", $jsonRouteData['paths'][$slug["path1"]]);
		$moduleName = end($moduleName);
		loadModule($moduleName);
	} else {
		trigger_logikserror("Sorry, '".PAGE."' page/module not found",E_LOGIKS_ERROR,404);
	}
} else {
	trigger_logikserror("Route Defination Not Found");
}

if(!function_exists("get_route_link")) {

	function get_route_link($module) {
		if(!isset($_SESSION['APP_ROUTE_PATH'])) {
			$routeFile = APPROOT."config/route.json";
			$jsonRouteData = json_decode(file_get_contents($routeFile), true);

			$_SESSION['APP_ROUTE_PATH'] = $jsonRouteData;
		}

		foreach($_SESSION['APP_ROUTE_PATH']['paths'] as $pathKey=>$moduleName) {
			if(strpos($moduleName, "module@{$module}")===0) {
				return _link($pathKey);
			}
		}
		return _link($module);
	}
}
?>