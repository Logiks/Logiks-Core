<?php
/*
 * This class is used for Loading And Managing Widgets
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/classes/Widgets.inc";

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

		$cachePath=_metaCache("WIDGETS",$widget);
		if(!$cachePath) {
			$path=getAllWidgetsFolders();

			foreach($path as $a) {
				$f1=ROOT . $a . $widget . "/index.php";
				$f2=ROOT . $a . $widget . ".php";
				if(file_exists($f1)) {
					_metaCacheUpdate("WIDGETS",$widget,$f1);
					Widgets::printWidget($widget,$f1,$params);
					return false;
				} elseif(file_exists($f2)) {
					_metaCacheUpdate("WIDGETS",$widget,$f2);
					Widgets::printWidget($widget, $f2, $params);
					return false;
				}
			}

			if(MASTER_DEBUG_MODE=='true') {
				trigger_logikserror("Widget Not Found :: " . $widget,E_LOGIKS_ERROR,404);
			}
		} else {
			Widgets::printWidget($widget,$cachePath,$params);
			return false;
		}
	}
	function checkWidget($widget) {
		if(strlen($widget)<=0) return false;
		$cachePath=_metaCache("WIDGETS",$widget);
		if(!$cachePath) {
			$path=getAllWidgetsFolders();
			foreach($path as $a) {
				$f1=ROOT . $a . $widget . "/index.php";
				$f2=ROOT . $a . $widget . ".php";
				if(file_exists($f1)) {
					_metaCacheUpdate("WIDGETS",$widget,$f1);
					return $f1;
				} elseif(file_exists($f2)) {
					_metaCacheUpdate("WIDGETS",$widget,$f2);
					return $f2;
				}
			}
			return false;
		} else {
			return $cachePath;
		}
	}
	function getAllWidgetsFolders() {
		if(!isset($_ENV['WIDGETS_DIRS'])) {
			$_ENV['WIDGETS_DIRS']=getPluginFolders("widgets");
		} else {
			$paths=$_ENV['WIDGETS_DIRS'];
		}
		return $_ENV['WIDGETS_DIRS'];
	}
}
?>
