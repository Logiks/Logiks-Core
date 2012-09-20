<?php
if(!defined('ROOT')) exit('No direct script access allowed');

/*
 * This file helps in generating and understanding of URL structs that
 * are used to access the pages of the site.
 * 
 * Supported URL Configurations
 * 
 * /index.php?site=&page=			:: Native Logiks URL Struct
 * 
 * /site/page/						:: via seo.php
 * /index.php/site/page				:: default
 * 
 * Not Supported URL Configurations
 * 
 * /index.php/page?site=
 * /index.php/site?page=
 * */

if(!function_exists("getQueryParams")) {
	function getQueryParams($basics=false) {
		$params=array();
		if($basics) {
			$params['QUERY_STRING']=$_SERVER['QUERY_STRING'];
		}
		if(isset($_SERVER['PATH_INFO'])) $params['PATH_INFO']=$_SERVER['PATH_INFO'];
		else $params['PATH_INFO']="";
		
		/*if(strlen($params['PATH_INFO'])<=0) {
			$a=$_SERVER['REQUEST_URI'];
			$a=strstr($a,"?");
			$b=dirname($_SERVER['PHP_SELF']);
			$c=substr($a,strlen($b));
			if($c=="/index.php?" || $c=="/index.php") $c="";
			if(!isset($_REQUEST["site"]) && strlen($c)>0) {
				$params['PATH_INFO']=$c;
				$_SERVER['PATH_INFO']=$c;
				
				$pi=explode("/",$_SERVER["PATH_INFO"]);
				if(isset($pi[0]) && strlen($pi[0])<=0) unset($pi[0]);
				if(isset($pi[1]) && !isset($_REQUEST["site"])) {
					$_REQUEST["site"]=$pi[1];
					unset($pi[1]);
				}
				if(!isset($_REQUEST["page"])) {
					$_REQUEST["page"]=implode("/",$pi);
				}
			}
		}*/
		if(strlen($params['PATH_INFO'])>0 && !isset($_REQUEST["site"])) {
			$pi=explode("/",$params['PATH_INFO']);
			if(isset($pi[0]) && strlen($pi[0])<=0) unset($pi[0]);
			if(isset($pi[1]) && !isset($_REQUEST["site"])) {
				$_REQUEST["site"]=$pi[1];
				unset($pi[1]);
			}
			if(!isset($_REQUEST["page"])) {
				$_REQUEST["page"]=implode("/",$pi);
			}
		}
		if(isset($_REQUEST["page"]) && $_REQUEST["page"]=="/") {
			$_REQUEST["page"]="";
		}
		if(!isset($_REQUEST["site"])) {
			if(isset($_SESSION["LGKS_SESS_SITE"])) {
				$_REQUEST["site"]=$_SESSION["LGKS_SESS_SITE"];
			} else {
				$_REQUEST["site"]=DEFAULT_SITE;
			}
		} elseif($_REQUEST["site"]=="/") {
			$_REQUEST["site"]=DEFAULT_SITE;
		}
		$params["site"]=$_REQUEST["site"];
		
		if(!isset($_REQUEST["page"])) {
			if(strlen($params["PATH_INFO"])>0) {
				$r=substr($_SERVER['PATH_INFO'],1,strlen($_SERVER['PATH_INFO'])-1);
				$_REQUEST["page"]=$r;
			} else {
				$_REQUEST["page"]="";
			}
		}
		$params["page"]=$_REQUEST["page"];
		
		foreach(array_keys($_REQUEST) as $a) {
			$params[strtoupper($a)]=$_REQUEST[$a];
		}
		return $params;
	}
	function processUserRequest() {
		$params=getQueryParams();
		$_SESSION['LGKS_SESS_SITE']=$params["SITE"];
		createCookie('LGKS_SESS_SITE',$params["SITE"]);
		return $params;
	}
	
	function generateUserRequestFromAddress($newParams=array()) {
		$params=getQueryParams();
		
		$xArr=array("SITE","PAGE","PATH_INFO","QUERY_STRING");
		
		if(!array_key_exists("SITE", $params)) {$params["SITE"]=DEFAULT_SITE;}
		if(!array_key_exists("PAGE", $params)) {
			if(array_key_exists("PATH_INFO", $params) && strlen($params["PATH_INFO"])>0) {
				$params["PAGE"]=$params["PATH_INFO"];
			} else {
				$params["PAGE"]="";
			}
		}
		foreach($newParams as $a=>$b) {
			if(in_array(strtoupper($a),$xArr)) $a=strtoupper($a);			
			$params[$a]=$b;
		}
		$sArr=array();
		foreach($params as $a=>$b) {			
			if(!in_array($a,$xArr)) {
				array_push($sArr,"$a=$b");
			}
		}
		return generatePageRequest($params["PAGE"], $sArr, $params["SITE"]);
	}
	
	function generatePageRequest($page="", $query="", $site=SITENAME) {
		if(strpos("  ".$site,"http")==2) return $site;
		
		if($site==null || strlen($site)<=0) {
			if(defined("SITENAME")) {
				$site=SITENAME;
			} else {
				$site=DEFAULT_SITE;
			}
		}
		$s=createPrettyPageLink($page, $site);
		
		$ss="";
		if(is_array($query)) {
			$sArr=array();
			foreach($query as $a=>$b) {			
				array_push($sArr,"$a=$b");
			}
			if(sizeOf($sArr)>0) {
				$ss.="&".implode("&",$sArr);
			}
		} else {
			if(strlen($query)>0) $ss .= "&" .$query;
		}
		if(strlen($ss)>0) {
			if(strpos($s,"?")>1) {
				$s.=$ss;
			} else {
				$s.="?".$ss;
				$s=str_replace("?&","?",$s);
			}
		}
		return $s;
	}
	function createPrettyPageLink($page, $site) {
		$s="";
		if(getConfig("GENERATED_PERMALINK_STYLE")=="default") {
			$s=SiteLocation . "index.php?site=$site";
			if(strlen($page)>0) $s.="&page=$page";
		} elseif(getConfig("GENERATED_PERMALINK_STYLE")=="paged") {
			if(strlen($page)>0) {
				$s=SiteLocation . "$site/$page";
			} else {
				$s=SiteLocation . "$site";
			}
		} else {
			$s=SiteLocation . "index.php?site=$site";
			if(strlen($page)>0) $s.="&page=$page";
		}
		return $s;
	}
	function getPrettyPageLinkStyles() {
		$arr=array(
				"Logiks Default Style"=>"default",
				"Directory Style"=>"paged",
			);
		return $arr;
	}
	
	function cryptURL($url) {
		$enc=new LogiksEncryption();
		$url=$enc->encode($url);
		return $url;
	}
	function decryptURL($url) {
		$enc=new LogiksEncryption();
		$url=$enc->decode($url);
		return $url;
	}
}
?>
