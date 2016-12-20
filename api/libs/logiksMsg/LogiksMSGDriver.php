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
	
	 protected function msgLog($status,$logMsg, $to,$subject,$body,$params) {
		 if($status) {
			 _log($logMsg,"message",LogiksLogger::LOG_WARNING,[$to,$subject,$body,$params]);
		 } else {
			 _log($logMsg,"error",LogiksLogger::LOG_ERROR,[$to,$subject,$body,$params]);
		 }
	 }
	 
 	public function send($to,$subject,$body,$params=[]) {
 		return false;
 	}
	 
	public function sendTemplate($to,$subject,$msgTemplate,$msgData=[],$params=[]) {
		$body=_templateData($msgTemplate,$msgData);
		return $this->send($to,$subject,$body,$params);
	}
}
?>