<?php
/*
 * This file is used for session based minor Encryption And Decryption.
 * This mostly does not support cross session encryption and decryption.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksEncryption {
    private $skey = "asd4564asd5as44e";//session_id();

	public function __construct($skey=null) {
		if($skey==null) {
			$this->skey=md5(session_id());
		}
	}

    public function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value){
        if(!$value){return false;}
        $text = $value;
        if(function_exists("mcrypt_get_iv_size")) {
        	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	        $text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
        }
        return trim($this->safe_b64encode($text));
    }

    public function decode($value){
        if(!$value){return false;}
        $crypttext = $this->safe_b64decode($value);
        if(function_exists("mcrypt_get_iv_size")) {
        	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	        $crypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        }
        return trim($crypttext);
    }
}

?>
