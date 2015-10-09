<?php
/*
 * MetaCache saves the definations of search output (Internal Logiks Type) for all practical future needs.
 * This in turn reduces the amount of time taken by various system wide search.
 * This is a container based cache mechanisim where resource belongs to a container and at times container
 * may be updated at parts or full.
 *
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class MetaCache extends LogiksCache {

	private static $instance=null;
	
	protected function __construct() {
		parent::__construct("metaCache");
	}
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}

	public function getMetaFor($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return parent::getCache($key,$siteKey.$group);
	}

	public function setMetaFor($group,$key,$data) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return parent::setCache($key,$data,$siteKey.$group);
	}

	public function removeMetaFor($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return parent::deleteCache($key,$siteKey.$group);
	}
}
?>
