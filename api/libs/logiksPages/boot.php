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

if(!function_exists("_css")) {
	function _cssLink($cssLnk,$themeName=null) {
		if(is_array($cssLnk) && count($cssLnk)<=0) return false;
		elseif(is_array($cssLnk) && count($cssLnk)==1 && strlen($cssLnk[0])==0) return false;
		elseif(is_string($cssLnk)) $cssLnk=explode(",", $cssLnk);

		if($themeName=="*" || $themeName==null) $themeName=APPS_THEME;

		$lx=_service("resources","","raw")."&type=css&src=".implode(",", $cssLnk)."&theme=$themeName";

		return $lx;
	}
	function _css($cssLnk,$themeName=null,$browser="",$media="") {
		$lx=_cssLink($cssLnk, $themeName);
		if(strlen($lx)<=0) return false;

		if($browser!=null && strlen($browser)>0) {
			echo "<!--[if $browser]>\n";
			echo "<link href='$lx' rel='stylesheet' type='text/css'";
			if($media!=null && strlen($media)>0) echo " media='$media'";
			echo " />\n";
			echo "<![endif]-->\n";
		} else {
			echo "<link href='$lx' rel='stylesheet' type='text/css'";
			if($media!=null && strlen($media)>0) echo " media='$media'";
			echo " />\n";
		}
	}
}
if(!function_exists("_js")) {
	function _jsLink($jsLnk,$themeName=null) {
		if(is_array($jsLnk) && count($jsLnk)<=0) return false;
		elseif(is_array($jsLnk) && count($jsLnk)==1 && strlen($jsLnk[0])==0) return false;
		elseif(is_string($jsLnk)) $jsLnk=explode(",", $jsLnk);

		if($themeName=="*" || $themeName==null) $themeName=APPS_THEME;

		$lx=_service("resources","","raw")."&type=js&src=".implode(",", $jsLnk)."&theme=$themeName";

		return $lx;
	}
	function _js($jsLnk,$themeName=null,$browser="") {
		$lx=_jsLink($jsLnk, $themeName);
		if(strlen($lx)<=0) return false;

		if($browser!=null && strlen($browser)>0) {
			echo "<!--[if $browser]>\n";
			echo "<script src='$lx' type='text/javascript' language='javascript'></script>\n";
			echo "<![endif]-->\n";
		} else {
			echo "<script src='$lx' type='text/javascript' language='javascript'></script>\n";
		}
	}
}
if(!function_exists("getPageConfig")) {
	function getPageConfig($key) {
  		if(isset($_ENV['PAGECONFIG']) && isset($_ENV['PAGECONFIG'][$key])) {
  			return $_ENV['PAGECONFIG'][$key];
  		}
  		return false;
  	}
}
?>
