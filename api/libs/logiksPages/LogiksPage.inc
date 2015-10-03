<?php
/*
 * This Class For Developers To Create MultiLayout Systems Where Users Will Be Able To Change The Page Contents.
 * All this works only for Generated Pages. Designed Pages Do Not Neccessarily Use This System..
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012, 05/07/2015
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksPage {
	
	protected $theme=null;
	protected $pageFolder="";
	protected $device="";

	protected $pageConfig=array();
	protected $pageType=null;

	public function __construct($theme) {
		$this->theme=$theme;

		$this->device=strtolower(getUserDeviceType());

		$pageFolder=APPROOT.APPS_PAGES_FOLDER."layouts_".$this->device."/";
		if(!is_dir($pageFolder)) {
			$pageFolder=APPROOT.APPS_PAGES_FOLDER."layouts/";
		}
		if(!is_dir($pageFolder)) {
			trigger_logikserror("Error Finding Pages Layouts Folder For Device {$this->device}",E_LOGIKS_ERROR);
		}
		$this->pageFolder=$pageFolder;
	}

	public function loadPage($page) {
		$pageToLoad=$this->findPage(PAGE);

		$pageConfig=$this->pageFolder.$pageToLoad.".json";
		//ToDO : Handle php, tpl, html files directly
		if(!file_exists($pageConfig)) {
			trigger_logikserror("Error Finding Pages $page For Device {$this->device}",E_USER_ERROR, 404);
		}
		$this->pageType="json";
		$pageConfig=json_decode(file_get_contents($pageConfig),true);

		if(isset($pageConfig['modules'])) {
			$pageConfig['modules_preload']=$pageConfig['modules'];
			unset($pageConfig['modules']);
		}
		$pageConfig['modules_preload']=explode(",", trim($pageConfig['modules_preload']));
		$pageConfig['modules_postload']=explode(",", trim($pageConfig['modules_postload']));

		$pageConfig['theme']=APPS_THEME;
		$pageConfig['class']=str_replace("/"," ",$pageConfig['url']);

		$this->pageConfig=$pageConfig;
		if(!$this->isAccessible()) {
			trigger_logikserror('Sorry, page '.PAGE.' is not accessible to you. Needs Credentials',E_LOGIKS_ERROR,401);
		}

		return $this->pageConfig;
	}
	public function printPage() {
		$pageConfig=$this->pageConfig;
		//printArray($pageConfig);

		if(!$pageConfig) {
			trigger_logikserror('Sorry, page '.PAGE.' is not available or accessible to you.',E_LOGIKS_ERROR,404);
		}
		if(!$pageConfig['enabled']) {
			trigger_logikserror('Sorry, page '.PAGE.' is not available',E_LOGIKS_ERROR,403);
		}

		//include ROOT."api/scripts.php";
		//
		//$cacheFile=
		//include $pageLinkPath;
		//runHooks("postPage");

		$ext=explode(".", $pageConfig['template']);
		if(count($ext)>1) {
			$ext=$ext[count($ext)-1];
			$ext=".{$ext}";
			$engine=LogiksTemplate::getEngineForExtension($ext);
		} else {
			$ext=LogiksTemplate::getExtensionForEngine(APPS_TEMPLATEENGINE);
			$engine=APPS_TEMPLATEENGINE;
			$pageConfig['template'].=$ext;
		}
		if(!$engine || !$ext) {
			trigger_logikserror("Template Engine Not Yet Supported",E_LOGIKS_ERROR);
		}

		$tmpl=$this->theme->findTemplate($pageConfig['template']);

		if($tmpl) {
			$_ENV['PAGECONFIG']=$this->pageConfig;

			runHooks("prePage");

			$lt=new LogiksTemplate($engine);
			$this->setupMeta($lt);
			
			if(count($pageConfig['modules_preload'])>0) loadModule($pageConfig['modules_preload']);

			if(MASTER_DEBUG_MODE)
				$lt->printTemplate($tmpl,null,true);
			else
				$lt->printTemplate($tmpl,null);

			if(count($pageConfig['modules_postload'])>0) loadModule($pageConfig['modules_postload']);

			runHooks("postPage");
		} else {
			trigger_logikserror("Template {$pageConfig['template']} Not Found",E_LOGIKS_ERROR,404);
		}
	}

	protected function isAccessible() {
		return true;
  	}

  	protected function findPage($page) {
	    $page=explode("/",$page);
	    $page=current($page);

	    return $page;
	}
	
  	protected function setupMeta($logiksTemplate) {
  		$this->pageConfig=$_ENV['PAGECONFIG'];

  		if(!isset($this->pageConfig['title']) || strlen($this->pageConfig['title'])<=0) $this->pageConfig['title']=_replace(getConfig("TITLE_FORMAT"));
  		if(!isset($this->pageConfig['description']) || strlen($this->pageConfig['description'])<=0) $this->pageConfig['description']=_replace(getConfig("APPS_DESCRIPTION"));
  		if(!isset($this->pageConfig['keywords']) || strlen($this->pageConfig['keywords'])<=0) $this->pageConfig['keywords']=_replace(getConfig("APPS_KEYWORDS"));
  		if(!isset($this->pageConfig['robots']) || strlen($this->pageConfig['robots'])<=0) $this->pageConfig['robots']=_replace(getConfig("APPS_ROBOTS"));
  		if(!isset($this->pageConfig['viewport']) || strlen($this->pageConfig['viewport'])<=0) $this->pageConfig['viewport']=_replace(getConfig("PAGE_VIEWPORT"));
  		
  		$page=array(
  				'CSS'=>"",
  				'JS_PRELOAD'=>"",
  				'JS_POSTLOAD'=>"",
  			);

  		if(isset($this->pageConfig['css']) && strlen($this->pageConfig['css'])>0) $page['CSS']=$this->pageConfig['css'];
  		if(isset($this->pageConfig['js_preload']) && strlen($this->pageConfig['js_preload'])>0) $page['JS_PRELOAD']=$this->pageConfig['js_preload'];
  		if(isset($this->pageConfig['js_postload']) && strlen($this->pageConfig['js_postload'])>0) $page['JS_POSTLOAD']=$this->pageConfig['js_postload'];

  		$device=strtoupper(getUserDeviceType());
		if($device=="PC") {
			$page['CSS']=getConfig("{$device}_CSS_TO_LOAD")." ".getConfig("DEFAULT_CSS_TO_LOAD")." ".$page['CSS'];
			$page['JS_PRELOAD']=getConfig("{$device}_JS_TO_PRELOAD")." ".getConfig("DEFAULT_JS_TO_PRELOAD")." ".$page['JS_PRELOAD'];
			$page['JS_POSTLOAD']=getConfig("{$device}_JS_TO_POSTLOAD")." ".getConfig("DEFAULT_JS_TO_POSTLOAD")." ".$page['JS_POSTLOAD'];
		} else {
			$page['CSS']=getConfig("{$device}_CSS_TO_LOAD")." ".$page['CSS'];
			$page['JS_PRELOAD']=getConfig("{$device}_JS_TO_PRELOAD")." ".$page['JS_PRELOAD'];
			$page['JS_POSTLOAD']=getConfig("{$device}_JS_TO_POSTLOAD")." ".$page['JS_POSTLOAD'];
		}

		$page['CSS']=explode(",", str_replace(" ",",",trim($page['CSS'])));
		$page['CSS']=array_unique($page['CSS']);
		$page['CSS']=implode(",", $page['CSS']);
		
		$page['JS_PRELOAD']=explode(",", str_replace(" ",",",trim($page['JS_PRELOAD'])));
		$page['JS_PRELOAD']=array_unique($page['JS_PRELOAD']);
		$page['JS_PRELOAD']=implode(",", $page['JS_PRELOAD']);

		$page['JS_POSTLOAD']=explode(",", str_replace(" ",",",trim($page['JS_POSTLOAD'])));
		$page['JS_POSTLOAD']=array_unique($page['JS_POSTLOAD']);
		$page['JS_POSTLOAD']=implode(",", $page['JS_POSTLOAD']);

  		$favicon=getConfig("APPS_ICON");
		if(strlen($favicon)>0) {
			$favicon="<link rel='shortcut icon' type='image/x-icon' href='$favicon' />";
		}
		$page['FAVICON']=$favicon;

		$page['TITLE']=$this->pageConfig['title'];
		$page['DESCRIPTION']=$this->pageConfig['description'];
		$page['KEYWORDS']=$this->pageConfig['keywords'];
		$page['ROBOTS']=$this->pageConfig['robots'];

		$page['VIEWPORT']=$this->pageConfig['viewport'];
		if(strlen(trim($page['VIEWPORT']))>0) $page['VIEWPORT']="<meta name='viewport' content='{$page['VIEWPORT']}'>";

		$cfg=getConfig("HEADER_DOCTYPE");
		if(strlen($cfg)>0) $page['DOCTYPE']="<!DOCTYPE $cfg>";

		$page['HTML_ATTRIBUTES']=getConfig("HEADER_HTML_ATTRIBUTES");
		$page['HEAD_ATTRIBUTES']=getConfig("HEADER_HEAD_ATTRIBUTES");

		$cfg=getConfig("PAGE_BODY_CLASS")." ".$this->pageConfig['class'];
		if(strlen($cfg)>0) $page['BODY_CLASS']="class='".trim($cfg)."'";
		
		$cfg=getConfig("PAGE_BODY_ATTRIBUTES");
		if(strlen($cfg)>0) $page['BODY_ATTRIBUTES']=$cfg;
		else $page['BODY_ATTRIBUTES']="";

		if(getConfig("PAGE_LOCK_CONTEXTMENU")) $page['BODY_ATTRIBUTES'].=" oncontextmenu='return false'";
		if(getConfig("PAGE_LOCK_SELECTION")) $page['BODY_ATTRIBUTES'].=" onselectstart='return false'";
		if(getConfig("PAGE_LOCK_MOUSEDRAG")) $page['BODY_ATTRIBUTES'].=" ondragstart='return false' draggable='false'";

		if(strlen(trim($page['CSS']))>0) 
			$page['CSS']="<link href='"._cssLink(explode(",", trim($page['CSS'])))."' rel='stylesheet' type='text/css' />";

		if(strlen(trim($page['JS_PRELOAD']))>0) 
			$page['JS_PRELOAD']="<script src='"._jsLink(explode(",", trim($page['JS_PRELOAD'])))."' type='text/javascript' language='javascript'></script>";

		if(strlen(trim($page['JS_POSTLOAD']))>0) 
			$page['JS_POSTLOAD']="<script src='"._jsLink(explode(",", trim($page['JS_POSTLOAD'])))."' type='text/javascript' language='javascript'></script>";

		$logiksTemplate->addVar("PAGE",$page);
  	}
}
?>
