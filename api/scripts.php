<script language='javascript'>
SiteLocation="<?=SiteLocation?>";
SITENAME="<?=SITENAME?>";
function getServiceCMD(cmd,action,q) {
	return _service(cmd,action,q);
}
function _service(cmd,action,q) {
	sxx="<?=SiteLocation?>services/?site=<?=SITENAME?>";
	<?php
		if(isset($_REQUEST["forsite"]) && strlen($_REQUEST["forsite"])>0)
			echo "sxx+='&forsite={$_REQUEST["forsite"]}';";
	?>
	if(cmd!=null && cmd.length>0) {
		sxx+="&scmd="+cmd;
	}
	if(action!=null && action.length>0) {
		sxx+="&action="+action;
	}
	if(q!=null && q.length>0) {
		sxx+=q;
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

