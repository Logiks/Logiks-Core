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

    public static generateSalt() {
        return strtr(base64_encode(substr(sha1(mt_rand()), 0, 16)), '+', '.');
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

    public function encode($data, $password){
        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($password);

        $salt = sha1(mt_rand());
        $saltWithPassword = hash('sha256', $password.$salt);

        $encrypted = openssl_encrypt(
          "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }

    public function decode($msg_encrypted_bundle, $password){
        $password = sha1($password);

        $components = explode( ':', $msg_encrypted_bundle );
        $iv            = $components[0];
        $salt          = hash('sha256', $password.$components[1]);
        $encrypted_msg = $components[2];

        $decrypted_msg = openssl_decrypt(
          $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ( $decrypted_msg === false )
            return false;

        // $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }

    public function encode1($value){
        if(!$value){return false;}
        $text = $value;
        if(function_exists("mcrypt_get_iv_size")) {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
        }
        return trim($this->safe_b64encode($text));
    }

    public function decode1($value, $password){
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
