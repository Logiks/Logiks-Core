<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_pageseometa($params) {
	if(!isset($_ENV['PAGECONFIG']['meta'])) return "";
	
	$pageConfig=$_ENV['PAGECONFIG']['meta'];
	if($pageConfig && is_array($pageConfig)) {
		$html="";
		foreach($pageConfig as $meta) {
			$html.="<meta ".array_implode_associative(" ", "=",$meta)." />\n\t";
		}
		return $html;
	}
	return "";
}
?>
