<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["DEFAULT_SITE"]=array(
		"type"=>"list",
		"function"=>"getAppList"
	);
if(!function_exists("getAppList")) {
	function getAppList() {
		$arr=array();
		$f=ROOT.APPS_FOLDER;
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			if(file_exists($f."$b/apps.cfg")) {
				$t=str_replace("_"," ",$b);
				$t=ucwords($t);
				$arr[$t]=$b;
			}
		}
		return $arr;
	}
}
?>
