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

  public function detect($redirect=true) {
    $site="";
    $adminApps=explode(",", DOMAIN_CONTROLS_ADMINAPP);
    $mappedSite=$this->detectDomainApp();
    define("WEBDOMAIN",$mappedSite);

    $uri=$_SERVER['REQUEST_URI'];
    if(substr($uri, 0,1)=="/") $uri=substr($uri, 1);
    $uri=explode("/", $uri);
    if(count($uri)==1) {
      $fp=ROOT.APPS_FOLDER.$uri[0]."/apps.cfg";
      if(file_exists($fp)) {
        if($mappedSite!=$uri[0]) {
          $lx=SiteLocation."?site={$uri[0]}";
        } else {
          $lx=SiteLocation;
        }
        header("Location:$lx");
      }
    }
    
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

            if(strlen($query)>0) {
              $uri=WEBROOT.PAGE."?".$query;
            } else {
              $uri=WEBROOT.PAGE;
            }
            if($redirect) {
              header("Location:{$uri}");
            } else {
              $site=$mappedSite;
            }
          }
        } else {
          $site=$_REQUEST['site'];
        }
  		} else {
        //Detect APPSITE
        $site=$mappedSite;
      }
  	} else {
      //Detect APPSITE
      $site=$mappedSite;
    }
    return $this->setupAppSite($site);
  }
  protected function detectDomainApp() {
    $siteServerList = [];

    $server=strtoupper(_server("HTTP_HOST"));
    $server=str_replace("WWW.","",$server);
    $siteServerList[] = $server;

    $uri = _server("REQUEST_URI");
    $uri = explode("/", $uri);
    array_shift($uri);

    if(count($uri)>=4) {
      $siteServerList[] = $server."/".$uri[0];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1]."/".$uri[2];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1]."/".$uri[2]."/".$uri[3];

      $siteServerList = array_reverse($siteServerList);
    } elseif(count($uri)>=3) {
      $siteServerList[] = $server."/".$uri[0];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1]."/".$uri[2];

      $siteServerList = array_reverse($siteServerList);
    } elseif(count($uri)>=2) {
      $siteServerList[] = $server."/".$uri[0];
      $siteServerList[] = $server."/".$uri[0]."/".$uri[1];

      $siteServerList = array_reverse($siteServerList);
    } elseif(count($uri)>=1) {
      $siteServerList[] = $server."/".$uri[0];

      $siteServerList = array_reverse($siteServerList);
    }

    foreach ($siteServerList as $domainURI) {
      $domainURI = strtoupper($domainURI);
      if(array_key_exists($domainURI,$this->domainList)) {
        define("DOMAIN_URI", strtolower(str_replace($server, "", $domainURI)));
        define("DOMAIN_LINK", strtolower($domainURI));
        $dsite = $this->domainList[$domainURI]["appsite"];
        return $dsite;
      }
    }

    //printArray([$uri,_server("REQUEST_URI"), $siteServerList,$this->domainList]);

    define("DOMAIN_URI", "");
    define("DOMAIN_LINK", strtolower($server));
    if(array_key_exists($server,$this->domainList)) {
      $dsite=$this->domainList[$server]["appsite"];
      return $dsite;
    } else {
      return DEFAULT_SITE;
    }
  }
  protected function setupAppSite($site) {
    if(!defined("SITENAME")) define('SITENAME',$site);
    setcookie('LGKS_SESS_SITE',SITENAME,time()+3600,"/",null, isHTTPS());
    //setcookie('LGKS_SESS_SITE',SITENAME,time()+3600,"/",null, isHTTPS(), (isHTTPS()?false:true));
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
