<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_widget($params) {
	return loadWidget($params['src']);
}
?>
