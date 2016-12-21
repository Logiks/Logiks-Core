<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("pwdhash");

if(!isset($_REQUEST["type"])) {
	$_REQUEST["type"]="";
}
if($_REQUEST["type"]=="dialog") {
	checkServiceSession();
	printDialog();
} elseif($_REQUEST["type"]=="change") {
	checkServiceSession();
	changePWD();
} elseif($_REQUEST["type"]=="generate") {
	loadHelpers("pwdgen");
	$pwd=generatePasswordY(getConfig("PWD_MIN_LENGTH"),3);
	echo $pwd;	
}
exit();
function changePWD() {
	$userid=$_SESSION["SESS_USER_ID"];
	$tbl=_dbTable("users",true);
	
	$data=_db(true)->_selectQ($tbl,"pwd",["userid"=>$userid])->_GET();
	
	if(count($data)<=0) {
		$q=array(
				"code"=>"1",
				"msg"=>"Error In Changing Password (1).",
			);
		echo json_encode($q);
		exit();
	}
	
	$ra=$data[0];
	$_POST["old"]=getPWDHash($_POST["old"]); 
	$_POST["new"]=getPWDHash($_POST["new"]);
	
	//printArray($ra);
	//printArray($_POST);
	//exit();
	
	if($ra["pwd"]!=$_POST["old"]) {
		$q=array(
				"code"=>"0",
				"msg"=>"Old Password Doesn't Match. Please Use Correct Credentials. (2)",
			);
		echo json_encode($q);
		exit();
	}
	
	$oldPwd=$_POST["old"];
	$newPwd=$_POST["new"];
	$date=date("Y-m-d");
	
	$a=_db(true)->_updateQ($tbl,["pwd"=>$newPwd,"edited_on"=>$date],["userid"=>$userid])->_RUN();
	if($a && _db(true)->get_affectedRows()<=0) {
		$q=array(
				"code"=>"0",
				"msg"=>"Old Password Doesn't Match. Please Use Correct Credentials.(3)",
			);
		echo json_encode($q);
	} else {
		$q=array(
				"code"=>"1",
				"msg"=>"Successfully Updated Your New Password",
			);
		echo json_encode($q);
	}
}
function printDialog() {
	$_SESSION["PWD_TEMPLATE"]="simple";
	if(isset($_REQUEST["templ"])) $_SESSION["PWD_TEMPLATE"]=$_REQUEST["templ"];
	
	$f=ROOT.PAGES_FOLDER."pwdpage.php";
	if(file_exists($f)) {
		include $f;
		exit();
	} else {
		exit("<h3 align=center style='color:maroon;'>Sorry, No Password Change Page Found.</h3>");
	}
} 
?>
