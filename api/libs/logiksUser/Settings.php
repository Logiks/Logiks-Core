<?php
/*
 * This class contains the settings related functionlities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getSettings")) {

	function getSettings($name,$defaultValue="",$scope="default") {//,$type="string",$editParams="",$class=""
		if(strlen($name)<=0 || !isset($_SESSION['SESS_USER_ID'])) return $defaultValue;

		$sql=_db(true)->_selectQ(_dbTable("settings",true),"name,settings")->_where(array(
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>SITENAME,
				"scope"=>$scope,
				"name"=>$name,
			));
		$res=_dbQuery($sql,true);
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
			if(isset($data[0])) {
				return $data[0]['settings'];
			} else {
				registerSettings($name,$value,$scope);
			}
		}
		return $defaultValue;
	}
	
	function setSettings($name,$value="",$scope="default") {
		if(strlen($name)<=0 || !isset($_SESSION['SESS_USER_ID'])) return $defaultValue;

		$sql=_db(true)->_selectQ(_dbTable("settings",true),"name,settings")->_where(array(
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>SITENAME,
				"scope"=>$scope,
				"name"=>$name,
			));
		$res=_dbQuery($sql,true);
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
			if(isset($data[0])) {
				$data=array(
						"settings"=>$value,
					);
				$q=_db(true)->_updateQ(_dbTable("settings",true),$data,array(
						"userid"=>$_SESSION['SESS_USER_ID'],
						"site"=>SITENAME,
						"scope"=>$scope,
						"name"=>$name,
					));
				_dbQuery($q,true);
				return $value;
			}
		}
		if(registerSettings($name,$value,$scope)) return $value;
		return false;
	}
	
	function registerSettings($name,$value="",$scope="default") {
		$data=array(
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>SITENAME,
				"scope"=>$scope,
				"name"=>$name,
				"settings"=>$value,
			);
		$q=_db(true)->_insertQ1(_dbTable("settings",true),$data);
		$res=_dbQuery($q,true);
		if($res) return true;
		else return false;
	}
}
?>