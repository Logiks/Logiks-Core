<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("createSelectorFromListFile")) {
	function createSelectorFromListFile($file) {
		$list="";
		$data="";

		if(file_exists($file)) $data=file_get_contents($file);
		elseif(defined("APPROOT") && defined("APPS_MISC_FOLDER") && file_exists(APPROOT.APPS_MISC_FOLDER.$file)) {
			$file=APPROOT.APPS_MISC_FOLDER.$file;
			$data=file_get_contents($file);
		} elseif(file_exists(ROOT.MISC_FOLDER.$file)) {
			$file=ROOT.MISC_FOLDER.$file;
			$data=file_get_contents($file);
		} else {
			return "";
		}
		if(strlen($data)>0) {
			$data=explode("\n",$data);
			foreach($data as $a=>$b) {
				if(strlen($b)>0 && strpos($b,"#")!==0) {
					if(strpos($b,"=")>0) {
						$e=explode("=",$b);
						$e1=$e[0];
						$e2=$e[1];
						echo "<option value='$e2'>"._ling($e1)."</option>";
					} else {
						echo "<option value='$b'>"._ling($b)."</option>";
					}
				}
			}
		}
		return $list;
	}
}
?>
