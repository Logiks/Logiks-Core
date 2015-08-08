<?php
/*
 * LogiksLogger is meant to support various kinds of logging facility using
 * Monolog vendor library.
 *
 * Usage:
 *  Create New Logger
 *    LogiksLogger::getInstance()->createLogger("console",new Monolog\Handler\BrowserConsoleHandler());
 *  Add New Handler to existing
 *    LogiksLogger::getInstance()->addHandler("core",new Monolog\Handler\BrowserConsoleHandler());
 *  Attach New Handler
 *    LogiksLogger::log("core",LogiksLogger::LOG_WARN,"Hello World",array("A"=>123));
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

    $this->createLogger("core");

    //$config=ROOT."config/security/loggers.json";
    //$this->loadLogConfig($config);
  }

  public function getLogKeys() {
    return array_keys($this->logBook);
  }
  public function getLogger($logKey) {
    if(isset($this->logBook[$logKey])) {
      return $this->logBook[$logKey];
    }
    return false;
  }
  public function register($logKey,$logHandler,$allowSimilarClass=false) {
    $clz=get_class($logHandler);
    if($logKey=="*") {
      foreach ($this->logBook as $a => $logger) {
        if(!$allowSimilarClass) {
          foreach ($logger->getHandlers() as $key => $value) {
            if(get_class($value)==$clz) {
              return false;
            }
          }
        }
        $this->logBook[$a]->pushHandler($logHandler);
      }
      return true;
    } elseif(isset($this->logBook[$logKey])) {
      $logger=$this->logBook[$logKey];

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
      $this->createLogger($logKey, $logHandler);
      return true;
    }
  }

  public function createLogger($loggerName,$handler=null) {
    $logger = new Logger($loggerName);

    if($handler==null) {
      $handler=$this->getDefaultHandler($loggerName);
    }
    $logger->pushHandler($handler);

    return $this->addLogger($loggerName, $logger);
  }

  public function addLogger($key, $logger) {
    if($logger==null) return false;
    $logger->pushProcessor(function ($record) {
          //$record['extra']['user'] = Auth::user() ? Auth::user()->username : 'anonymous';
          $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'];
          $params=explode(",", LOG_PARAMS);
          foreach ($params as $px) {
            if(isset($_SESSION[$px])) $record['extra'][$px] = $_SESSION[$px];
            elseif(isset($_REQUEST[$px])) $record['extra'][$px] = $_REQUEST[$px];
          }
          return $record;
      });

    $this->logBook[$key] = $logger;
    return $this->logBook[$key];
  }

  private function getDefaultHandler($loggerName) {
    $logFile= $this->logPath.$loggerName."/a.log";
    if(!is_dir(dirname($logFile))) mkdir(dirname($logFile),0755,true);

    $dateFormat = LOG_DATE." ".LOG_TIME;
    $output = LOG_FORMAT."\n";
    $formatter = new Monolog\Formatter\LineFormatter($output, $dateFormat);

    $handler=new Monolog\Handler\RotatingFileHandler($logFile, LOG_ROLL_PERIOD, LOG_DEFAULT_LEVEL);
    $handler->setFormatter($formatter);

    return $handler;
  }

  public static function log($logKey, $logLevel, $msg, $param=null) {
    //echo "LOGGING : $logKey > $logLevel > $msg > $param";
    if($logKey=="*") {
      foreach ($this->logBook as $a => $logger) {
        $logger->log($logLevel,$msg,$param);
      }
      return true;
    } elseif(isset(LogiksLogger::$instance->logBook[$logKey])) {
      $logger=LogiksLogger::$instance->logBook[$logKey];
      $logger->log($logLevel,$msg,$param);
      return true;
    }
    return false;
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

  // protected function loadLogConfig($config) {
  //   if(file_exists($config)) {
  //     $configData=file_get_contents($config);
  //     $configData=json_decode($configData,true);
  //
  //     foreach ($configData as $loggerName => $logbus) {
  //       $logger = new Logger($loggerName);
  //       foreach ($logbus['handlers'] as $handler) {
  //         if(!isset($handler['level'])) $handler['level']="ERROR";
  //
  //         print_r($handler);
  //
  //         //echo $handler['class'];
  //         //use
  //         //print_r($handler);echo "<br/><br/>";
  //       }
  //     }
  //   }
  //
  //   //$logger=new Logger('default');
  //   //$this->register("core", $logger)
  // }
}
?>
