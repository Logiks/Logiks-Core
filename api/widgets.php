<?php
//This class is used for Loading And Managing Widgets
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('getWidgetGroupCode')) {
	function getWidgetGroupCode($funcCode) {
		global $current_page;
		if(file_exists($funcCode)) {
			$funcCode=basename(dirname($funcCode));
		}
		$s=md5(SITENAME . "_" . $current_page . "_" . $funcCode);
		return $s;
	}
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
		
		if(getConfig("BLOCK_GOOGLE_GADGETS")=='true') {
			if(strpos("#".strtolower($widget),"google")==1 || strpos("#".strtolower($widget),"gadget")==1) {
				return;
			}
		}
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
				printWidget($widget,$f1,$params, $asPortlets);
				return;
			} elseif(file_exists($f2)) {
				printWidget($widget, $f2, $params, $asPortlets);
				return;
			}
		}
		if(MASTER_DEBUG_MODE=='true') trigger_error("Widget Not Found :: " . $widget);
	}
	function loadPageWidget($block,$params) {
		global $widgetspath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."pageblocks/";
			if(file_exists($p)) {
				if(!in_array($p,$widgetspath)) array_push($widgetspath, $p);
			}
			$p=BASEPATH . APPS_PLUGINS_FOLDER."widgets/";
			if(file_exists($p)) {
				if(!in_array($p,$widgetspath)) array_push($widgetspath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."pageblocks/")) {
				if(!in_array(PLUGINS_FOLDER."pageblocks/",$widgetspath)) array_push($widgetspath, PLUGINS_FOLDER."pageblocks/");
			}
			if(file_exists(ROOT.PLUGINS_FOLDER."widgets/")) {
				if(!in_array(PLUGINS_FOLDER."widgets/",$widgetspath)) array_push($widgetspath, PLUGINS_FOLDER."widgets/");
			}
		}
		foreach($widgetspath as $a) {
			$f1=ROOT . $a . $widget . "/index.php";
			$f2=ROOT . $a . $widget . ".php";
			if(file_exists($f1)) {
				printWidget($widget,$f1,$params, $asPortlets);
				return;
			} elseif(file_exists($f2)) {
				printWidget($widget, $f2, $params, false);
				return;
			}
		}
		if(MASTER_DEBUG_MODE=='true') trigger_error("Widget Not Found :: " . $widget);
	}
	function printWidget($name, $widgetPath, $params=array(), $asPortlets=true) {
		global $js,$css,$ling,$cache,$templates;
		
		if(strlen($widgetPath)<=0) return;
		if($asPortlets) echo "<div name='$name' class='portlet'>";
		$WIDGET_PARAMS=$params;		
		$WIDGET_PARAMS["WIDGET_GROUP_ID"]=getWidgetGroupCode($name);
		include $widgetPath;
		if($asPortlets) echo "</div>";
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
	function checkPageWidget($widget) {
		if(strlen($widget)<=0) return false;
		global $widgetspath;
		if(defined("APPS_PLUGINS_FOLDER")) {
			$p=BASEPATH . APPS_PLUGINS_FOLDER."pageblocks/";
			if(file_exists($p)) {
				if(!in_array($p,$widgetspath)) array_push($widgetspath, $p);
			}
			$p=BASEPATH . APPS_PLUGINS_FOLDER."widgets/";
			if(file_exists($p)) {
				array_push($widgetspath, $p);
			}
		}
		if(defined("PLUGINS_FOLDER")) {
			if(file_exists(ROOT.PLUGINS_FOLDER."pageblocks/")) {
				if(!in_array(PLUGINS_FOLDER."pageblocks/",$widgetspath)) array_push($widgetspath, PLUGINS_FOLDER."pageblocks/");
			}
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
	function unsetWidgetSettings($WIDGET_PARAMS) {
		unset($WIDGET_PARAMS['WIDGET_WEB_PATH']);
		unset($WIDGET_PARAMS['WIDGET_ROOT_PATH']);
		unset($WIDGET_PARAMS['WIDGET_CONFIG_PATH']);
		unset($WIDGET_PARAMS['WIDGET_GROUP_ID']);
		return $WIDGET_PARAMS;
	}
}
?>
