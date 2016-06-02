<?php
/*
 * This class contains the Privilege/Permission/Role Model based permission system
 * for all the logical inputs within Logiks Framework
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class RoleModel {
	public static function checkRole($module,$activity,$category="Block") {
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]<=ROLE_PRIME) {
			return true;
		}
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_NAME"].$_SESSION["SESS_PRIVILEGE_ID"]);
			} else {
				if($module=="PAGES") {
					return (strtolower(getConfig("ACCESS"))=="public");
				}
			}
		}
		if(isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			$privilegehash=$_SESSION["SESS_PRIVILEGE_HASH"];

			$roleHash=md5($module.$activity.$category.$privilegehash);
			if(isset($_SESSION["ROLEMODEL"][$roleHash])) {
				return $_SESSION["ROLEMODEL"][$roleHash];
			}

			$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"id,allow,role_type,count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$category,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
					->_orderby("id desc");

			$r=_dbQuery($sql,true);
			if($r) {
				$data=_dbData($r,true);
				_dbFree($r,true);
				$data=$data[count($data)-1];
				if($data['cnt']==0) {
					RoleModel::registerRole($module,$activity,$category);
					$_SESSION["ROLEMODEL"][$roleHash]=false;
					return false;
				} else {
					$_SESSION["ROLEMODEL"][$roleHash]=($data['allow']=="true")?true:false;
					return $_SESSION["ROLEMODEL"][$roleHash];
				}
			}
		} else {
			trigger_logikserror("Role access error");
		}
		return false;
	}
	public static function registerRole($module,$activity,$category="Block") {
		$roleTbl=_dbTable("rolemodel",true);
		$userid=$_SESSION['SESS_USER_ID'];
		$site=SITENAME;
		$guid=$_SESSION['SESS_GUID'];

		$finalData=[];

		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"id,name")
							->_whereOR("site",[$site,"*"]);
		$r=_dbQuery($sql,true);
		if($r) {
			$data=_dbData($r,true);
			_dbFree($r,true);
			foreach($data as $d) {
				if($d['id']<=ROLE_PRIME) continue;

				$pid=$d['id'];
				$privilegehash=md5($d['name'].$d['id']);
				$privilegename=$d['name'];

				$sql=_db(true)->_selectQ($roleTbl,"count(*) as cnt")
					->_where(array("site"=>$site,"category"=>$category,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash));
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					if($data[0]['cnt']==0) {
						$finalData[]=[
								//"id"=>"",
								"guid"=>$guid,
								"site"=>$site,
								"category"=>$category,
								"module"=>$module,
								"activity"=>$activity,
								"privilegehash"=>$privilegehash,
								"remarks"=>$privilegename,
								"role_type"=>"auto",
								"creator"=>$userid
							];
					}
				} else {
					$finalData[]=[
							//"id"=>"",
							"guid"=>$guid,
							"site"=>$site,
							"category"=>$category,
							"module"=>$module,
							"activity"=>$activity,
							"privilegehash"=>$privilegehash,
							"remarks"=>$privilegename,
							"role_type"=>"auto",
							"creator"=>$userid
						];
				}
			}
		}
		$finalData[]=[
					//"id"=>"",
					"guid"=>$guid,
					"site"=>$site,
					"category"=>$category,
					"module"=>$module,
					"activity"=>$activity,
					"privilegehash"=>md5("guest"),
					"remarks"=>"guest",
					"role_type"=>"auto",
					"creator"=>$userid
				];
		return _db(true)->_insert_batchQ($roleTbl,$finalData)->_run();
	}
	public static function cleanRoleModelCache() {
		if(isset($_SESSION["ROLEMODEL"])) {
			unset($_SESSION["ROLEMODEL"]);
		}
	}
}
?>
