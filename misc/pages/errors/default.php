<?php
global $css;

$msg_header=$errorParams["msg_header"];
$msg_body=$errorParams["msg_body"];
$msg_tips=$errorParams["msg_tips"];
$posted_on=$errorParams["posted_on"];
$posted_by=$errorParams["posted_by"];
		
$css->TypeOfDispatch("Tagged");
//$js->TypeOfDispatch("SerializedCacheData");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?=getConfig("APPS_NAME")?> :: ERROR</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php
	_js(array("jquery"));
	_css(array("error"));
	
	$css->loadCSS("errorprint","*","","print");
	$css->display();
?>
<?=getUserPageStyle();?>
</head>
<body>
<div id="headermessage">
	<h3><?php if(defined("WEBMASTER_EMAIL")) echo WEBMASTER_EMAIL; ?></h3>
</div>
<div id="canvas">
	<div id="medal"></div>
	<div id="contexthead">
		<div class='headericon'></div>		
		<div class='headermsg'>
			<div class="f1"><?=$msg_header?></div>
			<div class="f2">Source: <span class='posted_by'><?=$posted_by?></span>, 
				<span class='posted_on'><?=$posted_on?></span>
			</div>			
		</div>
	</div>
	<div id="context" style="background-image: url(<?=getErrorIcon($errorParams["errorType"])?>);">
		<div class='spareimg'></div>
		<div class='txtholder'>
			<?=$msg_body?>
			<div align=center title='Goto Home Page'><a href='<?=SiteLocation?>index.php' ><img src="<?=loadMedia("images/home.png")?>" /></a></div>
		</div>		
	</div>
	<div id="footer" align="center">
		All rights reserved, © <?=getConfig("APPS_COPYRIGHT_YEAR")?> - <a href="<?=getConfig("APPS_COMPANY_SITE")?>" target="_blank"><?=getConfig("APPS_COMPANY")?></a>
	</div>
</div>
<div id="footermessage"><?=getConfig("APPS_NAME")?> v<?=getConfig("APPS_VERS")?></div>
<?php 
	if(MASTER_DEBUG_MODE=='true') {
		printTrace();
	}
?>

<div id=onlyprint align=center>
	<h1 class=header>Error : <?=$msg_header?></h1>
	<br/><br/>
	<p align=justify style='width:60%;margin:auto;'>
		<?=$msg_body?>
	</p>
	<br/>
	<div align=center style='background:#ccc;border:2px solid #aaa;border-left:0px;border-right:0px;'><h3 style='margin:0px;'>Trace</h3></div>
	<div align=left>
		<pre style='font-size:12px;'>
			<?php 
				if(MASTER_DEBUG_MODE=='true') {
					printTrace();
				}
			?>
		</pre>
	</div>
</div>

</body>
</html>
