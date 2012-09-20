<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["THEME_SPECS"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly uppercase",
	);
$cfgSchema["SUBSKIN_SPECS"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly uppercase",
	);
$cfgSchema["APPS_CSS_TYPE"]=array(
		"type"=>"list",
		"values"=>array(
			"Tagged"=>"tagged",
			)
	);
$cfgSchema["APPS_JS_TYPE"]=array(
		"type"=>"list",
		"values"=>array(
			"Tagged"=>"tagged",
			"UnCompressed"=>"uncompressed",
			"Compressed"=>"compressed",
			//"Serialized Cache Link"=>"serializedcachelink",
			//"Serialized Cache Data"=>"serializedcachedata",
			)
	);
$cfgSchema["APPS_THEME"]=array(
		"type"=>"list",
		"function"=>"getThemeList"
	);
$cfgSchema["APPS_SUBSKIN"]=array(
		"type"=>"list",
		"function"=>"getSubSkinList"
	);
$cfgSchema["PAGE_BACKGROUND"]=array(
		"type"=>"list",
		"function"=>"getBackgroundList",
		"tips"=>"The Page Background For The WebApps"
	);
$cfgSchema["DASHBOARD_PAGE"]=array(
		"type"=>"list",
		"function"=>"getPageList",
		"tips"=>"The Page Dashboard Loads On Startup"
	);
$cfgSchema["LANDING_PAGE"]=array(
		"type"=>"list",
		"function"=>"getPageList",
		"tips"=>"The Default Landing Page For Any User"
	);
$cfgSchema["DEFAULT_NAVIGATION"]=array(
		"type"=>"list",
		"function"=>"getNavigationList",
		"tips"=>"The Default Navigation Menu For The Site"
	);
$cfgSchema["LOGO"]=array(
		"type"=>"list",
		"function"=>"getImageList",
		"tips"=>"The Site Logo For Branding"
	);
/*$cfgSchema["CFG_GROUPS"]=array(
		"General UI Settings"=>array("APPS_CSS_TYPE","APPS_JS_TYPE"),
		"Theme Settings"=>array("THEME_SPECS","SERVICE_DEBUG","APPS_THEME","APPS_JQTHEME","ICON_THEME","PAGE_BACKGROUND","LOGO"),
		"Others"=>array()
	);*/
if(!function_exists("getSubSkinList")) {
	function getSubSkinList() {
		$f=ROOT.SKINS_FOLDER."jquery/";
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		$arr["Theme Default"]="";
		foreach($fs as $a=>$b) {
			if(!is_dir($f.$b)) {
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
if(!function_exists("getThemeList")) {
	function getThemeList() {
		//if(!isset($_SESSION["CFG_DATA"]["THEME_SPECS"])) return array();
		$f=ROOT.THEME_FOLDER;
		$thm=strtolower("{$_SESSION["CFG_DATA"]["THEME_SPECS"]}.thm");
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		foreach($fs as $a=>$b) {
			if(file_exists($f.$b."/style.css") && file_exists($f.$b."/$thm")) {
				$arr[ucwords($b)]=$b;
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
if(!function_exists("getPageList")) {
	function getPageList() {
		$site="";
		$arr=array();
		if(isset($_REQUEST['forsite'])) {
			$site=$_REQUEST['forsite'];
		} else {
			$site=SITENAME;
		}
		
		$f=ROOT.APPS_FOLDER.$site."/pages/";
		$f1=ROOT.APPS_FOLDER.$site."/config/layouts/";
		$fs=scandir($f);
		unset($fs[0]);unset($fs[1]);
		
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			$ext=strtolower(substr($b,strlen($b)-3));
			if(is_file($f.$b) && $ext=="php") {
				$t=substr($b,0,strlen($b)-4);
				$arr[$t]=$t;
			}
		}
		
		$fs=scandir($f1);
		unset($fs[0]);unset($fs[1]);
		foreach($fs as $a=>$b) {
			$ext=strtolower(substr($b,strlen($b)-4));
			if(is_file($f1.$b) && $ext=="json") {
				$t=substr($b,0,strlen($b)-5);
				$arr[$t]=$t;
			}
		}
		
		return $arr;
	}
}
if(!function_exists("getImageList")) {
	function getImageList() {
		$site="";
		$arr=array();
		if(isset($_REQUEST['forsite'])) {
			$site=$_REQUEST['forsite'];
		} else {
			$site=SITENAME;
		}
		
		$dirs=array("","logos/");
		
		$arr["None"]="";
		foreach($dirs as $d) {
			$f=ROOT.APPS_FOLDER.$site."/media/".$d;
			if(is_dir($f)) {
				$fs=scandir($f);
				unset($fs[0]);unset($fs[1]);
				foreach($fs as $a) {
					if(is_file($f.$a)) {
						$ext=explode(".",$a);
						$ext=$ext[count($ext)-1];
						$arr[$d.$a]=$d.$a;
					}
				}
			}
		}
		
		return $arr;
	}
}
if(!function_exists("getNavigationList")) {
	function getNavigationList() {
		$arr=array();
		
		$sql="SELECT menuid,count(*) as cnt FROM lgks_admin_links WHERE (SITE='".SITENAME."' OR SITE='*') GROUP BY menuid";
		$result=_dbQuery($sql,true);
		if($result) {
			$data=_dbData($result);
			foreach($data as $a) {
				if(strlen($a['menuid'])>0) {
					$arr[toTitle($a['menuid'])." [{$a['cnt']}]"]=$a['menuid'];
				}
			}
		}
		return $arr;
	}
}
?>
