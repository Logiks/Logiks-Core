<?php
/*
 * This file contains the administrative tasks for user management
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("whoami")) {
	
	function whoami($more = false) {
		if($more)
			return getUserInfo();
		else 
			return getUserID();
	}

	function switchBack() {
		if(!isset($_SESSION['SESS_ORIGINAL_ID'])) return false;

		return switchToUser($_SESSION['SESS_ORIGINAL_ID']);
	}

	function switchToUser($userid) {
		if(!isset($_SESSION['SESS_ORIGINAL_ID'])) $_SESSION['SESS_ORIGINAL_ID'] = $_SESSION['SESS_USER_ID'];

		if($_SESSION['SESS_PRIVILEGE_ID']>1 && $_SESSION['SESS_ORIGINAL_ID'] == $_SESSION['SESS_USER_ID']) {
			return false;
		}

		$userInfo = getUserInfo($userid);

		$accessData=_db(true)->_selectQ(_dbTable("access",true),"sites,name as access_name")->_where([
			"id"=>$userInfo['accessid'],
			"blocked"=>"false"
		])->_get();
		if(!$accessData) return false;
		else $accessData = $accessData[0];

		//$allSites=explode(",",$accessData['sites']);

		$privilegeData=_db(true)->_selectQ(_dbTable("privileges",true),"id,md5(concat(id,name)) as hash,name as privilege_name")->_where([
			"id"=>$userInfo['privilegeid'],
			"blocked"=>"false"
		])->_get();
		if(!$privilegeData) return false;
		else $privilegeData = $privilegeData[0];

		// $groupData=_db(true)->_selectQ(_dbTable("users_group",true),"id,group_name,group_manager,group_descs")->_where([
		// 		"id"=>$userInfo['groupid']
		// 	])->_get();
		// if(!$groupData) return false;
		// else $groupData = $groupData[0];

		$roleScopeData=_db(true)->_selectQ(_dbTable("rolescope",true),"*")->_where([
				"blocked"=>"false",
				//"scope_id"=> $_POST['policy_scope']
			])->_whereRAW("(privilegeid='{$privilegeData['privilege_name']}' OR privilegeid='*')")
				->_GET();
		if(!$roleScopeData) $roleScopeData = [];
		else $roleScopeData = $roleScopeData[0];

		$finalScope = [];
		foreach($roleScopeData as $row) {
			if(!isset($finalScope[$row["scope_type"]])) $finalScope[$row["scope_type"]] = [];
			$scopeData = json_decode($row['scope_params'], true);
			if($scopeData) {
				$finalScope[$row["scope_type"]] = array_merge($finalScope[$row["scope_type"]], $scopeData);
			}
		}

		
		//Setup the User Session
		$_SESSION['SESS_GUID']=$userInfo["guid"];
	    $_SESSION['SESS_USER_NAME']=$userInfo["name"];

	    $_SESSION['SESS_USER_ID']=$userInfo["userid"];
	    $_SESSION['SESS_USER_CELL']=$userInfo["mobile"];
	    $_SESSION['SESS_USER_EMAIL']=$userInfo["email"];
	    $_SESSION['SESS_USER_COUNTRY']=$userInfo["country"];
	    $_SESSION['SESS_USER_ZIPCODE']=$userInfo["zipcode"];
	    $_SESSION['SESS_USER_GEOLOC']=$userInfo["geolocation"];

	    $_SESSION['SESS_PRIVILEGE_ID']=$userInfo["privilegeid"];
	    $_SESSION['SESS_PRIVILEGE_NAME']=$privilegeData["privilege_name"];
	    $_SESSION['SESS_ACCESS_ID']=$userInfo["accessid"];
	    $_SESSION['SESS_GROUP_ID']=$userInfo["groupid"];
	    //$_SESSION['SESS_ACCESS_SITES']=$accessData["access"];
	    $_SESSION['SESS_USER_AVATAR']=$userInfo["avatarlink"];

	    $_SESSION['SESS_POLICY'] = $finalScope;

	    $_SESSION['SESS_LOGIN_SITE']=SITENAME;
	    $_SESSION['SESS_ACTIVE_SITE']=SITENAME;
	    
	    $_SESSION["SESS_PRIVILEGE_HASH"]=md5($userInfo["privilegeid"].$privilegeData["privilege_name"]);

		return true;
	}
}
?>