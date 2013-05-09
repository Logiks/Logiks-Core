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
	function loadAllMedia($media,$relativeOnly=false,$defaultMedia=null) {
		if(strlen($media)<=0) return "";
		$linkedApps=getConfig("LINKED_APPS");
		if(strlen($linkedApps)<=0) return loadMedia($media,$relativeOnly);
		$linkedApps=explode(",",$linkedApps);

		global $mediaPaths;
		if(count($mediaPaths)<=0)
			$mediaPaths=$GLOBALS['mediaPaths'];

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
	function loadMedia($name,$relativeOnly=false,$defaultMedia=null) {
		if(strlen($name)<=0) return "";
		$paths=getAllMediaFolders();
		if(count($paths)>0) {
			foreach($paths as $a) {
				if(file_exists(ROOT.$a.$name)) {
					if(getConfig("FULL_MEDIA_PATH")=="true" && !$relativeOnly)
						return SiteLocation.$a.$name;
					else
						return $a.$name;
				}
			}
		}
		if($defaultMedia==null)
			return $name;
		else $defaultMedia;
	}
	function loadMediaList($mediaPath) {
		if(strlen($mediaPath)<=0) return "";
		$paths=getAllMediaFolders();

		foreach($paths as $a) {
			$fa=$a.$mediaPath;
			if(is_dir(ROOT.$fa)) {
				$out=array();
				$arr=scandir(ROOT.$fa);
				unset($arr[0]);unset($arr[1]);
				if(getConfig("FULL_MEDIA_PATH")=="true") {
					foreach($arr as $m=>$n) {
						$fs=$fa."/".$n;
						$fs=str_replace("//","/",$fs);
						array_push($out,SiteLocation.$fs);
					}
				} else {
					foreach($arr as $m=>$n) {
						$fs=$fa."/".$n;
						$fs=str_replace("//","/",$fs);
						array_push($out,$fs);
					}
				}
				return $out;
			}
		}
		return array();
	}
	function loadContent($p,$defaultContent=null) {
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
			if($defaultContent==null)
				echo $p;
			else
				echo $defaultContent;
		}
	}
	function getAllMediaFolders() {
		$paths=array();
		if(!isset($_ENV['MEDIA_DIRS'])) {
			$mediaPaths=$GLOBALS['mediaPaths'];
			if(defined("BASEPATH")) {
				foreach($mediaPaths as $a) {
					array_push($paths,BASEPATH.$a);
				}
			}
			if(defined("APPS_THEME")) {
				array_push($paths,THEME_FOLDER.APPS_THEME."/");
			}
			if(defined("MEDIA_FOLDER")) {
				array_push($paths,MEDIA_FOLDER);
				array_push($paths,MEDIA_FOLDER.SITENAME."/");
			}
			$_ENV['MEDIA_DIRS']=$paths;
		} else {
			$paths=$_ENV['MEDIA_DIRS'];
		}
		return $paths;
	}
}
?>
