<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["LOGIN_SELECTOR_SITES"]=array(
		"type"=>"list",
		"values"=>array(
			"All Apps/Sites"=>"all",
			"Requested Apps (Including AdminCP,CMS) Only"=>"ondemand",
			"Requested App  (Including AdminCP,CMS)"=>"restrictive",
			"Requested App  (Excluding AdminCP,CMS)"=>"locked",
			)
	);
$cfgSchema["LOGIN_THEME"]=array(
		"type"=>"list",
		"function"=>"getLGThemeList"
	);
$cfgSchema["LOGIN_SKIN"]=array(
		"type"=>"list",
		"function"=>"getJQSkinList"
	);
$cfgSchema["BACKGROUND"]=array(
		"type"=>"list",
		"function"=>"getBackgroundList"
	);
$cfgSchema["WATERMARK_STYLE"]=array(
		"type"=>"list",
		"values"=>array(
			"Background"=>"repeat",
			"Top-Right"=>"no-repeat top right",
			"Top-Left"=>"no-repeat top left",
			"Bottom-Right"=>"no-repeat bottom right",
			"Bottom-Left"=>"no-repeat bottom left",
			"Center"=>"no-repeat center center",			
			)
	);
$cfgSchema["MOBILITY_BACKGROUND"]=array(
		"type"=>"list",
		"function"=>"getBackgroundList"
	);
$cfgSchema["MOBILITY_PAGE_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["MOBILITY_HEADER_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["MOBILITY_BUTTON_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["CFG_GROUPS"]=array(
		"Mobility"=>array("USE_MOBILITY_LOGIN","MOBILITY_BACKGROUND","MOBILITY_PAGE_THEME","MOBILITY_HEADER_THEME","MOBILITY_BUTTON_THEME"),
		"Text And Links"=>array("ALLOW_REGISTER","ALLOW_PASSWORD_RECOVER","ALLOW_HOME","ALLOW_PERSISTENT_LOGIN","SHOW_COPYRIGHT","SHOW_BROWSER_LOGOS"),
		"Login Page Intractions"=>array("LOCK_CONTEXTMENU","LOCK_SELECTION","LOCK_MOUSEDRAG"),
		"Others"=>array(),
	);
if(!function_exists("getLGThemeList")) {
	function getLGThemeList() {
		$f=ROOT.THEME_FOLDER;
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			if(file_exists($f.$b."/login.css")) {
				$arr[ucwords($b)]=$b;
			}
		}
		return $arr;
	}
}
if(!function_exists("getJQSkinList")) {
	function getJQSkinList() {
		$f=ROOT.SKINS_FOLDER."jquery/";
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			if(is_file($f.$b)) {
				$b=str_replace(".css","",$b);
				$b=str_replace("jquery.ui.","",$b);
				$t=ucwords($b);
				if($b=="jquery.ui") $t="Default";
				$arr[$t]=$b;
			}
		}
		return $arr;
	}
}
if(!function_exists("getBackgroundList")) {
	function getBackgroundList() {
		$f=ROOT.MEDIA_FOLDER."cssbg/";
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			if(is_file($f.$b)) {
				$s=filesize($f.$b);
				if($s>0) $s=round($s/1024, 2);
				$arr[ucwords($b) . " ($s kb)"]=$b;//MEDIA_FOLDER."cssbg/$b";
			}
		}
		return $arr;
	}
}
?>
