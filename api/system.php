<?php
/*
 * This centralizes all the system level functions.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("pathfuncs");
//Some Special System Functions
if(!function_exists("getSysDBLink")) {
	function deleteCookies($name) {
		setcookie($name, "", time()-1000000000);
		if(isset($_COOKIE[$name])) unset($_COOKIE[$name]);
	}
	function isLocalhost() {
		$client=$_SERVER['REMOTE_ADDR'];
		$server=$_SERVER['SERVER_ADDR'];
		if($client==$server) return true;
		else return false;
	}
	//$refs :: __FILE__
	function getLocation($refs) {
		$x=SiteLocation . "/" .str_replace(ROOT,"",$refs);
		return $x;
	}
	function getSysDBLink($toClose=false) {
		if(getConfig("ALLOW_ROOTDB_ACCESS")=="false") return;
		global $sysdbLink;
		if(!$toClose) {
			if($sysdbLink==null) {
				$sysdbLink=Database();
				$sysdbLink->connect();
			}
		}
		return $sysdbLink;
	}
	function getAppsDBLink($toClose=false) {
		global $appdbLink;
		if(!isset($GLOBALS['DBCONFIG']["DB_USER"]) || strlen($GLOBALS['DBCONFIG']["DB_USER"])<=0) return null;
		if(!$toClose) {
			if($appdbLink==null) {
				$appdbLink=new Database();
				$appdbLink->connect();
			}
		}
		return $appdbLink;
	}

	//Initializes The User Names, etc...
	function initUserCredentials() {
		if(isset($_SESSION["SESS_USER_ID"])) {
			$q1="SELECT userid,name,email,mobile,address,access,privilege FROM "._dbtable("users",true)." where userid='".$_SESSION["SESS_USER_ID"]."'";
			$res1=_dbQuery($q1,true);
			if($res1) {
				$data=_db()->fetchData($res1);
				$_SESSION['SESS_USER_NAME'] = $data['name'];
				$_SESSION['SESS_USER_EMAIL'] = $data['email'];
				$_SESSION['SESS_USER_CELL'] = $data['mobile'];
			}
		} else {
			$_SESSION['SESS_USER_ID'] = "Guest";
			$_SESSION['SESS_PRIVILEGE_ID'] = -1;
			$_SESSION['SESS_ACCESS_ID'] = -1;

			$_SESSION['SESS_PRIVILEGE_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_SITES'] = array($_SESSION['LGKS_SESS_SITE']);

			$_SESSION['SESS_USER_NAME'] = "Guest";
			$_SESSION['SESS_USER_EMAIL'] = "";
			$_SESSION['SESS_USER_CELL'] = "";
		}
	}
	function flushPermissions($site=SITENAME) {
		$f=ROOT.CACHE_PERMISSIONS_FOLDER."{$site}/";
		if(is_dir($f)) {
			$fs=scandir($f);
			foreach($fs as $a) {
				if($a=="." || $a=="..") continue;
				unlink("{$f}{$a}");
			}
			return true;
		}
		return false;
	}
	function getPageCacheFile() {
		$pageCacheDir=ROOT.TMP_FOLDER."fullcache/pages/".SITENAME."/";
		if(!is_dir($pageCacheDir)) {
			if(mkdir($pageCacheDir,0777,true)) chmod($pageCacheDir,0777);
		}
		$hash=md5($_SERVER['REQUEST_URI']);
		$hashFile=$pageCacheDir.$hash.".php";
		return $hashFile;
	}
	function getFunctionCaller() {
		$trace=debug_backtrace();
		array_shift($trace);//Remove Self
		//array_shift($trace);//Remove Parent
		$caller=array_shift($trace);//Caller
		return $caller;
	}
	function getSupportedPages($page) {
		$arr=array();
		$arr=array("{$page}.php","{$page}.htm","{$page}.html");
		return $arr;
	}
	function logoutSession($msg=null) {
		session_destroy();
		$relink=SiteLocation . "login.php";
		if(defined("SITENAME")) $relink.="?site=".SITENAME;
		if($msg!=null && strlen($msg)>0) {
			$_SESSION['SESS_ERROR_MSG']=$msg;
			$relink.="&errormsg=$msg";
		}
		redirectTo($relink,"SESSION Expired. Going To Login Page");
	}
	function getSessionSite() {
		/*if(isset($_SESSION['LGKS_SESS_SITE'])) return $_SESSION['LGKS_SESS_SITE'];
		elseif(isset($_REQUEST['site'])) {
			$_SESSION['LGKS_SESS_SITE']=$_REQUEST['site'];
			return $_REQUEST['site'];
		}
		else return DEFAULT_SITE;*/

		$site="";
		if(isset($_SESSION["LGKS_SESS_SITE"])) {
			//active session
			if(isset($_REQUEST['site'])) {
				if($_REQUEST['site']!=$_SESSION["LGKS_SESS_SITE"]) {
					if(DOMAIN_CONTROLS_ENABLE=="true") {
						$dm=new DomainMap($sysdbLink);
						$site=$dm->checkHost();
					} else {
						$site=$_REQUEST['site'];
					}
				} else {
					$site=$_REQUEST['site'];
				}
			} else {
				$site=$_SESSION["LGKS_SESS_SITE"];
			}
		} else {
			//inactive session
			if(DOMAIN_CONTROLS_ENABLE=="true") {
				$dm=new DomainMap($sysdbLink);
				$site=$dm->checkHost();
			} else {
				if(isset($_REQUEST['site'])) {
					$site=$_REQUEST['site'];
				} else {
					$site=DEFAULT_SITE;
				}
			}
		}
		if($site==null || strlen($site)<=0) $site=DEFAULT_SITE;
		return $site;
	}
	//Gets and lists the Sources/Handlers that can be used for the activity.
	//It helps when a single handler is to be used where multiple handlers are available.
	//Can be accessed using handler# at various places.
	//eg. editors :: multiple are available, single to be used.
	function get_handlers($activity,$forceReload=false) {
		if($activity==null || strlen($activity)<=0) return array();
		if(!isset($GLOBALS['DEFAULT_HANDLERS']) || $forceReload) {
			$jsondb=new SimpleJSONDB(ROOT.MISC_FOLDER."jsondb/");
			$dmArr=$jsondb->getAll("default_handlers");
			if(!$dmArr) {
				$dmArr=array();
			}
			$GLOBALS['DEFAULT_HANDLERS']=$dmArr;
		}
		if(isset($GLOBALS['DEFAULT_HANDLERS'][$activity]) && $GLOBALS['DEFAULT_HANDLERS'][$activity]["enabled"]) {
			return $GLOBALS['DEFAULT_HANDLERS'][$activity]["opts"];
		} else return array();
	}
	//Gets the time difference between current time and time of request.
	function getRequestTime() {
		return (microtime(true)-$_SESSION['REQUEST_START']);
	}
}
?>
