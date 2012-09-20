<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["ACCESS"]=array(
		"type"=>"list",
		"values"=>array(
			"private"=>"Private",
			"public"=>"Public",
			)
	);
$cfgSchema["PUBLISH_MODE"]=array(
		"type"=>"list",
		"values"=>array(
			"publish"=>"Published Mode",
			"blocked"=>"Blocked Mode",
			"restricted"=>"Restricted Mode",
			"maintainance"=>"Maintainance Mode",
			"underconstruction"=>"Under Construction Mode",
			)
	);
$cfgSchema["LINGUALIZER_DICTIONARIES"]=array(
		"type"=>"string",
		"tips"=>getSupportedLanguageList(),
		"attrs"=>"multiple",
	);
$cfgSchema["APPS_TYPE"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
		"tips"=>"This changes the way default pages and sub pages are loaded in the app site.",
		/*"type"=>"list",
		"values"=>array(
			"controller"=>"Central Controller Page",
			"cms"=>"CMS Controller Page",
			"website"=>"Website pages within folders",
			"general"=>"General Purpose/Programmer Friendly",
			)*/
	);	
$cfgSchema["ALT_SITE"]=array(
		"type"=>"list",
		"function"=>"getAppList"
	);
$cfgSchema["DEV_MODE_IP"]=array(
		"tips"=>"Comma separated IP Address will load Developer Mode Providing Access Only To These IP.",
	);
$cfgSchema["HOME_PAGE"]=array(
		"type"=>"list",
		"function"=>"getRootPageList"
	);
$cfgSchema["MOBILE_PAGE"]=array(
		"type"=>"list",
		"function"=>"getRootPageList"
	);
$cfgSchema["TABLET_PAGE"]=array(
		"type"=>"list",
		"function"=>"getRootPageList"
	);

function getSupportedLanguageList() {
	$s="eg. ";
	$f=ROOT.LING_FOLDER;
	$fs=scandir($f);
	unset($fs[0]);unset($fs[1]);
	foreach($fs as $a) {
		$a=str_replace(".ling","",$a);
		$s.="$a,";
	}
	return $s;
}
if(!function_exists("getAppList")) {
	function getAppList() {
		$arr=array();
		$f=ROOT.APPS_FOLDER;
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			if(file_exists($f."$b/apps.cfg")) {
				$t=str_replace("_"," ",$b);
				$t=ucwords($t);
				$arr[$t]=$b;
			}
		}
		return $arr;
	}
}
if(!function_exists("getRootPageList")) {
	function getRootPageList() {
		$site="";
		$arr=array();
		if(isset($_REQUEST['forsite'])) {
			$site=$_REQUEST['forsite'];
		} else {
			$site=SITENAME;
		}
		
		$f=ROOT.APPS_FOLDER.$site."/";
		$fs=scandir($f);
		unset($fs[0]);unset($fs[1]);
		
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			$ext=substr($b,strlen($b)-3);
			if(is_file($f.$b) && $ext=="php") {
				$arr[$b]=$b;
			}
		}
		
		return $arr;
	}
}
if(!function_exists("getRootPageList")) {
	function getAppPageList() {
		$site="";
		$arr=array();
		if(isset($_REQUEST['forsite'])) {
			$site=$_REQUEST['forsite'];
		} else {
			$site=SITENAME;
		}
		
		$f=ROOT.APPS_FOLDER.$site."/pages/";
		$fs=scandir($f);
		unset($fs[0]);unset($fs[1]);
		
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			$ext=substr($b,strlen($b)-3);
			if(is_file($f.$b) && $ext=="php") {
				$arr[$b]=$b;
			}
		}
		
		return $arr;
	}
}
?>
