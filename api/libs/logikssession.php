<?php
/*
 * LogiksSession handles the Session data and others across Logiks Framework.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/10/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksSession {

	private static $instance=null;

	protected $DATA=[];

	protected function __construct() {
		//var_dump($_SERVER);
		//$GLOBALS['LOGIKS']["_SERVER"]
		
		if(isset($GLOBALS['LOGIKS']["_SERVER"])) {
			$this->DATA['SERVER']=$GLOBALS['LOGIKS']["_SERVER"];
		} elseif(isset($_SERVER)) {
			$this->DATA['SERVER']=$_SERVER;
		}

		if(isset($GLOBALS['LOGIKS']["_SESSION"])) {
			$this->DATA['SESSION']=$GLOBALS['LOGIKS']["_SESSION"];
		} elseif(isset($_SESSION)) {
			$this->DATA['SESSION']=$_SESSION;
		}

		if(isset($GLOBALS['LOGIKS']["_ENV"])) {
			$this->DATA['ENV']=$GLOBALS['LOGIKS']["_ENV"];
		} elseif(isset($_ENV)) {
			$this->DATA['ENV']=$_ENV;
		}
	}
	public static function getInstance() {
	    if(LogiksSession::$instance==null) {
	      LogiksSession::$instance=new LogiksSession();
	    }
	    return LogiksSession::$instance;
	}
	public function data($key) {
		if(isset($this->DATA[$key]))
			return $this->DATA[$key];
		return [];
	}
	public function set($key,$name,$val) {
		$this->DATA[$key][$name]=$val;
		return $val;
	}
}
LogiksSession::getInstance();

if(!function_exists("_session")) {
	function _session($var) {
		$data=LogiksSession::getInstance()->data('SESSION');
		if(isset($data[$var])) {
			return $data[$var];
		}
		return false;
	}
	function _server($var) {
		$data=LogiksSession::getInstance()->data('SERVER');
		if(isset($data[$var])) {
			return $data[$var];
		}
		return false;
	}
	function _env($var) {
		$data=LogiksSession::getInstance()->data('ENV');
		if(isset($data[$var])) {
			return $data[$var];
		}
		return false;
	}
	function _envData($key,$name,$value) {
		LogiksSession::getInstance()->set(strtoupper($key),$name,$value);
	}
}
?>