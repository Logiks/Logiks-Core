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
		$f1=APPROOT.APPS_PAGES_FOLDER."comps/{$component}.php";
		$f2=APPROOT.APPS_PAGES_FOLDER."comps/{$component}.tpl";
		$f3=APPROOT.APPS_PAGES_FOLDER."comps/{$component}.htm";
		if(file_exists($f1)) {
			include $f1;
		} elseif(file_exists($f2)) {
			_template($f2);
		} elseif(file_exists($f3)) {
			readfile($f3);
		}

	}

	function loadContent($file) {
		$f=APPROOT.APPS_PAGES_FOLDER."contents/{$file}.htm";
		if(file_exists($f)) {
			return file_get_contents($f);
		}
		return "";
	}
}