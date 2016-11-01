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
				APPROOT.APPS_MISC_FOLDER."contents/{$file}.htm",
				APPROOT.APPS_PAGES_FOLDER."contents/{$file}.htm"
			];
		foreach ($fs as $f) {
			if(file_exists($f)) {
				return file_get_contents($f);
			}
		}
		return "";
	}
	function loadSnippets($files) {
		if(!is_array($files)) {
			$files=[$files];
		}
		$out=[];
		foreach ($files as $f) {
			$fx=[
					APPROOT.APPS_PAGES_FOLDER."snippets/{$f}.php",
					APPROOT.APPS_PLUGINS_FOLDER."snippets/{$f}.php"
				];
			if(file_exists($fx[0])) {
				$out["loaded"][]=$f;
				eval(file_get_contents($fx[0]));
			} elseif(file_exists($fx[1])) {
				$out["loaded"][]=$f;
				eval(file_get_contents($fx[1]));
			} else {
				$out["error"][]=$f;
			}
		}
		return $out;
	}
}
?>
