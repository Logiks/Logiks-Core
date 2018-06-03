<?php
/*
 * Cookie related functions
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("deleteCookie")) {
	function deleteCookie($name,$path="/") {
		setcookie($name, "", -1, $path);
		if(isset($_COOKIE[$name])) unset($_COOKIE[$name]);
	}

	function createCookie($name,$value,$time=null,$path="/") {
		if($time!=null) {			
			if(is_numeric($time)) $time=time()+$time;
			else $time=time()+3600; //1 Hour
		}
		setCookie($name, $value, $time, $path,$_SERVER['SERVER_NAME'], isHTTPS());
		$_COOKIE[$name]=$value;
	}
	
	function clearCookies($noClear=array("LGKS_SESS_SITE","PHPSESSID","USER_DEVICE")) {
		if($noClear==null) $noClear=array();
		foreach($_COOKIE as $a=>$b) {
			if(!in_array($a,$noClear)) deleteCookie($a);
		}
	}
}
?>
