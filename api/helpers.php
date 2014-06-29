<?php
/*
 * This class is used for managing and using helpers
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadHelpers')) {
	function helper_exists($helper) {
		global $js,$css,$ling,$cache,$templates;
		$helperArr=getAllHelpersFolders();
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
		$helperpath=getAllHelpersFolders();
		$b=false;
		if($helperNames=="*") {
			foreach($helperpath as $p) {
				$p=ROOT. $p;
				$fs=scandir($p);
				foreach($fs as $a) {
					if($a!=".." && $a!=".") {
						$b=loadHelpers($a, $p);
					}
				}
			}
		} elseif(is_array($helperNames)) {
			foreach($helperNames as $x=>$a) {
				$b=loadHelpers($a);
			}
		} else {
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
		}
		if(!$b && MASTER_DEBUG_MODE=='true') trigger_error("Helper Not Found :: " . $helperNames);
		return $b;
	}
	function getAllHelpersFolders() {
		$paths=array();
		if(!isset($_ENV['HELPERS_DIRS'])) {
			if(defined("APPS_HELPERS_FOLDER") && defined("BASEPATH")) {
				if(!in_array(BASEPATH . APPS_HELPERS_FOLDER,$paths)) array_push($paths, BASEPATH . APPS_HELPERS_FOLDER);
			}
			if(defined("HELPERS_FOLDER")) {
				if(!in_array(HELPERS_FOLDER,$paths)) array_push($paths, HELPERS_FOLDER);
			}
			$_ENV['HELPERS_DIRS']=$paths;
		} else {
			$paths=$_ENV['HELPERS_DIRS'];
		}
		return $paths;
	}
}
?>
