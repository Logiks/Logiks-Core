<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_component($params) {
	$f1=APPROOT.APPS_PAGES_FOLDER."components/{$params['src']}.php";
	$f2=APPROOT.APPS_PAGES_FOLDER."components/{$params['src']}.tpl";
	$f3=APPROOT.APPS_PAGES_FOLDER."components/{$params['src']}.htm";
	if(file_exists($f1)) {
		include $f1;
	} elseif(file_exists($f2)) {
		_template($f2);
	} elseif(file_exists($f3)) {
		readfile($f3);
	}
}
?>
