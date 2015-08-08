<?php
/*
 * This class controls the Domain Control Logic for Logiks Framework.
 * Here we manage what Domains are mapped to which apps and thus vice versa.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

class DomainMap {

  private $appList=null;

  private $domainList=array();
  private $mappedApps=array();
	private $notMappedSiteList=array();

  public function __construct() {
    $dataArr=loadJSONConfig("domainmap");

    $this->getAppList();

    foreach($dataArr as $a=>$b) {
			if($b["active"]) {
        $title=strtoupper(trim($a));
				$this->domainList[$title]=$b;
        if(!in_array($b["appsite"],$this->mappedApps)) array_push($this->mappedApps,$b["appsite"]);
			}
		}
    foreach ($this->appList as $app) {
      if(!in_array($app, $this->mappedApps)) $this->notMappedSiteList[]=$app;
    }
  }

  public function detect() {
    $site="";
    $adminApps=explode(",", DOMAIN_CONTROLS_ADMINAPP);
    if(isset($_REQUEST['site'])) {
  		if(is_dir(ROOT.APPS_FOLDER.$_REQUEST['site']) && file_exists(ROOT.APPS_FOLDER.$_REQUEST['site']."/apps.cfg")) {
        if(DOMAIN_CONTROLS_STRICT) {
          if(in_array($_REQUEST['site'], $adminApps)) {
            $site=$_REQUEST['site'];
          } else {
            $dx=$_GET;
            unset($dx['site']);

            $query = array_map(function($value, $key) {
                return "{$key}={$value}";
            }, array_values($dx), array_keys($dx));
            $query = implode(' ', $query);

            $uri=ROOTURL.PAGE."?".$query;
            header("Location:{$uri}");
          }
        } else {
          $site=$_REQUEST['site'];
        }
  		} else {
        //Detect APPSITE
        $site=$this->detectDomainApp();
      }
  	} else {
      //Detect APPSITE
      $site=$this->detectDomainApp();
    }
    return $this->setupAppSite($site);
  }
  protected function detectDomainApp() {
    $server=strtoupper($_SERVER["HTTP_HOST"]);
		$server=str_replace("WWW.","",$server);

    if(array_key_exists($server,$this->domainList)) {
      $dsite=$this->domainList[$server]["appsite"];
      return $dsite;
    } else {
      return DEFAULT_SITE;
    }
  }
  protected function setupAppSite($site) {
    define('SITENAME',$site);
    setcookie('LGKS_SESS_SITE',SITENAME,time()+3600,"/");
    $_REQUEST['site']=SITENAME;
    return SITENAME;
  }
  public function getDomainList() {
		return $this->domainList;
	}
  public function getMappedSiteList() {
		return $this->mappedApps;
	}
  public function getNotMappedSiteList() {
		return $this->notMappedSiteList;
	}

  public function getAppList() {
    if($this->appList==null) {
      $list=scandir(ROOT.APPS_FOLDER);
  		$last=array();
  		unset($list[0]);unset($list[1]);
  		foreach($list as $a=>$site) {
  			if(file_exists(ROOT.APPS_FOLDER.$site."/apps.cfg")) {
  				array_push($last,$site);
  			}
  		}
      $this->appList=$last;
    }
    return $this->appList;
	}
}
?>
