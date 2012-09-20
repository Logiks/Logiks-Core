<?php
if (!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["goto"])) {
	if(!defined("marketURL")) {
		LoadConfigFile(ROOT . "config/olks.cfg");
	}
	$x=$_REQUEST["goto"];
	if(defined($x)) {
		$x=constant($x);
		header("Location:$x");
	} else {
		echo "<style>body {overflow:hidden;}</style>";
		dispErrMessage("The Requested OpenLogiks Link Does Not Exist...","Link Not Supported",412);
	}
} else {
	echo "<style>body {overflow:hidden;}</style>";
	dispErrMessage("Link Not Specified...","Link Not Specified",400);
}
?>
