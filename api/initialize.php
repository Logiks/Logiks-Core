<?php
/*
 * This file bootstraps the system into a running system by configuring 
 * all the parameters and including the basic required files.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.2
 */
//Check Installation
$bpath=dirname(dirname(__FILE__));
if(!file_exists("$bpath/config/basic.cfg") || !file_exists("$bpath/config/db.cfg")) {
	if(file_exists("$bpath/install/")) {
		echo "Initiating Installation Sequence ...";
		header("Location:install/");
	} else {
		echo "<h1 align=center style='color:#BF2E11'>Error In Logiks Installation Or Corrupt Installation. <br/>Please Contact Admin.</h1>";
	}
	exit();
}

if(!isset($initialized)) {
	ob_start();

	clearstatcache ();
	session_start();

	// platform neurtral url handling
	if(isset($_SERVER['REQUEST_URI'] ) ) {
		$request_uri = $_SERVER['REQUEST_URI'];
	} else {
		$request_uri = $_SERVER['SCRIPT_NAME'];
		// Append the query string if it exists and isn't null
		if(isset( $_SERVER['QUERY_STRING'] ) && !empty( $_SERVER['QUERY_STRING'] ) ) {
			$request_uri .= '?' . $_SERVER['QUERY_STRING'];
		}
		$_SERVER['REQUEST_URI'] = $request_uri;
	}
	if(!isset($_SERVER['ACTUAL_URI'])) {
		$_SERVER['ACTUAL_URI']=$_SERVER['REQUEST_URI'];
	}
	if(empty( $_SERVER['PHP_SELF'])) {
		$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
	}

	define ('ROOT', dirname(dirname(__FILE__)) . '/');

	require_once ROOT. "api/configurator.php";
	
	LoadConfigFile(array(
			ROOT . "config/basic.cfg"=>"",
			ROOT . "config/php.cfg"=>"",
			ROOT . "config/system.cfg"=>"",
			ROOT . "config/developer.cfg"=>"",
			ROOT . "config/security.cfg"=>"",
			ROOT . "config/folders.cfg"=>"",
			ROOT . "config/others.cfg"=>"",
			ROOT . "config/xtras.cfg"=>"",
			ROOT . "config/framework.cfg"=>"",
		));
	
	LoadConfigFile(array(
			ROOT . "config/masters_headers.cfg"=>"",
			ROOT . "config/masters_folders.cfg"=>"",
			ROOT . "config/masters_mobility.cfg"=>"",
			ROOT . "config/masters_page.cfg"=>"",
		));

	LoadConfigFile(ROOT . "config/db.cfg");

	header("X-Powered-By: ".Framework_Title." [".Framework_Site."]",false);

	fixPHPINIConfigs();
	fixLogiksVariables();

	if(!is_dir(ROOT.TMP_FOLDER."sessions/")) {
		$a=mkdir(ROOT.TMP_FOLDER."sessions/", 0777, true);
		if(!$a) {
			die('Failed To Create Session Cache Folders ...');
		}
		chmod(ROOT.TMP_FOLDER."sessions/", 0777);
	}
	session_save_path(ROOT.TMP_FOLDER."sessions/");

	include_once ROOT. "api/database.inc";

	$sysdbLink=new Database();
	$sysdbLink->connect();
	$appdbLink=null;

	include_once ROOT. "api/commons.php";
	include_once ROOT. "config/classpath.php";
	include_once ROOT. "api/libs/URLTools.php";

	include ROOT."config/errors.php";
	include_once ROOT. "api/logdb.php";//For Apps Events
	include_once ROOT. "api/errorhandler.php";//Error Handling System
	if(ERROR_HANDLER=="logiks") {
		loadAutoErrorHandlers();
	}

	$initialized=true;
	include_once ROOT. "api/initfuncs.php";//Special Functions For Logiks Framework's System Operations

	//Handling Encoded/Encrypted QUERY_STRINGS
	if(isset($_REQUEST['encoded'])) {
		$query=$_REQUEST['encoded'];
		$queryo=decryptURL($query);
		$query=explode("&",$queryo);
		foreach($query as $q) {
			$q=explode("=",$q);
			if(count($q)==1) {
				$_REQUEST[$q[0]]="";
			} else {
				$qs=$q[0];
				unset($q[0]);
				$qv=implode("=",$q);
				$_REQUEST[$qs]=$qv;
			}
		}
		$_SERVER['QUERY_STRING'].="&{$queryo}";
	}

	//AppSite Analysis
	$site="";
	if(isset($_SESSION["LGKS_SESS_SITE"])) $site=$_SESSION["LGKS_SESS_SITE"];
	if(isset($_REQUEST['site'])) $site=$_REQUEST['site'];

	if(DOMAIN_CONTROLS_ENABLE=="true") {
		$dm=new DomainMap();
		$_REQUEST["site"]=$dm->checkHost();
	} else {
		if($_REQUEST['site']!=$site || $_REQUEST['site']=="" || !is_dir(ROOT.APPS_FOLDER.$site)) {
			$_REQUEST["site"]=DEFAULT_SITE;
			if(DOMAIN_CONTROLS_REDIRECT=="true") {
				$qp=$_SERVER["QUERY_STRING"];
				if(strlen($qp)<=0) {
					$qp="?site=$dsite";
				} else {
					$qp=str_replace("=".$_REQUEST['site'],"=".$dsite,$qp);
				}
				$l="index.php{$qp}";
				$l=str_replace("?&","?",$l);
				header("Location:$l");
			}
		}
	}
	$siteParams=getQueryParams();
	$site=$_REQUEST["site"];
	if($site==null || strlen($site)<=0) {
		$site=DEFAULT_SITE;
		$_REQUEST["site"]=$site;
		header("Location:index.php?site=".DEFAULT_SITE);
	}

	$_SESSION['LGKS_SESS_SITE']=$site;
	$_REQUEST['site']=$site;
	setcookie('LGKS_SESS_SITE',$site,time()+3600,"/");
	if(!defined("SITENAME")) define("SITENAME",$site);
	if(isset($_GET['page']) && (!isset($_REQUEST['page']) OR strlen($_REQUEST['page'])<=0)) {
		$_REQUEST['page']=$_GET['page'];
	}

	include_once ROOT. "api/loaders.php";
	include_once ROOT. "api/security.php";
	include_once ROOT. "api/usersettings.php";
	include_once ROOT. "api/rolemodel.php";
	include_once ROOT. "api/system.php";
	include_once ROOT. "api/user.php";
	include_once ROOT. "api/uifuncs.php";

	loadHelpers("phpsupport");
	loadHelpers("cookies");
	loadHelpers("mobility");
	loadHelpers("outputbuffer");
	loadHelpers("hooks");

	activateAutoHookSystem();

	$js=JsPHP::singleton();
	$css=CssPHP::singleton();
	$ling=Lingulizer::singleton();
	$cache=CacheManager::singleton();
	$templates=new TemplateEngine();

	loadHelpers("shortfuncs");

	function __cleanup() {
		if(_databus("PAGE_BUFFER_ENCODING")!="plain") {
			printOPBuffer();
		}
		//ob_flush();
		if(_db(true)!=null && _db(true)->isOpen()) _db(true)->close();
		if(_db()!=null && _db()->isOpen()) _db()->close();
		echo "</html>";
	}
	register_shutdown_function("__cleanup");

	if(!isset($_SESSION['SESS_USER_ID'])) $_SESSION['SESS_USER_ID']="Guest";
	if(!isset($_SESSION['SESS_USER_NAME'])) $_SESSION['SESS_USER_NAME']="Guest";

	log_Requests();

	runHooks("postinit");
}
?>
