<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_module($params, Smarty_Internal_Template $template) {
	$GLOBALS['PAGETMPL']=$template->smarty;
	return loadModule($params['src']);
}
?>
