<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_module($params, Smarty_Internal_Template $template) {
	$src=explode(".",$params['src']);
	if(count($src)<=1) {
		$GLOBALS['PAGETMPL']=$template->smarty;
		$_ENV['MODULECONFIG'][$params['src']]=$params;
		if(checkModule($params['src'])) {
			return loadModule($params['src']);
		} else {
			return "<div class='errorMsg' align=center><h1>Module '{$params['src']}' Not Found. </h1><citie>Please install it using Package Manager</citie></div>";
		}
	} else {
		$GLOBALS['PAGETMPL']=$template->smarty;
		$_ENV['MODULECONFIG'][$src[0]]=$params;
		loadModuleLib($src[0],$src[1]);
	}
}
?>
