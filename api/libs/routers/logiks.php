<?php
/*
 * A more complex router file for routing requests into the app used for logiks cms app.
 * When logiks cms takes over the app control mechanism, this router is used.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 07/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

loadModule("core",true);loadModule(SITENAME,true);

$lt=new LogiksTheme(APPS_THEME,SITENAME);

$lp=new LogiksPage($lt);
$pg=$lp->loadPage(PAGE);
$lp->printPage();
?>
