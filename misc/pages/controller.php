<?php
if(!defined('ROOT')) exit('No direct script access allowed');
if(!defined('APPROOT')) exit('No direct script access allowed');

if(isset($css) && defined("APPS_CSS_TYPE")) $css->TypeOfDispatch(APPS_CSS_TYPE);
if(isset($js) && defined("APPS_JS_TYPE")) $js->TypeOfDispatch(APPS_JS_TYPE);

if(!isset($_REQUEST["page"]) || strlen($_REQUEST["page"])==0) {
	$_REQUEST["page"]=getConfig("LANDING_PAGE");
}

if(!isset($_SESSION["SESS_PRIVILEGE_NAME"])) $_SESSION["SESS_PRIVILEGE_NAME"]="guest";
if(!isset($_SESSION["SESS_PRIVILEGE_ID"])) $_SESSION["SESS_PRIVILEGE_ID"]="Guest";
if(!isset($_SESSION["SESS_USER_NAME"])) $_SESSION["SESS_USER_NAME"]="Guest";
if(!isset($_SESSION["SESS_USER_ID"])) $_SESSION["SESS_USER_ID"]="-1";

//echo $_REQUEST["page"];
function __printPage($page=null) {
	if($page==null) {
		if(!isset($_REQUEST["page"]) || strlen($_REQUEST["page"])==0) {
			$_REQUEST["page"]=getConfig("LANDING_PAGE");
		}
		$page=$_REQUEST["page"];
	}
	$page=explode("/",$page);
	$page=current($page);
	
	loadModule("core");loadModule(SITENAME);

	$device=strtoupper(getUserDeviceType());
	if($device=="PC") {
		_css(explode(",", getConfig("DEFAULT_CSS_TO_LOAD")));
		_css("ie6","*","ie6");
		_css("print","*","","print");
		printSubSkin();

		_js(explode(",", getConfig("DEFAULT_JS_TO_PRELOAD")));

		findPage($page);
	
		_js(explode(",", getConfig("DEFAULT_JS_TO_POSTLOAD")));	
	} else {
		_css(explode(",", getConfig("{$device}_CSS_TO_LOAD")));

		_js(explode(",", getConfig("{$device}_JS_TO_PRELOAD")));

		findPage($page);
	
		_js(explode(",", getConfig("{$device}_JS_TO_POSTLOAD")));	
	}
}
?>