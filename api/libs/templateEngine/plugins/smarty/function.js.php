<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_js($params) {
	return _jsLink($params['src']);
}
?>
