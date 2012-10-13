<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$params=processUserRequest();
if(strtolower($params["PAGE"])=="/login" || strtolower($params["PAGE"])=="login") {
	header("Location:".SiteLocation."login.php?site=".$params["SITE"]);
}
if(!defined("SITENAME")) define("SITENAME",$params["SITE"]);
define("APPROOT",ROOT . APPS_FOLDER . $params["SITE"] . "/");
define("WEBAPPROOT",SiteLocation . "apps/" . $params["SITE"] . "/");
define("BASEPATH",APPS_FOLDER . $params["SITE"] . "/");

$site=$params["SITE"];
if(file_exists(APPROOT)) {
	$apps_cfg=APPROOT."apps.cfg";
	if(!file_exists($apps_cfg)) {		
		trigger_ForbiddenError("Site <b>'$site'</b> Has Not Yet Been Activated.");
		exit();
	}
	LoadConfigFile($apps_cfg,true);
	
	if(defined("RELINK") && strlen(RELINK)>0) {
		$relink=generatePageRequest("","",RELINK);
		redirectTo($relink);
	}
	if(defined("APPS_TYPE") && strtolower(APPS_TYPE)=="3rdparty") {
		$relink=WEBAPPROOT;
		redirectTo($relink);
	}
	
	loadConfigDir(APPROOT."config/");
	
	if(defined("LINGUALIZER_DICTIONARIES")) $ling->loadLocaleFile(LINGUALIZER_DICTIONARIES);
	
	checkSiteMode($site);
	
	//Constuct Current Page
	if(isset($params["PAGE"]) && strlen($params["PAGE"])>0) {
		$current_page=$params["PAGE"];
	} else {
		$current_page="home";
	}
	
	if(isset($_REQUEST["sos"])) {
		$a=true;
		if(defined("ALLOW_DEFAULT_SYSTEM_PAGES")) $a=(ALLOW_DEFAULT_SYSTEM_PAGES=="true")?true:false;
		if($a && file_exists(ROOT.PAGES_FOLDER."sos/".$current_page.".php")) {
			include ROOT.PAGES_FOLDER."sos/".$current_page.".php";
			exit();
		}
	}
	if(defined("ACCESS") && strtolower(ACCESS)=="private") {
		$check=session_check(false);
		if($check) {
			$toload_page=navigateToPage($params);
		} else {
			$rt=explode("/",$_SERVER['PHP_SELF']);
			$oldPage=SiteLocation."{$rt[count($rt)-1]}";
			if($rt[count($rt)-1]=="seo.php") {
				if(isset($_REQUEST['site'])) {
					$oldPage=SiteLocation.$_REQUEST['site'];
					if(isset($_REQUEST['page'])) {
						$oldPage.="/{$_REQUEST['page']}";
					}
					if(strlen($_SERVER['QUERY_STRING'])>0) {
						$oldPage.="?".$_SERVER['QUERY_STRING'];
					}
				} else {
					$oldPage="http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
				}
			} else {
				if(strlen($_SERVER['QUERY_STRING'])>0) {
					$oldPage.="?".$_SERVER['QUERY_STRING'];
				}
			}
			
			if(!defined("ALT_SITE") || strlen(ALT_SITE)<=0) {
				$relink=SiteLocation . "login.php?site={$params['SITE']}";
				$_SESSION['LOGIN_RELINK']=$oldPage;
				redirectTo($relink,"SESSION Expired. Going To Login Page");
			} else {
				if(strtolower(ALT_SITE)=='login') {
					$_SESSION['LOGIN_RELINK']=$oldPage;
					$relink=SiteLocation . "login.php?site={$params['SITE']}";					
					redirectTo($relink,"SESSION Expired. Going To Login Page");
				} else {
					$relink=generatePageRequest("","",ALT_SITE);
					redirectTo($relink);
				}
			}
		}
	} else {		
		$toload_page=navigateToPage($params);
	}
} else {
	trigger_NotFound("Site Not Found <b>'$site'</b>");
}

function getPageToLoad() {
	global $toload_page,$current_page;
	return $toload_page;
}
function getCurrentPage() {
	global $toload_page,$current_page;
	return $current_page;
}
function navigateToPage($params) {
	//simple	 :: means the site to accessed as general site would have to
	//controller :: means to load controller oriented page		
	//cms	 :: means CMS controlled site
	/*
	 * simple/direct means :: Direct Access To Page
	 * controller :: means Always access Home/Device Page
	 * cms :: means Allways access cms.php
	 */
	global $current_page;
	$appsType=strtolower(APPS_TYPE);
	
	//Construct Home Page Across Devices
	if(defined("HOME_PAGE")) {
		$home_page=HOME_PAGE;
	} else $home_page="home.php";
	if(defined("MOBILE_PAGE") && strlen(MOBILE_PAGE)>0) {
		$mobile_page=MOBILE_PAGE;
	} else $mobile_page=$home_page;	
	if(defined("TABLET_PAGE") && strlen(TABLET_PAGE)>0) {
		$tablet_page=TABLET_PAGE;
	} else $tablet_page=$home_page;
	
	//Construct Page To Be Loaded	
	if($appsType=="controller") {
		$device=getUserDeviceType();
		if($device=="mobile") {
			$a=$mobile_page;
		} elseif($device=="tablet") {
			$a=$tablet_page;
		} else {
			$a=$home_page;
		}
		$toload_page=APPROOT.$a;
	} elseif($appsType=="cms") {
		$home_page="cms.php";
		//$mobile_page="mobile.php";
		//$tablet_page="tablet.php";
		
		$device=getUserDeviceType();
		if($device=="mobile") {
			$a=$mobile_page;
		} elseif($device=="tablet") {
			$a=$tablet_page;
		} else {
			$a=$home_page;
		}
		$toload_page=APPROOT.$a;
	} elseif($appsType=="website") {
		if(isset($_REQUEST["page"])) {
			$a="pages/".$_REQUEST["page"].".php";
		} else {
			if(file_exists(APPROOT.$home_page)) {
				$a=$home_page;
			} else {
				$a="pages/".$home_page.".php";
			}
		}
		$toload_page=APPROOT.$a;
	} else {//$appsType=="simple" / "direct"
		$toload_page=APPROOT.$current_page.".php";
	}
	return $toload_page;
}
?>
