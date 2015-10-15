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
     $this->checkDevMode();
     $this->checkUserSiteAccess();
     $this->isBlacklisted(PAGE,SITENAME);
     $this->isLinkRedirected(PAGE);
     $this->checkBadBot(true);
   }

   public function checkServiceRequest() {
     //$this->checkSiteMode();
     $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     $this->checkBadBot(true);

   }

   public function checkUserSiteAccess() {
      if(session_check()) {
        if(isset($_SESSION["SESS_ACCESS_ID"]) && $_SESSION["SESS_ACCESS_ID"]=="1") {
          return true;
        }
        if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]=="1") {
          return true;
        }
        if(in_array(SITENAME, $_SESSION["SESS_ACCESS_SITES"])) return true;
        trigger_logikserror(403.1);
      } else {
        if(defined("ACCESS")) {
          if((strtolower(ACCESS)=="public")) return true;
          trigger_logikserror(403.1);
        } else {
          trigger_logikserror("Logiks Site Is Misconfigured",null,800);
        }
      }
   }

   function isLinkRedirected($page=null,$site=SITENAME) {
      if($page==null) $page=PAGE;
      //TODO : After DB
      //301,307
      return false;
   }

   public function isBlacklisted($page,$site=SITENAME) {
      //TODO : After DB
      //$client=$_SERVER["REMOTE_ADDR"];
      //802/806
   }

   public function checkSiteMode() {
      if(!defined("PUBLISH_MODE")) return false;

      switch (strtolower(PUBLISH_MODE)) {
        case 'blocked':
          trigger_logikserror(807);
          exit();
          break;
        case 'restricted':
          trigger_logikserror(806);
          exit();
          break;

        case 'whitelist':
          trigger_logikserror(806.1);
          exit();
          break;

        case 'maintainance':
          trigger_logikserror(804);
          exit();
          break;

        case 'underconstruction':
          trigger_logikserror(805);
          exit();
          break;
      }
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
}
?>
