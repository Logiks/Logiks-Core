<?php
/*
 * This class is used for managing site and user settings
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getSettings")) {
	//User Specific Settings
	function getSettings($name,$defaultValue="",$scope="default",$type="string",$editParams="",$class="") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
		$q=getSysDBLink()->_selectQ(_dbtable("config_users",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(getSysDBLink()->recordCount($result)>0) {
			$data=getSysDBLink()->fetchData($result);
			return $data["value"];
		} else {
			$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name,"value"=>$defaultValue,"type"=>$type,"edit_params"=>$editParams,"class"=>$class,"doc"=>date("Y-m-d"),"doe"=>date("Y-m-d"));
			$q=getSysDBLink()->_insertQ1(_dbtable("config_users",true),$w);
			_dbQuery($q,true);
			return $defaultValue;
		}
	}
	function setSettings($name,$value) {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
		$q=getSysDBLink()->_updateQ(_dbtable("config_users",true),array("value"=>$value,"doe"=>date("Y-m-d")),$w);
		$result=_dbQuery($q,true);
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	function getSettingsForScope($scope) {
		if(strlen($scope)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID']);
		$q=_db()->_selectQ(_dbtable("config_users",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(_db()->recordCount($result)>0) {
			$arr=array();
			while($rec=_db()->fetchData($result)) {
				$arr[$rec["id"]]=array();
				$arr[$rec["id"]]["name"]=$rec["name"];
				$arr[$rec["id"]]["value"]=$rec["value"];
			}
			return $arr;
		}
		return array();
	}
	function removeSettings($name,$scope="default") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
		$q=_db()->_deleteQ(_dbtable("config_users",true),$w);
		_dbQuery($q,true);
	}
	function registerSettings($name,$defaultValue="",$scope="default",$type="string",$editParams="",$class="") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
		$q=_db()->_selectQ(_dbtable("config_users",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(getSysDBLink()->recordCount($result)>0) {
			$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
			$q=getSysDBLink()->_updateQ(_dbtable("config_users",true),array("value"=>$defaultValue,"type"=>$type,"edit_params"=>$editParams,"class"=>$class,"doe"=>date("Y-m-d")),$w);
			$result=_dbQuery($q,true);
		} else {
			$w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name,"value"=>$defaultValue,"type"=>$type,"edit_params"=>$editParams,"class"=>$class,"doc"=>date("Y-m-d"),"doe"=>date("Y-m-d"));
			$q=getSysDBLink()->_insertQ1(_dbtable("config_users",true),$w);
			$result=_dbQuery($q,true);
		}
		return $defaultValue;
	}
	function findSettings($name,$scope="default") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		if($name=="*") $w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID']);
		else $w=array("site"=>SITENAME,"scope"=>$scope,"userid"=>$user['SESS_USER_ID'],"name"=>$name);
		$q=_db()->_selectQ(_dbtable("config_users",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(_db(true)->recordCount($result)>0) {
			return _db(true)->recordCount($result);
		} else {
			return false;
		}
	}
}
if(!function_exists("getSiteSettings")) {
	//Site Specific Settings
	function getSiteSettings($name,$defaultValue="",$scope="default",$type="string",$editParams="",$class="") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"name"=>$name);
		$q=getSysDBLink()->_selectQ(_dbtable("config_sites",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(getSysDBLink()->recordCount($result)>0) {
			$data=getSysDBLink()->fetchData($result);
			return $data["value"];
		} else {
			$w=array("site"=>SITENAME,"scope"=>$scope,"name"=>$name,"value"=>$defaultValue,"type"=>$type,"edit_params"=>$editParams,"class"=>$class,"doc"=>date("Y-m-d"),"doe"=>date("Y-m-d"));
			$q=getSysDBLink()->_insertQ1(_dbtable("config_sites",true),$w);
			_dbQuery($q,true);
			return $defaultValue;
		}
	}
	function setSiteSettings($name,$value) {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"name"=>$name);
		$q=getSysDBLink()->_updateQ(_dbtable("config_sites",true),array("value"=>$value),$w);
		$result=_dbQuery($q,true);
		if($result) {
			return true;
		} else {
			return false;
		}
	}
	function getSiteSettingsForScope($scope) {
		if(strlen($scope)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope);
		$q=_db()->_selectQ(_dbtable("config_sites",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(_db()->recordCount($result)>0) {
			$arr=array();
			while($rec=_db()->fetchData($result)) {
				$arr[$rec["id"]]=array();
				$arr[$rec["id"]]["name"]=$rec["name"];
				$arr[$rec["id"]]["value"]=$rec["value"];
			}
			return $arr;
		}
		return array();
	}
	function removeSiteSettings($name,$scope="default",$site=null) {
		if($site==null) {
			if(defined("SITENAME")) $site=SITENAME;
			elseif(isset($_SESSION["LGKS_SESS_SITE"])) $site=$_SESSION["LGKS_SESS_SITE"];
			else $site="";
		}
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>$site,"scope"=>$scope,"name"=>$name);
		$q=_db()->_deleteQ(_dbtable("config_sites",true),$w);
		_dbQuery($q,true);
	}
	function registerSiteSettings($name,$defaultValue="",$scope="default",$type="string",$editParams="",$class="") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"name"=>$name);
		$q=getSysDBLink()->_selectQ(_dbtable("config_sites",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(getSysDBLink()->recordCount($result)>0) {
			$w=array("site"=>SITENAME,"name"=>$name);
			$q=getSysDBLink()->_updateQ(_dbtable("config_sites",true),array("value"=>$value),$w);
			_dbQuery($q,true);
		} else {
			$w=array("site"=>SITENAME,"scope"=>$scope,"name"=>$name,"value"=>$defaultValue,"type"=>$type,"edit_params"=>$editParams,"class"=>$class,"doc"=>date("Y-m-d"),"doe"=>date("Y-m-d"));
			$q=getSysDBLink()->_insertQ1(_dbtable("config_sites",true),$w);
			_dbQuery($q,true);
		}
		return $defaultValue;
	}
	function findSiteSettings($name,$scope="default") {
		if(strlen($name)<=0) return "";
		$user=getUserInfo();
		$w=array("site"=>SITENAME,"scope"=>$scope,"name"=>$name);
		$q=getSysDBLink()->_selectQ(_dbtable("config_sites",true),array("id","name","value"),$w);
		$result=_dbQuery($q,true);
		if(getSysDBLink()->recordCount($result)>0) {
			return _db(true)->recordCount($result);
		} else {
			return false;
		}
	}
}
?>
