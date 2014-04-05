<?php
if (!defined('ROOT')) exit('No direct script access allowed');

_css(array("formfields","settings"));

$userid=$_SESSION["SESS_USER_ID"];
$sql="SELECT * FROM lgks_users WHERE userid='$userid'";
$res=_dbQuery($sql,true);
if($res) {
	$data=_db()->fetchData($res);
	//printArray($data);
} else {
	exit("Permission Denied ?");
}
loadHelpers("countries");
?>
<style>
textarea {
	border:1px solid #aaa;
}
</style>
<div id=toolbar>
<button onclick='submitForm()'>Save</button><button onclick='resetForm()'>Reset</button>
<h2 style='float:right;margin:0px;margin-right:20px;color:#102F69;'>My Profile</h2>
</div>
<form id=profileForm method=POST action="services/?scmd=formaction&action=submit" target='targetForm'>
<div id=configurations>
		<input type=hidden name='frmMode' value='update' />
		<input type=hidden name='submit_table' value='lgks_users' />
		<input type=hidden name='submit_wherecol' value='userid' />
		<input type=hidden name='userid' value='<?=$userid?>' />
		<input type=hidden name='site' value='<?=SITENAME?>' />
		
		<input type=hidden name='frmID' value='-1' />
		<input type=hidden name='on_success' value="<b>Profile Save Successfull</b><br/><br/><div style='color:blue;text-align:center'>Please Reload Page.</div>" />
		<input type=hidden name='on_error' value="<b>Profile Save Error</b>" />
		
		<ul>
			<li class=title>Full Name</li>
			<li class=input><input name=name id=title type=text class='textfield' value='<?=$data["name"]?>'/></li>
			<li class=title>EMail</li>
			<li class=input><input name=email id=title type=text class='emailfield' value='<?=$data["email"]?>'/></li>
			<li class=title>Mobile</li>
			<li class=input><input name=mobile id=title type=text class='phonefield' value='<?=$data["mobile"]?>'/></li>
			<li class=title>Address</li>
			<li class=input>	
				<textarea name=address ><?=$data["address"]?></textarea>
			</li>
			<li class=title>City/Region</li>
			<li class=input><input name=region id=title type=text class='textfield' value='<?=$data["region"]?>'/></li>
			<li class=title>Country</li>
			<li class=input>	
				<select name=country>
					<?=getCountrySelector($data["country"])?>
				</select>
			</li>
			<li class=title>ZipCode</li>
			<li class=input><input name=zipcode id=title type=text class='textfield' value='<?=$data["zipcode"]?>'/></li>
		</ul>
</div>

<div id=settings align=right>
	<iframe id=targetForm name=targetForm width=100% height=200px frameborder=0 align=center></iframe>
</div>
</form>
<script language='javascript'>
$(function() {
	$("#toolbar button").button();
	$("#settings button").uniform();
	$("select").addClass("ui-widget-header ui-corner-all");	
});
function resetForm() {
	document.getElementById("profileForm").reset();
}
function submitForm() {
	document.getElementById("profileForm").submit();
}
</script>
