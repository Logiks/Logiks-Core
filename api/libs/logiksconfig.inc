<?php
/*
 * This file contains all the configuration functions
 * Supported Types :: DEFINE, SESSION, CONFIG, PHPINI, COOKIE
 * Uses $_ENV as index to the Configs
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 3.0
 */
class LogiksConfig {

  private static $instance=null;
  private $configCompilePath="";

  public static function getInstance() {
    if(LogiksConfig::$instance==null) {
      LogiksConfig::$instance=new LogiksConfig();
    }
    return LogiksConfig::$instance;
  }

  public function __construct() {
    $this->configCompilePath=ROOT."tmp/configs/";
    if(!is_dir($this->configCompilePath)) {
      mkdir($this->configCompilePath,0755,true);
      file_put_contents($this->configCompilePath.".htaccess", "deny for all");
    }
  }

  public function getConfig($key) {
    if($key==null || strlen($key)<=0) return "";
    if(isset($_ENV[$key])) {
      return $_ENV[$key]['value'];
    } else {
      return false;
    }
  }
  public function setConfig($key,$value) {
    if($key==null || strlen($key)<=0) return "";
    if(isset($_ENV[$key])) {
      $arr=$_ENV[$key];

      switch($arr['mode']) {
        case "DEFINE":
          return false;
        break;
        default:
          $arr['name']=$key;
          $arr['value']=$value;
          $this->evalConfig([$arr]);
          $_ENV[$key]=$arr;
          return true;
        break;
      }
    }
    return false;
  }

  public function loadConfigs($pathParam) {
    $ckey=$this->getCompileKey($pathParam);
    if(defined("MASTER_DEBUG_MODE") && MASTER_DEBUG_MODE) {
      $this->compileConfig($pathParam);
      return $this->loadCompiledConfig($ckey);
    } else {
      if(!$this->isPreCompiled($ckey)) {
        $this->compileConfig($pathParam);
      }
      return $this->loadCompiledConfig($ckey);
    }
  }

  //Compile Config Files To PHP
  protected function loadCompiledConfig($compileKey) {
    $compilePath=$this->getCompilePath($compileKey);
    include_once $compilePath;

    //$cfgData=file_get_contents($compilePath);
    //$cfgData=unserialize($cfgData);
    //return $this->evalConfig($cfgData);
  }
  protected function compileConfig($pathParam) {
    $compileKey=$this->getCompileKey($pathParam);
    $compilePath=$this->getCompilePath($compileKey);
    $compileDir=dirname($compilePath);

    $outputData=array();
    if(is_array($pathParam)) {
			foreach($pathParam as $file) {
        $cfgData=$this->parseConfigFile($file);
        $outputData=array_merge($outputData,$cfgData);
      }
    } else if(is_file($pathParam)) {
      $cfgData=$this->parseConfigFile($pathParam);
      $outputData=array_merge($outputData,$cfgData);
    }
    $data="<?php\n";
    $data1="";
    foreach ($outputData as $key => $cfg) {
      $name=$cfg['name'];
      $value=LogiksConfig::processServerStrings($cfg['value']);

      switch(strtoupper($cfg['mode'])) {
        case "DEFINE":
          $data.="if(!defined('$name')) define('$name','".$value."');\n";
        break;
        case "SESSION":
          $data.='$_SESSION["'.$name.'"]="'.$value.'";'."\n";
        break;
        case "CONFIG":
          $data.='$GLOBALS["CONFIG"]["'.$name.'"]="'.$value.'";'."\n";
        break;
        case "COOKIE":
          $data.="setcookie('$name','".$value."',time() + (86400 * 1));\n"; // 86400 = 1 day
        break;
        case "PHPINI":
          $data.="@ini_set('$name','".$value."');\n";
        break;
        default:

      }
      //Build the index for the configSet
      $data1.='$_ENV["'.$name.'"]='."['mode'=>'{$cfg['mode']}','value'=>\"{$value}\"];\n";
    }
    $data.="\n\n".$data1;
    $data.="?>";
    /*$data=serialize($outputData);
    if(!is_dir($compileDir)) {
      mkdir($compileDir,0755,true);
    }*/
    return file_put_contents($compilePath, $data);
  }
  protected function isPreCompiled($compileKey) {
    $path=$this->getCompilePath($compileKey);
    if(file_exists($path) && filectime($path)==filemtime($path)) return true;
    else return false;
  }
  protected function getCompileKey($pathParam) {
    if(is_array($pathParam)) return md5(implode("-", $pathParam));
    else return md5($pathParam);
  }
  protected function getCompilePath($compileKey) {
    return $this->configCompilePath.$compileKey;
  }

