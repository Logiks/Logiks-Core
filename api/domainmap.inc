<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class DomainMap {	
	private $domainList=array();
	private $appList=array();
	private $siteList=array();
		
	function __construct($dbCon) {
		$sql="SELECT host,appsite,nodal FROM ".Database::getSysTable("aliaspath")." WHERE active='true'";
		$res=$dbCon->executeQuery($sql);
		if($res) {
			while($row=$dbCon->fetchData($res)) {				
				$this->domainList[strtoupper(trim($row["host"]))]=$row;
				array_push($this->siteList,$row["appsite"]);
			}
			$dbCon->freeResult($res);
		}
		if(DOMAIN_CONTROLS_FLOWTYPE=="appsite") $list=$this->getAppList();
		elseif(DOMAIN_CONTROLS_FLOWTYPE=="apps") $list=$this->getControlAppsList();
		elseif(DOMAIN_CONTROLS_FLOWTYPE=="site") $list=$this->getSiteList();
		else $list=$this->getAppList();
		
		if(DOMAIN_CONTROLS_FLOWTYPE!="blocking") {
			foreach($list as $a=>$b) {
				if(!in_array($b,$this->siteList)) {
					array_push($this->appList,$b);
				}
			}
		}		
	}
	function getDomainList() {
		return $this->domainList;
	}
	function getControlledSiteList() {
		return $this->siteList;
	}
	function getNotControledSite() {
		return $this->appList;
	}
	function checkServiceHost() {
		$server=strtoupper($_SERVER["HTTP_HOST"]);
		$server=str_replace("WWW.","",$server);
		
		if(array_key_exists($server,$this->domainList)) {
			$dsite=$this->domainList[$server]["appsite"];
			if(isset($_REQUEST['site'])) {
				if($_REQUEST['site']==$dsite) {
					$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
				} elseif(!in_array($_REQUEST['site'],$this->appList)) {
					if($_REQUEST['site']!=$this->domainList[$server]["appsite"]) {
						$_SESSION["LGKS_SESS_SITE"]=$this->domainList[$server]["appsite"];						
					}
				} else {
					$_SESSION["LGKS_SESS_SITE"]=$dsite;//$_REQUEST['site'];
				}
			} else {
				$_SESSION["LGKS_SESS_SITE"]=$this->domainList[$server]["appsite"];
			}
		} else {
			if(isset($_REQUEST['site'])) {
				$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
			} else {
				$_SESSION["LGKS_SESS_SITE"]=DEFAULT_SITE;
			}
		}
		return $_SESSION["LGKS_SESS_SITE"];
	}
	function checkHost() {
		$server=strtoupper($_SERVER["HTTP_HOST"]);
		$server=str_replace("WWW.","",$server);
		if(array_key_exists($server,$this->domainList)) {
			$dsite=$this->domainList[$server]["appsite"];
			if(isset($_REQUEST['site'])) {
				if($_REQUEST['site']==$dsite) {
					$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
				} elseif(!in_array($_REQUEST['site'],$this->appList)) {
					if($_REQUEST['site']!=$this->domainList[$server]["appsite"]) {
						$_SESSION["LGKS_SESS_SITE"]=$this->domainList[$server]["appsite"];
						if(DOMAIN_CONTROLS_REDIRECT=="true") {
							$qp=$_SERVER["QUERY_STRING"];
							if(strlen($qp)<=0) {
								$qp="site=".$this->domainList[$server]["appsite"];
							} else {
								$qp=str_replace("=".$_REQUEST['site'],"=".$this->domainList[$server]["appsite"],$qp);
							}
							header("Location:index.php?$qp");
						}
					}
				} else {
					$_SESSION["LGKS_SESS_SITE"]=$dsite;//$_REQUEST['site'];
					if(DOMAIN_CONTROLS_REDIRECT=="true") {
						$qp=$_SERVER["QUERY_STRING"];
						if(strlen($qp)<=0) {
							$qp="?site=$dsite";
						} else {
							$qp="?{$qp}&site=$dsite";
						}
						$l="index.php{$qp}";
						$l=str_replace("?&","?",$l);
						header("Location:$l");
					}
				}
			} else {
				$_SESSION["LGKS_SESS_SITE"]=$this->domainList[$server]["appsite"];
				if(DOMAIN_CONTROLS_REDIRECT=="true") {
					$qp=$_SERVER["QUERY_STRING"];
					$l="index.php?$qp&site=".$this->domainList[$server]["appsite"];
					$l=str_replace("?&","?",$l);
					header("Location:$l");
				}
			}
		} else {
			if(isset($_REQUEST['site'])) {
				$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
			} else {
				$_SESSION["LGKS_SESS_SITE"]=DEFAULT_SITE;
				if(DOMAIN_CONTROLS_REDIRECT=="true") {
					$qp=$_SERVER["QUERY_STRING"];
					$l="index.php?$qp&site=".DEFAULT_SITE;
					$l=str_replace("?&","?",$l);
					header("Location:$l");
				}
			}
		}
		return $_SESSION["LGKS_SESS_SITE"];
	}
	
	public static function getControlAppsList() {
		$list=scandir(ROOT.APPS_FOLDER);
		$last=array();
		unset($list[0]);unset($list[1]);
		foreach($list as $a=>$site) {
			if(file_exists(ROOT.APPS_FOLDER.$site."/apps.cfg") 
				&& !file_exists(ROOT.APPS_FOLDER.$site."/cms.php")) {
				array_push($last,$site);
			}			
		}
		return $last;
	}
	public static function getSiteList() {
		$list=scandir(ROOT.APPS_FOLDER);
		$last=array();
		unset($list[0]);unset($list[1]);
		foreach($list as $a=>$site) {
			if(file_exists(ROOT.APPS_FOLDER.$site."/apps.cfg") 
				&& file_exists(ROOT.APPS_FOLDER.$site."/cms.php")) {
				array_push($last,$site);
			}			
		}
		return $last;
	}
	public static function getAppList() {
		$list=scandir(ROOT.APPS_FOLDER);
		$last=array();
		unset($list[0]);unset($list[1]);
		foreach($list as $a=>$site) {
			if(file_exists(ROOT.APPS_FOLDER.$site."/apps.cfg")) {
				array_push($last,$site);
			}			
		}
		return $last;
	}
}
?>
