<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$autload_modules=getConfig("POSTLOAD_MODULES");
$autload_modules=explode(",",$autload_modules);
foreach($autload_modules as $module) {
	loadModule($module);
}
?>
