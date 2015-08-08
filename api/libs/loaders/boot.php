<?php
/*
 * This file is used to manage and use all loaders like helpers,modules,widgets, etc..
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/media.php";
include_once dirname(__FILE__)."/helpers.php";
include_once dirname(__FILE__)."/vendors.php";
include_once dirname(__FILE__)."/modules.php";
include_once dirname(__FILE__)."/widgets.php";

if(!function_exists('getLoaderFolders')) {
	function getLoaderFolders($loaderType,$addPath="",$site=null) {
		$src="LOADERS_".strtoupper($loaderType);
		if(!isset($_ENV[$src])) {
			if($site==null) $site=SITENAME;
			$basePath=APPS_FOLDER.$site."/";

			$pluginFolders=$GLOBALS[$loaderType];
			if(strlen($addPath)>0) {
				$addPath.="/";
			}
			foreach ($pluginFolders as $key => $value) {
				$value=str_replace("#ROOT#", "", $value);
				$value=str_replace("#APPROOT#", $basePath, $value);
				$value=str_replace("#SITENAME#", $site, $value);
				if(defined("APPS_THEME")) $value=str_replace("#THEME#", THEME_FOLDER.APPS_THEME, $value);
				else $value=str_replace("#THEME#", THEME_FOLDER."default/", $value);
				
				$pluginFolders[$key]=$value."{$addPath}";
			}
			$_ENV[$src]=$pluginFolders;
		}
		return $_ENV[$src];
	}
}
?>
