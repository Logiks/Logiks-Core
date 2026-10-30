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
		
		if(!isValidMd5($pwd)) $pwd=md5($pwd);
		
		switch (strtolower(getConfig("PWD_HASH_TYPE"))) {
			case 'md5':
				return md5($pwd);
				break;
			case 'sha1':
				return sha1($pwd);
				break;
			/*case "shamd5":
				return sha1(md5($pwd));
				break;*/
			default:
				if($salt==null || strlen($salt)<=0 || $salt===false) {
					$salt = LogiksEncryption::generateSalt();

					$options = [
						    'cost' => getConfig("HASH_COST"),
						    'salt' => $salt,
						];
					// $hash=password_hash($pwd, PASSWORD_BCRYPT, $options);
					$hash=crypt($pwd, '$2a$'.$options['cost'].'$'.$salt.'$');

					$options['hash']=$hash;
					return $options;
				} else {
					$options = [
						    'cost' => getConfig("HASH_COST"),
						    'salt' => $salt,
						];
					//$hash=password_hash($pwd, PASSWORD_BCRYPT, $options);
					$hash=crypt($pwd, '$2a$'.$options['cost'].'$'.$salt.'$');

					$options['hash']=$hash;
					return $options;
				}
				break;
		}
		return "";
	}
	function matchPWD($pwdHash, $pwd, $salt=null) {
		if(strlen(getConfig("PWD_HASH_TYPE"))<=0 || !getConfig("PWD_HASH_TYPE")) {
			setConfig("PWD_HASH_TYPE","logiks");
		}

		$newHash=getPWDHash($pwd, $salt);
// 		printArray([$newHash,$pwd,$salt,$pwdHash]);
		if(is_array($newHash)) $newHash=$newHash['hash'];

		//println($pwdHash);println(getPWDHash($pwd, $salt));exit($pwd);
		
// 		println(($pwdHash===$newHash));exit("XXX $pwdHash $newHash ".getConfig("PWD_HASH_TYPE"));
		
		return ($pwdHash===$newHash);
	}

	/*
	 * Decrypts the password a login page sent encrypted (AES-CBC with zero padding, the scheme the
	 * removed mcrypt Rijndael-128 code used). $saltHex is the session LOGINSALT used as key and $ivHex
	 * the client's pubkey, both hex strings. Returns the password, or false if it cannot be decrypted.
	 */
	function decryptLoginPassword($payloadB64, $saltHex, $ivHex) {
		if(!function_exists("openssl_decrypt")) return false;
		if(!is_string($payloadB64) || !is_string($saltHex) || !is_string($ivHex)) return false;
		if(!ctype_xdigit($saltHex) || !ctype_xdigit($ivHex) || strlen($saltHex)%2 || strlen($ivHex)%2) return false;

		$key=pack("H*", $saltHex);
		$iv=pack("H*", $ivHex);
		$encrypted=base64_decode($payloadB64, true);
		if($encrypted===false || $encrypted==="" || strlen($iv)!==16 || strlen($encrypted)%16!==0) return false;

		//mcrypt zero-padded a short key up to the next valid AES size; keep that behaviour
		$len=strlen($key);
		if($len>32 || $len==0) return false;
		$size=$len<=16?16:($len<=24?24:32);
		$key=str_pad($key, $size, "\0");

		$plain=openssl_decrypt($encrypted, "aes-".($size*8)."-cbc", $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
		if($plain===false) return false;
		return trim($plain);
	}

	function isValidMd5($md5Hash) {
	    return preg_match('/^[a-f0-9]{32}$/', $md5Hash);
	}
}
?>
