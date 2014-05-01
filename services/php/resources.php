<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST['src'])) {
	$_REQUEST['src']=ROOT.base64_decode($_REQUEST['src']);

	$ext=strtolower(end(explode(".",$_REQUEST['src'])));
	if(file_exists($_REQUEST['src'])) {
		if($ext=="css") {
			header("Content-type: text/css");
			readfile($_REQUEST['src']);
		} elseif($ext=="less") {
			$lessData=file_get_contents($_REQUEST['src']);

			$p=getWebpath($_REQUEST['src']);
			$search = '#url\((?!\s*[\'"]?(?:https?:)?//)\s*([\'"])?#';
			$replace = "url($1{$p}";
			$lessData = preg_replace($search, $replace, $lessData);

			$lessData=str_replace("/*CONFIG*/",getLessParams(),$lessData);
			
			$less = new lessc();
			$cssData=$less->parse($lessData);

			header("Content-type: text/css");
			echo $cssData;
		} elseif($ext=="js") {
			header('Content-type: text/javascript');
			readfile($_REQUEST['src']);
		} elseif($ext=="jss") {
			$jssData=file_get_contents($_REQUEST['src']);
			$jssData=_replace($jssData);

			header('Content-type: text/javascript');
			echo $jssData;
		}
		//else echo $_REQUEST['src'];
	}
}
function getLessParams() {
	$f="";
	$lessData="";
	if(defined("APPROOT")) {
		if(file_exists(APPROOT."config/theme.less")) {
			$f=APPROOT."config/theme.less";
		} elseif(file_exists(APPROOT."css/theme.less")) {
			$f=APPROOT."css/theme.less";
		}
	}
	/*if(strlen($f)<=0) {
		if(file_exists(ROOT."config/theme.less")) {
			$f=ROOT."config/theme.less";
		} else if(file_exists(ROOT.$this->theme."theme.less")) {
			$f=ROOT.$this->theme."theme.less";
		}
	}*/
	if(strlen($f)>0 && is_readable($f)) {
		if(file_exists($f)) {
			$lessData=file_get_contents($f);
		}
	}
	if(isset($_ENV['THEME_LESS_DATA'])) $lessData.=$_ENV['THEME_LESS_DATA'];
	return $lessData;
}
?>
