<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

include "logging.php";

$cfgSchema["DEFAULT_THEME"]=array(
		"type"=>"list",
		"function"=>"getThemeList",
	);

$cfgSchema["DEFAULT_TIMEZONE"]=array(
		"type"=>"list",
		"function"=>"getTimeZones"
	);
$cfgSchema["DEFAULT_COUNTRY"]=array(
		"type"=>"list",
		"function"=>"getCountries"
	);
$cfgSchema["DEFAULT_LOCALE"]=array(
		"type"=>"list",
		"function"=>"getLocales"
	);

$cfgSchema["LOADER_LAYOUT"]=array(
		"type"=>"list",
		"function"=>"getLoaderLayouts"
	);

$cfgSchema["CSS_DISP_TYPE"]=array(
		"type"=>"list",
		"values"=>array(
			"Tagged"=>"tagged",
			)
	);
$cfgSchema["JS_DISP_TYPE"]=array(
		"type"=>"list",
		"values"=>array(
			"Tagged"=>"tagged",
			"UnCompressed"=>"uncompressed",
			"Compressed"=>"compressed",
			//"Serialized Cache Link"=>"serializedcachelink",
			//"Serialized Cache Data"=>"serializedcachedata",
			)
	);
$cfgSchema["TEMPLATE_CACHE_ON_DISPLAY"]=array(
		"type"=>"list",
		"values"=>array(
			"Work With Temp Data"=>"0",
			"Replace The Old Data In Cache"=>"1",
			"Use Old Data"=>"2",
			)
	);
$cfgSchema["TEMPLATE_EXPIRY"]=array(
		"tips"=>"(Secs) Time after which the templates are automatically recreated.",
	);
$cfgSchema["CACHE_EXPIRY"]=array(
		"tips"=>"(secs) Seconds after which Cache Expires.",
	);
$cfgSchema["GENERATED_PERMALINK_STYLE"]=array(
		"type"=>"list",
		"function"=>"getPrettyPageLinkStyles",
	);
$cfgSchema["TIMESTAMP_FORMAT"]=array(
		"tips"=>"You may use PHP Date Format Keys. For Details Refer Documentation.",
	);
$cfgSchema["DATE_YEAR_RANGE"]=array(
		"tips"=>"(years) Plus Minus Years To Be Displayed In Forms, etc.",
	);
$cfgSchema["MAX_UPLOAD_FILE_SIZE"]=array(
		"tips"=>"(bytes) Maximum size of file that can be uploaded.",
	);
$cfgSchema["ERROR_REDIRECTION_LEVEL"]=array(
		"tips"=>"Depth At Which Top Frame exists OR Top Frame ID.",
	);
if(!function_exists("getThemeList")) {
	function getThemeList() {
		$f=ROOT.THEME_FOLDER;
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			if(file_exists($f.$b."/style.css")) {
				$arr[ucwords($b)]=$b;
			}
		}
		return $arr;
	}
}
if(!function_exists("getEncodings")) {
	function getEncodings() {
			$o=array();
			include_once ROOT."config/encodings.php";
			$arr=getEncodingList();
			foreach($arr as $a=>$b) {
				$o[$b]=$b;
			}
			return $o;
	}
}
if(!function_exists("getCountries")) {
	function getCountries() {
		include_once ROOT.API_FOLDER."helpers/countries.php";
		$arr=getCountryList();
		foreach($arr as $a=>$b) {
			unset($arr[$a]);
			$arr[$b]=$b;
		}
		return $arr;
	}
}
if(!function_exists("getLocales")) {
	function getLocales() {
		include_once ROOT.API_FOLDER."helpers/countries.php";
		$arr=array();
		$o=getLocaleList();
		foreach($o as $a=>$b) {
			foreach($b as $e=>$r) {
				$arr[$r]=strtolower($a);
			}
		}
		return $arr;
	}
}
if(!function_exists("getLoaderLayouts")) {
	function getLoaderLayouts() {
		$arr=array();
		$f=ROOT.PAGES_FOLDER."loading/";
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			$arr[$b]="loading/".$b;
		}
		return $arr;
	}
}
if(!function_exists("getPrettyPageLinkStyles")) {
	loadHelpers("prettylink");
}
?>
