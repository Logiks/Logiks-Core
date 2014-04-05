<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["DATE_FORMAT"]=array(
		"type"=>"list",
		"values"=>array(
				"d/m/yy","m/d/yy","yy/m/d","yy/d/m"
			),
	);
$cfgSchema["TIME_FORMAT"]=array(
		"type"=>"list",
		"values"=>array(
				"H:i:s","h:i:s","H:i:s:u","h:i:s:u","G:i:s","g:i:s","G:i:s:u","g:i:s:u"
			),
	);
?>
