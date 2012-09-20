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

printHTMLPageHeader();
$pageLinkPath=getPageToLoad();
if(strlen($pageLinkPath)>0 && file_exists($pageLinkPath)) {
?>
	<script language=javascript>
	function getServiceCMD(cmd) {
		s="<?=SiteLocation?>services/?site=<?=SITENAME?>";
		if(cmd!=null && cmd.length>0) {
			s+="&scmd="+cmd;
		}
		return s;
	}
	</script>
<?php
	runHooks("beforepage");
	include $pageLinkPath;
	runHooks("afterpage");
} else {
	trigger_NotFound("Sorry , Page Not Found. Page::" . $current_page);	
}
exit();
?>
