<?php
/*
 * Some Special System Functions 
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
 
if(!defined('ROOT')) exit('No direct script access allowed');
//Some Special System Functions
if(!function_exists("getRelativePath")) {
	function getRelativePath($file) {
		$s=str_replace(SiteRoot,"",dirname($file) . "/");
		$basepath="";
		$s=str_replace("//","/",$s);
		for($j=0;$j<substr_count($s,"/");$j++) {
			$basepath.="../";
		}
		return $basepath;
	}
	function getWebPath($file) {
		return WEBROOT.dirname(str_replace(ROOT,"",$file))."/";
	}
	function getRootPath($file) {
		return ROOT.dirname(str_replace(ROOT,"",$file))."/";
	}
	function getBasePath() {
		if(isset($_SERVER['PATH_INFO'])) {
			$file=$_SERVER['PATH_INFO'];
			return getRelativePath($file);
		} else return "";
	}
	function getConfigPath($local=true) {
		if($local) {
			return APPROOT.APPS_CONFIG_FOLDER;
		} else {
			return ROOT.CFG_FOLDER;
		}
	}
}
?>
