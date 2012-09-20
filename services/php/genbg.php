<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["bg"])) $bg=$_REQUEST["bg"]; else $bg="blank.gif";
///var/www/projects/logiks3/misc/themes/default/images/bg

$exts=array(".gif",".png",".jpg",".jpeg",".svg");
$fldr=ROOT.MEDIA_FOLDER."cssbg/";

foreach($exts as $a) {
	$f=$fldr.$bg.$a;
	if(file_exists($f)) {
		if($a==".svg") {
			$content=file_get_contents($f);
			header('Content-type: image/svg+xml');
			echo $content;
		} else {
			$ax=substr($a,1);
			$content=file_get_contents($f);
			header("Content-type: image/$ax\n");
			header("Content-Transfer-Encoding: binary\n");
			echo $content;
		}
	}
}
?>
