<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["PAGE_ENCODING"]=array(
		"type"=>"list",
		"function"=>array(
				"UTF-8","UTF-7","ASCII","Latin"
			)
	);
$cfgSchema["PAGE_BUFFER_ENCODING"]=array(
		"type"=>"list",
		"values"=>array(
			"Plain Encoding"=>"plain",
			"GZip Encoding"=>"gzip"
			)
	);
$cfgSchema["PAGE_EXPIRES"]=array(
		"class"=>"datefield readonly",
		"src"=>"D, d M yy 00:00:00",
	);
$cfgSchema["PAGE_LAST_MODIFIED"]=array(
		"class"=>"datefield readonly",
		"src"=>"D, d M yy 00:00:00",
	);
$cfgSchema["CFG_GROUPS"]=array(
		"System Properties"=>array("SHOW_DEVELOPER_META","PRINT_PHP_HEADERS"),
		"Developer/Source Meta"=>array("DEV_AUTHOR","DEV_MAIL","DEV_COPYRIGHT"),
		"Default App Properties"=>array("APPS_NAME","APPS_VERS","APPS_COMPANY","APPS_COMPANY_SITE","APPS_COPYRIGHT","APPS_COPYRIGHT_YEAR","APPS_RELEASE_YEAR"),
		"Xtra Default Properties"=>array("DESCRIPTION","KEYWORDS","ROBOTS"),
		"HTML Page Headers"=>array("HEADER.DOCTYPE","HEADER.HTML_ATTRIBUTES","HEADER.HEAD_ATTRIBUTES",
			"PAGE_CACHE_CONTROL","PAGE_PRAGMA","PAGE_ENCODING","PAGE_BUFFER_ENCODING","PAGE_EXPIRES","PAGE_LAST_MODIFIED","PAGE_REFRESH"),
		"Others"=>array()
	);
if(!function_exists("getEncodings")) {
	function getEncodings() {
			$o=array();
			include ROOT."config/encodings.php";
			$arr=getEncodingList();
			foreach($arr as $a=>$b) {
				$o[$b]=$b;
			}
			return $o;
	}
}
?>
