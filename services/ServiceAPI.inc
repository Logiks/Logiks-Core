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
		elseif(isset($httpRef["HTTP_HOST"]) && strlen($httpRef["HTTP_HOST"])>0 && $httpRef["HTTP_HOST"]==$GLOBALS['LOGIKS']["_SERVER"]["HTTP_HOST"]) return false;
		return true;
	}
	public function checkRemoteClient() {
		if(!$this->isRemoteCall()) return true;

		if(!$this->processAPIKey()) return false;

		if(!$this->checkAPIKey()) return false;

		if(!$this->checkAccessRules()) return false;

		return true;
	}
	protected function processAPIKey() {
		//TODO : authorization system with apikey
		//Session restorin using apikey
		//This function is never called for localhosts, or in session calls, but only called for remote api calls.
		//$apiKey=$_REQUEST['APIKEY'];
		//$site=SITENAME;

		return true;
	}
	public function checkAPIKey() {
		return true;
		$apiKey=$_REQUEST['APIKEY'];
		$site=SITENAME;
		$a=false;
		if(isset($GLOBALS['LOGIKS']["_SERVER"]["HTTP_REFERER"]) && strlen($GLOBALS['LOGIKS']["_SERVER"]["HTTP_REFERER"])!=0) {
			$httpRef=parseHTTPReferer();
			if($httpRef["HTTP_HOST"]==$GLOBALS['LOGIKS']["_SERVER"]["HTTP_HOST"]) {
				$a=true;
			} else {
				if(isset($_REQUEST['APIKEY']) && getConfig("API_KEY")==$_REQUEST['APIKEY']) {
					$a=true;
				} else {
					$a=false;
				}
			}
		} else {
			if(strlen(getConfig("API_KEY"))>0) {
				if(isset($apiKey) && getConfig("API_KEY")==$apiKey) {
					$a=true;
				}
			} else {
				$a=true;
			}
		}
		return $a;
	}
	
	//Blocks frequent visitors, and abnormal calls
	protected function checkAccessRules() {
		return true;
	}
}
?>
