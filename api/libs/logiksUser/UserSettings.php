<?php
/*
 * This class contains the user settings related functionlities.
 * It allows us to load a json file as default fallback if the user settings does not exit.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getUserConfig")) {

    //Returns the User Configuration for the scope
    function getUserConfig($configKey,$scope = "system", $reset=false) {
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
    function setUserConfig($configKey,$configData,$scope = "system") {
        $configKey=strtolower($configKey);
        $_SESSION['USERCONFIG'][$configKey]=$configData;
        setSettings($configKey,json_encode($configData), $scope);
        return $_SESSION['USERCONFIG'][$configKey];
    }
}
?>