<?php
/*
 * Mobility Device Identifier and other mobility related functions
 * 
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getUserDeviceType")) {
	function getUserDeviceType() {
		$out=array("pc","mobile","tablet");		
		if(isset($_COOKIE['USER_DEVICE'])) {
			return $_COOKIE['USER_DEVICE'];
		} else {
			$detect = new DeviceDetection();
			$device=$out[0];
			if($detect->isMobile()) {
				$device=$out[1];
			} elseif($detect->isTablet()) {
				$device=$out[2];
			} else {
				$device=$out[0];
			}
			setCookie("USER_DEVICE",$device,null,"/",$_SERVER['SERVER_NAME'], isHTTPS());
			return $device;
		}
	}
	
	function switchUserDeviceType($device="pc") {
		$device=strtolower($device);
		if($device=="pc") {
			setCookie("USER_DEVICE",$device,null,"/",$_SERVER['SERVER_NAME'], isHTTPS());
		} elseif($device=="mobile") {
			setCookie("USER_DEVICE",$device,null,"/",$_SERVER['SERVER_NAME'], isHTTPS());
		} elseif($device=="tablet") {
			setCookie("USER_DEVICE",$device,null,"/",$_SERVER['SERVER_NAME'], isHTTPS());
		} else {
			setCookie("USER_DEVICE","pc",null,"/",$_SERVER['SERVER_NAME'], isHTTPS());
		}
		return $device;
	}
	
	function getUserDevice() {
		$detect = new DeviceDetection();
		if($detect->isBlackberrytablet()){//$detect->isMobile()
			return "BlackBerry Tablet";
		}elseif($detect->isAndroidtablet()){//$detect->isMobile()
			return "Android Tablet";
		}elseif($detect->isWindowsphone()){//$detect->isMobile()
			return "Windows Phone";
		}elseif($detect->isWindows()){//$detect->isMobile()
			return "Windows Mobile";
		}elseif($detect->isIpad()){//$detect->isMobile()
			return "iPad";
		}elseif($detect->isIphone()){//$detect->isMobile()
			return "iPhone";
		}elseif($detect->isBlackberry()){
			return "BlackBerry Mobile";
		}elseif($detect->isAndroid()){//$detect->isMobile()
			return "Android Mobile";
		}elseif($detect->isPalm()){//$detect->isMobile()
			return "Palm";
		}elseif($detect->isGeneric()){//$detect->isMobile()
			return "Generic";
		}else{
			return "PC";
		}
	}
}

?>
