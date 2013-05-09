<?php
if(isset($_REQUEST['keycode']) && strlen($_REQUEST['keycode'])>0) {
	$sql="SELECT userid FROM "._dbtable("users",true)." WHERE vcode='{$_REQUEST['keycode']}'";
	$r=_dbQuery($sql,true);
	if($r) {
		if(_db(true)->recordCount($r)>0) {
			$d=_db(true)->fetchData($r);
			printForm($d["userid"],$_REQUEST['keycode']);
		} else {
			header("Location:error.php?code=401");
		}
	} else {
		header("Location:error.php?code=401");
	}
} else {
	header("Location:error.php?code=401");
}
exit();
function printForm($userid,$vcode) {
?>
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
_js(array("jquery","jquery.ui","dialogs","ajax"));
_skin(array("jquery.ui.".getConfig("LOGIN_JQTHEME")));
_css(array("formfields","ajax"));
?>
<style>
#restoreform input {
	width:100%;
	height:22px;
	border:1px solid #aaa;
}
</style>
</head>
<body oncontextmenu='return true' onselectstart='return false' ondragstart='return false'  >
	<div class="wrapper" style="width:800px;margin:auto;">
	<div class="ui-widget-header" style='height:21px;padding:3px;'><b>Password Recovery</b></div><br/>
	<p style="width:90%;margin:auto;text-align:justify;font-family:Georgia, Times, serif;">
		<h3 style='margin:0px;padding:0px;color:maroon;'>Reset Your Password !</h3>
		<span style='font-size:14px;padding:25px;'>
		Please fill the passwords in the given boxes along with the CAPTCHA. Pressing Submit button will change your 
		password to the current password. Then you can login using this password.
		</span>
	</p>
	<div id=restoreform style='width:100%;'>
		<form>
		<table class='formTable' cellpadding=2 cellspacing=0 border=0 style='Width:500px;border:0px solid #aaa;padding:3px;'>
			<input name='userid' type=hidden value='<?=$userid?>' />
			<input name='vcode' type=hidden value='<?=$vcode?>' />
			<tr align=left>
				<th class=title width=30%>New Password</th><td class=value><input name=pwd1 type=password value='' /></td><td class=support width=5%></td>
			</tr>
			<tr align=left>
				<th class=title width=30%>Old Password</th><td class=value><input name=pwd2 type=password value='' /></td><td class=support width=5%></td>
			</tr>
			<tr align=left>
				<th class=title width=30% valign=top>Humans Only</th>
				<td class=value>
					<input name=captcha type=text value='' style='width:100px;float:right;margin-top:10px;' />
					<div id=captchaviewer style='background:url(services/?scmd=captcha&captchaId=pwdr1) no-repeat center left;width:200px;height:50px;' 
						ondblclick='$(this).css("background","url(services/?scmd=captcha&captchaId=pwdr1) no-repeat center left");'></div>
					<small><small>Please Double Click To refresh Captcha</small></small>
				</td>
				<td class=support width=5%></td>
			</tr>
			<tr align=left>
				<td colspan=10><hr/></td>
			</tr>
			<tr align=left>
				<td colspan=10 align=center>
					<button type=reset>Clear</button>
					<button type=button onclick="submitForm();">Submit</button>
				</td>
			</tr>
		</table>
		</form>
	</div>
	<hr/>
	<div align=center><small><small>Thank you.</small></small></div>
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
	if($("#restoreform input[name=pwd1]").val()==null || $("#restoreform input[name=pwd1]").val().length<=0) {
		lgksAlert("Password Fields CanNot Be Empty.");
		return false;
	}
	if($("#restoreform input[name=pwd2]").val()==null || $("#restoreform input[name=pwd2]").val().length<=0) {
		lgksAlert("Password Fields CanNot Be Empty.");
		return false;
	}
	if($("#restoreform input[name=pwd1]").val()!=$("#restoreform input[name=pwd2]").val()) {
		lgksAlert("Password Fields Should Match.");
		return false;
	}
	if($("#restoreform input[name=captcha]").val()==null || $("#restoreform input[name=captcha]").val().length<=0) {
		lgksAlert("Filling the CAPTCHA field is must to prove that you are human.");
		return false;
	}
	l="services/?scmd=pwdrecover&action=reset";
	q="&userid="+$("#restoreform input[name=userid]").val();
	q+="&vcode="+$("#restoreform input[name=vcode]").val();
	q+="&pwd="+$("#restoreform input[name=pwd1]").val();
	q+="&captcha="+$("#restoreform input[name=captcha]").val();
	q+="&captchaId=pwdr1";
	$("#restoreform").html("<div class='ajaxloading6'>Processing ...</div>");
	$("#restoreform input").val("");
	$("#restoreform #captchaviewer").css("background","url(services/?scmd=captcha&captchaId=pwdr1) no-repeat center left");
	processAJAXPostQuery(l,q,function(txt) {
			$("#restoreform").html(txt);
			$("button").button();
			//if(txt.length>0) lgksAlert(txt);
		});
}
</script>
</html>
<?php } ?>
