<?php
/*
 * This contains all the security related functions.
 * Functions ::  session_check,isAdminSite,user_admin_check,checkUserSiteAccess,isLinkAccessible
 *  				checkDevMode, checkBlacklist,checkSiteMode
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("session_check")) {
	//User Is Logged In and Site Being Accessed Is Correct
	function session_check($redirect=false,$showErrorMsg=false) {
		$valid=false;
		
		if(defined("SITENAME")) {
			if(isset($_SESSION['SESS_USER_ID']) && isset($_SESSION['SESS_PRIVILEGE_ID']) && isset($_SESSION['SESS_ACCESS_ID']) &&
				isset($_SESSION['SESS_TOKEN']) && isset($_SESSION['SESS_SITEID']) &&
				isset($_SESSION['SESS_LOGIN_SITE']) && isset($_SESSION['SESS_ACCESS_SITES']) &&
				isset($_SESSION['SESS_SITEID']) && $_SESSION['SESS_SITEID'] == SiteID) {
				if($_SESSION['SESS_TOKEN'] == session_id() ||
					$_SESSION['MAUTH_KEY']==generateMAuthKey()) {
					if(is_numeric($_SESSION['SESS_PRIVILEGE_ID']) && $_SESSION['SESS_PRIVILEGE_ID']>0) {
						if($_SESSION['SESS_LOGIN_SITE']==$_REQUEST['site'])
							$valid=true;
						elseif(is_array($_SESSION['SESS_ACCESS_SITES']) && in_array(SITENAME,$_SESSION['SESS_ACCESS_SITES']))
							$valid=true;
					}
				}
			}
		}
		if($valid) {
			return true;
		} else {
			if($redirect) {
				$relink=SiteLocation."login";
				redirectTo($relink,"SESSION Expired. Going To Login Page");
				session_destroy();
				exit();
			} else {
				if($showErrorMsg) {
					trigger_logikserror("Accessing Forbidden Page",E_USER_ERROR,401);
				}
				return false;
			}
		}
	}
	//function session_login_check() {}
	
	//DEPRECEATED : to be delated on Dec 2017
// 	function checkAccess($module,$activity,$actionType="view") {
// 		$module=strtoupper($module);
// 		$actionType=strtoupper($actionType);
// 		if($module=="PAGE") {
// 			//checkPageAccess
// 		} else {
// 			//checkRoleScope
// 			//checkUserRoles
// 		}
// 	}
	
	function isAdminSite($site=SITENAME) {
		$adminSites=explode(",", DOMAIN_CONTROLS_ADMINAPP);
		if(in_array($site, $adminSites)) return true;
		return false;
	}

	function user_admin_check() {
		$a=session_check(false);
		if($a && isAdminSite()) {
			$acp=$_SESSION['SESS_ACCESS_SITES'];
			if(in_array(SITENAME,$acp)) {
				return true;
			}
			return false;
		} else {
			return false;
		}
	}

  function parseHTTPReferer() {
		$arr=array();
		$arr["SERVER_PROTOCOL"]="";
		$arr["HTTP_HOST"]="";
		$arr["REQUEST_URI"]="";
		$arr["SCRIPT_NAME"]="";
		$arr["QUERY_STRING"]="";

		$arr["SITE"]="";
		$arr["PAGE"]="";
		$arr["MODULE"]="";

		if(_server("HTTP_REFERER") && strlen(_server("HTTP_REFERER"))>0) {
			$s=_server("HTTP_REFERER");
			$a1=substr($s,0,strpos($s,"://"));
			$s=substr($s,strpos($s,"://")+3);
			$a2=substr($s,0,strpos($s,"/"));
			$s=substr($s,strpos($s,"/")+1);
			$a3=$s;
			$a4=substr($s,0,strpos($s,"?"));
			$s=substr($s,strpos($s,"?")+1);
			$a5=$s;

			$n1=strpos($s,"site=");
			if($n1!==false) {
				$w="";
				if($n1>=0) {
					$n2=strpos($s,"&",$n1+5);
					$w=substr($s,$n1+5,$n2-$n1-5);
				} else {
					$w=$_REQUEST['site'];
				}
			} else {
				$w=$_REQUEST['site'];
			}

			$n1=strpos($s,"page=");
			$p="";
			if($n1!==false) {
				if($n1>=0) {
					$n2=strpos($s,"&",$n1+5);
					$p=substr($s,$n1+5,$n2-$n1-5);
				}
			}

			$n1=strpos($s,"mod=");
			$m="";
			if($n1!==false) {
				if($n1>=0) {
					$n2=strpos($s,"&",$n1+5);
					$m=substr($s,$n1+4,$n2-$n1-4);
				}
			}

			$arr["SERVER_PROTOCOL"]=strtoupper($a1);
			$arr["HTTP_HOST"]=$a2;
			$arr["REQUEST_URI"]=$a3;
			$arr["SCRIPT_NAME"]=$a4;
			$arr["QUERY_STRING"]=$a5;
			$arr["SITE"]=$w;
			$arr["PAGE"]=$p;
			$arr["MODULE"]=$m;
		}
		if(strlen($arr["SITE"])==0 && isset($_REQUEST['site'])) {
			$arr["SITE"]=$_REQUEST['site'];
		}
		return $arr;
	}

	//Generates the auth key required for logging into system remotely and via mobility
	function generateMAuthKey() {
		if(!isset($_REQUEST['deviceuuid'])) $_REQUEST['deviceuuid']="LOGIKS007";
		if(!isset($_REQUEST['appkey'])) $_REQUEST['appkey']="SILKAPP0000";
		$str=$_REQUEST['site']._server('HTTP_USER_AGENT').SiteID.$_REQUEST['deviceuuid'].$_REQUEST['appkey'];
		if(isset($_SESSION['SESS_USER_ID'])) $str.=$_SESSION['SESS_USER_ID'];

		$key=md5(base64_encode($str));

		unset($_REQUEST['deviceuuid']);
		return $key;
	}
	
	function generateCSRF($pgLevel=false) {
		$arrCSRF=["KEY"=>base64_encode(openssl_random_pseudo_bytes(32)),"NAME"=>uniqid(SITENAME."_")];
		
		if($pgLevel!==false) {
			$_SESSION['CSRF_TOKEN_KEY'][SITENAME][$pgLevel] = $arrCSRF['KEY'];
			$_SESSION['CSRF_TOKEN_NAME'][SITENAME][$pgLevel] = $arrCSRF['NAME'];
		} else {
			$_SESSION['CSRF_TOKEN_KEY'][SITENAME] = $arrCSRF['KEY'];
			$_SESSION['CSRF_TOKEN_NAME'][SITENAME] = $arrCSRF['NAME'];
		}
		
		return $arrCSRF;
	}
	
	function validateCSRF($pgLevel=false) {
		$csrfKey="";
		$csrfName="";
		
		if($pgLevel!==false) {
			if(isset($_SESSION['CSRF_TOKEN_KEY'][SITENAME][$pgLevel]) && isset($_SESSION['CSRF_TOKEN_NAME'][SITENAME][$pgLevel])) {
				$csrfKey=$_SESSION['CSRF_TOKEN_KEY'][SITENAME][$pgLevel];
				$csrfName=$_SESSION['CSRF_TOKEN_NAME'][SITENAME][$pgLevel];
			}
		} else {
			if(isset($_SESSION['CSRF_TOKEN_KEY'][SITENAME]) && isset($_SESSION['CSRF_TOKEN_NAME'][SITENAME])) {
				$csrfKey=$_SESSION['CSRF_TOKEN_KEY'][SITENAME];
				$csrfName=$_SESSION['CSRF_TOKEN_NAME'][SITENAME];
			}
		}
		
		if(strlen($csrfName)>2) {
			if(isset($_POST[$csrfName]) && $_POST[$csrfName]==$csrfKey) {
				return true;
			}
		}
		
		return false;
	}
}
?>
