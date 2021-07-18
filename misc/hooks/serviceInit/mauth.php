<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//MAUTH KEY, checking and setting up the Mobile APP Rest Key Transactions

if(!defined("SERVICE_ROOT")) return false;

$_HEADERS = getallheaders();

if(!isset($_HEADERS['Authorization'])) {
  return false;
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
      // if($_REQUEST['site']!=$tokenData["site"]) {
      //     printServiceErrorMsg(401,"Please login to continue for this site");
      //     exit();
      // }
      //$tokenData["site"]        //SESS_LOGIN_SITE

      $_SESSION['SESS_GUID']=$tokenData["guid"];
      $_SESSION['SESS_USER_NAME']=$tokenData["username"];

      $_SESSION['SESS_USER_ID']=$tokenData["user"];
      $_SESSION['SESS_USER_CELL']=$tokenData["mobile"];
      $_SESSION['SESS_USER_EMAIL']=$tokenData["email"];
      $_SESSION['SESS_USER_COUNTRY']=$tokenData["country"];
      $_SESSION['SESS_USER_ZIPCODE']=$tokenData["zipcode"];
      $_SESSION['SESS_USER_GEOLOC']=$tokenData["geolocation"];

      $_SESSION['SESS_PRIVILEGE_ID']=$tokenData["privilegeid"];
      $_SESSION['SESS_PRIVILEGE_NAME']=$tokenData["privilege_name"];
      $_SESSION['SESS_ACCESS_ID']=$tokenData["accessid"];
      $_SESSION['SESS_GROUP_ID']=$tokenData["groupid"];
      $_SESSION['SESS_ACCESS_SITES']=$tokenData["access"];
      $_SESSION['SESS_USER_AVATAR']=$tokenData["avatar"];

      $_SESSION['SESS_POLICY'] = $tokenData['policies'];

      $_SESSION['SESS_LOGIN_SITE']=$tokenData["site"];
      $_SESSION['SESS_ACTIVE_SITE']=$tokenData["site"];

      if(!defined("SITENAME")) define("SITENAME",$_SESSION['SESS_LOGIN_SITE']);
      
      $_SESSION['SESS_SITEID'] = SiteID;
      $_SESSION['SESS_TOKEN'] = session_id();
      $_SESSION['MAUTH_KEY'] = generateMAuthKey();
      $_REQUEST['syshash'] = getSysHash();
      
      $_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);

      $_SESSION['MAUTH_KEY']=$tokenData["authkey"];

      $_SESSION['SESS_LOGIN_TIME'] = $tokenData["iat"];

      // $groupData = _db(true)->_selectQ(_dbTable("users_group", true), "*", ["blocked"=>"false", "id"=>$_SESSION['SESS_GROUP_ID']])->_GET();
      // if($groupData) {
      //   $_SESSION['SESS_GROUP_NAME'] = $groupData[0]['group_name'];
      //   $_SESSION['SESS_GROUP_MANAGER'] = $groupData[0]['group_manager'];
      //   $_SESSION['SESS_GROUP_DESCS'] = $groupData[0]['group_descs'];
      // } else {
      //   return false;
      // }
    } else {
      return false;
    }
  break;
  default:
    return false;
}

//printArray($_SESSION);exit();
?>