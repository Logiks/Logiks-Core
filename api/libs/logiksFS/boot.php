<?php
/*
 * This file contains the startup sequences for the File System of Logiks.
 * Drivers : local,ftp,sftp,s3,dropbox,gdrive
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("_fs")) {
	include_once dirname(__FILE__)."/LogiksFSDriver.php";
	
	function _fs($fsKey="app",$params=null) {
		//$fsd=LogiksFSDriver::findInstance($fsKey);
		//if($fsd) return $fsd;

		if($params==null || !is_array($params)) {
			$cfg=loadJSONConfig("fs");
			if(!isset($cfg[$fsKey])) {
				trigger_logikserror("FS ERROR, Connection Configuration Could Not Be Found For {$fsKey}");
			} else {
				$params=$cfg[$fsKey];
			}
		}
		if(!isset($params['basedir']) || strlen($params['basedir'])<=0 || $params['basedir']=="./") {
			$params['basedir']=APPROOT;
		}
		if(!isset($params['driver'])) {
			trigger_logikserror("FS ERROR, Connection Configuration Could Not Be Found For {$fsKey}");	
		}
		$driver=$params['driver'];

		$driverClass="{$driver}FSDriver";
		$driverFile=__DIR__."/drivers/{$driverClass}.inc";
		if(file_exists($driverFile)) include_once $driverFile;
		else {
			trigger_logikserror("FS ERROR, Driver {$driver} Could Not Be Found For {$fsKey}");	
		}

		$fs=new $driverClass($fsKey, $params);

		//if(isset($params['basedir']) && $params['basedir']!=$fs->pwd()) {
		//	$fs->cd($params['basedir']);
		//}

		return $fs;
	}
}
?>