<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getNav")) {
	include_once dirname(__FILE__)."/AutoMenus.inc";

	function getNav($menuid,$menuAutoGroupFile=null,$params=null,$colDefn=null) {
		$treeArray=array();

		if($colDefn==null) {
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
		}
		if($params==null) {
			$params=array(
					//"printTitle"=>true,
					"orderBy"=>null,
					"dbtable"=>_dbtable("links"),
					"requiredTableList"=>_db(true)->getTableList(),
					"requiredModuleList"=>null,
					"sysdb"=>false,
					"site"=>SITENAME,
				);
		}

		$sm=getNavMenuObj($menuid,$menuAutoGroupFile,$params,$colDefn);

		$out=$sm->getMainMenuArray($params['dbtable'],$menuid,$params['site'],$_SESSION["SESS_PRIVILEGE_NAME"],$colDefn,$params['orderBy'],$sysdb);
		$arr=$sm->getSubmenuArrays($params['sysdb']);

		foreach($out as $a=>$b) {
			$treeArray=array_merge_recursive($treeArray,$b);
		}
		foreach($arr as $a=>$b) {
			$treeArray=array_merge_recursive($treeArray,$b);
		}
		return $treeArray;
	}
	function getNavMenuObj($menuid,$menuAutoGroupFile=null,$params=null) {
		if($params==null) {
			$params=array(
					//"printTitle"=>true,
					"dbtable"=>_dbtable("links"),
					"requiredTableList"=>_db(true)->getTableList(),
					"requiredModuleList"=>null,
					"sysdb"=>false,
					"site"=>SITENAME,
				);
		}

		if($menuAutoGroupFile!=null) {
			if(file_exists($menuAutoGroupFile)) {
				$json=file_get_contents($menuAutoGroupFile);
				$arrMenu=json_decode($json, true);
				if($arrMenu==null) $arrMenu=array();
				else {
					foreach($arrMenu as $a=>$b) {
						if(isset($b['enabled']) && !$b['enabled'] && !$allGenerators) {
							unset($arrMenu[$a]);
						}
					}
				}
			}
		} else {
			$arrMenu=array();
		}

		$sm=new AutoMenus($arrMenu);
		//$sm->printTitle($params['printTitle']);
		$sm->requiredTableList($params['requiredTableList']);
		$sm->requiredModuleList($params['requiredModuleList']);
		$sm->generateSQL($params['site'],$_SESSION["SESS_PRIVILEGE_NAME"],$params['sysdb']);
		
		return $sm;
	}
}
?>