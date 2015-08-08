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

class LogiksCache {

	protected $cacheTag=null;
	protected $cacheDriver=null;
	protected $cacheDriverObject=null;

	protected function __construct($srcTag,$driver="*",$driverParams=array()) {
		$this->cacheTag=$srcTag;
		$this->cacheDriver=$driver;

		switch ($driver) {
			case '*':
			case 'files':
			  include_once dirname(__FILE__)."/drivers/filesCacheDriver.inc";

				$this->cacheDriverObject=new FilesCacheDriver($srcTag,$driverParams);
			break;

			default:
				trigger_error("Cache Driver is not supported.");
				exit();
			break;
		}
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
	protected function getCache($key) {
		return $this->cacheDriverObject->cache_get($key);
	}

	//Writes Cache to Local/Remote Sources
	protected function setCache($key,$data) {
		return $this->cacheDriverObject->cache_set($key,$data);
	}

	//Deletes Cache from/to Local/Remote Sources
	protected function deleteCache($key) {
		return $this->cacheDriverObject->cache_delete($key);
	}

	//Saves if required the whole Cache To System
	public function dumpAllCache() {
		return $this->cacheDriverObject->cache_dumpAll();
	}

	public function lastUpdated($key) {
		return $this->cacheDriverObject->cache_lastUpdated($key);
	}

	public function isExpired($key) {
		return $this->cacheDriverObject->cache_isexpired($key);
	}
	public function isCached($key) {
		return $this->cacheDriverObject->cache_exists($key);
	}
	//findCache, , reCache
}
?>
