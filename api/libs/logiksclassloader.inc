<?php
/*
 * LogiksClassLoader handles the ClassLoading Across Logiks Framework.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksClassLoader {

	private static $instance=null;

	private $saveRequired=false;
	private $classCache=[];
	private $classCachePath="";

	private $namespaceSeparator = '\\';

	public static function getInstance() {
	    if(LogiksClassLoader::$instance==null) {
				LogiksClassLoader::$instance=new LogiksClassLoader();
	    }
	    return LogiksClassLoader::$instance;
	}

	protected function __construct() {
		$this->classCachePath=ROOT."tmp/classes/";

		if(!is_dir($this->classCachePath)) {
	      mkdir($this->classCachePath,0755,true);
	      file_put_contents($this->classCachePath.".htaccess", "deny for all");
	    }

		spl_autoload_register(function ($class) {
				//include 'classes/' . $class . '.class.php';
				$cp=LogiksClassLoader::getInstance()->findClassInCache($class);
				if(!$cp) {
					$cp=LogiksClassLoader::getInstance()->findClassUniversal($class);
				} elseif(!file_exists($cp)) {
					$cp=LogiksClassLoader::getInstance()->findClassUniversal($class);
				}

				if($cp) {
					include $cp;
				} else {
					echo "<div class='logiksClassNotFound'>CLASS NOT FOUND :: $class (LogiksClassLoader:47)</div>";
				}
		});

		register_shutdown_function(function() {
			LogiksClassLoader::getInstance()->dumpCache();
		});

		$this->loadCache();
	}

	protected function findClassUniversal($class) {
		$cls=strtolower($class);

		$classpath=$GLOBALS['classPath'];
		foreach($classpath as $p) {
			if(file_exists(ROOT."{$p}{$cls}.inc")) {
				$ff=ROOT."{$p}{$cls}.inc";
				$this->addToCache($cls, $ff);
				return $ff;
			} elseif(file_exists(ROOT."{$p}{$class}.inc")) {
				$ff=ROOT."{$p}{$class}.inc";
				$this->addToCache($class, $ff);
				return $ff;
			} elseif(file_exists(ROOT."{$p}{$cls}.php")) {
				$ff=ROOT."{$p}{$cls}.php";
				$this->addToCache($cls, $ff);
				return $ff;
			} elseif(file_exists(ROOT."{$p}{$class}.php")) {
				$ff=ROOT."{$p}{$class}.php";
				$this->addToCache($class, $ff);
				return $ff;
			}
		}

		$className = ltrim($class, $this->namespaceSeparator);
		$fileName  = '';
	    $namespace = '';
	    if ($lastNsPos = strripos($className, $this->namespaceSeparator)) {
	        $namespace = substr($className, 0, $lastNsPos);
	        $className = substr($className, $lastNsPos + 1);
	        $fileName  = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	    }
	    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);
		$fileName=current(explode(DIRECTORY_SEPARATOR, $fileName)).DIRECTORY_SEPARATOR.$fileName;

		$vendorpath=$GLOBALS['vendorPath'];
		foreach($vendorpath as $p) {
			$ff=ROOT.$p.$fileName . '.php';
			$ff1=ROOT.$p.$fileName . '.inc';
			if(file_exists($ff)) {
				$this->addToCache($cls, $ff);
				return $ff;
			} elseif(file_exists($ff1)) {
				$this->addToCache($cls, $ff1);
				return $ff1;
			}
		}
		return false;
	}
	protected function findClassInCache($class) {
		$class=strtolower($class);
		if(isset($this->classCache[$class])) return $this->classCache[$class];
		return false;
	}
	protected function addToCache($class,$classPath) {
		$this->classCache[$class]=$classPath;
		$this->saveRequired=true;
	}
	public function dumpCache() {
		if($this->saveRequired) {
			if($this->classCache==null) $this->classCache=[];
			$cacheFile=$this->classCachePath."cache";
			$data=serialize($this->classCache);
			file_put_contents($cacheFile, $data);
		}
	}
	public function loadCache() {
		$cacheFile=$this->classCachePath."cache";
		if(file_exists($cacheFile)) {
			$cfgData=file_get_contents($cacheFile);
		} else {
			$cfgData="";
		}
		$this->classCache=unserialize($cfgData);
	}
}
?>
