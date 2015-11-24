<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$arr=array();

$arr["TIMESTAMP"]=_timestamp(false);
$arr["SITE"]=SITENAME;
$arr["SERVER"]=_server("HTTP_HOST");
$arr["SERVER SOFTWARE"]=_server("SERVER_SOFTWARE");

//$arr["HTTPS"]=_server("HTTPS");
//printArray($GLOBALS['LOGIKS']["_SERVER"]);

printServiceMsg($arr);
?>
