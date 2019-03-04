<?php
/*
 * This handles all the errors and exeptions in Logiks Framework.
 * Exceptions are thrown - they are intended to be caught. (User Defined)
 * Errors are generally unrecoverable. (Just Happens :-( )
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("trigger_logikserror")) {

  include_once dirname(__FILE__)."/definations.php";
  include_once dirname(__FILE__)."/logikslogger.inc";
  include_once dirname(__FILE__)."/logiksexception.inc";
  include_once dirname(__FILE__)."/logikserror.inc";

  //Peacefull Error printing
  function print_error($message, $noLog=false) {
    echo "<h2 class='errorBox errorMsg' align=center>"._ling($message)."</h2>";
    
    if(!$noLog) {
    	$caller=debug_backtrace();
    	$caller=current($caller);
    	
    	$logMsg="{$caller['file']}:{$caller['line']}";
    	$logData=$caller['args'];
    	
    	_log($logMsg, "error", LogiksLogger::LOG_WARNING, $logData);
    }
  }
	
  //Logiks ERROR Trigger Function
  function trigger_logikserror($message, $severity=E_USER_NOTICE, $errorCode=null) {
    if(is_numeric($message)) {
      $errorCode=$message;
      $message=getErrorTitle($errorCode);
    }
    if($errorCode==null) $errorCode=500;

    $caller=debug_backtrace();
    $caller = current($caller);

    $file = "unknown file";
		$line = 0;
    
    if(in_array($severity,E_LOGIKS_NOERROR)) return;

    if($caller !== NULL) {
      if(isset($caller['file'])) $file=$caller['file'];
      if(isset($caller['line'])) $line=$caller['line'];

      if(defined("SERVICE_ROOT")) {
        LogiksError::handleJSON($severity, $message, $file, $line, $errorCode);
      } else {
        LogiksError::handle($severity, $message, $file, $line, $errorCode);
      }
    } else {
      trigger_error($message,$severity);
    }
  }
  //ERROR Handler Function
	function errorHandler($severity, $errMsg, $file, $line) {
    if(in_array($severity,E_LOGIKS_NOERROR)) return;
    //LOGGING is done by LogiksError
    if(defined("SERVICE_ROOT")) {
      LogiksError::handleJSON($severity, $errMsg, $file, $line, 500);
    } else {
      LogiksError::handle($severity, $errMsg, $file, $line, 500);
    }
  }

	//ERROR Handler For Fatal Errors
	function fatalErrorHandler() {
		$file = "unknown file";
		$errMsg  = "shutdown";
		$severity   = E_CORE_ERROR;
		$line = 0;

		$error = error_get_last();

		if($error !== NULL) {
			$severity   = $error["type"];
			$errMsg  = $error["message"];
			$file = $error["file"];
			$line = $error["line"];
      
      if(in_array($severity,E_LOGIKS_NOERROR)) return;

      //LOGGING is done by LogiksError
      if(defined("SERVICE_ROOT")) {
        LogiksError::handleJSON($severity, $errMsg, $file, $line, 500);
      } else {
        LogiksError::handle($severity, $errMsg, $file, $line, 500);
      }
	  }
	}

  //Exception Handler Function
  function exceptionHandler($exception) {
    $errData=[];
    $errData['severity']=E_EXCEPTION;
    $errData['msg']=$exception->getMessage();
    $errData['code']=$exception->getCode();
    $errData['file']=$exception->getFile();
    $errData['line']=$exception->getLine();
    
    if(in_array($errData['severity'],E_LOGIKS_NOERROR)) return;

    if(EXCEPTION_CONSOLE_TRACE) {
      $errData['trace']=$exception->getTrace();
      $errData['trace_str']=$exception->getTraceAsString();
    }

    if(defined("SERVICE_ROOT")) {
      LogiksError::handleJSON($errData['severity'], $errData['msg'], $errData['file'], $errData['line'], $errData['code']);
    } else {
      //JS Console Logging for advanced debugging
      $logKeys=LogiksLogger::getInstance()->getLogKeys();
      if(!in_array("console", $logKeys)) {
        LogiksLogger::getInstance()->registerHandler("console",new Monolog\Handler\BrowserConsoleHandler());
      }
      LogiksLogger::log("console",LogiksLogger::LOG_WARNING,$errData['msg'],$errData);

      if(EXCEPTION_TO_ERROR) {
        LogiksError::handle($errData['severity'], $errData['msg'], $errData['file'], $errData['line'], $errData['code']);
      } elseif(EXCEPTION_TO_SCREEN) {
        LogiksError::handleExpection($errData['severity'], $errData['msg'], $errData['file'], $errData['line'], $errData['code']);
        echo "<div class='logiksException'>{$errData['msg']} <citie>({$errData['severity']})</citie></div>";
      } else {
        var_dump($exception);
      }
    }
  }

  /*
  * Logs the given message in appropiate logger for the current site.
  * 
  * $logMsg       The message that needs to be logged
  * $logKey       The logger on to which the log has to be done eg. acivity, error, console, system, requests, etc.
  * $logLevel     The logging level of information
  * $logData      An array of extra parameters that needs to be logged like userids, etc
  */
  function _log($logMsg, $logkey="activity", $logLevel=null, $logData=array()) {
    if($logLevel==null) {
      $logLevel=LogiksLogger::LOG_WARNING;
    }
    if(_server('REQUEST_TIME_FLOAT')) {
      $logData['time']=(microtime(true)-_server('REQUEST_TIME_FLOAT'));
    } elseif(_server('REQUEST_PAGE_START')) {
      $logData['time']=(microtime(true)-_server('REQUEST_PAGE_START'));
    } elseif(_server('REQUEST_SERVICE_START')) {
      $logData['time']=(microtime(true)-_server('REQUEST_SERVICE_START'));
    }

    LogiksLogger::log($logkey,$logLevel,$logMsg,$logData);
  }

	//Start the LogiksLogger Instance for logging everything
	LogiksLogger::getInstance();

  //Set error handlers
	set_error_handler("errorHandler");

	//Fatal Handler
	register_shutdown_function( "fatalErrorHandler" );

	//Set exception handlers
	set_exception_handler("exceptionHandler");

  //Switching php display error off
	ini_set('display_errors', 'Off');
}
?>
