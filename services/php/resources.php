<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST['src'])) {
	if(isset($_REQUEST['encoded']) && $_REQUEST['encoded']=="true") {
		$_REQUEST['src']=base64_decode($_REQUEST['src']);
	}
	if(strlen($_REQUEST['src'])>0) {
		$_REQUEST['src']=explode(",", $_REQUEST['src']);
		if(!isset($_REQUEST['type'])) {
			$_REQUEST['type']=current($_REQUEST['src']);
			$_REQUEST['type']=strtolower(end(explode(".", $_REQUEST['type'])));
		} else {
			$_REQUEST['type']=strtolower($_REQUEST['type']);
		}

		$theme="";
		if(isset($_REQUEST['theme'])) {
			$theme=$_REQUEST['theme'];
		} elseif(defined("APPS_THEME")) {
			$theme=APPS_THEME;
		} else {
			define("APPS_THEME","default");
			$theme="default";
		}
		if($theme=="*" || strlen($theme)<=0) {
			if(defined("APPS_THEME")) $theme=APPS_THEME;
			else $theme="default";
		}

		_theme($theme);
		$htmlAsset=HTMLAssets::singleton();

		switch ($_REQUEST['type']) {
			case 'css':
				header("Content-type: text/css");
			break;
			case 'js':
				header('Content-type: text/javascript');
			break;
			default:
				header('Content-type: text/plain');
		}
		//printArray($_REQUEST['src']);exit();
		
		$type=$_REQUEST['type'];
		foreach ($_REQUEST['src'] as $file) {
			if(strlen($file)<=0) continue;

			$fsx=explode(".", $file);
			$ext=end($fsx);
			if(strtolower($ext)==$_REQUEST['type']) {
				array_pop($fsx);
				$fname=implode(".", $fsx);
			} else {
				$fname=$file;
			}

			$path=$htmlAsset->getAssetPath($fname,$type,array("theme"=>$theme));
			//printArray($path);continue;

			if($path["FILE"]=="CDN") {
				echo "/*************$file****************/\n";
				echo _cachePrint($path["LINK"]);
				echo "\n\n";
			} elseif(file_exists($path["FILE"])) {
				echo "/*************$file****************/\n";
				readfile($path["FILE"]);
				echo "\n\n";
			}
		}
	}
}
?>
