<?php
//Simulate the Server if not found

define("LOGIKS_ROOT",dirname(__DIR__)."/devlogiks/");

if(isset($_SERVER) && count($_SERVER)>0) {
	$GLOBALS['LOGIKS']["_SERVER"]=$_SERVER;
} else {
	$GLOBALS['LOGIKS']["_SERVER"]=array();

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
}

if(!class_exists("PHPUnit_Framework_TestCase")) {
	require_once('PHPUnit/Autoload.php');
}

include_once dirname(__DIR__)."/api/libs/LogiksTestCase.php";

?>
