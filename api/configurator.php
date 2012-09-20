<?php
//The central configuration functions
//Types :: DEFINE, SESSION, CONFIG, PHPINI, DBCONFIG,DATABUS
if(!defined('ROOT')) {
	define('ROOT',dirname(dirname(__FILE__)) . '/');
}

include ROOT. "config/classpath.php";
include ROOT. "api/databus.inc";

DataBus::singleton();
if(!function_exists('LoadConfigFile')) {
	function LoadConfigFile($path,$mode="DEFINE") {
		if(file_exists($path) && is_readable($path))	{
			$cfgData=file_get_contents($path);
			$cfgData=explode("\n",$cfgData."\n");
			foreach($cfgData as $s) {
				if(strlen($s)<=1) continue;
				if(substr($s,0,2)=="//") continue;
				if(substr($s,0,1)=="#") continue;
				
				if(strlen($s)>0) {
					$n1=strpos($s, "=");
					if($n1>0) {
						$name=substr($s,0,$n1);
						$value=substr($s,$n1+1);
						//echo $mode . " " .  $name . " " . $value . "<br>";
						
						switch($mode) {
							case "SESSION":
								$_SESSION[$name] = processServerStrings($value);
								break;
							case "CONFIG":
								$GLOBALS['CONFIG'][$name] = processServerStrings($value);
								//$CONFIG[$name] = processServerStrings($value);
								break;
							case "DBCONFIG":
								$GLOBALS['DBCONFIG'][$name] = processServerStrings($value);
								break;
							case "DEFINE":
								if(!defined($name)) define($name,processServerStrings($value));
								break;
							case "PHPINI":
								if(function_exists("ini_set")) {
									ini_set($name,processServerStrings($value));
								}
								break;
							case "ENV":
								$_ENV[$name] = processServerStrings($value);
								break;
							case "COOKIE":
								setcookie($name,processServerStrings($value),time() + (86400 * 1)); // 86400 = 1 day
								break;
							case "DATABUS":
								$keyTag=explode(".",$name);
								if(count($keyTag)==1) {
									$a=$keyTag[0];
									$keyTag[0]="/";
									$keyTag[1]=$a;
								}
								DataBus::singleton()->setData($keyTag[1],processServerStrings($value),$keyTag[0]);
								break;
							default:
								break;
						}
					} else {
						switch($s) {
							case "[DEFINE]":
								$mode="DEFINE";
								break;
							case "[SESSION]":
								$mode="SESSION";
								break;
							case "[CONFIG]":
								$mode="CONFIG";
								break;
							case "[DBCONFIG]":
								$mode="DBCONFIG";
								break;
							case "[PHPINI]":
								$mode="PHPINI";
								break;
							case "[ENV]":
								$mode="ENV";
								break;
							case "[COOKIE]":
								$mode="COOKIE";
								break;
							case "[DATABUS]":
								$mode="DATABUS";
								break;
							default:
								$mode="DEFINE";
								break;
						}
					}
				}
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>Config File Could Not Be Loaded " . $path;
		}
	}
	function loadConfigDir($dir,$cfgOnly=false) {
		if(!file_exists($dir)) return;
		$arr=scandir($dir);	
		foreach($arr as $a) {
			if($a!="." && $a!="..") {
				$b=$dir.$a;
				if(strrchr(strtolower($a),".")==".cfg") {
					LoadConfigFile($b);
				} elseif(strpos(strtolower($a),"php")>0 && !$cfgOnly) {
					include_once $b;					
				}
			}
		}
	}
	function fixPHPINIConfigs() {
		if(function_exists("ini_set")) {
			ini_set("date.timezone",getConfig("DEFAULT_TIMEZONE"));
			ini_set("upload_max_filesize",MAX_UPLOAD_FILE_SIZE);
			ini_set("post_max_size",MAX_UPLOAD_FILE_SIZE);
			$a=ini_get("error_reporting");
			$a=explode(",",$a);
			$err=0;
			foreach($a as $b) {
				if(defined($b)) {
					$err=$err|constant($b);
				}
			}
			ini_set("error_reporting",$err);
		}
	}
	function fixLogiksVariables() {
		$hostProtocol="http://";
		if(strlen(getConfig("SiteProtocol"))>0) {
			$hostProtocol=getConfig("SiteProtocol")."://";
		}
		define("SiteRoot",$_SERVER['DOCUMENT_ROOT']."/".InstallFolder);
		define("SiteLocation",$hostProtocol.$_SERVER['HTTP_HOST']."/".InstallFolder);
		define("WEBROOT", SiteLocation);
		
		$df=str_replace("yy","Y",getConfig("DATE_FORMAT"));
		$GLOBALS['CONFIG']["PHP_DATE_FORMAT"] = $df;
		
		//$df=str_replace("yy","Y",getConfig("TIME_FORMAT"));
		//$GLOBALS['CONFIG']["PHP_TIME_FORMAT"] = $df;
	}
	function getConfig($name,$context="/") {
		if($name==null || strlen($name)<=0) return "";
		$keyTag=explode(".",$name);
		if(count($keyTag)<=1) {
			$a=$keyTag[0];
			$context="/";
			$keyTag=$a;
		} else {
			$context=$keyTag[0];
			$keyTag=$keyTag[1];
		}
		if(defined($name)) {
			return constant($name);
		} elseif(isset($_SESSION[$name])) {
			return $_SESSION[$name];
		} elseif(isset($_SERVER[$name])) {
			return $_SERVER[$name];
		} elseif(isset($GLOBALS['CONFIG'][$name])) {
			return $GLOBALS['CONFIG'][$name];
		} elseif(isset($_ENV[$name])) {
			return $_ENV[$name];
		} elseif(isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		} elseif(isset($GLOBALS['CONFIG'][$name])) {
			return $GLOBALS['CONFIG'][$name];
		} elseif(DataBus::singleton()->issetData($keyTag,$context)) {
			return DataBus::singleton()->getData($keyTag,$context);
		}
		return "";
	}
	function setConfig($name, $value, $context="/") {
		if($name==null || strlen($name)<=0) return false;
		$keyTag=explode(".",$name);
		if(count($keyTag)<=1) {
			$a=$keyTag[0];
			$context="/";
			$keyTag=$a;
		} else {
			$context=$keyTag[0];
			$keyTag=$keyTag[1];
		}
		if(defined($name)) {
			trigger_error("Non Modifible Constant- $name");
			return false;
		} elseif(isset($_SERVER[$name])) {
			$_SERVER[$name]=$value;
			return true;
		} elseif(isset($_SESSION[$name])) {
			$_SESSION[$name]=$value;
		} elseif(isset($GLOBALS['CONFIG'][$name])) {
			$GLOBALS['CONFIG'][$name]=$value;
		} elseif(isset($_ENV[$name])) {
			$_ENV[$name]=$value;
		} elseif(isset($GLOBALS['CONFIG'][$name])) {
			$GLOBALS['CONFIG'][$name]=$value;
		} elseif(isset($_COOKIE[$name])) {
			$_COOKIE[$name]=$value;
		} elseif(DataBus::singleton()->issetData($keyTag,$context)) {
			DataBus::singleton()->setData($keyTag,$value,$context);
		} else {
			DataBus::singleton()->setData($keyTag,$value,"/");
		}
		return true;
	}
	function parseConfigFile($path) {
		$outArr=array();
		if(file_exists($path))	{
			$file=fopen($path,"r") or die("Unable to open file");
			while(!feof($file)){
					$s=fgets($file);
					if(substr($s,0,2)=="//") continue;
					if(substr($s,0,1)=="#") continue;
					$s=substr($s,0,strlen($s)-1);
					if(strlen($s)>0) {
						$n1=strpos($s, "=");
						if($n1>0) {
							$name=substr($s,0,$n1);
							$value=substr($s,$n1+1);
							$r=array("name"=>$name,"value"=>$value,"mode"=>$mode);
							$outArr[$name]=$r;
						} else {
							if($s=="[DEFINE]") $mode="DEFINE";
							else if($s=="[SESSION]") $mode="SESSION";
							else if($s=="[CONFIG]") $mode="CONFIG";
							else if($s=="[DBCONFIG]") $mode="DBCONFIG";
							else if($s=="[PHPINI]") $mode="PHPINI";
							else if($s=="[ENV]") $mode="ENV";
							else if($s=="[COOKIE]") $mode="COOKIE";
							else if($s=="[DATABUS]") $mode="DATABUS";
							else $mode="DEFINE";
						}
					}
			}
		}
		return $outArr;
	}
	function processServerStrings($str) {
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
