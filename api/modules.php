<?php
/*
 * This file contains functions for Module Level Operations
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
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
		if(strlen($module)<=0) return false;

		$modulespath=getAllModulesFolders();

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

			$curModule="";
			if(isset($_ENV['CURRENT_MODULE'])) $curModule=$_ENV['CURRENT_MODULE'];
			$_ENV['CURRENT_MODULE']=$module;
			$MODULE_PARAMS=$p;
			runPluginHooks($module,"preload");
			include $fpath;
			runPluginHooks($module,"postload");
			$_ENV['CURRENT_MODULE']=$curModule;

			return true;
		} else {
			if(MASTER_DEBUG_MODE=='true') trigger_error("Module Not Found :: " . $module);
		}
		return false;
	}
	function loadModuleLib($module,$file) {
		$f=checkModule($module);
		if(strlen($f)>0) {
			$f=dirname($f)."/{$file}.php";
			if(file_exists($f)) {
				include $f;
				return true;
			}
		}
		return false;
	}

	function checkModule($module) {
		if(strlen($module)<=0) return false;
		$modulespath=getAllModulesFolders();
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

	function checkService($scmd,$ext="php",$path=false) {
		$cmdArr=array();
		if(!defined("SERVICE_ROOT")) {
			define("SERVICE_ROOT",ROOT.SERVICES_FOLDER);
		}
		$cmdArr=array(
					SERVICE_ROOT.$ext."/".$scmd.".".$ext,
					ROOT.APPS_FOLDER.SITENAME."/services/".$ext."/".$scmd.".".$ext,
					ROOT.APPS_FOLDER.SITENAME."/".APPS_PLUGINS_FOLDER."modules/".$scmd."/service.php",
					ROOT.PLUGINS_FOLDER."modules/".$scmd."/service.php",
					SERVICE_ROOT.$ext."/".SITENAME."/".$scmd.".".$ext,
				);
		foreach($cmdArr as $fl) {
			if(file_exists($fl)) {
				if($path) return $f1;
				else return true;
			}
		}
		if($path) return "";
		else return false;
	}
	function getAllModulesFolders() {
		$paths=array();
		if(!isset($_ENV['MODULES_DIRS'])) {
			if(defined("APPS_PLUGINS_FOLDER")) {
				$p=BASEPATH.APPS_PLUGINS_FOLDER."modules/";
				if(file_exists(ROOT.$p)) {
					if(!in_array($p,$paths)) array_push($paths, $p);
				}
			}
			if(defined("PLUGINS_FOLDER")) {
				$p=PLUGINS_FOLDER."modules/";
				if(file_exists(ROOT.$p)) {
					if(!in_array($p,$paths)) array_push($paths, $p);
				}
			}
			$_ENV['MODULES_DIRS']=$paths;
		} else {
			$paths=$_ENV['MODULES_DIRS'];
		}
		return $paths;
	}
}
?>
