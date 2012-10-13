<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("pathfuncs");
//Some Special System Functions
if(!function_exists("createTimeStamp")) {
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
	function createTimeStamp($encoded=true) {
		if($encoded) {
			$s=date(TIMESTAMP_FORMAT).microtime();
			if(function_exists("md5")) {
				$s=md5($s);
			} else {
				$s=base64_encode($s);
			}
			return $s;
		} else {
			$s=date(TIMESTAMP_FORMAT).microtime();
			return $s;
		}
	}
	function getSysDBLink($toClose=false) {
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
			$q1="SELECT userid,name,email,mobile,address,access,privilege FROM "._dbTable("users",true)." where userid='".$_SESSION["SESS_USER_ID"]."'";
			$res1=_dbQuery($q1,true);
			if($res1) {
				$data=_db()->fetchData($res1);
				$_SESSION['SESS_USER_NAME'] = $data['name'];
				$_SESSION['SESS_USER_EMAIL'] = $data['email'];
				$_SESSION['SESS_USER_CELL'] = $data['mobile'];
			}
		} else {
			$_SESSION['SESS_USER_ID'] = "Guest";
			$_SESSION['SESS_PRIVILEGE_ID'] = "Guest";
			$_SESSION['SESS_ACCESS_ID'] = "NA";

			$_SESSION['SESS_PRIVILEGE_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_SITES'] = getSessionSite();
			
			$_SESSION['SESS_USER_NAME'] = "Guest";
			$_SESSION['SESS_USER_EMAIL'] = "NA";
			$_SESSION['SESS_USER_CELL'] = "NA";
		}
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
	function logoutSession() {
		session_destroy();
		$relink=SiteLocation . "login.php";
		if(defined("SITENAME")) $relink.="?site=".SITENAME;
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
}
?>
