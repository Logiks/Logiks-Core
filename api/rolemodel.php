<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("checkUserRoles")) {
	function checkUserRoles($module,$activity,$category="Block") {
		$pridid="*";
		if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $pridid=$_SESSION["SESS_PRIVILEGE_ID"];
		if($pridid<=3) return true;
		if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $pridid=$_SESSION["SESS_PRIVILEGE_NAME"];
		$sql="SELECT id,access,role_type,count(*) as cnt FROM "._dbtable("rolemodel",true)." WHERE ";
		$sql.="site='".SITENAME."' AND category='$category' AND module='$module' AND activity='$activity' AND privilegeid='$pridid'";
		$r=_dbQuery($sql,true);
		if($r) {
			$data=_dbData($r);
			$data=$data[0];
			if($data['cnt']==0) {
				RoleModel::registerRole($module,$activity,$category="Block");
				return false;
			} else {
				return ($data['access']=="true")?true:false;
			}
		}
		return false;
	}
	
	function checkServiceRoles($path,$autoExit=true) {
		$pdir=$path."config/.permissions/";
		if(file_exists($pdir."services.php") && is_dir($pdir)) {
			include $pdir."services.php";
			if(isset($roles[$_REQUEST['scmd']])) {
				checkServiceSession($autoExit);
				$a=$roles[$_REQUEST['scmd']];
				if(is_array($a)) {
					if(in_array("*",$a)) {
						return true;
					} elseif(in_array($_SESSION["SESS_PRIVILEGE_NAME"],$a)) {
						return true;
					}
				} else {
					if($a=="*") {
						return true;
					} elseif(strpos("#".$roles[$_REQUEST['scmd']],$_SESSION["SESS_PRIVILEGE_NAME"].",")>0) {
						return true;
					} 
				}
				return false;
			}
		}
		return true;
	}
}
class RoleModel {
	public static function registerRole($module,$activity,$category) {
		$sql="SELECT id,name FROM "._dbtable("privileges",true)." WHERE site='".SITENAME."' OR site='*'";
		$r=_dbQuery($sql,true);
		$sql1="";
		$date=date('Y-m-d');
		$roleTbl=_dbtable("rolemodel",true);
		if($r) {
			$data=_dbData($r);
			foreach($data as $d) {
				$pid=$d['id'];
				if($pid<=3) continue;
				$privilegeid=$d['name'];
				$sql="SELECT count(*) as cnt FROM {$roleTbl} WHERE ";
				$sql.="site='".SITENAME."' AND category='$category' AND module='$module' AND activity='$activity' AND privilegeid='$privilegeid'";
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					$data=$data[0];
					if($data['cnt']==0) {
						$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegeid','false','auto','$date','$date'),";
					}
				} else {
					$sql1.="(0,'".SITENAME."','$category','$module','$activity','$privilegeid','false','auto','$date','$date'),";
				}
			}
		}
		$sql1=trim($sql1);
		if(strlen($sql1)>0) {
			if((strpos($sql1,",",strlen($sql1)-2))==(strlen($sql1)-1))
				$sql1=substr($sql1,0,strlen($sql1)-1);
			
			if(strlen($sql1)>1) {		
				$sql="INSERT INTO {$roleTbl} (id,site,category,module,activity,privilegeid,access,role_type,doc,doe) VALUES $sql1";
				_dbQuery($sql,true);
			}
		}
	}
	function fixRoleModel() {
		
	}
}
?>
