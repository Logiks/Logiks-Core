<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_widget($params, Smarty_Internal_Template $template) {
	$GLOBALS['PAGETMPL']=$template->smarty;
	return loadWidget($params['src'],$params);
}
?>
