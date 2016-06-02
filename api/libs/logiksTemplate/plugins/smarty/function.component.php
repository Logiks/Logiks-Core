<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_component($params, Smarty_Internal_Template $template) {
	//loadComponent($params['src']);
	$component=$params['src'];
	$fs=[
			APPROOT.APPS_PAGES_FOLDER."comps/{$component}.php"=>"php",
			APPROOT.APPS_PAGES_FOLDER."comps/{$component}.tpl"=>"tpl",
			APPROOT.APPS_PAGES_FOLDER."comps/{$component}.htm"=>"htm",
		];
	foreach($fs as $f=>$ext) {
		if(file_exists($f)) {
			switch($ext) {
				case "tpl":
					$vx=$template->tpl_vars;
					$dx=[];
					foreach ($vx as $key => $value) {
						$dx[$key]=$value->value;
						$template->smarty->assign($key,$value->value);
					}
					$template->smarty->assign("component",$f);
					$template->smarty->display($f);
				break;
				case "php":
					$GLOBALS['PAGETMPL']=$template->smarty;
					include $f;
				break;
				case "htm":
					readfile($f);
				break;
			}
		}
	}
}
?>
