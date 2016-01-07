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
