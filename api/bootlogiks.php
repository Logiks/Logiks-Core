<?php
/*
 * This file contains only the initiating functions or auto functions that are called forward during
 * loading or shutdown sequences for logiks request process and service engine.
 * Most of the functions can be called only once and after that can't be called forth at all.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

include_once dirname(__FILE__). "/libs/logikssingleton.inc";
include_once dirname(__FILE__). "/libs/logiksclassloader.inc";

if(!function_exists("__cleanup")) {
	function __cleanup() {
		if(isset($_ENV['SOFTHOOKS']['SHUTDOWN'])) {
			foreach ($_ENV['SOFTHOOKS']['SHUTDOWN'] as $hook) {
				executeUserParams($hook["FUNC"],$hook["OBJ"]);
			}
		}

		if(function_exists("runHooks")) runHooks("shutdown");

		// saveSettings();
		// saveSiteSettings();
		saveSession();

		MetaCache::getInstance()->dumpAllCache();
		DataCache::getInstance()->dumpAllCache();

		//Database::closeAll();


	 // $error = error_get_last();
	 //    if ($error['type'] == 1) {
	 //        header('HTTP/1.1 500 Internal Server Error');
	 //        $errorMsg = htmlspecialchars_decode(strip_tags($error['message']));
	 //        exit($errorMsg);
	 //    }
	}

	function __cleanupService() {
		if(isset($_ENV['SOFTHOOKS']['SHUTDOWN'])) {
			foreach ($_ENV['SOFTHOOKS']['SHUTDOWN'] as $hook) {
				executeUserParams($hook["FUNC"],$hook["OBJ"]);
			}
		}

		if(function_exists("runHooks")) runHooks("serviceClose");

		// saveSettings();
		// saveSiteSettings();
		saveSession();

		MetaCache::getInstance()->dumpAllCache();
		DataCache::getInstance()->dumpAllCache();

		//Database::closeAll();	
	}

	function logiksRequestPreboot() {
		if(defined('SERVICE_ROOT')) {
			register_shutdown_function("__cleanupService");
		} else {
			register_shutdown_function("__cleanup");
		}

		// platform neurtral url handling
		if(isset($GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI'] ) ) {
			$request_uri = $GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI'];
		} else {
			$request_uri = $GLOBALS['LOGIKS']["_SERVER"]['SCRIPT_NAME'];
			// Append the query string if it exists and isn't null
			if(isset( $GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING'] ) && !empty( $GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING'] ) ) {
				$request_uri .= '?' . $GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING'];
			}
			$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI'] = $request_uri;
		}
		if(!isset($GLOBALS['LOGIKS']["_SERVER"]['ACTUAL_URI'])) {
			$GLOBALS['LOGIKS']["_SERVER"]['ACTUAL_URI']=$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI'];
		}
		if(empty( $GLOBALS['LOGIKS']["_SERVER"]['PHP_SELF'])) {
			$GLOBALS['LOGIKS']["_SERVER"]['PHP_SELF'] = $GLOBALS['LOGIKS']["_SERVER"]['SCRIPT_NAME'];
		}

		$hostProtocol="http://";
		if(isset($GLOBALS['LOGIKS']["_SERVER"]['HTTPS'])) {
			$hostProtocol="https://";
		}
		define('SiteProtocol',str_replace("://","",$hostProtocol));
	}

	function logiksRequestBoot() {
		if(LogiksSingleton::funcCheckout("logiksRequestBoot")) {
			$page=str_replace("?".$GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING'],"",$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI']);
			$page=str_replace(InstallFolder, "", $page);
			if(substr($page, 0,1)=="/") $page=substr($page, 1);
			if($page==null || strlen($page)<=0) {
				$page="home";
			}
			define("PAGE",$page);
			$_SESSION['QUERY']=$_GET;

			$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_PATH']=SiteProtocol."://".$GLOBALS['LOGIKS']["_SERVER"]['HTTP_HOST'].$GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI'];

			$dm=new DomainMap();
			$dm->detect();

			if(!defined("SITENAME")) {
				trigger_error("SITE NOT DEFINED", E_USER_ERROR);
			}

			if(!isset($_SESSION['SESS_USER_ID'])) $_SESSION['SESS_USER_ID']="";
			if(!isset($_SESSION['SESS_USER_NAME'])) $_SESSION['SESS_USER_NAME']="Guest";
		}
	}

	function logiksServiceBoot() {
		if(LogiksSingleton::funcCheckout("logiksServiceBoot")) {
			$dm=new DomainMap();
			$dm->detect();

			if(!defined("SITENAME")) {
				trigger_error("SITE NOT DEFINED", E_USER_ERROR);
			}

			if(!isset($_REQUEST['scmd'])) {
				$rURI=explode("?", $GLOBALS['LOGIKS']["_SERVER"]['REQUEST_URI']);
				$rURI=explode(".", $rURI[0]);
				if(isset($rURI[1])) {
					$_REQUEST['format']=$rURI[1];
				}
				$scmdArr=explode("services/", $rURI[0]);
				if(count($scmdArr)>1) {
					array_shift($scmdArr);
				}
				$scmdArr=explode("/", $scmdArr[0]);

				$_REQUEST['scmd']=$scmdArr[0];
				if(isset($scmdArr[1])) $_REQUEST['action']=$scmdArr[1];
				if(count($scmdArr)>2) {
					array_shift($scmdArr);
					$_REQUEST['actionslug']=implode("-", $scmdArr);
					$_REQUEST['slug']=$scmdArr;
				}
			}

			if(!isset($_REQUEST['action'])) {
				//TODO : GET, POST, PUT, DELETE, PURGE, VIEW
				//		PATCH, COPY, HEAD, OPTIONS, LINK, UNLINK, LOCK, UNLOCK, PROPFIND,
				$_REQUEST['action']="";
			}
			if(!isset($_REQUEST['actionslug'])) $_REQUEST['slugpath']=$_REQUEST['action'];
			if(!isset($_REQUEST['slug'])) $_REQUEST['slug']=array();

			$_REQUEST['site']=SITENAME;

			//Handling Encoded/Encrypted QUERY_STRINGS
			if(isset($_REQUEST['encoded'])) {
				$query=$_REQUEST['encoded'];
				$queryo=decryptURL($query);
				$query=explode("&",$queryo);
				foreach($query as $q) {
					$q=explode("=",$q);
					if(count($q)==0) {
					} elseif(count($q)==1) {
						$_REQUEST[$q[0]]="";
					} else {
						$qs=$q[0];
						unset($q[0]);
						$qv=implode("=",$q);
						$_REQUEST[$qs]=$qv;
					}
				}
				$GLOBALS['LOGIKS']["_SERVER"]['QUERY_STRING'].="&{$queryo}";
			}

			$cmdFormat=explode(",",SUPPORTED_OUTPUT_FORMATS);
			if(!isset($_REQUEST['format'])) {
				$_REQUEST['format']=getConfig("DEFAULT_OUTPUT_FORMAT");
			} else {
				$_REQUEST['format']=strtolower($_REQUEST['format']);
				if(!in_array($_REQUEST['format'], $cmdFormat)) {
					trigger_logikserror(902, E_USER_ERROR);
				} else {
					$_GET['format']=$_REQUEST['format'];
				}
			}
		}
	}

	function loadLogiksBootEngines() {
		if(function_exists("runHooks")) runHooks("enginesStart");
		//Optional Data Components
		//include_once ROOT. "api/libs/logiksDB/boot.php";
		//include_once ROOT. "api/libs/logiksUser/boot.php";

		//initiate the database connection for core database

		//include_once ROOT. "api/libs/logiksORM/boot.php";	//Optional
		//include_once ROOT. "api/libs/uiComponents/boot.php";	//Optional

		loadModule("core",true);
		loadModule(SITENAME,true);

		if(function_exists("runHooks")) runHooks("enginesRunning");
	}

	LogiksSingleton::funcRegister("logiksPreboot");
	LogiksSingleton::funcRegister("logiksRequestBoot");
	LogiksSingleton::funcRegister("logiksServiceBoot");

	LogiksClassLoader::getInstance();
}
?>
