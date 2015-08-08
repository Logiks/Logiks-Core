<?php
/*
 * This Class is meant to contain all security related functions. This is an extraction
 * of functions from security.php.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 05/04/2014
 * Version: 1.0
 */

 class LogiksSecurity {

   public function __construct() {

   }
   public function checkPageRequest() {
     $this->checkSiteMode();
     $this->isBlacklisted();
     $this->checkBadBot(true);
   }

   public function checkServiceRequest() {
     $this->checkSiteMode();
     $this->isBlacklisted();
     $this->checkBadBot(true);
   }

   public function isBlacklisted() {

   }

   public function checkSiteMode() {

   }
   public function checkDevMode($site=null) {
		if($site==null) $site=SITENAME;
    if(defined("DEV_MODE_IP") && strlen(DEV_MODE_IP)>0) {
    	$ips=explode(",",DEV_MODE_IP);
    	if(count($ips)>0) {
    		loadHelpers("devmode");
    		__testDevMode($ips);
    	}
    }
	 }
   protected function checkBadBot($autoBlock=true) {
		if(!getConfig("STOP_BAD_BOTS")) return false;
		$uAgent=$_SERVER['HTTP_USER_AGENT'];
		$blockedAgents=getConfig("BAD_BOTS");
		$regex="/\b({$blockedAgents})\b/i";
		if(preg_match($regex,$uAgent)>0) {
			if($autoBlock) {
				header("HTTP/1.1 403 Bots Not Allowed");
				exit("Bots Not Allowed");
			}
		}
		return false;
	}
  public function generateMAuthKey() {
   if(!isset($_REQUEST['deviceuuid'])) $_REQUEST['deviceuuid']="";
   $key=md5(base64_encode($_REQUEST['site'].$_SERVER['HTTP_USER_AGENT'].SiteID.$_REQUEST['deviceuuid'].$_SESSION['SESS_USER_ID']));
   unset($_REQUEST['deviceuuid']);
   return $key;
  }
 }
?>