  public function loadJSONConfig($configName,$key=null) {
    $cfgData=array();
		$f=ROOT.CFG_FOLDER."jsonConfig/{$configName}.json";
		if(file_exists($f)) {
			$data=file_get_contents($f);
			if(strlen($data)>2) {
				$json=json_decode($data,true);
				if($key==null) {
					$fData=array();
					if(isset($json['GLOBALS'])) {
						$fData[]=$json['GLOBALS'];
					}
					if(defined("SITENAME") && isset($json[SITENAME])) {
						$fData[]=$json[SITENAME];
					}
					for ($i=1; $i <count($fData) ; $i++) {
						$fData[0]=array_merge($fData[0],$fData[$i]);
					}
					$cfgData=$fData[0];
				} else {
					if(isset($json[$key])) {
						$cfgData=$json[$key];
					} elseif(isset($json["GLOBALS"])) {
						$cfgData=$json["GLOBALS"];
					}
				}
			}
		} else {
      trigger_error("$configName configuration not found, Error In Installation.", E_USER_ERROR);
    }
		return $cfgData;
  }

  public static function loadFeature($fname,$forceReload=false,$debug=false) {
		$ftrs=array();

		$arrFiles=array();
		if(defined("APPS_CONFIG_FOLDER")) {
			$arrFiles[]=APPROOT.APPS_CONFIG_FOLDER."features/$fname";
		}
		$arrFiles[]=ROOT.CFG_FOLDER."features/$fname";
		if(getConfig("ALLOW_LOCAL_FEATURE_LOAD")=="true") {
			$mPath=checkModule($fname);
			if(strlen($mPath)>0) {
				$arrFiles[]=dirname($mPath)."/config";
			}
		}
		foreach($arrFiles as $f) {
			if(file_exists("{$f}.cfg")) {
				if(isset($GLOBALS['FEATURES']["{$fname}.cfg"]) && !$forceReload) {
					return $GLOBALS['FEATURES']["{$fname}.cfg"];
				} else {
					$ftrs=LogiksConfig::parseConfigFile("{$f}.cfg");
					$arr=array();
					foreach($ftrs as $a=>$b) {
						$arr[$a]=$b['value'];
					}
					$GLOBALS['FEATURES']["{$fname}.cfg"]=$arr;
					if($debug) {
						return $ftrs;
					} else {
						return $arr;
					}
				}
			} elseif(file_exists("{$f}.json")) {
				if(isset($GLOBALS['FEATURES']["{$fname}.json"]) && !$forceReload) {
					return $GLOBALS['FEATURES']["{$fname}.json"];
				} else {
					$data=file_get_contents("{$f}.json");
					$arr=json_decode($data,true);
					$GLOBALS['FEATURES']["{$fname}.json"]=$arr;
					return $arr;
				}
			} elseif(file_exists("{$f}.lst")) {
				if(isset($GLOBALS['FEATURES']["{$fname}.lst"]) && !$forceReload) {
					return $GLOBALS['FEATURES']["{$fname}.lst"];
				} else {
					$f=file_get_contents("{$f}.lst");
					$arr=explode("\n",$f);
					if(strlen($arr[count($arr)-1])==0) unset($arr[count($arr)-1]);
					$GLOBALS['FEATURES']["{$fname}.lst"]=$arr;
					return $arr;
				}
			}
		}
		return array();
	}
  public static function unloadFeature($fname) {
		if(isset($GLOBALS['FEATURES']["{$fname}.cfg"])) {
			unset($GLOBALS['FEATURES']["{$fname}.cfg"]);
			return true;
		} elseif(isset($GLOBALS['FEATURES']["{$fname}.json"])) {
			unset($GLOBALS['FEATURES']["{$fname}.json"]);
			return true;
		} elseif(isset($GLOBALS['FEATURES']["{$fname}.lst"])) {
			unset($GLOBALS['FEATURES']["{$fname}.lst"]);
			return true;
		} else {
			return false;
		}
	}

  public static function fixPHPINIConfigs() {
		if(function_exists("ini_set")) {
			@ini_set("date.timezone",getConfig("DEFAULT_TIMEZONE"));
			@ini_set("upload_max_filesize",MAX_UPLOAD_FILE_SIZE);
			@ini_set("post_max_size",MAX_UPLOAD_FILE_SIZE*FILE_UPLOAD_COUNT+1000);
		}
	}

