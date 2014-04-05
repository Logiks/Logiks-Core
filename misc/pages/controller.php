<?php
if(!defined('ROOT')) exit('No direct script access allowed');
if(!defined('APPROOT')) exit('No direct script access allowed');

if(isset($css) && defined("APPS_CSS_TYPE")) $css->TypeOfDispatch(APPS_CSS_TYPE);
if(isset($js) && defined("APPS_JS_TYPE")) $js->TypeOfDispatch(APPS_JS_TYPE);

if(!isset($_REQUEST["page"]) || strlen($_REQUEST["page"])==0) {
	$_REQUEST["page"]=getConfig("LANDING_PAGE");
}

if(!isset($_SESSION["SESS_PRIVILEGE_NAME"])) $_SESSION["SESS_PRIVILEGE_NAME"]="guest";
if(!isset($_SESSION["SESS_PRIVILEGE_ID"])) $_SESSION["SESS_PRIVILEGE_ID"]="guest";
if(!isset($_SESSION["SESS_USER_NAME"])) $_SESSION["SESS_USER_NAME"]="Guest";
if(!isset($_SESSION["SESS_USER_ID"])) $_SESSION["SESS_USER_ID"]="-1";

//echo $_REQUEST["page"];
function __printPage() {
	$page=$_REQUEST["page"];

	_css(explode(",", getConfig("DEFAULT_CSS_TO_LOAD")));
	_css("ie6","*","ie6");
	_css("print","*","","print");
	printSubSkin();

	_js(explode(",", getConfig("DEFAULT_JS_TO_PRELOAD")));

	loadModule("core");loadModule(SITENAME);
	
	$loaded=false;
	if(isLogiksLayout($page)) {
		$loaded=true;
		getUserPageStyle(true);
		echo "</head><body ".getBodyContext().">";
		generatePageLayout($page);
		echo "</body>";
	} else {
		$arrPages=array();
		if(defined("APPS_PAGES_FOLDER")) {
			array_push($arrPages,APPROOT.APPS_PAGES_FOLDER."{$page}.php");
		}
		if(getConfig("ALLOW_DEFAULT_SYSTEM_PAGES")=="true") {
			array_push($arrPages,ROOT.PAGES_FOLDER."{$page}.php");
		}
		foreach($arrPages as $f) {
			if(file_exists($f)) {
				$loaded=true;
				getUserPageStyle(true);
				echo "</head><body ".getBodyContext().">";
				include $f;
				echo "</body>";
				break;
			}
		}
	}
	if(!$loaded) {
		$page="error";
		if(isLogiksLayout($page)) {
			$loaded=true;
			getUserPageStyle(true);
			echo "</head><body ".getBodyContext().">";
			generatePageLayout($page);
			echo "</body>";
		} elseif(defined("APPS_PAGES_FOLDER") && file_exists(APPROOT.APPS_PAGES_FOLDER."{$page}.php")) {
			$f=APPROOT.APPS_PAGES_FOLDER."{$page}.php";
			$loaded=true;
			getUserPageStyle(true);
			echo "</head><body ".getBodyContext().">";
			include $f;
			echo "</body>";
		} else {
			trigger_ErrorCode(404,"Sorry, Requested <i>{$_REQUEST['page']}</i> Page Not Found.");
		}
	}
	_js(explode(",", getConfig("DEFAULT_JS_TO_POSTLOAD")));
}
?>