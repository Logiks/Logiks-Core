<?php
/*
 * This file is used to initiate the cache engines.
 *
 * LogiksCache :
 * DataCache : This file contains all Data/Content Caching System and may be used to create,delete and update
 *           systemwide as well as applevel caches.
 * MetaCache : MetaCache saves the definations of search output (Internal Logiks Type) for all practical future needs.
 *           This in turn reduces the amount of time taken by various system wide search.
 *           This is a container based cache mechanisim where resource belongs to a container and at times container
 *           may be updated at parts or full.
 *
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once dirname(__FILE__)."/LogiksCache.inc";
include_once dirname(__FILE__)."/DataCache.inc";
include_once dirname(__FILE__)."/MetaCache.inc";

//Dcoument caching Capabilities, can cache remote objects
if(!function_exists("_cache")) {
	//Gets or sets the cache data in a quick and accessible fashion
	function _cache($key,$value=null,$group="LOGIKS-".SiteID) {
		$metaCache=MetaCache::getInstance();
		if($value!==null) {
			$metaCache->setMetaFor($group,$key,$value);
		}
		return $metaCache->getMetaFor($group,$key);
	}
}

//Dcoument caching Capabilities, can cache remote objects
if(!function_exists("_dataCache")) {
	//This function checks if cache exists, yes returns cached data, no creates and returns cached data
	function _dataCache($source,$cacheID=null,$reCache=false) {
		$cache=DataCache::getInstance();
		return $cache->getCache($source,$cacheID,$reCache);
	}
	//This function checks if cache exists, yes returns cache ID, no creates and returns cache ID
	function _dataCacheID($source,$reCache=false) {
		$cache=DataCache::getInstance();
		return $cache->getCacheID($source,$reCache);
	}
	//Sometimes we just need to print the Cache From CachedID with out caring about source
	//Usefull while transfering command of the cache from one object to another
	function _dataCachePrint($cacheID) {
		$cache=DataCache::getInstance();
		return $cache->printCacheFromID($cacheID);
	}
}

//A more extensive Key value pair cache system, meant to store oneliner outputs, etc.
if(!function_exists("_metaCache")) {
	function _metaCache($group,$srcFile) {
		$metaCache=MetaCache::getInstance();
		return $metaCache->getMetaFor($group,$srcFile);
	}
	function _metaCacheUpdate($group,$srcFile,$data) {
		$metaCache=MetaCache::getInstance();
		return $metaCache->setMetaFor($group,$srcFile,$data);
	}
}

?>
