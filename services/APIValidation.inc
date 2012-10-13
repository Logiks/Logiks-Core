<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

//All API Key Related Functions
//Working On UserBased APIKey Generation And Validation
class APIValidation {
	public function __construct() {
		//API_KEY_ENGINE
	}
	public function checkAPIKey($apiKey,$site) {
		$a=false;
		if(isset($_SERVER["HTTP_REFERER"]) && strlen($_SERVER["HTTP_REFERER"])!=0) {
			$httpRef=parseHTTPReferer();
			if($httpRef["HTTP_HOST"]==$_SERVER["HTTP_HOST"]) {
				$a=true;
			} else {
				if(isset($_REQUEST['APIKEY']) && API_KEY==$_REQUEST['APIKEY']) {
					$a=true;
				} else {
					$a=false;
				}
			}
		} else {
			if(strlen(API_KEY)>0) {
				if(isset($apiKey) && API_KEY==$apiKey) {
					$a=true;
				}			
			} else {
				$a=true;
			}
		}
		return $a;
	}
}
?>
