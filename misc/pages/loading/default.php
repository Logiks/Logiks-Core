<?php
if(isset($loading_msg)) $msg=$loading_msg;
else $msg="Loading ...";
if(isset($loading_icon) && strlen($loading_icon)>0) $icon=$loading_icon;
else $icon=SiteLocation."misc/themes/default/images/ajax/loading.gif";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Logiks :: Loading</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php 
	$css->display();
?>
</head>
<body>
<div style='color:#888;font:bold 15px Georgia;'><?=$msg?></div>
<div style='width:300px' align=center><img src='<?=$icon?>' alt=''></div>
</body>
</html>



