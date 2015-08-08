<?php
/*
 * This class is meant to maintain a singlton function call sequences based function calls.
 * When any function is called upon that wants itself to be called single time only, then this class
 * handles the specifics.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 05/07/2014
 * Version: 1.0
 */
class LogiksSingleton {
	private static $bootedFuncs=array();
	private static $singltonFuncs=array();

	public static function funcRegister($func,$params=array()) {
		if(!isset(LogiksSingleton::$singltonFuncs[$func])) {
			LogiksSingleton::$singltonFuncs[$func]=$params;
			return true;
		}
		return false;
	}

	public static function funcCheckout($func) {
		if(isset(LogiksSingleton::$singltonFuncs[$func])) {
			if(isset(LogiksSingleton::$bootedFuncs[$func])) {
				return false;
			} else {
				LogiksSingleton::$bootedFuncs[$func]=1;
				return true;
			}
		}
		return true;
	}

	public static function funcLoaded($func) {
		if(isset(LogiksSingleton::$singltonFuncs[$func])) {
			if(isset(LogiksSingleton::$bootedFuncs[$func])) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
}
?>
