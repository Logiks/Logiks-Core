<?php
/*
 * This file contains the all User related functionalities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getUserID")) {
	loadHelpers("pwdhash");

	function getUserID() {
		if(isset($_SESSION['SESS_USER_ID']) && strlen($_SESSION['SESS_USER_ID'])>0){
			$user=$_SESSION['SESS_USER_ID'];
		} else {
			$user="guest";
		}
		return $user;
	}

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

	function getMyInfo() {
		if(!isset($_SESSION['SESS_USER_ID'])) return false;
		return getUserInfo($_SESSION['SESS_USER_ID']);
	}

	//Alter User Informations
	function getDefaultParams($userID="",$pwd="",$privilegeID="",$accessID="") {
		$hashSalt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		
		$pwdAns=getPWDHash($pwd,$hashSalt);
		if(is_array($pwdAns)) $pwdAns=$pwdAns['hash'];
		
		$params=array(
				"guid"=>"c21f969b5f03d33d43e04f8f136e7682",
				"userid"=>$userID,
				"pwd"=>$pwdAns,
				"pwd_salt"=>$hashSalt,
				"privilegeid"=>$privilegeID,
				"accessid"=>$accessID,
				"groupid"=>1,
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
				"registerd_site"=>SITENAME,
				"privacy"=>"protected",
				"avatar_type"=>"photoid",
				"avatar"=>"",
				"dtoc"=>date("Y-m-d H:i:s"),
				"dtoe"=>date("Y-m-d H:i:s"),
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
			))->_whereOR("site",[SITENAME,'*']);
		
		$resData=$sql->_GET();
		if(!$resData) {
			return array("error"=>"PrivilegeID Query Error");
		} elseif($resData[0]['cnt']<=0) {
			return array("error"=>"PrivilegeID Not Found For Site");
		}
		
		$sql=_db(true)->_selectQ(_dbTable("access",true),"count(*) as cnt")->_where(array(
				"blocked"=>'false',
				"id"=>$accessID,
			))->_whereMulti([["sites",[SITENAME,"FIND"]],["sites",'*']],"AND","OR");
		
		$resData=$sql->_GET();
		if(!$resData) {
			return array("error"=>"AccessID Query Error");
		} elseif($resData[0]['cnt']<=0) {
			return array("error"=>"AccessID Not Found For Site");
		}

		$params=getDefaultParams($userID,$pwd,$privilegeID,$accessID);
		//code added by Mita 
		
		if(isset($attrs['pwd'])) unset($attrs['pwd']);
		if(isset($attrs['pwd_salt'])) unset($attrs['pwd_salt']);
		
		//End of Mita's Code
		$data=array_merge($params,$attrs);
		
		//If custom guid is there, then no default guid
		if(isset($data['guid'])) $data['guid']=generateGUID($data['guid']);
		else $data['guid']=generateGUID($params['guid']);
		
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
		
		$errMsg=_db(true)->get_error();
		if(strpos(strtolower("###".$errMsg),"duplicate")>2) {
			return array("error"=>"UserID Duplicate Across Sites");
		} else {
			return array("error"=>"Error In User Creation","details"=>$errMsg);
		}
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

			if(isset($dataUser['pwd'])) unset($dataUser['pwd']);
			if(isset($dataUser['pwd_salt'])) unset($dataUser['pwd_salt']);
			
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
			$dataUser["dtoe"]=date("Y-m-d H:i:s");
			
			$sql=_db(true)->_updateQ(_dbTable("users",true),$dataUser,array("userid"=>"$userID"));
			$res=_dbQuery($sql,true);
			if($res) {
				return true;
			}
			return array("error"=>"Error In User Updating","details"=>_db(true)->get_error());
		}
		return array("error"=>"UserID Not Found");
	}

	function updatePassword($pwd,$userID=null,$site=SITENAME) {
		
		if(!isset($_SESSION['SESS_PRIVILEGE_ID']) || $_SESSION['SESS_PRIVILEGE_ID']>ROLE_PRIME) {
			$site=SITENAME;
			$userID=$_SESSION['SESS_USER_ID'];
		}
	
		if($userID==null && isset($_SESSION['SESS_USER_ID'])) {
			$userID=$_SESSION['SESS_USER_ID'];
		}

		if(checkUserID($userID,$site)) {
			$hashSalt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

			$pwdAns=getPWDHash($pwd,$hashSalt);
			if(is_array($pwdAns)) $pwdAns=$pwdAns['hash'];
			
			$dataUser=array(
					"pwd"=>$pwdAns,
					"pwd_salt"=>$hashSalt,
					"dtoe"=>date("Y-m-d H:i:s"),
				);
			$sql=_db(true)->_updateQ(_dbTable("users",true),$dataUser,array("userid"=>"$userID"));
			
			$res=_dbQuery($sql,true);
			if($res) {
				return true;
			}
			return array("error"=>"Error In User Updating","details"=>_db(true)->get_error());
		}
		return array("error"=>"UserID Not Found");
	}

	function generateGUID($name) {
		return trim(strtolower(preg_replace('/\W/', '', $name)));
	}

	function getMyRoleHash() {
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);
			} else {
				return false;
			}
		}
		return $_SESSION["SESS_PRIVILEGE_HASH"];
	}
	function fetchUserRoleHash($userid) {
		$tbl1=_dbTable("users", true);
		$tbl2=_dbTable("privileges", true);
		$data=_db(true)->_raw("SELECT md5(concat({$tbl2}.id,{$tbl2}.name)) as hash FROM {$tbl1},{$tbl2} WHERE {$tbl1}.privilegeid={$tbl2}.id AND {$tbl1}.userid='{$userid}'")
				->_get();
		if(isset($data[0])) return $data[0]['hash'];
		else return false;
	}
}
?>