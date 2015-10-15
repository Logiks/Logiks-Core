<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["src"])) {
	if(!isset($_REQUEST["term"])) $_REQUEST["term"]="";
	
	$dict=$_REQUEST["src"];
	$term=$_REQUEST["term"];
	$path=array();
	
	if(defined("APPS_MISC_FOLDER")) {
		if(file_exists(APPROOT.APPS_MISC_FOLDER."lookups/")) {
			array_push($path,APPROOT.APPS_MISC_FOLDER."lookups/");
		}
	}
	if(file_exists(ROOT.MISC_FOLDER."lookups/")) {
		array_push($path,ROOT.MISC_FOLDER."lookups/");
	}
	foreach($path as $a=>$p) {
		if(file_exists($p.$dict.".dat")) {
			$data=file_get_contents($p.$dict.".dat");
			$data=explode("\n",$data);
			foreach($data as $a=>$b) {
				if(strlen($term)>0) {
					if(strpos("#".strtolower($b),strtolower($term))==1) {					
					} elseif(strpos($b,"=")>1 && strpos(strtolower($b),strtolower("=".$term))==strpos($b,"=")) {
					} else {
						unset($data[$a]);
					}
				}				
			}
			dispatchData($data);
			if(getConfig("ALLOW_LOOKUP_CONCATANATION")=="false") break;
		} elseif(file_exists($p.$dict.".xml")) {
			//echo $p.$dict.".dat";
		}
	}
}
exit();
function dispatchData($data) {	
	$format="json";
	if(isset($_REQUEST['format'])) {
		$format=$_REQUEST['format'];
	}
	if($format=="json") {
		$arr=array();
		foreach($data as $a=>$b) {
			if(strlen($b)>0) {
				$x=array();
				
				if(strpos($b,"=")>0) {
					$e=explode("=",$b);
					$x["label"]=$e[0];
					$x["value"]=$e[1];
				} else {
					$x["label"]="$b";
					$x["value"]="$b";
				}
				$x["data"]="";
				array_push($arr,$x);
			}
		}
		echo json_encode($arr);
	} elseif($format=="selector") {
		foreach($data as $a=>$b) {
			if(strlen($b)>0) {
				if(strpos($b,"=")>0) {
					$e=explode("=",$b);
					$e1=$e[0];
					$e2=$e[1];					
					echo "<option value='$e2'>$e1</option>";
				} else {
					echo "<option value='$b'>$b</option>";
				}
			}
		}
	} else {
		
	}
}
?>
