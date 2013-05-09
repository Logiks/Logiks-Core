<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Password Recovery Page</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1">
	<meta name="generator" content="Logiks" />
	<meta name="robots" content="index,nofollow" />
<?php
$jqTheme=getConfig("LOGIN_JQTHEME");
if(strlen($jqTheme)>0) $jqTheme="jquery.ui.{$jqTheme}";
else $jqTheme="jquery.ui";
_js(array("jquery","jquery.ui","dialogs","ajax"));
_skin(array($jqTheme));
_css(array("formfields","ajax"));

$loginLink=SiteLocation."login.php";
if(isset($_REQUEST['site'])) $loginLink.="?site={$_REQUEST['site']}";
?>
<style>
#restoreform input {
	width:100%;
	height:22px;
	border:1px solid #aaa;
}
</style>
</head>
<body oncontextmenu='return false' onselectstart='return false' ondragstart='return false'  >
	<div class="wrapper" style="width:800px;margin:auto;">
	<div class="ui-widget-header" style='height:21px;padding:3px;'><b>Password Recovery</b></div><br/>
	<p style="width:90%;margin:auto;text-align:justify;font-family:Georgia, Times, serif;">
		<h3 style='margin:0px;padding:0px;color:maroon;'>Did You Forget Your Password ?</h3>
		<span style='font-size:14px;padding:25px;'>
		To reset your password, enter the userid you use to sign in to our service and the emailid that you used
		as your primary email id.The Password Reset Link Will be sent on your email which then can be used to reset
		your password and set new password.
		</span>
	</p>
	<div id=restoreform style='width:100%;'>
		<form>
		<table class='formTable' cellpadding=2 cellspacing=0 border=0 style='Width:500px;border:0px solid #aaa;padding:3px;'>
			<tr align=left>
				<th class=title width=30%>User ID</th><td class=value><input name=userid type=text value='' /></td><td class=support width=5%></td>
			</tr>
			<tr align=left>
				<th class=title width=30%>Date Of Birth</th><td class=value><input name=dob type=text value='' class='datefield' /></td><td class=support width=5%></td>
			</tr>
			<tr align=left>
				<th class=title width=30%>EmailID</th><td class=value><input name=email type=text value='' class='emailfield' /></td><td class=support width=5%></td>
			</tr>
			<tr align=left>
				<th class=title width=30% valign=top>Humans Only</th>
				<td class=value>
					<input name=captcha type=text value='' style='width:100px;float:right;margin-top:10px;' />
					<div id=captchaviewer style='background:url(<?=SiteLocation?>services/?scmd=captcha&cid=pwdr1) no-repeat center left;width:200px;height:50px;'
						ondblclick='$(this).css("background","url(<?=SiteLocation?>services/?scmd=captcha&cid=pwdr1) no-repeat center left");'></div>
					<small><small>Please Double Click To refresh Captcha</small></small>
				</td>
				<td class=support width=5%></td>
			</tr>
			<tr align=left>
				<td colspan=10><hr/></td>
			</tr>
			<tr align=left>
				<td colspan=10 align=center>
					<button type=reset onclick="document.location='<?=$loginLink?>'">Login</button>
					<button type=reset>Clear</button>
					<button type=button onclick="submitForm();">Save</button>
				</td>
			</tr>
		</table>
		</form>
	</div>
	<hr/>
	<div align=center><small><small>Thank you, Please check your mail.</small></small></div>
	</div>
</body>
<script language=javascript>
lnk1="<?=_url()?>";
lnk2="<?=_site()?>";
$(function() {
	$("button").button();
	$("#restoreform input.datefield").datepicker({
			dateFormat:"<?=getConfig("DATE_FORMAT")?>",
		});
});
function submitForm() {
	if($("#restoreform input[name=userid]").val()==null || $("#restoreform input[name=userid]").val().length<=0) {
		lgksAlert("UserID is required to reset your password.");
		return false;
	}
	if($("#restoreform input[name=dob]").val()==null || $("#restoreform input[name=dob]").val().length<=0) {
		lgksAlert("The Data Of Birth that you used during registering is also required.");
		return false;
	}
	if($("#restoreform input[name=email]").val()==null || $("#restoreform input[name=email]").val().length<=0) {
		lgksAlert("The Password Reset Mail Will Be Dispatched at the registered address.");
		return false;
	}
	if($("#restoreform input[name=captcha]").val()==null || $("#restoreform input[name=captcha]").val().length<=0) {
		lgksAlert("Filling the CAPTCHA field is must to prove that you are human.");
		return false;
	}
	l="services/?scmd=pwdrecover&action=submit";
	q="&user="+$("#restoreform input[name=userid]").val();
	q+="&dob="+$("#restoreform input[name=dob]").val();
	q+="&email="+$("#restoreform input[name=email]").val();
	q+="&captcha="+$("#restoreform input[name=captcha]").val();
	q+="&captchaId=pwdr1";
	$("#restoreform").html("<div class='ajaxloading6'>Processing ...</div>");
	$("#restoreform input").val("");
	$("#restoreform #captchaviewer").css("background","url(services/?scmd=captcha&cid=pwdr1) no-repeat center left");
	processAJAXPostQuery(l,q,function(txt) {
			$("#restoreform").html(txt);
			$("button").button();
			//if(txt.length>0) lgksAlert(txt);
		});
}
</script>
</html>
