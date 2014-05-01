<?php
require_once ('api/initialize.php');
include ('api/router.php');

if(defined("APPS_THEME")) $css->loadTheme(APPS_THEME);
else define("APPS_THEME","default");

if(!defined("APPS_NAME")) define("APPS_NAME",getConfig("APPS_NAME"));
if(!defined("APPS_VERS")) define("APPS_VERS",getConfig("APPS_VERS"));
$_SESSION["SITELOCATION"]=SiteLocation;

checkDevMode();
runHooks("startup");
log_VisitorEvent();

if(_databus("PAGE_BUFFER_ENCODING")!="plain") startOPBuffer();

$a=isLinkAccessible();
if(!$a) {
	trigger_ForbiddenError("Requested Page is Forbidden From Your Access.");
	exit();
}

if(!(isset($_GET['lgksHeader']) && $_GET['lgksHeader']=="false")) {
	printHTMLPageHeader();
}

$pageLinkPath=getPageToLoad();
if(strlen($pageLinkPath)>0 && file_exists($pageLinkPath)) {
	include "api/scripts.php";
	runHooks("prePage");
	$cacheFile=RequestCache::getCachePath("pages");
	switch(getConfig("FULLPAGE_CACHE_ENABLED")) {
		case "true":
			$noCache=explode(",",getConfig("FULLPAGE_CACHE_NOCACHE"));
			$pg=explode("/",$_REQUEST['page']);
			if(in_array($pg[0],$noCache)) {
				include $pageLinkPath;
			} else {
				$a=RequestCache::checkCache("pages",getConfig("FULLPAGE_CACHE_PERIOD"));
				if($a) {
					include_once $cacheFile;
				} else {
					ob_start();
					include $pageLinkPath;
					$data=ob_get_contents();
					ob_flush();
					if(!(isset($_REQUEST['nocache']) && $_REQUEST['nocache']=="true"))
						file_put_contents($cacheFile,$data);
				}
			}
			break;
		default:
			include $pageLinkPath;
			break;
	}
	runHooks("postPage");
} else {
	trigger_NotFound("Sorry , Page Not Found. Page::" . $current_page);
}
?>