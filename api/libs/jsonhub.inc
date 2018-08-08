<?php
/*
 * Logiks uses a lot of json files, sometimes its required by them to load in sequence and merge with parent such
 * that they can inherit attributes from parent objects. JSONHub allows php arrays to be used along with JSON files
 * in the same way.
 * 
 * Features
 * 1. Loads JSON files across system on demand basis
 * 2. Each JSON file is loaded into its own node
 * 3. When ever required, new arrays can be formed which then can be passed into the JSONHub to be process with the 
 *    pre loaded nodes
 * 4. Use session,cache to keep the JSON structure intact across session and avoid reading the source file everytime
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 02/08/2018
 * Version: 1.0
 */
class JSONHub {
  
  private static $instance=null;
  
  var $jhubCacheID = "JSONHUBDATA";
  var $sessCacheID=null;
  
  private $masterNode=[];
  
  protected function __construct() {
		$this->sessCacheID=md5(SiteID.SITENAME.$this->jhubCacheID);
    
    $cacheData = $this->getJHubCache();
    if($cacheData) {
      $this->masterNode=$cacheData;
    }
	}
  
  public static function getInstance() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}
  
  public function merge($node,$jsonData) {
    if($jsonData && is_array($jsonData) && isset($this->masterNode[$node])) {
      return array_merge($this->masterNode[$node],$jsonData);
    }
    return $jsonData;
  }
  
  public function addNodeFile($node,$file) {
    if(file_exists($file)) {
      $jsonData=file_get_contents($file);
      $jsonData=json_decode($jsonData,true);
      if($jsonData && is_array($jsonData)) {
        if(!isset($this->masterNode[$node])) {
          $this->masterNode[$node]=[];
        }
        $this->masterNode[$node]=array_merge($this->masterNode[$node],$jsonData);
        $this->updateJHubCache();
        return $this->masterNode[$node];
      }
    }
    return false;
  }
  
  public function addNodeData($node,$jsonData) {
    if($jsonData && is_array($jsonData)) {
      if(!isset($this->masterNode[$node])) {
        $this->masterNode[$node]=[];
      }
      $this->masterNode[$node]=array_merge($this->masterNode[$node],$jsonData);
      $this->updateJHubCache();
      return $this->masterNode[$node];
    }
  }
  
  public function removeNode($node) {
    if(isset($this->masterNode[$node])) {
      unset($this->masterNode[$node]);
      $this->updateJHubCache();
    }
    return true;
  }
  
  protected function getJHubCache() {
    if(isset($_SESSION[$this->sessCacheID])) {
      return $_SESSION[$this->sessCacheID];
    }
    $cacheData = _cache($this->jhubCacheID);
    if($cacheData) {
      $cacheData=json_decode($cacheData,true);
      $_SESSION[$this->sessCacheID]=$cacheData;
      return $cacheData;
    }
    return false;
  }
  
  protected function clearJHubCache() {
    $this->masterNode=[];
    _cache($this->jhubCacheID,false);
    $_SESSION[$this->sessCacheID]=$this->masterNode;
  }
  
  protected function updateJHubCache() {
    _cache($this->jhubCacheID,json_encode($this->masterNode));
    $_SESSION[$this->sessCacheID]=$this->masterNode;
  }
}