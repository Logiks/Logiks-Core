<?php
//include ROOT . "api/jsphp.inc";
if (!defined('ROOT')) exit('No direct script access allowed');

$JSAPI_LINK=getRequestPath() . "?&jslib=";// . $_SERVER['QUERY_STRING'] . "&jslib="

$js=JsPHP::singleton();

if(isset($_REQUEST['jslib'])) {
	$jslib=$_REQUEST['jslib'];
	$vers=$_REQUEST['vers'];
	$b=$js->loadJS($jslib, $vers);
	
	if($b) {
		header('Content-type: text/javascript');
		//$js->TypeOfDispatch("SerializedCacheData");
		$js->TypeOfDispatch("uncompressed");
		$js->display(false);
	} else {
		header('Content-type: text/javascript');
		if(strlen($vers)>0) echo("//Script Library Not Found :: " . $jslib . "-" . $vers);
		else  echo("//Script Library Not Found :: " . $jslib);
		exit();
	}
} else {
	header('Content-type: text/javascript');
	//echo file_get_contents($JS_LINK . "jsapi.js");
	echo file_get_contents($GLOBALS['JS_LINK'] . "jsapi.js");
}
?>
