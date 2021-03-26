<?php
/*
 * This contains all the basic scripts inserted into loading request.
 * This is where PHP and js variables cross each other.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

//CurrentUser="(isset($_SESSION['SESS_USER_ID']))?$_SESSION['SESS_USER_ID']:"" ";
//CurrentRole="(isset($_SESSION['SESS_PRIVILEGE_NAME']))?$_SESSION['SESS_PRIVILEGE_NAME']:"" ";

$refPath = strip_tags(_server('REQUEST_PATH'));
$refPath = preg_replace("/<script.*?\/script>/s", "", $refPath);
?>
<script language='javascript'>
SiteLocation="<?=SiteLocation?>";
SITENAME="<?=SITENAME?>";
PAGE="<?=PAGE?>";
UserDevice="<?=strtoupper(getUserDevice())?>";
UserDeviceType="<?=strtoupper(getUserDeviceType())?>";
LingData={};
<?php
	$ling=Lingualizer::getInstance();
	$json=json_encode($ling->lang);
	if(strlen($json)>2) echo 'LingData='.$json.';';
?>

function getServiceCMD(cmd,action,q) {
	return _service(cmd,action,null,q);
}
function _service(cmd,action,format,q) {
	if(cmd==null || cmd.length<=0) {
		return "";
	}
	sxx="<?=SiteLocation?>services/"+cmd+"?site=<?=SITENAME?>&syshash=<?=getSysHash()?>";
	<?php
		if(isset($_REQUEST["forsite"]) && strlen($_REQUEST["forsite"])>0) {
			echo "sxx+='&forsite={$_REQUEST["forsite"]}';";
		} elseif(defined("DOMAIN_URI") && strlen(DOMAIN_URI)>1) {
			echo "sxx+='&forsite="+SITENAME+"';";
		}
	?>
	
	if(action!=null && action.length>0) {
		sxx+="&action="+action;
	}
	if(format!=null && format.length>0) {
		sxx+="&format="+format;
	}
	if(q!=null && q.length>0) {
		sxx+=q;
	}
	return sxx;	
}
function _link(href) {
	<?php
		if(defined("DOMAIN_URI") && strlen(DOMAIN_URI)>1) {
	?>
	var SITELINK = "<?=SiteLocation.substr(DOMAIN_URI, 1)?>/";
	<?php
		} else {
	?>
	var SITELINK = "<?=SiteLocation?>";
	<?php
		}
	?>
	
	if(href==null) href="<?=$refPath?>";
	if(href.indexOf("http")>=0 ||
			href.indexOf("https")>=0 ||
			href.indexOf("ftp")>=0) {
	} else if(href.indexOf("/")===0) {
		href=SITELINK+href.substr(1);
	} else {
		href=SITELINK+href;
	}
	<?php
		if(SITENAME!=WEBDOMAIN) {
			echo 'if(href.indexOf("?")>1) {';
				echo "href+='&site=".SITENAME."';";
			echo '} else {';
				echo "href+='?site=".SITENAME."';";
			echo '}';
		}
	?>
	if(href.indexOf("?")<0) {
		href+="?";
	}
	<?php
		if(isset($_REQUEST["forsite"]) && strlen($_REQUEST["forsite"])>0) {
			echo "href+='&forsite={$_REQUEST["forsite"]}';";
		}
	?>
	return href;
}
function appMedia(media,userData) {
	<?php if(defined("APPS_USERDATA_FOLDER")) { ?>
	if(userData)
		return "<?=SiteLocation.APPS_FOLDER.SITENAME."/".APPS_USERDATA_FOLDER?>"+media;
	else
		return "<?=SiteLocation.APPS_FOLDER.SITENAME."/".APPS_MEDIA_FOLDER?>"+media;
	<?php } else { ?>
		return "<?=SiteLocation."/".MEDIA_FOLDER?>"+media;
	<?php } ?>
}
function _ling(txt,forceWord,toLang) {
	if(txt==null) return "";
	if(forceWord==null) forceWord=true;
	if(toLang==null) toLang="<?=getConfig("DEFAULT_LOCALE")?>";

	txtTemp=txt.toLowerCase();

	if(LingData[txtTemp]!=null) return LingData[txtTemp];
	else return txt;
}
</script>
