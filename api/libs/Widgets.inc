<?php
/*
 * This class is used for creating widget views,etc.
 */
class Widgets {
	public static function isEnabled($widget) {
		$wGroups=array();
		if(strlen(getConfig("BLOCK_WIDGET_GROUPS"))>0) {
			$wGroups=explode(",",getConfig("BLOCK_WIDGET_GROUPS"));
			$wg=explode("-",strtolower($widget));
			if(count($wg)>1) {
				$wg=$wg[0];
				if(in_array($wg,$wGroups)) {
					return false;
				}
			}
		}
		return true;
	}

	public static function getWidgetGroupCode($funcCode) {
		global $current_page;
		if(file_exists($funcCode)) {
			$funcCode=basename(dirname($funcCode));
		}
		$s=md5(SITENAME . "_" . $current_page . "_" . $funcCode);
		return $s;
	}
	public static function printWidget($name, $widgetPath, $params=array(), $asPortlets=true) {
		global $js,$css,$ling,$cache,$templates;

		if(strlen($widgetPath)<=0) return;
		if($asPortlets) echo "<div name='$name' class='portlet'>";
		$WIDGET_PARAMS=$params;
		$WIDGET_PARAMS["WIDGET_GROUP_ID"]=Widgets::getWidgetGroupCode($name);
		include $widgetPath;
		if($asPortlets) echo "</div>";
	}
	public static function unsetWidgetSettings($WIDGET_PARAMS) {
		unset($WIDGET_PARAMS['WIDGET_WEB_PATH']);
		unset($WIDGET_PARAMS['WIDGET_ROOT_PATH']);
		unset($WIDGET_PARAMS['WIDGET_CONFIG_PATH']);
		unset($WIDGET_PARAMS['WIDGET_GROUP_ID']);
		return $WIDGET_PARAMS;
	}
}
?>
