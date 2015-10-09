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
	//include_once ROOT. "api/libs/logikssecurity.php";

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
				$relink=SiteLocation . "login.php?site=".SITENAME;
				redirectTo($relink,"SESSION Expired. Going To Login Page");
				sessionExpired();
				exit();
			} else {
				if($showErrorMsg) {
					trigger_ForbiddenError("Accessing Forbidden Page");
				}
				return false;
			}
		}
	}
	//function session_login_check() {}
	
}
?>