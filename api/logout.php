<?php
include "initialize.php";
$domain=$_SESSION['SESS_LOGIN_SITE'];

$relink="";
if(isset($_REQUEST['relink'])) $relink=$_REQUEST['relink'];

$dbLogLink=LogDB::singleton()->getLogDBCon();
$q1=$dbLogLink->_updateQ("lgks_log_login",
		array('logout_time'=>date('Y-m-d H:i:s'), 'status'=>'LOGGED OUT'),
		array("user"=>$_SESSION['SESS_USER_ID'],"status"=>"LOGGED IN","token"=>$_SESSION['SESS_TOKEN'])
	);//,"site"=>$_SESSION['SESS_LOGIN_SITE']

$dbLogLink->executeQuery($q1,true);

clearCookies(null);
session_destroy();

if(strlen($relink)>0) {
	if($relink=="#") {
		
	} else {
		header("Location:$relink");
	}
} else {
	header("Location:../login.php?site=$domain");
}
?>
<h5>Redirecting To Login Screen ...</h5>
