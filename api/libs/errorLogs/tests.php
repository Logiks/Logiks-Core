<?php
/*
 * For Testing system support only
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("trigger_logikserror")) {

  function trigger_logikserror($message, $severity=E_USER_NOTICE, $errorCode=null) {
    trigger_error($message,$severity);
  }
  
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
}
?>
