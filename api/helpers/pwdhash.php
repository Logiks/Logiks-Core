<?php
/*
 * Password Related Functionalities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getPWDHash")) {
	function getPWDHash($pwd,$salt=null) {
		if(strlen(getConfig("PWD_HASH_TYPE"))<=0 || !getConfig("PWD_HASH_TYPE")) {
			setConfig("PWD_HASH_TYPE","logiks");
		}
		switch (strtolower(getConfig("PWD_HASH_TYPE"))) {
			case 'md5':
				return md5($pwd);
				break;
			case 'sha1':
				return sha1($pwd);
				break;
			case "shamd5":
				return sha1(md5($pwd));
				break;
			default:
				if($salt==null) {
					$salt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

					$options = [
						    'cost' => getConfig("HASH_COST"),
						    'salt' => $salt,
						];
					$hash=password_hash($pwd, PASSWORD_BCRYPT, $options);
					$options['hash']=$hash;
					return $options;
				} else {
					$options = [
						    'cost' => getConfig("HASH_COST"),
						    'salt' => $salt,
						];
					$hash=password_hash($pwd, PASSWORD_BCRYPT, $options);
					return $hash;
				}
				break;
		}
		return "";
	}
	function matchPWD($pwdHash, $pwd, $salt) {
		if(strlen(getConfig("PWD_HASH_TYPE"))<=0 || !getConfig("PWD_HASH_TYPE")) {
			setConfig("PWD_HASH_TYPE","logiks");
		}
		if(!isValidMd5($pwd)) $pwd=md5($pwd);

		$newHash=getPWDHash($pwd, $salt);

		//println($pwdHash);println(getPWDHash($pwd, $salt));exit($pwd);
		
		return ($pwdHash===$newHash);
	}

	function isValidMd5($md5Hash) {
	    return preg_match('/^[a-f0-9]{32}$/', $md5Hash);
	}
}
?>
