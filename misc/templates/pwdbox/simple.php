<style>
#pwdform input {border:1px solid #aaa;width:100%;}
#pwdform {font:16px plain Verdana,San-seriff;}
</style>
<table id=pwdform width=100%>
	<tr>
		<td width=150px>Old Password</td><td><input name=oldpwd id=oldpwd type=password /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td id=msg style='color:#800000'>&nbsp;</td>
	</tr>
	<tr>
		<td width=150px>New Password</td><td><input name=newpwd id=newpwd1 type=password /></td>
	</tr>
	<tr>
		<td width=150px>Retype New Password</td><td><input id=newpwd2 type=password /></td>
	</tr>
	<tr>
		<td colspan=10><hr/></td>
	</tr>
	<tr>
		<td colspan=10 align=center>
			<button onclick='resetFields()'>Reset</button>
			<button onclick='savePWD()'>Save</button>			
		</td>
	</tr>
</table>

