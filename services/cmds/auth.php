<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_POST['mauth'])) {
	echo "<h5>Securing Access Authentication ... </h5>";
}

$userid=clean($_POST['userid']);
$pwd=clean($_POST['password']);
if(isset($_POST['site'])) $domain=$_POST['site']; 
elseif(isset($_REQUEST['site'])) $domain=$_REQUEST['site']; 
else $domain=SITENAME;

loadConfigs(ROOT . "config/auth.cfg");
include ROOT."api/helpers/pwdhash.php";
//include ROOT."api/security.php";

/*
CLEAR_OLD_SESSION=true
@session_start();
session_destroy();
session_start();
*/
$dbLink=_db(true);
$dbLogLink=null;//LogDB::getInstance()->getLogDBCon();

if(!$dbLink->isAlive()) {
	relink("Database Connection Error",$domain);
}
if($userid == '') {
	relink('Login ID missing',$domain);
}
if($pwd == '') {
	relink('Password missing',$domain);
}

$date=date('Y-m-d');

$userFields=explode(",", USERID_FIELDS);

$q1="SELECT id, guid, userid, pwd, privilegeid, accessid, name, email, mobile, blocked, avatar, avatar_type FROM "._dbTable("users",true)." where (expires IS NULL OR expires='0000-00-00' OR expires > now())";// AND blocked='false'

if(CASE_SENSITIVE_AUTH=="true") {
	foreach ($userFields as $key => $value) {
		$userFields[$key]="BINARY ".trim($value)."='$userid'";
	}
} else {
	foreach ($userFields as $key => $value) {
		$userFields[$key]=trim($value)."='$userid'";
	}
}

$userFields=implode(" OR ", $userFields);
if(strlen($userFields)>0) $q1.=" AND ($userFields)";
else {
	relink('Wrong Configuration For Authenetication System',$domain);
}

$result=$dbLink->executeQuery($q1);

if($result) {
	$data=$dbLink->fetchData($result);

	$dbLink->freeResult($result);
	if($data==null) {
		relink("Sorry, you have not yet joined us or your userid has expired.",$domain);
	}
} else {
	relink("Sorry, you have not yet joined us or your userid has expired.",$domain);
}

if(!matchPWD($data['pwd'],$pwd)) {
	relink("UserID/Password Wrong/Mismatch",$domain);
}
if($data['blocked']=="true") {
	relink("Sorry, you are currently blocked by system admin.",$domain);
}

//Creating Access Rules
$q3="SELECT sites,name as access_name FROM "._dbTable("access",true)." where id='".$data['accessid']."' and blocked='false'";
$q4="SELECT hash,name as privilege_name FROM "._dbTable("privileges",true)." where id='".$data['privilegeid']."' and blocked='false'";

$result=$dbLink->executeQuery($q3);
if($result) {
	$d1=$dbLink->fetchData($result);
	$dbLink->freeResult($result);
	if($d1==null) {
		relink("No Accessibilty Defined For You Or Blocked By Admin.",$domain);
	}
} else {
	relink("No Accessibilty Defined For You Or Blocked By Admin.",$domain);
}

$result=$dbLink->executeQuery($q4);
if($result) {
	$d2=$dbLink->fetchData($result);
	$dbLink->freeResult($result);
	if($d2==null) {
		relink("No Privileges Defined For You Or Blocked By Admin.", $domain);
	}
} else {
	relink("No Privileges Defined For You Or Blocked By Admin.", $domain);
}

$allSites=explode(",",$d1['sites']);
if($d1['sites']=="*") {
	$allSites=getAccessibleSitesArray();
}
if(count($allSites)>0) {
	$_SESSION['SESS_ACCESS_SITES']=$allSites;
} else {
	relink("No Accessible Site Found For Your UserID");
}
if(!in_array($domain,$allSites)) {
	relink("Sorry, You [UserID] do not have access to requested site.", $domain);
}

$_ENV['AUTH-DATA']=array_merge($data,$d1);
$_ENV['AUTH-DATA']=array_merge($_ENV['AUTH-DATA'],$d2);

loadHelpers("mobility");
$_ENV['AUTH-DATA']['device']=getUserDeviceType();
$_ENV['AUTH-DATA']['client']=_server("REMOTE_ADDR");
if(isset($_POST['persistant']) && $_POST['persistant']=="true") {
	$_ENV['AUTH-DATA']['persistant']="true";
} else {
	$_ENV['AUTH-DATA']['persistant']="false";
}
$_ENV['AUTH-DATA']['sitelist']=$allSites;

