<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();
isAdminSite();
//TODO to use db.json for controlling what and where is accessed.
//Replacing all the place in diff modules where they are required (eg. datacontrols,dbedit)
if(isset($_REQUEST["action"])) {
		$db=_db();
		if($db!=null) {
			switch($_REQUEST["action"]) {
				case "tablelist":
					$arr=$db->getTableList();
					printServiceMsg($arr);
					break;
				case "collist":
					if(isset($_REQUEST["tbl"]) && strlen($_REQUEST["tbl"])>0) {
						$arr=$db->getTableInfo($_REQUEST["tbl"]);
						printServiceMsg($arr[0]);
					}
					break;
				case "tbldetails":
					if(isset($_REQUEST["tbl"]) && strlen($_REQUEST["tbl"])>0) {
						$arr=$db->getColumnList($_REQUEST["tbl"]);
						printServiceMsg($arr);
					}
					break;
			}
		}
}
?>
