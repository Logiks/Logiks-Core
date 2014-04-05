<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//Password Related Functionalities
if(!function_exists("getPWDHash")) {
	function getPWDHash($pwd) {
		if(strlen(getConfig("PWD_HASH_TYPE"))<=0 || !getConfig("PWD_HASH_TYPE")) {
			setConfig("PWD_HASH_TYPE","pwdhash");
		}
		if(getConfig("PWD_HASH_TYPE")=="md5") return md5($pwd);
		elseif(getConfig("PWD_HASH_TYPE")=="sha1") return sha1($pwd);
		else return PwdHash::hash($pwd);
	}
	function matchPWD($hash, $pwd) {
		if(strlen(getConfig("PWD_HASH_TYPE"))<=0 || !getConfig("PWD_HASH_TYPE")) {
			setConfig("PWD_HASH_TYPE","pwdhash");
		}
		if(getConfig("PWD_HASH_TYPE")=="md5") return ($hash==md5($pwd));
		elseif(getConfig("PWD_HASH_TYPE")=="sha1") return ($hash==sha1($pwd));
		else return PwdHash::check_password($hash, $pwd);
	}
}
class PwdHash {
	// blowfish
	private static $algo = '$2a';
	// cost parameter
	private static $cost = '$10';

	//mainly for internal use
	public static function unique_salt() {
		return substr(sha1(mt_rand()),0,22);
	}

	// this will be used to generate a hash
	public static function hash($password) {
		return crypt($password,
			self::$algo .
			self::$cost .
			'$'.self::unique_salt());
	}

	// this will be used to compare a password against a hash
	public static function check_password($hash, $password) {
		$full_salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $full_salt);		
		return ($hash == $new_hash);
	}
}
?>
