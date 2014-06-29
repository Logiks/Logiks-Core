<?php
/*
 * This class is used for Loading And Managing Widgets
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadAllWidgets')) {
	function loadAllWidgets($widgetList, $params=array()) {
		if(!is_array($widgetList)) {
			$widgetList=explode(",",$widgetList);
		} elseif(strlen($widgetList)<=0) return;

		foreach($widgetList as $a) {
			loadWidget($a,$params);
		}
	}
	function loadWidget($widget, $params=array()) {
		if(strlen($widget)<=0) return;
		if(!Widgets::isEnabled($widget)) return;

		$path=getAllWidgetsFolders();

		foreach($path as $a) {
			$f1=ROOT . $a . $widget . "/index.php";
			$f2=ROOT . $a . $widget . ".php";
			if(file_exists($f1)) {
				Widgets::printWidget($widget,$f1,$params, $asPortlets);
				return false;
			} elseif(file_exists($f2)) {
				Widgets::printWidget($widget, $f2, $params, $asPortlets);
				return false;
			}
		}
		if(MASTER_DEBUG_MODE=='true') trigger_error("Widget Not Found :: " . $widget);
		return "Widget Not Found :: " . $widget;
	}
	function checkWidget($widget) {
		if(strlen($widget)<=0) return false;
		$path=getAllWidgetsFolders();
		foreach($path as $a) {
			$f1=ROOT . $a . $widget . "/index.php";
			$f2=ROOT . $a . $widget . ".php";
			if(file_exists($f1)) {
				return $f1;
			} elseif(file_exists($f2)) {
				return $f2;
			}
		}
		return false;
	}
	function getAllWidgetsFolders() {
		$paths=array();
		if(!isset($_ENV['WIDGETS_DIRS'])) {
			if(defined("APPS_PLUGINS_FOLDER")) {
				$p=BASEPATH.APPS_PLUGINS_FOLDER."widgets/";
				if(file_exists(ROOT.$p)) {
					if(!in_array($p,$paths)) array_push($paths, $p);
				}
			}
			if(defined("PLUGINS_FOLDER")) {
				$p=PLUGINS_FOLDER."widgets/";
				if(file_exists(ROOT.$p)) {
					if(!in_array($p,$paths)) array_push($paths, $p);
				}
			}
			$_ENV['WIDGETS_DIRS']=$paths;
		} else {
			$paths=$_ENV['WIDGETS_DIRS'];
		}
		return $paths;
	}
}
?>
