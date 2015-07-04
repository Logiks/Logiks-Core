<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($MODULE_PARAMS[1]['menuid'])) {
	echo "No MenuID Defined.";
	exit();
}

include_once dirname(__FILE__)."/api.php";

if(isset($MODULE_PARAMS[1]['menuAutoGroupFile'])) {
	$menuAutoGroupFile=$MODULE_PARAMS[1]['menuAutoGroupFile'];
} else $menuAutoGroupFile=null;

if(isset($MODULE_PARAMS[1]['noScript'])) {
	$noScript=$MODULE_PARAMS[1]['noScript'];
} else $noScript=false;

if(isset($MODULE_PARAMS[1]['menuType'])) {
	$menuType=$MODULE_PARAMS[1]['menuType'];
} else $menuType="sidebar";

$params=$MODULE_PARAMS[1];


$nav=getNav($params['menuid'],$menuAutoGroupFile,$params);
//printArray($nav);

$atl=new ArrayToList();
$atl->printTitle(true);
$s=$atl->getTree($nav,'data',0,false);
$s=substr($s,4,strlen($s)-9);
echo $s;

if(!$noScript) { ?>
<script language=javascript>
<?php
	if(file_exists(dirname(__FILE__)."/{$menuType}.js"))
		include "{$menuType}.js";
?>
function openLink(lnk) {
	var r=$(lnk).attr("href");
	var s="<?=generatePageRequest("","")?>";
	var cls=$(lnk).attr("class");
	var target=$(lnk).attr("target");
	var title=$(lnk).text();
	if(title==null || title.length<=0) {
		if($(lnk).attr("alias")!=null && $(lnk).attr("alias").length>0)
			title=$(lnk).attr("alias");
		else
			title=$(lnk).attr("title");
	}
	if(!(r.length>0 && r!="#")) {
		return;
	}
	if(r.indexOf("services")==0) {
		r="<?=SiteLocation?>"+r;
	}
	if(!(r.indexOf("http")>=0)) {
		//r=s+"&"+r;
	}
	if(target!=null && target.length>0) {
		if(target=="_blank") {
			window.open(r);
		}
		else if(target=="_self") {
			window.self.location=r;
		}
		else if(target=="_parent") {
			window.parent.location=r;
		}
		else if(target=="_top") {
			window.top.location=r;
		}

		else if(target=="overlay") {
			jqPopupURL(r,title, null, true,$(window).width()-50,$(window).height()-50);
		}
		else if(target=="popup") {
			jqPopupURL(r,title, null, true,500,250);
		}
		else if(target=="minipopup") {
			jqPopupURL(r,title, null, true,500,250);
		}
		else {
			window.frames[target].location=r;
		}
		return true;
	} else {
		if(cls!=null) {
			if(cls.toLowerCase()=="overlay") {
				jqPopupURL(r,title, null, true,$(window).width()-50,$(window).height()-50);
			} else if(cls.toLowerCase()=="popup") {
				jqPopupURL(r,title, null, true,500,250);
			} else if(cls.toLowerCase()=="minipopup") {
				jqPopupURL(r,title, null, true,500,250);
			}
		} else {
			if(typeof openInNewTab=="function") {
				openInNewTab(title,r);
			} else if(typeof parent.openInNewTab=="function") {
				openInNewTab(title,r);
			} else {
				document.location=r;
			}
		}
	}
	return false;
}
</script>
<?php } ?>
