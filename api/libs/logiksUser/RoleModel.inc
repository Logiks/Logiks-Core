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

	//Checks if a single activity exists for the provided scope/module
	//ie if the scope is required
	public static function checkScope($module,$actionType="ACCESS") {
		$actionType=strtoupper($actionType);
		$roleHash=md5($module.$actionType);
		if(isset($_SESSION["ROLESCOPE"][$roleHash])) {
			return $_SESSION["ROLESCOPE"][$roleHash];
		}

		$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module));

		$r=_dbQuery($sql,true);
		$d=_dbData($r,true);
		_dbFree($r,true);

		$_SESSION["ROLESCOPE"][$roleHash]=0;

		if($d[0]['cnt']>0) {
			$privilegehash=$_SESSION["SESS_PRIVILEGE_HASH"];
			$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"allow"=>"true", "privilegehash"=>$privilegehash));

			$r=_dbQuery($sql,true);
			$d=_dbData($r,true);
			_dbFree($r,true);

			if($d[0]['cnt']>0) {
				$_SESSION["ROLESCOPE"][$roleHash]=2;
			} else {
				$_SESSION["ROLESCOPE"][$roleHash]=1;
			}
		}
		
		return $_SESSION["ROLESCOPE"][$roleHash];
	}

	public static function checkRole($module,$activity,$actionType="ACCESS") {
		$actionType=strtoupper($actionType);
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]<=ROLE_PRIME) {
			return true;
		}
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);
			} else {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5("0guest");
			}
		}
		if(isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			$privilegehash=$_SESSION["SESS_PRIVILEGE_HASH"];

			$roleHash=md5($module.$activity.$actionType.$privilegehash);
			if(isset($_SESSION["ROLEMODEL"][$roleHash])) {
				return $_SESSION["ROLEMODEL"][$roleHash];
			}

			$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"id,allow,role_type,count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
					->_orderby("id desc");

			$r=_dbQuery($sql,true);
			if($r) {
				$data=_dbData($r,true);
				_dbFree($r,true);
				$data=$data[count($data)-1];
				if($data['cnt']==0) {
					RoleModel::registerRole($module,$activity,$actionType);
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
		return (!getConfig("ROLEMODEL_DEFAULT_DENY"));
	}
	public static function registerRole($module,$activity,$actionType="ACCESS") {
		$actionType=strtoupper($actionType);
		$roleTbl=_dbTable("rolemodel",true);
		$userid=$_SESSION['SESS_USER_ID'];
		$site=SITENAME;
		if(isset($_SESSION['SESS_GUID'])) {
			$guid=$_SESSION['SESS_GUID'];
		} else {
			$guid="global";
		}

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
				$privilegehash=md5($d['id'].$d['name']);
				$privilegename=$d['name'];

				$sql=_db(true)->_selectQ($roleTbl,"count(*) as cnt")
					->_where(array("site"=>$site,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash));
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					if($data[0]['cnt']==0) {
						$finalData[]=[
								//"id"=>"",
								"guid"=>$guid,
								"site"=>$site,
								"category"=>$actionType,
								"module"=>$module,
								"activity"=>$activity,
								"privilegehash"=>$privilegehash,
								"allow"=>(getConfig("ROLEMODEL_DEFAULT_DENY")?"false":"true"),
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
							"category"=>$actionType,
							"module"=>$module,
							"activity"=>$activity,
							"privilegehash"=>$privilegehash,
							"allow"=>(getConfig("ROLEMODEL_DEFAULT_DENY")?"false":"true"),
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
					"category"=>$actionType,
					"module"=>$module,
					"activity"=>$activity,
					"privilegehash"=>md5("0guest"),
					"allow"=>(getConfig("ROLEMODEL_DEFAULT_DENY")?"false":"true"),
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
