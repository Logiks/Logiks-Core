<?php
/*
 * This class contains the all User related functionalities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getUserList")) {
	loadHelpers("pwdhash");

	function getUserAvatar($params) {
		if(is_array($params)) {
			if(isset($params['avatar'])) {
				if(strpos("#".$params['avatar'], "http://") || strpos("#".$params['avatar'], "https://")) {
					return $params['avatar'];
				} elseif(isset($params['avatar_type'])) {
					return _service("avatar")."&authorid={$params['avatar']}&method={$params['avatar_type']}";
				} elseif(is_numeric($params['avatar'])) {
					return _service("avatar")."&authorid={$params['avatar']}&method=photoid";
				} else {
					return _service("avatar")."&authorid={$params['avatar']}&method=email";
				}
			} elseif(isset($params['userid'])) {
				$params=$params['userid'];
			} else {
				return loadMedia("images/user.png");
			}
		}
		//SQL Type
		$sql=_db(true)->_selectQ(_dbTable("users",true),"avatar,avatar_type")->_where(array(
				"blocked"=>'false',
				"userid"=>$params,
			));
		$res=_dbQuery($sql,true);
		$data=[];
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
			if(isset($data[0])) {
				$data=$data[0];
				return getUserAvatar($data);
			}
		}
		return loadMedia("images/user.png");
	}
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

	function getUserID() {
		if(isset($_SESSION['SESS_USER_ID'])){
			$user=$_SESSION['SESS_USER_ID'];
		} else {
			$user="Guest";
		}
		return $user;
	}

	function getUserInfo($userid=null) {
		if($userid==null) $userid=$_SESSION['SESS_USER_ID'];
		if(isset($_SESSION["USERINFO"][$userid])) {
			return $_SESSION["USERINFO"][$userid];
		}
		$sql=_db(true)->_selectQ(_dbTable("users",true),"*")->_where(array(
				"blocked"=>'false',
				"userid"=>$userid,
			));
		$res=_dbQuery($sql,true);
		$data=[];
		if($res) {
			$data=_dbData($res,true);
			_dbFree($res,true);
			if(isset($data[0])) $data=$data[0];
		}
		$data['avatarlink']=getUserAvatar($data);
		$_SESSION["USERINFO"][$userid]=$data;
		return $data;
	}

	function getMyUserInfo() {
		if(!isset($_SESSION['SESS_USER_ID'])) return false;
		return getUserInfo($_SESSION['SESS_USER_ID']);
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

	function getDefaultParams($userID="",$pwd="",$privilegeID="",$accessID="") {
		$params=array(
				"guid"=>"c21f969b5f03d33d43e04f8f136e7682",
				"userid"=>$userID,
				"pwd"=>getPWDHash($pwd),
				"privilegeid"=>$privilegeID,
				"accessid"=>$accessID,
				"name"=>toTitle($userID),
				"dob"=>"",
				"gender"=>"male",
				"email"=>"",
				"mobile"=>"",
				"address"=>"",
				"region"=>"",
				"country"=>"",
				"zipcode"=>"",
				"geolocation"=>"",
				"geoip"=>"",
				"blocked"=>"false",
				"expires"=>"",
				"remarks"=>"",
				"vcode"=>"",
				"mauth"=>"",
				"refid"=>"",
				"privacy"=>"protected",
				"avatar_type"=>"photoid",
				"avatar"=>"",
			);
		return $params;
	}

	function createUser($userID,$privilegeID,$accessID,$pwd,$attrs=array(),$site=SITENAME) {
		if(!isset($_SESSION['SESS_PRIVILEGE_ID']) || $_SESSION['SESS_PRIVILEGE_ID']>ROLE_PRIME) {
			$site=SITENAME;
		}
		if(checkUserID($userID,$site)) {
			return array("error"=>"UserID Exists");
		}

		//Check PrivilegeID
		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"count(*) as cnt")->_where(array(
				"id"=>$privilegeID,
			))->_where(" (site='".SITENAME."' OR site='*')");
		$res=_dbQuery($sql,true);
		if(!$res) {
			return array("error"=>"PrivilegeID Query Error");
		}
		$data=_dbData($res,true);
		_dbFree($res,true);
		if($data[0]['cnt']<=0) {
			return array("error"=>"PrivilegeID Not Found For Site $site");
		}

		//Check AccessID
		$sql=_db(true)->_selectQ(_dbTable("access",true),"count(*) as cnt")->_where(array(
				"blocked"=>'false',
				"id"=>$accessID,
			))->_where(" (FIND_IN_SET('".SITENAME."',sites) OR sites='*')");
		$res=_dbQuery($sql,true);
		if(!$res) {
			return array("error"=>"AccessID Query Error");
		}
		$data=_dbData($res,true);
		_dbFree($res,true);
		if($data[0]['cnt']<=0) {
			return array("error"=>"AccessID Not Found For For Site $site");
		}

		$params=getDefaultParams($userID,$pwd,$privilegeID,$accessID);
		$data=array_merge($params,$attrs);

		$data['guid']=generateGUID($data['guid']);

		$reqParams=explode(",", getConfig("USER_CREATE_REQUIRED_FIELDS"));
		
		foreach ($reqParams as $vx) {
			if(!isset($data[$vx]) || $data[$vx]==null || strlen($data[$vx])<=0) {
				return array("error"=>"Missing Field","field"=>$vx);
			}
		}
		$sql=_db(true)->_insertQ1(_dbTable("users",true),$data);
		$res=_dbQuery($sql,true);
		if($res) {
			return array(
					"guid"=>$data['guid'],
					"userid"=>$data['userid'],
					"name"=>$data['name'],
					"email"=>$data['email'],
					"status"=>"success",
				);
		}
		return array("error"=>"Error In User Creation","details"=>_db(true)->get_error());
	}

	function updateUser($attrs=array(),$userID=null,$site=SITENAME) {
		if(!isset($_SESSION['SESS_PRIVILEGE_ID']) || $_SESSION['SESS_PRIVILEGE_ID']>ROLE_PRIME) {
			$site=SITENAME;
			$userID=$_SESSION['SESS_USER_ID'];
		}
		if($userID==null && isset($_SESSION['SESS_USER_ID'])) {
			$userID=$_SESSION['SESS_USER_ID'];
		}

		if(checkUserID($userID,$site)) {
			$dataUser=$attrs;
			
			$reqParams=explode(",", getConfig("USER_CREATE_REQUIRED_FIELDS"));
			
			foreach ($reqParams as $vx) {
				if(isset($dataUser[$vx]) && ($dataUser[$vx]==null || strlen($dataUser[$vx])<=0)) {
					return array("error"=>"Missing Field","field"=>$vx);
				}
			}

			//Check PrivilegeID if required
			if(isset($dataUser['privilegeid'])) {
				$privilegeID=$dataUser['privilegeid'];
				$sql=_db(true)->_selectQ(_dbTable("privileges",true),"count(*) as cnt")->_where(array(
						"id"=>$privilegeID,
					))->_where(" (site='".SITENAME."' OR site='*')");
				$res=_dbQuery($sql,true);
				if(!$res) {
					return array("error"=>"PrivilegeID Query Error");
				}
				$data=_dbData($res,true);
				_dbFree($res,true);
				if($data[0]['cnt']<=0) {
					return array("error"=>"PrivilegeID Not Found This Site $site");
				}
			}
			//Check AccessID if required
			if(isset($dataUser['accessid'])) {
				$accessID=$dataUser['accessid'];
				$sql=_db(true)->_selectQ(_dbTable("access",true),"count(*) as cnt")->_where(array(
						"blocked"=>'false',
						"id"=>$accessID,
					))->_where(" (FIND_IN_SET('".SITENAME."',sites) OR sites='*')");
				$res=_dbQuery($sql,true);
				if(!$res) {
					return array("error"=>"AccessID Query Error");
				}
				$data=_dbData($res,true);
				_dbFree($res,true);
				if($data[0]['cnt']<=0) {
					return array("error"=>"AccessID Not Found For This Site $site");
				}
			}
			$sql=_db(true)->_updateQ(_dbTable("users",true),$dataUser,array("userid"=>"$userID"));
			$res=_dbQuery($sql,true);
			if($res) {
				return true;
			}
			return array("error"=>"Error In User Updating","details"=>_db(true)->get_error());
		}
		return array("error"=>"UserID Not Found");
	}
}
?>