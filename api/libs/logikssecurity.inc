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
    if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted(PAGE,SITENAME);
    if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);
     
    $this->checkSiteMode();
    $this->checkDevMode();
    $this->checkUserSiteAccess();
    //$this->checkUserPageAccess(); It can only be done after page load is complete

    // $this->isLinkRedirected(PAGE);

     runHooks("requestSecurityChecks");
   }

   public function checkServiceRequest() {
     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);

     $this->checkSiteMode();
     $this->checkDevMode();
     $this->checkUserSiteAccess();     

     runHooks("serviceSecurityChecks");
   }

   public function checkAPIRequest() {
     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);

     $this->checkSiteMode();

     runHooks("apiSecurityChecks");
   }

   /*
   * Global access checking. This function checks for generic condition that user is logged in or not.
   * This does not check what privileges or roles user has in doing so.
   *
   */
   public function checkUserSiteAccess() {
      if(defined("ACCESS")) {
        switch (strtolower(ACCESS)) {
            case 'public':
              return true;
              break;
            case 'private':
                if(session_check()) return true;
                if(defined("SERVICE_ROOT")) return true;
                $noLogin=explode(",", getConfig("LOGIN_EXEMPT"));
                $noLogin[]=getConfig("PAGE_LOGIN");
                if(in_array(current(explode("/",PAGE)), $noLogin)) {
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
	$sqlData=_db(true)->_selectQ(_dbTable("security_iplist",true),"count(*) as max")
		->_where(array("ipaddress"=>$client,"allow_type"=>"blacklist","active"=>"true"))
		->_GET();

	if($sqlData[0]['max']>0) {
		trigger_logikserror("Your IP has been blacklisted",E_USER_ERROR);
	}
   }

   public function checkSiteMode() {
      if(!defined("PUBLISH_MODE")) return false;

      switch (strtolower(PUBLISH_MODE)) {
        case 'blocked':
          trigger_logikserror(807);
          break;
        case 'restricted':
          trigger_logikserror(806);
          break;
        case 'maintainance':
          trigger_logikserror(804);
          break;
        case 'underconstruction':
          trigger_logikserror(805);
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

    $botFile=ROOT.DATA_FOLDER."lookups/badbots.dat";
    $botPHP=ROOT.CACHE_FOLDER."badbots.php";
    if(!file_exists($botPHP) || (time()-filectime($botPHP))>getConfig("CACHE_EXPIRY")) {
      $botData=file_get_contents($botFile);
      $botData=explode("\n", $botData);
      $botData=implode("|", $botData);

      file_put_contents($botPHP, "<?php return '{$botData}'; ?>");
    }
    $blockedAgents=include $botPHP;
    $uAgent=_server('HTTP_USER_AGENT');

		$regex="/\b({$blockedAgents})\b/i";
		if(preg_match($regex,$uAgent)>0) {
			if($autoBlock) {
				header("HTTP/1.1 403 Bots Not Allowed");
				exit("Bots Not Allowed");
			}
		}
		return false;
	}

  public static function isPageAccessible($pageObj) {
    if(is_a($pageObj, "LogiksPage")) {
      return $pageObj->isAccessible();
    } else {
      return checkUserRoles("PAGES",$pageObj,"PAGES");
    }
  }
}
?>
