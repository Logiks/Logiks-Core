<?php
/*
 * This class is used for as the base Driver Class from which all fs drivers are created.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
 
 class LogiksFSDriver {

 	protected $keyName=array();
 	protected $params=[];

 	protected static $register=[];

 	public function __construct($key,$params=null) {
 		$this->keyName=$key;
 		$this->params=$params;

 		$this::$register[$key]=$this;
 	}
 	
 	public function __destruct() {

 	}

 	public static function findInstance($fsKey) {
 		if(isset(LogiksFSDriver::$register[$fsKey])) return LogiksFSDriver::$register[$fsKey];
 		return false;
 	}

 	public function reset() {
 		
 	}

 	public function info($path=null) {trigger_error("Current FS Driver Does Not Support This");}

 	public function pwd() {trigger_error("Current FS Driver Does Not Support This");}
 	public function ls($path=null) {trigger_error("Current FS Driver Does Not Support This");}
 	public function cd($path) {trigger_error("Current FS Driver Does Not Support This");}
 	public function mkdir($path) {trigger_error("Current FS Driver Does Not Support This");}
 	public function rm($path) {trigger_error("Current FS Driver Does Not Support This");}
 	public function rename($path,$newName) {trigger_error("Current FS Driver Does Not Support This");}
 	
 	public function upload($path,$file) {trigger_error("Current FS Driver Does Not Support This");}
	public function download($path,$targetDir) {trigger_error("Current FS Driver Does Not Support This");}
 	
 	public function copy($oldPath,$newPath) {trigger_error("Current FS Driver Does Not Support This");}
 	public function move($oldPath,$newPath) {trigger_error("Current FS Driver Does Not Support This");}

 	public function chmod($path,$permissions) {trigger_error("Current FS Driver Does Not Support This");}

	public function grep($path, $q) {trigger_error("Current FS Driver Does Not Support This");}
 	public function grepName($path, $q) {trigger_error("Current FS Driver Does Not Support This");}

 	// public function __debugInfo() {
  //       return [];
  //   }
 }
 ?>