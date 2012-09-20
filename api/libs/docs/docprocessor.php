<?php
require_once "excel_reader.php";

function printCSS($ext) {
	if($ext=="xls") {
		$p=dirname(__FILE__)."/excel.css";
		return file_get_contents($p);
	}
}
function printJS($ext) {
	if($ext=="xls") {
		$p=dirname(__FILE__)."/excel.js";
		return file_get_contents($p);
	}
}
?>
