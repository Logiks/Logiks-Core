<?php
//Here all ui related common tasks are kept, for easier updates.
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('getUserPageStyle')) {
	function getUserPageStyle($encloseTag=true) {
		$s="";
		if(strlen(getConfig("PAGE_BACKGROUND"))>0) {
			$bg=getConfig("PAGE_BACKGROUND");
			$s="background: url(media/cssbg/$bg) repeat;";
			$s="body { $s }";
		}
		if(strlen($s)>0 && $encloseTag) {
			$s="<style>{$s}</style>";
		}
		return $s;
	}
	function printSubSkin() {
		if(defined("SUBSKIN_SPECS") && defined("APPS_SUBSKIN")) {
			$skinSpec=SUBSKIN_SPECS;
			if($skinSpec=="jquery") $skinSpec="jquery.ui";

			$skin=getConfig("APPS_SUBSKIN");
			if(strlen($skin)==0 || $skin=="*") {
				$skin="";
			} else {
				$skin=".{$skin}";
			}
			_skin("{$skinSpec}{$skin}");
		}
	}
	function getBodyContext() {
		$bodyContext="";
		if(getConfig("LOCK_CONTEXTMENU")=="true") $bodyContext.="oncontextmenu='return false' ";
		if(getConfig("LOCK_SELECTION")=="true") $bodyContext.="onselectstart='return false' ";
		if(getConfig("LOCK_MOUSEDRAG")=="true") $bodyContext.="ondragstart='return false' ";
		$bClass=getConfig("BODY_CLASS");
		if(strlen($bClass)>0) $bodyContext.="class='$bClass' ";
		return $bodyContext;
	}
}
if(!function_exists('displayLayout')) {
	function displayLayout($layoutTemplate, $params,$css="",$js="") {
		$pageLayout=new PageLayout();
		if($pageLayout->loadLayoutTemplate($layoutTemplate)) {
			if(!is_array($params)) {
				$appLayoutDir=APPROOT."config/layouts/";
				$f1=$appLayoutDir."{$params}.layout";
				if(file_exists($f1)) {
					$params=PageLayout::readLayoutConfig($f1);
				}
			}
			if(!is_array($css) && strlen($css)>0) $css=explode(",",$css);
			if(!is_array($js) && strlen($js)>0) $js=explode(",",$js);
			_css($css);
			_js($js);
			echo $pageLayout->printLayout($params);
		} else {
			dispErrMessage("Required Layout Template Not Found.","PageLayout Template Error",409,'apps');
			exit();
		}
	}
	function generatePageLayout($layout,$css="",$js="") {
		$appLayoutDir=APPROOT.APPS_PAGES_FOLDER."layouts/";
		$f1=$appLayoutDir."{$layout}.json";
		if(file_exists($f1) && is_readable($f1)) {
			$json=file_get_contents($f1);
			$json=json_decode($json,true);
			if($json==null) {
				dispErrMessage("Layout Configuration Has Wrong Format.","Layout Config Error",409,'apps');
				exit();
			}

			if(!isset($json['css'])) $json['css']="";
			if(!isset($json['js'])) $json['js']="";
			if(!isset($json['modules'])) $json['modules']="";
			if(!isset($json['enabled'])) $json['enabled']=true;

			if(!$json['enabled']) {
				dispErrMessage("Requested Page Is Not Available Or Blocked.","PageLayout Error",409,'apps');
				exit();
			}

			$layoutTemplate=$json['template'];
			$layoutParams=$json['layout'];
			$csss=explode(",",$json['css']);
			$jss=explode(",",$json['js']);
			$modules=explode(",",$json['modules']);

			$params=array();
			foreach($layoutParams as $a=>$b) {
				if($b["enable"]) {
					$params[$a]=$b['component'];
				}
			}
			loadModules($modules);
			displayLayout($layoutTemplate, $params, $css, $js);
			_css($csss);
			_js($jss);
		} else {
			dispErrMessage("Layout Configuration File Missing OR UnReadable.","Layout Config Error",409,'apps');
			exit();
		}
	}
	function isLayoutConfig($layout) {
		$appLayoutDir=APPROOT.APPS_PAGES_FOLDER."layouts/";
		$f1=$appLayoutDir."{$layout}.json";
		if(file_exists($f1) && is_readable($f1)) {
			return true;
		}
		return false;
	}
}
?>
