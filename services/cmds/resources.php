<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST['src'])) {
	include_once ROOT. "api/libs/logiksPages/boot.php";

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

		$themeEngine=new LogiksTheme($theme);
		$htmlAsset=new HTMLAssets($themeEngine);

		ob_clean();
		switch ($_REQUEST['type']) {
			case 'css':
				header("Content-type: text/css");
			break;
			case 'js':
				header('Content-type: application/javascript; charset=utf-8');
			break;
			default:
				header('Content-type: text/plain');
		}
		header('Pragma:cache');
		header('Cache-Control: max-age='.(60*60*24*30));
		// header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 60)));
		// printArray($_REQUEST['src']);exit();

		$noMinifyCSS = [];
		$noMinifyJS = [];
		if(getConfig("NO_MINIFY_CSS")) {
			$noMinifyCSS = explode(",", getConfig("NO_MINIFY_CSS"));
		}
		if(getConfig("NO_MINIFY_JS")) {
			$noMinifyJS = explode(",", getConfig("NO_MINIFY_JS"));
		}
		
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
			
			if(strpos(strtolower($file), ".min")) {
				$htmlAsset->printAsset($fname,$type,array("theme"=>$theme), false);
			} else {
				switch ($_REQUEST['type']) {
					case 'css':
						if(in_array($file, $noMinifyCSS)) {
							$htmlAsset->printAsset($fname,$type,array("theme"=>$theme), false);
						} else {
							$htmlAsset->printAsset($fname,$type,array("theme"=>$theme), false);
						}
					break;
					case 'js':
						if(in_array($file, $noMinifyJS)) {
							$htmlAsset->printAsset($fname,$type,array("theme"=>$theme), false);
						} else {
							$htmlAsset->printAsset($fname,$type,array("theme"=>$theme), true);
						}
					break;
				}
			}
		}
	}
}
?>
