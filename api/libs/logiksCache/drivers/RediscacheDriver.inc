<?php
/*
 * Cache Driver With Redis (php-redis)
 * https://github.com/phpredis/phpredis#usage
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 20/07/2018
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class RediscacheDriver extends AbstractCacheDriver {
  
  protected $cacheTag=null;
	protected $params=null;
	protected $expires=86400;
	protected $prefix="";

	private $redis=null;

	public function __construct($srcTag,$params) {
		parent::__construct();
		$this->cacheTag=$srcTag;
		$this->params=$params;

		if(isset($this->params['expires'])) $expires=$this->params['expires'];
		if(isset($this->params['prefix'])) $prefix=$this->params['prefix'];

		if(!isset($this->params['weight'])) $this->params['weight']=100;

		if(!class_exists("Redis")) {
			trigger_logikserror("RedisDriver needs Redis Class to run.",E_USER_ERROR);
		}

		$this->redis = new Redis();
    $this->redis->pconnect($this->params['host'],$this->params['port'],$this->params['weight']);

		$statuses = $this->redis->info();
  
		if(!isset($statuses['redis_version'])) {
			trigger_logikserror("RediscacheDriver could not access the redis server for '$srcTag'.",E_USER_ERROR);
		}

		$_ENV['SOFTHOOKS']['SHUTDOWN'][]=array("FUNC"=>"closeConnection","OBJ"=>$this);
	}

	public function closeConnection() {
		$this->redis->close();
	}
  
  //Reads Cache from Local/Remote Sources
	public function cache_get($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return $this->redis->get(md5($this->prefix.$siteKey.$group.$key));
	}

	//Writes Cache to Local/Remote Sources
	public function cache_set($group,$key,$data) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return $this->redis->set(md5($this->prefix.$siteKey.$group.$key),$data,$this->expires);
	}

	//Deletes Cache from/to Local/Remote Sources
	public function cache_delete($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		return $this->redis->delete(md5($this->prefix.$siteKey.$group.$key));
	}

	//Immediately invalidates all existing items.
	public function cache_purge($group=null) {
		return $this->redis->flushDb();
	}

	//Is Cache Exists
	public function cache_exists($group,$key) {
		$siteKey="core";
		if(defined("SITENAME")) $siteKey=SITENAME;
		$a=$this->redis->get(md5($this->prefix.$siteKey.$group.$key));
		return !($a===false);
	}

	//Gets the cache stats
	public function cache_stats() {
		return $this->redis->info();
	}

	//Last Updated This Key On
	public function cache_lastUpdated($group,$key) {
		trigger_logikserror("RedisDriver could not support cache_lastUpdated.",E_USER_WARNING);
	}

	//Is Cache Expired
	public function cache_isexpired($group,$key) {
		return false;
	}
}