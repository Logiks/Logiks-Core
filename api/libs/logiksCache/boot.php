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
 * RequestCache : RequestCache handles the way the requested command (scmd) is executed based on the command type (stype)
 *           for all sync/async requests.
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
include_once dirname(__FILE__)."/RequestCache.inc";

/*Cache And Template Oriented Functions*/
if(!function_exists("_cache")) {
	//This function checks if cache exists, yes returns cached data, no creates and returns cached data
	function _cache($source,$cacheID=null,$reCache=false) {
		$cache=DataCache::getInstance();
		return $cache->getCache($source,$cacheID,$reCache);
	}
	//Introduced in v3.6.6 as a method load, print themes
	//This function checks if cache exists, yes returns cache ID, no creates and returns cache ID
	function _cacheID($source,$reCache=false) {
		$cache=DataCache::getInstance();
		return $cache->getCacheID($source,$reCache);
	}
	//Introduced in v3.6.6 as a method load, print themes
	//Sometimes we just need to print the Cache From CachedID with out caring about source
	//Usefull while transfering command of the cache from one object to another
	function _cachePrint($cacheID) {
		$cache=DataCache::getInstance();
		return $cache->printCacheFromID($cacheID);
	}
}

if(!function_exists("_metaCache")) {
	//Introduced in v3.6.6 as a method load, print themes
	function _metaCache($container,$srcFile) {
		$metaCache=MetaCache::getInstance();
		return $metaCache->getMetaFor($container,$srcFile);
	}
	function _metaCacheUpdate($container,$srcFile,$data) {
		$metaCache=MetaCache::getInstance();
		return $metaCache->setMetaFor($container,$srcFile,$data);
	}
}

?>
