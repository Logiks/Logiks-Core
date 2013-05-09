<?php
require_once ('api/initialize.php');
include ('api/router.php');

if(defined("APPS_THEME")) $css->loadTheme(APPS_THEME);
else define("APPS_THEME","default");

if(!defined("APPS_NAME")) define("APPS_NAME",getConfig("APPS_NAME"));
if(!defined("APPS_VERS")) define("APPS_VERS",getConfig("APPS_VERS"));
$_SESSION["SITELOCATION"]=SiteLocation;

checkDevMode();
runHooks("startup");
log_VisitorEvent();

if(_databus("PAGE_BUFFER_ENCODING")!="plain") startOPBuffer();

$a=isLinkAccessable();
if(!$a) {
	trigger_ForbiddenError("Requested Page is Forbidden From Your Access.");
	exit();
}

if(!(isset($_GET['lgksHeader']) && $_GET['lgksHeader']=="false")) {
	printHTMLPageHeader();
}

$pageLinkPath=getPageToLoad();
if(strlen($pageLinkPath)>0 && file_exists($pageLinkPath)) {
?>
<script language=javascript>
SiteLocation="<?=SiteLocation?>";
SITENAME="<?=SITENAME?>";
function getServiceCMD(cmd) {
	sxx="<?=SiteLocation?>services/?site=<?=SITENAME?>";
	<?php
		if(isset($_REQUEST["forsite"]) && strlen($_REQUEST["forsite"])>0)
			echo "sxx+='&forsite={$_REQUEST["forsite"]}';";
	?>
	if(cmd!=null && cmd.length>0) {
		sxx+="&scmd="+cmd;
	}
	return sxx;
}
function _link(href) {
	if(href.indexOf("http")>=0) {
	} else if(href.indexOf("ftp")>=0) {
	} else if(href.indexOf("/")===0) {
		href="<?=SiteLocation?>"+href.substr(1);
	} else {
		<?php if(getConfig("GENERATED_PERMALINK_STYLE")=="default") { ?>
		if(href.indexOf("page=")>=0) {
			href="<?=SiteLocation."?site=".SITENAME?>&"+href;
		} else {
			href="<?=SiteLocation."?site=".SITENAME?>&page="+href;
		}
		<?php } else { ?>
		href="<?=SiteLocation.SITENAME."/"?>"+href;
		<?php } ?>
	}
	return href;
}
function appMedia(media,userData) {
	if(userData)
		return "<?=SiteLocation.APPS_FOLDER.SITENAME."/".APPS_USERDATA_FOLDER?>"+media;
	else
		return "<?=SiteLocation.APPS_FOLDER.SITENAME."/".APPS_MEDIA_FOLDER?>"+media;
}
</script>
<?php
	runHooks("beforepage");
	$cacheFile=RequestCache::getCachePath("pages");
	switch(getConfig("FULLPAGE_CACHE_ENABLED")) {
		case "true":
			$noCache=explode(",",getConfig("FULLPAGE_CACHE_NOCACHE"));
			$pg=explode("/",$_REQUEST['page']);
			if(in_array($pg[0],$noCache)) {
				include $pageLinkPath;
			} else {
				$a=RequestCache::checkCache("pages",getConfig("FULLPAGE_CACHE_PERIOD"));
				if($a) {
					include_once $cacheFile;
				} else {
					ob_start();
					include $pageLinkPath;
					$data=ob_get_contents();
					ob_flush();
					if(!(isset($_REQUEST['nocache']) && $_REQUEST['nocache']=="true"))
						file_put_contents($cacheFile,$data);
				}
			}
			break;
		default:
			include $pageLinkPath;
			break;
	}
	runHooks("afterpage");
} else {
	trigger_NotFound("Sorry , Page Not Found. Page::" . $current_page);
}
exit();
?>
