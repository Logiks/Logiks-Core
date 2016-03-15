<?php
/*
 * A more complex router file for routing requests into the app used for logiks cms app.
 * When logiks cms takes over the app control mechanism, this router is used.
 * Good for avid Logiks Developers, who want to user LogiksPages System and cms based development
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 07/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

$lt=new LogiksTheme(APPS_THEME,SITENAME);

$lp=new LogiksPage($lt);
$pg=$lp->loadPage(PAGE);
if($pg) {
	$lp->printPage();
} else {
	trigger_logikserror("Sorry, '".PAGE."' page not found",E_LOGIKS_ERROR,404);
}
?>
