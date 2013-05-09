<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

$name="";
$value="";
$default="";
$scope="default";
$etype="user";

if(isset($_REQUEST["name"])) $name=$_REQUEST["name"];
if(isset($_REQUEST["value"])) $value=$_REQUEST["value"];
if(isset($_REQUEST["scope"])) $scope=$_REQUEST["scope"];

if(isset($_REQUEST["etype"])) $type=$_REQUEST["etype"];

if(isset($_REQUEST["default"])) $default=$_REQUEST["default"]; else $default=$value;

if(strtolower($_REQUEST["mod"])=="save") {
	$a=false;
	if($type=="site") $a=setSiteSettings($name,$value);
	else $a=setSettings($name,$value,$scope);
	if($a) {
		exit($value);
	} else {
		exit($default);
	}
} elseif(strtolower($_REQUEST["mod"])=="load") {
	if($type=="site") exit(getSiteSettings($name,$default,$scope));
	else exit(getSettings($name,$default,$scope));
} elseif(strtolower($_REQUEST["mod"])=="create") {
	if(isset($_REQUEST["type"])) $type=$_REQUEST["type"]; else $type="string";
	if(isset($_REQUEST["editParams"])) $editParams=$_REQUEST["editParams"]; else $editParams="";
	$a=registerSettings($name,$value,$scope,$type,$editParams);
	exit();
} elseif(strtolower($_REQUEST["mod"])=="delete") {
	removeSettings($name,$scope);
	exit();
}
exit("Error:Settings Not Found");
?>
