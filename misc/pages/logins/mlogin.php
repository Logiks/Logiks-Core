<?php
if (!defined('ROOT')) exit('No direct script access allowed');
	_js(array("mobile.jquery-1.4.4.min","mobile.jquery.mobile-1.0a2.min","ajax"));
	_css(array("jquery.mobile-1.0a2","mobile-login"));//,"mobile","handheld"
	
	$theme_page=getConfig("MOBILITY_PAGE_THEME");
	$theme_header=getConfig("MOBILITY_HEADER_THEME");
	$theme_footer=getConfig("MOBILITY_HEADER_THEME");
	$theme_button=getConfig("MOBILITY_BUTTON_THEME");

	$watermark=$GLOBALS["CONFIG"]["WATERMARK"];
	$watermark_style=$GLOBALS["CONFIG"]["WATERMARK_STYLE"];
	$bg=$GLOBALS["CONFIG"]["MOBILITY_BACKGROUND"];
	echo "<style>";
	if(strlen($bg)>0) echo ".ui-page {background-image:url(media/cssbg/$bg);}";
	echo "</style>";
?>
<div data-role='page' data-theme='<?=$theme_page?>'>
	<div data-role='header' data-theme='<?=$theme_header?>' data-nobackbtn="true">
		<img src='media/icons/<?=$device?>.png' width=25px height=25px alt='' style='float:left;margin-right:5px;margin-top:5px;' />
		<h1>Login Window</h1>
	</div><!-- /header -->
	<div id='homeheader' align=center>
        <img src='<?=$watermark?>' width=100px height=80px alt=''/>
	</div><!-- /homeheader -->
	<div id=errormsg style='display:none;'></div>
	<div data-role='content' data-inset="true" style='padding-top:0px;padding-bottom:0px;'>
        <form name='loginForm' method='post' action='services/?scmd=auth'>
			<input name=onsuccess type=hidden value="<?=$relinkPage?>" />
			<input name=onerror type=hidden value="*" />
			<fieldset>
				<label for='userid'>Username:</label>
                <input type='text' name='userid' id='userid' value='' style="width:98%;"/>
                
                <label for='password'>Password:</label>
                <input type='password' name='password' id='password' value='' style="width:98%;" />
                
                <label for='domain'>Domain:</label>
                <select name=site id=domain>
					<?=$site_selector?>
                </select>
				<br/> <hr/>
				<div class='submit-wrapper' align=center data-role="controlgroup" data-type="horizontal">
					<button type='reset' data-inline="true" data-theme='<?=$theme_button?>' data-icon="delete">Reset</button>
					<button id=submitbtn type='button' data-inline="true" data-theme='<?=$theme_button?>' data-icon="check" rel='external' onclick="formSubmit();">Login</button>
				</div>
			</fieldset>
        </form>
       
        <div id=pagelinks style='margin-bottom:5px;'>
			<?php
				if(getConfig("ALLOW_HOME")=="true") echo "<a class='home' href='index.php?site=$site'>Go Home</a>";
				if(getConfig("ALLOW_REGISTER")=="true") echo "<a class='register' href='index.php?sos=register&site=$site&page=register'>Register !</a>";
				if(getConfig("ALLOW_PASSWORD_RECOVER")=="true") echo "<a class='recover' href='index.php?sos=recover&site=$site&page=pwdrecover'>Recover !</a>";
			?>
		</div>
	</div>
	<?php
		if($GLOBALS["CONFIG"]["SHOW_COPYRIGHT"]=="true") {
			echo "<div id=loginFooter data-role='content' align=center data-theme='$theme_footer'>";
			if(defined("APPS_COPYRIGHT")) {
				echo "<div>".APPS_COPYRIGHT."</div>";
			} else {
				echo "<div>".Framework_Copyright."</div>";
			}
			echo "</div>";
		}
	?>
	<!-- /pagefooter -->
	</div>
<script language=javascript>
$(function() {
	$(".ui-select").css("width","100%");	
	<?php
		if(isset($errormsg)) echo "showError('$errormsg');";
	?>
	$("input[name=password]").keydown(function(e) {
			if(e.keyCode==13) {
				formSubmit();
			}
		});
});
function formSubmit() {
	 if($("input[name=userid]").val().length<=0) { alert("UserId Field CanNot Be Blank."); return; }
	 if($("input[name=password]").val().length<=0) { alert("Password Field CanNot Be Blank."); return; }
	 if($("select[name=site]").val().length<=0) { alert("Where Do You Want To Login."); return; }
	 
	 a='services/?scmd=auth';
	 q="&userid="+$("input[name=userid]").val()+"&password="+$("input[name=password]").val()+"&site="+$("select[name=site]").val();
	 $("input[name=password]").val("");
	 $.ajax({
		  type: 'POST',
		  url: a,
		  data: q,
		  success: function(data, textStatus, jqXHR) {
				document.location="index.php?site="+$("select[name=site]").val();
			},
		});
}
function showError(msg) {
	$('#errormsg').hide();
	$('#errormsg').html(msg);
	$('#errormsg').fadeIn('slow').delay(1000).fadeOut('slow');
}
</script>
