<?php
/*
 * RequestCache handles the way the requested command (scmd) is executed
 * based on the command type (stype) for all sync/async requests.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class RequestCache {
	public static function getCachePath($type) {
		$cacheDir=ROOT.TMP_FOLDER."fullcache/{$type}/".SITENAME."/";
		if(!is_dir($cacheDir)) {
			if(mkdir($cacheDir,0777,true)) chmod($cacheDir,0777);
		}
		$hash=md5($_SERVER['REQUEST_URI']);
		$hashFile=$cacheDir.$hash.".php";
		return $hashFile;
	}
	public static function checkCache($type,$period) {
		$cacheFile=RequestCache::getCachePath($type);
		if(isset($_REQUEST['autocache']) && $_REQUEST['autocache']=="false") return false;
		else {
			if(file_exists($cacheFile) && (time()-filectime($cacheFile))<$period) {
				return true;
			} else return false;
		}
	}
}
?>
