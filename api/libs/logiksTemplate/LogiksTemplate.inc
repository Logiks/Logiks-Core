<?php
/*
 * This class is a generalized template engine used to compile, print templates from multiple
 * template engines.
 *
 * Current Support : SMARTY
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksTemplate {

  private static $templateEngines=array(
		"smarty"=>array(
			"ext"=>".tpl",
			"source"=>"/engines/SmartyTemplateEngine.inc",
		)
	);

  private $engineParam=array(); //Engine Paramaters
  private $engineName=""; //The Name of the Template Engine
  private $engine=null; //The actual Template Engine

  public function __construct($engine) {
    if(!isset(self::$templateEngines[$engine])) {
      trigger_error("Template Engine $engine Not Yet Supported",E_USER_ERROR);
    }

    $this->engineParam=self::$templateEngines[$engine];
    $this->engineName=$engine;

    if(!is_dir(CACHE_TEMPLATES_FOLDER)) {
			mkdir(CACHE_TEMPLATES_FOLDER,0755,true);
		}

    if(isset($this->engineParam['source']) && strlen($this->engineParam['source'])>0) {
        if(file_exists(dirname(__FILE__).$this->engineParam['source'])) {
          include_once dirname(__FILE__).$this->engineParam['source'];
        } elseif(file_exists($this->engineParam['source'])) {
          include_once $this->engineParam['source'];
        }
        $className=str_replace(".inc", "", basename($this->engineParam['source']));
        $this->engine = new $className;
    } else {
      trigger_error("Template Engine $engine Not Yet Configured",E_USER_ERROR);
    }
  }

  //BYPASS printTemplate to driver
  public function printTemplate($tmpl,$data=array(),$recreateCache=false) {
    if(!file_exists($tmpl)) {
		trigger_error("Template ".basename($tmpl)." Not Found",E_USER_ERROR);
	}
    return $this->engine->printTemplate($tmpl,$data,$recreateCache);
  }

  //BYPASS compileTemplate to driver
  public function compileTemplate($tmpl,$data=array()) {
    if(!file_exists($tmpl)) {
			trigger_error("Template ".basename($tmpl)." Not Found",E_USER_ERROR);
		}
    return $this->engine->compileTemplate($tmpl,$data);
  }
  //BYPASS clearTemplateCache to driver
  public function clearTemplateCache($tmpl) {
    return $this->engine->clearCache($tmpl);
  }
  //BYPASS addVar to driver
  public function addVar($name,$value) {
    return $this->engine->addVar($name,$value);
  }
  public function getVar($name) {
    return $this->engine->getVar($name);
  }
  //BYPASS removeVar to driver
  public function removeVar($name) {
    return $this->engine->removeVar($name);
  }
  //BYPASS isCached to driver
  public function isCached($tmpl) {
    return $this->engine->isCached($tmpl);
  }

  public static function getEngineForExtension($ext) {
    $engines=LogiksTemplate::$templateEngines;
    foreach ($engines as $engine => $params) {
      if($params['ext']==$ext) return $engine;
    }
    return false;
  }
  public static function getExtensionForEngine($engine) {
    if(isset(LogiksTemplate::$templateEngines[$engine])) return LogiksTemplate::$templateEngines[$engine]['ext'];
    return false;
  }

  //Generic Logiks Templating Components and functions
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
		$q=preg_replace_callback("/#[a-zA-Z0-9-_]+#/",array("LogiksTemplate","replaceFromEnviroment"),$q);
		return $q;
	}
	public static function processTemplate($txt,$data=array(),$editable=true) {
		if(strlen($txt)>0) {
			foreach($data as $a=>$b) {
				$t="#$a#";
				$txt=str_replace($t,$b,$txt);
			}
			$txt=preg_replace_callback("/#[a-zA-Z0-9-_]+#/",array("LogiksTemplate","replaceFromEnviroment"),$txt);
			if($editable) {
				$txt=preg_replace_callback("/[_]{3}+/",
					create_function('$in', 'return "<span class=editable>{$in[0]}</span>";'),$txt);
			}
		}
		return $txt;
	}

  //Database related functions
  public function loadSQL($sqlData) {
	// 	if(strlen($sqlData)<=0) return;
	// 	if(!is_array($sqlData)) {
	// 		$sqlData=explode("\n",$sqlData);
	// 	}
	// 	$cnt=0;
	// 	$allData=array();
	// 	foreach($sqlData as $q) {
	// 		if(strlen($q)>0) {
	// 			if(strpos(" ".$q,"#")==1) {
	// 				$allData[$cnt]=array();
	// 			} else {
	// 				$sql=TemplateEngine::processQuery($q);
	// 				$res=_dbQuery($sql);
	// 				if($res) {
	// 					$data=_db()->fetchAllData($res);
	// 					$allData[$cnt]=$data;
	// 				} else {
	// 					$allData[$cnt]=array();
	// 				}
	// 			}
	// 		} else {
	// 			$allData[$cnt]=array();
	// 		}
	// 		$cnt++;
	// 	}
	// 	$this->addVar("SQL",$allData);
	}
}
?>
