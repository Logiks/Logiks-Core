<?php
/*
 * This class contains the CSS-PHP bridge for Logiks Framework
 * This class is used for CSS embeding.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!defined('CSS_DISP_TYPE')) define("CSS_DISP_TYPE","compressed");

class CssPHP extends DataEmbedder {
	private static $instance=null;
	private $theme;
	private $prependURL=true;
	private $lessData=null;
	private $defaulTheme;
	private $specialLibs=array();
	private $allMedias=array();

	protected function __construct() {
		parent::__construct();

		if(!isset($GLOBALS['CONFIG']['CURRENT_THEME'])) {
			$GLOBALS['CONFIG']['CURRENT_THEME']=DEFAULT_THEME;
		}

		$this->typeOfDispatch=CSS_DISP_TYPE;

		$this->theme=THEME_FOLDER . $GLOBALS['CONFIG']['CURRENT_THEME'] . "/";
		$this->defaulTheme=THEME_FOLDER."default/";

		$GLOBALS["CSS_PRINTED"]=array();
	}

	public static function singleton() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}

	public function currentTheme($themeName="*") {
		if($themeName=="*") {
			return $GLOBALS['CONFIG']['CURRENT_THEME'];
		} else {
			$GLOBALS['CONFIG']['CURRENT_THEME']=$themeName;
			$this->theme=THEME_FOLDER . $GLOBALS['CONFIG']['CURRENT_THEME'] . "/";
			return $GLOBALS['CONFIG']['CURRENT_THEME'];
		}
	}

	public function getThemePath() {
		return $this->theme;
	}

	public function loadTheme($theme) {
		$s=THEME_FOLDER . $theme . "/";
		$this->theme=$s;
	}

	public function getTheme() {
		$s=str_replace(THEME_FOLDER,"",$this->theme);
		$s=str_replace("/","",$s);
		return $s;
	}

	public function loadSkin($css,$skin="") {
		$cssO=$css;
		$s="";
		if(strpos(" ".$css,"/")>0) {//Points To A Style Folder
			$s=$css."/style";
		} else { //Points To A Style Sheet
			if($skin=="") {
				$arr=explode(".",$css);
				$skin=$arr[0];
			}
			$s=$skin."/".$css;
		}
		if(strlen($s)>0) {
			$css=$s;
			$paths=array();

			if(defined('BASEPATH')) array_push($paths,BASEPATH."css/$css.css");

			if(!in_array($this->theme.$cssO . ".css",$paths)) array_push($paths, $this->theme.$cssO . ".css");
			if(!in_array(SKINS_FOLDER."default/". $css . ".css",$paths)) array_push($paths, SKINS_FOLDER."default/".$css . ".css");//Failsafe For Other Themes Skins
			if(!in_array(SKINS_FOLDER. $css . ".css",$paths)) array_push($paths, SKINS_FOLDER.$css . ".css");//Failsafe For Skins
			if(!in_array(SKINS_FOLDER. $css . "/style.css",$paths)) array_push($paths, SKINS_FOLDER.$css . "/style.css");//Failsafe For Skins

			//printArray($paths);
			$themePath="";
			foreach($paths as $p) {
				if(file_exists(ROOT . $p)) {
					$themePath=$p;
					break;
				}
			}
			return $this->loadCSSFromPath($css,$themePath,"","");
		}
	}

	//ToDoS :: Yet To Be Implemented
	/*public function loadSkinDir($css,$skin="") {
		return false;
	}*/

	public function loadCSS($css,$themeName="*",$browser="",$media="") {
		$paths=array();

		$cdn=$this->getCDNResource($css,'css');
		if($cdn!==false) {
			return $this->loadCSSFromPath($css,$cdn,$browser,$media);
		}

		if(defined('BASEPATH')) {
			array_push($paths,BASEPATH."css/$css");
			array_push($paths,BASEPATH."plugins/modules/$css/style");
		}
		array_push($paths,"plugins/modules/$css/style");
		if($themeName=="*") {
			if(!in_array($this->theme.$css,$paths)) array_push($paths, $this->theme.$css);
			if(!in_array(THEME_FOLDER.DEFAULT_THEME."/". $css,$paths)) array_push($paths,THEME_FOLDER.DEFAULT_THEME."/". $css);//Failsafe
			if(!in_array(SKINS_FOLDER."default/". $css,$paths)) array_push($paths, SKINS_FOLDER."default/".$css);//Failsafe For Other Themes Skins
			if(!in_array(SKINS_FOLDER. $css,$paths)) array_push($paths, SKINS_FOLDER.$css);//Failsafe For Skins
		} else {
			if(!in_array(THEME_FOLDER.$themeName . "/" . $css,$paths)) array_push($paths, THEME_FOLDER.$themeName . "/" . $css);
			if(!in_array($this->theme.$css,$paths)) array_push($paths, $this->theme.$css);
			if(!in_array(THEME_FOLDER.DEFAULT_THEME."/". $css,$paths)) array_push($paths, THEME_FOLDER.DEFAULT_THEME."/". $css);//Failsafe
			if(!in_array(SKINS_FOLDER."default/". $css,$paths)) array_push($paths, SKINS_FOLDER."default/".$css);//Failsafe For Other Themes Skins
			if(!in_array(SKINS_FOLDER. $css,$paths)) array_push($paths, SKINS_FOLDER.$css);//Failsafe For Skins
		}
		//printArray($paths);
		$themePath="";
		foreach($paths as $p) {
			if(file_exists(ROOT . $p . ".css")) {
				$themePath=$p . ".css";
				break;
			} elseif(file_exists(ROOT . $p . ".less")) {
				$themePath=$p . ".less";
				break;
			}
		}

		return $this->loadCSSFromPath($css,$themePath,$browser,$media);
	}

	private function loadCSSFromPath($css,$themePath,$browser="",$media="") {
		//println($themePath);
		//Failsafe
		if(strlen($themePath)<=0) {
			if(file_exists(ROOT.THEME_FOLDER."default/".$css.".css")) {
				$themePath=THEME_FOLDER."default/".$css.".css";
			} elseif(file_exists(ROOT.SKINS_FOLDER."default/".$css.".css")) {
				$themePath=SKINS_FOLDER."default/".$css.".css";
			} else {
				return false;
			}
		}
		if(substr($themePath, 0,2)=="//" ||
			substr($themePath, 0,7)=="http://" ||
			substr($themePath, 0,8)=="https://" ||
			substr($themePath, 0,8)=="ftp://") {

			$this->scripts[$css]=$themePath;
			if(strlen($browser)>0) {
				$this->specialLibs[$css]=$browser;
			}
			if(strlen($media)>0) {
				$this->allMedias[$css]=strtolower($media);
			} else {
				$this->allMedias[$css]="screen";
			}
			return true;
		} elseif(file_exists(ROOT.$themePath)) {
			$this->scripts[$css]=$themePath;
			if(strlen($browser)>0) {
				$this->specialLibs[$css]=$browser;
			}
			if(strlen($media)>0) {
				$this->allMedias[$css]=strtolower($media);
			} else {
				$this->allMedias[$css]="screen";
			}
			return true;
		} else {
			return false;
		}
	}

	public function unloadCSS($css) {
		unset($this->scripts[$css]);
		return true;
	}

	public function __clone() {
		trigger_error("Clone is not allowed.",E_USER_ERROR);
	}

	public function display($scriptTag=true,$params=array()) {
		$keys=array_keys($this->getScripts());
		$arr=$this->getScripts();
		$x=$this->specialLibs;

		foreach($keys as $a=>$b) {
			if(in_array($b,$GLOBALS["CSS_PRINTED"])) {
				unset($keys[$a]);
			}
		}
		foreach($keys as $a=>$b) {
			$GLOBALS["CSS_PRINTED"][count($GLOBALS["CSS_PRINTED"])]=$b;
		}
		$cnt=count($keys);
		if(strtolower($this->typeOfDispatch)=="tagged") {
			foreach($keys as $k) {
				if(array_key_exists($k, $x)) {
					$this->printIfTag($x[$k]);
					$a=$arr[$k];
					$p=$this->getCSSWebPath($a,$params);
					$media=$this->allMedias[$k];
					$s="<link href='$p' rel='stylesheet' type='text/css' media='$media' /> ";
					echo $s . "\n";
					echo "<![endif]-->\n";
				} else {
					$a=$arr[$k];
					$media=$this->allMedias[$k];
					$p=$this->getCSSWebPath($a,$params);
					$s="<link href='$p' rel='stylesheet' type='text/css' media='$media' /> ";
					echo $s . "\n";
				}
			}
		} else {
			if($scriptTag) {
				if($this->isolatedBlocks) {
					foreach($keys as $k) {
						if(array_key_exists($k, $x)) {
							$this->printIfTag($x[$k]);

							$a=$arr[$k];
							$media=$this->allMedias[$k];
							echo "\n<style type='text/css' media='$media' >\n";
							echo $this->getCSSContents($a,$params);
							echo "\n</style>\n";

							echo "<![endif]-->\n";
						} else {
							$a=$arr[$k];
							$media=$this->allMedias[$k];
							echo "\n<style type='text/css' media='$media' >\n";
							$media=$this->allMedias[$k];
							echo $this->getCSSContents($a,$params);
							echo "\n</style>\n";
						}
					}
				} else {
					$media=$this->allMedias[$k];
					echo "\n<style type='text/css' media='$media' >\n";
					foreach($keys as $k) {
						$a=$arr[$k];
						echo $this->getCSSContents($a,$params);
					}
					echo "\n</style>\n";
				}
			} else {
				foreach($keys as $k) {
					if(array_key_exists($k, $x)) {
						$this->printIfTag($x[$k]);

						$a=$arr[$k];
						echo $this->getCSSContents($a,$params);

						echo "\n<![endif]-->\n";
					} else {
						$a=$arr[$k];
						echo $this->getCSSContents($a,$params);
						echo "\n<![endif]-->\n";
					}
				}
			}
		}
		$this->clearScripts();
		return $cnt;
	}

	protected function getCSSPath($a) {
		if(!file_exists(ROOT.$a)) {
			$t=end(explode(DIRECTORY_SEPARATOR,$a));
			$a=$this->defaulTheme.$t;
		}
		return $a;
	}

	protected function getCSSWebPath($a,$params) {
		if(substr($a, 0,2)=="//" ||
			substr($a, 0,7)=="http://" ||
			substr($a, 0,8)=="https://" ||
			substr($a, 0,8)=="ftp://") {

			return $a;
		}
		$a=$this->getCSSPath($a);
		if(getConfig("CSS_CACHEBUSTER")=="true") $a.="?".rand();
		$ext=explode(".",$a);
		$ext=end($ext);
		$ext=strtolower($ext);

		$fp="";
		if($ext=="css") {
			$fp=SiteLocation.$a;
		} else {
			$fp=_service("resources","load","css")."&src=".base64_encode($a);
		}
		return $fp;
	}

	protected function getCSSContents($a,$params) {
		$file=$this->getCSSPath($a);
		if(!file_exists(ROOT.$file)) return "";
		
		$cssData=file_get_contents(ROOT.$file);
		$cssData=$this->parseCSS($cssData,$file);

		//$cssData=$this->compress($cssData);

		return $cssData;
	}

	protected function compress($buffer) {
		/* remove comments */
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
		/* remove tabs, spaces, newlines, etc. */
		$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

		return $buffer;
	}

	protected function parseCSS($cssData,$cssFile) {
		$p=getWebpath($cssFile);
		//$p=substr($p,0,strlen($p)-1);

		$search = '#url\((?!\s*[\'"]?(?:https?:)?//)\s*([\'"])?#';
		$replace = "url($1{$p}";
		$cssData = preg_replace($search, $replace, $cssData);

		$ext=strtolower(end(explode(".",$cssFile)));
		if($ext=="less") {
			$lessData=$this->getLessData();
			$cssData=str_replace("/*CONFIG*/",$lessData,$cssData);
			$less = new lessc();
			$cssData=$less->parse($cssData);
		}
		return $cssData;
	}

	protected function getLessData() {
		if($this->lessData==null) {
			$this->lessData="";
			$f="";
			if(defined("APPROOT")) {
				if(file_exists(APPROOT."config/theme.less")) {
					$f=APPROOT."config/theme.less";
				} elseif(file_exists(APPROOT."css/theme.less")) {
					$f=APPROOT."css/theme.less";
				}
			}
			if(strlen($f)<=0) {
				if(file_exists(ROOT."config/theme.less")) {
					$f=ROOT."config/theme.less";
				} else if(file_exists(ROOT.$this->theme."theme.less")) {
					$f=ROOT.$this->theme."theme.less";
				}
			}
			if(strlen($f)>0 && is_readable($f)) {
				if(file_exists($f)) {
					$this->lessData=file_get_contents($f);
				}
			}
		}
		if(isset($_ENV['THEME_LESS_DATA'])) $this->lessData.=$_ENV['THEME_LESS_DATA'];
		return $this->lessData;
	}

	public function reset() {
		parent::reset();
		$this->specialLibs=array();
	}

	public function printIfTag($a) {
		//if(strtolower($a)=="ie6") 
		echo "<!--[if $a]>\n";
	}
}
?>
