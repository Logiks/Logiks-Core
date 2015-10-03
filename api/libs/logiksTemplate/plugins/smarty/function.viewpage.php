<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_viewpage($params) {
	$page=explode("/",PAGE);
	if(strlen($page[0])<=0) return;
	$f1=APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.php";
	$f2=APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.tpl";
	$f3=APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.htm";
	if(file_exists($f1)) {
		include $f1;
	} elseif(file_exists($f2)) {
		_template($f2);
	} elseif(file_exists($f3)) {
		readfile($f3);
	}
}
?>
