<?php
/*
 * The Service Engine is the base for all the Logiks based REST communications and ajax calls.
 * It provides the basic architecture for all the remote command extecutions.
 * Logiks Service Engine (LSE) provides multiple developement language support including
 *				php, py, perl, ruby, js (node) via engines
 *
 *
 *
 * Service Handler For Logiks 4.0+
 * Commands : scmd, action, format
 * Output Formats : table,list,select, json, xml, raw, txt, css,js
 * Special Parameters
 * 	autoformat 		Use toTitle/UCWORDS or not
 *	debug 			Enable debug mode or not
 *	cache 			To use cache or not
 *	stype 			Type of command (py, php, etc.)
 */
if(defined('ROOT')) exit('Only Direct Access Is Allowed');

define('ROOT',dirname(dirname(__FILE__)) . '/');

require_once (ROOT . 'services/initialize.php');



if(!isset($_REQUEST['scmd'])) {
	trigger_logikserror(901, E_USER_ERROR);
	exit();
}

loadAppServices(SITENAME);
loadLogiksBootEngines();

//Check blacklists, bots, and others
$security=new LogiksSecurity();
$security->checkServiceRequest();

runHooks("serviceStart");

$ctrl=new ServiceController();
//loads the parameters into the service controller
$ctrl->setupRequest($_REQUEST);
//access_control, privilege_model, APIKEY check
if($ctrl->checkRequest()) {
	//checks cache and if required executes the scmd and prints the output
	$ctrl->executeRequest();
} else {
	trigger_logikserror(905, E_USER_ERROR);
}

runHooks("serviceStop");
?>
