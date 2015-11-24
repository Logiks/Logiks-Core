<?php
/*
 * ServiceAPI is central to the remote api key system for Logiks. This system 
 * is an advanced form of APIValidation.inc. Ofcourse this does all that the old
 * system was planned to do and more.
 * 
 * @author : Bismay Kumar Mohapatra
 * @version : 1.0
 */
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

//All API Key Related Functions
//Working On UserBased APIKey Generation And Validation
//Blocking rules for frequent APIKEY access
class ServiceAPI {
	private $API_ENGINE="simple";
	private $KEY_STATIC="";
	public function __construct() {
		$this->API_ENGINE=getConfig("APIKEY_ENGINE");
		$this->KEY_STATIC=getConfig("APIKEY_STATIC");
	}
	public function isRemoteCall() {
		$httpRef=parseHTTPReferer();
		if(isLocalhost()) return false;
		elseif(isAjax()) return false;
		elseif(isset($httpRef["HTTP_HOST"]) && strlen($httpRef["HTTP_HOST"])>0 && $httpRef["HTTP_HOST"]==_server("HTTP_HOST")) return false;
		return true;
	}
	public function checkRemoteClient() {
		if(!$this->isRemoteCall()) return true;

		if(!$this->checkAPIKey()) return false;

		if(!$this->processAPIKey()) return false;

		return true;
	}
	protected function processAPIKey() {
		//TODO : authorization system with apikey
		//Session restoring using apikey
		//$apiKey=$_REQUEST['APIKEY'];
		//$site=SITENAME;

		return true;
	}
	public function checkAPIKey() {
		$site=SITENAME;
		$a=false;
		if(_server("HTTP_REFERER") && strlen(_server("HTTP_REFERER"))!=0) {
			$httpRef=parseHTTPReferer();
			if($httpRef["HTTP_HOST"]==_server("HTTP_HOST")) {
				$a=true;
			} else {
				$a=$this->checkKeyWithEngine();
			}
		} else {
			$a=$this->checkKeyWithEngine();
		}
		return $a;
	}

	protected function checkKeyWithEngine() {
		if(!isset($_REQUEST['APIKEY'])) {
			return false;
		}
		$apiKey=$_REQUEST['APIKEY'];
		$a=false;

		switch ($this->API_ENGINE) {
			case 'none':
				return true;
				break;
			case 'static':
				if(strlen(getConfig("APIKEY_STATIC"))>0 && getConfig("APIKEY_STATIC")==$apiKey) {
					return true;
				}
				break;
			default:
				$engine=$this->API_ENGINE;
				if(class_exists($engine)) {
					$obj=new $engine();
					if(is_a($obj,"ServiceAuthEngine")) {
						return $obj->testAPIKey($apiKey);
					}
				}
				break;
		}
		return $a;
	}
}
?>
