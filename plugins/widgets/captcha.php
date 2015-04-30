<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$w=getConfig("CAPTCHA_WIDTH");
$h=getConfig("CAPTCHA_HEIGHT");
$cid=_randomid();
$captchaLnk=SiteLocation."services/?scmd=captcha&cid={$cid}&w={$w}&h={$h}";
?>
<div>
	<img src='<?=$captchaLnk?>' ondblclick='$(this).attr("src","<?=$captchaLnk?>&q=q1")' width="<?=$w?>" height="<?=$h?>" alt='.' />
	<input class='captchaid' type=hidden name='captchaid' value='<?=$cid?>' />
</div>
