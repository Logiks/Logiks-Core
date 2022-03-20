<?php
/*
 * LogiksPages helps generate pages and related components including
 *    Pages, SiteMap, Theme, Layout, HTMLAssets
 *
 * This includes interdependent classes which come togethar to result in ui rendering.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Version: 1.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/HTMLAssets.inc";
include_once dirname(__FILE__)."/LogiksPage.inc";
include_once dirname(__FILE__)."/LogiksTheme.inc";
include_once dirname(__FILE__)."/PageIndex.inc";

if(!function_exists("_css")) {
	function _cssLink($cssLnk,$themeName=null) {
		if(is_array($cssLnk) && count($cssLnk)<=0) return false;
		elseif(is_array($cssLnk) && count($cssLnk)==1 && strlen($cssLnk[0])==0) return false;
		elseif(is_string($cssLnk)) $cssLnk=explode(",", $cssLnk);

		if($themeName=="*" || $themeName==null) $themeName=APPS_THEME;

		$lx=_service("resources","","raw")."&type=css&src=".implode(",", $cssLnk)."&theme={$themeName}";

		return $lx;
	}
	function _cssAssets($css,$themeName=null,$browser="",$media="") {
		$lx = _cssLink($css,$themeName);
		$html="<link href='{$lx}' rel='stylesheet' type='text/css'";
		if($media!=null && strlen($media)>0) $html.=" media='$media'";
		$html.=" />\n";
		return $html;
	}
	function _css($css,$themeName=null,$browser="",$media="") {
		if(!is_array($css)) $css=explode(",", $css);

		$html="\n\n";
		foreach ($css as $cssLnk) {
			if(strlen($cssLnk)<=0) continue;

			//$lx=_cssLink($cssLnk, $themeName);
			$lx=LogiksSession::getInstance()->htmlAssets()->getAssetURL($cssLnk,'css');
			if(strlen($lx)<=0) continue;

			if($browser!=null && strlen($browser)>0) {
				$html.="<!--[if $browser]>\n";
				$html.="<link href='$lx' rel='stylesheet' type='text/css'";
				if($media!=null && strlen($media)>0) $html.=" media='$media'";
				$html.=" />\n";
				$html.="<![endif]-->\n";
			} else {
				$html.="<link href='$lx' rel='stylesheet' type='text/css'";
				if($media!=null && strlen($media)>0) $html.=" media='$media'";
				$html.=" />\n";
			}
		}
		
		return $html;
	}

	function _jsLink($jsLnk,$themeName=null) {
		if(is_array($jsLnk) && count($jsLnk)<=0) return false;
		elseif(is_array($jsLnk) && count($jsLnk)==1 && strlen($jsLnk[0])==0) return false;
		elseif(is_string($jsLnk)) $jsLnk=explode(",", $jsLnk);

		if($themeName=="*" || $themeName==null) $themeName=APPS_THEME;

		$lx=_service("resources","","raw")."&type=js&src=".implode(",", $jsLnk)."&theme={$themeName}";

		return $lx;
	}
	function _jsAssets($js,$themeName=null,$browser="") {
		$lx = _jsLink($js,$themeName);
		return "<script src='{$lx}' type='text/javascript' language='javascript'></script>\n";
	}
	function _js($js,$themeName=null,$browser="") {
		if(!is_array($js)) $js=explode(",", $js);

		$html="\n\n";
		foreach ($js as $jsLnk) {
			if(strlen($jsLnk)<=0) continue;

			//$lx=_jsLink($jsLnk, $themeName);
			$lx=LogiksSession::getInstance()->htmlAssets()->getAssetURL($jsLnk,'js');
			if(strlen($lx)<=0) continue;
			
			if($browser!=null && strlen($browser)>0) {
				$html.="<!--[if $browser]>\n";
				$html.="<script src='$lx' type='text/javascript' language='javascript'></script>\n";
				$html.="<![endif]-->\n";
			} else {
				$html.="<script src='$lx' type='text/javascript' language='javascript'></script>\n";
			}
		}
		
		return $html;
	}

	function _slug($arrCfg=null) {
		if($arrCfg==null) {
			if(isset($_ENV['PAGESLUG'])) return $_ENV['PAGESLUG'];
		} else {
			if(isset($_ENV['PAGESLUG-MAIN'])) {
				if(!is_array($arrCfg)) $arrCfg=explode("/", $arrCfg);
				$arrCfg=array_flip($arrCfg);

				foreach ($arrCfg as $key => $value) {
					if(isset($_ENV['PAGESLUG-MAIN'][$value])) $arrCfg[$key]=$_ENV['PAGESLUG-MAIN'][$value];
					else $arrCfg[$key]="";
				}

				return $arrCfg;
			} else {
				$uri=current(explode("?", $_SERVER['REQUEST_URI']));
				$slugs=explode("/", $uri);
				if(strlen($slugs[0])==0) $slugs=array_splice($slugs, 1);

				if(!is_array($arrCfg)) $arrCfg=explode("/", $arrCfg);
				$arrCfg=array_flip($arrCfg);

				foreach ($arrCfg as $key => $value) {
					if(isset($slugs[$value])) $arrCfg[$key]=$slugs[$value];
					else $arrCfg[$key]="";
				}
				return $arrCfg;
			}
		}
		return array();
	}

  	//used only for printing pages
	function _templatePage($file,$dataArr=null) {
		if($dataArr==null) $dataArr=[];
		if(isset($_ENV['PAGEVAR']) && is_array($_ENV['PAGEVAR'])) {
			foreach ($_ENV['PAGEVAR'] as $key => $value) {
				$dataArr['PAGE'][$key]=$value;
			}
		}
		return _template($file,$dataArr);
	}
}

if(!function_exists("_pageConfig")) {
	//Sets single variable into loaded into template enviroment
	//This can be accessed from within the templates
	//Also these variables can be accessed using $<name>
  	function _pageVar($key,$value=null) {
  		if(isset($GLOBALS['PAGETMPL'])) {
  			if(is_a($GLOBALS['PAGETMPL'], "Smarty")) {
	  			$GLOBALS['PAGETMPL']->assign($key,$value);
	  			return $value;
	  		} elseif(is_a($GLOBALS['PAGETMPL'], "SmartyTemplateEngine")) {
	  			$GLOBALS['PAGETMPL']->addVar($key,$value);
	  			return $value;
	  		}
  		}
  		return false;
  	}
  	//Sets single variable in page enviroment
  	//Effective if called in viewpage source code
  	//Also these variables can be accessed using $PAGE.<name>
  	function _pageConfig($key,$value=null) {
		//$key=strtoupper($key);
		if($value==null) {
			if(isset($_ENV['PAGEVAR'][$key])) {
				return $_ENV['PAGEVAR'][$key];
			} else {
				return "";
			}
		}
		if($value==-1 && isset($_ENV['PAGEVAR'][$key])) {
			unset($_ENV['PAGEVAR'][$key]);
		}
		$_ENV['PAGEVAR'][$key]=$value;
		return $value;
  	}

  	//Sets single meta variable in page enviroment
  	//Effective if called in viewpage source code
  	function _pageMeta($value=null,$flushAll=false) {
		if($value==null) {
			return $_ENV['PAGECONFIG']['meta'];
		} elseif(is_array($value)) {
			if($flushAll) {
				$_ENV['PAGECONFIG']['meta']=$value;
			} else {
				$_ENV['PAGECONFIG']['meta'][]=$value;
			}
		}
  	}
}
?>
