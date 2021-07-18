<?php
/*
 * This class contains the site wide settings related functionlities.
 * It allows us to load a json file as default fallback if the user settings does not exit.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getSiteSettings")) {
    //Returns the Site Settings
    function getSiteSettings($configKey, $defaultValue = "", $reset = true) {
        $configKey=strtolower($configKey);
        $configKeyArr = explode("-", $configKey);
        if($reset) {
            if(isset($_SESSION['SITECONFIG'][$configKey])) {
                unset($_SESSION['SITECONFIG'][$configKey]);
            }
        }
        if(isset($_SESSION['SITECONFIG']) && isset($_SESSION['SITECONFIG'][$configKey])) {
            return $_SESSION['SITECONFIG'][$configKey];
        }

        $_SESSION['SITECONFIG'][$configKey] = getSettings($configKey, $defaultValue, "appsite", true);

        return $_SESSION['SITECONFIG'][$configKey];
    }

    function setSiteSettings($configKey,$configData) {
        $configKey=strtolower($configKey);
        $_SESSION['SITECONFIG'][$configKey]=$configData;
        setSettings($configKey,$configData,"appsite",true);
        return $_SESSION['SITECONFIG'][$configKey];
    }
}
?>