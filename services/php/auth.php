<h5>Securing Access Authentication ... </h5>
<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include ROOT."api/helpers/pwdhash.php";

$userid=clean($_POST['userid']);
$pwd=clean($_POST['password']);
if(isset($_REQUEST['site'])) $domain=$_REQUEST['site']; else $domain="";

LoadConfigFile(ROOT . "config/auth.cfg");

if(ALLOW_LOGIN_RELINKING=="true") {
	if(isset($_REQUEST['onsuccess'])) $onsuccess=$_REQUEST['onsuccess']; else $onsuccess="";
	if(isset($_REQUEST['onerror'])) $onerror=$_REQUEST['onerror']; else $onerror="";
} else {
	$onsuccess="";
	$onerror="";
}

/*
CLEAR_OLD_SESSION=true
@session_start();
session_destroy();
session_start();
*/
$dbLink=getSysDBLink();
$dbLogLink=LogDB::singleton()->getLogDBCon();

if(!$dbLink->isOpen()) {
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

$q1="SELECT id, userid, pwd, site, privilege, access, name, email, mobile, blocked FROM "._dbTable("users",true)." where (expires IS NULL OR expires='0000-00-00' OR expires > now())";// AND blocked='false'
//$q1="SELECT id, userid, pwd, site, privilege, access, name, email, mobile, blocked FROM "._dbTable("users",true)." where userid='$userid' AND blocked='false' AND (expires IS NULL OR expires='0000-00-00' OR expires > now())";// AND blocked='false'

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
$q3="SELECT * FROM "._dbTable("access",true)." where id='".$data['access']."' and blocked='false'";
$q4="SELECT * FROM "._dbTable("privileges",true)." where id='".$data['privilege']."' and blocked='false'";

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

if(!in_array($domain,$allSites)) {
	relink("Sorry, You [UserID] do not have access to requested site.", $domain);
}

checkBlockedUser($data,$domain);
checkBlacklists($data,$domain,$dbLink,$userid);
checkLoginLogTables($userid,$domain, $dbLogLink);

session_regenerate_id();
if(count($allSites)>0) {
	$_SESSION['SESS_ACCESS_SITES']=$allSites;
} else {
	relink("No Accessible Site Found For Your UserID");
}

$_SESSION['SESS_USER_ID'] = $userid;
$_SESSION['SESS_PRIVILEGE_ID'] = $data['privilege'];
$_SESSION['SESS_ACCESS_ID'] = $data['access'];

$_SESSION['SESS_PRIVILEGE_NAME'] = $d2['name'];
$_SESSION['SESS_ACCESS_NAME'] = $d1['master'];
$_SESSION['SESS_ACCESS_SITES'] = $allSites;

$_SESSION['SESS_USER_NAME'] = $data['name'];
$_SESSION['SESS_USER_EMAIL'] = $data['email'];
$_SESSION['SESS_USER_CELL'] = $data['mobile'];

$_SESSION['SESS_LOGIN_SITE'] = $domain;
$_SESSION['SESS_TOKEN'] = md5(SiteID.session_id());
$_SESSION['SESS_SITEID'] = SiteID;

if($data['privilege']<=3) $_SESSION["SESS_FS_FOLDER"]=ROOT;
else $_SESSION["SESS_FS_FOLDER"]=ROOT.APPS_FOLDER.$domain."/";

if($data['privilege']<=3) $_SESSION["SESS_FS_URL"]=SiteLocation;
else $_SESSION["SESS_FS_URL"]=SiteLocation.APPS_FOLDER.$domain."/";

if(strlen($_SESSION['SESS_USER_NAME'])<=0) {
	$_SESSION['SESS_USER_NAME']=$_SESSION['SESS_USER_ID'];
}
$dbLink->freeResult($result);

loadHelpers("mobility");
$device=getUserDeviceType();
$client=$_SERVER["REMOTE_ADDR"];
$persistant="false";
$mauth_key=md5(base64_encode($domain.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SESSION['SESS_TOKEN'].$device, $client));

if(isset($_POST['persistant']) && $_POST['persistant']=="true") {
	$persistant="true";
}

$q1=$dbLink->_insertQ(_dbTable("log_login",true),
	array('DATE', 'user', 'site', 'login_time', 'logout_time', 'sys_spec', 'user_agent', 'token', 'status', 'device', 'client', 'persistant', 'mauth_key'),
	array($date,$userid,$domain,date('H:i:s'),'',$_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT'],$_SESSION['SESS_TOKEN'],'LOGGED IN', $device, $client,$persistant,$mauth_key));
$dbLogLink->executeQuery($q1);


setcookie("LOGIN", "true", time()+36000);
setcookie("USER", $_SESSION['SESS_USER_ID'], time()+36000);
setcookie("TOKEN", $_SESSION['SESS_TOKEN'], time()+36000);
setcookie("SITE", $_SESSION['SESS_LOGIN_SITE'], time()+36000);

if(ALLOW_MAUTH=="true") {
	if(isset($_POST['mauth']) && $_POST['mauth']=="authkey") {
		echo $mauth_key;
	} elseif(isset($_POST['mauth']) && $_POST['mauth']=="jsonkey") {
		$arr=array(
				"user"=>$userid,
				"authkey"=>$mauth_key,
				"date"=>$date,
				"site"=>$domain,
				"client"=>$client,
			);
		header("Content-Type:text/json");
		echo json_encode($arr);
	} else {
		echo "<h5>Securing Access Authentication ... </h5>";
		if(strlen($onsuccess)==0 || $onsuccess=="*")
			header("location: ../index.php?site=$domain");
		else
			header("location: $onsuccess");
	}
} else {
	echo "<h5>Securing Access Authentication ... </h5>";
	if(strlen($onsuccess)==0 || $onsuccess=="*")
		header("location: ../index.php?site=$domain");
	else
		header("location: $onsuccess");
}
exit();

//All Functions Required By Authentication System
function relink($msg,$domain) {
	$_SESSION['SESS_ERROR_MSG']=$msg;
	
	$onerror="";
	if((ALLOW_LOGIN_RELINKING=="true" || ALLOW_LOGIN_RELINKING)) {
		if(isset($_REQUEST['onerror'])) $onerror=$_REQUEST['onerror'];
	}
	if(strlen($onerror)==0 || $onerror=="*") {
		$s="../login.php";
		if(strlen($domain)>0) $s.="?site=$domain";
		$onerror=$s;
	}
	header("Location:$onerror");
	exit($msg);
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
	if(Security::isBlacklisted($dbLink,$domain)) {
		relink("You are currently Blacklisted On Server, Please contact Site Admin.",$domain);
	} else {
		return false;
	}
}
//LogBook Checking
function checkLoginLogTables($userid,$domain, $dbLink) {
	$q1=$dbLink->_selectQ("lgks_log_login",
						"count(*), date, login_time, logout_time, sys_spec, token, client, persistant",
						array("user"=>$userid,"status"=>"LOGGED IN")// and site='$domain'
					);
	$result=$dbLink->executeQuery($q1);
	if($result) {
		$data=$dbLink->fetchData($result);
		if($data!=null && $data["count(*)"]>0) {
			if($data['persistant']=="false") {
				$sql=$dbLink->_updateQ("lgks_log_login",
						array('logout_time'=>date('Y-m-d H:i:s'), 'status'=>'LOGGED OUT'),
						array("user"=>$userid,"status"=>"LOGGED IN")
					);
				$dbLink->executeQuery($sql);
			} elseif(ALLOW_MULTI_LOGIN=="false") {
				relink("MultiLogin Attempt, You are logged in from the system {$data['sys_spec']} since {$data['login_time']} On {$data['date']}.",$domain);
			}
		}
	}
}
?>