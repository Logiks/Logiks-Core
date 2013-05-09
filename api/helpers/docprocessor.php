<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once ROOT."/api/libs/docs/excel_reader.php";

if(!function_exists("printDocCSS")) {
	function printDocCSS($ext) {
		if($ext=="xls") {
			$p=dirname(ROOT."api/libs/docs/")."/docs/excel.css";
			return file_get_contents($p);
		}
	}
	function printDocJS($ext) {
		if($ext=="xls") {
			$p=dirname(ROOT."/api/libs/docs/")."docs/excel.js";
			return file_get_contents($p);
		}
	}
}
?>
