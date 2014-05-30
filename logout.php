<?php
//Logout file, used to destroy everything
require_once ('api/initialize.php');

$domain=$_SESSION['SESS_LOGIN_SITE'];

$relink="";
if(isset($_REQUEST['relink'])) $relink=$_REQUEST['relink'];

$dbLogLink=LogDB::singleton()->getLogDBCon();
$q1=$dbLogLink->_updateQ("lgks_log_login",
		array('logout_time'=>date('Y-m-d H:i:s'), 'status'=>'LOGGED OUT'),
		array(
			"user"=>$_SESSION['SESS_USER_ID'],
			"status"=>"LOGGED IN",
		)
	)." AND (token='{$_SESSION['SESS_TOKEN']}' OR mauth_key='{$_SESSION['MAUTH_KEY']}')";

$dbLogLink->executeQuery($q1,true);

clearCookies(null);
session_destroy();

if(strlen($relink)>0) {
	if($relink=="#") {
		header("Location:login.php?site=$domain");
	} else {
		header("Location:$relink");
	}
} else {
	header("Location:login.php?site=$domain");
}
?>
<h5>Redirecting To Login Screen ...</h5>
