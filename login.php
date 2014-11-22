<?php
require_once ('api/initialize.php');

if(session_login_check()) {
	header("location: index.php?site=".$_SESSION['SESS_LOGIN_SITE']);
}

LoadConfigFile(ROOT . "config/login.cfg");

$_REQUEST['page']="login";

if(isset($_REQUEST['errormsg'])) $errormsg=$_REQUEST['errormsg'];
if(isset($_SESSION['SESS_ERROR_MSG'])) {
	$errormsg=$_SESSION['SESS_ERROR_MSG'];
	unset($_SESSION['SESS_ERROR_MSG']);
}
//else $errormsg="Please fill the appropriate domain details.";
if(isset($_REQUEST['relink'])) $relinkPage=$_REQUEST['relink'];
if(isset($_SESSION['LOGIN_RELINK'])) {
	$relinkPage=$_SESSION['LOGIN_RELINK'];
	unset($_SESSION['LOGIN_RELINK']);
} else $relinkPage="*";

function createAppNameArray($dirs=null) {
	if($dirs==null) {
		$dirs=scandir(ROOT.APPS_FOLDER);
		unset($dirs[0]);unset($dirs[1]);
	}
	if(count($dirs)<=0) return array();
	$domains=array();
	foreach($dirs as $a) {
		$f=ROOT.APPS_FOLDER."{$a}/apps.cfg";
		if(file_exists($f)) {
			$data=file_get_contents($f);
			$data=str_replace("\n","=",$data);
			$data=explode("=",$data);
			$n=array_search("APPS_NAME",$data);
			if($n>=0) {
				if(!array_key_exists($a,$domains)) $domains[$a]=$data[$n+1];
			}
		} else {
			if(!array_key_exists($a,$domains)) $domains[$a]=$a;
		}
	}
	return $domains;
}

//Define $domains :: All Domains
if(getConfig("LOGIN_SELECTOR_SITES")=="all") {
	$domains=createAppNameArray(null);
} elseif(getConfig("LOGIN_SELECTOR_SITES")=="ondemand") {
	if(isset($_REQUEST['sites'])) {
		$domains=explode(",",$_REQUEST['sites']);
		if(!isset($_REQUEST['site'])) {
			$_REQUEST['site']=$domains[0];
		} elseif(!in_array($_REQUEST['site'],$domains)) {
			array_push($domains,$_REQUEST['site']);
		}
		$site=$_REQUEST['site'];
	} elseif(isset($_REQUEST['site'])) {
		$domains=explode(",",$_REQUEST['site']);
		$site=$domains[0];
	} else {
		$_REQUEST['site']=DEFAULT_SITE;
		$domains=explode(",",$_REQUEST['site']);
		$site=DEFAULT_SITE;
	}
	if(!in_array("admincp",$domains)) array_push($domains,"admincp");
	if(!in_array("cms",$domains)) array_push($domains,"cms");
	$domains=createAppNameArray($domains);
} elseif(getConfig("LOGIN_SELECTOR_SITES")=="restrictive") {
	$domains=array();
	$dm=new DomainMap($sysdbLink);
	$dmArr=$dm->getDomainList();
	$server=strtoupper($_SERVER["HTTP_HOST"]);
	$server=str_replace("WWW.","",$server);
	
	if(array_key_exists($server,$dmArr)) {
		$xs=$dmArr[$server];
		if(!in_array($xs['appsite'],$domains)) array_push($domains,$xs['appsite']);
		$site=$xs['appsite'];
	} else {
		if(isset($_REQUEST['site'])) {
			array_push($domains,$_REQUEST['site']);
			$site=$_REQUEST['site'];
		}
	}
	if(!in_array("admincp",$domains)) array_push($domains,"admincp");
	if(!in_array("cms",$domains)) array_push($domains,"cms");
	if(isset($_REQUEST['site']) && in_array($_REQUEST['site'],$domains)) {
		$site=$_REQUEST['site'];
	}
	$domains=createAppNameArray($domains);
} elseif(getConfig("LOGIN_SELECTOR_SITES")=="locked") {
	$domains=array();
	$dm=new DomainMap($sysdbLink);
	$dmArr=$dm->getDomainList();
	$server=strtoupper($_SERVER["HTTP_HOST"]);
	$server=str_replace("WWW.","",$server);
	
	if(array_key_exists($server,$dmArr)) {
		$xs=$dmArr[$server];
		if(!in_array($xs['appsite'],$domains)) array_push($domains,$xs['appsite']);
		$site=$xs['appsite'];
	} else {
		if(isset($_REQUEST['site'])) {
			array_push($domains,$_REQUEST['site']);
			$site=$_REQUEST['site'];
		}
	}
	if(isset($_REQUEST['site']) && in_array($_REQUEST['site'],$domains)) {
		$site=$_REQUEST['site'];
	}
	$domains=explode(",",$site);
} else {
	$domains=createAppNameArray(null);
}

$webpage='http://' . $_SERVER['HTTP_HOST'] . dirname ($_SERVER['PHP_SELF']) . "/index.php";
$loginpage='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

//Do APP Specific Functionalities Like Themes/Selectors/Favicon,title,Site, Author
define("APPROOT",ROOT . APPS_FOLDER . $site . "/");
define("BASEPATH",APPS_FOLDER . $site . "/");
$apps_cfg=APPROOT."apps.cfg";
$apps_login_cfg=APPROOT."config/login.cfg";
$apps_dir_cfg=APPROOT."config/folders.cfg";

if(!(file_exists(APPROOT) && is_dir(APPROOT))) {
	trigger_NotFound("Site Not Found <b>'$site'</b>");
	exit();
}

