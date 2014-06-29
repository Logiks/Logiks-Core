<?php
/*
 * This file contains all Logiks Caching System and may be used to create,delete and update 
 * systemwide as well as applevel caches.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("filetransfer");

class CacheManager {
	private static $instance=null;
	private $cacheFolder=null;
	
	protected function __construct() {
		$this->getCacheFolder();
	}
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}
	//Creates and manages the cache folder that is used By CacheManager
	public function getCacheFolder() {
		if(defined("APPS_CACHE_FOLDER")) {
			$this->cacheFolder=APPROOT.APPS_CACHE_FOLDER."var/";
			if(!file_exists($this->cacheFolder) && mkdir($this->cacheFolder, 0777, true))
				chmod($this->cacheFolder, 0777);
		} else {
			$this->cacheFolder=ROOT.CACHE_FOLDER."var/";
			if(!file_exists($this->cacheFolder) && mkdir($this->cacheFolder, 0777, true))
				chmod($this->cacheFolder, 0777);
		}
		if(!file_exists($this->cacheFolder)) {
			if(!is_writable(dirname($this->cacheFolder))) {
				trigger_error("Cache Folder Is ReadOnly. Failed To Create Cache");
				exit();
			}
			mkdir($this->cacheFolder, 0777, true);
			if (!file_exists($this->cacheFolder)) {
				trigger_error("Cache Folder Is ReadOnly. Failed To Create Cache");
				exit();
			} else {
				chmod($this->cacheFolder, 0777);
			}
		}
		if(!is_writable($this->cacheFolder)) {
			trigger_error("Cache Not Writable");
		}
		return $this->cacheFolder;
	}
	//Generates the Cache ID for the link.
	public function getCacheID($source) {
		return md5($source);
	}
	//Generates the Cache Link for the source.
	public function getCachePath($source) {
		return $this->getCacheFolder().md5($source);
	}
	//Checks if the cache is expired from cacheLink
	public function isExpired($source) {
		$cacheLink=$this->getCachePath($source);
		$tm=time()-filemtime($cacheLink);
		$diff=intval(getConfig("CACHE_EXPIRY"))-$tm;
		if($diff<0) {
			return true;
		} else {
			return false;
		}
	}
	//Checks if the cacheID is expired from cacheLink
	public function isExpiredCacheID($cacheID) {
		$cacheLink=$this->getCacheFolder().$cacheID;
		$tm=time()-filemtime($cacheLink);
		$diff=intval(getConfig("CACHE_EXPIRY"))-$tm;
		if($diff<0) {
			return true;
		} else {
			return false;
		}
	}
	//Checks cache's source and determines if its cached
	public function isCached($source) {
		$cacheID=md5($source);
		$cacheLink=$this->getCacheFolder().$cacheID;
		if(file_exists($cacheLink)) {
			if($this->isExpiredCacheID($cacheID)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	//Checks cache's cacheid and determines if its cached
	public function isCachedByID($cacheID) {
		$cacheLink=$this->getCacheFolder().$cacheID;
		if(file_exists($cacheLink)) {
			if($this->isExpiredCacheID($cacheID)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	//Cache or no cache, source is called upon if required, else returns : cache file link.
	public function getCacheLink($source,$cacheID=null,$forceRecache=false) {
		if($cacheID==null) $cacheID=md5($source);
		if($forceRecache || !$this->isCachedByID($cacheID)) {
			$this->cacheSource($source,$cacheID);
		}
		$cacheLink=$this->getCacheFolder().$cacheID;
		if(file_exists($cacheLink)) {
			return $cacheLink;
		} else {
			$this->cacheError($source);
		}
	}
	//Cache or no cache, source is called upon if required, else return cached data.
	public function getCache($source,$cacheID=null,$forceRecache=false) {
		if($cacheID==null) $cacheID=md5($source);
		if($forceRecache || !$this->isCachedByID($cacheID)) {
			$this->cacheSource($source,$cacheID);
		}
		$cacheLink=$this->getCacheFolder().$cacheID;
		if(file_exists($cacheLink)) {
			readfile($cacheLink);
		} else {
			$this->cacheError($source);
		}
	}
	//Creates New Cache From Source where params are function,method,file
	private function cacheSource($source,$cacheID=null,$params=null) {
		if($cacheID==null) $cacheID=md5($source);
		$cacheLink=$this->getCacheFolder().$cacheID;
		if(function_exists($source)) {
			ob_start();
			call_user_func($source,$params);
			$data=ob_get_contents();
			ob_clean();
			file_put_contents($cacheLink,$data);
		} elseif(is_object($params)) {
			ob_start();
			call_user_method($source,$params);
			$data=ob_get_contents();
			ob_clean();
			file_put_contents($cacheLink,$data);
		} elseif(file_exists($source)) {
			ob_start();
			include $source;
			$data=ob_get_contents();
			ob_clean();
			file_put_contents($cacheLink,$data);
		} elseif(strpos($source,"http://")===0 || 
			strpos($source,"https://")===0 ||
			strpos($source,"ftp://")===0) {
			transferFileHTTPToLocal($source,$cacheLink);
		} else {
			$this->cacheError($source);
		}
		return file_exists($cacheLink);
	}
	//Displays the Error in caching source
	private function cacheError($source) {
		trigger_ErrorCode(404,"Unable to Cache Resource.");
	}
	//All Cache Clearing Functions
	public function clearCache($fileLink="") {
		if($fileLink==null || strlen($fileLink)<=0 || $fileLink=="*") {
			return $this->clearLocalCache();
		} else {
			$fname=md5($fileLink);
			if(defined("APPS_CACHE_FOLDER")) {
				if(file_exists(APPROOT.APPS_CACHE_FOLDER.$fname)) {
					unlink(APPROOT.APPS_CACHE_FOLDER.$fname);
					return true;
				}
			}
			if(file_exists($this->getCacheFolder().$fname)) {
				unlink($this->cacheFolder.$fname);
				return true;
			}
		}
		return false;
	}

	//Clears Cache Of APPROOT system
	public function clearLocalCache() {
		if(defined("APPS_CACHE_FOLDER")) {
			if(file_exists(APPROOT.APPS_CACHE_FOLDER)) {
				$arr=scandir(APPROOT.APPS_CACHE_FOLDER);
				foreach($arr as $a) {
					if($a!="." && $a!="..")
					unlink(APPROOT.APPS_CACHE_FOLDER.$a);
				}
				rmdir(APPROOT.APPS_CACHE_FOLDER);
				mkdir(APPROOT.APPS_CACHE_FOLDER,0777,true);
				chmod(APPROOT.APPS_CACHE_FOLDER,0777);
				return true;
			}
		}
		return false;
	}

	//Clears Cache Of ROOT system
	public function clearGlobalCache() {
		if(file_exists(ROOT.CACHE_FOLDER)) {
			$arr=scandir(ROOT.CACHE_FOLDER);
			foreach($arr as $a) {
				if($a!="." && $a!="..")
				unlink(ROOT.CACHE_FOLDER.$a);
			}
			rmdir(ROOT.CACHE_FOLDER);
			mkdir(ROOT.CACHE_FOLDER,0777,true);
			chmod(ROOT.CACHE_FOLDER,0777);
			return true;
		}
		return false;
	}
}
?>
