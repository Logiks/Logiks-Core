<?php
if (!defined('ROOT')) exit('No direct script access allowed');

_js(array("jquery","jquery.ui","dialog"));
$css->loadSkin("jquery.ui.".$GLOBALS["CONFIG"]["LOGIN_SKIN"]);
_css("login");

$watermark=$GLOBALS["CONFIG"]["WATERMARK"];
$watermark_style=$GLOBALS["CONFIG"]["WATERMARK_STYLE"];
$bg=$GLOBALS["CONFIG"]["BACKGROUND"];

if(isset($GLOBALS["CONFIG"]["HEADER_MESSAGE"])) $hdMsg=$GLOBALS["CONFIG"]["HEADER_MESSAGE"];
else $hdMsg="Sign In!";

echo "<style>";
if(strlen($bg)>0) echo "html,body {background-image:url(media/cssbg/$bg);}";
echo "#screentable { background: transparent url(".loadMedia($watermark).") $watermark_style; }";
echo "</style>";
?>
<table id="screentable" width=100% height=100%>
		<tr class='prelogindivTr'><td align=center>
			<div id="errormsg" style="display:none;"></div>
		</td></tr>
		<tr class='logindivTr'><td width=100% height=100% align=center>
			<div id="logindiv" style="display:none">
				<div id="loginHeader"><?=$hdMsg?></div>
				<div id="loginLogo"></div>
				<div id="loginForm">
					<form name="loginForm" method="post" action="<?=_service("auth")?>" onsubmit="return validateForm();" 
						<?php
							if(getConfig("ALLOW_USERID_AUTOCOMPLETE")=="false") {
								echo "autocomplete='off'";
							}
						?>
						>
						<input name=onsuccess type=hidden value="<?=$relinkPage?>" />
						<input name=onerror type=hidden value="*" />
						<div class='formbox username'>
							<label for='userid'>Username : </label>
							<input placeholder='Username' name=userid type=text class='textfield' onchange="enableLogin();" onKeyUp="enableLogin();" value="" >
						</div>
						<div class='formbox password'>
							<label for='password'>Password : </label>
							<input placeholder='Password' name=password type=password class='textfield pwdfield'  value="" disabled=true>
						</div>
						<div class='formbox domain'>
							<label for='site'>Domain : </label>
							<select name="site" id='domain'>
								<?=$site_selector?>
							</select>
						</div>
						<?php if($GLOBALS["CONFIG"]["ALLOW_PERSISTENT_LOGIN"]=="true") { ?>
						<span class='formbox keepsigned'>
							<input name='persistant' id="keepsigned" type=checkbox value='true'>
							<label id="keepsignedlabel" for='keepsigned'>Keep Me Signed In</label>
						</span>
						<?php } ?>
						<hr/>
						<div id="loginbtns" align=center>
							<button class=button type="reset" id="Reset" name="Reset" value="Reset" width='100' 
									onclick="$('#Submit, input[name=password]').attr('disabled', 'disabled');$('input[name=userid]').focus();">
								<div class="icon reseticon"></div>Reset</button>
							<button class=button type="submit" id="Submit" name="Submit" value="Login" width='100' disabled=true>
								<div class="icon loginicon"></div>Login</button>
						</div>
					</form>
					<div id="pagelinks">
						<?php
							if(getConfig("ALLOW_HOME")=="true") {
								echo "<a class='home' href='"._link("home")."'>Go Home</a>";
							}
							if(getConfig("ALLOW_REGISTER")=="true") {
								$cfg=getConfig("REGISTER_PAGE");
								if($cfg=="auto")
									echo "<a class='register autoregister' href='#'>Register !</a>";
								elseif(strlen($cfg)>0)
									echo "<a class='register' href='"._link(getConfig("REGISTER_PAGE"))."'>Register !</a>";
							}
							if(getConfig("ALLOW_PASSWORD_RECOVER")=="true") {
								$cfg=getConfig("PWDRECOVER_PAGE");
								if($cfg=="auto")
									echo "<a class='recover autorecover' href='#'>Recover !</a>";
								elseif(strlen($cfg)>0)
									echo "<a class='recover' href='"._link($cfg)."'>Recover !</a>";
							}
						?>
					</div>
				</div>
			</div>
			<div class='browserLogos' align=center>
			<?php
				if($GLOBALS["CONFIG"]["SHOW_BROWSER_LOGOS"]=="true") {
					echo "<img src='media/images/browsers/all_browser_logos-32.png' >";
				}
			?>
			</div>
			<?php
				if($GLOBALS["CONFIG"]["SHOW_COPYRIGHT"]=="true") {
					if(defined("APPS_COPYRIGHT")) {
						echo "<div id=loginFooter>".APPS_COPYRIGHT."</div>";
					} else {
						echo "<div id=loginFooter>".Framework_Copyright."</div>";
					}				
				}
			?>
		</td></tr>
	</table>
<div style="display:none">
	<div id="pwdrecover" title="Password Recovery">
		<table>
			<tr>
				<th align=left width=150px>UserID</th>
				<td><input name=userid type=text readonly value="" class='ui-corner-all' /></td>
			</tr>
			<tr>
				<th align=left width=150px>EMail</th>
				<td><input name=email type=text class='ui-corner-all' /></td>
			</tr>
		</table>
	</div>
</div>
<script>
$(function() {
	$('input[name=userid]').val("");
	$('input[name=password]').val("");
	$('input[name=userid]').focus();
	
	$('#logindiv').fadeIn();
	<?php
		if(isset($errormsg)) echo "showError('$errormsg');";
	?>
});
function enableLogin() {
	if($('input[name=userid]').val().length<=0) {
		$('#Submit, input[name=password]').attr('disabled', 'disabled');
	} else {
		$('#Submit, input[name=password]').removeAttr('disabled');
	}
}
function validateForm() {
	if($('input[name=userid]').val().length<=0) {
		showError("Please fill proper UserId.");
		$('input[name=userid]').focus();
		return false;
	}
	return true;
}
function showError(msg) {
	$('#errormsg').hide();
	$('#errormsg').html(msg);
	$('#errormsg').fadeIn('slow').delay(5000).slideUp("slow");
}
</script>