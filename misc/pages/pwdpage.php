<?php
if (!defined('ROOT')) exit('No direct script access allowed');

$tmpl=$_SESSION["PWD_TEMPLATE"];
$fs=array();

array_push($fs,ROOT.TEMPLATE_FOLDER."pwdbox/$tmpl.php");
if(defined("APPS_PAGES_FOLDER")) {
	array_push($fs,APPROOT.APPS_PAGES_FOLDER."$tmpl.php");
}
if(defined("APPS_TEMPLATE_FOLDER")) {
	array_push($fs,APPROOT.APPS_TEMPLATE_FOLDER."pwdbox/$tmpl.php");
	array_push($fs,APPROOT.APPS_TEMPLATE_FOLDER."$tmpl.php");	
}
$fTmpl="";
foreach($fs as $a) {
	if(file_exists($a)) {
		$fTmpl=$a;
		break;
	}
}
unset($_SESSION["PWD_TEMPLATE"]);
include $fTmpl;
?>
<script language=javascript>
function savePWD() {
	//alert($("#pwdform #newpwd1").val());
	if($("#pwdform input#oldpwd").val().length<=0) {
		$("#pwdform #msg").html("Old Password Is Required.");
		$("#pwdform #oldpwd").focus();
		return;
	}	
	if($("#pwdform input#newpwd1").val().length<=0) {
		$("#pwdform #msg").html("New Password Field Is Empty.");
		$("#pwdform #newpwd1").focus();
		return;
	}
	if($("#pwdform input#newpwd1").val()==$("#pwdform input#oldpwd").val()) {
		$("#pwdform #newpwd1").val("");
		$("#pwdform #newpwd2").val("");
		$("#pwdform #msg").html("Old Password And New Password Can Not Be Same.");
		$("#pwdform #newpwd1").focus();
		return;
	}
	if($("#pwdform input#newpwd1").val()!=$("#pwdform input#newpwd2").val()) {
		$("#pwdform #newpwd1").val("");
		$("#pwdform #newpwd2").val("");
		$("#pwdform #msg").html("New Passwords Don't Match.");
		$("#pwdform #newpwd1").focus();
		return;
	}
	
	s="services/?scmd=pwd&type=change";
	q="&old="+encodeURIComponent($("#pwdform #oldpwd").val())+"&new="+encodeURIComponent($("#pwdform input#newpwd1").val());
	processAJAXPostQuery(s,q,function(txt) {
			txt=$.parseJSON(txt);
			if(txt.code==1) {
				$("#pwdform").html("<tr><td>"+txt.msg+"</td></tr>");
			} else {
				lgksAlert(txt.msg);
			}
		});
}
function resetFields() {
	$("#pwdform #oldpwd,#pwdform #newpwd1,#pwdform #newpwd2").val("");
}
$(function() {
	$("#pwdform button").button();
});
</script>
