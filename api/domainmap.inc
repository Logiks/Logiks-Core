<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class DomainMap {
	private $domainList=array();
	private $siteList=array();
	private $notMappedSiteList=array();

	function __construct() {
		$dmF=ROOT.CFG_FOLDER."jsondb/domainmap.json";
		if(!file_exists($dmF)) {
			$this->createDefaultDomainMap();
		}
		$dmArr=json_decode(file_get_contents($dmF),true);
		if($dmArr==null) {
			$dmArr=array();
		}
		foreach($dmArr as $a=>$b) {
			if($b["active"]) {
				$this->domainList[strtoupper(trim($a))]=$b;
				array_push($this->siteList,$b["appsite"]);
			}
		}

		if(DOMAIN_CONTROLS_FLOWTYPE=="appsite") $list=$this->getAppList();
		elseif(DOMAIN_CONTROLS_FLOWTYPE=="apps") $list=$this->getControlAppsList();
		elseif(DOMAIN_CONTROLS_FLOWTYPE=="site") $list=$this->getSiteList();
		else $list=$this->getAppList();
		//blocking

		if(DOMAIN_CONTROLS_FLOWTYPE!="blocking") {
			foreach($list as $a=>$b) {
				if(!in_array($b,$this->siteList)) {
					array_push($this->notMappedSiteList,$b);
				}
			}
		}
	}
	function getDomainList() {
		return $this->domainList;
	}
	function getMappedSiteList() {
		return $this->siteList;
	}
	function getNotMappedSiteList() {
		return $this->notMappedSiteList;
	}
	function checkServiceHost() {
		$server=strtoupper($_SERVER["HTTP_HOST"]);
		$server=str_replace("WWW.","",$server);

		if(array_key_exists($server,$this->domainList)) {
			$dsite=$this->domainList[$server]["appsite"];
			if(isset($_REQUEST['site'])) {
				if($_REQUEST['site']==$dsite) {
					$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
				} elseif(in_array($_REQUEST['site'],$this->notMappedSiteList)) {
					$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
				} else {
					$_SESSION["LGKS_SESS_SITE"]=$dsite;
				}
			} else {
				$_SESSION["LGKS_SESS_SITE"]=$dsite;
			}
		} else {
			if(isset($_REQUEST['site'])) {
				$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
			} elseif(!isset($_SESSION['LGKS_SESS_SITE'])) {
				if(isset($_SERVER["HTTP_REFERER"])) {
					$pos1=strpos($_SERVER["HTTP_REFERER"],"site=");
					if($pos1>0) {
						$d1=substr($_SERVER["HTTP_REFERER"],$pos1);
						$pos2=strpos($d1,"&");
						if($pos2>0) {
							$d1=substr($d1,0,$pos2);
						}
						$pos3=strpos($d1,"=")+1;
						$d1=substr($d1,$pos3);
						$_SESSION["LGKS_SESS_SITE"]=$d1;
					} else {
						$_SESSION["LGKS_SESS_SITE"]=$defSite;
					}
				} else {
					$_SESSION["LGKS_SESS_SITE"]=DEFAULT_SITE;
				}
			} else {
				return  $_SESSION["LGKS_SESS_SITE"];
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
				} elseif(in_array($_REQUEST['site'],$this->notMappedSiteList)) {
					$_SESSION["LGKS_SESS_SITE"]=$_REQUEST['site'];
				} else {
					$_SESSION["LGKS_SESS_SITE"]=$dsite;
					if(DOMAIN_CONTROLS_REDIRECT=="true") {
						$qp=$_SERVER["QUERY_STRING"];
						if(strlen($qp)<=0) {
							$qp="?site=$dsite";
						} else {
							$qp=str_replace("=".$_REQUEST['site'],"=".$dsite,$qp);
						}
						$l="index.php{$qp}";
						$l=str_replace("?&","?",$l);
						header("Location:$l");
					}
				}
			} else {
				$_SESSION["LGKS_SESS_SITE"]=$dsite;
				if(DOMAIN_CONTROLS_REDIRECT=="true") {
					$qp=$_SERVER["QUERY_STRING"];
					$l="index.php?$qp&site=".$dsite;
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
	public static function createDefaultDomainMap() {
		$dmF=ROOT.CFG_FOLDER."jsondb/domainmap.json";
		if(!is_writable(dirname($dmF))) exit("DomainMap Configuration Cannot Be Created ...");
		$dmArr=array();
		$dmArr["localhost"]=array("appsite"=>"cms","nodal"=>"","active"=>true,"doc"=>date("Y-m-d"),"doe"=>date("Y-m-d"));
		$dmArr=json_encode($dmArr);
		file_put_contents($dmF,$dmArr);
		chmod($dmF,0777);
	}
}
?>
