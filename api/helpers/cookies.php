<?php
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
		setcookie($name, $value, $time, $path);
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
