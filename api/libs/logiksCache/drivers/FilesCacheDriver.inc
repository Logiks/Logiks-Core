<?php
/*
 * Cache Driver With Files as the base for all operations
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 10/03/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class FilesCacheDriver extends AbstractCacheDriver {

	protected $cacheTag=null;
	protected $params=null;

	protected $cacheFolder=null;

	private $cacheResource=array();
	private $toUpdateResource=array();

	public function __construct($srcTag,$params) {
		parent::__construct();
		$this->cacheTag=$srcTag;
		$this->params=$params;

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
				trigger_logikserror("Cache Folder Is ReadOnly. Failed To Create Cache");
				exit();
			}
			mkdir($this->cacheFolder, 0777, true);
			if (!file_exists($this->cacheFolder)) {
				trigger_logikserror("Cache Folder Is ReadOnly. Failed To Create Cache");
				exit();
			} else {
				//chmod($this->cacheFolder, 0777);
			}
		}
		if(!is_writable($this->cacheFolder)) {
			trigger_logikserror("Cache {$this->cacheTag} Not Writable");
		}

		file_put_contents(dirname($this->cacheFolder)."/.htaccess", "Deny from all");

		return $this->cacheFolder;
	}

	private function getCacheFile($group) {
		$fx=$this->cacheFolder."{$group}";//.cache
		return $fx;
	}

	//Reads Cache from Local/Remote Sources
	public function cache_get($group,$key) {
		$this->testGroup($group);

		if(isset($this->cacheResource[$group][$key])) return $this->cacheResource[$group][$key];
		else return false;
	}

	//Writes Cache to Local/Remote Sources
	public function cache_set($group,$key,$data) {
		$this->testGroup($group);

		$this->cacheResource[$group][$key]=$data;

		if(!in_array($group,$this->cacheResource)) {
			$this->toUpdateResource[]=$group;
		}

		return true;
	}

	//Deletes Cache from/to Local/Remote Sources
	public function cache_delete($group,$key) {
		$this->testGroup($group);

		unset($this->cacheResource[$group][$key]);		

		if(!in_array($group,$this->cacheResource)) {
			$this->toUpdateResource[]=$group;
		}

		return true;
	}

	//Saves if required the whole Cache To System If Required
	public function cache_dump() {
		$this->toUpdateResource=array_unique($this->toUpdateResource);
		foreach ($this->toUpdateResource as $group) {
			$groupFile=$this->getCacheFile($group);
			file_put_contents($groupFile,serialize($this->cacheResource[$group]));
		}
	}

	//Last Updated This Key On
	public function cache_lastUpdated($group,$key) {
		$fx=$this->getCacheFile($group);
		if(!file_exists($fx)) {
			return filemtime($fx);
		} else {
			return time();
		}
	}

	//Is Cache Expired
	public function cache_isexpired($group,$key) {
		$fx=$this->getCacheFile($group);
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
	public function cache_exists($group,$key) {
		$fx=$this->getCacheFile($group);
		return file_exists($fx);
	}

	//Remove all cache for the $resource
	public function cache_purge($group=null) {
		if($group!=null) {
			$fx=$this->getCacheFile($group);
			return unlink($fx);
		} else {
			$dir=$this->cacheFolder;
			$fs=scandir($dir);
			foreach ($fs as $f) {
				if(file_exists($dir.$f)) unlink($dir.$f);
			}
		}
		return false;
	}

	//Gets the cache stats
	public function cache_stats() {
		return array(
				"CACHE_FOLDER"=>$this->cacheFolder,
				"ALL_OK"=>(is_writable($this->cacheFolder) && is_dir($this->cacheFolder)),
			);
	}

	//All private functions
	private function testGroup($group) {
		if(isset($this->cacheResource[$group])) {
			return $this->cacheResource[$group];
		}
		$gFile=$this->getCacheFile($group);
		$containerData=array();
		if(file_exists($gFile)) {
			$containerData=file_get_contents($gFile);
			$containerData=unserialize($containerData);
		}
		$this->cacheResource[$group]=$containerData;
		return $this->cacheResource[$group];
	}
}
?>
