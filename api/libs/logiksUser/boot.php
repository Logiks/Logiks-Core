<?php
/*
 * This bootstraps the user management system along with role model and acess system.
 *
 * Function Flow => checkUserPolicy->checkUserScope->checkUserRoles
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/Group.php";
include_once dirname(__FILE__)."/User.php";
include_once dirname(__FILE__)."/Site.php";
include_once dirname(__FILE__)."/RoleModel.inc";
include_once dirname(__FILE__)."/Settings.php";
include_once dirname(__FILE__)."/SiteSettings.php";
include_once dirname(__FILE__)."/UserSettings.php";
include_once dirname(__FILE__)."/admins.php";

if(!function_exists("checkUserRoles")) {
  
  //Reset entire Role Cache in Session
	function resetRoleCache($totalClear = false) {
		if($totalClear) {
			RoleModel::cleanRoleModelCache();
		} else {
			if(!isset($_SESSION["ROLEMODEL"])) unset($_SESSION["ROLEMODEL"]);
			if(!isset($_SESSION["ROLEMODEL2"])) unset($_SESSION["ROLEMODEL2"]);
		}
	}
	
  //Checks access to a certain module or system
  function checkUserScope($module) {
		return RoleModel::getInstance()->checkScope($module);
	}
	
  //Checks specific permission
	function checkUserRoles($module,$activity="MAIN",$actionType="ACCESS") {
		return RoleModel::getInstance()->checkRole($module,$activity,$actionType);
	}
	
	function checkUserPolicy($policyStr,$policyName=null) {
		if($policyStr==null || strlen($policyStr)<=0) return true;
    
    $policyStrOriginal = $policyStr;
		$policyStr=(str_replace(" ",".",$policyStr));//strtolower
		if($policyName==null || strlen($policyName)<=0) $policyName=toTitle(str_replace(".","_",$policyStr));
		
		$policyData=explode(":",$policyStr);
		if(count($policyData)<=1)  {
			$policyArr=explode(".",$policyStr);
			if(count($policyArr)==1) {
				return checkUserScope($policyArr[0]);
			} elseif(count($policyArr)==2) {
				return checkUserRoles($policyArr[0],$policyArr[1]);
			} else {
				$policyArr[2] = implode(".", array_slice($policyArr, 2, count($policyArr)-2));
				return checkUserRoles($policyArr[0],$policyArr[1],$policyArr[2]);
			}
		} else {
			switch(strtolower($policyData[0])) {
				case "scope":case "policy":
					return checkUserPolicy($policyData[1],$policyName);
					break;
				case "privileges":
					$privileges=explode(",",$policyData[1]);
					if(count($privileges)<=0) return true;
					if(in_array($_SESSION['SESS_PRIVILEGE_NAME'],$privileges) || in_array(RoleModel::getInstance()->getPrivilegeHash(),$privileges)) return true;
					break;
				case "users":
					$users=explode(",",$policyData[1]);
					if(count($users)<=0) return true;
					if(in_array($_SESSION['SESS_USER_ID'],$users)) return true;
					break;
			}
		}
		
		return false;
	}
	
	function updateUserMetas() {
		//SELECT count(*) as cnt FROM `lgks_users` WHERE guid NOT IN (SELECT guid FROM lgks_users_guid)
		$data=_db(true)->_selectQ(_dbTable("users",true),"count(*) as cnt")->_whereRAW("guid NOT IN (SELECT guid FROM lgks_users_guid)")->_GET();
		if($data[0]['cnt']>0) {
			$sql=[
				"INSERT INTO lgks_users_guid (guid,org_name,org_email) (SELECT guid, organization_name, organization_email FROM lgks_users WHERE guid NOT IN (SELECT guid FROM lgks_users_guid) GROUP BY guid)",
				//"UPDATE lgks_users_guid,lgks_users SET lgks_users_guid.guid=lgks_users.guid, lgks_users_guid.org_name=lgks_users.organization_name, lgks_users_guid.org_email=lgks_users.organization_email WHERE lgks_users.guid=lgks_users_guid.guid",
			];
			foreach($sql as $q) {
				_dbQuery($q,true);
			}
		}
		
		//$data=_db(true)->_selectQ(_dbTable("users_group",true),"count(*) as cnt")->_whereRAW("guid NOT IN (SELECT guid FROM lgks_users_guid)")->_GET();
		//if($data[0]['cnt']>0) {
			//INSERT INTO lgks_users_group (guid,group_name,group_manager) (SELECT guid,'hq')
		//}
		
		if(isset($_ENV['FORMSUBMIT']) && isset($_ENV['FORMSUBMIT']['data']) && isset($_ENV['FORMSUBMIT']['where'])) {
			$data=_db(true)->_selectQ(_dbTable("users",true),"*",$_ENV['FORMSUBMIT']['where'])->_GET();
			if(count($data)>0) {
				if($data[0]['guid']!="global") {
					$orgData=[
							"org_name"=>$data[0]['organization_name'],
							"org_email"=>$data[0]['organization_email'],
						];
					_db(true)->_updateQ(_dbTable("users_guid",true),$orgData,["guid"=>$data[0]['guid']])->_RUN();
				}
			}
		}
	}
}
?>
