<?php
/*
 * Cache Driver With Files as the base for all operations
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 10/03/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class MemcachedDriver extends AbstractCacheDriver {

	protected $cacheTag=null;
	protected $params=null;
	protected $expires=86400;
	protected $prefix="";

	private $memcache=null;

	public function __construct($srcTag,$params) {
		parent::__construct();
		$this->cacheTag=$srcTag;
		$this->params=$params;

		if(isset($this->params['expires'])) $expires=$this->params['expires'];
		if(isset($this->params['prefix'])) $prefix=$this->params['prefix'];

		if(!isset($this->params['weight'])) $this->params['weight']=100;

		if(!class_exists("Memcached")) {
			trigger_logikserror("MemcachedDriver needs Memcached Class to run.",E_USER_ERROR);
		}

		$this->memcache = new Memcached();
		$this->memcache->addServer($this->params['host'],$this->params['port'],$this->params['weight']);

		$statuses = $this->memcache->getStats();

		if($statuses[$this->params['host'].":".$this->params['port']]['pid']<0) {
			trigger_logikserror("MemcachedDriver could not access the memcache server for '$srcTag'.",E_USER_ERROR);
		}

		$_ENV['SOFTHOOKS']['SHUTDOWN'][]=array("FUNC"=>"closeConnection","OBJ"=>$this);
	}

	public function closeConnection() {
		$this->memcache->quit();
	}
	
	//Reads Cache from Local/Remote Sources
	public function cache_get($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return $this->memcache->get(md5($this->prefix.$siteKey.$group.$key));
	}

	//Writes Cache to Local/Remote Sources
	public function cache_set($group,$key,$data) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		if($this->memcache->add(md5($this->prefix.$siteKey.$group.$key),$data,$this->expires)) {
			return true;
		} else {
			return $this->memcache->replace(md5($this->prefix.$siteKey.$group.$key),$data,$this->expires);
		}
	}

	//Deletes Cache from/to Local/Remote Sources
	public function cache_delete($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return $this->memcache->delete(md5($this->prefix.$siteKey.$group.$key));
	}

	//Immediately invalidates all existing items.
	public function cache_purge($group=null) {
		return $this->memcache->flush();
	}

	//Is Cache Exists
	public function cache_exists($group,$key) {
		// $siteKey="core";
		// if(defined("SITENAME")) $siteKey=SITENAME;
		//$this->memcache->get(md5($this->prefix.$siteKey.$group.$key));
		//return (Memcached::RES_NOTFOUND !=== $this->memcache->getResultCode());
	}

	//Gets the cache stats
	public function cache_stats() {
		return $this->memcache->getStats();
	}

	//Last Updated This Key On
	public function cache_lastUpdated($group,$key) {
		trigger_logikserror("MemcachedDriver could not support cache_lastUpdated.",E_USER_WARNING);
	}

	//Is Cache Expired
	public function cache_isexpired($group,$key) {
		return false;
	}
}
?>
