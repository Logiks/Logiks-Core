<?php
/*
 * This class is central to various functions used for ui generations and others.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
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
	function getBodyContext($params=array()) {
		$bodyContext="";
		if(getConfig("LOCK_CONTEXTMENU")=="true") $bodyContext.="oncontextmenu='return false' ";
		if(getConfig("LOCK_SELECTION")=="true") $bodyContext.="onselectstart='return false' ";
		if(getConfig("LOCK_MOUSEDRAG")=="true") $bodyContext.="ondragstart='return false' ";
		$bClass=getConfig("BODY_CLASS");
		$pClass=str_replace("/", "-", $_REQUEST["page"]);
		if(strlen(trim($bClass.$pClass))>0) $bodyContext.="class='".trim($bClass." ".$pClass)."' ";
		foreach ($params as $key => $value) {
			$bodyContext.="$key='$value' ";
		}
		return $bodyContext;
	}
}
if(!function_exists('displayLayout')) {
	function displayLayout($layoutTemplate, $params,$css="",$js="") {
		$pageLayout=new PageLayout();
		if($pageLayout->loadLayoutTemplate($layoutTemplate)) {
			if(!is_array($params)) {
				$appLayoutDir=APPROOT.APPS_PAGES_FOLDER."layouts/";
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
	function findPage($page) {
		$layout=isLogiksLayout($page);
		$loaded=false;
		if($layout) {
			$loaded=true;
			getUserPageStyle(true);
			echo "</head>\n\n<body ".getBodyContext().">";
			generatePageLayout($page);
			echo "</body>";
		} else {
			$device=strtoupper(getUserDeviceType());
			$arrPages=array();
			$arrErrPages=array();
			if(defined("APPS_{$device}_PAGES_FOLDER")) {
				array_push($arrPages,APPROOT.constant("APPS_{$device}_PAGES_FOLDER")."{$page}.php");
				array_push($arrErrPages,APPROOT.constant("APPS_{$device}_PAGES_FOLDER")."error.php");
			}
			if(defined("APPS_PAGES_FOLDER")) {
				array_push($arrPages,APPROOT.APPS_PAGES_FOLDER."{$page}.php");
				array_push($arrErrPages,APPROOT.APPS_PAGES_FOLDER."error.php");
			}
			if(getConfig("ALLOW_DEFAULT_SYSTEM_PAGES")=="true") {
				array_push($arrPages,ROOT.PAGES_FOLDER."{$page}.php");
				array_push($arrErrPages,ROOT.PAGES_FOLDER."errors/404.php");
				array_push($arrErrPages,ROOT.PAGES_FOLDER."errors/default.php");
			}
			foreach($arrPages as $f) {
				if(file_exists($f)) {
					$loaded=true;
					getUserPageStyle(true);
					echo "</head>\n\n<body ".getBodyContext().">";
					include $f;
					echo "</body>";
					break;
				}
			}
		}
		if(!$loaded) {
			$page="error";
			$layout=isLogiksLayout($page);
			$deviceFolder="APPS_{$device}_PAGES_FOLDER";
			if($layout) {
				$loaded=true;
				getUserPageStyle(true);
				echo "</head>\n\n<body ".getBodyContext().">";
				generatePageLayout($page);
				echo "</body>";
			} elseif(constant($deviceFolder) && file_exists(APPROOT.constant($deviceFolder)."{$page}.php")) {
				$f=APPROOT.constant($deviceFolder)."{$page}.php";
				$loaded=true;
				getUserPageStyle(true);
				echo "</head>\n\n<body ".getBodyContext().">";
				include $f;
				echo "</body>";
			} elseif(defined("APPS_PAGES_FOLDER") && file_exists(APPROOT.APPS_PAGES_FOLDER."{$page}.php")) {
				$f=APPROOT.APPS_PAGES_FOLDER."{$page}.php";
				$loaded=true;
				getUserPageStyle(true);
				echo "</head>\n\n<body ".getBodyContext().">";
				include $f;
				echo "</body>";
			} else {
				trigger_ErrorCode(404,"Sorry, Requested <i>{$_REQUEST['page']}</i> Page Not Found.");
			}
		}
	}
	function isLogiksLayout($layout) {
		$device=strtoupper(getUserDeviceType());
		$deviceFolder="APPS_{$device}_PAGES_FOLDER";

		if(defined($deviceFolder)) {
			$pagesDir=APPROOT.constant($deviceFolder);
			if(is_dir($pagesDir)) {
				$f1=$pagesDir."layouts/{$layout}.json";
				if(file_exists($f1) && is_readable($f1)) {
					return $f1;
				}
				return false;
			}	
		}
		$pagesDir=APPROOT.constant("APPS_PAGES_FOLDER");
		if(is_dir($pagesDir)) {
			$f1=$pagesDir."layouts/{$layout}.json";
			if(file_exists($f1) && is_readable($f1)) {
				return $f1;
			}
			return false;
		}
		return false;
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
