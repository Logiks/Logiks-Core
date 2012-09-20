<?php
//This class is used for managing and using helpers
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadHelpers')) {
	function helper_exists($helper) {
		global $js,$css,$ling,$cache,$templates;
		global $helperpath;
		if(defined("APPS_HELPERS_FOLDER") && defined("BASEPATH")) {
			if(!in_array(BASEPATH . APPS_HELPERS_FOLDER,$helperpath)) array_push($helperpath, BASEPATH . APPS_HELPERS_FOLDER);
		}
		
		if(defined("HELPERS_FOLDER")) {
			if(!in_array(HELPERS_FOLDER,$helperpath)) {
				if(!in_array(HELPERS_FOLDER,$helperpath)) array_push($helperpath, HELPERS_FOLDER);
			}
		}
		if(is_array($helper)) {
			$helperArr=array_flip($helper);
			foreach($helperArr as $ha=>$n) {
				$b=false;
				$ext=substr($ha,strlen($ha)-4);
				foreach($helperpath as $path) {
					$p=ROOT.$path.$ha;
					if(file_exists($p . ".php")) {
						$b=true;
					} elseif(file_exists($p . ".inc")) {
						$b=true;
					}
				}
				$helperArr[$ha]=$b;
			}
			return $helperArr;
		} else {
			$b=false;
			$ext=substr($helper,strlen($helper)-4);
			foreach($helperpath as $path) {
				$p=ROOT.$path.$helper;
				if(file_exists($p . ".php")) {
					$b=true;
				} elseif(file_exists($p . ".inc")) {
					$b=true;
				}
			}
			return $b;
		}
		return false;
	}
	function loadHelpers($helperNames, $path="*", $type="include_once") {
		global $js,$css,$ling,$cache,$templates;
		global $helperpath;
		if(defined("APPS_HELPERS_FOLDER") && defined("BASEPATH")) {
			if(!in_array(BASEPATH . APPS_HELPERS_FOLDER,$helperpath)) array_push($helperpath, BASEPATH . APPS_HELPERS_FOLDER);
		}
		
		if(defined("HELPERS_FOLDER")) {
			if(!in_array(HELPERS_FOLDER,$helperpath)) {
				if(!in_array(HELPERS_FOLDER,$helperpath)) array_push($helperpath, HELPERS_FOLDER);
			}
		}
		if($helperNames=="*") {
			$b=false;
			foreach($helperpath as $p) {
				$p=ROOT. $p;
				$fs=scandir($p);
				foreach($fs as $a) {
					if($a!=".." && $a!=".") {
						$b=loadHelpers($a, $p);
					}			
				}
			}
			return $b;
		} elseif(is_array($helperNames)) {
			$b=false;
			foreach($helperNames as $x=>$a) {
				$b=loadHelpers($a);				
			}
			return $b;
		} else {
			$b=false;
			$ext=substr($helperNames,strlen($helperNames)-4);
			if($path=="*") {
				foreach($helperpath as $path) {
					$p=ROOT.$path.$helperNames;	
					//echo $p . "<br/>";
					if(file_exists($p . ".php")) {
						if($type=="require_once") require_once $p . ".php";
						elseif($type=="require") require $p . ".php";
						elseif($type=="include_once") include_once $p . ".php";
						else include $p . ".php";
						$b=true;
					} elseif(file_exists($p . ".inc")) {
						if($type=="require_once") require_once $p . ".inc";
						elseif($type=="require") require $p . ".inc";
						elseif($type=="include_once") include_once $p . ".inc";
						else include $p . ".inc";
						$b=true;
					}
				}
			} else {
				$p=ROOT.$path."/".$helperNames;
				$p=str_replace("//","/",$p);
				if(file_exists($p . ".php")) {
					if($type=="require_once") @require_once $p . ".php";
					elseif($type=="require") @require $p . ".php";
					elseif($type=="include_once") @include_once $p . ".php";
					else @include $p . ".php";
					$b=true;
				} elseif(file_exists($p . ".inc")) {
					if($type=="require_once") @require_once $p . ".inc";
					elseif($type=="require") @require $p . ".inc";
					elseif($type=="include_once") @include_once $p . ".inc";
					else @include $p . ".inc";
					$b=true;
				}
			}
			return $b;
		}
	}
}
?>
