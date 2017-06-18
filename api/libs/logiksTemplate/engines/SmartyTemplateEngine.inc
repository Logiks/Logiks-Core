<?php
/*
 * SmartyTemplateEngine connects and configures Smart Engine For Logiks Framework
 * This class is a gateway to smarty engine and is used to operate on templates.
 * This class configures smarty to be used inside Logiks Framework
 * This is the template engine used by Logiks 3, Powered By Smarty
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class SmartyTemplateEngine extends AbstractTemplateEngine {

  private $smartyEngine=null;

  public function __construct($allowPHP=NULL) {
    $GLOBALS['classPath'][]=API_FOLDER."vendors/smarty/smarty/";
    $GLOBALS['classPath'][]=API_FOLDER."vendors/smarty/smarty/plugins/";
    $GLOBALS['classPath'][]=API_FOLDER."vendors/smarty/smarty/sysplugins/";
    $GLOBALS['classPath'][]=API_FOLDER."libs/logiksTemplate/plugins/smarty/";

	
    if($allowPHP==NULL) {
        $allowPHP=getConfig("TEMPLATE_ALLOW_PHP");
    }
    if($allowPHP) {
      $sm=new SmartyBC();
    } else {
      $sm=new Smarty();
    }
    $sm->php_handling=Smarty::PHP_ALLOW;
    //$sm->allow_php_tag=true;
    //$sm->allow_php_templates=true;

    $baseCacheDir=ROOT.CACHE_TEMPLATES_FOLDER.SITENAME."/smarty/";
    if(!is_dir($baseCacheDir)) {
    	mkdir($baseCacheDir,0755,true);
    }

    $sm->addPluginsDir(API_FOLDER."libs/logiksTemplate/plugins/smarty/");

    $sm->template_dir = ROOT.APPS_FOLDER.SITENAME."/css/templates/";
    $sm->config_dir   = ROOT . CFG_FOLDER;
    $sm->compile_dir  = $baseCacheDir."compiled/";
    $sm->cache_dir    = $baseCacheDir."cached/";

    //$sm->secure_dir=array();

    if(!file_exists($sm->compile_dir)) {
    	mkdir($sm->compile_dir,0755,true);
    }
    if(!file_exists($sm->cache_dir)) {
    	mkdir($sm->cache_dir,0755,true);
    }

    $sm->debug_tpl=API_FOLDER."vendors/smarty/smarty/debug.tpl";

    $sm->setCompileCheck(getConfig("TEMPLATE_COMPILE_CHECK"));
    $sm->setCacheLifetime(getConfig("TEMPLATE_EXPIRY"));

    if(MASTER_DEBUG_MODE || getConfig("APP_DEBUG_MODE")) {
      $sm->setCaching(0);
    } else {
      $sm->setCaching(getConfig("TEMPLATE_CACHE"));//Smarty::CACHING_LIFETIME_CURRENT
    }
    
    //direct_access_security

    //var_dump($sm);

    if(getConfig("TEMPLATE_DEBUG")) {
    	$sm->debugging=true;
    }

    $sm->error_reporting=getConfig("TEMPLATE_ERROR_REPORTING");//E_ERROR|E_PARSE;

    $this->smartyEngine=$sm;

    $this->autoloadVars();
  }

  public function autoloadVars() {
    foreach ($_ENV as $key => $value) {
      if(isset($value['value'])) {
        $this->smartyEngine->assign($key,$value['value']);
      }
    }
    foreach ($_GET as $key => $value) {
      $this->smartyEngine->assign($key,$value);
    }
  }

  public function addVar($name,$value) {
		$this->smartyEngine->assign($name,$value);
		return true;
	}
  public function getVar($name) {
    return $this->smartyEngine->getTemplateVars($name);
  }
	public function removeVar($name) {
		unset($this->smartyEngine->tpl_vars[$name]);
		return true;
	}

  public function isCached($tmpl) {
    return $this->smartyEngine->isCached($tmpl);
  }

  public function clearCache($tmpl) {
    $this->smartyEngine->clearCompiledTemplate($tmpl);
    $this->smartyEngine->clearCache($tmpl);
  }

  public function compileTemplate($tmpl,$data=array(),$tmplID=null) {
    foreach($data as $a=>$b) {
      $this->addVar($a,$b);
    }
    if($tmpl=="*") {
      $this->smartyEngine->compileAllTemplates('.tpl',true);
    } else {
      ob_start();
      $this->smartyEngine->display($tmpl);
      ob_clean();
    }
  }
  //$caching==0 Means Temp Data
	//$caching==1 Means Replace The Old Data In Cache
	//$caching==2 Means Use Old Data
	public function fetchTemplate($tmpl,$data=array(),$tmplID=null,$recreateCache=false) {
    //echo $tmpl;
    //printArray($this->smartyEngine->getTemplateVars());exit();
    if(is_array($data)) {
      foreach($data as $a=>$b) {
  			$this->addVar($a,$b);
  		}
    }

    if(MASTER_DEBUG_MODE || getConfig("APP_DEBUG_MODE")) {
      $this->smartyEngine->setCaching(0);
    } else {
      $this->smartyEngine->setCaching(getConfig("TEMPLATE_CACHE"));//Smarty::CACHING_LIFETIME_CURRENT
    }

    if($recreateCache) {
	$this->clearCache($tmpl);
	$this->clearCache($tmplID);
    }
    if(getConfig("TEMPLATE_CLEARCACHE_ON_DISPLAY")) {
	$this->clearCache($tmpl);
	$this->clearCache($tmplID);
    }
    if(MASTER_DEBUG_MODE || getConfig("APP_DEBUG_MODE")) {
	$this->clearCache($tmpl);
	$this->clearCache($tmplID);
    }

    //$this->clearCache($tmpl);exit();
    //println("$tmpl $tmplID");exit();
    //var_dump($this->smartyEngine);

    $this->addVar("ROOT",ROOT);
    $this->addVar("APPROOT",APPROOT);
    $this->addVar("WEBROOT",WEBROOT);
    $this->addVar("WEBAPPROOT",WEBAPPROOT);

    ob_start();
    if($tmplID && strlen($tmplID)>3) {
      $this->smartyEngine->display($tmpl,$tmplID);
    } else {
      $this->smartyEngine->display($tmpl);
    }
    $data=ob_get_contents();
    ob_clean();

    return $data;
  }
}
?>
