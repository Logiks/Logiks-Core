<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_component($params, Smarty_Internal_Template $template) {
	$GLOBALS['PAGETMPL']=$template->smarty;
	loadSnippets(explode(",",$params['src']));
}
?>
