<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getUserInfo")) {
	function getUserInfo($forcereInit=true) {
		$arr=array();
		
		if(isset($_SESSION['SESS_USER_ID'])) $arr["SESS_USER_ID"]=$_SESSION['SESS_USER_ID']; else $arr["SESS_USER_ID"]="Guest";
		if(isset($_SESSION['SESS_PRIVILEGE_ID'])) $arr["SESS_PRIVILEGE_ID"]=$_SESSION['SESS_PRIVILEGE_ID']; else $arr["SESS_PRIVILEGE_ID"]="-1";
		if(isset($_SESSION['SESS_ACCESS_ID'])) $arr["SESS_ACCESS_ID"]=$_SESSION['SESS_ACCESS_ID']; else $arr["SESS_ACCESS_ID"]="-1";
		if(isset($_SESSION['SESS_PRIVILEGE_NAME'])) $arr["SESS_PRIVILEGE_NAME"]=$_SESSION['SESS_PRIVILEGE_NAME']; else $arr["SESS_PRIVILEGE_NAME"]="guest";
		if(isset($_SESSION['SESS_ACCESS_NAME'])) $arr["SESS_ACCESS_NAME"]=$_SESSION['SESS_ACCESS_NAME']; else $arr["SESS_ACCESS_NAME"]="guest";
		if(isset($_SESSION['SESS_ACCESS_SITES'])) $arr["SESS_ACCESS_SITES"]=$_SESSION['SESS_ACCESS_SITES']; else $arr["SESS_ACCESS_SITES"]="*";
		if(isset($_SESSION['SESS_USER_NAME'])) $arr["SESS_USER_NAME"]=$_SESSION['SESS_USER_NAME']; else $arr["SESS_USER_NAME"]="Guest";
		if(isset($_SESSION['SESS_USER_EMAIL'])) $arr["SESS_USER_EMAIL"]=$_SESSION['SESS_USER_EMAIL']; else $arr["SESS_USER_EMAIL"]="";
		if(isset($_SESSION['SESS_USER_CELL'])) $arr["SESS_USER_CELL"]=$_SESSION['SESS_USER_CELL']; else $arr["SESS_USER_CELL"]="";
		if(isset($_SESSION['SESS_LOGIN_SITE'])) $arr["SESS_LOGIN_SITE"]=$_SESSION['SESS_LOGIN_SITE']; else $arr["SESS_LOGIN_SITE"]="guest";
		if(isset($_SESSION['SESS_TOKEN'])) $arr["SESS_TOKEN"]=$_SESSION['SESS_TOKEN']; else $arr["SESS_TOKEN"]=session_id();
		
		if($forcereInit) {
			foreach($arr as $a=>$b) {
				if(!isset($_SESSION[$a])) $_SESSION[$a]=$b;
			}
		}
		
		return $arr;
	}
	function getUserID() {
		if(isset($_SESSION['SESS_USER_ID'])){
			$user=$_SESSION['SESS_USER_ID'];
		} else {
			$user="Guest";
		}
		if(isset($_REQUEST['userid'])){
			$userid=$_REQUEST['userid'];
		} else {
			$userid=$user;
		}
		return $userid;
	}
	
	function getUserList($cols=null, $where="", $orderBy="", $limit="") {
		if($cols==null || sizeOf($cols)==0) {
			$cols=array("userid", "privilege", "access", "name", "email", "address", "region", "country", "zipcode", "mobile");
		}
		$arr=array();
		$sql="SELECT userid,privilege,access,name,email,address,region,country,zipcode,mobile FROM lgks_users WHERE blocked='false'";
		if($_SESSION['SESS_PRIVILEGE_ID']<=2) {
			$sql.=" AND (site='".SITENAME."' OR site='*')";
		} else {
			$sql.=" AND site='".SITENAME."'";
		}
		if(strlen($where)>0) {
			$sql.=" and ($where)";
		}
		if(strlen($orderBy)>0) {
			$sql.=" order by $orderBy";
		}
		if(strlen($limit)>0) {
			$sql.=" limit $limit";
		}
		$res=_dbQuery($sql,true);
		if($res) {
			while($record=_db()->fetchData($res)) {
				if(sizeOf($cols)==sizeOf($record)) {
					$arr[sizeOf($arr)]=$record;
				} else {
					$arr[sizeOf($arr)]=array();
					foreach($cols as $a=>$b) {
						$arr[sizeOf($arr)][$b]=$record[$b];
					}
				}
			}
			_db(true)->freeResult($sql);
		}
		return $arr;
	}
	function checkUserID($userid,$site=SITENAME) {
		if($userid=="root") return true;
		if($_SESSION['SESS_PRIVILEGE_ID']>2) {
			$site=SITENAME;
		}
		$sql="SELECT sites FROM "._dbTable("access",true)." WHERE id=(SELECT access from "._dbTable("users",true)." WHERE userid='{$userid}' AND blocked='false' AND (expires IS NULL OR expires='0000-00-00' OR expires > now())) AND blocked='false'";
		
		$res=_dbQuery($sql,true);
		if($res) {
			$data=_dbData($res);
			_dbFree($res);
			if(isset($data[0]['sites'])) {
				$sites=$data[0]['sites'];
				if($sites=="*") return true;
				$sites=explode(",",$sites);
				if(in_array($site,$sites)) {
					return true;
				}
			}
		}
		return false;
	}
	function createUser($userID,$privilegeID,$accessID,$pwd,$attrs=array(),$site=SITENAME) {
		if($_SESSION['SESS_PRIVILEGE_ID']>2) {
			$site=SITENAME;
		}
		if(checkUserID($userID,$site)) {
			return "UserID Exists";
		}
		$sql1="SELECT COUNT(*) AS cnt FROM "._dbTable("privileges",true)." WHERE ID=$privilegeID";
		$sql2="SELECT COUNT(*) AS cnt,sites FROM "._dbTable("access",true)." WHERE ID=$accessID";
		$res=_dbQuery($sql1,true);
		$data=_dbData($res);
		_dbFree($res);
		if(count($data)<=0 || $data[0]['cnt']<=0) return "Wrong Privilege ID";
		$res=_dbQuery($sql2,true);
		$data=_dbData($res);
		_dbFree($res);
		if(count($data)<=0 || $data[0]['cnt']<=0) return "Wrong Access ID";
		if($data[0]['sites']!="*") {
			$ss=explode(",",$data[0]['sites']);
			if(!in_array($site,$ss)) {
				return "Mismatching AccessID With Given Site";
			}
		}
		
		$params=array(
				"userid"=>"$userID",
				"pwd"=>"$pwd",
				"site"=>"$site",
				"privilege"=>"$privilegeID",
				"access"=>"$accessID",
				"name"=>toTitle($userID),
				"dob"=>"",
				"email"=>"",
				"address"=>"",
				"region"=>"",
				"country"=>"",
				"zipcode"=>"",
				"mobile"=>"",
				"blocked"=>"false",
				"expires"=>"",
				"remarks"=>"",
				"notes"=>"",
				"vcode"=>"",
				"refid"=>"",
				"privacy"=>"protected",
				"avatar_type"=>"photoid",
				"avatar"=>"",
				"q1"=>"",
				"a1"=>"",
				"doc"=>date('Y-m-d'),
				"doe"=>date('Y-m-d'),
			);
		foreach($params as $a=>$b) {
			if(isset($attrs[$a])) $params[$a]=$attrs[$a];
		}
		$sql=_db(true)->_insertQ(_dbTable("users",true),array_keys($params),array_values($params));
		if(strlen($sql)>0) {
			$res=_dbQuery($sql,true);
			if($res) {
				return true;
			}
			return "Error In User Creation "._db(true)->getError();
		}
		return "Error In User Creation "._db(true)->getError();
	}
	
	function updateUser($attrs=array(),$userID=null,$site=SITENAME) {
		if($_SESSION['SESS_PRIVILEGE_ID']>2) {
			$site=SITENAME;
			$userID=$_SESSION['SESS_USER_ID'];
		}
		if($userID==null) {
			$userID=$_SESSION['SESS_USER_ID'];
		}
		if(checkUserID($userID,$site)) {
			$params=array(
				//"privilege"=>"$privilegeID",
				//"access"=>"$accessID",
				"name"=>toTitle($userID),
				"dob"=>"",
				"email"=>"",
				"address"=>"",
				"region"=>"",
				"country"=>"",
				"zipcode"=>"",
				"mobile"=>"",
				"blocked"=>"false",
				"expires"=>"",
				"remarks"=>"",
				"notes"=>"",
				"vcode"=>"",
				"refid"=>"",
				"privacy"=>"protected",
				"avatar_type"=>"photoid",
				"avatar"=>"",
				"q1"=>"",
				"a1"=>"",
				"doe"=>date('Y-m-d'),
			);
			foreach($attrs as $a=>$b) {
				if(!isset($params[$a])) {
					unset($attrs[$a]);
				}
			}
			$sql1=_db(true)->_selectQ(_dbTable("users",true),array("userid"),array("userid"=>"$userID","site"=>"$site"));
			$res=_dbQuery($sql1,true);
			$uData=_dbData($res);
			_dbFree($res,true);
			if(count($uData)<=0) {
				return "User Not Found";
			}
			$sql2=_db(true)->_updateQ(_dbTable("users",true),$attrs,array("userid"=>"$userID","site"=>"$site"));
			$res=_dbQuery($sql2,true);
			if(!$res) return "Error Updating User Info";
			else return "";
		}
		return "User Not Found";
	}
	/*function createPWDRecovery($userID,$site=SITENAME) {
		
	}*/
}
?>
