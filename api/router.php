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
  startLogiksApp(SITENAME);

  $security=new LogiksSecurity();
  $security->checkPageRequest();

  $device=getUserDeviceType();

  $routerPage=getConfig("APPS_ROUTER");
  if(strlen($routerPage)<=0) {
    trigger_error("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_USER_ERROR);
  }

  $routerDir=ROOT.API_FOLDER."libs/routers/";

  $routerFile="{$routerDir}{$routerPage}.php";

  if(file_exists($routerFile)) {
    include_once $routerFile;
  } elseif(is_file(APPROOT.$routerPage)) {
    include_once APPROOT.$routerPage;
  } elseif(is_file(ROOT.$routerPage)) {
    include_once ROOT.$routerPage;
  } else {
    trigger_error("Site <b>'".SITENAME."'</b> Does Not Have ROUTER Defined.",E_USER_ERROR);
  }
}
?>
