<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_pageseometa($params) {
	$pageConfig=getPageConfig('meta');
	if($pageConfig) {
		$html="";
		foreach($pageConfig as $meta) {
			$html.="<meta ".array_implode_associative(" ", "=",$meta)." />\n\t";
		}
		return $html;
	}
}
?>
