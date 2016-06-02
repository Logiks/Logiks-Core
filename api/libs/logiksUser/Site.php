<?php
/*
 * This class contains the all Site related user functionalities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getUserList")) {
	function getUserList($cols=null, $where="", $orderBy="", $limit=null) {
		if($cols==null || count($cols)==0) {
			$cols=array("userid", "privilegeid", "accessid", "name", "email", "address", "region", "country", "zipcode", "mobile", "avatar", "avatar_type");
		}
		$sql=_db(true)->_selectQ(_dbTable("users",true),$cols)->_where(array(
				"blocked"=>'false'
			));
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]>ROLE_PRIME) {
			//$sql=$sql->_where(" (site='".SITENAME."' OR site='*')");
			$sql1=_db(true)->_selectQ(_dbTable("access",true),"id")->_where(array(
				"blocked"=>'false'
			))->_where(" (FIND_IN_SET('".SITENAME."',sites) OR sites='*')");
			$sql=$sql->_query("accessid",$sql1);
		}
		if(strlen($where)>0) {
			$sql=$sql->_where(" ($where)");
		}
		if(strlen($orderBy)>0) {
			$sql=$sql->_orderBy($orderBy);
		}
		if(is_array($limit)) {
			$sql=$sql->_limit($limit);
		} elseif(strlen($limit)>0) {
			$sql=$sql->_limit($limit);
		}
		
		$res=_dbQuery($sql,true);
		$data=[];
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);

			foreach ($data as $a => $row) {
				$data[$a]['avatarlink']=getUserAvatar($row);
			}
		}
		return $data;
	}

	function getPrivilegeList($where="") {
		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"id,name,site,remarks,blocked")->_where(array(
				"blocked"=>'false'
			));
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]>ROLE_PRIME) {
			$sql=$sql->_where(" (site='".SITENAME."' OR site='*')");
		}
		if(strlen($where)>0) {
			$sql=$sql->_where(" ($where)");
		}

		$res=_dbQuery($sql,true);
		$data=[];
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
		}
		return $data;
	}

	function getPrivilegeByName($privilageName) {
		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"id,name,site,remarks,blocked")->_where(array(
				"blocked"=>'false',
				"name"=>$privilageName,
			));
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]>ROLE_PRIME) {
			$sql=$sql->_where(" (site='".SITENAME."' OR site='*')");
		}

		$res=_dbQuery($sql,true);
		$data=[];
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
		}
		return $data;
	}

	function checkUserID($userid,$site=SITENAME) {
		if($userid=="root") return true;
		if(!isset($_SESSION['SESS_PRIVILEGE_ID']) || $_SESSION['SESS_PRIVILEGE_ID']>2) {
			$site=SITENAME;
		}

		$sql=_db(true)->_selectQ(_dbTable("users",true),"count(*) as cnt")->_where(array(
				"blocked"=>'false',
				"userid"=>$userid,
			));
		$sql1=_db(true)->_selectQ(_dbTable("access",true),"id")->_where(array(
			"blocked"=>'false',
		))->_where(" (FIND_IN_SET('".SITENAME."',sites) OR sites='*')");
		$sql=$sql->_query("accessid",$sql1);

		$res=_dbQuery($sql,true);
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
			return ($data[0]['cnt']>0)?true:false;
		}
		return false;
	}
}
?>