<?php
/*
 * This file contains all Data/Content Caching System and may be used to create,delete and update
 * systemwide as well as applevel caches.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

//loadHelpers("filetransfer");

class DataCache extends LogiksCache {

	private static $instance=null;

	protected function __construct() {
		parent::__construct("dataCache");
	}
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}

	//Checks if the cacheID is expired from cacheLink
	public function isExpired($source,$group="General") {
		$cacheID=md5($source);
		return parent::isExpired($cacheID,$cacheID);
	}
	//Checks if the cacheID is expired from cacheLink
	public function isExpiredCacheID($cacheID) {
		return parent::isExpired($cacheID);
	}
	//Checks cache's cacheid and determines if its cached
	public function isCached($source,$group="General") {
		$cacheID=md5($source);
		return parent::isCached($cacheID,$cacheID);
	}
	//Checks cache's cacheid and determines if its cached
	public function isCachedByID($cacheID) {
		return parent::isCached($cacheID,$cacheID);
	}

	public function clearCache($cacheID) {
		return parent::purge($cacheID,$cacheID);
	}

	//Sometimes we just need to print the Cache From CachedID with out caring about source
	//Usefull while transfering command of the cache from one object to another
	public function printCacheFromID($cacheID) {
		return parent::getCache($cacheID,$cacheID);
	}

	//Cache or no cache, source is called upon if required, else returns : cache ID.
	public function getCacheID($source,$forceRecache=false) {
		$cacheID=md5($source);
		if($forceRecache || parent::isExpired($cacheID,$cacheID)) {
			$result=$this->cacheSource($source,$cacheID);
		} else {
			$result=parent::getCache($cacheID,$cacheID);
		}
		if(!$result) {
			$this->cacheSource($source,$cacheID);
		}
		return $cacheID;
	}
	//Cache or no cache, source is called upon if required, else returns : cache file data
	public function getCache($source,$group="*",$cacheID=null,$forceRecache=false) {
		if($cacheID==null) $cacheID=md5($source);
		if($forceRecache || parent::isExpired($cacheID,$cacheID)) {
			$result=$this->cacheSource($source,$cacheID);
		} else {
			$result=parent::getCache($cacheID,$cacheID);
		}

		if(!$result) {
			return $this->cacheSource($source,$cacheID);
		} else {
			return $result;
		}
	}
	//Should handle default logiks processed data like tpls
	private function cacheSource($source,$cacheID=null,$params=null) {
		if($cacheID==null) $cacheID=md5($source);
		$data="";
		if(file_exists($source)) {
			ob_start();
			include $source;
			$data=ob_get_contents();
			ob_clean();
		} elseif(strpos($source,"http://")===0 ||
			strpos($source,"https://")===0 ||
			strpos($source,"ftp://")===0) {

			ob_start();
			readfile($source);
			$data=ob_get_contents();
			ob_clean();
			//$data=file_get_contents($source);
			//transferFileHTTPToLocal($source,$cacheLink);
		} elseif(function_exists($source)) {
			ob_start();
			call_user_func($source,$params);
			$data=ob_get_contents();
			ob_clean();
		} elseif(is_object($params)) {
			ob_start();
			call_user_method($source,$params);
			$data=ob_get_contents();
			ob_clean();
		} else {
			$this->cacheError($source);
		}
		if(parent::setCache($cacheID,$data,$cacheID)) return $data;
		else return false;
	}
	//Displays the Error in caching source
	private function cacheError($source) {
		if(function_exists("trigger_ErrorCode")) trigger_ErrorCode(404,"Unable to Cache Resource.");
		else echo "Unable to Cache Resource (404).";
		//exit();
	}
}
?>
