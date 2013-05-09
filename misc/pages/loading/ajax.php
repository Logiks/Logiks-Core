<?php
if(isset($loading_msg)) $msg=$loading_msg;
else $msg="Loading ...";
if(isset($loading_class) && strlen($loading_class)>0) $class=$loading_class;
else $class="ajaxloading5";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Logiks :: Loading</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<?php 
	_css("ajax");
?>
</head>
<body>
<div class='<?=$class?>'><?=$msg?></div>
</body>
</html>