if(file_exists($apps_cfg)) {
	LoadConfigFile($apps_cfg);
}
if(file_exists($apps_login_cfg)) {
	LoadConfigFile($apps_login_cfg);
}
if(file_exists($apps_dir_cfg)) {
	LoadConfigFile($apps_dir_cfg);
}

if(strlen(getConfig("APPS_NAME"))>0 && !defined("APPS_NAME")) define("APPS_NAME",getConfig("APPS_NAME"));
if(strlen(getConfig("APPS_VERS"))>0 && !defined("APPS_VERS")) define("APPS_VERS",getConfig("APPS_VERS"));
if(strlen(getConfig("APPS_COMPANY"))>0 && !defined("APPS_COMPANY")) define("APPS_COMPANY",getConfig("APPS_COMPANY"));
if(strlen(getConfig("APPS_COPYRIGHT"))>0 && !defined("APPS_COPYRIGHT")) define("APPS_COPYRIGHT",getConfig("APPS_COPYRIGHT"));

if(defined("LINGUALIZER_DICTIONARIES")) $ling->loadLocaleFile(LINGUALIZER_DICTIONARIES);

if(defined("APPS_THEME") && file_exists(ROOT.THEME_FOLDER.APPS_THEME."/login.css")) {
	$css->loadTheme(APPS_THEME);
} elseif(file_exists(ROOT.THEME_FOLDER.$GLOBALS['CONFIG']['LOGIN_THEME']."/login.css")) {
	$css->loadTheme($GLOBALS['CONFIG']['LOGIN_THEME']);
} else {
	$css->loadTheme(DEFAULT_THEME);
}

checkSiteMode($site);
checkDevMode();

/*if(ENABLE_AUTO_PCRON=="true") {
	$_REQUEST['pcron_key']=PCRON_KEY;
	include "pcron.php";
}*/

if((defined("ACCESS") && ACCESS!='private') && !(is_array($domains) && sizeOf($domains)>1)) {
	header("Location:index.php?site=$site");
	exit("Relocating To Main Page");
}

if(!defined("APPS_NAME")) define("APPS_NAME",$GLOBALS['CONFIG']["APPS_NAME"]);
if(!defined("APPS_VERS")) define("APPS_VERS",$GLOBALS['CONFIG']["APPS_VERS"]);

$site_selector="<option value='$site' selected>".strtoupper(APPS_NAME)."</option>";
if(isset($domains) && is_array($domains)) {
	foreach($domains as $a=>$b) {
		$t=$b;
		if(!is_numeric($a)) {
			$t=$a;
		}
		if($t!=$site) $site_selector.="<option value='$t'>".strtoupper($b)."</option>";
	}
}
$device="pc";
if(getConfig("USE_MOBILITY_LOGIN")=="true") {
	$device=getUserDeviceType();
} else {
	$device="pc";
}
$loginTemplate="";
$loginPgArr=array();

$p1=getConfig("LOGIN_PAGE_".strtoupper($device));
$p2=getConfig("LOGIN_PAGE");

if(defined("APPROOT") && defined("APPS_PAGES_FOLDER")) {
	if(strlen($p1)>0) {
		$loginPgArr[]=$p1;
		$loginPgArr[]=APPROOT.APPS_PAGES_FOLDER."{$p1}.php";
	}
	if(strlen($p2)>0) {
		$loginPgArr[]=$p2;
		$loginPgArr[]=APPROOT.APPS_PAGES_FOLDER."{$p2}.php";
	}
	$loginPgArr[]=APPROOT.APPS_PAGES_FOLDER.GLOBAL_LOGIN_PAGE."-$device.php";
	$loginPgArr[]=APPROOT.APPS_PAGES_FOLDER.GLOBAL_LOGIN_PAGE.".php";
}
$loginPgArr[]=ROOT.PAGES_FOLDER."login.php";
foreach ($loginPgArr as $pg) {
	if(strpos($pg,".php")===false) {
		if(isLogiksLayout($pg)) {
			$loginTemplate=$pg;
			break;
		}
	} elseif(file_exists($pg)) {
		$loginTemplate=$pg;
		break;
	}
}
if(strlen($loginTemplate)<=0) {
	$loginTemplate=ROOT.PAGES_FOLDER."login.php";
}
//printArray($loginPgArr);exit($loginTemplate);

$bodyContext="";
if(getConfig("LOCK_CONTEXTMENU")=="true") $bodyContext.="oncontextmenu='return false' ";
if(getConfig("LOCK_SELECTION")=="true") $bodyContext.="onselectstart='return false' ";
if(getConfig("LOCK_MOUSEDRAG")=="true") $bodyContext.="ondragstart='return false' ";

if(getConfig("PAGE_BUFFER_ENCODING")!="plain") startOPBuffer();

setConfig("TITLE_FORMAT","#APPS_NAME# :: Login");
printHTMLPageHeader();

$css->TypeOfDispatch("Tagged");
$css->display();
$js->TypeOfDispatch("Tagged");
$js->display();
?>
</head>
<body <?=$bodyContext?>>
<?php
	runHooks("beforepageLogin");
	if(file_exists($loginTemplate)) {
		include $loginTemplate;
	} elseif(strpos($loginTemplate,".php")===false) {
		generatePageLayout($loginTemplate);
	} else {
		echo "<style>body {overflow:hidden;}</style>";
		dispErrMessage("Login Page Missing For Site <u>{$site}</u>, It May Have Been Moved.","404:Login Not Found",
			404,"media/images/unknown.png");
	}
	runHooks("afterpageLogin");
?>
</body>
</html>
<?php
if(getConfig("PAGE_BUFFER_ENCODING")!="plain") printOPBuffer();
if(getConfig("ALLOW_PERSISTENT_LOGIN")=="false") {
	session_destroy();
}
?>
