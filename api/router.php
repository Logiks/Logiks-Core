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

  //Load the app.cfg and app Config Folder
  loadLogiksApp(SITENAME);
  loadLogiksBootEngines();

  $security=new LogiksSecurity();
  $security->checkPageRequest();

  $device=getUserDeviceType();

  $routerPage=getConfig("APPS_ROUTER");
  if(strlen($routerPage)<=0) {
    trigger_error("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_USER_ERROR);
  }

  //$routerDir=ROOT.API_FOLDER."libs/routers/";
  //$routerFile="{$routerDir}{$routerPage}.php";

  $routerFiles=array(
      APPROOT."{$routerPage}.php",
      //APPROOT."router.php",
      ROOT.API_FOLDER."libs/routers/{$routerPage}.php"
    );

  $routerLoaded=false;
  foreach ($routerFiles as $rfile) {
    if(file_exists($rfile)) {
      $routerLoaded=true;

      runHooks("startup");

      include_once $rfile;
      
      break;
    } else {
      trigger_logikserror("Page Not Found",E_USER_ERROR,404);
    }
  }
  if(!$routerLoaded) {
    trigger_error("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_USER_ERROR);
  }
}
?>
