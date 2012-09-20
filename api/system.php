<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("pathfuncs");
//Some Special System Functions
if(!function_exists("createTimeStamp")) {
	function deleteCookies($name) {
		setcookie($name, "", time()-1000000000);
		if(isset($_COOKIE[$name])) unset($_COOKIE[$name]);
	}
	function isLocalhost() {
		$client=$_SERVER['REMOTE_ADDR'];
		$server=$_SERVER['SERVER_ADDR'];
		if($client==$server) return true;
		else return false;
	}
	//$refs :: __FILE__
	function getLocation($refs) {
		$x=SiteLocation . "/" .str_replace(ROOT,"",$refs);
		return $x;
	}	
	function createTimeStamp($encoded=true) {
		if($encoded) {
			$s=date(TIMESTAMP_FORMAT).microtime();
			if(function_exists("md5")) {
				$s=md5($s);
			} else {
				$s=base64_encode($s);
			}
			return $s;
		} else {
			$s=date(TIMESTAMP_FORMAT).microtime();
			return $s;
		}
	}
	function getSysDBLink($toClose=false) {
		global $sysdbLink;
		if(!$toClose) {
			if($sysdbLink==null) {
				$sysdbLink=Database();
				$sysdbLink->connect();
			}
		}
		
		return $sysdbLink;
	}
	function getAppsDBLink($toClose=false) {
		global $appdbLink;
		if(!$toClose) {
			if($appdbLink==null) {
				$appdbLink=new Database();
				$appdbLink->connect();
			}
		}
		return $appdbLink;
	}
	function getUserList($cols=null, $where="", $orderBy="", $limit="") {
		if($cols==null || sizeOf($cols)==0) {
			$cols=array("userid", "privilege", "access", "name", "email", "address", "region", "country", "zipcode", "mobile");
		}
		$arr=array();
		$sql="SELECT userid,privilege,access,name,email,address,region,country,zipcode,mobile FROM lgks_users WHERE site='".SITENAME."' and blocked='false'";
		if(strlen($where)>0) {
			$sql.=" and ($where)";
		}
		if(strlen($orderBy)>0) {
			$sql.=" order by $orderBy";
		}
		if(strlen($limit)>0) {
			$sql.=" limit $limit";
		}
		$res=_db(true)->executeQuery($sql);
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
		$sql="SELECT sites FROM "._dbTable("access",true)." WHERE id=(SELECT access from "._dbTable("users",true)." WHERE userid='{$userid}' AND blocked='false' AND (expires IS NULL OR expires='0000-00-00' OR expires > now())) AND blocked='false'";
		//$sql="SELECT a.site,a.access,a.privilege,b.sites FROM "._dbTable("users",true)." as a,"._dbTable("access",true)." as b";
		//$sql.=" WHERE a.userid='{$userid}' AND a.blocked='false' AND b.blocked='false' AND a.access=b.id AND (a.expires IS NULL OR a.expires='0000-00-00' OR a.expires > now())";
		$res=_db(true)->executeQuery($sql);
		if($res) {
			$data=_dbData($res);
			_db(true)->freeResult($res);
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
	
	//Initializes The User Names, etc...
	function initUserCredentials() {
		if(isset($_SESSION["SESS_USER_ID"])) {
			$q1="SELECT userid,name,email,mobile,address,access,privilege FROM "._dbTable("users",true)." where userid='".$_SESSION["SESS_USER_ID"]."'";
			$res1=_dbQuery($q1,true);
			if($res1) {
				$data=_db()->fetchData($res1);
				$_SESSION['SESS_USER_NAME'] = $data['name'];
				$_SESSION['SESS_USER_EMAIL'] = $data['email'];
				$_SESSION['SESS_USER_CELL'] = $data['mobile'];
			}
		} else {
			$_SESSION['SESS_USER_ID'] = "Guest";
			$_SESSION['SESS_PRIVILEGE_ID'] = "Guest";
			$_SESSION['SESS_ACCESS_ID'] = "NA";

			$_SESSION['SESS_PRIVILEGE_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_NAME'] = "Guest";
			$_SESSION['SESS_ACCESS_SITES'] = getSessionSite();
			
			$_SESSION['SESS_USER_NAME'] = "Guest";
			$_SESSION['SESS_USER_EMAIL'] = "NA";
			$_SESSION['SESS_USER_CELL'] = "NA";
		}
	}
	function getFunctionCaller() {
		$trace=debug_backtrace();
		array_shift($trace);//Remove Self
		//array_shift($trace);//Remove Parent
		$caller=array_shift($trace);//Caller
		return $caller;
	}
	function getSupportedPages($page) {
		$arr=array();
		$arr=array("{$page}.php","{$page}.htm","{$page}.html");
		return $arr;
	}
	function logoutSession() {
		session_destroy();
		$relink=SiteLocation . "login.php";
		if(defined("SITENAME")) $relink.="?site=".SITENAME;
		redirectTo($relink,"SESSION Expired. Going To Login Page");
	}
	function getSessionSite() {
		/*if(isset($_SESSION['LGKS_SESS_SITE'])) return $_SESSION['LGKS_SESS_SITE'];
		elseif(isset($_REQUEST['site'])) {
			$_SESSION['LGKS_SESS_SITE']=$_REQUEST['site'];
			return $_REQUEST['site'];
		}
		else return DEFAULT_SITE;*/
		
		$site="";
		if(isset($_SESSION["LGKS_SESS_SITE"])) {
			//active session
			if(isset($_REQUEST['site'])) {
				if($_REQUEST['site']!=$_SESSION["LGKS_SESS_SITE"]) {
					if(DOMAIN_CONTROLS_ENABLE=="true") {
						$dm=new DomainMap($sysdbLink);
						$site=$dm->checkHost();
					} else {
						$site=$_REQUEST['site'];
					}				
				} else {
					$site=$_REQUEST['site'];
				}
			} else {
				$site=$_SESSION["LGKS_SESS_SITE"];
			}
		} else {
			//inactive session
			if(DOMAIN_CONTROLS_ENABLE=="true") {
				$dm=new DomainMap($sysdbLink);
				$site=$dm->checkHost();
			} else {
				if(isset($_REQUEST['site'])) {
					$site=$_REQUEST['site'];
				} else {
					$site=DEFAULT_SITE;
				}
			}
		}
		if($site==null || strlen($site)<=0) $site=DEFAULT_SITE;
		return $site;
	}
}
?>
