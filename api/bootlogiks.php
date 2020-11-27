<?php
/*
 * This file contains only the initiating functions or auto functions that are called forward during
 * loading or shutdown sequences for logiks request process and service engine.
 * Most of the functions can be called only once and after that can't be called forth at all.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

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

		if(function_exists("saveSettings")) saveSettings();
		if(function_exists("saveSiteSettings")) saveSiteSettings();
		if(function_exists("saveSession")) saveSession();

		if(class_exists("LogiksCache"))  {
			MetaCache::getInstance()->dumpAllCache();
			DataCache::getInstance()->dumpAllCache();
		}

		if(class_exists("Database")) Database::closeAll();

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
		if(function_exists("saveSession")) saveSession();

		if(class_exists("LogiksCache"))  {
			MetaCache::getInstance()->dumpAllCache();
			DataCache::getInstance()->dumpAllCache();
		}

		if(class_exists("Database")) Database::closeAll();
	}

	function logiksRequestPreboot() {
		if(defined('SERVICE_ROOT')) {
			register_shutdown_function("__cleanupService");
		} else {
			register_shutdown_function("__cleanup");
		}
		
		// platform neurtral url handling
		if(_server('REQUEST_URI')) {
			$request_uri = _server('REQUEST_URI');
		} else {
			$request_uri = _server('SCRIPT_NAME');
			// Append the query string if it exists and isn't null
			if(_server('QUERY_STRING') && strlen(_server('QUERY_STRING'))>0 ) {
				$request_uri .= '?' . _server('QUERY_STRING');
			}
			_envData("SERVER",'REQUEST_URI',$request_uri);
		}
		if(!_server('ACTUAL_URI')) {
			_envData("SERVER",'ACTUAL_URI',_server('REQUEST_URI'));
		}
		if(empty( _server('PHP_SELF'))) {
			_envData("SERVER",'PHP_SELF',_server('SCRIPT_NAME'));
		}

		$hostProtocol="http://";
		if(isHTTPS()) {
			$hostProtocol="https://";
		}
		_envData("SERVER",'SiteProtocol',str_replace("://","",$hostProtocol));
		define('SiteProtocol',str_replace("://","",$hostProtocol));
	}

	function logiksSystemBoot() {
		if(LogiksSingleton::funcCheckout("logiksSystemBoot")) {
			$dm=new DomainMap();
			$dm->detect();

			if(!defined("SITENAME")) {
				trigger_error("SITE NOT DEFINED", E_ERROR);
			} else {
				LogiksLogger::getInstance()->activateCurrentSite();
			}
		}
	}

	function logiksRequestBoot() {
		if(LogiksSingleton::funcCheckout("logiksRequestBoot")) {
			_envData("SESSION",'QUERY',$_GET);

			_envData("SERVER",'REQUEST_PATH',SiteProtocol."://"._server('HTTP_HOST')._server('REQUEST_URI'));

			$page=PageIndex::findPageFromURL(SITENAME);
			$page = strip_tags($page);
			_envData("SERVER","PAGE",$page);
			define("PAGE",$page);

			if(!isset($_SESSION['SESS_USER_ID'])) $_SESSION['SESS_USER_ID']="guest";
			if(!isset($_SESSION['SESS_USER_NAME'])) $_SESSION['SESS_USER_NAME']="Guest";
		}
	}

	function logiksServiceBoot() {
		if(LogiksSingleton::funcCheckout("logiksServiceBoot")) {
			if(!isset($_REQUEST['scmd'])) {
				$rURI=explode("?", _server('REQUEST_URI'));
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
				//PATCH, COPY, HEAD, OPTIONS, LINK, UNLINK, LOCK, UNLOCK, PROPFIND,
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
				_envData("SERVER",'QUERY_STRING',"&{$queryo}");
			}

			$cmdFormat=explode(",",SUPPORTED_OUTPUT_FORMATS);
			if(!isset($_REQUEST['format'])) {
				$_REQUEST['format']=strtolower(getConfig("DEFAULT_OUTPUT_FORMAT"));
			} else {
				$_REQUEST['format']=strtolower($_REQUEST['format']);
			}
			if(!in_array($_REQUEST['format'], $cmdFormat)) {
				trigger_logikserror(902, E_USER_ERROR);
			} else {
				$_GET['format']=$_REQUEST['format'];
			}
		}
	}

	function loadLogiksBootEngines() {
		if(function_exists("runHooks")) runHooks("enginesStart");
		
		//Optional Data Components
		include_once ROOT. "api/libs/logiksDB/boot.php";
		include_once ROOT. "api/libs/logiksUser/boot.php";

		$status=getConfig("APPS_STATUS");

		switch ($status) {
			case 'development':
				ini_set('display_errors', 'On');
			    error_reporting(1);
			    if(!defined("MASTER_DEBUG_MODE")) {
			    	define("MASTER_DEBUG_MODE",true);
			    }
			break;
			case 'staging':
				if(isset($_GET['debug']) && $_GET['debug']=="true") {
				    ini_set('display_errors', 'On');
				    error_reporting(1);
				    if(!defined("MASTER_DEBUG_MODE")) {
				    	define("MASTER_DEBUG_MODE",true);
				    }
				}
			break;
			case 'production':
				
			break;
		}

		if(getConfig("LOGIKS_OPTIONAL")) {
			loadLogiksOptional();
		}

		loadModule("core",true);
		loadModule(SITENAME,true);

		if(function_exists("runHooks")) runHooks("enginesRunning");
	}

	function loadLogiksOptional() {
		$logiksOptional=getConfig("LOGIKS_OPTIONAL_COMPONENTS");
		$logiksOptional=explode(",", $logiksOptional);
		foreach ($logiksOptional as $comp) {
			$comp=trim($comp);
			$f=ROOT."api/libs/{$comp}/boot.php";
			if(strlen($comp)>1 && file_exists($f)) {
				include_once $f;
			}
		}
	}

	LogiksSingleton::funcRegister("logiksPreboot");
	LogiksSingleton::funcRegister("logiksSystemBoot");
	LogiksSingleton::funcRegister("logiksRequestBoot");
	LogiksSingleton::funcRegister("logiksServiceBoot");

	LogiksClassLoader::getInstance();
}
?>
