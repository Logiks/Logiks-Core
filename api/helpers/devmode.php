<?php
/*
 * DevMode functionality allows limiting access to used server/app only to
 * limited developer ips
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("__testDevMode")) {
	function __testDevMode($addr,$msg="OOPs, Down For Maintaince/Updates. Back In A Bit.") {
		if(is_array($addr)) {
			if(!in_array(_server("REMOTE_ADDR"),$addr)) {
				trigger_logikserror(808,E_USER_WARNING);
				exit();
			}
		} else {
			if(_server("REMOTE_ADDR")!=$addr) {
				trigger_logikserror(808,E_USER_WARNING);
				exit();
			}
		}
	}

	function __initDevMode() {
	    if(defined("DEV_MODE_IP") && strlen(DEV_MODE_IP)>0) {
	      $addr=explode(",",DEV_MODE_IP);
	      if(count($addr)>0) {
	        if(is_array($addr)) {
				if(!in_array(_server("REMOTE_ADDR"),$addr)) {
					ob_start();
				}
			} else {
				if(_server("REMOTE_ADDR")!=$addr) {
					ob_start();
				}
			}
	      }
	    }
   }

   	function __closeDevMode() {
	    if(defined("DEV_MODE_IP") && strlen(DEV_MODE_IP)>0) {
	      $addr=explode(",",DEV_MODE_IP);
	      if(count($addr)>0) {
	        	if(is_array($addr)) {
					if(!in_array(_server("REMOTE_ADDR"),$addr)) {
						ob_end_clean();
					} else {
						ob_end_flush();
					}
				} else {
					if(_server("REMOTE_ADDR")!=$addr) {
						ob_end_clean();
					} else {
						ob_end_flush();
					}
				}
	      }
	    }
   }
}
?>
