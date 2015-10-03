<?php
/*
 * This class contains the Privilege/Permission/Role Model based permission system
 * for all the logical inputs within Logiks Framework
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("checkUserRoles")) {
	function checkUserRoles($module,$activity,$category="Block") {
		return RoleModel::checkRole($module,$activity,$category);
	}
}

class RoleModel {
	public static function checkRole($module,$activity,$category="Block") {
		$pridid="*";
		if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $pridid=$_SESSION["SESS_PRIVILEGE_ID"];
		if(is_numeric($pridid) && $pridid>=0 && $pridid<=2) return true;
		if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $pridid=$_SESSION["SESS_PRIVILEGE_NAME"];
		$sql="SELECT id,access,role_type,count(*) as cnt FROM "._dbtable("rolemodel",true)." WHERE ";
		$sql.="site='".SITENAME."' AND category='$category' AND module='$module' AND activity='$activity' AND privilegeid='$pridid'";
		$r=_dbQuery($sql,true);
		if($r) {
			$data=_dbData($r);
			$data=$data[0];
			if($data['cnt']==0) {
				RoleModel::registerRole($module,$activity,$category);
				return false;
			} else {
				return ($data['access']=="true")?true:false;
			}
		}
		return false;
	}
	public static function registerRole($module,$activity,$category="Block") {
		$sql="SELECT id,name FROM "._dbtable("privileges",true)." WHERE site='".SITENAME."' OR site='*'";
		$r=_dbQuery($sql,true);
		$sql1="";
		$date=date('Y-m-d');
		$roleTbl=_dbtable("rolemodel",true);
		$userid="auto";
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && isset($_SESSION["SESS_USER_ID"]) &&
			is_numeric($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]>0) {
			$userid=$_SESSION["SESS_USER_ID"];
		}
		if($r) {
			$data=_dbData($r);
			foreach($data as $d) {
				$pid=$d['id'];
				if($pid<=2) continue;
				$privilegeid=$d['name'];
				$sql="SELECT count(*) as cnt FROM {$roleTbl} WHERE ";
				$sql.="site='".SITENAME."' AND category='$category' AND module='$module' AND activity='$activity' AND privilegeid='$privilegeid'";
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					$data=$data[0];
					if($data['cnt']==0) {
						$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegeid','false','auto','$userid','$date','$date'),";
					}
				} else {
					$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegeid','false','auto','$userid','$date','$date'),";
				}
			}
		}
		$sql1.="(0,'".SITENAME."','$category','$module','$activity','guest','false','auto','$userid','$date','$date'),";
		$sql1=trim($sql1);
		if(strlen($sql1)>0) {
			if((strpos($sql1,",",strlen($sql1)-2))==(strlen($sql1)-1))
				$sql1=substr($sql1,0,strlen($sql1)-1);
			if(strlen($sql1)>1) {		
				$sql="INSERT INTO {$roleTbl} (id,site,category,module,activity,privilegeid,access,role_type,userid,doc,doe) VALUES $sql1";
				_dbQuery($sql,true);
			}
		}
	}
	function cleanRoleModelCache($site) {
		//ToDoS
	}
	function createRoleModelCache($site,$prevID) {
		//Creates a live dump of cache
	}
}
?>
