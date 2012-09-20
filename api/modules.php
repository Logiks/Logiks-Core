<?php
//This class is used for Loading And Managing Modules
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadModule')) {
	function loadModules($module) {
		if(is_array($module)) {
			foreach($module as $m) loadModule($m);
		} else {
			loadModule($module);
		}
	}
	function loadModule($module) {
		global $js,$css,$ling,$cache,$templates;
		if(strlen($module)<=0) return;
		global $modulespath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."modules/";
			if(file_exists(ROOT.$p)) {
				if(!in_array($p,$modulespath)) array_push($modulespath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."modules/")) {
				if(!in_array(PLUGINS_FOLDER."modules/",$modulespath)) array_push($modulespath, PLUGINS_FOLDER."modules/");
			}
		}
		//printArray($modulespath);
		$fpath="";
		foreach($modulespath as $a) {
			$f1=ROOT . $a . $module . "/index.php";
			if(file_exists($f1)) {
				$fpath=$f1;
				break;
			} else {
				$fpath="";
			}
		}
		if(strlen($fpath)>0) {
			$x=dirname(str_replace(ROOT,"",$fpath))."/";
			
			$p=func_get_args();
			unset($p[0]);
			$MODULE_PARAMS=$p;
			include $fpath;
		} else {
			if(MASTER_DEBUG_MODE=='true') trigger_error("Module Not Found :: " . $module);
		}
	}
	function loadModuleLib($module,$file) {		
		$f=checkModule($module);
		if(strlen($f)>0) {
			$f=dirname($f)."/$file.php";
			if(file_exists($f)) {
				include $f;
				return true;
			}
		}
		return false;
	}
	function loadModuleAPI($module) {
		$f=checkModule($module);
		if(strlen($f)>0) {
			$f=dirname($f)."/api.php";
			if(file_exists($f)) {
				include $f;
				return true;
			}
		}
		return false;
	}
	function checkModule($module) {
		if(strlen($module)<=0) return false;
		global $modulespath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."modules/";
			if(file_exists(ROOT.$p)) {
				array_push($modulespath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."modules/")) {
				if(!in_array(PLUGINS_FOLDER."modules/",$modulespath)) array_push($modulespath, PLUGINS_FOLDER."modules/");
			}
		}
		//printArray($modulespath);
		$fpath="";
		foreach($modulespath as $a) {
			$f1=ROOT . $a . $module . "/index.php";
			$f2=ROOT . $a . $module . ".php";			
			if(file_exists($f1)) {
				//include_once $f1;
				$fpath=$f1;
				break;
			} elseif(file_exists($f2)) {
				//include_once $f2;
				$fpath=$f2;
				break;
			} else {
				$fpath="";
			}
		}
		if(strlen($fpath)>0) {
			return $fpath;
		} else {
			return false;
		}
	}
}
?>