  //Process Config Data For and put into Enviroment
  protected function evalConfig($cfgData) {
    foreach ($cfgData as $key => $cfg) {
      $name=$cfg['name'];
      $value=$cfg['value'];
      switch(strtoupper($cfg['mode'])) {
        case "DEFINE":
          if(!defined($name)) {
            define($name,LogiksConfig::processServerStrings($value));
          }
        break;
        case "SESSION":
          $_SESSION[$name] = LogiksConfig::processServerStrings($value);
        break;
        case "CONFIG":
          $GLOBALS['CONFIG'][$name] = LogiksConfig::processServerStrings($value);
        break;
        case "COOKIE":
          setcookie($name,LogiksConfig::processServerStrings($value),time() + (86400 * 1)); // 86400 = 1 day
        break;
        case "ENV":
          $_ENV[$name] = LogiksConfig::processServerStrings($value);
        break;
        case "PHPINI":
          @ini_set($name,LogiksConfig::processServerStrings($value));
        break;
        default:

      }
    }
  }
  public static function fetchConfigFile($path) {
    $outArr=array();
		if(file_exists($path))	{
			$file=fopen($path,"r") or die("Unable to open file");
			while(!feof($file)){
					$s=fgets($file);
          if(substr($s,0,2)=="//") continue;
					if(substr($s,0,1)=="#") continue;
					$s=trim($s);//substr($s,0,strlen($s)-1);
					if(strlen($s)>0) {
            $s=explode("=", $s);
            $name=$s[0];
            array_shift($s);
            $value=implode("=", $s);
            $outArr[$name]=$value;
          }
      }
    }
    return $outArr;
  }
  public static function parseConfigFile($path) {
		$mode="CONFIG";
		$outArr=array();
		if(file_exists($path))	{
			$file=fopen($path,"r") or die("Unable to open file");
			while(!feof($file)){
					$s=fgets($file);
					if(substr($s,0,2)=="//") continue;
					if(substr($s,0,1)=="#") continue;
					$s=trim($s);//substr($s,0,strlen($s)-1);
					if(strlen($s)>0) {
						$n1=strpos($s, "=");
						if($n1>0) {
							$asx=explode("=",$s);
							$key=$asx[0];
							if(!isset($asx[1])) $val="";
							else {
								array_shift($asx);
								$val=implode("=", $asx);
								$val=str_replace('"', "'", $val);
							}
							$name=$key;
							$value=$val;
							$r=array("name"=>$name,"value"=>$value,"mode"=>$mode);
						  	$outArr["{$mode}-{$name}"]=$r;
						} else {
							if($s=="[DEFINE]") $mode="DEFINE";
							else if($s=="[SESSION]") $mode="SESSION";
							else if($s=="[CONFIG]") $mode="CONFIG";
							else if($s=="[PHPINI]") $mode="PHPINI";
							else if($s=="[ENV]") $mode="ENV";
							else if($s=="[COOKIE]") $mode="COOKIE";
							else $mode="CONFIG";
						}
					}
			}
		}
		return $outArr;
	}
  public static function parseListFile($path) {
		if(!file_exists($path)) $path=ROOT.CFG_FOLDER."lists/$path.lst";
		$f=file_get_contents($path);
		$arr=explode("\n",$f);
		if(strlen($arr[count($arr)-1])==0) unset($arr[count($arr)-1]);
		return $arr;
	}
  public static function processServerStrings($str) {
		if(strlen($str)<=0) return "";

    if($str=="true") return true;
    elseif($str=="false") return false;

		$pattern = '/\$_SERVER\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_SERVER[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . $_SERVER[$s2];
			$str=str_replace($s1,$_SERVER[$s2],$str);
		}
		$pattern = '/\$_SESSION\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_SESSION[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . $_SESSION[$s2];
			$str=str_replace($s1,$_SESSION[$s2],$str);
		}
		$pattern = '/\$_COOKIE\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_COOKIE[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . $_COOKIE[$s2];
			$str=str_replace($s1,$_COOKIE[$s2],$str);
		}
		$pattern = '/\$_CONFIG\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_CONFIG[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . $$GLOBALS['CONFIG'][$s2];
			$str=str_replace($s1,$GLOBALS['CONFIG'][$s2],$str);
		}
		$pattern = '/\$_ENV\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_ENV[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . $_ENV[$s2];
			$str=str_replace($s1,$_ENV['CONFIG'][$s2],$str);
		}
		$pattern = '/\$_DEFINE\[\'[a-zA-Z0-9_-]+\'\]/';
		$cnt=preg_match_all($pattern, $str, $matches,0);
		for($i=0;$i<$cnt;$i++) {
			$s1=$matches[0][$i];
			$s2=str_replace('$_DEFINE[\'','',$s1);
			$s2=str_replace('\']','',$s2);
			//echo $s2 . " " . BASEPATH;
			if(defined($s2)) {
				$str=str_replace($s1,constant($s2),$str);
			} else {
				$str=str_replace($s1,">>",$str);
			}
		}
		return $str;
	}
}
?>
