<?php
/*
 * These functions is used for managing app and its attributes
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("loadLogiksApp")) {

  function loadLogiksApp($appName=SITENAME) {
    if(defined("BASEPATH")) {
      trigger_logikserror("App <b>'".$appName."'</b> has already been activated",E_ERROR);
    }

    define("BASEPATH",APPS_FOLDER . $appName . "/");
    define("APPROOT", ROOT . BASEPATH);
    define("WEBAPPROOT",SiteLocation . BASEPATH);

    if(!file_exists(APPROOT)) {
      trigger_logikserror("Site Not Found <b>'".$appName."'</b>",E_ERROR);
    }

    $apps_cfg=APPROOT."apps.cfg";
    if(!file_exists($apps_cfg)) {
    	trigger_logikserror("Site <b>'".$appName."'</b> Has Not Yet Been Activated (missing apps.cfg).",E_ERROR);
    }
    loadConfigs($apps_cfg,true);

    if(defined("RELINK") && strlen(RELINK)>0) {
      if(substr(RELINK, 0,7)=="http://" || substr(RELINK, 0,8)=="https://") {
        redirectTo(RELINK);
      } else {
        $relink=SiteLocation."?site=".RELINK;
        redirectTo($relink);
      }
    }
    
    if(defined("APPS_TYPE") && strtolower(APPS_TYPE)=="3rdparty") {
    	$relink=WEBAPPROOT;
    	redirectTo($relink);
    }

    loadConfigDir(APPROOT."config/");

    if(!defined("APPS_CONFIG_FOLDER")) {
      loadConfigs(ROOT."config/masters/folders.cfg");
    }

    if(defined("LINGUALIZER_DICTIONARIES")) Lingualizer::getInstance()->loadLocaleFile(LINGUALIZER_DICTIONARIES);

    if(!defined("APPS_THEME")) define("APPS_THEME",getConfig("APPS_THEME"));
    if(!defined("APPS_TEMPLATEENGINE")) define("APPS_TEMPLATEENGINE",getConfig("APPS_TEMPLATEENGINE"));

    if(!defined("APPNAME")) define("APPNAME",SITENAME);

    return true;
  }

  function loadAppServices($appName=SITENAME) {
    if(defined("BASEPATH")) {
      trigger_logikserror("App <b>'".$appName."'</b> has already been activated",E_ERROR);
    }

    define("BASEPATH",APPS_FOLDER . $appName . "/");
    define("APPROOT", ROOT . BASEPATH);
    define("WEBAPPROOT",SiteLocation . BASEPATH);

    if(!file_exists(APPROOT)) {
      trigger_logikserror("Site Not Found <b>'".$appName."'</b>",E_ERROR);
    }

    $apps_cfg=APPROOT."apps.cfg";
    if(!file_exists($apps_cfg)) {
      trigger_logikserror("Site <b>'".$appName."'</b> Has Not Yet Been Activated (missing apps.cfg).",E_ERROR);
    }
    loadConfigs($apps_cfg);

    loadConfigDir(APPROOT."config/");

    if(!defined("APPS_CONFIG_FOLDER")) {
      loadConfigs(ROOT."config/masters/folders.cfg");
    }

    if(defined("LINGUALIZER_DICTIONARIES")) Lingualizer::getInstance()->loadLocaleFile(LINGUALIZER_DICTIONARIES);

    if(!defined("APPS_THEME")) define("APPS_THEME",getConfig("APPS_THEME"));
    if(!defined("APPS_TEMPLATEENGINE")) define("APPS_TEMPLATEENGINE",getConfig("APPS_TEMPLATEENGINE"));

    if(!defined("APPNAME")) define("APPNAME",SITENAME);

    return true;
  }

  function fetchLogiksAppInfo($appName=SITENAME) {
    $cfgPath=ROOT.APPS_FOLDER . $appName . "/apps.cfg";
    if(file_exists($cfgPath)) return LogiksConfig::parseConfigFile($cfgPath);
    return false;
  }

  function configureAppLinking() {
      if(defined("LINKED_APPS")) {
          $parentApps = explode(",",LINKED_APPS);
          if(getConfig("APPS_STATUS")=="production") {
              foreach($parentApps as $app) {
                  $GLOBALS['pluginPaths'][] = "#ROOT#apps/{$app}/plugins/";
                  $GLOBALS['vendorPath'][] = "#ROOT#apps/{$app}/plugins/";
              }
          } else {
              foreach($parentApps as $app) {
                  $GLOBALS['pluginPaths'][] = "#ROOT#apps/{$app}/plugins/";
                  $GLOBALS['pluginPaths'][] = "#ROOT#apps/{$app}/pluginsDev/";

                  $GLOBALS['vendorPath'][] = "#ROOT#apps/{$app}/plugins/";
                  $GLOBALS['vendorPath'][] = "#ROOT#apps/{$app}/pluginsDev/";
              }
          }

          $GLOBALS['mediaPaths'][] = "#ROOT#apps/{$app}/usermedia/";
          $GLOBALS['mediaPaths'][] = "#ROOT#apps/{$app}/userdata/";
      }
  }
}
?>
