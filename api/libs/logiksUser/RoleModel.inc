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
			}
		}
		if(isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			$privilegehash=$_SESSION["SESS_PRIVILEGE_HASH"];

			$roleHash=md5($module.$activity.$category.$privilegehash);
			if(isset($_SESSION["ROLEMODEL"][$roleHash])) {
				return $_SESSION["ROLEMODEL"][$roleHash];
			}

			$sql=_db(true)->_selectQ(_dbTable("rolemodel",true),"id,allow,role_type,count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$category,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash));

			$r=_dbQuery($sql,true);
			if($r) {
				$data=_dbData($r,true);
				_dbFree($r,true);
				$data=$data[0];
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
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			return false;
		}
		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"id,name,hash")
					->_where("site='".SITENAME."' OR site='*'");

		$r=_dbQuery($sql,true);
		
		$sql1="";
		$date=date('Y-m-d');
		$roleTbl=_dbTable("rolemodel",true);
		$userid="auto";
		if($r) {
			$data=_dbData($r,true);
			_dbFree($r,true);
			foreach($data as $d) {
				$pid=$d['id'];
				$privilegehash=$d['hash'];
				$privilegename=$d['name'];

				$sql=_db(true)->_selectQ($roleTbl,"count(*) as cnt")
					->_where(array("site"=>SITENAME,"category"=>$category,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash));
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					$data=$data[0];
					if($data['cnt']==0) {
						$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegehash','$privilegename','false','auto','$userid'),";
					}
				} else {
					$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegehash','$privilegename','false','auto','$userid'),";
				}
			}
		}
		$sql1.="(0,'".SITENAME."','$category','$module','$activity','".md5('guest')."','Guest','false','auto','$userid'),";
		$sql1=trim($sql1);
		if(strlen($sql1)>0) {
			if((strpos($sql1,",",strlen($sql1)-2))==(strlen($sql1)-1)) {
				$sql1=substr($sql1,0,strlen($sql1)-1);
			}
			if(strlen($sql1)>1) {
				$sql="INSERT INTO {$roleTbl} (id,site,category,module,activity,privilegehash,remarks,allow,role_type,creator) VALUES $sql1";
				_dbQuery($sql,true);
			}
		}
	}
	public static function cleanRoleModelCache() {
		if(isset($_SESSION["ROLEMODEL"])) {
			unset($_SESSION["ROLEMODEL"]);
		}
	}
}
?>
