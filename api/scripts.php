<?php
/*
 * This contains all the basic scripts inserted into loading request.
 * This is where PHP and js variables cross each other.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');
?>
<script language='javascript'>
SiteLocation="<?=SiteLocation?>";
SITENAME="<?=SITENAME?>";
PAGE="<?=PAGE?>";
UserDevice="<?=strtoupper(getUserDevice())?>";
UserDeviceType="<?=strtoupper(getUserDeviceType())?>";
CurrentUser="<?=(isset($_SESSION['SESS_USER_ID']))?$_SESSION['SESS_USER_ID']:"" ?>";
CurrentRole="<?=(isset($_SESSION['SESS_PRIVILEGE_NAME']))?$_SESSION['SESS_PRIVILEGE_NAME']:"" ?>";
function getServiceCMD(cmd,action,q) {
	return _service(cmd,action,null,q);
}
function _service(cmd,action,format,q) {
	if(cmd==null || cmd.length<=0) {
		return "";
	}
	sxx="<?=SiteLocation?>services/"+cmd+"?site=<?=SITENAME?>&syshash=<?=md5(session_id())?>";
	<?php
		if(isset($_REQUEST["forsite"]) && strlen($_REQUEST["forsite"])>0)
			echo "sxx+='&forsite={$_REQUEST["forsite"]}';";
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
	if(href==null) href="<?=_server('REQUEST_PATH')?>";
	if(href.indexOf("http")>=0) {
	} else if(href.indexOf("ftp")>=0) {
	} else if(href.indexOf("/")===0) {
		href="<?=SiteLocation?>"+href.substr(1);
	} else {
		href="<?=SiteLocation?>"+href;
	}
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
</script>

