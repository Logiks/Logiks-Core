<?php
/*
 * This class is a gateway to smarty engine and is used to operate on templates.
 * This class configures smarty to be used inside Logiks Framework
 * This is the template engine used by Logiks 3, Powered By Smarty
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

define('SMARTY_DIR', dirname(__FILE__) . "/libs/smarty/");
include_once dirname(__FILE__) . "/libs/smarty/Smarty.inc";

class TemplateEngine {
	
	private $smarty;
	private $tpl;
	private $cacheID;
	
	public function __construct() {
		$this->smarty = $this->createEngine();
	}
	
	public function getSmartyEngine() {
		return $this->smarty;
	}
	
	public function getCacheID() {
		return $this->cacheID;
	}
	
	public function isCached() {
		if(strlen($this->cacheID)<=0) {
			return false;
		} else {
			return $this->smarty->isCached($this->tpl,$this->cacheID);
		}
	}
	
	public function getCacheDir() {
		return $this->smarty->cache_dir;
	}
	
	public function createEngine() {
		$sm = new Smarty();	
		
		$sm->plugins_dir[] = array(SMARTY_DIR . "plugins",SMARTY_DIR . "sysplugins");
		if(defined("APPS_TEMPLATE_FOLDER")) {
			$sm->template_dir = APPROOT . APPS_TEMPLATE_FOLDER;
			
			if(defined("APPS_CONFIG_FOLDER")) {
				$sm->config_dir = APPROOT . APPS_CONFIG_FOLDER;
			} else {
				$sm->config_dir   = ROOT . CFG_FOLDER;
			}
			if(defined("APPS_CACHE_FOLDER")) {
				$sm->compile_dir = APPROOT . CACHE_TEMPLATES_FOLDER;
			} else {
				$sm->compile_dir  = ROOT . CACHE_TEMPLATES_FOLDER;
			}
			if(defined("APPS_CACHE_FOLDER")) {
				$sm->cache_dir = APPROOT . APPS_CACHE_FOLDER;
			} else {
				$sm->cache_dir    = ROOT . CACHE_FOLDER;
			}
		} else {
			$sm->template_dir = ROOT . TEMPLATE_FOLDER;
			$sm->config_dir   = ROOT . CFG_FOLDER;
			$sm->compile_dir  = ROOT . CACHE_TEMPLATES_FOLDER;
			$sm->cache_dir    = ROOT . CACHE_FOLDER;
		}
		if(!file_exists($sm->compile_dir)) {
			mkdir($sm->compile_dir,0777,true);
			chmod($sm->compile_dir,0777);
		}
		if(!file_exists($sm->cache_dir)) {
			mkdir($sm->cache_dir,0777,true);
			chmod($sm->cache_dir,0777);
		}
		//$debug_tpl = 'file:' . SMARTY_DIR . 'debug.tpl';
		
		$sm->setCompileCheck(SMARTY_COMPILE_CHECK);		
		$sm->setCacheLifetime(TEMPLATE_EXPIRY);
		if(TEMPLATE_CACHE=="true" || TEMPLATE_CACHE==1)
			$sm->setCaching(true);
		else
			$sm->setCaching(false);
		
		$sm->assign($GLOBALS['CONFIG']);
		if(getConfig("SMARTY_PHP_ALLOW")=="true") {
			$sm->php_handling=Smarty::PHP_ALLOW;
			$sm->allow_php_tag=true;
		}
		$a=getConfig("SMARTY_PHP_ERROR_LEVEL");
		$a=explode(",",$a);
		$err=0;
		foreach($a as $b) {
			if(defined($b)) {
				$err=$err|constant($b);
			}
		}
		$sm->error_reporting=$err;
		return $sm;
	}
	
	public function recreateEngine() {
		$this->smarty = $this->createSmartEngine();
	}
	
	public function clearCache() {
		$this->smarty->clearCache($this->tpl,$this->cacheID);
	}
	
	public static function printTemplateFile($tmpl,$queries,$dataArr=null) {
		$templates=new TemplateEngine();
		$templates->loadTemplate($tmpl);
		if(strlen($queries)>0) $templates->loadSQL($queries);
		if($dataArr==null) {
			$dataArr=array();
			$dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			$dataArr["time"]=date(getConfig("TIME_FORMAT"));
			$dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));
			$dataArr["site"]=SITENAME;
			if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";
			if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
		}
		ob_start();
		$templates->display();
		$body=ob_get_contents();
		ob_clean();
		
		$body=TemplateEngine::processTemplate($body,$dataArr);
		return $body;
	}
	
	public function loadTemplate($template, $cache_id="") {		
		$this->tpl=$template;
		$this->cacheID=$cache_id;
	}
	
	public function addVar($name,$value) {
		$this->smarty->assign($name,$value);
		return true;
	}
	
	public function removeVar($name) {
		unset($this->smarty->tpl_vars[$name]);
		return true;
	}
	
	public function display($data=array(),$caching=TEMPLATE_CACHE_ON_DISPLAY) {
		//$caching==0 Means Temp Data
		//$caching==1 Means Replace The Old Data In Cache
		//$caching==2 Means Use Old Data
		foreach($data as $a=>$b) {
			$this->addVar($a,$b);
		}
		if($this->tpl!=null) {
			//echo $caching;
			if(is_numeric($caching)) {
				$c=$this->smarty->getCaching();
				if($caching==0) {
					$this->smarty->setCaching(0);
				} elseif($caching==1) {
					$this->clearCache();
					$this->smarty->setCaching(1);					
				} elseif($caching==2) {
					$this->smarty->setCaching(2);
				}
				
				$this->smarty->display($this->tpl,$this->cacheID);
				$this->smarty->setCaching($c);
			} else {
				$this->smarty->display($this->tpl,$this->cacheID);
			}
		}
	}
	
	public static function replaceFromEnviroment($in) {
		$in=$in[0];
		$in=substr($in,1,strlen($in)-2);
		if(isset($_REQUEST[$in])) return $_REQUEST[$in];
		elseif(isset($_SESSION[$in])) return $_SESSION[$in];
		//elseif(isset($GLOBALS[$in])) return $GLOBALS[$in];
		elseif(isset($_SERVER[$in])) return $_SERVER[$in];
		return "";
	}
	
	public static function processQuery($q) {
		$q=preg_replace_callback("/#[a-zA-Z0-9-_]+#/",array("TemplateEngine","replaceFromEnviroment"),$q);
		return $q;
	}
	public static function processTemplate($txt,$data=array(),$addEdit=true) {
		if(strlen($txt)>0) {
			foreach($data as $a=>$b) {
				$t="#$a#";
				$txt=str_replace($t,$b,$txt);
			}
			$txt=preg_replace_callback("/#[a-zA-Z0-9-_]+#/",array("TemplateEngine","replaceFromEnviroment"),$txt);
			if($addEdit) {
				$txt=preg_replace_callback("/[_]{3}+/",
					create_function('$in', 'return "<span class=editable>{$in[0]}</span>";'),$txt);
			}
		}		
		return $txt;
	}
	
	public function loadSQL($sqlData) {
		if(strlen($sqlData)<=0) return;
		if(!is_array($sqlData)) {
			$sqlData=explode("\n",$sqlData);
		}
		$cnt=0;
		$allData=array();
		foreach($sqlData as $q) {
			if(strlen($q)>0) {
				if(strpos(" ".$q,"#")==1) {
					$allData[$cnt]=array();
				} else {
					$sql=TemplateEngine::processQuery($q);
					$res=_dbQuery($sql);
					if($res) {
						$data=_db()->fetchAllData($res);
						$allData[$cnt]=$data;
					} else {
						$allData[$cnt]=array();
					}
				}
			} else {
				$allData[$cnt]=array();
			}			
			$cnt++;
		}
		$this->addVar("SQL",$allData);
	}
}
?>
