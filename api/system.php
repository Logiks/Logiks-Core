<?php
/*
 * This centralizes all the system level functions.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getRequestTime")) {
  loadHelpers("pathfuncs");

  function redirectTo($relink=null,$carryForwardQuery=true) {
    echo "<h5>Redirecting To Application ...</h5>";

    if(substr($relink, 0,7)=="http://" || substr($relink, 0,8)=="https://") {
      header("Location:$relink");
    } else {
      $relink=getPrettyLink($relink);
      header("Location:$relink");
    }
		exit();
	}

  //Quick hack to delete the cookies at runtime and impact tthe system as well.
  function deleteCookies($name) {
		setcookie($name, "", time()-1000000000);
		if(isset($_COOKIE[$name])) unset($_COOKIE[$name]);
	}
  //Checks is localhost
	function isLocalhost() {
		$client=$_SERVER['REMOTE_ADDR'];
		$server=$_SERVER['SERVER_ADDR'];
		if($client==$server) return true;
		else return false;
	}
	//Used for converting filepath to direct webpath, $refs :: __FILE__
	function getLocation($refs) {
		$x=SiteLocation . "/" .str_replace(ROOT,"",$refs);
		return $x;
	}
  //Clears the permission cache
  function flushPermissions($site=SITENAME) {
		$f=ROOT.CACHE_PERMISSIONS_FOLDER."{$site}/";
		if(is_dir($f)) {
			$fs=scandir($f);
			foreach($fs as $a) {
				if($a=="." || $a=="..") continue;
				unlink("{$f}{$a}");
			}
			return true;
		}
		return false;
	}
  //Used for debugging
  function getFunctionCaller() {
		$trace=debug_backtrace();
		array_shift($trace);//Remove Self
		//array_shift($trace);//Remove Parent
		$caller=array_shift($trace);//Caller
		return $caller;
	}
  //Returns the list of supported pages for Logiks
  function getSupportedPages($page=null) {
    if($page==null) $page="page";
		$arr=array(
      "pages"=>array(
        "{$page}.php","{$page}.htm","{$page}.html","{$page}.tpl"
      ),
      "config"=>array(
        "{$page}.cfg","{$page}.json","{$page}.lst"
      )
    );
		return $arr;
	}
	//Logsout the session cleanly
	//TODO :: DATABASE LEVEL LOGOUT
  	function logoutSession($msg=null,$relink=null) {
		session_destroy();
	    if($relink==null) {
	      $relink=SiteLocation . "login.php";
	  		if(defined("SITENAME")) $relink.="?site=".SITENAME;
	  		if($msg!=null && strlen($msg)>0) {
	  			$_SESSION['SESS_ERROR_MSG']=$msg;
	  			$relink.="&errormsg=$msg";
	  		} else {
	        $msg="SESSION Expired. Going To Login Page";
	      }
	  		redirectTo($relink,$msg);
	    } else {
	      redirectTo($relink,$msg);
	    }
	}
	//Just returns the session site, continued from old system.
	function getSessionSite() {
		return SITENAME;
	}
	//Gets and lists the Sources/Handlers that can be used for the activity.
	//It helps when a single handler is to be used where multiple handlers are available.
	//Can be accessed using handler# at various places.
	//eg. editors :: multiple are available, single to be used.
	function get_handlers($activity,$forceReload=false) {
		if($activity==null || strlen($activity)<=0) return array();
		if(!isset($GLOBALS['DEFAULT_HANDLERS']) || $forceReload) {
			$jsondb=new SimpleJSONDB(ROOT.MISC_FOLDER."jsondb/");
			$dmArr=$jsondb->getAll("default_handlers");
			if(!$dmArr) {
				$dmArr=array();
			}
			$GLOBALS['DEFAULT_HANDLERS']=$dmArr;
		}
		if(isset($GLOBALS['DEFAULT_HANDLERS'][$activity]) && $GLOBALS['DEFAULT_HANDLERS'][$activity]["enabled"]) {
			return $GLOBALS['DEFAULT_HANDLERS'][$activity]["opts"];
		} else return array();
	}

	//This function can execute user functions and methods unviversally for automatic execution from strings.
	function executeUserParams($func,$obj=null) {
		if(function_exists($func)) {
			call_user_func($func,$obj);
			return true;
		} elseif($obj!=null && is_object($obj)) {
			call_user_method($func,$obj);
			return true;
		}
		return false;
	}

	//Gets the time difference between current time and time of request.
	function getRequestTime() {
		return (microtime(true)-$_SESSION['REQUEST_START']);
	}
}
?>
