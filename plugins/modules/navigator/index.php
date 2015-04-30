<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($MODULE_PARAMS[1]['menuid'])) {
	echo "No MenuID Defined.";
	exit();
}

include_once "AutoMenus.inc";

$weightCol="w";
$colDefn=array(
		"idCol"=>"id",
		"titleCol"=>"title",
		"groupCol"=>"menugroup",
		"categoryCol"=>"category",
		"linkCol"=>"link",
		"iconCol"=>"iconpath",
		"classCol"=>"class",
		"tipsCol"=>"tips",
		"targetCol"=>"target",
		"toCheckCol"=>"to_check",
	);
$site=null;
$dbtable="";
$dbLink=null;
$sysdb=false;
$menuid="default";
$menuAutoGroupFile=null;
$requiredTableList=null;
$requiredModuleList=null;
$menuType='sidebar';
$printTitle=true;
$noScript=false;
$showID=false;
$allGenerators=false;
$orderBy=null;

if(is_array($MODULE_PARAMS[1])) {
	if(isset($MODULE_PARAMS[1]['menuid'])) {
		$menuid=$MODULE_PARAMS[1]['menuid'];
	}

	if(isset($MODULE_PARAMS[1]['site'])) {
		$site=$MODULE_PARAMS[1]['site'];
	}
	if(isset($MODULE_PARAMS[1]['dbtable'])) {
		$dbtable=$MODULE_PARAMS[1]['dbtable'];
	} else {
		$dbtable=_dbtable("links");
	}
	if(isset($MODULE_PARAMS[1]['dbLink'])) {
		$dbLink=$MODULE_PARAMS[1]['dbLink'];
	} else {
		$dbLink=getAppsDBLink();
	}
	if(isset($MODULE_PARAMS[1]['sysdb'])) {
		$sysdb=$MODULE_PARAMS[1]['sysdb'];
	}
	//Optional Components
	if(isset($MODULE_PARAMS[1]['colDefn']) && is_array($MODULE_PARAMS[1]['colDefn'])) {
		$colDefn=$MODULE_PARAMS[1]['colDefn'];
	}
	if(isset($MODULE_PARAMS[1]['menuAutoGroupFile'])) {
		$menuAutoGroupFile=$MODULE_PARAMS[1]['menuAutoGroupFile'];
	}
	if(isset($MODULE_PARAMS[1]['menuType'])) {
		$menuType=$MODULE_PARAMS[1]['menuType'];
	}
	if(isset($MODULE_PARAMS[1]['printTitle'])) {
		$printTitle=$MODULE_PARAMS[1]['printTitle'];
	}
	if(isset($MODULE_PARAMS[1]['useCategory'])) {
		if(!$MODULE_PARAMS[1]['useCategory']) {
			$colDefn["categoryCol"]="";
			$colDefn["groupCol"]="";
		}
	}
	if(isset($MODULE_PARAMS[1]['noScript'])) {
		$noScript=$MODULE_PARAMS[1]['noScript'];
	}
	if(isset($MODULE_PARAMS[1]['showID'])) {
		$showID=$MODULE_PARAMS[1]['showID'];
	}
	if(isset($MODULE_PARAMS[1]['allGenerators'])) {
		$allGenerators=$MODULE_PARAMS[1]['allGenerators'];
	}
	if(isset($MODULE_PARAMS[1]['tableList'])) {
		$requiredTableList=$MODULE_PARAMS[1]['tableList'];
	} else {
		$requiredTableList=_db(true)->getTableList();
	}
	if(isset($MODULE_PARAMS[1]['moduleList'])) {
		$requiredModuleList=$MODULE_PARAMS[1]['moduleList'];
	}
	if(isset($MODULE_PARAMS[1]['orderBy'])) {
		$orderBy=$MODULE_PARAMS[1]['orderBy'];
	}
}
if($site==null) $site=SITENAME;

if(strlen($dbtable)<=0) {
	trigger_error("Proper Parameters Are Not Passed To Navigator Module.");
}
$arrMenu=array();
if(file_exists($menuAutoGroupFile)) {
	$json=file_get_contents($menuAutoGroupFile);
	$arrMenu=json_decode($json, true);
	if($arrMenu==null) $arrMenu=array();
}
foreach($arrMenu as $a=>$b) {
	if(isset($b['enabled']) && !$b['enabled'] && !$allGenerators) {
		unset($arrMenu[$a]);
	}
}

$sm=new AutoMenus($arrMenu);
$sm->printTitle($printTitle);
$sm->requiredTableList($requiredTableList);
$sm->requiredModuleList($requiredModuleList);

$sm->generateSQL($site,$_SESSION["SESS_PRIVILEGE_NAME"],$sysdb);

$out=$sm->getMainMenuArray($dbtable,$menuid,$site,$_SESSION["SESS_PRIVILEGE_NAME"],$colDefn,$orderBy,$sysdb);
$arr=$sm->getSubmenuArrays($sysdb);

$treeArray=array();
foreach($out as $a=>$b) {
	$treeArray=array_merge_recursive($treeArray,$b);
}
foreach($arr as $a=>$b) {
	$treeArray=array_merge_recursive($treeArray,$b);
}
//printArray($treeArray);exit();
$sm->printMenuTree($treeArray,$showID);

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
