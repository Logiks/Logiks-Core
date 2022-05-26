<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST['mauth']) && !isset($_REQUEST['auth-policy'])) {
	echo "<h5>Securing Access Authentication ... </h5>";
}
if(isset($_REQUEST['auth-policy'])) {
	$_REQUEST['mauth'] = $_REQUEST['auth-policy'];
}
if(!isset($_REQUEST['mauth'])) $_REQUEST['mauth'] = "session";

runHooks("preAuth");

$userid=clean($_POST['userid']);
$pwd=clean($_POST['password']);

//if(!isValidMd5($pwd)) $pwd=md5($pwd);

if(isset($_POST['site'])) $domain=$_POST['site']; 
elseif(isset($_REQUEST['site'])) $domain=$_REQUEST['site']; 
else $domain=SITENAME;


loadConfigs(ROOT . "config/auth.cfg");
include ROOT."api/helpers/pwdhash.php";
//include ROOT."api/security.php";

/*
 if(!isset($_SESSION['LOGINSALT'])) {
	if(SITENAME!="cms") {
		relink("Sorry, Login must be done through the Login Page.",$domain);
	}
}*/

if(isset($_SESSION['LOGINSALT']) && isset($_REQUEST['pubkey'])) {
	$key = pack("H*", $_SESSION['LOGINSALT']);
	$iv =  pack("H*", $_REQUEST['pubkey']);
	
	$encrypted = base64_decode($pwd);
	$pwdFinal = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
	
	$pwd=trim($pwdFinal);
	
// 	$encrypted = base64_decode($userid);
// 	$useridFinal = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
	
// 	$userid=trim($useridFinal);
}
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
if(count($userFields)<=0) $userFields=["userid"];
if(CASE_SENSITIVE_AUTH) {
	foreach ($userFields as $key => $value) {
		unset($userFields[$key]);
		$userFields["BINARY {$value}"]=$userid;
	}
} else {
	foreach ($userFields as $key => $value) {
		unset($userFields[$key]);
		$userFields["{$value}"]=$userid;
	}
}

$userColumns = _db(true)->get_columnList(_dbTable("users",true));

if(in_array("roles",$userColumns)) {
	$sql=_db(true)->_selectQ(_dbTable("users",true),"id, guid, userid, pwd, pwd_salt, privilegeid, accessid, groupid, name, email, mobile, region, country, zipcode, geolocation, geoip, tags, blocked, avatar, avatar_type, roles")->_whereOR("expires",[
			["NULL","NU"],["now()","GT"]//"0000-00-00",["0000-00-00","EQ"],
		])->_where($userFields,"AND","OR");
} else {
	$sql=_db(true)->_selectQ(_dbTable("users",true),"id, guid, userid, pwd, pwd_salt, privilegeid, accessid, groupid, name, email, mobile, region, country, zipcode, geolocation, geoip, tags, blocked, avatar, avatar_type")->_whereOR("expires",[
			["NULL","NU"],["now()","GT"]//"0000-00-00",["0000-00-00","EQ"],
		])->_where($userFields,"AND","OR");
}

$result=$sql->_get();

if(!empty($result)) {
	$data=$result[0];
} else {
	relink("Sorry, you have not yet joined us or your userid has expired.",$domain);
}

if(!isset($data['roles']) || strlen($data['roles'])<=0) $data['roles'] = $data['privilegeid'];
else {
	$data['roles'] = explode(",", $data['roles']);
	$data['roles'][] = $data['privilegeid'];
	
	if(strlen($data['roles'][0])<=0) unset($data['roles'][0]);

	$data['roles'] = array_unique($data['roles']);
	$data['roles'] = implode(",", $data['roles']);
}

// echo "{$data['pwd']} >>> $pwd >>> {$data['pwd_salt']}\n\n<br>";
// printArray(getPWDHash($pwd,$data['pwd_salt']));
// exit(matchPWD($data['pwd'],$pwd, $data['pwd_salt']));

if(!matchPWD($data['pwd'],$pwd, $data['pwd_salt'])) {
	relink("UserID/Password Wrong/Mismatch",$domain);
}
if($data['blocked']=="true") {
	relink("Sorry, you are currently blocked by system admin.",$domain);
}

