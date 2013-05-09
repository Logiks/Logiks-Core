<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!isset($_REQUEST["src"])) {
	exit("<h2 class=error align=center>Report Source Not Found</h2>");
}
if(!isset($_REQUEST["template"])) {
	exit("<h2 class=error align=center>Report Template Not Defined</h2>");
}

loadModuleLib("views","printer");
if($_REQUEST["src"]=="file") {
	$tmpl=$_REQUEST["template"].".tpl";
	$tmpl=str_replace(".tpl.tpl",".tpl",$tmpl);
	if(file_exists(APPROOT.TEMPLATE_FOLDER.$tmpl)) {
		loadTemplateFromFile(APPROOT.TEMPLATE_FOLDER.$tmpl);
	} elseif(file_exists(ROOT.TEMPLATE_FOLDER.$tmpl)) {
		loadTemplateFromFile(ROOT.TEMPLATE_FOLDER.$tmpl);
	} else {
		exit("<h2 class=error align=center>Report Template File Not Found</h2>");
	}
} elseif($_REQUEST["src"]=="db") {
	$tmplID=$_REQUEST["template"];
	$tArr=explode("@",$tmplID);
	$id=0;$tbl=_dbtable("views");
	if(isset($tArr[0])) $id=$tArr[0];
	if(isset($tArr[1])) $tbl=$tArr[1];
	loadTemplateFromDB($id,$tbl);
}
exit();
?>
