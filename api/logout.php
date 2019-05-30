<?php
if (!defined('ROOT')) exit('No direct script access allowed');

loadHelpers('cookies');

$domain=SITENAME;
if(isset($_REQUEST['site'])) {
	$domain=$_REQUEST['site'];
} elseif(isset($_SESSION['SESS_LOGIN_SITE'])) {
	$domain=$_SESSION['SESS_LOGIN_SITE'];
}

//loadLogiksApp($domain);

$relink="";
if(isset($_REQUEST['relink'])) $relink=$_REQUEST['relink'];

// $dbLogLink=LogDB::singleton()->getLogDBCon();
// $q1=$dbLogLink->_updateQ("lgks_log_login",
// 		array('logout_time'=>date('Y-m-d H:i:s'), 'status'=>'LOGGED OUT'),
// 		array(
// 			"user"=>$_SESSION['SESS_USER_ID'],
// 			"status"=>"LOGGED IN",
// 		)
// 	)." AND (token='{$_SESSION['SESS_TOKEN']}' OR mauth_key='{$_SESSION['MAUTH_KEY']}')";

// $dbLogLink->executeQuery($q1,true);

clearCookies(null);
session_destroy();

if(defined("SERVICE_ROOT")) {
	printServiceMsg("Logout Successfull");
} else {
	if(strlen($relink)>0) {
		if($relink=="#") {
			header("Location:"._link(getConfig("PAGE_HOME")));
		} else {
			header("Location:$relink");
		}
	} else {
		header("Location:"._link(getConfig("PAGE_HOME")));
	}
}
?>