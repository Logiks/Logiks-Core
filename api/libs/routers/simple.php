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

if(!defined("APPS_PAGES_FOLDER")) define("APPS_PAGES_FOLDER","pages/");

$page=PAGE;

$page=explode("/",$page);
$page=current($page);

$pageFiles=array(
		APPROOT.APPS_PAGES_FOLDER."{$page}.tpl",
		APPROOT.APPS_PAGES_FOLDER."{$page}.php"
		APPROOT.APPS_PAGES_FOLDER."{$page}.htm"
	);

$loaded=false;
foreach ($pageFiles as $f) {
	if(file_exists($f)) {
		$loaded=true;
		runHooks("prePage");
		switch ($k) {
			case 0:
				_templatePage($f);
				break;
			case 1:
				include_once $f
				break;
			case 2:
				readfile($f);
				break;
		}
		runHooks("postPage");
		break;
	}
}
if(!$loaded) {
	trigger_error("Page Not Found $page");
}
?>
