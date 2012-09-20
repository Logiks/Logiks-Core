<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["abt"])) {
	if(file_exists(ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.php")) {
		include ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.php";
	} elseif(file_exists(ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.html")) {
		include ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.html";
	} elseif(file_exists(ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.htm")) {
		include ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.htm";
	} elseif(file_exists(ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.txt")) {
		echo "<textarea style='width:99%;height:99%;border:0px;resize:none;' readonly>";
		readfile(ROOT.PAGES_FOLDER."abouts/{$_REQUEST['abt']}.txt");
		echo "</textarea>";
	} elseif(file_exists(ROOT."{$_REQUEST['abt']}.txt")) {
		echo "<textarea style='width:99%;height:99%;border:0px;resize:none;' readonly>";
		readfile(ROOT."{$_REQUEST['abt']}.txt");
		echo "</textarea>";
	} else {
		printErr("DataNotFound","About Page Not Found");
	}
} else {
	echo "Nothing Asked, Nothing Delivered ...";
}
function countInDir($dir,$scanMode="*",$checkFile="") {
	$fs=scandir($dir);
	unset($fs[0]);unset($fs[1]);
	$cnt=0;
	foreach($fs as $a) {
		if(strlen($checkFile)==0) {
			if($scanMode=="*") {$cnt++;}
			elseif($scanMode=="dir_only" && is_dir($dir.$a)) {$cnt++;}
			elseif($scanMode=="file_only" && is_file($dir.$a)) {$cnt++;}
		} else {
			if($scanMode=="*" && file_exists($dir.$a."/$checkFile")) {$cnt++;}
			elseif($scanMode=="dir_only" && is_dir($dir.$a) && file_exists($dir.$a."/$checkFile")) {$cnt++;}
			elseif($scanMode=="file_only" && is_file($dir.$a) && file_exists($dir.$a."/$checkFile")) {$cnt++;}
		}
	}
	return $cnt;
}
?>