checkBlockedUser($data,$domain);
checkBlacklists($data,$domain,$dbLink,$userid);

initializeLogin($userid, $domain, $dbLogLink);


//All Functions Required By Authentication System
function relink($msg,$domain) {
	$_SESSION['SESS_ERROR_MSG']=$msg;
	
	$onerror="";
	if((ALLOW_LOGIN_RELINKING=="true" || ALLOW_LOGIN_RELINKING)) {
		if(isset($_REQUEST['onerror'])) $onerror=$_REQUEST['onerror'];
	}
	if(strlen($onerror)==0 || $onerror=="*") {
		$s=SiteLocation."login.php";
		if(strlen($domain)>0) $s.="?site=$domain";
		$onerror=$s;
	}
	if(substr($onerror,0,7)=="http://" || substr($onerror,0,8)=="https://" ||
		substr($onerror,0,2)=="//" || substr($onerror,0,2)=="./" || substr($onerror,0,1)=="/") {
			header("Location:$onerror");
			exit($msg);
	} else {
		header("SESS_ERROR_MSG:".$msg,false);
		exit($onerror);
	}
}
function getAccessibleSitesArray() {
	$arr=scandir(ROOT.APPS_FOLDER);
	unset($arr[0]);unset($arr[1]);
	$out=array();
	foreach($arr as $a=>$b) {
		if(is_file(ROOT.APPS_FOLDER.$b)) {
			unset($arr[$a]);
		} elseif(is_dir(ROOT.APPS_FOLDER.$b) && !file_exists(ROOT.APPS_FOLDER.$b."/apps.cfg")) {
			unset($arr[$a]);
		} else {
			array_push($out,$b);
		}
	}
	return $out;
}
//Logging And Checking Functions
function checkBlockedUser($data,$domain) {
	if($data['blocked']=='true') {
		relink("You are currently blocked,Please contact Site Admin.",$domain);
	} return false;
}
function checkBlacklists($data,$domain,$dbLink,$userid) {
	$ls=new LogiksSecurity();
	if($ls->isBlacklisted("login",$domain)) {
		relink("You are currently Blacklisted On Server, Please contact Site Admin.",$domain);
	} else {
		return false;
	}
}
//LogBook Checking
function initializeLogin($userid,$domain, $dbLink,$params=array()) {
	$mauthKey=generateMAuthKey();
	startNewSession($userid, $domain, $dbLink, $params);

	// $q1=$dbLink->_selectQ("lgks_log_login",
	// 					"date, login_time, logout_time, sys_spec, token, mauth_key, client, persistant",
	// 					array("user"=>$userid,"status"=>"LOGGED IN")// and site='$domain'
	// 				);
	// $result=$dbLink->executeQuery($q1);
	// if($result) {
	// 	$logData=$dbLink->fetchAllData($result);
	// 	$dbLink->freeResult($result);
	// 	if($logData!=null && count($logData)>0) {
	// 		$mauthKey=generateMAuthKey();
	// 		foreach($logData as $data) {
	// 			if($data['mauth_key']==$mauthKey) {
	// 				if($data['persistant']=="true") {
	// 					restoreOldSession($data, $userid, $domain, $dbLink, $params);
	// 				} else {
	// 					logoutOldSessions($userid, $domain, $dbLink, $params);
	// 					startNewSession($userid, $domain, $dbLink, $params);
	// 				}
	// 				return;
	// 			}
	// 		}
	// 		if(ALLOW_MULTI_LOGIN=="false") {
	// 			//relink("MultiLogin Attempt, You are logged in from the system {$data['sys_spec']} since {$data['login_time']} On {$data['date']}.",$domain);
	// 			logoutOldSessions($userid, $domain, $dbLink, $params);
	// 			startNewSession($userid, $domain, $dbLink, $params);
	// 		} else {
	// 			startNewSession($userid, $domain, $dbLink, $params);
	// 		}
	// 	} else {
	// 		startNewSession($userid, $domain, $dbLink, $params);
	// 	}
	// } else {
	// 	startNewSession($userid, $domain, $dbLink, $params);
	// }
	exit();
}
//All session functions
function startNewSession($userid, $domain, $dbLink, $params=array()) {
	session_regenerate_id();
	$data=$_ENV['AUTH-DATA'];
	//printArray($data);exit();

	$_SESSION['SESS_GUID'] = $data['guid'];
	$_SESSION['SESS_USER_ID'] = $data['userid'];
	$_SESSION['SESS_PRIVILEGE_ID'] = $data['privilegeid'];
	$_SESSION['SESS_ACCESS_ID'] = $data['accessid'];
	
	$_SESSION['SESS_PRIVILEGE_NAME'] = $data['privilege_name'];
	$_SESSION['SESS_ACCESS_NAME'] = $data['access_name'];
	$_SESSION['SESS_ACCESS_SITES'] = $data['sitelist'];

	$_SESSION['SESS_USER_NAME'] = $data['name'];
	$_SESSION['SESS_USER_EMAIL'] = $data['email'];
	$_SESSION['SESS_USER_CELL'] = $data['mobile'];

	$_SESSION['SESS_USER_AVATAR'] = $data['avatar_type']."::".$data['avatar'];

	$_SESSION['SESS_LOGIN_SITE'] = $domain;
	$_SESSION['SESS_ACTIVE_SITE'] = $domain;
	_envData("SESSION",'SESS_ACTIVE_SITE',$domain);
	$_SESSION['SESS_TOKEN'] = session_id();
	$_SESSION['SESS_SITEID'] = SiteID;
	$_SESSION['SESS_LOGIN_TIME'] =time();
	$_SESSION['MAUTH_KEY'] = generateMAuthKey();
	
	if($data['privilegeid']<=2) {
		$_SESSION["SESS_FS_FOLDER"]=ROOT;
		$_SESSION["SESS_FS_URL"]=SiteLocation;
	} else {
		$_SESSION["SESS_FS_FOLDER"]=ROOT.APPS_FOLDER.$domain."/";
		$_SESSION["SESS_FS_URL"]=SiteLocation.APPS_FOLDER.$domain."/";
	}

	if(strlen($_SESSION['SESS_USER_NAME'])<=0) {
		$_SESSION['SESS_USER_NAME']=$_SESSION['SESS_USER_ID'];
	}
	header_remove("SESSION-KEY");
	header("SESSION-KEY:".session_id(),false);

	// $q1=$dbLink->_insertQ1(_dbTable("log_login",true),array(
	// 		"date"=>date("Y-m-d"),
	// 		"user"=>$userid,
	// 		"site"=>$domain,
	// 		"login_time"=>date('H:i:s'),
	// 		//"logout_time"=>,
	// 		"sys_spec"=>_server('REMOTE_ADDR'),
	// 		"token"=>$_SESSION['SESS_TOKEN'],
	// 		"mauth_key"=>$_SESSION['MAUTH_KEY'],
	// 		"status"=>'LOGGED IN',
	// 		"msg"=>'',
	// 		"persistant"=>$data['persistant'],
	// 		"client"=>_server('REMOTE_ADDR'),
	// 		"user_agent"=>_server('HTTP_USER_AGENT'),
	// 		"device"=>$data['device'],
	// 	));
	// $dbLink->executeQuery($q1);

	setcookie("LOGIN", "true", time()+36000);
	setcookie("USER", $_SESSION['SESS_USER_ID'], time()+36000);
	setcookie("TOKEN", $_SESSION['SESS_TOKEN'], time()+36000);
	setcookie("SITE", $_SESSION['SESS_LOGIN_SITE'], time()+36000);

	if($data['persistant']=="true") {
		// $q1=$dbLink->_insertQ1(_dbTable("log_sessions",true),array(
		// 		"sessionid"=>$_SESSION['SESS_TOKEN'],
		// 		"timestamp"=>date("Y-m-d H:i:s"),
		// 		"last_updated"=>date("Y-m-d H:i:s"),
		// 		"user"=>$userid,
		// 		"site"=>$domain,
		// 		"session_data"=>json_encode($_SESSION),
		// 		"global_data"=>json_encode($GLOBALS),
		// 		"client"=>_server('REMOTE_ADDR'),
		// 		"user_agent"=>_server('HTTP_USER_AGENT'),
		// 		"device"=>$data['device'],
		// 	));
		// $dbLink->executeQuery($q1);	
	}

	gotoSuccessLink();
}
function logoutOldSessions($userid, $domain, $dbLink, $params=array()) {
	$sql=$dbLink->_updateQ("lgks_log_login",
			array('logout_time'=>date('Y-m-d H:i:s'), 'status'=>'LOGGED OUT'),
			array("user"=>$userid,"status"=>"LOGGED IN")
		);
	$dbLink->executeQuery($sql);
}
function restoreOldSession($sessionData, $userid, $domain, $dbLink, $params=array()) {
	$data=$_ENV['AUTH-DATA'];
	$sessionID=$sessionData['token'];
	$q1=$dbLink->_selectQ(_dbTable("log_sessions",true),"*",
						array(
							"sessionid"=>$sessionID,
							"user"=>$userid,
							"client"=>_server('REMOTE_ADDR'),
							"user_agent"=>_server('HTTP_USER_AGENT'))
					);
	$result=$dbLink->executeQuery($q1);
	if($result) {
		$logData=$dbLink->fetchAllData($result);
		$dbLink->freeResult($result);
		if($logData!=null && count($logData)>0) {
			$logData=$logData[0];
			
			$logData['session_data']=stripslashes($logData['session_data']);
			$logData['session_data']=json_decode($logData['session_data'],true);
			
			session_regenerate_id();
			foreach($logData['session_data'] as $key => $value) {
				$_SESSION[$key]=$value;
			}
			setcookie("LOGIN", "true", time()+36000);
			setcookie("USER", $_SESSION['SESS_USER_ID'], time()+36000);
			setcookie("TOKEN", $_SESSION['SESS_TOKEN'], time()+36000);
			setcookie("SITE", $_SESSION['SESS_LOGIN_SITE'], time()+36000);

			//$logData['global_data']$GLOBALS
			//printArray($_SESSION);exit();

			gotoSuccessLink();
		} else {
			logoutOldSessions($userid, $domain, $dbLink, $params);
			startNewSession($userid, $domain, $dbLink, $params);	
		}
	} else {
		logoutOldSessions($userid, $domain, $dbLink, $params);
		startNewSession($userid, $domain, $dbLink, $params);
	}
	exit();
}
function gotoSuccessLink() {
	$onsuccess="";
	if((ALLOW_LOGIN_RELINKING=="true" || ALLOW_LOGIN_RELINKING)) {
		if(isset($_REQUEST['onsuccess'])) $onsuccess=$_REQUEST['onsuccess'];
	}

	$domain=$_SESSION['SESS_ACTIVE_SITE'];//ACTIVE
	if(ALLOW_MAUTH=="true") {
		if(isset($_POST['mauth']) && $_POST['mauth']=="authkey") {
			echo $_SESSION['MAUTH_KEY'];
		} elseif(isset($_POST['mauth']) && $_POST['mauth']=="jsonkey") {
			$arr=array(
					"user"=>$_SESSION['SESS_USER_ID'],
					"authkey"=>$_SESSION['MAUTH_KEY'],
					"date"=>date("Y-m-d"),
					"time"=>date("H:i:s"),
					"site"=>$domain,
					"client"=>_server('REMOTE_ADDR'),
					"token"=>$_SESSION['SESS_TOKEN'],
				);
			header("Content-Type:text/json");
			echo json_encode($arr);
		} else {
			echo "<h5>Securing Access Authentication ... </h5>";
			if(strlen($onsuccess)==0 || $onsuccess=="*")
				header("location: ".SiteLocation.$domain);
			else {
				if(substr($onsuccess,0,7)=="http://" || substr($onsuccess,0,8)=="https://" ||
					substr($onsuccess,0,2)=="//" || substr($onsuccess,0,2)=="./" || substr($onsuccess,0,1)=="/") {
						header("location: $onsuccess");
				}
			}
		}
	} else {
		//echo "<h5>Securing Access Authentication ... </h5>";
		if(strlen($onsuccess)==0 || $onsuccess=="*") {
			header("location: "._link(getConfig("PAGE_HOME")));
		} else {
			if(substr($onsuccess,0,7)=="http://" || substr($onsuccess,0,8)=="https://" ||
				substr($onsuccess,0,2)=="//" || substr($onsuccess,0,2)=="./" || substr($onsuccess,0,1)=="/") {
					header("location: $onsuccess");
			}
		}
	}
	exit();
}
?>
