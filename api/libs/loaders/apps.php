<?php
/*
 * This file contains functions for app level componennt/comps loading
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadComponent')) {
	function loadComponent($component) {
		$fs=[
				APPROOT.APPS_PAGES_FOLDER."comps/{$component}.php"=>"php",
				APPROOT.APPS_PAGES_FOLDER."comps/{$component}.tpl"=>"tpl",
				APPROOT.APPS_PAGES_FOLDER."comps/{$component}.htm"=>"htm",
			];
		foreach($fs as $f=>$ext) {
			if(file_exists($f)) {
				switch($ext) {
					case "tpl":
						_templatePage($f);
					break;
					case "php":
						include $f;
					break;
					case "htm":
						readfile($f);
					break;
				}
			}
		}
	}

	function loadContent($file) {
		$fs=[
				APPROOT.APPS_MISC_FOLDER."contents/{$file}.htm"
				APPROOT.APPS_PAGES_FOLDER."contents/{$file}.htm"
			];
		foreach ($fs as $f) {
			if(file_exists($f)) {
				return file_get_contents($f);
			}
		}
		return "";
	}
}
