<?php
/*
 * This class is used for as the base Driver Class from which all MSG drivers are created.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
 
 class LogiksMSGDriver {

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
 		if(isset(LogiksMSGDriver::$register[$fsKey])) return LogiksMSGDriver::$register[$fsKey];
 		return false;
 	}

 	public static function isValidEmail($address) {
		return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
	}

 	public function send($to,$subject,$body,$params=[]) {
 		return false;
 	}
}
?>