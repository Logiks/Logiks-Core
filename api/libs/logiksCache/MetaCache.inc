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
	private $metaContainers=array();
	private $toUpdateContainers=array();

	protected function __construct() {
		parent::__construct("metaCache","*",true);
	}
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}

	public function getMetaFor($container,$resource) {
		$this->testContainer($container);

		if(isset($this->metaContainers[$container][$resource])) return $this->metaContainers[$container][$resource];
		return false;
	}
	public function setMetaFor($container,$resource,$data) {
		$this->testContainer($container);

		$this->metaContainers[$container][$resource]=$data;
		//$this->dumpContainer($container);

		if(!in_array($container,$this->metaContainers)) {
			$this->toUpdateContainers[]=$container;
		}
	}
	public function removeMetaFor($container,$resource) {
		$this->testContainer($container);

		unset($this->metaContainers[$container][$resource]);
		//$this->dumpContainer($container);

		if(!in_array($container,$this->metaContainers)) {
			$this->toUpdateContainers[]=$container;
		}
	}

	//All private functions
	private function testContainer($container) {
		if(isset($this->metaContainers[$container])) {
			return $this->metaContainers[$container];
		}
		$containerData=parent::getCache($container);
		if($containerData) {
			$containerData=unserialize($containerData);
		} else {
			$containerData=array();
		}

		$this->metaContainers[$container]=$containerData;
	}
	private function dumpContainer($container) {
		if(isset($this->metaContainers[$container])) {
			parent::setCache($container,serialize($this->metaContainers[$container]));
			return true;
		}
		return false;
	}

	public function dumpAllCache() {
		$this->toUpdateContainers=array_unique($this->toUpdateContainers);
		foreach ($this->toUpdateContainers as $container) {
			parent::setCache($container,serialize($this->metaContainers[$container]));
		}
		parent::dumpAllCache();
	}
}
?>
