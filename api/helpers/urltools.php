<?php
/*
 * This file helps in generating and understanding of URL structs that
 * are used to access the pages of the site.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getQueryParams")) {
	function getQueryParams($map=null) {
		$params=array();
		$params['site']=SITENAME;
		$params['page']=PAGE;
		$slug=explode("/", PAGE);

		if(count($slug)>0) {
			$params['basepage']=$slug[0];
		} else {
			$params['basepage']=$params['page'];
		}
		array_shift($slug);
		if(is_array($map) && count($map)>0) {
			$params['slug']=array();
			array_unshift($map, "");
			foreach ($map as $nx=>$key) {
				if($nx==0) continue;
				if(isset($slug[$nx]) && strlen($slug[$nx])>0) $params['slug'][$key]=$slug[$nx];
			}
		} else {
			$params['slug']=$slug;
		}

		$params['query']=_session('QUERY');

		return $params;
	}

	function getPrettyLink($page=PAGE, $site=SITENAME,$query=null) {
		if(substr($page, 0,1)=="/") $page = substr($page, 1);

		$url=SiteLocation.$page;
		
		if(defined("DOMAIN_URI") && strlen(DOMAIN_URI)>1) {
			$url=SiteLocation.substr(DOMAIN_URI, 1)."/".$page;
		}

		// if($query==null && !is_array($query)) {
		// 	$query=_session('QUERY');
		// } elseif(is_string($query)) {
		// 	trigger_logikserror('$query expected array got string');
		// }
		if($query==null) $query=[];
		elseif(is_string($query)) {
			$dxq=explode("&", $query);
			$query=[];
			foreach ($dxq as $key => $vx) {
				if(strlen($vx)<=0) continue;
				$vx=explode("=", $vx);
				if(!isset($vx[1])) $vx[1]="";
				$query[$vx[0]]=$vx[1];
			}
		}

		if(isset($query['site'])) unset($query['site']);

		if($site!=null && $site!=WEBDOMAIN) {
			$query['site']=$site;
		}
		
		if(isset($_REQUEST['forsite']) && !isset($query['forsite'])) $query['forsite']=$_REQUEST['forsite'];

		if($query!=null && (is_array($query) && count($query)>0)) {
			$url.="?".http_build_query($query);
		}
		return $url;
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

	function getRelativePathToROOT($file) {
		$basepath="";

		$s=str_replace(ROOT,"",dirname($file) . "/");
		$s=str_replace("//","/",$s);
		for($j=0;$j<substr_count($s,"/");$j++) {
			$basepath.="../";
		}

		return $basepath;
	}

	function getRequestPath() {
		return dirname('http://' . _server('HTTP_HOST') . _server('PHP_SELF')).'/';
	}
}
?>
