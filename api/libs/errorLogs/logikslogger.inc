<?php
/*
 * LogiksLogger is meant to support various kinds of logging facility using
 * Monolog vendor library.
 * When you register or add a logger to system, it stores that under the same site or core.
 *
 * Usage:
 *  Create New Logger
 *    LogiksLogger::getInstance()->createLogger("console",new Monolog\Handler\BrowserConsoleHandler());
 *  Add New Handler to existing
 *    LogiksLogger::getInstance()->addHandler("core",new Monolog\Handler\BrowserConsoleHandler());
 *  Log data
 *    LogiksLogger::log("core",LogiksLogger::LOG_WARN,"Hello World",array("A"=>123));
 *
 * PARAMS
 *    logKey
 * 
 * 
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

use Monolog\Logger;

class LogiksLogger {

  private static $instance=null;
  private $logPath="";
  private $logBook=[];

  //Reiniate MonoLog Logger constants Log Levels for being accessible through framework
  const LOG_WARN=Logger::DEBUG;//100
  const LOG_INFO=Logger::INFO;//200
  const LOG_NOTICE=Logger::NOTICE;//250
  const LOG_WARNING=Logger::WARNING;//300
  const LOG_ERROR=Logger::ERROR;//400
  const LOG_CRITICAL=Logger::CRITICAL;//500
  const LOG_ALERT=Logger::ALERT;//550
  const LOG_EMERGENCY=Logger::EMERGENCY;//600

  private $logHandlers=array();

  public static function getInstance() {
    if(LogiksLogger::$instance==null) {
      LogiksLogger::$instance=new LogiksLogger();
    }
    return LogiksLogger::$instance;
  }

  public function __construct() {
    $this->logPath=ROOT.LOG_FOLDER;
    if(!is_dir($this->logPath)) {
      mkdir($this->logPath,0755,true);
      file_put_contents($this->logPath.".htaccess", "deny for all");
    }

    $config=ROOT."config/jsonConfig/errorlogs.json";
    $this->loadLogConfig($config);
  }

  public function getLogKeys() {
    $siteKey="core";
    if(defined("SITENAME")) $siteKey=SITENAME;
    if(isset($this->logBook[$siteKey])) return array_keys($this->logBook[$siteKey]);
    else return [];
  }
  public function getLogger($logKey) {
    $siteKey="core";
    if(defined("SITENAME")) $siteKey=SITENAME;

    if(isset($this->logBook[$siteKey][$logKey])) {
      return $this->logBook[$siteKey][$logKey];
    }
    return false;
  }

  //Registers new handler for the given Sitename and logkey as logger
  public function registerHandler($logKey,$logHandler,$allowSimilarClass=false) {
    $clz=get_class($logHandler);
    
    $siteKey="core";
    if(defined("SITENAME")) $siteKey=SITENAME;

    if(!isset($this->logBook[$siteKey])) {
      $this->logBook[$siteKey]=array();
    }

    if(isset($this->logBook[$siteKey][$logKey])) {
      $logger=$this->logBook[$siteKey][$logKey];

      if(!$allowSimilarClass) {
        foreach ($logger->getHandlers() as $key => $value) {
          if(get_class($value)==$clz) {
            return false;
          }
        }
      }

      $logger->pushHandler($logHandler);
      return true;
    } else {
      return $this->createLogger($logKey, $logHandler);
    }
  }

  public function createLogger($loggerName,$handler=null) {
    $logger = new Logger($loggerName);

    if($handler==null) {
      $handler=$this->createDefaultHandler($loggerName);
    }
    if(is_array($handler)) {
      foreach ($handler as $h) {
        $logger->pushHandler($h);
      }
    } else {
      $logger->pushHandler($handler);
    }

    return $this->addLogger($loggerName, $logger);
  }

  public function addLogger($key, $logger) {
    if($logger==null) return false;

    $siteKey="core";
    if(defined("SITENAME")) $siteKey=SITENAME;

    $logger->pushProcessor(function ($record) {
          $params=explode(",", LOG_PARAMS);
          foreach ($params as $px) {
            if(isset($_SESSION[$px])) $record['extra'][$px] = $_SESSION[$px];
            elseif(isset($_SERVER[$px])) $record['extra'][$px] = $_SERVER[$px];
            elseif(isset($_REQUEST[$px])) $record['extra'][$px] = $_REQUEST[$px];
            elseif(defined($px)) $record['extra'][$px] = constant($px);
          }
          return $record;
      });

    $this->logBook[$siteKey][$key] = $logger;
    return $this->logBook[$siteKey][$key];
  }

  protected function createDefaultHandler($loggerName) {
    $logFile= $this->logPath.SITENAME."/".$loggerName."/a.log";
    if(!is_dir(dirname($logFile))) mkdir(dirname($logFile),0755,true);

    $dateFormat = LOG_DATE." ".LOG_TIME;
    $output = LOG_FORMAT."\n";
    $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);

    $handler=new Monolog\Handler\RotatingFileHandler($logFile, LOG_ROLL_PERIOD, LOG_DEFAULT_LEVEL);
    $handler->setFormatter($formatter);

    return $handler;
  }

  public static function severityToLevel($severity) {
    switch($severity) {
        case E_ERROR: // 1 //
            return LogiksLogger::LOG_ERROR;
        case E_WARNING: // 2 //
          return LogiksLogger::LOG_WARNING;
        case E_PARSE: // 4 //
          return LogiksLogger::LOG_CRITICAL;
        case E_NOTICE: // 8 //
          return LogiksLogger::LOG_NOTICE;
        case E_CORE_ERROR: // 16 //
          return LogiksLogger::LOG_EMERGENCY;
        case E_CORE_WARNING: // 32 //
          return LogiksLogger::LOG_WARN;
        case E_COMPILE_ERROR: // 64 //
          return LogiksLogger::LOG_EMERGENCY;
        case E_COMPILE_WARNING: // 128 //
          return LogiksLogger::LOG_WARN;
        case E_USER_ERROR: // 256 //
          return LogiksLogger::LOG_CRITICAL;
        case E_USER_WARNING: // 512 //
          return LogiksLogger::LOG_ALERT;
        case E_USER_NOTICE: // 1024 //
          return LogiksLogger::LOG_NOTICE;
        case E_STRICT: // 2048 //
          return LogiksLogger::LOG_NOTICE;
        case E_RECOVERABLE_ERROR: // 4096 //
          return LogiksLogger::LOG_WARNING;
        case E_DEPRECATED: // 8192 //
          return LogiksLogger::LOG_WARNING;
        case E_USER_DEPRECATED: // 16384 //
          return LogiksLogger::LOG_NOTICE;
        case E_EXCEPTION:	// 32768 //
          return LogiksLogger::LOG_WARNING;
        case E_LOGIKS_ERROR: // 65536 //
          return LogiksLogger::LOG_CRITICAL;
    }
    return LogiksLogger::LOG_ERROR;
  }

  protected function loadLogConfig($configFile) {
    $cfgHandler=loadJSONConfig("errorlogs","handler",true);
    $cfgLogger=loadJSONConfig("errorlogs","logger",true);

    $siteKey="core";
    if(defined("SITENAME")) $siteKey=SITENAME;
    $lr=new LogiksReplace();

    //Create all handlers
    foreach ($cfgHandler as $key => $cfg) {
        foreach ($cfg as $clz => $param) {
          if(isset($param['formatter'])) {
            if(isset($param['formatter']['params1'])) {
              $params1=$lr->_replace($param['formatter']['params1']);
            } else $params1=null;
            if(isset($param['formatter']['params2'])) {
              $params2=$lr->_replace($param['formatter']['params2']);
            } else $params2=null;

            include_once ROOT."api/vendors/Monolog/".str_replace("\\", "/", $param['formatter']['class']).".php";


            $formatter = new $param['formatter']['class']($params1, $params2);
          } else {
            $formatter=null;
          }

          $_SESSION['logFilePath']=$this->logPath.$siteKey."/".$key;

          if(!is_dir($_SESSION['logFilePath']) && strpos(strtolower($clz), "null")<=0) {
            mkdir($_SESSION['logFilePath'],0755,true);
          }

          if(isset($param['params1'])) {
            $params1=$lr->_replace($param['params1']);
          } else $params1=null;
          if(isset($param['params2'])) {
            $params2=$lr->_replace($param['params2']);
          } else $params2=null;

          if(isset($param['level'])) {
            $level=$param['level'];
          } else $level=LOG_DEFAULT_LEVEL;

          include_once ROOT."api/vendors/Monolog/".str_replace("\\", "/", $clz).".php";

          if($params1==null) {
            $handler = new $clz();
          } elseif($params2==null) {
            $handler = new $clz($params1);
          } else {
            $handler = new $clz($params1, $params2);
          }
          
          if(method_exists($handler, "setLevel")) {
            $handler->setLevel($level);
          }

          if($formatter!=null) {
            $handler->setFormatter($formatter);
          }

          //unset($cfgHandler[$key][$clz]);
          $cfgHandler[$key]=$handler;
        }
    }
    $this->logHandlers=$cfgHandler;
    //Create all loggers
    foreach ($cfgLogger as $loggerName => $cfg) {
        $handlers=$cfg['handler'];

        if(is_string($handlers)) {
          if(isset($this->logHandlers[$handlers])) {
            $handlers=$this->logHandlers[$handlers];
          }
        } else {
          $keys=array_keys($handlers);
          $temp=array();
          foreach ($handlers as $h) {
            if(isset($this->logHandlers[$h])) {
              $temp[]=$this->logHandlers[$h];
            }
          }
          $handlers=$temp;
        }

        $this->createLogger($loggerName,$handlers);
    }
  }

  public function loadSiteConfig() {
    $config=ROOT."config/jsonConfig/errorlogs.json";
    $this->loadLogConfig($config);
  }

  public function getLogBook($site) {
    $logger1=[];$logger2=[];
    if(isset($this->logBook['core'])) $logger1=$this->logBook['core'];
    if(isset($this->logBook[$site])) $logger2=$this->logBook[$site];
    return array_merge($logger1,$logger2);
  }

  //Finally log the data into the logger
  public static function log($logKey, $logLevel, $msg, $param=array()) {
    //echo "LOGGING : $logKey > $logLevel > $msg >";//$parame
    if(defined("SITENAME")) {
      $logBook=LogiksLogger::getInstance()->getLogBook(SITENAME);
    } else {
      $logBook=LogiksLogger::getInstance()->getLogBook("core");
    } 
    
    //var_dump($logBook[$logKey]);exit();
    if($logKey=="*") {
      foreach ($logBook as $a => $logger) {
          $logger->log($logLevel,$msg,$param);
      }
    } elseif(isset($logBook[$logKey])) {
      $logBook[$logKey]->log($logLevel,$msg,$param);
    } else {
      $logBook["error"]->log($logLevel,$msg,$param);
    }
    return false;
  }
}
?>
