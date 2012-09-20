<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

include "APIValidation.inc";

class ServiceSecurity {	
	function checkSecurity($request) {
		$scmd=$request['scmd'];
		$temp=strstr($scmd,".",true);
		
		if(strlen($temp)<=0) {
			return $request;
		}
		if(!is_dir(ROOT.APPS_FOLDER.$temp)) {
			return $request;
		}
		//Is A App Command
		if($temp==SITENAME) {
			return $request;
		} elseif(in_array($temp,$_SESSION['SESS_ACCESS_SITES'])) {
			return $request;
		} elseif(isAdminSite()) {
			return $request;
		}
		printErr('AccessDenial',"Requested Service Requires You To Login.<br/>Thank You.");
		exit();
	}
	
	function isBlacklisted($autoQuit=false) {
		if(isLocalhost()) return false;
		$site=SITENAME;
		$client=$_SERVER['REMOTE_ADDR'];
		
		$f=ROOT.CACHE_IPLIST_FOLDER."{$site}/blacklist.dat";
		$a=false;
		if(file_exists($f)) {
			$data=file_get_contents($f);
			$ipArr=explode("\n",$data);
			if(strlen($ipArr[count($ipArr)-1])==0) unset($ipArr[count($ipArr)-1]);
			if(in_array($client,$ipArr)) {
				$a=true;
			}
		}
		if($autoQuit && $a) {
			printErr("Blacklisted");
			exit();
		}
		return $a;
	}
	
	function isRemoteKeyValid($autoQuit=false) {
		if(isLocalhost()) return true;
		if(!isset($_REQUEST['APIKEY'])) {
			if($autoQuit) {
				printErr("CrossSite","Please Obtain A Valid API-Key for accessing this service.");
				exit();
			} else {
				return $a;
			}
		}
		$api=new APIValidation();		
		$a=$api->checkAPIKey($_REQUEST['APIKEY'],SITENAME);
		if($autoQuit && !$a) {
			printErr("CrossSite","Please Obtain A Valid API-Key for accessing this service.");
			exit();
		}
		return $a;
	}
	
	function checkHTTPReferenceLocks($autoQuit=false) {
		if(getConfig("HTTP_REFERER_LOCK")=="false") {
			return true;
		}
		if(!isset($_SERVER["HTTP_REFERER"]) || strlen($_SERVER["HTTP_REFERER"])==0) {
			if($autoQuit) {
				printErr("AccessDenial","Security On Service Server Does Not Allow Direct Connection");
				exit();
			}
			return false;
		}
		$httpRef=parseHTTPReferer();
		if(getConfig("HOST_LOCK")=="true" && (strlen($httpRef["HTTP_HOST"]))>0  && $httpRef["HTTP_HOST"]!=$_SERVER["HTTP_HOST"]) {
			if($autoQuit) {
				printErr("AccessDenial","Multi Host Service Not Enabled");
				exit();
			}
			return false;
		}
		if(getConfig("SITE_LOCK")=="true" && (strlen($httpRef["SITE"])>0 && $httpRef["SITE"]!=$_REQUEST['site'])) {
			if($autoQuit) {
				printErr("AccessDenial","Cross AppSite Service Is Disabled");
				exit();
			}
			return false;
		}
		return true;
	}
	
	function cleanSecurityConfigs() {
		unset($GLOBALS["LocalHostPass"]);
		unset($GLOBALS["allowNoPath"]);
		unset($GLOBALS["allowNoPathLocalhost"]);
		unset($GLOBALS["privateMsg"]);
		unset($GLOBALS["privateErr"]);
		unset($GLOBALS["privateAccess"]);
		unset($GLOBALS["privatePath"]);
		unset($GLOBALS["noPath"]);
		unset($GLOBALS["blacklist"]);
	}
}
if(!function_exists("checkServiceSession")) {
	function checkServiceSession($autoExit=true) {
		if(isset($_SESSION['SESS_USER_ID']) && isset($_SESSION['SESS_PRIVILEGE_ID']) && isset($_SESSION['SESS_TOKEN']) &&
			isset($_SESSION['SESS_LOGIN_SITE']) && isset($_SESSION['SESS_ACCESS_SITES'])) {
				if($_SESSION['SESS_LOGIN_SITE']==$_REQUEST['site']) {
					return true;
				} elseif(in_array($_REQUEST['site'],$_SESSION['SESS_ACCESS_SITES'])) {					
					return true;
				} else {
					if($autoExit) {
						printErr("AccessDenial","Invalid Session. Please Login For Using This Service.");
						exit();
					} else {
						return false;
					}
				}
		} else {
			if($autoExit) {
				printErr("AccessDenial","Invalid Session. Please Login For Using This Service.");
				exit();
			} else {
				return false;
			}
		}
	}
}
?>
