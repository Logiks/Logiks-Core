<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

include "APIValidation.inc";

class ServiceSecurity {
	function checkSecurity($request,$serviceCtrlDb) {
		$cmdCntrls=null;

		$scmd=$request['scmd'];
		$scmd=str_replace("/",".",$scmd);

		if(isset($serviceCtrlDb['SERVICE_ACCESS_CONTROLS']) && isset($serviceCtrlDb['SERVICE_ACCESS_CONTROLS'][$scmd])) {
			$cmdCntrls=$serviceCtrlDb['SERVICE_ACCESS_CONTROLS'][$scmd];
		} else {
			$cmdCntrls=array();
		}
		if(!isset($_GET['format']) && isset($cmdCntrls['default_print_format'])) {
			$_REQUEST['format']=$cmdCntrls['default_print_format'];
		}
		if(!isset($_REQUEST['autocache']) && isset($cmdCntrls['autocache'])) {
			$_REQUEST['autocache']=$cmdCntrls['autocache'];
		}
		if(!isset($_REQUEST['nocache']) && isset($cmdCntrls['nocache'])) {
			$_REQUEST['nocache']=$cmdCntrls['nocache'];
		}

		if(isset($cmdCntrls['access_control'])) {
			//access_control :: public,apikey,adminonly,onlogin,privileged
			if($cmdCntrls['access_control']=="public") {
				return $request;
			} elseif($cmdCntrls['access_control']=="apikey") {
				$httpRef=parseHTTPReferer();
				if(isset($httpRef["HTTP_HOST"]) && $httpRef["HTTP_HOST"]==$_SERVER["HTTP_HOST"]) return $request;
				$this->isRemoteKeyValid(true);
				$this->checkHTTPReferenceLocks(true);
				return $request;
			} elseif($cmdCntrls['access_control']=="adminonly") {
				isAdminSite();
				checkServiceSession();
				return $request;
			} elseif($cmdCntrls['access_control']=="onlogin") {
				checkServiceSession();
				return $request;
			} elseif($cmdCntrls['access_control']=="none") {
				return array();
			} else {
				return array();
			}
		}
		if(DEFAULT_ACCESS_CONTROL=="public"){
			return $request;
		} elseif(DEFAULT_ACCESS_CONTROL=="apikey") {
			$httpRef=parseHTTPReferer();
			if(isset($httpRef["HTTP_HOST"]) && $httpRef["HTTP_HOST"]==$_SERVER["HTTP_HOST"]) return $request;
			$this->isRemoteKeyValid(true);
			$this->checkHTTPReferenceLocks(true);
			return $request;
		} elseif(DEFAULT_ACCESS_CONTROL=="adminonly") {
			checkServiceSession();
			isAdminSite();
			return $request;
		} elseif(DEFAULT_ACCESS_CONTROL=="onlogin") {
			checkServiceSession();
			return $request;
		} elseif(DEFAULT_ACCESS_CONTROL=="none") {
			return array();
		}
		return array();
	}

	function isBlacklisted($autoQuit=false) {
		if(isLocalhost()) return false;

		//$a=Security::isBlacklisted(_db(true),SITENAME);
		$a=checkBlacklist(SITENAME);

		$site=SITENAME;
		$client=$_SERVER['REMOTE_ADDR'];

		$a=checkBlacklist();
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
		$a=false;
		if(API_KEY_ENGINE=="single") {
			if(strlen(API_KEY)>0) {
				if(API_KEY==$_REQUEST['APIKEY']) {
					$a=true;
				}
			} else {
				$a=true;
			}
		} else {
			$api=new APIValidation(API_KEY_ENGINE);
			$a=$api->checkAPIKey($_REQUEST['APIKEY'],SITENAME);
		}
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
		//To Be Used If Some Security Details Are Involved
	}
}
if(!function_exists("checkServiceSession")) {
	//User Is Logged In
	//Site Being Accessed Is Correct
	function checkServiceSession($autoExit=true) {
		$valid=false;
		if(isset($_SESSION['SESS_USER_ID']) && isset($_SESSION['SESS_PRIVILEGE_ID']) && isset($_SESSION['SESS_ACCESS_ID']) &&
			isset($_SESSION['SESS_TOKEN']) && isset($_REQUEST['site']) &&
			isset($_SESSION['SESS_LOGIN_SITE']) && isset($_SESSION['SESS_ACCESS_SITES'])) {
			if($_SESSION['SESS_PRIVILEGE_ID']>0) {
				if($_SESSION['SESS_LOGIN_SITE']==$_REQUEST['site'])
					$valid=true;
				elseif(is_array($_SESSION['SESS_ACCESS_SITES']) && in_array($_REQUEST['site'],$_SESSION['SESS_ACCESS_SITES']))
					$valid=true;
			}
		}

		if($valid) {
			return true;
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
