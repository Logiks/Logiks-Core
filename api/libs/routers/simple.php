<?php
/*
 * A simple router file for routing requests into the app
 * Mostly used by developers for ease of deployment and controls.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 07/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

$page=PAGE;

$page=explode("/",$page);
$page=current($page);

$pageFile=APPROOT.APPS_PAGES_FOLDER."{$page}.php";

if(file_exists($pageFile))  {
  include_once $pageFile;
} else {
  trigger_error("Page Not Found $page");
}
?>
