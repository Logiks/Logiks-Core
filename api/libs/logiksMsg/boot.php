<?php
/*
 * This file contains the startup sequences for the Messaging System of Logiks.
 * Drivers : logfile,php,sendmail,smtp,mailgun,madnril,sns
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("_message")) {
	include_once dirname(__FILE__)."/LogiksMSGDriver.php";
	
	function _msg($msgKey="app",$params=null) {
		return _message($msgKey,$params);
	}
	
	function _message($msgKey="app",$params=null) {
		//$msd=LogiksMsgDriver::findInstance($msgKey);
		//if($msd) return $msd;

		if($params==null || !is_array($params)) {
			$cfg=loadJSONConfig("message");
			if(!isset($cfg[$msgKey])) {
				trigger_logikserror("MSG ERROR, Connection Configuration Could Not Be Found For {$msgKey}");
			} else {
				$params=$cfg[$msgKey];
			}
		}
		$driver=$params['driver'];

		$driverClass="{$driver}MSGDriver";
		$driverFile=__DIR__."/drivers/{$driverClass}.inc";

		if(file_exists($driverFile)) include_once $driverFile;
		else {
			trigger_logikserror("MSG ERROR, Connection Driver Could Not Be Found For {$msgKey}");
		}

		$msg=new $driverClass($msgKey, $params);
		
		return $msg;
	}
}
?>