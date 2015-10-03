<?php
/*
 * Abstract Cache Driver Which all other CacheDrivers have to extend.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 10/03/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class AbstractCacheDriver {

	public function __construct() {
	}

	//Reads Cache from Local/Remote Sources
	public function cache_get($group,$key) {
		//echo "cache_get\n<br>";
	}

	//Writes Cache to Local/Remote Sources
	public function cache_set($group,$key,$data) {
		//echo "cache_set\n<br>";
	}

	//Deletes Cache from/to Local/Remote Sources
	public function cache_delete($group,$key) {
		//echo "cache_delete\n<br>";
	}

	//Deletes all cache in the instance or server
	public function cache_purge($group=null) {
		//echo "cache_purge\n<br>";
	}

	//Saves if required the whole Cache To System
	public function cache_dump() {
		//echo "cache_dump\n<br>";
	}

	//Last Updated This Key On
	public function cache_lastUpdated($group,$key) {
		//echo "cache_lastUpdated\n<br>";
	}

	//Is Cache Expired
	public function cache_isexpired($group,$key) {
		//echo "cache_isexpired\n<br>";
	}

	//Is Cache Exists
	public function cache_exists($group,$key) {
		//echo "cache_exists\n<br>";
	}

	//Gets the cache stats
	public function cache_stats() {
		//echo "cache_stats\n<br>";
	}
}
?>