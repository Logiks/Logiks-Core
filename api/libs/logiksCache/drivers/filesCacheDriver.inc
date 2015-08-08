<?php
/*
 * Cache Driver With Files as the base for all operations
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 10/03/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/abstractCacheDriver.inc";

class FilesCacheDriver extends AbstractCacheDriver {

	protected $cacheTag=null;
	protected $cacheFolder=null;

	public function __construct($srcTag) {
		parent::__construct();
		$this->cacheTag=$srcTag;

		$this->getCacheFolder();
	}
	//Creates and manages the cache folder that is used By DataCache
	public function getCacheFolder() {
		$this->cacheFolder=ROOT.CACHE_FOLDER."{$this->cacheTag}/";
		if(defined("SITENAME")) {
			$this->cacheFolder.=SITENAME."/";
		}
		if(!file_exists($this->cacheFolder)) {
			mkdir($this->cacheFolder,0777,true);
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
				//chmod($this->cacheFolder, 0777);
			}
		}
		if(!is_writable($this->cacheFolder)) {
			trigger_error("Cache {$this->cacheTag} Not Writable");
		}

		file_put_contents(dirname($this->cacheFolder)."/.htaccess", "Deny from all");

		return $this->cacheFolder;
	}

	private function getCacheFile($key) {
		$fx=$this->cacheFolder."{$key}";//.cache
		//if(!file_exists($fx)) {
		//	file_put_contents($fx, "");
		//}
		return $fx;
	}
	//Reads Cache from Local/Remote Sources
	public function cache_get($key) {
		$fx=$this->getCacheFile($key);
		if(file_exists($fx)) {
			return file_get_contents($fx);
		} else {
			return false;
		}
	}

	//Writes Cache to Local/Remote Sources
	public function cache_set($key,$data) {
		$fx=$this->getCacheFile($key);
		$dx=file_put_contents($fx, $data);
		return $dx;
	}

	//Deletes Cache from/to Local/Remote Sources
	public function cache_delete($key) {
		$fx=$this->getCacheFile($key);
		return unlink($fx);
	}

	//Saves if required the whole Cache To System If Required
	public function cache_dumpAll() {
		//We don't use this here, as everything is written the instance it is set.
	}

	//Last Updated This Key On
	public function cache_lastUpdated($key) {
		$fx=$this->getCacheFile($key);
		if(!file_exists($fx)) {
			return filemtime($fx);
		} else {
			return time();
		}
	}

	//Is Cache Expired
	public function cache_isexpired($key) {
		$fx=$this->getCacheFile($key);
		if(!file_exists($fx)) return true;
		$tm=time()-filemtime($fx);
		$diff=intval(getConfig("CACHE_EXPIRY"))-$tm;
		if($diff<0) {
			return true;
		} else {
			return false;
		}
	}

	//Is Cache Exists
	public function cache_exists($key) {
		$fx=$this->getCacheFile($key);
		return file_exists($fx);
	}
}
?>
