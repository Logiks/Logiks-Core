<?php
/*
 * A simple router file for routing requests into the php files residing within PAGE folders.
 * Mostly used by developers for ease of deployment and controls.
 * Good for programers
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
		APPROOT.APPS_PAGES_FOLDER."{$page}.php",
	);

$loaded=false;
foreach ($pageFiles as $f) {
	if(file_exists($f)) {
		$loaded=true;
		runHooks("prePage");
		include_once $f
		runHooks("postPage");
		break;
	}
}
if(!$loaded) {
	trigger_logikserror("Page Not Found $page");
}
?>
