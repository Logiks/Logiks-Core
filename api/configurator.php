<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadConfigs')) {

	function loadConfigs($pathParam) {
		return LogiksConfig::getInstance()->loadConfigs($pathParam);
	}

	//No Compile Yet and loads json configuration on demand.
	function loadJSONConfig($configName,$key=null) {
		return LogiksConfig::getInstance()->loadJSONConfig($configName,$key);
	}

	//Loads all config (cfg) files in the folder.
	function loadConfigDir($dir) {
		if(!file_exists($dir)) return;
		$arr=scandir($dir);
		$arr=array_splice($arr, 2);
		foreach ($arr as $key => $value) {
			if(!is_file($dir.$value)) unset($arr[$key]);
			elseif(substr($value, 0,1)=="~") unset($arr[$key]);
			else $arr[$key]=$dir.$value;
		}
		return LogiksConfig::getInstance()->loadConfigs($arr);
	}

	//For getting output of config system.
	function getConfig($name,$context="/") {
		return LogiksConfig::getInstance()->getConfig($name);
	}
	function setConfig($name, $value, $context="/") {
		return LogiksConfig::getInstance()->setConfig($name,$value);
	}

	//For module level configuration system
	function getFeature($key,$fname) {
		$arrFeature=LogiksConfig::loadFeature($fname);
		if(isset($arrFeature[$key])) {
			return $arrFeature[$key];
		}
		return "";
	}
	function setFeature($key,$value,$fname) {
		if(isset($GLOBALS['FEATURES']["{$fname}.cfg"]) && !$forceReload) {
			$GLOBALS['FEATURES']["{$fname}.cfg"][$key]=$value;
		} elseif(isset($GLOBALS['FEATURES']["{$fname}.json"]) && !$forceReload) {
			$GLOBALS['FEATURES']["{$fname}.json"][$key]=$value;
		} elseif(isset($GLOBALS['FEATURES']["{$fname}.lst"]) && !$forceReload) {
			$GLOBALS['FEATURES']["{$fname}.lst"][$key]=$value;
		} else return false;
		return true;
	}
}
?>
