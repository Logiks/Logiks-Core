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

	private $assets=[];

	protected function __construct() {
		//var_dump($_SERVER);exit();
		//var_dump($GLOBALS['LOGIKS']);exit();
		//$GLOBALS['LOGIKS']["_SERVER"]
		
		if(isset($GLOBALS['LOGIKS']["_SERVER"])) {
			$this->DATA['SERVER']=$GLOBALS['LOGIKS']["_SERVER"];
		} elseif(isset($_SERVER)) {
			$GLOBALS['LOGIKS']["_SERVER"]=$_SERVER;
			$this->DATA['SERVER']=$_SERVER;
		}

		if(isset($GLOBALS['LOGIKS']["_SESSION"])) {
			$this->DATA['SESSION']=$GLOBALS['LOGIKS']["_SESSION"];
		} elseif(isset($_SESSION)) {
			$GLOBALS['LOGIKS']["_SESSION"]=$_SESSION;
			$this->DATA['SESSION']=$_SESSION;
		}

		if(isset($GLOBALS['LOGIKS']["_ENV"])) {
			$this->DATA['ENV']=$GLOBALS['LOGIKS']["_ENV"];
		} elseif(isset($_ENV)) {
			$GLOBALS['LOGIKS']["_ENV"]=$_ENV;
			$this->DATA['ENV']=$_ENV;
		}

		if(isset($GLOBALS['LOGIKS']["_COOKIE"])) {
			$this->DATA['COOKIE']=$GLOBALS['LOGIKS']["_COOKIE"];
		} elseif(isset($_COOKIE)) {
			$GLOBALS['LOGIKS']["_COOKIE"]=$_COOKIE;
			$this->DATA['COOKIE']=$_COOKIE;
		}

		//unset($_SERVER);
		//unset($_SESSION);
		//unset($_ENV);
		//$_COOKIE
		//var_dump($_SERVER);exit();
	}
	public static function getInstance() {
	    if(LogiksSession::$instance==null) {
	      LogiksSession::$instance=new LogiksSession();
	    }
	    return LogiksSession::$instance;
	}
	public function data($key) {
		$key=strtoupper($key);
		if(isset($this->DATA[$key]))
			return $this->DATA[$key];
		return [];
	}
	public function set($key,$name,$val) {
		$key=strtoupper($key);
		$this->DATA[$key][$name]=$val;
		return $val;
	}
	public function get($key,$name) {
		$key=strtoupper($key);
		if(isset($this->DATA[$key][$name])) return $this->DATA[$key][$name];
		return false;
	}

	public function dump() {

	}

	public function htmlAssets() {
		if(isset($this->assets["htmlAsset"])) {
			return $this->assets["htmlAsset"];
		} else {
			$lt=new LogiksTheme(APPS_THEME,SITENAME);
			$this->assets["htmlAsset"]=new HTMLAssets($lt);

			return $this->assets["htmlAsset"];;
		}
	}
}

LogiksSession::getInstance();
?>
