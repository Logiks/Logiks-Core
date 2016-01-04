<?php
/*
 * This bootstraps the user management system along with role model and acess system.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/User.php";
include_once dirname(__FILE__)."/RoleModel.inc";
include_once dirname(__FILE__)."/Settings.php";

//UserSettings
//SiteSettings

if(!function_exists("checkUserRoles")) {
	function checkUserRoles($module,$activity,$category="Block") {
		return RoleModel::checkRole($module,$activity,$category);
	}
	function generateGUID($name) {
		return trim(strtolower(preg_replace('/\W/', '', $name)));
	}
}


?>
