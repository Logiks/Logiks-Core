<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$exts=array(".gif",".png",".jpg",".jpeg",".svg");

if(!isset($_REQUEST['src'])) $_REQUEST['src']="cssbg";
if(isset($_REQUEST["bg"])) $bg=$_REQUEST["bg"]; else $bg="blank.gif";

switch($_REQUEST['src']) {
	case "cssbg":
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
	break;
	case "image":
		$f=loadMedia($bg);
		if(!file_exists($f)) {
			$f=ROOT.MEDIA_FOLDER."cssbg/blank.gif";
		}
		$ext=end(explode(".",$bg));
		header("Content-type: image/$ext\n");
		header("Content-Transfer-Encoding: binary\n");
		readfile($f);
	break;
	case "gradient":
		
	break;
	case "color":
		
	break;
	case "random":
		
	break;
}
?>