$accessData=_db(true)->_selectQ(_dbTable("access",true),"sites,name as access_name")->_where([
		"id"=>$data['accessid'],
		"blocked"=>"false"
	])->_get();
$privilegeData=_db(true)->_selectQ(_dbTable("privileges",true),"id,md5(concat(id,name)) as hash,name as privilege_name")->_where([
		"id"=>$data['privilegeid'],
		"blocked"=>"false"
	])->_get();

$groupData=_db(true)->_selectQ(_dbTable("users_group",true),"id,group_name,group_manager,group_descs")->_where([
		"id"=>$data['groupid']
	])->_get();

if(empty($accessData)) {
	relink("No Accessibilty Defined For You Or Blocked By Admin.",$domain);
} else {
	$accessData=$accessData[0];
}
if(empty($privilegeData)) {
	relink("No Privileges Defined For You Or Blocked By Admin.", $domain);
} else {
	$privilegeData=$privilegeData[0];
}
if(empty($groupData)) {
	$groupData="";
} else {
	$groupData=$groupData[0];
}

$allSites=explode(",",$accessData['sites']);
if($accessData['sites']=="*") {
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

$_ENV['AUTH-DATA']=array_merge($data,$accessData);
$_ENV['AUTH-DATA']=array_merge($_ENV['AUTH-DATA'],$privilegeData);

if(isset($_POST['policy_scope'])) {
	$roleScopeData=_db(true)->_selectQ(_dbTable("rolescope",true),"*")->_where([
			"blocked"=>"false",
			"scope_id"=> $_POST['policy_scope']
		])->_whereRAW("(privilegeid='{$privilegeData['privilege_name']}' OR privilegeid='*')")
			->_GET();
	if(!$roleScopeData) $roleScopeData = [];
} else {
	$roleScopeData = [];
}

$finalScope = [];
foreach($roleScopeData as $row) {
	if(!isset($finalScope[$row["scope_type"]])) $finalScope[$row["scope_type"]] = [];
	$scopeData = json_decode($row['scope_params'], true);
	if($scopeData) {
		$finalScope[$row["scope_type"]] = array_merge($finalScope[$row["scope_type"]], $scopeData);
	}
}
$_ENV['AUTH-DATA']['policies'] = $finalScope;

loadHelpers("mobility");

$_ENV['AUTH-DATA']['device']=getUserDeviceType();
$_ENV['AUTH-DATA']['client']=_server("REMOTE_ADDR");
if(isset($_POST['persistant']) && $_POST['persistant']) {
	$_ENV['AUTH-DATA']['persistant']="true";
} else {
	$_ENV['AUTH-DATA']['persistant']="false";
}
$_ENV['AUTH-DATA']['sitelist']=$allSites;
$_ENV['AUTH-DATA']['groups']=$groupData;

checkBlacklists($data,$domain,$dbLink,$userid);

runHooks("postAuth");

initializeLogin($userid, $domain);


//All Functions Required By Authentication System
function relink($msg,$domain) {
	_log("Login Attempt Failed","login",LogiksLogger::LOG_ALERT,[
				"userid"=>$_POST['userid'],
				"site"=>$domain,
				"device"=>getUserDeviceType(),
				"client_ip"=>$_SERVER['REMOTE_ADDR'],
				"msg"=>$msg]);

	$_SESSION['SESS_ERROR_MSG']=$msg;
	
	$onerror="";
	if((ALLOW_LOGIN_RELINKING || ALLOW_LOGIN_RELINKING)) {
		if(isset($_REQUEST['onerror'])) $onerror=$_REQUEST['onerror'];
	}
	if(ALLOW_MAUTH) {
    if(isset($_REQUEST['mauth'])) {
      if($_REQUEST['mauth']=="jwt") {
        header("Content-Type:text/json");
        echo json_encode(["msg"=>$msg,"status"=>'failed']);
      } elseif($_REQUEST['mauth']=="authkey") {
        echo "ERROR:$msg";
      } elseif($_REQUEST['mauth']=="jsonkey" || $_REQUEST['mauth']=="json") {
        header("Content-Type:text/json");
        echo json_encode(["msg"=>$msg,"status"=>'failed']);
      } else {
        echo "ERROR/$msg";
      }
      exit();
    }
	}
	
	if(strlen($onerror)==0 || $onerror=="*") {
		$s=SiteLocation."login";
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
function checkBlacklists($data,$domain,$dbLink,$userid) {
	$ls=new LogiksSecurity();
	if($ls->isBlacklisted("login",$domain)) {
		relink("You are currently Blacklisted On Server, Please contact Site Admin.",$domain);
	} else {
		return false;
	}
}

//LogBook Checking
function initializeLogin($userid,$domain,$params=array()) {
	startNewSession($userid, $domain, $params);

	_log("Login Successfull @{$_SESSION['SESS_USER_ID']}","login",LogiksLogger::LOG_INFO,[
				"guid"=>$_SESSION['SESS_GUID'],
				"userid"=>$_SESSION['SESS_USER_ID'],
				"username"=>$_SESSION['SESS_USER_NAME'],
				"site"=>$domain,
				"device"=>$_ENV['AUTH-DATA']['device'],
				"client_ip"=>$_SERVER['REMOTE_ADDR']]);
	
	_db(true)->_updateQ(_dbTable("users",true),['last_login'=>date("Y-m-d H:i:s")],["guid"=>$_SESSION['SESS_GUID'],
				"userid"=>$_SESSION['SESS_USER_ID']])->_RUN();
	
	gotoSuccessLink();
}
//All session functions
function startNewSession($userid, $domain, $params=array()) {
	session_regenerate_id();
	$data=$_ENV['AUTH-DATA'];
	//printArray($data);exit();

	$_SESSION['SESS_GUID'] = $data['guid'];
	
	$_SESSION['SESS_USER_ID'] = $data['userid'];
	$_SESSION['SESS_PRIVILEGE_ID'] = $data['privilegeid'];
	$_SESSION['SESS_ACCESS_ID'] = $data['accessid'];
	$_SESSION['SESS_GROUP_ID'] = $data['groupid'];
	
	$_SESSION['SESS_PRIVILEGE_NAME'] = $data['privilege_name'];
	$_SESSION['SESS_ACCESS_NAME'] = $data['access_name'];
	$_SESSION['SESS_ACCESS_SITES'] = $data['sitelist'];

	if(empty($data['groups'])) {
		$data['groups']=[
				"id"=>0,
				"group_name"=>"",
				"group_manager"=>"",
				"group_descs"=>"",
			];
	}
	$_SESSION['SESS_GROUP_ID'] = $data['groups']['id'];
	$_SESSION['SESS_GROUP_NAME'] = $data['groups']['group_name'];
	$_SESSION['SESS_GROUP_MANAGER'] = $data['groups']['group_manager'];
	$_SESSION['SESS_GROUP_DESCS'] = $data['groups']['group_descs'];

	$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);

	$_SESSION['SESS_USER_NAME'] = $data['name'];
	$_SESSION['SESS_USER_EMAIL'] = $data['email'];
	$_SESSION['SESS_USER_CELL'] = $data['mobile'];
	
	$_SESSION['SESS_USER_COUNTRY'] = $data['country'];
	$_SESSION['SESS_USER_ZIPCODE'] = $data['zipcode'];
	$_SESSION['SESS_USER_GEOLOC'] = $data['geolocation'];
	
	$_SESSION['SESS_USER_AVATAR'] = $data['avatar_type']."::".$data['avatar'];

	if(isset($data['policies'])) {
		$_SESSION['SESS_POLICY'] = $data['policies'];
	} else {
		$_SESSION['SESS_POLICY'] = [];
	}

	$_SESSION["SESS_ROLE_LIST"] = getUserRoleList($data['roles']);

	$_SESSION['SESS_LOGIN_SITE'] = $domain;
	$_SESSION['SESS_ACTIVE_SITE'] = $domain;
	$_SESSION['SESS_TOKEN'] = session_id();
	$_SESSION['SESS_SITEID'] = SiteID;
	$_SESSION['SESS_LOGIN_TIME'] =time();
	$_SESSION['MAUTH_KEY'] = generateMAuthKey();
	
	if($data['privilegeid']<=1) {
		$_SESSION["SESS_FS_FOLDER"]=ROOT;
		$_SESSION["SESS_FS_URL"]=SiteLocation;
	} else {
		$_SESSION["SESS_FS_FOLDER"]=ROOT.APPS_FOLDER.$domain."/";
		$_SESSION["SESS_FS_URL"]=SiteLocation.APPS_FOLDER.$domain."/";
	}

	if(strlen($_SESSION['SESS_USER_NAME'])<=0) {
		$_SESSION['SESS_USER_NAME']=$_SESSION['SESS_USER_ID'];
	}
	
	if(isset($_POST['geolocation'])) {
		$_SESSION['SESS_GEOLOCATION']=$_POST['geolocation'];
	}

	LogiksSession::getInstance(true);

	header_remove("SESSION-KEY");
	header("SESSION-KEY:".$_SESSION['SESS_TOKEN'],false);
	header("SESSION-MAUTH:".$_SESSION['MAUTH_KEY'],false);

	setcookie("LOGIN", "true", time()+36000,"/",null, isHTTPS());
	setcookie("USER", $_SESSION['SESS_USER_ID'], time()+36000,"/",null, isHTTPS());
	setcookie("TOKEN", $_SESSION['SESS_TOKEN'], time()+36000,"/",null, isHTTPS());
	setcookie("SITE", $_SESSION['SESS_LOGIN_SITE'], time()+36000,"/",null, isHTTPS());
	
	$cnt = _db(true)->_selectQ(_dbTable("cache_sessions",true),"count(*) as cnt","created_on<DATE_SUB(NOW(), INTERVAL 1 MONTH)")->_GET();
	if($cnt[0]['cnt']>10000) {
		_db(true)->_selectQ(_dbTable("cache_sessions",true),"created_on<DATE_SUB(NOW(), INTERVAL 1 MONTH)")->_RUN();
	}
	
	if($data['persistant'] || (ALLOW_MAUTH && isset($_REQUEST['mauth']))) {
		_db(true)->_deleteQ(_dbTable("cache_sessions",true),"edited_on<DATE_SUB(NOW(), INTERVAL 10 DAY)")
				->_where([
				"guid"=>$_SESSION['SESS_GUID'],
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>$domain,
			])->_RUN();
		_db(true)->_insertQ1(_dbTable("cache_sessions",true),[
				"guid"=>$_SESSION['SESS_GUID'],
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>$domain,
				"device"=>$_ENV['AUTH-DATA']['device'],
				"session_key"=>$_SESSION['SESS_TOKEN'],
				"auth_key"=>$_SESSION['MAUTH_KEY'],
				"session_data"=>json_encode($_SESSION),
				"global_data"=>json_encode($GLOBALS),
				"client_ip"=>$_SERVER['REMOTE_ADDR'],
				"created_by"=>$_SESSION['SESS_USER_ID'],
				"edited_by"=>$_SESSION['SESS_USER_ID'],
			])->_RUN();
	}
}
function logoutOldSessions($userid, $domain, $params=array()) {
	_db(true)->_deleteQ(_dbTable("cache_sessions",true),[
				"guid"=>$_SESSION['SESS_GUID'],
				"userid"=>$_SESSION['SESS_USER_ID'],
				"site"=>$domain,
			])->_RUN();
}
function restoreOldSession($sessionData, $userid, $domain, $params=array()) {
	$data=$_ENV['AUTH-DATA'];
	$sessionID=$sessionData['token'];

	$logData=_db(true)->_selectQ(_dbTable("cache_sessions",true),"*",
						array(
							"session_key"=>$sessionID,
							"userid"=>$userid,
							"site"=>$domain,
							"device"=>getUserDeviceType(),
							"client_ip"=>$_SERVER['REMOTE_ADDR'])
					)->_get();

	if(!empty($logData)) {
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
		logoutOldSessions($userid, $domain, $params);
		startNewSession($userid, $domain, $params);
	}
}
function gotoSuccessLink() {
	$onsuccess="";
	if((ALLOW_LOGIN_RELINKING || ALLOW_LOGIN_RELINKING)) {
		if(isset($_REQUEST['onsuccess'])) $onsuccess=$_REQUEST['onsuccess'];
	}

	$domain=$_SESSION['SESS_ACTIVE_SITE'];//ACTIVE
	if(ALLOW_MAUTH) {
    if(isset($_REQUEST['mauth']) && $_REQUEST['mauth']!="session") {
      if($_REQUEST['mauth']=="authkey") {
        echo $_SESSION['MAUTH_KEY'];
      } elseif($_REQUEST['mauth']=="jwt") {
        $arr=array(
            "guid"=>$_SESSION['SESS_GUID'],
            "username"=>$_SESSION['SESS_USER_NAME'],

            "user"=>$_SESSION['SESS_USER_ID'],
            "mobile"=>$_SESSION['SESS_USER_CELL'],
            "email"=>$_SESSION['SESS_USER_EMAIL'],
            "country"=>$_SESSION['SESS_USER_COUNTRY'],
            "zipcode"=>$_SESSION['SESS_USER_ZIPCODE'],
            "geolocation"=>$_SESSION['SESS_USER_GEOLOC'],

            "privilegeid"=>$_SESSION['SESS_PRIVILEGE_ID'],
            "privilege_name"=>$_SESSION['SESS_PRIVILEGE_NAME'],
            "accessid"=>$_SESSION['SESS_ACCESS_ID'],
            "groupid"=>$_SESSION['SESS_GROUP_ID'],
            "access"=>$_SESSION['SESS_ACCESS_SITES'],
            "rolelist"=>$_SESSION["SESS_ROLE_LIST"],

            "policies"=>isset($_SESSION['SESS_POLICY'])?$_SESSION['SESS_POLICY']:[],

            "timestamp"=>date("Y-m-d H:i:s"),
            "site"=>$domain,
            "client"=>_server('REMOTE_ADDR'),
            "authkey"=>$_SESSION['MAUTH_KEY'],
            //"token"=>$_SESSION['SESS_TOKEN'],

            "avatar"=>$_SESSION['SESS_USER_AVATAR'],
          );
          $jwt = new LogiksJWT();
          $jwtToken = $jwt->generateToken($arr);
          header("Content-Type:text/json");
          echo json_encode([
          	"token"=>$jwtToken,
          	"msg"=>"Login Success",
          	"status"=>'success',
          	"token-refresh"=>_service("auth-refresh",false,false,false,SITENAME,false)
          ]);
      } elseif($_REQUEST['mauth']=="jsonkey" || $_REQUEST['mauth']=="json") {
        $arr=array(
        		"guid"=>$_SESSION['SESS_GUID'],
        		"username"=>$_SESSION['SESS_USER_NAME'],

            "user"=>$_SESSION['SESS_USER_ID'],
            "mobile"=>$_SESSION['SESS_USER_CELL'],
            "email"=>$_SESSION['SESS_USER_EMAIL'],
            "country"=>$_SESSION['SESS_USER_COUNTRY'],
            "zipcode"=>$_SESSION['SESS_USER_ZIPCODE'],
            "geolocation"=>$_SESSION['SESS_USER_GEOLOC'],

            "date"=>date("Y-m-d"),
            "time"=>date("H:i:s"),
            "site"=>$domain,
            "client"=>_server('REMOTE_ADDR'),
            "authkey"=>$_SESSION['MAUTH_KEY'],
            //"token"=>$_SESSION['SESS_TOKEN'],

            
            "avatar"=>$_SESSION['SESS_USER_AVATAR'],
					
					  
            "privilege_name"=>$_SESSION['SESS_PRIVILEGE_NAME'],
					  "group_name"=>$_SESSION['SESS_GROUP_NAME'],

            "policies"=>isset($_SESSION['SESS_POLICY'])?$_SESSION['SESS_POLICY']:[],
          );
        header("Content-Type:text/json");
        echo json_encode($arr);
      } else {
        echo $_SESSION['MAUTH_KEY'];
      }
    } else {
      echo "<h5>Securing Access Authentication ... </h5>";
			if(strlen($onsuccess)==0 || $onsuccess=="*")
				header("location: ".SiteLocation."?site=$domain");
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
