<?php
//This class is used for managing and using loaders.
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "helpers.php";
include_once "modules.php";
include_once "widgets.php";

if(!function_exists('loadMedia')) {
	function loadTheme($theme) {
		global $css;
		$css->loadTheme($theme);
	}
	function loadAllMedia($media,$relativeOnly=false) {
		if(strlen($media)<=0) return "";
		$linkedApps=getConfig("LINKED_APPS");
		if(strlen($linkedApps)<=0) return "";
		$linkedApps=explode(",",$linkedApps);
		
		global $mediaPaths;
		if(count($mediaPaths)<=0)
			$mediaPaths=array("userdata/","media/","");
		
		foreach($linkedApps as $app) {
			$appDir=APPS_FOLDER.$app."/";
			if(is_dir(ROOT.$appDir)) {
				foreach($mediaPaths as $mp) {
					$f=$appDir.$mp.$media;
					if(file_exists(ROOT.$f)) {
						if(getConfig("FULL_MEDIA_PATH")=="true" && !$relativeOnly)
							return SiteLocation.$f;
						else
							return $f;
					}
				}
				
			}
		}
		
		return loadMedia($media,$relativeOnly);
	}
	function loadMedia($name,$relativeOnly=false) {
		if(strlen($name)<=0) return "";
		$paths=array();
		
		if(!in_array(BASEPATH . $name,$paths) && file_exists(ROOT.BASEPATH . $name)) 
				array_push($paths,BASEPATH . $name);
		if(defined("APPS_USERDATA")) {
			if(!in_array(BASEPATH . APPS_USERDATA . $name,$paths) && file_exists(ROOT.BASEPATH . APPS_USERDATA . $name)) 
				array_push($paths,BASEPATH . APPS_USERDATA . $name);
		}
		if(defined("APPS_MEDIA_FOLDER")) {
			if(!in_array(BASEPATH . APPS_MEDIA_FOLDER . $name,$paths) && file_exists(ROOT.BASEPATH . APPS_MEDIA_FOLDER . $name)) 
				array_push($paths,BASEPATH . APPS_MEDIA_FOLDER . $name);
		}
		if(defined("APPS_THEME")) {
			if(!in_array(THEME_FOLDER.APPS_THEME."/".$name,$paths) && file_exists(ROOT.THEME_FOLDER.APPS_THEME."/".$name)) 
				array_push($paths,THEME_FOLDER.APPS_THEME."/".$name);
		}
		if(!in_array(MEDIA_FOLDER . $name,$paths) && file_exists(ROOT.MEDIA_FOLDER . $name)) 
			array_push($paths,MEDIA_FOLDER . $name);		
		if(defined("SITENAME")) {
			if(!in_array(MEDIA_FOLDER . SITENAME . "/" . $name,$paths) && file_exists(ROOT.MEDIA_FOLDER . SITENAME . "/" . $name)) 
				array_push($paths,MEDIA_FOLDER . SITENAME . "/" . $name);
		}
		if(count($paths)>0) {
			if(getConfig("FULL_MEDIA_PATH")=="true" && !$relativeOnly)
				return SiteLocation.$paths[0];
			else
				return $paths[0];
		}
		return $name;
	}
	function loadMediaList($mediaPath) {
		if(strlen($mediaPath)<=0) return "";
		$paths=array();
		
		if(defined("APPS_USERDATA")) {
			if(!in_array(BASEPATH . APPS_USERDATA . $mediaPath,$paths) && file_exists(ROOT.BASEPATH . APPS_USERDATA . $mediaPath)) 
				array_push($paths,BASEPATH . APPS_USERDATA . $mediaPath);
		}
		if(defined("APPS_MEDIA_FOLDER")) {
			if(!in_array(BASEPATH . APPS_MEDIA_FOLDER . $mediaPath,$paths) && file_exists(ROOT.BASEPATH . APPS_MEDIA_FOLDER . $mediaPath)) 
				array_push($paths,BASEPATH . APPS_MEDIA_FOLDER . $mediaPath);
		}
		if(defined("APPS_THEME")) {
			if(!in_array(THEME_FOLDER.APPS_THEME."/".$mediaPath,$paths) && file_exists(ROOT.THEME_FOLDER.APPS_THEME."/".$mediaPath)) 
				array_push($paths,THEME_FOLDER.APPS_THEME."/".$mediaPath);
		}
		if(!in_array(MEDIA_FOLDER . $mediaPath,$paths) && file_exists(ROOT.MEDIA_FOLDER . $mediaPath)) 
			array_push($paths,MEDIA_FOLDER . $mediaPath);		
		if(defined("SITENAME")) {
			if(!in_array(MEDIA_FOLDER . SITENAME . "/" . $mediaPath,$paths) && file_exists(ROOT.MEDIA_FOLDER . SITENAME . "/" . $mediaPath)) 
				array_push($paths,MEDIA_FOLDER . SITENAME . "/" . $mediaPath);
		}
		
		foreach($paths as $a) {
			$fa=$a;
			if(getConfig("FULL_MEDIA_PATH")=="true")
				$fa=SiteLocation.$a;
			if(file_exists(ROOT . $a)) {
				$out=array();
				$arr=scandir($a);
				unset($arr[0]);unset($arr[1]);
				
				foreach($arr as $m=>$n) {
					$fs=$fa."/".$n;
					$fs=str_replace("//","/",$fs);
					array_push($out,$fs);
				}				
				return $out;
			}
		}
		return array();
	}
	function loadContent($p) {
		global $js,$css,$ling,$cache,$templates;
		if(strlen($p)<=0) return;
		$paths=array();
		if(defined("APPS_PAGES_FOLDER") && file_exists(ROOT.BASEPATH . APPS_PAGES_FOLDER)) {
			array_push($paths,BASEPATH . APPS_PAGES_FOLDER);
		}
		array_push($paths,BASEPATH);
		foreach($paths as $a) {
			if(strlen($p)<=0) continue;
			if(file_exists($a . $p)) {
				include $a . $p;
				return;
			}
		}
		global ${$p};
		
		//echo $p;
		/*if(function_exists($p)) {
			call_user_func($p);
		} else*/
		if(strpos($p,".php")>2 || strpos($p,".htm")>2 || strpos($p,".html")>2) {
			//if(MASTER_DEBUG_MODE=="true")
			//trigger_error("Page Component Not Found :: <b>$p</b>, For Page :: <b>$current_page</b>, For Site :: <b>" . SITENAME . "</b>, ::: ");
		} elseif(isset(${$p})) {
			echo ${$p};
		} elseif(defined($p)) {
			echo constant($p);
		} else {
			echo $p;
		}
	}
}
?>
