<?php
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
	
	private function isExpired($cacheLink) {
		$tm=time()-filemtime($cacheLink);
		$diff=intval(getConfig("CACHE_EXPIRY"))-$tm;
		if($diff<0) {
			return true;
		} else {
			return false;
		}
	}
	//Locates the CacheFile From the Link used in the cache folders.
	private function cacheFilePathName($cacheID) {
		$cacheDir=$this->getCacheFolder();
		if(file_exists($cacheDir.$cacheID)) {
			return $cacheDir.$cacheID;
		}
		return "";
	}
	
	public function getCacheID($link,$cacheID=null) {
		if($cacheID==null) $cacheID=md5($link);
		return $cacheID;
	}
	
	public function isCached($cacheName) {
		$cacheID=$this->getCacheID($cacheName);
		return $this->isCachedByID($cacheID);
	}
	
	private function isCachedByID($cacheId) {
		$found=$this->cacheFilePathName($cacheId);
		
		if(strlen($found)>0) {
			if($this->isExpired($found)) {
				unlink($found);
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	//Gets The Cache Data/Link/URL If Exists, else creates and returns the link/data.
	public function getCacheData($link,$cacheId=null,$reCache=false) {
		if($link==null || strlen($link)==0) return "";
		$fpath=$this->getCacheLink($link,$cacheId,$reCache);
		if(strlen($fpath)>0) {
			return file_get_contents($fpath);
		} else {
			return "";
		}
	}	
	public function getCacheLink($link,$cacheId=null,$reCache=false) {
		$fpath="";
		$cid=$this->getCacheID($link,$cacheId);
		if(!$reCache && $this->isCachedByID($cid)) {
			$fpath=$this->cacheFilePathName($cid);
		} else {
			$fpath=$this->cacheLink($link,$cacheId);
		}
		return $fpath;
	}
	public function getCacheURL($url,$cacheId=null,$reCache=false) {
		$fpath="";
		$cid=$this->getCacheID($url,$cacheId);
		if(!$reCache && $this->isCachedByID($cid)) {
			$fpath=$this->cacheFilePathName($cid);
		} else {
			$fpath=$this->cacheURL($url,$cacheId);
		}
		return $fpath;
	}
	
	//All Cacheing Functions That Are Used To Create Caches
	public function cacheURL($url,$cacheName=null,$forceUpdate=false) {
		if($url==null || strlen($url)==0) return "";
		if($cacheName!=null && strlen($cacheName)>0) $cacheId=md5($cacheName);
		else $cacheId=md5($url);
		$fpath=$this->getCacheFolder().$cacheId;
		
		transferFileHTTPToLocal($url,$fpath);
		if(file_exists($fpath)) {
			return $fpath;
		}
		return "";
	}
	public function cacheLink($link,$cacheName=null,$forceUpdate=true) {
		if($link==null || strlen($link)==0) return "";
		if($cacheName!=null && strlen($cacheName)>0) $cacheId=md5($cacheName);		
		else $cacheId=md5($link);
		$fpath=$this->getCacheFolder().$cacheId;
		
		if(!file_exists($link)) {
			if(file_exists(ROOT.$link)) {
				$link=ROOT.$link;
			} elseif(file_exists(APPROOT.$link)) {
				$link=APPROOT.$link;
			} else {
				return false;
			}
		}
		if($this->isCachedByID($cacheId)) {
			if($forceUpdate) {
				ob_start();
				include $link;
				$data=ob_get_contents();
				file_put_contents($fpath,$data);
				ob_clean();
			}
		} else {
			ob_start();
			include $link;
			$data=ob_get_contents();
			file_put_contents($fpath,$data);
			ob_clean();
		}
		return $fpath;
	}
	public function cacheDataLink($data,$cacheName,$forceUpdate=false) {
		if($data==null || strlen($data)==0) return "";
		if($cacheName!=null && strlen($cacheName)>0) $cacheId=md5($cacheName);
		else $cacheId=md5($data);
		$fpath=$this->getCacheFolder().$cacheId;
				
		if($this->isCachedByID($cacheId)) {
			if($forceUpdate) {
				file_put_contents($fpath,$data);
			}
		} else {
			file_put_contents($fpath,$data);
		}
		return $fpath;
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
