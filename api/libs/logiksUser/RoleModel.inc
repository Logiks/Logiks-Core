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
	
	//Load all scoperules into Session
	protected static function loadScopeRules() {
		$_SESSION["ROLESGLOBAL"][SITENAME]=[];
		$_SESSION["SCOPEMAP"][SITENAME]=[];
		
		$scopeFiles=[
					ROOT.CFG_FOLDER."masters/roles.json"=>"GLOBALS",
					ROOT.CFG_FOLDER."jsonConfig/roles.json"=>"GLOBALS",
					APPROOT.APPS_CONFIG_FOLDER."roles.json"=>"SITE",
				];
		
		$scopeArr=[];$scopeMap=[];
		foreach($scopeFiles as $scf=>$typ) {
			if(file_exists($scf)) {
				try {
					$scArr=json_decode(file_get_contents($scf),true);
					if(is_array($scArr)) {
						if(!isset($scArr['GLOBALS'])) $scArr['GLOBALS']=[];
						if(!isset($scArr[SITENAME])) $scArr[SITENAME]=[];
						
						if(isset($scArr["scopemap"])) {
							$scopeMap=array_merge($scopeMap,$scArr["scopemap"]);
						}

						$scArr=array_merge($scArr['GLOBALS'],$scArr[SITENAME]);

						$scopeArr=array_merge($scopeArr,$scArr);
					}
				} catch(Exception $e) {

				}
			}
		}
		$_SESSION["SCOPEMAP"][SITENAME]=$scopeMap;
		$_SESSION["ROLESGLOBAL"][SITENAME]=$scopeArr;
	}
	
	//Gets the scope name for the given module
	protected static function getScopeModule($module) {
		if(!isset($_SESSION["SCOPEMAP"][SITENAME])) {
			RoleModel::loadScopeRules();
		}
		if(isset($_SESSION["SCOPEMAP"][SITENAME][$module])) {
			return $_SESSION["SCOPEMAP"][SITENAME][$module];
		}
		return $module;
	}
	
	//Generates all role scopes for users, modules etc by default
	protected static function getGlobalScopes() {
		if(!isset($_SESSION["ROLESGLOBAL"][SITENAME])) {
			RoleModel::loadScopeRules();
		}
		return $_SESSION["ROLESGLOBAL"][SITENAME];
	}
	
	//Checks the global policy that came with application and returns
	// -1 Not defined in the global scope
	//  0	Global scope is found, but no matching condition, so not allowed
	//  1	Allowed in global policy
	public static function checkGlobals($policyStr,$reloadScopes=false) {
		if(!isset($_SESSION['SESS_GUID'])) $_SESSION['SESS_GUID']="guest";
		
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]<=ROLE_PRIME) {
			return 1;
		}
		
		if($reloadScopes) {
			unset($_SESSION["ROLESGLOBAL"][SITENAME]);
		}
		$gScopes=RoleModel::getGlobalScopes();

		$policyStr=str_replace(".MAIN","",$policyStr);
		$policyStr=str_replace(".MASTER","",$policyStr);
		$policyStr=str_replace(".INDEX","",$policyStr);
		$policyStr=str_replace(".CORE","",$policyStr);
		$policyStr=str_replace(".ACCESS","",$policyStr);
// 		$policyStr=str_replace(".PAGES","",$policyStr);
		
		if(isset($gScopes[strtolower($policyStr)])) {
			$policyStr=strtolower($policyStr);
		}

		if(isset($gScopes[$policyStr])) {
			if(!isset($gScopes[$policyStr]['access_control'])) {
				$gScopes[$policyStr]['access_control']="public";
			}
			switch($gScopes[$policyStr]['access_control']) {
				case "public":
					return 1;
				break;
				case "private":
					if(isset($gScopes[$policyStr]['users'])) {
						if(in_array($_SESSION['SESS_USER_ID'],$gScopes[$policyStr]['users'])) {
							return 1;
						}
					} elseif(isset($gScopes[$policyStr]['privileges'])) {
						if(in_array($_SESSION["SESS_PRIVILEGE_NAME"],$gScopes[$policyStr]['privileges'])) {
							return 1;
						} elseif(in_array(RoleModel::getPrivilegeHash(),$gScopes[$policyStr]['privileges'])) {
							return 1;
						}
					} else {
						return 0;
					}
				break;
			}
			return 0;
		} else {
			return -1;
		}
	}
	
	//Checks if a single activity exists for the provided scope/module
	//ie if the scope is required
	public static function checkScope($module,$actionType="ACCESS") {
		$actionType=strtoupper($actionType);
		
		$activity="MAIN";
		$moduleOriginal=$module;
		$module=RoleModel::getScopeModule($module);
		$module=explode(".",$module);
		if(count($module)>1) {
			$activity=str_replace("{$moduleOriginal}.{$moduleOriginal}.","{$moduleOriginal}.",implode(".",array_slice($module,1)).".{$activity}");
			$module=$module[0];
		} else {
			$module=implode(".",$module);
		}
		
		$moduleArr=explode(".",$module);
		if(count($moduleArr)>1) {
			$module=$moduleArr[0];
			array_shift($moduleArr);
			$activity=strtoupper(implode(".",$moduleArr));
		}
		
		$policyStr=[$module,$activity,strtoupper($actionType)];
		
		$policyStr=implode(".",$policyStr);
		$globalStatus=RoleModel::checkGlobals($policyStr);
// 		var_dump($globalStatus);
		
		if($globalStatus>0) {
			return true;
		}
		
		$privilegehash=RoleModel::getPrivilegeHash();
		$roleHash=md5(strtoupper($module.$activity.$actionType.$privilegehash));
		
		if(isset($_SESSION["ROLESCOPE"][$roleHash])) {
			return $_SESSION["ROLESCOPE"][$roleHash];
		}

		$_SESSION["ROLESCOPE"][$roleHash]=0;

		$privilegehash=RoleModel::getPrivilegeHash();
		$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"count(*) as cnt")
				->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"allow"=>"true", "privilegehash"=>$privilegehash))
				->_where(['guid'=>$_SESSION['SESS_GUID']]);
