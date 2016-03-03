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

	function getMyRoleHash() {
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_NAME"].$_SESSION["SESS_PRIVILEGE_ID"]);
			} else {
				return false;
			}
		}
		return $_SESSION["SESS_PRIVILEGE_HASH"];
	}
	function fetchUserRoleHash($userid) {
		$tbl1=_dbTable("users", true);
		$tbl2=_dbTable("privileges", true);
		$data=_db(true)->_raw("SELECT md5(concat({$tbl2}.name,{$tbl2}.id)) as hash FROM {$tbl1},{$tbl2} WHERE {$tbl1}.privilegeid={$tbl2}.id AND {$tbl1}.userid='root'")
				->_get();
		if(isset($data[0])) return $data[0]['hash'];
		else return false;
	}

	//Returns the User Configuration for the scope
	function getUserConfig($configKey,$baseFolder=null,$reset=false) {
		$configKey=strtolower($configKey);
		if($reset) {
			if(isset($_SESSION['USERCONFIG'][$configKey])) {
				unset($_SESSION['USERCONFIG'][$configKey]);
			}
		}
		if(isset($_SESSION['USERCONFIG']) && isset($_SESSION['USERCONFIG'][$configKey])) {
			return $_SESSION['USERCONFIG'][$configKey];
		}

		$configData=getSettings($configKey);
		if(strlen($configData)>2) {
			$_SESSION['USERCONFIG'][$configKey]=json_decode($configData,true);

			return $_SESSION['USERCONFIG'][$configKey];
		}
		if($baseFolder==null) {
			$bt =  debug_backtrace();
			$baseFolder=dirname($bt[0]['file'])."/";
		}
		$configArr=[
				APPROOT.APPS_CONFIG_FOLDER."features/".$configKey.".json",
				$baseFolder."config.json",
			];
		foreach ($configArr as $f) {
			if(file_exists($f)) {
				$configData=file_get_contents($f);
				$_SESSION['USERCONFIG'][$configKey]=json_decode($configData,true);
				setSettings($configKey,$configData);
				return $_SESSION['USERCONFIG'][$configKey];
			}
		}
		return false;
	}
	function setUserConfig($configKey,$configData) {
		$configKey=strtolower($configKey);
		$_SESSION['USERCONFIG'][$configKey]=$configData;
		setSettings($configKey,json_encode($configData));
		return $_SESSION['USERCONFIG'][$configKey];
	}
}


?>
