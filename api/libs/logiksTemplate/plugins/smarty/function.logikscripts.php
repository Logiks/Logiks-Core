<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 * @author		Bismay Kumar Mohaptra
 */

function smarty_function_logikscripts($params) {
	include_once ROOT.API_FOLDER."scripts.php";
	
	$s="";
	$bg=getConfig("PAGE_BACKGROUND");
	if(strlen($bg)>0) {
		if(substr($bg,0,7)=="http://" || substr($bg,0,8)=="https://") {
			$s="background: url($bg) repeat;";
			$s="body { $s }";
		} elseif(file_exists($bg)) {
			$bg=getWebPath($bg);
			$s="background: url($bg) repeat;";
			$s="body { $s }";
		} else {
			$s="background: url(".loadMedia($bg).") repeat;";
			$s="body { $s }";
		}
	}
	if(strlen($s)>0) {
		$s="<style>{$s}</style>";
	}
	echo $s;
}
?>
