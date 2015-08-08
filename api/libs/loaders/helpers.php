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
		if(is_array($helper)) {
			$helperPath=getAllHelpersFolders();
			$helperArr=array_flip($helper);
			foreach($helperArr as $ha=>$n) {
				$cachePath=_metaCache("HELPERS",$ha);
				if(!$cachePath) {
					$fpath="";
					$b=false;
					$ext=substr($ha,strlen($ha)-4);
					foreach($helperPath as $path) {
						$p=ROOT.$path.$ha;
						if(file_exists($p . ".php")) {
							$fpath=$p . ".php";
							$b=true;
						} elseif(file_exists($p . ".inc")) {
							$fpath=$p . ".inc";
							$b=true;
						}
					}
					if($b) {
						_metaCacheUpdate("HELPERS",$ha,$fpath);
						$helperArr[$ha]=$fpath;
					} else {
						$helperArr[$ha]=$b;
					}
				} else {
					$helperArr[$ha]=$cachePath;
				}
			}
			return $helperArr;
		} else {
			$cachePath=_metaCache("HELPERS",$helper);
			if(!$cachePath) {
				$helperArr=getAllHelpersFolders();
				$b=false;
				$ext=substr($helper,strlen($helper)-4);
				foreach($helperArr as $path) {
					$p=ROOT.$path.$helper;
					if(file_exists($p . ".php")) {
						_metaCacheUpdate("HELPERS",$helper,$p . ".php");
						$cachePath=$p . ".php";
						$b=true;
					} elseif(file_exists($p . ".inc")) {
						_metaCacheUpdate("HELPERS",$helper,$p . ".inc");
						$cachePath=$p . ".inc";
						$b=true;
					}
				}
				if($b) return $cachePath;
				else return $b;
			} else {
				return $cachePath;
			}
		}
		return false;
	}
	function loadHelpers($helperNames, $path="*", $type="include_once") {
		if(is_array($helperNames)) {
			foreach($helperNames as $x=>$a) {
				$b=loadHelpers($a);
			}
		} else {
			$cachePath=_metaCache("HELPERS",$helperNames);
			if(!$cachePath || !file_exists($cachePath)) {
				$helperPath=helper_exists($helperNames);
			} else {
				$helperPath=$cachePath;
			}
			if(file_exists($helperPath)) {
				if($type=="require_once") require_once $helperPath;
				elseif($type=="require") require $helperPath;
				elseif($type=="include_once") include_once $helperPath;
				else include $helperPath;
			} else {
				trigger_logikserror("Helper Not Found :: " . $helperNames,E_LOGIKS_ERROR,404);
			}
		}
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
