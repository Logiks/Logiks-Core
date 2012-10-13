<?php
//This class is used for Loading And Managing Widgets
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadAllWidgets')) {
	function loadAllWidgets($widgetList, $params=array(), $asPortlets=false) {
		if(!is_array($widgetList)) {
			$widgetList=explode(",",$widgetList);
		} elseif(strlen($widgetList)<=0) return;
		
		foreach($widgetList as $a) {
			loadWidget($a,$params, $asPortlets);
		}
	}
	function loadWidget($widget, $params=array(), $asPortlets=false) {
		if(strlen($widget)<=0) return;
		if(!Widgets::isEnabled($widget)) return;
		
		global $widgetspath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."widgets/";
			if(file_exists($p)) {
				if(!in_array($p,$widgetspath)) array_push($widgetspath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."widgets/")) {
				if(!in_array(PLUGINS_FOLDER."widgets/",$widgetspath)) array_push($widgetspath, PLUGINS_FOLDER."widgets/");
			}
		}
		foreach($widgetspath as $a) {
			$f1=ROOT . $a . $widget . "/index.php";
			$f2=ROOT . $a . $widget . ".php";
			if(file_exists($f1)) {
				Widgets::printWidget($widget,$f1,$params, $asPortlets);
				return;
			} elseif(file_exists($f2)) {
				Widgets::printWidget($widget, $f2, $params, $asPortlets);
				return;
			}
		}
		if(MASTER_DEBUG_MODE=='true') trigger_error("Widget Not Found :: " . $widget);
	}
	function checkWidget($widget) {
		if(strlen($widget)<=0) return false;
		global $widgetspath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."widgets/";
			if(file_exists($p)) {
				array_push($widgetspath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."widgets/")) {
				if(!in_array(PLUGINS_FOLDER."widgets/",$widgetspath)) array_push($widgetspath, PLUGINS_FOLDER."widgets/");
			}
		}
		foreach($widgetspath as $a) {
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
}
?>
