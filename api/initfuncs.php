<?php
//This class is used for Storing Special Logiks PHP Functions.
//This is one time called by initialize.php only. 
//No other file should cak these functions as that may be fatal.
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("redirectToApp")) {
	function __autoload($class) {
		global $classpath;
		if(strpos(strtolower(" " . $class),"smarty_")>0) {
			if(function_exists("smartyAutoload")) {
				smartyAutoload($class);
				return;
			}
		}
		$name1=$class;
		$name2=strtolower($class);
		
		$found=false;	
		foreach($classpath as $p) {
			$s1=ROOT.$p.$name1.".inc";
			$s2=ROOT.$p.$name2.".inc";
			if(file_exists($s1)) {
				include_once $s1;
				$found=true;
				break;
			} elseif(file_exists($s2)) {			
				include_once $s2;
				$found=true;
				break;
			}
		}		
		if(!$found) trigger_error("$class Class Not Found within given paths");
	}
	
	function redirectToApp($site=null,$withQueryString=true) {
		if($site==null) {
			return;
		}
		$ss="site=$site";
		if($withQueryString && strlen($_SERVER["QUERY_STRING"])>0) 
			$ss.="&".$_SERVER["QUERY_STRING"];
		$ss=str_replace('&&','&',$ss);
		echo "<h5>Redirecting To Application ...</h5>";
		header("Location:index.php?$ss");
		exit();
	}
	function redirectTo($link,$msg="Please Wait While We Redirect You To Main Site") {
		global $css,$js;
		$loading_msg=$msg;
		$p=ROOT . PAGES_FOLDER  . $GLOBALS['CONFIG']["LOADER_LAYOUT"] . ".php";
		include $p;
		
		$n=getConfig("ERROR_REDIRECTION_LEVEL");
		$sp="self";
		if(is_numeric($n)) {
			for($i=0;$i<$n;$i++) {
				$sp.=".parent";
			}
			$sp.=".location";
			echo "<script language='javascript'> $sp = '$link'; </script>";
		} else {
			$sp=$n;
			echo "<script language='javascript'> if({$sp}==null) top.location = '$link'; else {$sp}.location = '{$link}'; </script>";
		}
		exit();
	}
}
if(!function_exists("printHTMLPageHeader")) {
	function printHTMLPageHeader() {
		if(!isset($_REQUEST["page"]) || strlen($_REQUEST["page"])==0) {
			$_REQUEST["page"]=getConfig("LANDING_PAGE");
		}
		$meta=getMetaTags();
		
		$title=$meta['title'];
		$descs=$meta['description'];
		$keywords=$meta['keywords'];
		$robots=$meta['robots'];
		
		$favicon=getConfig("ICON");
		if(file_exists(APPROOT.getConfig("ICON"))) {
			$favicon=BASEPATH.getConfig("ICON");
		}
		
		$defaultLocale=getConfig('DEFAULT_LOCALE');
		$expires=getConfig('PAGE_EXPIRES');
		$lastModified=getConfig('PAGE_LAST_MODIFIED');
		$pageCacheControl=getConfig('PAGE_CACHE_CONTROL');
		$pagePragma=getConfig('PAGE_PRAGMA');
		
		$devAuthor=getConfig("DEV_AUTHOR");
		$devMail=getConfig("DEV_MAIL");
		$devCopy=getConfig("DEV_COPYRIGHT");
		
		$appsCompany=getConfig("APPS_COMPANY");
		$appsCopy=getConfig("APPS_COPYRIGHT");
		$appsMail=getConfig("WEBMASTER_EMAIL");
		
		$refresh=getConfig("PAGE_REFRESH");
		
		if(strlen($expires)>0) {
			$expires=gmdate($expires);
		}
		if(strlen($lastModified)>0) {
			$lastModified=gmdate($lastModified);
		}
		
		$devCopy=str_replace("'","\"",$devCopy);
		$appsCopy=str_replace("'","\"",$appsCopy);
		
		$headerHTML="";
		$headerHTML.="<!DOCTYPE ".getConfig('HEADER.DOCTYPE').">\n";
		$headerHTML.="<html ".getConfig('HEADER.HTML_ATTRIBUTES').">\n";
		$headerHTML.="\t<title>$title</title>\n";
		$headerHTML.="\t<meta http-equiv='content-type' content='text/html;charset=".getConfig('PAGE_ENCODING')."' />\n";
		
		if(isset($_SERVER['REQUEST_PATH']) && strlen($_SERVER['REQUEST_PATH'])>0)
			$headerHTML.="\t<link rel='shortcut icon' type='image/x-icon' href='".SiteLocation."$favicon' />\n";
		elseif(isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])>0)
			$headerHTML.="\t<link rel='shortcut icon' type='image/x-icon' href='".SiteLocation."$favicon' />\n";
		else
			$headerHTML.="\t<link rel='shortcut icon' type='image/x-icon' href='$favicon' />\n";
		
		$headerHTML.="\n\t<meta name='generator' content='".Framework_Title . ' v' . Framework_Version . " [".Framework_Site."]"."' />\n\n";
		
		$headerHTML.="\t<meta name='viewport' content='width=device-width'>\n";
		$headerHTML.="\t<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>\n\n";
		
		$headerHTML.="\t<meta http-equiv='content-language' content='$defaultLocale' />\n";
		$headerHTML.="\t<meta http-equiv='cache-control' content='$pageCacheControl'>	\n";
		$headerHTML.="\t<meta http-equiv='pragma' content='$pagePragma'>\n";
		if(strlen($expires)>0) {
			$headerHTML.="\t<meta http-equiv='expires' content='$expires GMT'>\n";
		}
		if(strlen($lastModified)>0) {
			$headerHTML.="\t<meta http-equiv='last-modified' content='$lastModified GMT'>\n";
		}
		if(strlen($refresh)>0) {
			$headerHTML.="\t<meta http-equiv='refresh' content='$refresh' />\n";
		}
		
		if(getConfig("SHOW_DEVELOPER_META")=="true") {
			$headerHTML.="\n\t<meta name='author' lang='$defaultLocale' content='$devAuthor; e-mail:$devMail' />\n";	
			$headerHTML.="\t<meta name='copyright' lang='$defaultLocale' content='$devCopy' />\n\n";
		} else {
			$headerHTML.="\n";
		}
		$headerHTML.="\t<meta name='author' lang='$defaultLocale' content='$appsCompany; e-mail:$appsMail' />\n";	
		$headerHTML.="\t<meta name='copyright' lang='$defaultLocale' content='$appsCopy' />\n\n";
		
		if(strlen($descs)>0) {
			$headerHTML.="\t<meta name='description' content='$descs' />\n";
		}
		if(strlen($keywords)>0) {
			$headerHTML.="\t<meta name='keywords' content='$keywords' />\n";
		}
		if(strlen($robots)>0) {
			$headerHTML.="\t<meta name='robots' content='$robots' />\n";
		}
		
		$headerHTML.="\t\n";
		
		if(getConfig("PRINT_PHP_HEADERS")=="true") {
			header( "Content-language: $defaultLocale" );
			header( "Cache-Control: no-store, must-revalidate" );
			if(strlen($pageCacheControl)>0) header( "Cache-Control: $pageCacheControl", false );
			if(strlen($pagePragma)>0) header( "Pragma: $pagePragma" );
			if(strlen($expires)>0) header( "Expires: $expires GMT" );
			if(strlen($lastModified)>0) header( "Last-Modified: $lastModified GMT" );
		}
		
		if(getConfig("PRINT_METATAGS")=="true") {
			$headerHTML.=$meta['metatags'];
		}
		echo $headerHTML;
	}
	
	function getMetaTags() {
		if(isset($_REQUEST['page'])) $page=$_REQUEST['page'];
		else return "";
		if(!isset($_GET['page'])) $_GET['page']=$_REQUEST['page'];
		
		$bpath="";
		if(defined("APPROOT")) $bpath.=APPROOT;
		if(defined("APPS_CONFIG_FOLDER"))  $bpath.=APPS_CONFIG_FOLDER;
		else $bpath.="config/";
		$bpath.="meta/";
		
		$GET=$_GET;
		unset($GET['site']);unset($GET['forsite']);
		natcasesort($GET);
		$metaURL=array();
		foreach($GET as $a=>$b) {array_push($metaURL,"$a=$b");}
		$metaURL=(implode("&",$metaURL));
		$metaFiles=array("{$bpath}{$metaURL}.json","{$bpath}{$page}.json");
		
		$meta=array();
		foreach($metaFiles as $path) {
			if(file_exists($path)) {
				$data=file_get_contents($path);
				if(strlen($data)>0) {
					$meta=json_decode($data,true);
				}
				break;
			}
		}
		
		if(count($meta)<=0) {
			$meta['title']=getConfig("TITLE_FORMAT");
			$meta['keywords']=getConfig("KEYWORDS");
			$meta['description']=getConfig("DESCRIPTION");
			$meta['robots']=getConfig("ROBOTS");
			$meta['metatags']="";
		} else {
			if(!isset($meta['keywords']) || strlen($meta['keywords'])<=0) {
				$meta['keywords']=getConfig("KEYWORDS");
			}
			if(!isset($meta['description']) || strlen($meta['description'])<=0) {
				$meta['description']=getConfig("DESCRIPTION");
			}
			if(!isset($meta['robots']) || strlen($meta['robots'])<=0) {
				$meta['robots']=getConfig("ROBOTS");
			}
		}
		$title=$meta['title'];
		if(strpos("$$".$title,"#")>=2) {
			$title=_replace($title);
		} elseif(strpos($title,")")>=2) {
			$title=eval("return $title;");
		}
		$meta['title']=$title;
		return $meta;
	}
}
?>
