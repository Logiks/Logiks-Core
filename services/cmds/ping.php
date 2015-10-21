<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$arr=array();

$arr["TIMESTAMP"]=_timestamp(false);
$arr["SITE"]=SITENAME;
$arr["SERVER"]=$GLOBALS['LOGIKS']["_SERVER"]["HTTP_HOST"];
$arr["SERVER SOFTWARE"]=$GLOBALS['LOGIKS']["_SERVER"]["SERVER_SOFTWARE"];

//$arr["HTTPS"]=$GLOBALS['LOGIKS']["_SERVER"]["HTTPS"];
//printArray($GLOBALS['LOGIKS']["_SERVER"]);

printServiceMsg($arr);
?>
