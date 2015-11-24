<?php
//Simulate the Server if not found

if(!defined("LOGIKS_ROOT")) {
	define("LOGIKS_ROOT",dirname(__DIR__)."/devlogiks/");

	$GLOBALS['LOGIKS']["_SERVER"]=array();
	$GLOBALS['LOGIKS']["_SERVER"]['HTTP_HOST']='localhost';
	$GLOBALS['LOGIKS']["_SERVER"]['SERVER_NAME']='localhost';
	$GLOBALS['LOGIKS']["_SERVER"]['SERVER_ADDR']='127.0.0.1';
	$GLOBALS['LOGIKS']["_SERVER"]['SERVER_PORT']='80';
	$GLOBALS['LOGIKS']["_SERVER"]['REMOTE_ADDR']='127.0.0.1';
	$GLOBALS['LOGIKS']["_SERVER"]['DOCUMENT_ROOT']='/srcspace/wwwLogiks';
	$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_SCHEME']='http';
	$GLOBALS['LOGIKS']["_SERVER"]['SCRIPT_FILENAME']='/srcspace/wwwLogiks/devlogiks/index.php';
	$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_METHOD']='GET';
	$GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING']='';
	$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI']='/devlogiks/';
	$GLOBALS['LOGIKS']["_SERVER"]['SCRIPT_NAME']='/devlogiks/index.php';
	$GLOBALS['LOGIKS']["_SERVER"]['PHP_SELF']='/devlogiks/index.php';
	$GLOBALS['LOGIKS']["_SERVER"]['ACTUAL_URI']='/devlogiks/';
	$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PATH']='http://localhost:82/devlogiks/';
	$GLOBALS['LOGIKS']["_SERVER"]['HTTP_USER_AGENT']='mozilla/5.0 (x11; linux i686) applewebkit/537.36 (khtml, like gecko) chrome/46.0.2490.80 safari/537.36';

	if(!class_exists("PHPUnit_Framework_TestCase")) {
		require_once('PHPUnit/Autoload.php');
	}

	include_once dirname(__DIR__)."/api/libs/logikstestcase.php";

	if(!defined("ROOT")) {
		define ('ROOT', dirname(__DIR__) . '/');
	}

	if(!defined("SITENAME")) {
		define ('SITENAME', "default");
	}
	//Start the flow
	require_once ROOT."api/inittest.php";

	//Switching php display error off
	ini_set('display_errors', 'On');

	//Leave out the routing part.
	//This part has to be configured in the setup function of the TestCase
}
?>
