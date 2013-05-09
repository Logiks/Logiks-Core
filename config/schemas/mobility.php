<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["MOBILITY_BACKGROUND"]=array(
		"type"=>"list",
		"function"=>"getBackgroundList"
	);
$cfgSchema["MOBILITY_PAGE_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["MOBILITY_HEADER_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["MOBILITY_BUTTON_THEME"]=array(
		"type"=>"list",
		"values"=>array(
			"Theme A"=>"a",
			"Theme B"=>"b",
			"Theme C"=>"c",
			"Theme D"=>"d",
			"Theme E"=>"e",
			"Theme F"=>"f",
			)
	);
$cfgSchema["CFG_GROUPS"]=array(
		"Mobility UI"=>array("MOBILITY_BACKGROUND","MOBILITY_PAGE_THEME","MOBILITY_HEADER_THEME","MOBILITY_BUTTON_THEME"),
		"Others"=>array(),
	);
	
if(!function_exists("getBackgroundList")) {
	function getBackgroundList() {
		$f=ROOT.MEDIA_FOLDER."cssbg/";
		$arr=array();
		$fs=scandir($f);
		unset($fs[0]);
		unset($fs[1]);
		$arr["None"]="";
		foreach($fs as $a=>$b) {
			if(is_file($f.$b)) {
				$s=filesize($f.$b);
				if($s>0) $s=round($s/1024, 2);
				$arr[ucwords($b) . " ($s kb)"]=MEDIA_FOLDER."cssbg/$b";
			}
		}
		return $arr;
	}
}
?>
