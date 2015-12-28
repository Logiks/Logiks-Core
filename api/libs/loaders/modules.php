<?php
/*
 * This file contains functions for Module Level Operations
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadModule')) {
	//Depreciated
	function loadModules($module,$notMandatory=false) {
		loadModule($module,$notMandatory);
	}
	function loadModule($module,$notMandatory=false) {
		if(is_array($module)) {
			foreach($module as $m) loadModule($m,$notMandatory);
		} else {
			if(strlen($module)<=0) return false;

			$fpath=checkModule($module);

			if($fpath && strlen($fpath)>0) {
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
			} else {
				if(MASTER_DEBUG_MODE && !$notMandatory) {
					trigger_logikserror("Module Not Found :: " . $module,E_LOGIKS_ERROR,404);
				}
			}
		}
	}
	function loadModuleLib($module,$file) {
		$f=checkModule($module);
		if(strlen($f)>0) {
			$f=dirname($f)."/{$file}.php";
			if(file_exists($f)) {
				@include $f;
				return true;
			}
		}
		return false;
	}
	function loadModuleComponent($component,$module) {
		$f=checkModule($module);
		if(strlen($f)>0) {
			$ff=[
					dirname($f)."/api.php"=>false,
					dirname($f)."/comps/{$component}.php"=>false
				];
			foreach ($ff as $fx) {
				if(file_exists($fx)) {
					@include $fx;
					$ff[$fx]=true;
				}
			}
			return $ff;
		}
		return false;
	}
	function getPluginFolders() {
		if(!isset($_ENV['MODULE_DIRS'])) {
			$_ENV['MODULE_DIRS']=getLoaderFolders('pluginPaths',"modules");
		}
		return $_ENV['MODULE_DIRS'];
	}

	function checkModule($module) {
		if(strlen($module)<=0) return false;

		$cachePath=_metaCache("MODULES",$module);
		if(!$cachePath || !file_exists($cachePath)) {
			$modulespath=getLoaderFolders('pluginPaths',"modules");
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
				_metaCacheUpdate("MODULES",$module,$fpath);
				return $fpath;
			} else {
				return false;
			}
		} else {
			return $cachePath;
		}
	}
}
?>
