<?php
//This is the dbLogger for Logiks Framework. It helps in Logging Events 
//Specific to Logiks Into Database Tables. This is advanced Logger.
//This is table Specific.
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "libs/syslogger.php";
include_once "libs/LogDB.inc";

if(!function_exists("log_ErrorEvent")) {	
	function logCentral($module,$msg,$errCode=500,$arr=array(),$type="system") {
		if($type=="system") {
			log_SystemEvent($msg, $errCode, 2, $module, null);
		} elseif($type=="activity") {
			log_ActivityEvent($msg, $errCode, 2, $module, null);
		} elseif($type=="error") {
			ob_start();
			var_dump($arr);
			$log=ob_get_contents();
			ob_clean();
			log_ErrorEvent($errCode, $msg, $log);
		}
	}
	
	function log_ErrorEvent($errorCode,$errorMsg=null,$errorLog=null,$source=null) {
		$xmsg="TriggeredError +$errorCode @".$_SERVER["REQUEST_URI"]." #$errorMsg [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,3);
		return LogDB::singleton()->log_ErrorEvent($errorCode,$errorMsg,$errorLog,$source);
	}
	//$priority= Integer :: Lower The Better
	function log_SystemEvent($logData, $codeType="500", $priority=2, $module=null, $source=null) {
		$xmsg="TriggeredError +$codeType @".$_SERVER["REQUEST_URI"]." #$logData [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,1);
		return LogDB::singleton()->log_SystemEvent($logData,$codeType, $priority, $module, $source);
	}
	//$priority= Integer :: Lower The Better
	function log_ActivityEvent($logData, $codeType="500", $priority=2, $module=null, $source=null) {
		$xmsg="TriggeredError +$codeType @".$_SERVER["REQUEST_URI"]." #$logData [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,2);
		return LogDB::singleton()->log_ActivityEvent($logData,$codeType, $priority, $module, $source);
	}
	function log_SearchEvent($stext) {
		return LogDB::singleton()->log_SearchEvent($stext);
	}
	function log_Requests() {
		return LogDB::singleton()->log_Requests();
	}
	function log_ServiceRequest() {
		return LogDB::singleton()->log_ServiceRequest();
	}
	function log_VisitorEvent() {
		return LogDB::singleton()->log_VisitorEvent();
	}
	function get_LOG_EVENTS_VISITOR() {
		$arr=array();
		$arr["None"]="none";
		$arr["Always (Once A Session)"]="always";
		$arr["Once A Day"]="onceaday";
		$arr["Once A Month"]="onceamonth";
		$arr["Once A Year"]="onceayear";
		$arr["Once In Life Time"]="lifetime";
		return $arr;
	}
}
?>
