<?php
/*
 * This Class is meant to contain all security related functions. This is an extraction
 * of functions from security.php.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 05/04/2014
 * Version: 1.0
 */

 class LogiksSecurity {

   public function checkPageRequest() {
     $this->checkSiteMode();
     $this->checkDevMode();
     $this->checkUserSiteAccess();
     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted(PAGE,SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);
     // $this->isLinkRedirected(PAGE);

     runHooks("requestSecurityChecks");
   }

   public function checkServiceRequest() {
     //$this->checkSiteMode();
     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);

     runHooks("serviceSecurityChecks");
   }

   public function checkUserSiteAccess() {
      if(defined("ACCESS")) {
        switch (strtolower(ACCESS)) {
            case 'public':
              return true;
              break;
            case 'private':
                if(session_check()) return true;
                $noLogin=explode(",", getConfig("PAGE_NOLOGIN"));
                $noLogin[]=getConfig("PAGE_LOGIN");
                if(in_array(PAGE, $noLogin)) {
                  return true;
                }
                redirectTo(_link(getConfig("PAGE_LOGIN")));
               break;
            default:
              trigger_logikserror(403.1);
              break;
          }
      } else {
        trigger_logikserror("Logiks Site Is Misconfigured",null,800);
      }
   }

   public function isBlacklisted($page,$site=SITENAME) {
      $client=_server("REMOTE_ADDR");
      
      $tbl=_dbTable("security_iplist",true);
      $sql=_db(true)->_selectQ($tbl,"count(*) as max")
            ->_where(array("ipaddress"=>$client,"allow_type"=>"blacklist","active"=>"true"));

      $r=_dbQuery($sql,true);
      $dt=_dbFetch($r,true);
      _dbFree($r);

      if($dt['max']>0) {
        trigger_logikserror("Your IP has been blacklisted",E_USER_ERROR);
      }
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
    if(defined("DEV_MODE_ENABLED") && DEV_MODE_ENABLED && defined("DEV_MODE_IP") && strlen(DEV_MODE_IP)>0) {
    	$ips=explode(",",DEV_MODE_IP);
    	if(count($ips)>0) {
    		loadHelpers("devmode");
    		__testDevMode($ips);
    	}
    }
	 }

   protected function checkBadBot($autoBlock=true) {
		if(!getConfig("STOP_BAD_BOTS")) return false;
		$uAgent=_server('HTTP_USER_AGENT');
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
