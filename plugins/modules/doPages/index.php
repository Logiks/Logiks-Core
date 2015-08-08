<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printPageContent")) {
	$webPath=getWebPath(__FILE__);
	$rootPath=getRootPath(__FILE__);
?>
	<link href='<?=$webPath?>css/style.css' rel='stylesheet' type='text/css' media='all' />
	<script src='<?=$webPath?>js/script.js' type='text/javascript' language='javascript'></script>
<?php
	include_once dirname(__FILE__)."/api.php";
	//Params -
	//	apppage :: toolbar, contentarea, hidden, autopopup
	function printPageContent($layout,$metaParams=array(),$classes=array(),$opts=array()) {
		$webPath=getWebPath(__FILE__);

		if(!is_array($classes)) {
			$classes=array();
		}
		if(!is_array($opts)) {
			$opts=array();
		}
		$classes=array_merge(array(
					"page"=>"ui-widget-content",
					"toolbar"=>"ui-widget-header",

				),$classes);
		$opts=array_merge(array(
					"toolButtons"=>false,
					"title"=>""
				),$opts);
		$l=dirname(__FILE__)."/layouts/$layout.php";
		if(file_exists($l)) {
			$_SESSION["page_params"]=$metaParams;
			$_SESSION["page_classes"]=$classes;
			$_SESSION["page_opts"]=$opts;

			if(file_exists(dirname(__FILE__)."/js/{$layout}.js")) {
				echo "<script src='{$webPath}/js/{$layout}.js' type='text/javascript' language='javascript'></script>";
			}
			if(file_exists(dirname(__FILE__)."/css/{$layout}.css")) {
				echo "<link href='{$webPath}css/{$layout}.css' rel='stylesheet' type='text/css' media='all' />";
			}
			include $l;

			unset($_SESSION["page_params"]);
			unset($_SESSION["page_classes"]);
			unset($_SESSION["page_opts"]);
		} else {
			echo "<style>body {overflow:hidden;}</style>";
			dispErrMessage("The Layout <i>$layout</i> Not Supported","404:Page-Layout Not Found",404);
		}
	}
	function listPageLayouts() {
		$arr=scandir(dirname(__FILE__)."/layouts/");
		unset($arr[0]);unset($arr[1]);
		foreach($arr as $a=>$b) {
			$b=str_replace(".php","",$b);
			$arr[$a]=$b;
		}
		return $arr;
	}
	function listPageParams() {
		$arr=array(
				"toolbar"=>"",
				"contentarea"=>"",
				"hidden"=>"",
				"autopopup"=>"",
				"sidebar"=>"",
				"footer"=>"",
			);
		return $arr;
	}
}
?>
