<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_content($params) {
	$f=APPROOT.APPS_PAGES_FOLDER."contents/{$params['src']}.htm";
	if(file_exists($f)) {
		return file_get_contents($f);
	}
	return "";
}
?>
