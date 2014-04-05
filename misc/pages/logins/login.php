<?php
if (!defined('ROOT')) exit('No direct script access allowed');

_js(array("jquery","jquery.ui","jquery.ghosttext"));
$css->loadSkin("jquery.ui.".$GLOBALS["CONFIG"]["LOGIN_SKIN"]);
_css("login");

$watermark=$GLOBALS["CONFIG"]["WATERMARK"];
$watermark_style=$GLOBALS["CONFIG"]["WATERMARK_STYLE"];
$bg=$GLOBALS["CONFIG"]["BACKGROUND"];
echo "<style>";
if(strlen($bg)>0) echo "html,body {background-image:url(media/cssbg/$bg);}";
echo "#screentable { background: transparent url(".loadMedia($watermark).") $watermark_style; }";
echo "</style>";
?>
<table id=screentable width=100% height=100% style="width:100%;height:100%;">
		<tr><td align=center style='height:45px;'>
			<div id=errormsg style="display:none;"></div>
		</td></tr>
		<tr><td width=100% height=100% style="" align=center>
		<div id=logindiv style="display:none">
			<div id=loginHeader>Login Window</div>
			<div id=loginLogo></div>
			<div id=loginForm>
				<form name="loginForm" method="post" action="services/?scmd=auth" onsubmit="return validateForm();" 
					<?php
						if(getConfig("ALLOW_USERID_AUTOCOMPLETE")=="false") {
							echo "autocomplete='off'";
						}
					?>
					>
					<input name=onsuccess type=hidden value="<?=$relinkPage?>" />
					<input name=onerror type=hidden value="*" />
					<label>Username : </label>
					<input title='Username Please' name=userid type=text class='textfield ui-corner-all ui-status-active ghosttext' onchange="enableLogin();" onKeyUp="enableLogin();" value="" >
					<label>Password : </label>
					<input title='Password Please' name=password type=password class='textfield ui-corner-all ui-status-active ghosttext'  value="" disabled=true>
					<label>Domain : </label>
					<select name=site id=domain class='ui-widget-content ui-corner-all'>
						<?=$site_selector?>
					</select>
					<?php if($GLOBALS["CONFIG"]["ALLOW_PERSISTENT_LOGIN"]=="true") { ?>
						<input name=keepsigned id=keepsigned type=checkbox><label id=keepsignedlabel style='width:200px;font:12px bold Arial;'>Keep Me Signed In</label>
					<?php } ?>
					<hr/>
					<div id=loginbtns align=center>
						<button class=button type="reset" id="Reset" name="Reset" value="Reset" width='100' 
								onclick="$('#Submit, input[name=password]').attr('disabled', 'disabled');$('input[name=userid]').focus();">
							<div id=reseticon></div>Reset</button>
						<button class=button type="submit" id="Submit" name="Submit" value="Login" width='100' disabled=true>
							<div id=loginicon></div>Login</button>
					</div>
					
				</form>
				<div id=pagelinks>
					<?php
						if(getConfig("ALLOW_HOME")=="true") echo "<a class='home' href='index.php?site=$site'>Go Home</a>";
						if(getConfig("ALLOW_REGISTER")=="true") echo "<a class='register' href='index.php?sos=register&site=$site&page=register'>Register !</a>";
						if(getConfig("ALLOW_PASSWORD_RECOVER")=="true") echo "<a class='recover' href='index.php?sos=recover&site=$site&page=pwdrecover'>Recover !</a>";
					?>
				</div>
			</div>
		</div>
		<div style='position:absolute;bottom:10px;right:10px;' align=center>
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
<script language=javascript>
$(function() {
	$("button").addClass("ui-widget-header ui-corner-all");
	//$("button").addClass("ui-state-default");
	$("button").hover(function() {$(this).addClass("ui-state-hover");},function() {$(this).removeClass("ui-state-hover");});
	$("button").mousedown(function() {$(this).addClass("ui-state-active");});
	$("button").mouseup(function() {$(this).removeClass("ui-state-active");});
	//$("button").button();	
	$("#loginExtra a").addClass("ui-state-hover ui-corner-all");
	$("#loginExtra a").hover(function() {$(this).addClass("ui-state-active");},function() {$(this).removeClass("ui-state-active");});
	
	$('input[name=userid]').val("");
	$('input[name=password]').val("");
	$('input[name=userid]').focus();
	
	<?php
		 if(isset($domains)) echo "$('#loginForm').css('height','175px');";
	?>
	$('#logindiv').fadeIn();
	<?php
		if(isset($errormsg)) echo "showError('$errormsg');";
	?>
	
	$(".ghosttext").ghosttext();
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
