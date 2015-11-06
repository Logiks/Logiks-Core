<?php
/*
 * This file contains functions for Loading media and images
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadAllMedia')) {
  function loadAllMedia($media,$relativeOnly=false,$defaultMedia=null) {
		if(strlen($media)<=0) return "";
		$linkedApps=getConfig("LINKED_APPS");
		if(strlen($linkedApps)<=0) return loadMedia($media,$relativeOnly);
		$linkedApps=explode(",",$linkedApps);

		$cachePath=_metaCache("MEDIA:ALL",$media);
		if(!$cachePath) {
			$paths=getLoaderFolders('mediaPaths',"");

			foreach($linkedApps as $app) {
				$appDir=str_replace("#APPROOT#", APPROOT, $app)."/";
				if(is_dir(ROOT.$appDir)) {
					foreach($mediaPaths as $mp) {
						$f=$appDir.$mp.$media;
						if(file_exists(ROOT.$f)) {
							_metaCacheUpdate("MEDIA:ALL",$media,$f);
							if(!$relativeOnly)
								return SiteLocation.$f;
							else
								return $f;
						}
					}
				}
			}
			return loadMedia($media,$relativeOnly);
		} else {
			if(!$relativeOnly)
				return SiteLocation.$cachePath;
			else
				return $cachePath;
		}
	}
	function loadMedia($name,$relativeOnly=false,$defaultMedia=null) {
		if(strlen($name)<=0) return "";

		$cachePath=_metaCache("MEDIA:SITE",$name);
		if(!$cachePath) {
			$paths=getLoaderFolders('mediaPaths',"");

			if(count($paths)>0) {
				foreach($paths as $a) {
					if(file_exists(ROOT.$a.$name)) {
						_metaCacheUpdate("MEDIA:SITE",$name,$a.$name);
						if(!$relativeOnly)
							return SiteLocation.$a.$name;
						else
							return $a.$name;
					}
				}
			}
			if($defaultMedia==null)
				return $name;
			else $defaultMedia;
		} else {
			if(!$relativeOnly) {
				return SiteLocation.$cachePath;
			} else {
				return $cachePath;
			}
		}
	}
	function loadMediaList($mediaPath,$relativeOnly=false) {
		if(strlen($mediaPath)<=0) return "";
    	$paths=getLoaderFolders('mediaPaths',"");

		foreach($paths as $a) {
			$fa=$a.$mediaPath;
			if(is_dir(ROOT.$fa)) {
				$out=array();
				$arr=scandir(ROOT.$fa);
				unset($arr[0]);unset($arr[1]);
        		if(!$relativeOnly) {
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
}
?>
