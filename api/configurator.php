<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadConfigs')) {

	function loadConfigs($pathParam,$reload=false) {
		return LogiksConfig::getInstance()->loadConfigs($pathParam,$reload);
	}

	//No Compile Yet and loads json configuration on demand.
	function loadJSONConfig($configName,$keyName=null,$forceReload=null) {
		if($forceReload==null) $forceReload=MASTER_DEBUG_MODE;
		$cfg=LogiksConfig::getInstance()->loadJSONConfig($configName,$forceReload);
		if($keyName==null) return $cfg;
		else {
			if(isset($cfg[$keyName])) return $cfg[$keyName];
			return false;
		}
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
	function getConfig($name,$defaultValue = false) {
		$val = LogiksConfig::getInstance()->getConfig($name);
		if($val===false) return $defaultValue;
		return $val;
	}
	function setConfig($name, $value) {
		return LogiksConfig::getInstance()->setConfig($name,$value);
	}

	//For module level configuration system
	function getFeature($key,$fname) {
		$arrFeature=LogiksConfig::loadFeature($fname);
		if(isset($arrFeature[$key])) {
			return $arrFeature[$key];
		} elseif(isset($arrFeature["CONFIG-{$key}"])) {
			return $arrFeature["CONFIG-{$key}"];
		} elseif(isset($arrFeature["DEFINE-{$key}"])) {
			return $arrFeature["DEFINE-{$key}"];
		} elseif(isset($arrFeature["ENV-{$key}"])) {
			return $arrFeature["ENV-{$key}"];
		}
		return "";
	}
	function setFeature($key,$value,$fname) {
    	$key = "CONFIG-{$key}";
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

if(!function_exists("getUserConfig")) {

    //Returns the App Configuration for the scope
    function getAppConfig($configKey, $scope = "system", $reset=false) {
        $configKey=strtolower($configKey);
        $configKeyArr = explode("-", $configKey);
        if($reset) {
            if(isset($_SESSION['USERCONFIG'][$configKey])) {
                unset($_SESSION['USERCONFIG'][$configKey]);
            }
        }
        if(isset($_SESSION['USERCONFIG']) && isset($_SESSION['USERCONFIG'][$configKey])) {
            return $_SESSION['USERCONFIG'][$configKey];
        }

        $configData=getSettings($configKey, "", $scope);
        if(strlen($configData)>2) {
            $_SESSION['USERCONFIG'][$configKey]=json_decode($configData,true);

            return $_SESSION['USERCONFIG'][$configKey];
        }
        // if($baseFolder==null) {
        //     $bt =  debug_backtrace();
        //     $baseFolder=dirname($bt[0]['file'])."/";
        // }
        $configArr=[
                APPROOT.APPS_CONFIG_FOLDER."jsonData/{$configKeyArr[0]}/{$_SESSION['SESS_PRIVILEGE_NAME']}.json",
                APPROOT.APPS_CONFIG_FOLDER."jsonData/{$configKeyArr[0]}.json",
                APPROOT.APPS_CONFIG_FOLDER."jsonData/{$configKey}.json",
                //$baseFolder."config.json",
            ];
        foreach ($configArr as $f) {
            if(file_exists($f)) {
                $configData=file_get_contents($f);
                $_SESSION['USERCONFIG'][$configKey]=json_decode($configData,true);
                setSettings($configKey,$configData, $scope);
                return $_SESSION['USERCONFIG'][$configKey];
            }
        }
        return false;
    }
    function setAppConfig($configKey,$configData,$scope = "system") {
        $configKey=strtolower($configKey);
        $_SESSION['USERCONFIG'][$configKey]=$configData;
        setSettings($configKey,json_encode($configData), $scope);
        return $_SESSION['USERCONFIG'][$configKey];
    }
}
?>
