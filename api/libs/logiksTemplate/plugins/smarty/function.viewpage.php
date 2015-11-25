<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_viewpage($params, Smarty_Internal_Template $template) {
	if(isset($_ENV['PAGECONFIG']['viewpage']) && strlen($_ENV['PAGECONFIG']['viewpage'])>0) {
		$viewpage=$_ENV['PAGECONFIG']['viewpage'];
	} else {
		$viewpage=PAGE;
	}
	$page=explode("/",$viewpage);
	if(strlen($page[0])<=0) return;
	$fs=array(
			APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.php"=>"php",
			APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.tpl"=>"tpl",
			APPROOT.APPS_PAGES_FOLDER."viewpage/{$page[0]}.htm"=>"htm",
		);

	foreach ($fs as $f=>$ext) {
		if(file_exists($f)) {
			switch ($ext) {
				case 'php':
					include $f;
				break;
				case 'tpl':
					$vx=$template->tpl_vars;
					$dx=[];
					foreach ($vx as $key => $value) {
						$dx[$key]=$value->value;
					}
					_templatePage($f,$dx);
				break;
				case 'htm':
					readfile($f);
				break;
			}
		}
	}
}
?>
