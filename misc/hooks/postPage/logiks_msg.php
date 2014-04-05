<?php 
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

if(isset($_SESSION['LGKS_MSG'])) {
	echo "<script language='javascript'>\n$(function() {\n";
	echo "if(typeof jqPopupData=='function') lgksAlert('{$_SESSION['LGKS_MSG']}');\n";
	echo "else if(typeof jqPopupData=='function') jqPopupData('{$_SESSION['LGKS_MSG']}');\n";
	echo "else alert('{$_SESSION['LGKS_MSG']}');\n";
	echo "});\n</script>";
	unset($_SESSION['LGKS_MSG']);
}
?>
