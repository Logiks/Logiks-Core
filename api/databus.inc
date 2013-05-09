<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class DataBus {	
	private static $instance=null;
	private $dataStore=null;
	
	private function __construct() {
		$this->dataStore=array();
	}
	
	public static function singleton($nm="DataBus") {
		if(!isset(self::$instance)) {
			if(isset($_SESSION[$nm])) {
				$var = unserialize($_SESSION[$nm]);
				if(gettype($var)=="object" && get_class($var)=="DataBus") {
					DataBus::$instance=$var;
				}
			}
			if(!isset(self::$instance)) {
				if(is_file($nm)) {
					//ToDo :: Load DumpData To DataBus
				}
			}
			if(!isset(self::$instance)) {
				$c=__CLASS__;
				DataBus::$instance=new $c;
			}
		}
		return DataBus::$instance;
	}
	
	public function issetData($key,$context="/") {
		if($context==null || strlen($context)==0) $context="/";
		if(isset($this->dataStore[$context][$key])) {
			return true;
		} else {
			return false;
		}
	}
	
	public function setData($key,$val=null,$context="/") {
		if($context==null || strlen($context)==0) $context="/";
		if(!array_key_exists($context,$this->dataStore)) {
			$this->dataStore[$context]=array();
		}
		$this->dataStore[$context][$key]=array("value"=>$val,"timestamp"=>time());
		return $this->dataStore[$context][$key];
	}
	
	public function getData($key,$context="/") {
		if($context==null || strlen($context)==0) $context="/";
		if(!array_key_exists($context,$this->dataStore)) {
			$this->dataStore[$context]=array();
		}
		if(isset($this->dataStore[$context][$key])) {
			$arr=$this->dataStore[$context][$key];
			$this->dataStore[$context][$key]=array("value"=>$arr['value'],"timestamp"=>time());
			return $arr['value'];
		} else {
			return "";
		}
	}
	
	public function delete($key,$context="/") {
		if($context==null || strlen($context)==0) $context="/";
		if(array_key_exists($context,$this->dataStore)) {
			if(isset($this->dataStore[$context][$key])) {
				$a=$this->dataStore[$context][$key];
				unset($this->dataStore[$context][$key]);
				return $a;
			} else {
				return false;
			}
		}
		return false;
	}
	
	public function gc() {
		if($this->dataStore==null || count($this->dataStore)<=0) return;
		foreach($this->dataStore as $a=>$b) {
			foreach($b as $x=>$y) {
				if((time()-$y['timestamp'])>=DATABUS_TIMEOUT) {
					unset($this->dataStore[$a][$x]);
				}
			}
			if(count($this->dataStore[$a])<=0) {
				unset($this->dataStore[$a]);
			}
		}
	}
	
	public function count() {
		return count($this->dataStore);
	}
	public function dump($context="/") {
		if($context==null) {
			printArray($this->dataStore);
		} else {
			if(isset($this->dataStore[$context])) {
				printArray($this->dataStore[$context]);
			}
		}
	}
	public function dumpToFile($file) {
		$this->gc();
		$data=serialize(DataBus::singleton());//json_encode($this->dataStore);
		file_put_contents($file,$data);
	}
	public function dumpToSession($nm="DataBus") {
		$this->gc();
		$var=serialize(DataBus::singleton());
		$_SESSION[$nm]=$var;
	}
	public function clear() {
		$this->dataStore=array();
	}
}
?>
