<?php
/*
 * LogiksCache is an abstract or more short of parent to all the cache engines throughout the system, this is
 * where we actually do create, save, update etc on an abstract level.
 * This has been in a way such that the cache system can remain lightly coupled with the way content is saved
 * eg. We can use MemCached, APC (PHP Cache), etc and other engines.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/drivers/abstractCacheDriver.inc";

class LogiksCache {

	protected $cacheTag=null;
	protected $cacheDriver=null;
	protected $cacheDriverObject=null;

	protected function __construct($srcTag,$driver="*",$driverParams=array()) {
		$this->cacheTag=$srcTag;

		if($driver==null || $driver=="*") {
			$driverConfig=$this->configureInstance($srcTag);

			$this->cacheDriver=$driverConfig['driver'];
			$driverParams=$driverConfig['params'];
		} else {
			$this->cacheDriver=$driver;
		}

		$f=dirname(__FILE__)."/drivers/".$this->cacheDriver.".inc";
		if(file_exists($f)) {
			include_once $f;

			$cDriver=$this->cacheDriver;
			$this->cacheDriverObject=new $cDriver($srcTag,$driverParams);
		} else {
			trigger_error("Cache Driver is not supported.");
			exit();
		}
	}

	private function configureInstance($srcTag) {
		$cacheConfig=loadJSONConfig("cache",$srcTag);
		if(!$cacheConfig) {
			$cacheConfig=loadJSONConfig("cache","default");
		}
		if(!$cacheConfig) {
			$cacheConfig=array(
					"driver"=>"files",
				);
		}
		if(!isset($cacheConfig['params'])) $cacheConfig['params']=array();
		return $cacheConfig;
	}

	public function getCacheTag() {
		return $this->cacheTag;
	}
	public function getCacheDriver() {
		return $this->cacheDriver;
	}
	public function getCacheObject() {
		return $this->cacheDriverObject;
	}

	//Reads Cache from Local/Remote Sources
	protected function getCache($key,$group="General") {
		return $this->cacheDriverObject->cache_get($group,$key);
	}

	//Writes Cache to Local/Remote Sources
	protected function setCache($key,$data,$group="General") {
		return $this->cacheDriverObject->cache_set($group,$key,$data);
	}

	//Deletes Cache from/to Local/Remote Sources
	protected function deleteCache($key,$group="General") {
		return $this->cacheDriverObject->cache_delete($group,$key);
	}

	//Saves if required the whole Cache To System
	public function dumpAllCache() {
		return $this->cacheDriverObject->cache_dump();
	}

	public function lastUpdated($key,$group="General") {
		return $this->cacheDriverObject->cache_lastUpdated($group,$key);
	}

	public function isExpired($key,$group="General") {
		return $this->cacheDriverObject->cache_isexpired($group,$key);
	}
	public function isCached($key,$group="General") {
		return $this->cacheDriverObject->cache_exists($group,$key);
	}
	//findCache, , reCache

	//Clears all the cache on the server
	public function purge() {
		return $this->cacheDriverObject->purge($group);
	}
}
?>
