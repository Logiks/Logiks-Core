<?php
/*
 * This Class is meant to contain all security related functions. This is an extraction
 * of functions from security.php.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 05/04/2014
 * Version: 1.0
 */

require_once __DIR__."/logiksValidation/RateLimit.php";
require_once __DIR__."/logiksValidation/CSRFProtection.php";

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
     $this->checkServiceInput();

     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);

     $this->checkRateLimit("svc");
     $this->checkCSRF();

     $this->checkSiteMode();
     $this->checkDevMode();
     $this->checkUserSiteAccess();     

     runHooks("serviceSecurityChecks");
   }

   public function checkAPIRequest() {
     if(isset($_REQUEST['scmd'])) $this->checkServiceInput();

     if(REQUEST_CHECK_BLACKLIST) $this->isBlacklisted("SCMD-".$_REQUEST['scmd'],SITENAME);
     if(REQUEST_CHECK_BADBOT) $this->checkBadBot(true);

     $this->checkRateLimit("api");

     $this->checkSiteMode();

     runHooks("apiSecurityChecks");
   }

   /*
   * Validates the request parameters that are turned into file paths by the service
   * layer (scmd, slugpath). Anything outside a conservative whitelist is rejected, so
   * a crafted value can never walk out of the services/modules folders.
   */
   public function checkServiceInput() {
     $rules=[
         'scmd'=>'/^[A-Za-z0-9_\-.:@\/]+$/',
         'slugpath'=>'/^[A-Za-z0-9_\-.\/]*$/',
       ];
     foreach($rules as $key=>$pattern) {
       if(!isset($_REQUEST[$key])) continue;
       $v=$_REQUEST[$key];
       if(!is_string($v) || strpos($v,'..')!==false || !preg_match($pattern,$v)) {
         _log("Rejected service request, illegal {$key}","security",LogiksLogger::LOG_ALERT,["client_ip"=>RateLimit::clientIp()]);
         http_response_code(400);
         printServiceErrorMsg(400, "Illegal Service Request");
         exit();
       }
     }
   }

   /*
   * Per-client request throttling for services and APIs. Counters are kept in tmp/ratelimit/.
   * Config (config/security.cfg): RATELIMIT_ENABLED, RATELIMIT_SERVICE_MAX, RATELIMIT_SERVICE_WINDOW.
   * Fails open: if the counter store is unwritable the request is allowed (and logged by the web server),
   * because a storage problem must not take the whole service layer down.
   */
   public function checkRateLimit($bucket="svc") {
     if(!$this->configEnabled("RATELIMIT_ENABLED",true)) return true;
     if(PHP_SAPI==="cli") return true;

     $max=(int)getConfig("RATELIMIT_SERVICE_MAX",1200);
     $window=(int)getConfig("RATELIMIT_SERVICE_WINDOW",60);
     if($max<=0 || $window<=0) return true;

     $limit=new RateLimit(null,true);
     $ip=RateLimit::clientIp();
     if(!$limit->check("{$bucket}:{$ip}",$max,$window)) {
       _log("Rate limit exceeded","security",LogiksLogger::LOG_WARNING,["client_ip"=>$ip,"bucket"=>$bucket]);
       http_response_code(429);
       header("Retry-After: ".$limit->retryAfter());
       printServiceErrorMsg(429, "Too Many Requests");
       exit();
     }
     return true;
   }

   /*
   * CSRF protection for state-changing service calls (POST/PUT/PATCH/DELETE).
   * Config CSRF_MODE: off | log (default, records failures but allows) | enforce (rejects with 403).
   * The token is read from the csrf_token field or the X-CSRF-Token header, and the current
   * token is sent to the client in the X-CSRF-Token response header.
   * Requests without a session cookie (token/API-key clients) cannot be forged through a
   * browser and are exempt.
   */
   public function checkCSRF() {
     $mode=strtolower((string)getConfig("CSRF_MODE","log"));
     if($mode!=="log" && $mode!=="enforce") return true;
     if(PHP_SAPI==="cli") return true;
     if(session_status()!==PHP_SESSION_ACTIVE) return true;

     header("X-CSRF-Token: ".CSRFProtection::getToken(),true);

     $method=strtoupper(_server('REQUEST_METHOD'));
     if(!in_array($method,["POST","PUT","PATCH","DELETE"])) return true;
     if(!isset($_COOKIE[session_name()])) return true;

     $token=isset($_SERVER['HTTP_X_CSRF_TOKEN'])?$_SERVER['HTTP_X_CSRF_TOKEN']:(isset($_POST['csrf_token'])?$_POST['csrf_token']:'');
     if(is_string($token) && CSRFProtection::validateToken($token,false)) return true;

     _log("CSRF validation failed ({$mode})","security",LogiksLogger::LOG_ALERT,[
         "client_ip"=>RateLimit::clientIp(),"scmd"=>isset($_REQUEST['scmd'])?$_REQUEST['scmd']:null,"method"=>$method
       ]);
     if($mode==="enforce") {
       http_response_code(403);
       printServiceErrorMsg(403, "CSRF validation failed");
       exit();
     }
     return false;
   }

   protected function configEnabled($key,$default=true) {
     $v=getConfig($key);
     if($v===false) return $default;
     return !in_array(strtolower((string)$v),["false","0","off","no",""],true);
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
          trigger_logikserror(401);
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
