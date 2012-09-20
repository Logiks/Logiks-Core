<style>
#pwdform input {width:99%;height:20px;border:1px solid #aaa;}
#pwdform {font:16px plain Verdana,San-seriff;}
#pwdform input[readonly] {background:#eee;}
.pwdicon {background:transparent url(media/images/forbidden.png) no-repeat center left; width:48px; height:48px;padding-left:50px;}
</style>
<div id=pwdform width=100%>
	<h3 class='pwdicon' style='width:90%;font-size:2.3em;color:#3DBBFF;padding-top:10px;'>Change Your Password</h3>
	<p>
		To change your password, first enter your old/current password then type in the
		new password and retype it again the Confirm Password. Then Press <b>Ok</b> 
		to change your password. You can cancel any time by pressing <b>Cancel</b>.
	</p>			
	<table width=100% border=0>
		<tr>
			<th width=150px align=right>Current Password</th><td><input type=password id=oldpwd class='' style='font-size:14px;font-weight:bold;' /></td>
		</tr>
		<tr><td colspan=10><hr/></td></tr>
		<tr>
			<th width=150px align=right>New Password</th><td><input type=password id=newpwd1 class='' style='font-size:14px;font-weight:bold;' /></td>
		</tr>
		<tr>
			<th width=150px align=right>Confirm Password</th><td><input type=password id=newpwd2 class='' style='font-size:14px;font-weight:bold;' /></td>
		</tr>
		<tr><td colspan=10><hr/></td></tr>
		<tr><td colspan=10 align=right>
			<button onclick='resetFields()'>Reset</button>
			<button onclick='savePWD()'>Change Password</button>
			<br/><br/>
		</td></tr>
		<tr><td class='ui-widget-content' id=pwdmsgs colspan=10 style='padding:4px;' align=center><h5 style='margin:0px;'>Please fill the all the fields.</h5></td></tr>
	</table>
</div>

