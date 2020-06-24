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
	public $pageType=null;
	protected $pagePath=null;

	public function __construct($theme) {
		$this->theme=$theme;

		$this->device=strtolower(getUserDeviceType());

		$pageFolder=APPROOT.APPS_PAGES_FOLDER."defn_".$this->device."/";
		if(!is_dir($pageFolder)) {
			$pageFolder=APPROOT.APPS_PAGES_FOLDER."defn/";
		}
		if(is_dir($pageFolder)) {
			$this->pageFolder=$pageFolder;
			return $this;
		}
		return false;
	}

	public function loadPage($page) {
		$pageToLoad=$this->findPage($page);
		if(!$pageToLoad) {
			return false;
		}
		$ext=explode(".", $pageToLoad);
		$ext=strtolower(end($ext));

		$this->pageType=$ext;
		$this->pagePath=$pageToLoad;
		
		$pageConfig=array();
		switch ($this->pageType) {
			case 'json':
				$pageConfig=$pageToLoad;
				if(!file_exists($pageConfig)) {
					$this->pageConfig=false;
					return false;
				}
				$pageConfig=json_decode(file_get_contents($pageConfig),true);

				if(isset($pageConfig['modules'])) {
					$pageConfig['modules_preload']=$pageConfig['modules'];
					unset($pageConfig['modules']);
				}
				if(!isset($pageConfig['modules_preload'])) $pageConfig['modules_preload']="";
				if(!isset($pageConfig['modules_postload'])) $pageConfig['modules_postload']="";
				
				$pageConfig['modules_preload']=explode(",", trim($pageConfig['modules_preload']));
				$pageConfig['modules_postload']=explode(",", trim($pageConfig['modules_postload']));

				$pageConfig['class']=str_replace("/"," ",$page);
			break;
			
			case 'php':
				
			break;

			case 'htm':
				
			break;

			case 'tpl':
				
			break;

			default:
				$this->pageConfig=false;
				return false;
		}

		if(!isset($pageConfig['enabled'])) $pageConfig['enabled']=true;
		
		$pageConfig['theme']=APPS_THEME;
		$pageConfig['page']=basename(str_replace(".{$ext}","",$pageToLoad));
		$pageConfig['pagepath']=$this->pagePath;

		$pageConfig['viewpage']=$pageConfig['page'];
		$pageConfig['pageuri']=implode("/", explode("_", $pageConfig['page']));

		$fx=explode("/", $this->pagePath);
		$fx=end($fx);
		$fx=explode(".", $fx);
		unset($fx[count($fx)-1]);
		$fx=implode(".", $fx);
		$fx=str_replace("-", "/", $fx);

		$pageConfig['pageName']=$fx;

		$this->pageConfig=$pageConfig;

		//printArray($this->pageConfig);exit();

		return $this->pageConfig;
	}
	public function getSlug() {
		if(!isset($_ENV['PAGESLUG'])) $this->generateSLUG();
		return $_ENV['PAGESLUG'];
	}
	public function getPageContent($dataPrint=null) {
		$pageConfig=$this->pageConfig;
		$page=$pageConfig['page'];
		//printArray($pageConfig);exit();

		if(!$pageConfig) return false;
		if(!$pageConfig['enabled'])  return false;
		if(!$this->isAccessible()) return false;

		$_ENV['PAGECONFIG']=$this->pageConfig;

		switch ($this->pageType) {
			case 'json':
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
					return false;
				}

				$tmpl=$this->theme->findTemplate($pageConfig['template']);

				if($tmpl) {
					$lt=new LogiksTemplate($engine);
					
					if(getConfig("TEMPLATE_ID_MODE")=="URI") {
						$tmplID=md5($_SERVER['REQUEST_URI'].$tmpl);
					} else {
						$tmplID=md5(PAGE.$tmpl);
					}

					$pageSrc=APPROOT.APPS_PAGES_FOLDER."viewpage/{$page}.php";

					$GLOBALS['PAGETMPL']=$lt->getEngine();
					if(file_exists($pageSrc)) include_once $pageSrc;

					$this->setupMeta($lt);

					ob_start();
					if(MASTER_DEBUG_MODE || getConfig("APP_DEBUG_MODE"))
						$lt->printTemplate($tmpl,$dataPrint,$tmplID,true);
					else
						$lt->printTemplate($tmpl,$dataPrint,$tmplID);

					$html=ob_get_contents();
					ob_clean();

					return $html;
				}
			break;
			
			case 'php':
				ob_start();

				require_once $this->pagePath;

				$html=ob_get_contents();
				ob_clean();

				return $html;
			break;

			case 'htm':
				ob_start();

				readfile($this->pagePath);

				$html=ob_get_contents();
				ob_clean();

				return $html;
			break;

			default:
				return false;
		}
	}
	public function printPage() {
		$pageConfig=$this->pageConfig;
		$page=$pageConfig['page'];
		define("PAGEURI",$pageConfig['pageuri']);
		
		//printArray($pageConfig);exit($page);

		if(!$pageConfig) {
			trigger_logikserror("Sorry, page $page is not available or accessible to you.",E_LOGIKS_ERROR,404);
		}
		if(!$pageConfig['enabled'] || $pageConfig['enabled']==="false") {
			trigger_logikserror("Sorry, page $page is not available/enabled",E_LOGIKS_ERROR,403);
		}
		if(!$this->isAccessible()) {
			if(session_check(false)) {
				trigger_logikserror("Sorry, page $page is not accessible to you.",E_LOGIKS_ERROR,401);
			} else {
				$_SESSION['SESS_ERROR_MSG']="Page Is not accessible to you";
				header("Location:"._link("login"));
				return;
			}
		}

		//include ROOT."api/scripts.php";
		//printArray($this->pageConfig);exit();
		
		//$this->pageConfig['enabled']=="false"
		//$this->pageConfig['access']=="public"
		//
		

		$_ENV['PAGECONFIG']=$this->pageConfig;
		$slugs=$this->generateSLUG();
		
		switch ($this->pageType) {
			case 'json':
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
					runHooks("prePage");
					
					//$page=current(explode("/",PAGE));
					$page=$pageConfig['page'];

					$lt=new LogiksTemplate($engine);

					if(getConfig("TEMPLATE_ID_MODE")=="URI") {
						$tmplID=md5($_SERVER['REQUEST_URI'].$tmpl);
					} else {
						$tmplID=md5(PAGE.$tmpl);
					}
					
					if(count($pageConfig['modules_preload'])>0) loadModule($pageConfig['modules_preload']);

					$pageSrc=APPROOT.APPS_PAGES_FOLDER."viewpage/{$page}.php";

					$GLOBALS['PAGETMPL']=$lt->getEngine();
					if(file_exists($pageSrc)) include_once $pageSrc;

					$this->setupMeta($lt);

					if(MASTER_DEBUG_MODE)
						$lt->printTemplate($tmpl,null,$tmplID,true);
					else
						$lt->printTemplate($tmpl,null,$tmplID);
					
					if(count($pageConfig['modules_postload'])>0) loadModule($pageConfig['modules_postload']);

					runHooks("postPage");
				} else {
					trigger_logikserror("Template {$pageConfig['template']} Not Found",E_LOGIKS_ERROR,404);
				}		
			break;
			
			case 'php':
				if(file_exists($this->pagePath)) {
					runHooks("prePage");
					include_once $this->pagePath;
					runHooks("postPage");
				} else {
					trigger_logikserror("Template {$pageConfig['page']} Not Found",E_LOGIKS_ERROR,404);
				}
			break;

			case 'htm':
				if(file_exists($this->pagePath)) {
					runHooks("prePage");
					readfile($this->pagePath);
					runHooks("postPage");
				} else {
					trigger_logikserror("Template {$pageConfig['page']} Not Found",E_LOGIKS_ERROR,404);
				}
			break;

			default:
				return false;
		}
	}

	protected function findPage($pagePath) {
  		$pageFile=_metaCache("PAGES",$pagePath);

  		if($pageFile && file_exists($pageFile)) {
  			return $pageFile;
  		}

	    $page=explode("/",$pagePath);
	    foreach ($page as $key => $value) {
	    	if($key>0) {
	    		$page[$key]=$page[$key-1]."_".$value;
	    	}
	    }
	    $page=array_reverse($page);

	    $pageArr=array(
				"json"=>$this->pageFolder,
				"php"=>dirname($this->pageFolder)."/",
				"htm"=>dirname($this->pageFolder)."/",
			);
	    foreach ($page as $pgx) {
	    	foreach ($pageArr as $ext => $path) {
	    		$f="{$path}{$pgx}.{$ext}";
	    		if(file_exists($f)) {
	    			_metaCacheUpdate("PAGES",$pagePath,$f);
	    			return $f;
	    		}
	    	}
	    }
	    return false;
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

		$page['PAGE_CSS']=$page['CSS'];
		$page['PAGE_JS_PRELOAD']=$page['JS_PRELOAD'];
		$page['PAGE_JS_POSTLOAD']=$page['JS_POSTLOAD'];

  	$device=strtoupper(getUserDeviceType());
		$page['CSS']=getConfig("{$device}_CSS_TO_LOAD").",".getConfig("DEFAULT_CSS_TO_LOAD").",".$page['CSS'];
		$page['JS_PRELOAD']=getConfig("{$device}_JS_TO_PRELOAD").",".getConfig("DEFAULT_JS_TO_PRELOAD").",".$page['JS_PRELOAD'];
		$page['JS_POSTLOAD']=getConfig("{$device}_JS_TO_POSTLOAD").",".getConfig("DEFAULT_JS_TO_POSTLOAD").",".$page['JS_POSTLOAD'];
		
		$page['CSS']=explode(",", str_replace(" ",",",trim($page['CSS'])));
		$page['CSS'][]=$this->pageConfig['template'];
		$page['CSS'][]="comps/".str_replace("/","-",$this->pageConfig['pageName']);
		$page['CSS']=array_unique($page['CSS']);
		//$page['CSS']=implode(",", $page['CSS']);
		
		$page['JS_PRELOAD']=explode(",", str_replace(" ",",",trim($page['JS_PRELOAD'])));
		$page['JS_PRELOAD']=array_unique($page['JS_PRELOAD']);
		$page['JS_PRELOAD']=implode(",", $page['JS_PRELOAD']);

		$page['JS_POSTLOAD']=explode(",", str_replace(" ",",",trim($page['JS_POSTLOAD'].",comps/".str_replace("/","-",$this->pageConfig['pageName']))));
		$page['JS_POSTLOAD']=array_unique($page['JS_POSTLOAD']);
		$page['JS_POSTLOAD']=implode(",", $page['JS_POSTLOAD']);

		//printArray($page);exit();
			
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

		// if(strlen(trim($page['CSS']))>0) 
		// 	$page['CSS']="<link href='"._cssLink(explode(",", trim($page['CSS'])))."' rel='stylesheet' type='text/css' />";
		$html="";
		foreach ($page['CSS'] as $f) {
			$html.=_css($f);
		}
		$page['CSS']=$html;

		if(strlen(trim($page['JS_PRELOAD']))>0) 
			$page['JS_PRELOAD']="<script src='"._jsLink(explode(",", trim($page['JS_PRELOAD'])))."' type='text/javascript' language='javascript'></script>";

		if(strlen(trim($page['JS_POSTLOAD']))>0) 
			$page['JS_POSTLOAD']="<script src='"._jsLink(explode(",", trim($page['JS_POSTLOAD'])))."' type='text/javascript' language='javascript'></script>";


		if(isset($_ENV['PAGEVAR']) && is_array($_ENV['PAGEVAR'])) {
			foreach ($_ENV['PAGEVAR'] as $key => $value) {
				$page[$key]=$value;
			}
		}
		//var_dump($page);exit();

		$logiksTemplate->addVar("PAGE",$page);
  	}

  	protected function generateSLUG() {
  		$arrSlug=array();

  		$fx=PAGE;
  		$page=implode("/", explode("_", $this->pageConfig['page']));
		//$page=$this->pageConfig['page'];

		$pageSlug=str_replace("#{$page}", "", "#{$fx}");
		if(substr($pageSlug, 0,1)=="/") $pageSlug=substr($pageSlug, 1);
		if(substr($pageSlug, -1,1)=="/") $pageSlug=substr($pageSlug, 0,-1);
		if(substr($pageSlug, 0,1)=="#") $pageSlug=substr($pageSlug, 1);

		$pageSlug=explode("/", $pageSlug);
		$_ENV['PAGESLUG-MAIN']=$pageSlug;

		switch ($this->pageType) {
			case 'json':
				$slugX=array();
				if(isset($this->pageConfig['slug'])) {
					$slugX=explode("/", $this->pageConfig['slug']);
					$slugX=array_flip($slugX);

					foreach ($slugX as $key => $value) {
						if(isset($pageSlug[$value])) $slugX[$key]=$pageSlug[$value];
						else $slugX[$key]="";
					}
					$_ENV['PAGESLUG']=$slugX;
					$arrSlug=$_ENV['PAGESLUG'];
				} else {
					$_ENV['PAGESLUG']=$pageSlug;
					$arrSlug=$_ENV['PAGESLUG'];
				}
			break;
			
			case 'php':
			case 'htm':
				$_ENV['PAGESLUG']=$pageSlug;
				$arrSlug=$_ENV['PAGESLUG'];
			break;

			default:
				return false;
		}
  		return $arrSlug;
  	}

  	public function isAccessible() {
			if($this->pageType=="json") {
				if(isset($this->pageConfig['access'])) {
					$pageAccess=$this->pageConfig['access'];

					if(is_string($pageAccess)) $pageAccess=explode(",", $pageAccess);

					if(strlen($pageAccess[0])==0 || $pageAccess[0]=="public") {
						return true;
					} elseif($pageAccess[0]=="private") {
						if(isset($this->pageConfig['privilege_model'])) {
							if(!is_array($this->pageConfig['privilege_model'])) {
								$this->pageConfig['privilege_model']=explode(",",$this->pageConfig['privilege_model']);
							}
							
							if(in_array(RoleModel::getPrivilegeHash(),$this->pageConfig['privilege_model'])) {
								return true;
							} else {
								return false;
							}
						} elseif(function_exists("checkUserRoles")) {
							return checkUserRoles("PAGES",$this->pageConfig['pageName'],"ACCESS");
						}
					} elseif($pageAccess[0]=="protected" || $pageAccess[0]=="postlogin") {
						if(session_check(false,false)) {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				} else {
					return true;
				}
			} else {
				return checkUserRoles("PAGES",PAGE,"ACCESS");
			}
			return true;
  	}
}
?>
