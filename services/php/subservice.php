<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["mod"])) {
	$fp=checkModule($_REQUEST["mod"]);
	if($fp && strlen($fp)>0) {
		if(isset($_REQUEST["sub"])) {
			loadModuleLib($_REQUEST["mod"],$_REQUEST["sub"]);
		} else {
			loadModule($_REQUEST["mod"]);
		}
	} else {
		echo "<h3>Error Finding Module</h3>";
	}
} else {
	echo "<h3>No Module Mentioned</h3>";
}
?>
