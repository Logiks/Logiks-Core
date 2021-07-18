<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST['auth-policy'])) {
	$_REQUEST['mauth'] = $_REQUEST['auth-policy'];
}
if(!isset($_REQUEST['mauth'])) {
	$_REQUEST['mauth'] = "jwt";
}

$_HEADERS = getallheaders();

if(!isset($_HEADERS['Authorization'])) {
  	printServiceErrorMsg(401,"Auth Token not found");
	exit();
}

$authToken = explode(" ", $_HEADERS['Authorization']);

switch($authToken[0]) {
  case "Bearer":
    $token = $authToken[1];

    $jwt = new LogiksJWT();
    $tokenData = $jwt->decodeToken($token);
    // printArray($tokenData);exit();

    //printServiceErrorMsg(401,"Please login to continue (1)");exit();

    if($tokenData) {
      if($tokenData['exp']<time()) {
          printServiceErrorMsg(401,"Please login to continue");
          exit();
      }
      
      //Session setup is already done via mauth hook
      refreshAuthSession();
    } else {
      	printServiceErrorMsg(401,"Auth Token expired or is invalid");
		exit();
    }
  break;
  default:
    printServiceErrorMsg(401,"Authorization not supported");
	exit();
}

function refreshAuthSession() {
	// printArray($_SESSION);
	switch($_REQUEST['mauth']) {
		case "authkey":
			echo generateMAuthKey();
		break;
		case "jwt":
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

	            "policies"=>isset($_SESSION['SESS_POLICY'])?$_SESSION['SESS_POLICY']:[],

	            "timestamp"=>date("Y-m-d H:i:s"),
	            "site"=>SITENAME,
	            "client"=>_server('REMOTE_ADDR'),
	            "authkey"=>$_SESSION['MAUTH_KEY'],
	            //"token"=>$_SESSION['SESS_TOKEN'],

	            "privilegeid"=>$_SESSION['SESS_PRIVILEGE_ID'],
	            "privilege_name"=>$_SESSION['SESS_PRIVILEGE_NAME'],
	            "accessid"=>$_SESSION['SESS_ACCESS_ID'],
	            "groupid"=>$_SESSION['SESS_GROUP_ID'],

	            "avatar"=>$_SESSION['SESS_USER_AVATAR'],
	          );
			
			$jwt = new LogiksJWT();
			$jwtToken = $jwt->generateToken($arr);
			header("Content-Type:text/json");
			echo json_encode(["token"=>$jwtToken,"msg"=>"Login Success","status"=>'success']);
		break;
		case "jsonkey":case "json":
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
		break;
		default:
			echo generateMAuthKey();
		break;
	}
}
?>