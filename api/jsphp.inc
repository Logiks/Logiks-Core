<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$JS_LINK=SiteLocation . JS_FOLDER;
$JS_PATH=ROOT . JS_FOLDER;
$GLOBALS['JS_PATH']=$JS_PATH;
$GLOBALS['JS_LINK']=$JS_LINK;

class JsPHP extends DataEmbedder {
	private static $instance=null;
	private $DS=".";
	private $specialLibs=array();

	protected function __construct() {
		parent::__construct();
		$this->typeOfDispatch=JS_DISP_TYPE;//"Uncompressed";

		$GLOBALS["JS_PRINTED"]=array();
	}

	public static function singleton() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}

	public function loadJS($jslib,$vers="*",$params="") {
		return $this->loadLibs($jslib,$vers);
	}
	public function unloadJS($jslib,$vers="*") {
		return $this->unloadLibs($jslib,$vers);
	}

	public function __clone() {
		trigger_error("Clone is not allowed.",E_USER_ERROR);
	}

	public function getDataPath($jslib,$vers="*") {
		global $JS_PATH, $JS_LINK;

		if($vers=="*") $vers="";

		$jsp="";

		$a=explode($this->DS,$jslib);

		$paths=array();

		//creating all feasible script file names
		if(strlen($vers)>0) {
			array_push($paths, $jslib . "-" . $vers);
			array_push($paths, $a[0]. "/" . $jslib . "-" . $vers);
			array_push($paths, $a[0]. "/" . str_replace($a[0].".","",$jslib) . "-" . $vers);
			array_push($paths, $a[0]. "/$vers/" . $jslib);
			array_push($paths, $a[0]. "/$vers/" . str_replace($a[0].".","",$jslib));
			array_push($paths, $a[0]. "-$vers/" . $jslib);
			array_push($paths, $a[0]. "-$vers/" . str_replace($a[0].".","",$jslib));
			array_push($paths, $jslib . $vers);
			array_push($paths, $jslib);
			array_push($paths, $a[0]. "/" . $jslib);
			array_push($paths, $a[0]. "/" . str_replace($a[0].".","",$jslib));
		} else {
			array_push($paths, $jslib);
			array_push($paths, $a[0]. "/" . $jslib);
			array_push($paths, $a[0]. "/" . str_replace($a[0].".","",$jslib));
		}
		foreach($paths as $p) {
			if(defined("APPROOT")) {
				if(file_exists(APPROOT."js/{$p}.js")) {
					$jsp="js/". $p;
					$JSLINK=WEBAPPROOT . $jsp . ".js";
					$JSFILE=APPROOT . $jsp . ".js";
					$JSCACHE=ROOT . CACHE_FOLDER . md5($JSFILE) . ".js";
					$JSCACHELINK=SiteLocation . CACHE_FOLDER . md5($JSFILE) . ".js";

					$data=array($JSLINK, $JSFILE, $JSCACHE,$JSCACHELINK);

					return $data;
				} elseif(file_exists(APPROOT."plugins/modules/{$p}/script.js")) {
					$jsp="plugins/modules/{$p}/script";
					$JSLINK=WEBAPPROOT . $jsp . ".js";
					$JSFILE=APPROOT . $jsp . ".js";
					$JSCACHE=ROOT . CACHE_FOLDER . md5($JSFILE) . ".js";
					$JSCACHELINK=SiteLocation . CACHE_FOLDER . md5($JSFILE) . ".js";

					$data=array($JSLINK, $JSFILE, $JSCACHE,$JSCACHELINK);

					return $data;
				}
			}
			if(file_exists(ROOT."plugins/modules/{$p}/script.js")) {
				$jsp="plugins/modules/{$p}/script";
				$JSLINK=WEBROOT . $jsp . ".js";
				$JSFILE=ROOT . $jsp . ".js";
				$JSCACHE=ROOT . CACHE_FOLDER . md5($JSFILE) . ".js";
				$JSCACHELINK=SiteLocation . CACHE_FOLDER . md5($JSFILE) . ".js";

				$data=array($JSLINK, $JSFILE, $JSCACHE,$JSCACHELINK);

				return $data;
			} elseif(file_exists($JS_PATH . "{$p}.js")) {
				$jsp=$p;
				if(strlen($jsp)<=0) {
					$jsp=$jslib . $vers;
				}
				$JSLINK=$JS_LINK . $jsp . ".js";
				$JSFILE=$JS_PATH . $jsp . ".js";
				$JSCACHE=ROOT . CACHE_FOLDER . md5($JSFILE) . ".js";
				$JSCACHELINK=SiteLocation . CACHE_FOLDER . md5($JSFILE) . ".js";

				$data=array($JSLINK, $JSFILE, $JSCACHE,$JSCACHELINK);

				return $data;
			}
		}
	}

	public static function compress($jsFile,$cacheFile=false) {
		$data=file_get_contents($jsFile);

		$t1 = microtime(true);

		$packer = new JavaScriptPacker($data, 'Normal', true, false);
		$data = $packer->pack();

		$t2 = microtime(true);

		$time = sprintf('%.4f', ($t2 - $t1) );
		//echo 'script ', $src, ' packed in ' , $out, ', in ', $time, ' s.', "\n";

		if($cacheFile) {
			$cachedFilePath= ROOT . CACHE_FOLDER . md5($jsFile) . ".js";
			file_put_contents($cachedFilePath,$data);
			return $cachedFilePath;
		} else {
			return $data;
		}
	}

	public function display($scriptTag=true) {
		$jsScripts=$this->getScripts();
		if(sizeOf($jsScripts)<=0) {
			return;
		}
		foreach($jsScripts as $a=>$b) {
			if(in_array($a,$GLOBALS["JS_PRINTED"])) {
				unset($jsScripts[$a]);
			}
		}
		foreach($jsScripts as $a=>$b) {
			$GLOBALS["JS_PRINTED"][sizeOf($GLOBALS["JS_PRINTED"])]=$a;
		}
		
		//exit($this->typeOfDispatch);
		if(strtolower($this->typeOfDispatch)=="tagged") {
			foreach($jsScripts as $a) {
				$p=$a[0];
				if(getConfig("JS_CACHEBUSTER")=="true") $p.="?".rand();
				$s="<script src='$p' type='text/javascript' language='javascript'></script>";
				echo $s . "\n";
			}
		} elseif(strtolower($this->typeOfDispatch)=="uncompressed") {
			if($scriptTag) {
				if($this->isolatedBlocks) {
					foreach($jsScripts as $a) {
						$p=$a[1];
						echo "\n<script type='text/javascript' language='javascript'>\n";
						echo file_get_contents($p);
						echo "\n</script>";
					}
				} else {
					echo "\n<script type='text/javascript' language='javascript'>\n";
					foreach($jsScripts as $a) {
						$p=$a[1];
						echo file_get_contents($p);
					}
					echo "\n</script>";
				}
			} else {
				foreach($jsScripts as $a) {
					$p=$a[1];
					echo file_get_contents($p);
				}
			}
		} elseif(strtolower($this->typeOfDispatch)=="compressed") {
			if($scriptTag) {
				if($this->isolatedBlocks) {
					foreach($jsScripts as $a) {
						$p=$a[1];
						echo "\n<script type='text/javascript' language='javascript'>\n";
						echo $this->compress($p);
						echo "\n</script>";
					}
				} else {
					echo "\n<script type='text/javascript' language='javascript'>\n";
					foreach($jsScripts as $a) {
						$p=$a[1];
						echo $this->compress($p);
					}
					echo "\n</script>";
				}
			} else {
				foreach($jsScripts as $a) {
					$p=$a[1];
					echo $this->compress($p);
				}
			}
		} elseif(strtolower($this->typeOfDispatch)=="serializedcachelink") {
			foreach($jsScripts as $a) {
				$p=$a[3];
				if(file_exists($a[2])) {
					$d1=date("F d Y H:i:s");
					$d2=date("F d Y H:i:s", filemtime($a[2]));
					$diff=abs(strtotime($d1)-strtotime($d2));

					$data=$this->enumrateTime($diff);
					$hrs=100;
					if(is_array($data)) {
						$hrs=$data[$this->cacheTimeUnit];
					}
					if($hrs>=$this->cachePeriod) {
						unset($a[2]);
						$p=$this->compress($a[1],true);
					} else {
						$p=$a[3];
					}
				} else {
					$p=$this->compress($a[1],true);
				}
				if(getConfig("JS_CACHEBUSTER")=="true") $p.="?".rand();
				$s="<script src='$p' type='text/javascript' language='javascript'></script>";
				echo $s . "\n";
			}
		} elseif(strtolower($this->typeOfDispatch)=="serializedcachedata") {
			$jsLinks=array();
			foreach($jsScripts as $a) {
				$p=$a[3];
				if(file_exists($a[2])) {
					$d1=date("F d Y H:i:s");
					$d2=date("F d Y H:i:s", filemtime($a[2]));
					$diff=abs(strtotime($d1)-strtotime($d2));

					$data=$this->enumrateTime($diff);
					$hrs=100;
					if(is_array($data)) {
						$hrs=$data[$this->cacheTimeUnit];
					}
					if($hrs>=$this->cachePeriod) {
						unset($a[2]);
						$p=$this->compress($a[1],true);
					} else {
						$p=$a[3];
					}
				} else {
					$p=$this->compress($a[1],true);
				}
				$jsLinks[sizeof($jsLinks)]=$p;
			}

			if($scriptTag) {
				if($this->isolatedBlocks) {
					foreach($jsLinks as $a) {
						echo "\n<script type='text/javascript' language='javascript'>\n";
						echo file_get_contents($a);
						echo "\n</script>";
					}
				} else {
					echo "\n<script type='text/javascript' language='javascript'>\n";
					foreach($jsLinks as $a) {
						echo file_get_contents($a);
					}
					echo "\n</script>";
				}

			} else {
				foreach($jsLinks as $a) {
						echo file_get_contents($a);
				}
			}
		} else {
			/*strtolower($this->typeOfDispatch)="Uncompressed";
			$this->display();
			return;*/
		}
		echo "\n";
		$this->clearScripts();
	}
}

?>