// 		exit($sql->_SQL());
		$r=_dbQuery($sql,true);
		$d=_dbData($r,true);
		_dbFree($r,true);

		if($d[0]['cnt']>0) {
			$_SESSION["ROLESCOPE"][$roleHash]=2;
		} else {
			RoleModel::registerRole($module,$activity);
			$_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
		}
		
		return $_SESSION["ROLESCOPE"][$roleHash];
	}

	public static function checkRole($module,$activity,$actionType="ACCESS") {
		$moduleOriginal=$module;
		$module=RoleModel::getScopeModule($module);
		$module=explode(".",$module);
		if(count($module)>1) {
			$activity=str_replace("{$moduleOriginal}.{$moduleOriginal}.","{$moduleOriginal}.",implode(".",array_slice($module,1)).".{$activity}");
			$module=$module[0];
		} else {
			$module=implode(".",$module);
		}
		$activity=preg_replace("/{$module}./","",$activity);
		if($activity==null || strlen($activity)<=0) $activity="MAIN";

		$actionType=strtoupper($actionType);
		
		$policyStr=[$module,strtoupper($activity),strtoupper($actionType)];
		$policyStr=implode(".",$policyStr);
		$globalStatus=RoleModel::checkGlobals($policyStr);
// 		var_dump($globalStatus);
		
		if($globalStatus>0) {
			return true;
		}
		
		$privilegehash=RoleModel::getPrivilegeHash();
		$roleHash=md5(strtoupper($module.$activity.$actionType.$privilegehash));
		
		if(isset($_SESSION["ROLEMODEL"][$roleHash])) {
			return $_SESSION["ROLEMODEL"][$roleHash];
		}

		$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"id,allow,role_type,count(*) as cnt")
				->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
				->_where(['guid'=>$_SESSION['SESS_GUID']])
				->_orderby("id desc");

		$r=_dbQuery($sql,true);
		if($r) {
			$data=_dbData($r,true);
			_dbFree($r,true);
			$data=$data[count($data)-1];
			if($data['cnt']==0) {
				RoleModel::registerRole($module,$activity,$actionType);
				$_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
				return (getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
			} else {
				$_SESSION["ROLEMODEL"][$roleHash]=($data['allow']=="true")?true:false;
				return $_SESSION["ROLEMODEL"][$roleHash];
			}
		}
		
		return (!getConfig("ROLEMODEL_DEFAULT_DENY"));
	}
	public static function registerRole($module,$activity,$actionType="ACCESS") {
		$activity=preg_replace("/".strtolower($module)."./","",strtolower($activity));
		if($activity==null || strlen($activity)<=0) $activity="MAIN";

		$activity=strtoupper($activity);
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
					->_where(array("site"=>$site,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
					->_where(['guid'=>$_SESSION['SESS_GUID']]);
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
								"created_by"=>$userid,
								"edited_by"=>$userid
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
							"created_by"=>$userid,
							"edited_by"=>$userid
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
					"created_by"=>$userid,
					"edited_by"=>$userid
				];
		return _db(true)->_insert_batchQ($roleTbl,$finalData)->_run();
	}
	
	//Clears all the cache in session
	public static function cleanRoleModelCache() {
		if(isset($_SESSION["ROLEMODEL"])) {
			unset($_SESSION["ROLEMODEL"]);
		}
		if(isset($_SESSION["ROLESCOPE"])) {
			unset($_SESSION["ROLESCOPE"]);
		}
		if(isset($_SESSION["ROLESGLOBAL"])) {
			unset($_SESSION["ROLESGLOBAL"]);
		}
	}
	
	//Generates and returns the Required Privilege Hash for the Current Loggedin User
	public static function getPrivilegeHash() {
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);
			} else {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5("0guest");
			}
		}
		//trigger_logikserror("Role access error");
		return $_SESSION["SESS_PRIVILEGE_HASH"];
	}
}
?>
