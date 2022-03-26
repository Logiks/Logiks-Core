<?php
/*
 * This file contains the Request Routing logic for Logiks Framework.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.1
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!defined("BASEPATH")) {
  
  if(isset($_GET['site']) && $_GET['site']!=SITENAME) {
    trigger_logikserror("Site <b>'{$_GET['site']}'</b> does not exist.",E_ERROR);
  }

  include_once ROOT.API_FOLDER."libs/routers/boot.php";

  //Load the app.cfg and app Config Folder
  loadLogiksApp(SITENAME);

  logiksRequestBoot();

  loadLogiksBootEngines();

  $security=new LogiksSecurity();
  //Global Appsite Access Controls : this ensures prilimanary user access validation : only checks if logged in or not
  $security->checkPageRequest();
  
  $device=getUserDeviceType();

  $routerPage=getConfig("APPS_ROUTER");
  if(strlen($routerPage)<=0) {
    trigger_logikserror("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_ERROR);
  }

  $routerFiles=array(
      APPROOT."{$routerPage}.php",
      ROOT.API_FOLDER."libs/routers/{$routerPage}.php"
    );

  $routerLoaded=false;
  foreach ($routerFiles as $rfile) {
    if(file_exists($rfile)) {
      $routerLoaded=true;

      runHooks("startup");

      configureAppLinking();

      include_once $rfile;
      
      break;
    }
  }
  if(!$routerLoaded) {
    trigger_logikserror("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_ERROR);
  }
}
?>
